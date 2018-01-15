<?php

namespace DeployTracker\Parser;

use DeployTracker\Entity\Deployment;
use DeployTracker\Exception\RevisionLogParseException;

class CapistranoRevisionLogParser implements RevisionLogParserInterface
{
    const PATTERN_ROLLBACK = '/^(.+) rolled back to release (\d{14})$/';
    const PATTERN_RELEASE = '/^Branch (.+) \(at ([0-9a-f]+)\) deployed as release (\d{14}) by (.+)$/';
    const RELEASE_DATE_FMT = 'YmdHis';

    /**
     * {@inheritdoc}
     */
    public function parseLine(string $line): Deployment
    {
        $matches = [];

        if ($this->matchRelease($line, $matches)) {
            return $this->createRelease($matches);
        }
        
        if ($this->matchRollback($line, $matches)) {
            return $this->createRollback($matches);
        }

        throw new RevisionLogParseException(sprintf(
            'Unable to parse line "%s".',
            $line
        ));
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
        return (new Deployment())
            ->setBranch($matches[1])
            ->setCommitHash($matches[2])
            ->setDeployDate($this->parseReleaseDate($matches[3]))
            ->setDeployer($matches[4])
            ->markAsSuccess();
    }

    /**
     * @param array $matches
     * @return Deployment
     */
    private function createRollback(array $matches): Deployment
    {
        return (new Deployment())
            ->setDeployer($matches[1])
            ->markAsRollback();
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
