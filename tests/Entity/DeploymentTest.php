<?php

namespace DeployTracker\Tests\Entity;

use PHPUnit\Framework\TestCase;
use DeployTracker\Entity\Deployment;
use DeployTracker\Entity\Application;

class DeploymentTest extends TestCase
{
    const PROJECT_URL = 'https://github.com/martinohmann/deploy-tracker';

    /**
     * @test
     */
    public function shouldReportCorrectStatus()
    {
        $deployment = new Deployment();

        self::assertTrue($deployment->hasUnknownStatus());

        $deployment->markAsSuccess();
        self::assertTrue($deployment->isSuccess());

        $deployment->markAsRollback();
        self::assertTrue($deployment->isRollback());

        $deployment->markAsFailed();
        self::assertTrue($deployment->isFailed());
    }

    /**
     * @test
     */
    public function shouldBuildBranchUrlIfBranchAndApplicationAreSet()
    {
        $deployment = new Deployment();

        self::assertNull($deployment->getBranchUrl());

        $deployment->setBranch('develop');
        self::assertNull($deployment->getBranchUrl());

        $application = (new Application())
            ->setProjectUrl(self::PROJECT_URL);
        $deployment->setApplication($application);

        $expected = sprintf('%s/tree/develop', self::PROJECT_URL);
        self::assertSame($expected, $deployment->getBranchUrl());
    }

    /**
     * @test
     */
    public function shouldBuildCommitUrlIfCommitHashAndApplicationAreSet()
    {
        $deployment = new Deployment();

        self::assertNull($deployment->getCommitUrl());

        $deployment->setCommitHash('deadbeef');
        self::assertNull($deployment->getCommitUrl());

        $application = (new Application())
            ->setProjectUrl(self::PROJECT_URL);
        $deployment->setApplication($application);

        $expected = sprintf('%s/commit/deadbeef', self::PROJECT_URL);
        self::assertSame($expected, $deployment->getCommitUrl());
    }
}
