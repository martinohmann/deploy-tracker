<?php

namespace DeployTracker\Repository;

use DeployTracker\Entity\Application;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\AbstractQuery;
use DeployTracker\Entity\Deployment;

class ApplicationRepository extends EntityRepository
{
    use PaginatorTrait;

    const ITEMS_PER_PAGE = 50;

    /**
     * @param int $page
     * @return Paginator
     */
    public function findAll(int $page = 1): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->select([
                'a.id as id',
                'a.name as name',
                'a.projectUrl as projectUrl',
                'COUNT(d.id) as deploymentCount',
                'COUNT(ds.id) as successCount',
                'COUNT(dr.id) as rollbackCount',
                'COUNT(df.id) as failedCount',
                'COUNT(DISTINCT(d.stage)) as stageCount',
                'MAX(d.deployDate) as lastDeployDate',
                'DATE_DIFF(NOW(), MIN(d.deployDate)) as trackedSinceDays',
                'COUNT(d.id) / (DATE_DIFF(MAX(d.deployDate), MIN(d.deployDate)) / 7) as deploymentsPerWeek',
            ])
            ->leftJoin(
                Deployment::class,
                'd',
                Join::WITH,
                "a = d.application"
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
            ->addGroupBy('a')
            ->orderBy('name', 'ASC')
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);

        return $this->paginate($query, $page, $this->getItemsPerPage());
    }

    /**
     * @param Application $application
     * @return void
     */
    public function persist(Application $application)
    {
        $em = $this->getEntityManager();

        $em->persist($application);
        $em->flush();
    }

    /**
     * @param Application $application
     * @return void
     */
    public function remove(Application $application)
    {
        $em = $this->getEntityManager();

        $em->remove($application);
        $em->flush();
    }

    /**
     * @return int
     */
    public function getItemsPerPage(): int
    {
        return self::ITEMS_PER_PAGE;
    }
}
