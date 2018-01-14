<?php

namespace DeployTracker\Tests\Importer;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Importer\RevisionLogImporterFactory;
use DeployTracker\Importer\RevisionLogImporterInterface;
use DeployTracker\Exception\RevisionLogImporterNotFoundException;

class RevisionLogImporterFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionIfThereIsNoImporterForType()
    {
        $factory = new RevisionLogImporterFactory();

        $this->expectException(RevisionLogImporterNotFoundException::class);
        $factory->create('some_type');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenTryingToRegisterImporterForTypeTwice()
    {
        $factory = new RevisionLogImporterFactory();
        $importer =  Phake::mock(RevisionLogImporterInterface::class);
        $otherImporter =  Phake::mock(RevisionLogImporterInterface::class);

        $factory->addImporter('some_type', $importer);

        $this->expectException(\LogicException::class);
        $factory->addImporter('some_type', $otherImporter);
    }

    /**
     * @test
     */
    public function shouldReturnRegisteredImporterForType()
    {
        $factory = new RevisionLogImporterFactory();
        $importer =  Phake::mock(RevisionLogImporterInterface::class);

        $factory->addImporter('some_type', $importer);

        self::assertSame($importer, $factory->create('some_type'));
    }
}
