<?php

namespace DeployTracker\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use DeployTracker\Event\FilterEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use DeployTracker\Event\PreFindEvent;
use DeployTracker\Resolver\PageResolver;
use DeployTracker\Resolver\RepositoryFilterResolver;
use DeployTracker\Repository\FilterableInterface;

class PreFindEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var PageResolver
     */
    private $pageResolver;

    /**
     * @var RepositoryFilterResolver
     */
    private $filterResolver;

    /**
     * @param RequestStack $requestStack
     * @param PageResolver $pageResolver
     * @param RepositoryFilterResolver $filterResolver
     */
    public function __construct(
        RequestStack $requestStack,
        PageResolver $pageResolver,
        RepositoryFilterResolver $filterResolver
    ) {
        $this->requestStack = $requestStack;
        $this->pageResolver = $pageResolver;
        $this->filterResolver = $filterResolver;
    }

    /**
     * @param PreFindEvent $event
     * @return void
     */
    public function onPreFindEvent(PreFindEvent $event)
    {
        $repository = $event->getRepository();
        $request = $this->requestStack->getCurrentRequest();
        $page = $this->pageResolver->resolve($request);

        $event->setPage($page);

        if ($repository instanceof FilterableInterface) {
            $filters = $this->filterResolver->resolve($repository, $request);
            $filters = \array_merge($event->getFilters(), $filters);

            $event->setFilters($filters);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FilterEvents::PRE_FIND => 'onPreFindEvent',
        ];
    }
}
