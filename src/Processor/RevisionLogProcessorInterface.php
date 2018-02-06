<?php

namespace DeployTracker\Processor;

use DeployTracker\Entity\RevisionLog;

interface RevisionLogProcessorInterface
{
    /**
     * @param RevisionLog $revisionLog
     * @return void
     */
    public function process(RevisionLog $revisionLog);
}
