<?php

namespace DeployTracker\Processor;

use Doctrine\Common\Collections\ArrayCollection;

interface RevisionLogProcessorInterface
{
    /**
     * @param string $filename
     * @return ArrayCollection
     */
    public function process(string $filename): ArrayCollection;
}
