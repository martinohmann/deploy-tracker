<?php

namespace DeployTracker\Importer;

use DeployTracker\Entity\RevisionLog;
use DeployTracker\Repository\DeploymentRepository;
use Psr\Log\LoggerAwareInterface;

class RevisionLogImporter implements RevisionLogImporterInterface
{
    /**
     * @var DeploymentRepository
     */
    private $repository;

    /**
     * @param DeploymentRepository $repository
     */
    public function __construct(DeploymentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function import(RevisionLog $revisionLog)
    {
        $collection = $revisionLog->getDeployments();

        $this->repository->persistCollection($collection);
    }
}
