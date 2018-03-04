<?php

namespace DeployTracker\Tests\ORM\Tools\Pagination;

use PHPUnit\Framework\TestCase;
use Phake;
use Doctrine\ORM\Query;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Configuration;

class PaginatorTest extends TestCase
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @return void
     */
    public function setUp()
    {
        $em = Phake::mock(EntityManagerInterface::class);

        Phake::when($em)->getConfiguration()->thenReturn(new Configuration());
        $this->query = new Query($em);
    }

    /**
     * @test
     */
    public function shouldSetLimitsOnQuery()
    {
        $page = 2;
        $limit = 10;

        new Paginator($this->query, $page, $limit);

        $this->assertSame($limit * ($page - 1), $this->query->getFirstResult());
        $this->assertSame($limit, $this->query->getMaxResults());
    }

    /**
     * @test
     */
    public function shouldReturnLimitAndPage()
    {
        $page = 2;
        $limit = 10;

        $paginator = new Paginator($this->query, $page, $limit);

        $this->assertSame($page, $paginator->getPage());
        $this->assertSame($limit, $paginator->getLimit());
    }

    /**
     * @test
     */
    public function shouldCalculateMaxPage()
    {
        $page = 2;
        $limit = 10;
        $count = 42;

        $paginator = Phake::partialMock(Paginator::class, $this->query, $page, $limit);

        Phake::when($paginator)->count()->thenReturn($count);

        $this->assertEquals(ceil($count / $limit), $paginator->getMaxPage());
    }
}
