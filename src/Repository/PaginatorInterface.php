<?php

namespace DeployTracker\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;

interface PaginatorInterface
{
    /**
     * @param Query $query
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function paginate(Query $query, int $page = 1, int $limit = 10): Paginator;

    /**
     * @param Paginator $paginator
     * @return int
     */
    public function getMaxPage(Paginator $paginator): int;

    /**
     * @return int
     */
    public function getItemsPerPage(): int;
}
