<?php

namespace DeployTracker\Tests\Entity;

use PHPUnit\Framework\TestCase;
use DeployTracker\Entity\Application;
use Doctrine\Common\Collections\ArrayCollection;
use DeployTracker\Entity\Deployment;
use Doctrine\Common\Collections\Collection;

class ApplicationTest extends TestCase
{
    /**
     * @test
     */
    public function shouldFilterDeploymentsByStatus()
    {
        $application = new Application();

        $collection = new ArrayCollection();

        $this->addDeploymentsToCollection($collection, 3, Deployment::STATUS_SUCCESS);
        $this->addDeploymentsToCollection($collection, 2, Deployment::STATUS_ROLLBACK);
        $this->addDeploymentsToCollection($collection, 1, Deployment::STATUS_FAILED);

        $application->setDeployments($collection);

        self::assertCount(3, $application->getSuccessfulDeployments());
        self::assertCount(2, $application->getRollbacks());
        self::assertCount(1, $application->getFailedDeployments());
    }

    /**
     * @param Collection $collection
     * @param int $count
     * @param string $status
     * @return void
     */
    private function addDeploymentsToCollection(Collection $collection, int $count, string $status)
    {
        for ($i = 0; $i < $count; $i++) {
            $deployment = (new Deployment())->setStatus($status);
            $collection->add($deployment);
        }
    }
}
