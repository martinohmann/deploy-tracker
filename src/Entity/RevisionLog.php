<?php

namespace DeployTracker\Entity;

use DeployTracker\Entity\Application;
use DeployTracker\Entity\Deployment;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class RevisionLog
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
     * @var string
     */
    private $filename;

    /**
     * @var Collection
     */
    private $deployments;

    /**
     * @param Application $application
     * @param string $stage
     * @param string $filename
     */
    public function __construct(Application $application, string $stage, string $filename)
    {
        $this->application = $application;
        $this->stage = $stage;
        $this->filename = $filename;
        $this->deployments = new ArrayCollection();
    }

    /**
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * @return string
     */
    public function getStage(): string
    {
        return $this->stage;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return Collection
     */
    public function getDeployments(): Collection
    {
        return $this->deployments;
    }

    /**
     * @param Deployment $deployment
     * @return RevisionLog
     */
    public function addDeployment(Deployment $deployment): RevisionLog
    {
        $this->deployments->add($deployment);

        return $this;
    }
}
