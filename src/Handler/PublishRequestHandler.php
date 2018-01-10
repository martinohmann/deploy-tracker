<?php

namespace DeployTracker\Handler;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use DeployTracker\Entity\PublishRequest;
use DeployTracker\Exception\PublishRequestException;
use DeployTracker\Repository\ApplicationRepository;
use DeployTracker\Repository\DeploymentRepository;

class PublishRequestHandler
{
    /**
     * @var ApplicationRepository
     */
    private $applicationRepository;

    /**
     * @var DeploymentRepository
     */
    private $deploymentRepository;

    /**
     * @param ApplicationRepository $applicationRepository
     * @param DeploymentRepository $deploymentRepository
     */
    public function __construct(
        ApplicationRepository $applicationRepository,
        DeploymentRepository $deploymentRepository
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->deploymentRepository = $deploymentRepository;
    }

    /**
     * @param PublishRequest $request
     * @return void
     */
    public function handle(PublishRequest $request)
    {
        $applicationName = $request->getApplication();
        $application = $this->applicationRepository->findOneByName($applicationName);

        if (null === $application) {
            throw new PublishRequestException(sprintf(
                'Application "%s" does not exist.',
                $applicationName
            ));
        }

        $deployDate = (new \DateTime())->setTimestamp($request->getTimestamp());

        $deployment = new Deployment();
        $deployment->setApplication($application)
            ->setStage($request->getStage())
            ->setBranch($request->getBranch())
            ->setCommitHash($request->getCommitHash())
            ->setDeployer($request->getDeployer())
            ->setDeployDate($deployDate)
            ->setStatus($request->getStatus());

        $this->deploymentRepository->persist($deployment);
    }
}
