<?php

namespace DeployTracker\Tests\Importer;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Entity\RevisionLog;
use DeployTracker\Repository\DeploymentRepository;
use DeployTracker\Importer\RevisionLogImporter;
use DeployTracker\Entity\Application;

class RevisionLogImporterTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImportDeployments()
    {
        $revisionLog = new RevisionLog(new Application(), 'some_stage', 'some_file');
        $deployments = $revisionLog->getDeployments();

        $repositoryMock = Phake::mock(DeploymentRepository::class);

        $importer = new RevisionLogImporter($repositoryMock);
        $importer->import($revisionLog);

        Phake::verify($repositoryMock)->persistCollection($deployments);
    }
}
