<?php

namespace DeployTracker\Repository;

use Doctrine\ORM\EntityRepository;
use DeployTracker\Entity\Application;

class ApplicationRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findAll(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
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
}
