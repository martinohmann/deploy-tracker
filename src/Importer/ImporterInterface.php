<?php

namespace DeployTracker\Importer;

interface ImporterInterface
{
    /**
     * @param string $filename
     * @param string $applicationName
     * @param string $stage
     * @return void
     */
    public function import(string $filename, string $applicationName, string $stage);
}
