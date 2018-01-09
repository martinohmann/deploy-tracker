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

class CapistranoRevisionLogImporter implements ImporterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    const PATTERN_ROLLBACK = '/^(.+) rolled back to release (\d{14})$/';
    const PATTERN_RELEASE = '/^Branch (.+) \(at ([0-9a-f]+)\) deployed as release (\d{14}) by (.+)$/';
    const RELEASE_DATE_FMT = 'YmdHis';

    /**
     * @var DeploymentRepository
     */
    private $repository;

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
     * @param LoggerInterface $logger
     */
    public function __construct(DeploymentRepository $repository, LoggerInterface $logger = null)
    {
        $this->repository = $repository;
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
            $matches = [];

            if ($this->matchRelease($line, $matches)) {
                $deployment = $this->createRelease($matches);

                if (null !== $previous && $previous->isRollback()) {
                    $this->processRollback($previous, $deployment);

                    $collection->add($previous);
                }

                $collection->add($deployment);
            } elseif ($this->matchRollback($line, $matches)) {
                if (null === $previous || $previous->isRollback()) {
                    $this->logger->warn(sprintf(
                        'Unable to determine previous deployment for rollback "%s", skipping.',
                        $line
                    ));
                    continue;
                }

                $deployment = $this->createRollback($matches, $previous);
            } else {
                $this->logger->warn(sprintf(
                    'Unable to parse line "%s", skipping.',
                    $line
                ));
                continue;
            }

            $previous = $deployment;
        }

        return $collection;
    }

    /**
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
     * @param string $line
     * @param array $matches
     * @return int
     */
    private function matchRelease(string $line, array &$matches): int
    {
        return preg_match(self::PATTERN_RELEASE, $line, $matches);
    }

    /**
     * @param string $line
     * @param array $matches
     * @return int
     */
    private function matchRollback(string $line, array &$matches): int
    {
        return preg_match(self::PATTERN_ROLLBACK, $line, $matches);
    }

    /**
     * @param array $matches
     * @return Deployment
     */
    private function createRelease(array $matches): Deployment
    {
        $deployment = new Deployment();
        $deployment->setStage($this->stage)
            ->setBranch($matches[1])
            ->setCommitHash($matches[2])
            ->setDeployDate($this->parseReleaseDate($matches[3]))
            ->setDeployer($matches[4])
            ->setApplication($this->application)
            ->setStatus(Deployment::STATUS_SUCCESS);

        return $deployment;
    }

    /**
     * @param array $matches
     * @param Deployment $previous
     * @return Deployment
     */
    private function createRollback(array $matches, Deployment $previous): Deployment
    {
        $rollback = new Deployment();
        $rollback->setStage($this->stage)
            ->setDeployDate(clone $previous->getDeployDate())
            ->setDeployer($matches[1])
            ->setApplication($this->application)
            ->setStatus(Deployment::STATUS_ROLLBACK);

        return $rollback;
    }
    
    /**
     * @param string $filename
     * @return array
     */
    private function getFileContents(string $filename): array
    {
        return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * @param string $dateString
     * @return \DateTime
     */
    private function parseReleaseDate(string $dateString): \DateTime
    {
        return \DateTime::createFromFormat(self::RELEASE_DATE_FMT, $dateString);
    }
}
