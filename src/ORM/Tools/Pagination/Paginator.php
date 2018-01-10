<?php

namespace DeployTracker\ORM\Tools\Pagination;

use Doctrine\ORM\Tools\Pagination\Paginator as BasePaginator;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class Paginator extends BasePaginator
{
    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $limit;

    /**
     * @param Query $query
     * @param int $page
     * @param int $limit
     * @param bool $fetchJoinCollection
     */
    public function __construct(Query $query, int $page, int $limit, bool $fetchJoinCollection = true)
    {
        $this->page = $page;
        $this->limit = $limit;

        $query->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        parent::__construct($query, $fetchJoinCollection);
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getMaxPage(): int
    {
        return ceil($this->count() / $this->limit);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
