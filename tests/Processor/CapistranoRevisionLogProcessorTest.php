<?php

namespace DeployTracker\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Processor\CapistranoRevisionLogProcessor;
use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use DeployTracker\Processor\RevisionLogProcessorInterface;
use Psr\Log\LoggerInterface;
use DeployTracker\Entity\RevisionLog;

class CapistranoRevisionLogProcessorTest extends TestCase
{
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
        $this->application = (new Application())->setName('test_app');
        $this->stage = 'testing';
        $this->processor = new CapistranoRevisionLogProcessor();
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
        $revisionLog = new RevisionLog($this->application, $this->stage, 'nonexistent_file');
        $this->processor->process($revisionLog);
    }

    /**
     * @test
     */
    public function shouldSkipUnparsableLinesAndLogError()
    {
        $revisionLog = $this->createRevisionLogWithContents("some bogus line\n");

        $logger = Phake::mock(LoggerInterface::class);

        $this->processor->setLogger($logger);

        $this->processor->process($revisionLog);

        self::assertCount(0, $revisionLog->getDeployments());

        Phake::verify($logger)->error;
    }

    /**
     * @test
     */
    public function shouldAddLineToCollection()
    {
        $revisionLog = $this->createRevisionLogWithContents(
            "Branch master (at deadbeef) deployed as release 20181224123456 by someone\n"
        );

        $this->processor->process($revisionLog);

        $collection = $revisionLog->getDeployments();

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
        $revisionLog = $this->createRevisionLogWithContents("someotherguy rolled back to release 20181224012345\n");

        $logger = Phake::mock(LoggerInterface::class);

        $this->processor->setLogger($logger);

        $this->processor->process($revisionLog);

        self::assertCount(0, $revisionLog->getDeployments());

        Phake::verify($logger)->warning;
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfThereIsANoPreviousDeployment()
    {
        $revisionLog = $this->createRevisionLogWithContents(implode("\n", [
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $this->processor->process($revisionLog);

        $collection = $revisionLog->getDeployments();

        self::assertCount(1, $collection);

        self::assertTrue($collection[0]->isSuccess());
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfThereIsANoNextDeployment()
    {
        $revisionLog = $this->createRevisionLogWithContents(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
            'someotherguy rolled back to release 20181224012345',
        ]));

        $this->processor->process($revisionLog);

        $collection = $revisionLog->getDeployments();

        self::assertCount(1, $collection);

        self::assertTrue($collection[0]->isSuccess());
    }

    /**
     * @test
     */
    public function shouldNotAddRollbackIfPreviousIsRollbackAsWell()
    {
        $revisionLog = $this->createRevisionLogWithContents(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224123456 by someone',
            'someotherguy rolled back to release 20181224001234',
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $this->processor->process($revisionLog);

        $collection = $revisionLog->getDeployments();

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
        $revisionLog = $this->createRevisionLogWithContents(implode("\n", [
            'Branch master (at deadbeef) deployed as release 20181224123456 by someone',
            'someotherguy rolled back to release 20181224012345',
            'Branch master (at deadbeef) deployed as release 20181224133456 by someone',
        ]));

        $this->processor->process($revisionLog);

        $collection = $revisionLog->getDeployments();

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

    /**
     * @param string $contents
     * @return RevisionLog
     */
    private function createRevisionLogWithContents(string $contents): RevisionLog
    {
        return new RevisionLog($this->application, $this->stage, $this->createTmpFile($contents));
    }
}
