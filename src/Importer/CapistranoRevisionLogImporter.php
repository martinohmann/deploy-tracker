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

class CapistranoRevisionLogImporter implements ImporterInterface, LoggerAwareInterface
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
     * @var Application
     */
    private $application;

    /**
     * @var string
     */
    private $stage;

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
        $this->stage = $stage;
        $this->application = $application;

        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \RuntimeException(sprintf(
                'File "%s" does not exist or is not readable.',
                $filename
            ));
        }
        
        $collection = $this->processRevisionLog($filename);

        $this->repository->persistCollection($collection);

        $this->logger->info(sprintf(
            '%d entries imported.',
            $collection->count()
        ));
    }

    /**
     * @param string $filename
     * @return ArrayCollection
     */
    private function processRevisionLog(string $filename): ArrayCollection
    {
        $collection = new ArrayCollection();
        $previous = null;

        foreach ($this->getFileContents($filename) as $line) {
            try {
                $parsed = $this->parser->parseLine($line);
            } catch (RevisionLogParseException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }

            if ($parsed->isSuccess()) {
                if (null !== $previous && $previous->isRollback()) {
                    $this->processRollback($previous, $parsed);

                    $collection->add($previous);
                }

                $collection->add($parsed);
            } elseif ($parsed->isRollback()) {
                if (null === $previous || $previous->isRollback()) {
                    $this->logger->warn(sprintf(
                        'Unable to determine previous deployment for rollback "%s", skipping.',
                        $line
                    ));
                    continue;
                }

                $parsed->setDeployDate(clone $previous->getDeployDate());
            }

            $parsed
                ->setApplication($this->application)
                ->setStage($this->stage);

            $previous = $parsed;
        }

        return $collection;
    }

    /**
     * Sets an estimated date on a rollback based on the deployment before and
     * after the rollback. This is required as the default capistrano revision
     * log format does not include rollback timestamps.
     *
     * @param Deployment $rollback
     * @param Deployment $current
     * @return void
     */
    private function processRollback(Deployment $rollback, Deployment $current)
    {
        $rollbackDate = $rollback->getDeployDate();
        $currentDate = $current->getDeployDate();

        $diff = abs($currentDate->getTimestamp() - $rollbackDate->getTimestamp());

        $estimatedTimestamp = $rollbackDate->getTimestamp() + floor($diff / 2);

        $rollbackDate->setTimestamp($estimatedTimestamp);
    }
    
    /**
     * @param string $filename
     * @return array
     */
    private function getFileContents(string $filename): array
    {
        return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
