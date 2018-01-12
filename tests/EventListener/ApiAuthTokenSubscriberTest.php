<?php

namespace DeployTracker\Tests\EventListener;

use DeployTracker\Controller\ApiController;
use DeployTracker\Controller\DashboardController;
use DeployTracker\EventListener\ApiAuthTokenSubscriber;
use PHPUnit\Framework\TestCase;
use Phake;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiAuthTokenSubscriberTest extends TestCase
{
    /**
     * @var FilterControllerEvent
     */
    private $eventMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->eventMock = Phake::mock(FilterControllerEvent::class);
    }

    /**
     * @test
     */
    public function shouldOnlyHandleApiController()
    {
        $controller = [new DashboardController(), 'index'];

        Phake::when($this->eventMock)->isMasterRequest->thenReturn(true);
        Phake::when($this->eventMock)->getController->thenReturn($controller);

        $subscriber = new ApiAuthTokenSubscriber('thisisatoken', 'auth_token');
        $subscriber->onKernelController($this->eventMock);

        Phake::verify($this->eventMock, Phake::never())->getRequest;
    }

    /**
     * @test
     */
    public function shouldOnlyHandleMasterRequest()
    {
        Phake::when($this->eventMock)->isMasterRequest->thenReturn(false);

        $subscriber = new ApiAuthTokenSubscriber('thisisatoken', 'auth_token');
        $subscriber->onKernelController($this->eventMock);

        Phake::verify($this->eventMock, Phake::never())->getController;
    }

    /**
     * @test
     */
    public function shouldThrowAccessDeniedHttpExceptionOnMissingToken()
    {
        $request = new Request();
        $controller = [new ApiController(), 'publish'];

        Phake::when($this->eventMock)->isMasterRequest->thenReturn(true);
        Phake::when($this->eventMock)->getRequest->thenReturn($request);
        Phake::when($this->eventMock)->getController->thenReturn($controller);

        $subscriber = new ApiAuthTokenSubscriber('thisisatoken', 'auth_token');

        $this->expectException(AccessDeniedHttpException::class);
        $subscriber->onKernelController($this->eventMock);
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionOnValidAuthToken()
    {
        $request = new Request();
        $request->query->set('auth_token', 'thisisatoken');
        $controller = [new ApiController(), 'publish'];

        Phake::when($this->eventMock)->isMasterRequest->thenReturn(true);
        Phake::when($this->eventMock)->getRequest->thenReturn($request);
        Phake::when($this->eventMock)->getController->thenReturn($controller);

        $subscriber = new ApiAuthTokenSubscriber('thisisatoken', 'auth_token');

        self::assertNull($subscriber->onKernelController($this->eventMock));
    }
}
