<?php

namespace Lesara\DeployTracker\Repository;

use Doctrine\ORM\EntityRepository;
use Lesara\DeployTracker\Entity\Deployment;

class DeploymentRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->findBy([], ['deployDate' => 'DESC']);
    }

    /**
     * @return array
     */
    public function findMostRecent(): array
    {
        // we want to find the most recent
        // deployment per application and stage
        $subQuery = $this->createQueryBuilder('s')
            ->select('MAX(s.id)')
            ->addGroupBy('s.application')
            ->addGroupBy('s.stage');

        return $this->createQueryBuilder('d')
            ->select('d')
            ->where($subQuery->expr()->in('d.id', $subQuery->getDQL()))
            ->orderBy('d.deployDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $id
     * @return array
     */
    public function findByApplicationId(int $id): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.application = :application_id')
            ->setParameter('application_id', $id)
            ->orderBy('d.deployDate', 'DESC')
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
}
