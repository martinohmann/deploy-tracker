<?php

namespace DeployTracker\Tests\Result;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Result\FilteredResult;

class FilteredResultTest extends TestCase
{
    /**
     * @var Paginator
     */
    private $paginatorMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->paginatorMock = Phake::mock(Paginator::class);
    }

    /**
     * @test
     */
    public function shouldReturnPaginator()
    {
        $result = new FilteredResult($this->paginatorMock);

        $this->assertSame($this->paginatorMock, $result->getPaginator());
    }

    /**
     * @test
     */
    public function shouldReturnFilters()
    {
        $filters = ['somfilter' => 'somevalue'];

        $result = new FilteredResult($this->paginatorMock, $filters);

        $this->assertSame($filters, $result->getFilters());
    }
}
