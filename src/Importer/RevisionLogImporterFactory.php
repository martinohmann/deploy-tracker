<?php

namespace DeployTracker\Importer;

use DeployTracker\Exception\RevisionLogImporterNotFoundException;

class RevisionLogImporterFactory
{
    /**
     * @var array
     */
    private $importers = [];

    /**
     * @param string $type
     * @return RevisionLogImporterInterface
     */
    public function create(string $type): RevisionLogImporterInterface
    {
        if (!$this->hasImporter($type)) {
            throw new RevisionLogImporterNotFoundException(sprintf(
                'No importer found for type "%s".',
                $type
            ));
        }

        return $this->importers[$type];
    }

    /**
     * @param string $type
     * @return bool
     */
    public function hasImporter(string $type): bool
    {
        return isset($this->importers[$type]);
    }

    /**
     * @param string $type
     * @param RevisionLogImporterInterface $importer
     * @return void
     */
    public function addImporter(string $type, RevisionLogImporterInterface $importer)
    {
        if ($this->hasImporter($type)) {
            throw new \LogicException(sprintf(
                'Importer for type "%s" already registered.',
                $type
            ));
        }

        $this->importers[$type] = $importer;
    }
}
