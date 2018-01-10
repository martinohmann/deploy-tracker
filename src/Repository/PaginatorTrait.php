<?php

namespace DeployTracker\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

        $paginator = new Paginator($query, $fetchJoinCollection);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }

    /**
     * @param Paginator $paginator
     * @param ItemsPerPageAwareInterface $repository
     * @return int
     */
    public function getMaxPage(Paginator $paginator): int
    {
        return ceil($paginator->count() / $this->getItemsPerPage());
    }
}
