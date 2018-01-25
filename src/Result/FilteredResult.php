<?php

namespace DeployTracker\Result;

use DeployTracker\ORM\Tools\Pagination\Paginator;

class FilteredResult
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
     * @param array $filters
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
