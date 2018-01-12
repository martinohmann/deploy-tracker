<?php

namespace DeployTracker\Tests\EventListener;

use DeployTracker\EventListener\RedirectToRouteSubscriber;
use DeployTracker\Exception\RedirectToRouteException;
use PHPUnit\Framework\TestCase;
use Phake;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectToRouteSubscriberTest extends TestCase
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGeneratorMock;

    /**
     * @var GetResponseForExceptionEvent
     */
    private $eventMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->urlGeneratorMock = Phake::mock(UrlGeneratorInterface::class);
        $this->eventMock = Phake::mock(GetResponseForExceptionEvent::class);
    }

    /**
     * @test
     */
    public function shouldSetResponseOnRedirectToRouteException()
    {
        $exception = new RedirectToRouteException('some_route_name');

        Phake::when($this->urlGeneratorMock)->generate->thenReturn('/some-route');
        Phake::when($this->eventMock)->getException->thenReturn($exception);

        $subscriber = new RedirectToRouteSubscriber($this->urlGeneratorMock);
        $subscriber->onKernelException($this->eventMock);

        Phake::verify($this->eventMock)->setResponse(Phake::capture($response));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/some-route', $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldOnlySetResponseOnRedirectToRouteException()
    {
        $exception = new \Exception('this is some other exception');

        Phake::when($this->eventMock)->getException->thenReturn($exception);

        $subscriber = new RedirectToRouteSubscriber($this->urlGeneratorMock);
        $subscriber->onKernelException($this->eventMock);

        Phake::verify($this->eventMock, Phake::never())->setResponse;
    }
}
