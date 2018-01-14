<?php

namespace DeployTracker\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Parser\RevisionLogParserInterface;
use DeployTracker\Parser\CapistranoRevisionLogParser;
use DeployTracker\Processor\CapistranoRevisionLogProcessor;
use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use DeployTracker\Processor\RevisionLogProcessorInterface;
use Psr\Log\LoggerInterface;

class CapistranoRevisionLogProcessorTest extends TestCase
{
    /**
     * @var RevisionLogParserInterface
     */
    private $parser;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var string
     */
    private $stage;

    /**
     * @var RevisionLogProcessorInterface
     */
    private $processor;

    /**
     * @var array
     */
    private $tmpFiles = [];

    /**
     * @return void
     */
    public function setUp()
    {
        $this->parser = new CapistranoRevisionLogParser();
        $this->application = (new Application())->setName('test_app');
        $this->stage = 'testing';
        $this->processor = new CapistranoRevisionLogProcessor($this->parser, $this->application, $this->stage);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        foreach ($this->tmpFiles as $tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        }
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfFileIsNotProcessible()
    {
        $this->expectException(\RuntimeException::class);
        $this->processor->process('some_nonexisting_file');
    }

    /**
     * @test
     */
    public function shouldSkipUnparsableLinesAndLogError()
    {
        $filename = $this->createTmpFile("some bogus line\n");

        $logger = Phake::mock(LoggerInterface::class);

        $this->processor->setLogger($logger);

        $collection = $this->processor->process($filename);

        self::assertCount(0, $collection);

        Phake::verify($logger)->error;
    }

    /**
     * @test
     */
    public function shouldAddLineToCollection()
    {
        $filename = $this->createTmpFile(
            "Branch master (at deadbeef) deployed as release 20181224123456 by someone\n"
        );

        $collection = $this->processor->process($filename);

        self::assertCount(1, $collection);

        /** @var Deployment $deployment */
        $deployment = $collection->first();

        self::assertSame($this->stage, $deployment->getStage());
        self::assertSame($this->application, $deployment->getApplication());
        self::assertTrue($deployment->isSuccess());
    }

    /**
     * @test
     */
    public function shouldSkipRollbackAndLogWarningIfThereIsNoPreviousDeployment()
    {
        $filename = $this->createTmpFile("someotherguy rolled back to release 20181224012345\n");

        $logger = Phake::mock(LoggerInterface::class);

        $this->processor->setLogger($logger);

        $collection = $this->processor->process($filename);

        self::assertCount(0, $collection);

        Phake::verify($logger)->warning;
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfThereIsANoPreviousDeployment()
    {
        $filename = $this->createTmpFile(implode("\n", [
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $collection = $this->processor->process($filename);

        self::assertCount(1, $collection);

        self::assertTrue($collection[0]->isSuccess());
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfThereIsANoNextDeployment()
    {
        $filename = $this->createTmpFile(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
            'someotherguy rolled back to release 20181224012345',
        ]));

        $collection = $this->processor->process($filename);

        self::assertCount(1, $collection);

        self::assertTrue($collection[0]->isSuccess());
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfPreviousIsRollbackAsWell()
    {
        $filename = $this->createTmpFile(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224123456 by someone',
            'someotherguy rolled back to release 20181224001234',
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $collection = $this->processor->process($filename);

        self::assertCount(3, $collection);

        /** @var Deployment $deployment */
        foreach ($collection as $deployment) {
            self::assertSame($this->stage, $deployment->getStage());
            self::assertSame($this->application, $deployment->getApplication());
        }

        self::assertTrue($collection[0]->isSuccess());
        self::assertTrue($collection[1]->isRollback());
        self::assertTrue($collection[2]->isSuccess());
    }

    /**
     * @test
     */
    public function shouldProcessRollbackIfThereIsAPreviousAndNextDeployment()
    {
        $filename = $this->createTmpFile(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224123456 by someone',
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $collection = $this->processor->process($filename);

        self::assertCount(3, $collection);

        /** @var Deployment $deployment */
        foreach ($collection as $deployment) {
            self::assertSame($this->stage, $deployment->getStage());
            self::assertSame($this->application, $deployment->getApplication());
        }

        self::assertTrue($collection[0]->isSuccess());
        self::assertTrue($collection[1]->isRollback());
        self::assertTrue($collection[2]->isSuccess());
    }

    /**
     * @param string $contents
     * @return string
     */
    private function createTmpFile(string $contents): string
    {
        $this->tmpFiles[] = $tmpFile = tempnam(sys_get_temp_dir(), 'DeployTrackerTest');

        file_put_contents($tmpFile, $contents);

        return $tmpFile;
    }
}
