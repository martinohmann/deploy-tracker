<?php

namespace DeployTracker\Repository;

use DeployTracker\Entity\Deployment;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use DeployTracker\Entity\Application;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Common\Collections\ArrayCollection;

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
    public function findByApplication(Application $application, int $page = 1, array $filters = []): Paginator
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
     * @return ArrayCollection
     */
    public function findLastByStatus(string $status, int $limit = 5): ArrayCollection
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.status = :status')
            ->setParameter('status', $status)
            ->setMaxResults($limit);

        $this->addDefaultOrderBy($qb);

        $query = $qb->getQuery();

        return new ArrayCollection($query->getResult());
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
     * @param int $page
     * @return Paginator
     */
    public function findDeployers(int $page = 1): Paginator
    {
        $query = $this->createQueryBuilder('d')
            ->select([
                'd.deployer as name',
                'COUNT(d.id) as deploymentCount',
                'COUNT(ds.id) as successCount',
                'COUNT(dr.id) as rollbackCount',
                'COUNT(df.id) as failedCount',
                'COUNT(DISTINCT(d.application)) as applicationCount',
                'COUNT(DISTINCT(d.stage)) as stageCount',
                'MAX(d.deployDate) as lastDeployDate',
                'DATE_DIFF(NOW(), MIN(d.deployDate)) as trackedSinceDays',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate)) / 7) as deploymentsPerWeek',
            ])
            ->leftJoin(
                Deployment::class,
                'ds',
                Join::WITH,
                "d.id = ds.id AND ds.status = 'success'"
            )
            ->leftJoin(
                Deployment::class,
                'dr',
                Join::WITH,
                "d.id = dr.id AND dr.status = 'rollback'"
            )
            ->leftJoin(
                Deployment::class,
                'df',
                Join::WITH,
                "d.id = df.id AND df.status = 'failed'"
            )
            ->addGroupBy('d.deployer')
            ->orderBy('d.deployer', 'ASC')
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        return $this->paginate($query, $page, $this->getItemsPerPage());
    }

    /**
     * @param int $limit
     * @return ArrayCollection
     */
    public function findTopDeployers(int $limit = 4): ArrayCollection
    {
        $query = $this->createQueryBuilder('d')
            ->select([
                'd.deployer as name',
                'COUNT(d.id) as deploymentCount',
                'MAX(d.deployDate) as lastDeployDate'
            ])
            ->addGroupBy('d.deployer')
            ->orderBy('deploymentCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery();

        return new ArrayCollection($query->getArrayResult());
    }

    /**
     * @return array
     */
    public function aggregateDeploymentStats(): array
    {
        return $this->createQueryBuilder('d')
            ->select([
                'COUNT(d.id) as total',
                'COUNT(dd.id) as last24h',
                'COUNT(dw.id) as lastWeek',
                'COUNT(dm.id) as lastMonth',
                'COUNT(dy.id) as lastYear',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate))) as avgPerDay',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate)) / 7) as avgPerWeek',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate)) / 30) as avgPerMonth',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate)) / 365) as avgPerYear',
                'COUNT(ds.id) as successful',
                'COUNT(dr.id) as rollbacks',
                'COUNT(df.id) as failed',
                'COUNT(du.id) as unknown',
                '(COUNT(ds.id) / COUNT(d.id) * 100) as successPercentage',
                '(COUNT(dr.id) / COUNT(d.id) * 100) as rollbackPercentage',
                '(COUNT(df.id) / COUNT(d.id) * 100) as failedPercentage',
                '(COUNT(du.id) / COUNT(d.id) * 100) as unknownPercentage',
            ])
            ->leftJoin(
                Deployment::class,
                'dd',
                Join::WITH,
                "d.id = dd.id AND dd.deployDate > :day_ago"
            )
            ->leftJoin(
                Deployment::class,
                'dw',
                Join::WITH,
                "d.id = dw.id AND dw.deployDate > :week_ago"
            )
            ->leftJoin(
                Deployment::class,
                'dm',
                Join::WITH,
                "d.id = dm.id AND dm.deployDate > :month_ago"
            )
            ->leftJoin(
                Deployment::class,
                'dy',
                Join::WITH,
                "d.id = dy.id AND dy.deployDate > :year_ago"
            )
            ->leftJoin(
                Deployment::class,
                'ds',
                Join::WITH,
                "d.id = ds.id AND ds.status = 'success'"
            )
            ->leftJoin(
                Deployment::class,
                'dr',
                Join::WITH,
                "d.id = dr.id AND dr.status = 'rollback'"
            )
            ->leftJoin(
                Deployment::class,
                'df',
                Join::WITH,
                "d.id = df.id AND df.status = 'failed'"
            )
            ->leftJoin(
                Deployment::class,
                'du',
                Join::WITH,
                "d.id = du.id AND (du.status != 'success' AND du.status != 'rollback' AND du.status != 'failed')"
            )
            ->setParameters([
                'day_ago' => (new \DateTime('-1 day'))->format('Y-m-d H:i:s'),
                'week_ago' => (new \DateTime('-1 week'))->format('Y-m-d H:i:s'),
                'month_ago' => (new \DateTime('-1 month'))->format('Y-m-d H:i:s'),
                'year_ago' => (new \DateTime('-1 year'))->format('Y-m-d H:i:s'),
            ])
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_ARRAY)
            ->getSingleResult();
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
