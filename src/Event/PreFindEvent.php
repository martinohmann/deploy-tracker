<?php

namespace DeployTracker\Event;

use Symfony\Component\EventDispatcher\Event;
use Doctrine\ORM\EntityRepository;

class PreFindEvent extends Event
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var int
     */
    private $page;

    /**
     * @var array
     */
    private $filters;

    /**
     * @param EntityRepository $repository
     * @param int $page
     * @param array $filters
     */
    public function __construct(EntityRepository $repository, int $page = 1, array $filters = [])
    {
        $this->repository = $repository;
        $this->page = $page;
        $this->filters = $filters;
    }

    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository
    {
        return $this->repository;
    }

    /**
     * @param int $page
     * @return self
     */
    public function setPage(int $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param array $filters
     * @return self
     */
    public function setFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
