<?php

namespace DeployTracker\Event;

use DeployTracker\ORM\Tools\Pagination\Paginator;
use Symfony\Component\EventDispatcher\Event;

class PostFindEvent extends Event
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var array
     */
    private $filters;

    /**
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator, array $filters = [])
    {
        $this->paginator = $paginator;
        $this->filters = $filters;
    }

    /**
     * @return Paginator
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
