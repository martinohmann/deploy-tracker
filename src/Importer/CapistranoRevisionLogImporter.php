<?php

namespace DeployTracker\Importer;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use DeployTracker\Repository\DeploymentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use DeployTracker\Parser\RevisionLogParserInterface;
use DeployTracker\Processor\CapistranoRevisionLogProcessor;

class CapistranoRevisionLogImporter implements RevisionLogImporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var DeploymentRepository
     */
    private $repository;

    /**
     * @var RevisionLogParserInterface
     */
    private $parser;

    /**
     * @param DeploymentRepository $repository
     * @param RevisionLogParserInterface $parser
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeploymentRepository $repository,
        RevisionLogParserInterface $parser,
        LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->parser = $parser;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function import(string $filename, Application $application, string $stage)
    {
        $processor = new CapistranoRevisionLogProcessor($this->parser, $application, $stage);
        
        $collection = $processor->process($filename);

        $this->repository->persistCollection($collection);

        $this->logger->info(sprintf(
            '%d entries imported.',
            $collection->count()
        ));
    }
}
