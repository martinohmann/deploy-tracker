<?php

namespace DeployTracker\Tests\Handler;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\PublishRequest;
use DeployTracker\Exception\PublishRequestException;
use DeployTracker\Handler\PublishRequestHandler;
use DeployTracker\Repository\ApplicationRepository;
use DeployTracker\Repository\DeploymentRepository;
use PHPUnit\Framework\TestCase;
use Phake;

class PublishRequestHandlerTest extends TestCase
{
    /**
     * @var DeploymentRepository
     */
    private $deploymentRepositoryMock;

    /**
     * @var ApplicationRepository
     */
    private $applicationRepositoryMock;

    /**
     * @var PublishRequestHandler
     */
    private $handler;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->deploymentRepositoryMock = Phake::mock(DeploymentRepository::class);
        $this->applicationRepositoryMock = Phake::mock(ApplicationRepository::class);

        $this->handler = new PublishRequestHandler(
            $this->applicationRepositoryMock,
            $this->deploymentRepositoryMock
        );
    }

    /**
     * @test
     */
    public function shouldThrowExceptionIfRequestContainsNonExistentApplication()
    {
        $request = new PublishRequest();
        $this->expectException(PublishRequestException::class);
        $this->handler->handle($request);
    }

    /**
     * @test
     */
    public function shouldPersistNewDeploymentIfApplicationIsPresent()
    {
        $request = (new PublishRequest())
            ->setApplication('someapp')
            ->setStage('testing')
            ->setDeployer('somedev')
            ->setStatus('success');

        $application = (new Application())
            ->setName('someapp');

        Phake::when($this->applicationRepositoryMock)
            ->findOneByName('someapp')
            ->thenReturn($application);

        $this->handler->handle($request);

        Phake::verify($this->deploymentRepositoryMock)
            ->persist(Phake::capture($deployment));

        self::assertSame($application, $deployment->getApplication());
        self::assertTrue($deployment->isSuccess());
    }
}
