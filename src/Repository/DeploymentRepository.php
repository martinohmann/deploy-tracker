<?php

namespace DeployTracker\Repository;

use DeployTracker\Entity\Deployment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Collection;

class DeploymentRepository extends EntityRepository
{
    use PaginatorTrait;

    const ITEMS_PER_PAGE = 50;

    /**
     * @param int $page
     * @return Paginator
     */
    public function findAll(int $page = 1): Paginator
    {
        $query = $this->createQueryBuilder('d')
            ->addOrderBy('d.id', 'DESC')
            ->addOrderBy('d.deployDate', 'DESC')
            ->getQuery();

        return $this->paginate($query, $page, self::ITEMS_PER_PAGE);
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

        return $this->paginate($query, $page, self::ITEMS_PER_PAGE);
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
            ->addOrderBy('d.id', 'DESC')
            ->addOrderBy('d.deployDate', 'DESC')
            ->getQuery();

        return $this->paginate($query, $page, self::ITEMS_PER_PAGE);
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
            ->setMaxResults($limit)
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
    public function persist(Deployment $deployment)
    {
        $em = $this->getEntityManager();

        $em->persist($deployment);
        $em->flush();
    }

    /**
     * @param Collection $deployments
     * @return void
     */
    public function persistCollection(Collection $deployments)
    {
        $em = $this->getEntityManager();

        foreach ($deployments as $deployment) {
            $em->persist($deployment);
        }

        $em->flush();
    }
}
