<?php

namespace DeployTracker\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DeployTracker\Entity\Deployment;

/**
 * @ORM\Table(name="application")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="DeployTracker\Repository\ApplicationRepository")
 */
class Application
{
    /**
     * @ORM\Column(name="id", type="integer", length=10)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="project_url", type="string", length=255)
     *
     * @var string
     */
    private $projectUrl;

    /**
     * @ORM\OneToMany(targetEntity="DeployTracker\Entity\Deployment", mappedBy="application", cascade={"remove"})
     *
     * @var Collection
     */
    private $deployments;

    public function __construct()
    {
        $this->deployments = new ArrayCollection();
    }

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
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ?string
     */
    public function getName(): ?string
    {
        return $this->name;
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
     * @param Collection $deployments
     * @return self
     */
    public function setDeployments(Collection $deployments): self
    {
        $this->deployments = $deployments;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getDeployments(): Collection
    {
        return $this->deployments;
    }

    /**
     * @return Collection
     */
    public function getDeploymentsByStatus(string $status): Collection
    {
        return $this->deployments->filter(function (Deployment $deployment) use ($status) {
            return $deployment->getStatus() === $status;
        });
    }

    /**
     * @return Collection
     */
    public function getSuccessfulDeployments(): Collection
    {
        return $this->getDeploymentsByStatus(Deployment::STATUS_SUCCESS);
    }

    /**
     * @return Collection
     */
    public function getFailedDeployments(): Collection
    {
        return $this->getDeploymentsByStatus(Deployment::STATUS_FAILED);
    }

    /**
     * @return Collection
     */
    public function getRollbacks(): Collection
    {
        return $this->getDeploymentsByStatus(Deployment::STATUS_ROLLBACK);
    }
}
