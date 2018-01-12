<?php

namespace DeployTracker\Parser;

use DeployTracker\Entity\Deployment;
use DeployTracker\Exception\RevisionLogParseException;

interface RevisionLogParserInterface
{
    /**
     * @param string $line
     * @return Deployment
     *
     * @throws RevisionLogParseException
     */
    public function parseLine(string $line): Deployment;
}
