<?php

namespace DeployTracker\Entity;

use DeployTracker\Entity\Application;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="deployment")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="DeployTracker\Repository\DeploymentRepository")
 */
class Deployment
{
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_ROLLBACK = 'rollback';

    /**
     * @ORM\Column(name="id", type="integer", length=10)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="stage", type="string", length=255)
     *
     * @var string
     */
    private $stage;

    /**
     * @ORM\Column(name="branch", type="string", length=255)
     *
     * @var string
     */
    private $branch;

    /**
     * @ORM\Column(name="commit_hash", type="string", length=255)
     *
     * @var string
     */
    private $commitHash;

    /**
     * @ORM\Column(name="deployer", type="string", length=255)
     *
     * @var string
     */
    private $deployer;

    /**
     * @ORM\Column(name="deploy_date", type="datetime")
     *
     * @var \DateTime
     */
    private $deployDate;

    /**
     * @ORM\ManyToOne(targetEntity="DeployTracker\Entity\Application", inversedBy="deployments")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id")
     *
     * @var Application
     */
    private $application;

    /**
     * @ORM\Column(name="status", type="string", length=32)
     *
     * @var string
     */
    private $status;

    /**
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $stage
     * @return self
     */
    public function setStage(string $stage): self
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getStage(): ?string
    {
        return $this->stage;
    }

    /**
     * @param string $branch
     * @return self
     */
    public function setBranch(string $branch): self
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getBranch(): ?string
    {
        return $this->branch;
    }

    /**
     * @param string $commitHash
     * @return self
     */
    public function setCommitHash(string $commitHash): self
    {
        $this->commitHash = $commitHash;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getCommitHash(): ?string
    {
        return $this->commitHash;
    }

    /**
     * @param string $deployer
     * @return self
     */
    public function setDeployer(string $deployer): self
    {
        $this->deployer = $deployer;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getDeployer(): ?string
    {
        return $this->deployer;
    }

    /**
     * @param \DateTime $deployDate
     * @return self
     */
    public function setDeployDate(\DateTime $deployDate): self
    {
        $this->deployDate = $deployDate;

        return $this;
    }

    /**
     * @return ?\DateTime
     */
    public function getDeployDate(): ?\DateTime
    {
        return $this->deployDate;
    }

    /**
     * @param Application $application
     * @return self
     */
    public function setApplication(Application $application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return ?Application
     */
    public function getApplication(): ?Application
    {
        return $this->application;
    }

    /**
     * @param string $status
     * @return self
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getBranchUrl(): string
    {
        return sprintf('%s/tree/%s', $this->application->getProjectUrl(), $this->branch);
    }

    /**
     * @return string
     */
    public function getCommitUrl(): string
    {
        return sprintf('%s/commit/%s', $this->application->getProjectUrl(), $this->commitHash);
    }
}
