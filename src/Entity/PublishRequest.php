<?php

namespace DeployTracker\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PublishRequest
{
    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $application;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $projectUrl;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $stage;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $branch;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $commitHash;

    /**
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $deployer;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $status;

    /**
     * @param string $application
     * @return self
     */
    public function setApplication(string $application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getApplication(): ?string
    {
        return $this->application;
    }

    /**
     * @param string $projectUrl
     * @return self
     */
    public function setProjectUrl(string $projectUrl): self
    {
        $this->projectUrl = $projectUrl;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getProjectUrl(): ?string
    {
        return $this->projectUrl;
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
     * @param int $timestamp
     * @return self
     */
    public function setTimestamp(int $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return ?int
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
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
}
