<?php

namespace DeployTracker\Tests\EventListener;

use DeployTracker\EventListener\RequestedPageOutOfBoundsSubscriber;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Phake;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestedPageOutOfBoundsSubscriberTest extends TestCase
{
    /**
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * @return void
     */
    public function setUp()
    {
        $routeCollection = new RouteCollection();
        $routeCollection->add('some_route', new Route('/some-route'));

        $this->urlGenerator = new UrlGenerator($routeCollection, new RequestContext());
    }

    /**
     * @test
     */
    public function shouldRedirectOnRequestedPageOutOfBoundsException()
    {
        $requestedPage = 10;
        $maxPage = 3;
        $expectedRedirectUrl = '/some-route?some_param=some_value&page=' . $maxPage;

        $request = $this->createRequestWithRoute('some_route', [
            'some_param' => 'some_value',
            'page' => $requestedPage
        ]);

        $exception = new RequestedPageOutOfBoundsException($requestedPage, $maxPage);
        $eventMock = $this->createEventMock($request, $exception);

        $subscriber = new RequestedPageOutOfBoundsSubscriber($this->urlGenerator);
        $subscriber->onKernelException($eventMock);

        Phake::verify($eventMock)->setResponse(Phake::capture($response));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame($expectedRedirectUrl, $response->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldNotRedirectOnOtherExceptions()
    {
        $exception = new \Exception('this is some other exception');
        $eventMock = $this->createEventMock(new Request(), $exception);

        $subscriber = new RequestedPageOutOfBoundsSubscriber($this->urlGenerator);
        $subscriber->onKernelException($eventMock);

        Phake::verify($eventMock, Phake::never())->setResponse;
    }

    /**
     * @test
     */
    public function shouldSubscribeToKernelExceptionEvent()
    {
        $subscribedEvents = RequestedPageOutOfBoundsSubscriber::getSubscribedEvents();

        $this->assertArrayHasKey(KernelEvents::EXCEPTION, $subscribedEvents);
    }

    /**
     * @param string $routeName
     * @param array $routeParams
     * @return Request
     */
    private function createRequestWithRoute(string $routeName, array $routeParams = []): Request
    {
        $request = new Request();

        $request->attributes->set('_route', $routeName);
        $request->attributes->set('_route_params', $routeParams);

        return $request;
    }

    /**
     * @param Request $request
     * @param \Exception $exception
     * @return GetResponseForExceptionEvent
     */
    private function createEventMock(Request $request, \Exception $exception): GetResponseForExceptionEvent
    {
        return Phake::partialMock(
            GetResponseForExceptionEvent::class,
            Phake::mock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            $exception
        );
    }
}
