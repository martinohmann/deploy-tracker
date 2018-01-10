<?php

namespace DeployTracker\Repository;

use DeployTracker\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;

trait PaginatorTrait
{
    /**
     * @param Query $query
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function paginate(Query $query, int $page = 1, int $limit = 10): Paginator
    {
        $fetchJoinCollection = $query->getHydrationMode() === AbstractQuery::HYDRATE_OBJECT;

        return new Paginator($query, $page, $limit, $fetchJoinCollection);
    }
}
