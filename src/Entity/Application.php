<?php

namespace Lesara\DeployTracker\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="application")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Lesara\DeployTracker\Repository\ApplicationRepository")
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
     * @ORM\OneToMany(targetEntity="Lesara\DeployTracker\Entity\Deployment", mappedBy="application")
     *
     * @var ArrayCollection
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
     * @param ArrayCollection $deployments
     * @return self
     */
    public function setDeployments(ArrayCollection $deployments): self
    {
        $this->deployments = $deployments;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getDeployments(): ArrayCollection
    {
        return $this->deployments;
    }
}
