<?php

namespace DeployTracker\Importer;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\RevisionLog;

interface RevisionLogImporterInterface
{
    /**
     * @param RevisionLog $revisionLog
     * @return void
     */
    public function import(RevisionLog $revisionLog);
}
