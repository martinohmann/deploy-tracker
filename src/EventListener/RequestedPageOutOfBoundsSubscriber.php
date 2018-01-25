<?php

namespace DeployTracker\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RequestedPageOutOfBoundsSubscriber implements EventSubscriberInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     * @return void
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof RequestedPageOutOfBoundsException) {
            $url = $this->generateRedirectUrl($event->getRequest(), $exception);

            $event->setResponse(new RedirectResponse($url));
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    /**
     * @param Request $request
     * @param RequestedPageOutOfBoundsException $exception
     * @return string
     */
    private function generateRedirectUrl(Request $request, RequestedPageOutOfBoundsException $exception): string
    {
        $route = (string) $request->attributes->get('_route');
        $routeParams = (array) $request->attributes->get('_route_params');
        $parameters = array_merge($routeParams, ['page' => $exception->getMaxPage()]);

        return $this->urlGenerator->generate($route, $parameters);
    }
}
