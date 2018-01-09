<?php

namespace DeployTracker\Repository;

use DeployTracker\Entity\Deployment;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use DeployTracker\Entity\Application;

class DeploymentRepository extends EntityRepository implements PaginatorInterface, FilterableInterface
{
    use PaginatorTrait;
    use FilterableTrait;

    const ITEMS_PER_PAGE = 50;
    const AVAILABLE_FILTERS = ['deployer', 'stage', 'status'];

    /**
     * @param int $page
     * @param array $filters
     * @return Paginator
     */
    public function findAll(int $page = 1, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('d');

        $this->addDefaultOrderBy($qb);
        $this->addFilters($qb, $filters);

        return $this->paginate($qb->getQuery(), $page, $this->getItemsPerPage());
    }

    /**
     * @param Application $application
     * @param int $page
     * @param array $filters
     * @return Paginator
     */
    public function findAllForApplication(Application $application, int $page = 1, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.application = :application_id')
            ->setParameter('application_id', $application->getId());

        $this->addDefaultOrderBy($qb);
        $this->addFilters($qb, $filters);

        return $this->paginate($qb->getQuery(), $page, $this->getItemsPerPage());
    }

    /**
     * @param int $page
     * @param array $filters
     * @return Paginator
     */
    public function findMostRecent(int $page = 1, array $filters = []): Paginator
    {
        // we want to find the most recent
        // deployment per application and stage
        $subQuery = $this->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->addGroupBy('s.application')
            ->addGroupBy('s.stage');

        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->where($subQuery->expr()->in('d.id', $subQuery->getDQL()));
        
        $this->addDefaultOrderBy($qb);
        $this->addFilters($qb, $filters);

        return $this->paginate($qb->getQuery(), $page, $this->getItemsPerPage());
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
        $qb = $this->createQueryBuilder('d')
            ->where('d.status = :status')
            ->setParameter('status', $status)
            ->setMaxResults($limit);

        $this->addDefaultOrderBy($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $searchQuery
     * @param int $page
     * @param array $filter
     * @return Paginator
     */
    public function search(string $searchQuery, int $page = 1, array $filters = []): Paginator
    {
        $qb = $this->createQueryBuilder('d')
            ->join('d.application', 'a')
            ->where('d.stage LIKE :search_query')
            ->orWhere('d.branch LIKE :search_query')
            ->orWhere('d.commitHash LIKE :search_query')
            ->orWhere('d.deployer LIKE :search_query')
            ->orWhere('a.name LIKE :search_query')
            ->setParameter('search_query', '%' . $searchQuery . '%');

        $this->addDefaultOrderBy($qb);
        $this->addFilters($qb, $filters);

        return $this->paginate($qb->getQuery(), $page, $this->getItemsPerPage());
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

    /**
     * @param Collection $deployments
     * @return void
     */
    public function removeCollection(Collection $deployments)
    {
        $em = $this->getEntityManager();

        foreach ($deployments as $deployment) {
            $em->remove($deployment);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): int
    {
        return self::ITEMS_PER_PAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableFilters(): array
    {
        return self::AVAILABLE_FILTERS;
    }

    /**
     * @param QueryBuilder $qb
     * @return void
     */
    private function addDefaultOrderBy(QueryBuilder $qb)
    {
        $qb->addOrderBy('d.deployDate', 'DESC')
            ->addOrderBy('d.id', 'DESC');
    }

    /**
     * @param QueryBuilder $qb
     * @param array $filters
     * @return void
     */
    private function addFilters(QueryBuilder $qb, array $filters)
    {
        foreach ($filters as $key => $value) {
            $qb->andWhere(sprintf('d.%s = :%s', $key, $key))
                ->setParameter($key, $value);
        }
    }
}
