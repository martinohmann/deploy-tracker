<?php

namespace DeployTracker\Processor;

use DeployTracker\Exception\RevisionLogProcessorNotFoundException;

class RevisionLogProcessorFactory
{
    /**
     * @var array
     */
    private $processors = [];

    /**
     * @param string $type
     * @return RevisionLogProcessorInterface
     */
    public function create(string $type): RevisionLogProcessorInterface
    {
        if (!$this->hasProcessor($type)) {
            throw new RevisionLogProcessorNotFoundException(sprintf(
                'No processor found for type "%s".',
                $type
            ));
        }

        return $this->processors[$type];
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasProcessor(string $type): bool
    {
        return isset($this->processors[$type]);
    }

    /**
     * @param string $type
     * @param RevisionLogProcessorInterface $processor
     * @return void
     */
    public function addProcessor(string $type, RevisionLogProcessorInterface $processor)
    {
        if ($this->hasProcessor($type)) {
            throw new \LogicException(sprintf(
                'Processor for type "%s" already registered.',
                $type
            ));
        }

        $this->processors[$type] = $processor;
    }
}
