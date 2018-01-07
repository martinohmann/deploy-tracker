<?php

namespace DeployTracker\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use DeployTracker\Controller\ApiController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ApiAuthTokenSubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $authToken;

    /**
     * @var string
     */
    private $authTokenParam;

    /**
     * @param string $authToken
     * @param string $authTokenParam
     */
    public function __construct(string $authToken, string $authTokenParam = 'auth_token')
    {
        $this->authToken = $authToken;
        $this->authTokenParam = $authTokenParam;
    }

    /**
     * @param FilterControllerEvent $event
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $controller = $event->getController();

        if ($controller[0] instanceof ApiController) {
            $request = $event->getRequest();

            if ($request->query->get($this->authTokenParam) !== $this->authToken) {
                throw new AccessDeniedHttpException();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
