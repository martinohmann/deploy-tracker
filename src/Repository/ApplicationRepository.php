<?php

namespace DeployTracker\Repository;

use Doctrine\ORM\EntityRepository;
use DeployTracker\Entity\Application;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
            ->orderBy('a.name', 'ASC')
            ->getQuery();

        return $this->paginate($query, $page, self::ITEMS_PER_PAGE);
    }

    /**
     * @param Application $application
     * @return void
     */
    public function save(Application $application)
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
}
