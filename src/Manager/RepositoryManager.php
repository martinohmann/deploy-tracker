<?php

namespace DeployTracker\Manager;

use DeployTracker\Event\FilterEvents;
use DeployTracker\Event\PostFindEvent;
use DeployTracker\Event\PreFindEvent;
use DeployTracker\Result\FilteredResult;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RepositoryManager
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param EntityRepository $repository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityRepository $repository, EventDispatcherInterface $eventDispatcher)
    {
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return FilteredResult
     */
    public function __call(string $method, array $arguments): FilteredResult
    {
        $event = new PreFindEvent($this->repository);
        $this->eventDispatcher->dispatch(FilterEvents::PRE_FIND, $event);

        $arguments = \array_merge($arguments, [$event->getPage(), $event->getFilters()]);

        $paginator = \call_user_func_array([$this->repository, $method], $arguments);

        $event = new PostFindEvent($paginator, $event->getFilters());
        $this->eventDispatcher->dispatch(FilterEvents::POST_FIND, $event);

        return new FilteredResult($event->getPaginator(), $event->getFilters());
    }
}
