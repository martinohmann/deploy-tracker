<?php

namespace DeployTracker\Manager;

use DeployTracker\Repository\ApplicationRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DeployTracker\Manager\RepositoryManager;

class ApplicationManager extends RepositoryManager
{
    /**
     * @param ApplicationRepository $repository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ApplicationRepository $repository, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($repository, $eventDispatcher);
    }
}
