<?php

namespace DeployTracker\Processor;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use DeployTracker\Entity\RevisionLog;
use DeployTracker\Exception\RevisionLogParseException;
use DeployTracker\Parser\CapistranoRevisionLogParser;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;

class CapistranoRevisionLogProcessor implements RevisionLogProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function process(RevisionLog $revisionLog)
    {
        $parser = new CapistranoRevisionLogParser();
        $filename = $revisionLog->getFilename();
        $previous = null;

        foreach ($this->getFileContents($filename) as $line) {
            try {
                $parsed = $parser->parseLine($line);
            } catch (RevisionLogParseException $e) {
                $this->logger->error($e->getMessage());
                continue;
            }

            if ($parsed->isSuccess()) {
                if (null !== $previous && $previous->isRollback()) {
                    $this->processRollback($previous, $parsed);

                    $revisionLog->addDeployment($previous);
                }

                $revisionLog->addDeployment($parsed);
            } elseif ($parsed->isRollback()) {
                if (null === $previous || $previous->isRollback()) {
                    $this->logger->warning(sprintf(
                        'Unable to determine previous deployment for rollback "%s", skipping.',
                        $line
                    ));
                    continue;
                }

                $parsed->setDeployDate(clone $previous->getDeployDate());
            }

            $parsed
                ->setApplication($revisionLog->getApplication())
                ->setStage($revisionLog->getStage());

            $previous = $parsed;
        }
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
        if (!file_exists($filename) || !is_readable($filename)) {
            throw new \RuntimeException(sprintf(
                'File "%s" does not exist or is not readable.',
                $filename
            ));
        }

        return file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
