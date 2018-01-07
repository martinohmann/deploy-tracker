<?php

namespace DeployTracker\Repository;

use DeployTracker\Entity\Deployment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DeploymentRepository extends EntityRepository
{
    const ITEMS_PER_PAGE = 20;

    /**
     * @param int $page
     * @return Paginator
     */
    public function findAll(int $page = 1): Paginator
    {
        $query = $this->createQueryBuilder('d')
            ->orderBy('d.deployDate', 'DESC')
            ->getQuery();

        return $this->paginate($query, $page);
    }

    /**
     * @param int $page
     * @return Paginator
     */
    public function findMostRecent(int $page = 1): Paginator
    {
        // we want to find the most recent
        // deployment per application and stage
        $subQuery = $this->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->addGroupBy('s.application')
            ->addGroupBy('s.stage');

        $query = $this->createQueryBuilder('d')
            ->select('d')
            ->where($subQuery->expr()->in('d.id', $subQuery->getDQL()))
            ->orderBy('d.deployDate', 'DESC')
            ->getQuery();

        return $this->paginate($query, $page);
    }

    /**
     * @param int $id
     * @param int $page
     * @return Paginator
     */
    public function findByApplicationId(int $id, int $page = 1): Paginator
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.application = :application_id')
            ->setParameter('application_id', $id)
            ->orderBy('d.deployDate', 'DESC')
            ->getQuery();

        return $this->paginate($query, $page);
    }

    /**
     * @param string $status
     * @param int $limit
     * @return array
     */
    public function findCountsByStatus(string $status, int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->select([
                'COUNT(d.application) as count',
                'a.id as id',
                'a.name as name',
                'a.projectUrl as projectUrl',
            ])
            ->join(
                'd.application',
                'a',
                Join::WITH,
                "d.application = a.id"
            )
            ->where('d.status = :status')
            ->setParameter('status', $status)
            ->groupBy('d.application')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $status
     * @param int $limit
     * @return array
     */
    public function findByStatus(string $status, int $limit = 5): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.status = :status')
            ->setParameter('status', $status)
            ->orderBy('d.deployDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param Deployment $deployment
     * @return void
     */
    public function save(Deployment $deployment)
    {
        $em = $this->getEntityManager();

        $em->persist($deployment);
        $em->flush();
    }

    /**
     * @param Query $query
     * @param int $page
     * @param int $limit
     * @return Paginator
     */
    public function paginate(
        Query $query,
        int $page = 1,
        int $limit = self::ITEMS_PER_PAGE
    ): Paginator {
        $paginator = new Paginator($query);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        return $paginator;
    }
}
