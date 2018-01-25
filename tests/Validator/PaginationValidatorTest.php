<?php

namespace DeployTracker\Tests\Validator;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Validator\PaginationValidator;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;

class PaginationValidatorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldThrowExceptionIfPageIsGreaterThanMaxPage()
    {
        $paginator = Phake::mock(Paginator::class);

        Phake::when($paginator)->getPage->thenReturn(10);
        Phake::when($paginator)->getMaxPage->thenReturn(2);

        $validator = new PaginationValidator();

        $this->expectException(RequestedPageOutOfBoundsException::class);
        $validator->validate($paginator);
    }

    /**
     * @test
     */
    public function shouldNotThrowExceptionIfPageIsLessThanMaxPage()
    {
        $paginator = Phake::mock(Paginator::class);

        Phake::when($paginator)->getPage->thenReturn(2);
        Phake::when($paginator)->getMaxPage->thenReturn(10);

        $validator = new PaginationValidator();
        $this->assertNull($validator->validate($paginator));
    }
}
