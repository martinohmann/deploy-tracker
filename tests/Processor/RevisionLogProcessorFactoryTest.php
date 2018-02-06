<?php

namespace DeployTracker\Tests\Processor;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Processor\RevisionLogProcessorFactory;
use DeployTracker\Processor\RevisionLogProcessorInterface;
use DeployTracker\Exception\RevisionLogProcessorNotFoundException;

class RevisionLogProcessorFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionIfThereIsNoProcessorForType()
    {
        $factory = new RevisionLogProcessorFactory();

        $this->expectException(RevisionLogProcessorNotFoundException::class);
        $factory->create('some_type');
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenTryingToRegisterProcessorForTypeTwice()
    {
        $factory = new RevisionLogProcessorFactory();
        $processor = Phake::mock(RevisionLogProcessorInterface::class);
        $otherProcessor = Phake::mock(RevisionLogProcessorInterface::class);

        $factory->addProcessor('some_type', $processor);

        $this->expectException(\LogicException::class);
        $factory->addProcessor('some_type', $otherProcessor);
    }

    /**
     * @test
     */
    public function shouldReturnRegisteredProcessorForType()
    {
        $factory = new RevisionLogProcessorFactory();
        $processor = Phake::mock(RevisionLogProcessorInterface::class);

        $factory->addProcessor('some_type', $processor);

        self::assertSame($processor, $factory->create('some_type'));
    }
}
