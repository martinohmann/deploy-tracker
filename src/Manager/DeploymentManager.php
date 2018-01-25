<?php

namespace DeployTracker\Manager;

use DeployTracker\Repository\DeploymentRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use DeployTracker\Manager\RepositoryManager;

class DeploymentManager extends RepositoryManager
{
    /**
     * @param DeploymentRepository $repository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(DeploymentRepository $repository, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($repository, $eventDispatcher);
    }
}
