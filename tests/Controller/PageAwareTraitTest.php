<?php

namespace DeployTracker\Tests\Controller;

use DeployTracker\Controller\PageAwareTrait;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Tests\TestUtil;
use PHPUnit\Framework\TestCase;
use Phake;
use Symfony\Component\HttpFoundation\Request;

class PageAwareTraitTest extends TestCase
{
    /**
     * @param mixed $given
     * @param mixed $expected
     *
     * @test
     * @dataProvider pageDataProvider
     */
    public function shouldGetPageFromRequest($given, $expected)
    {
        $trait = $this->getMockForTrait(PageAwareTrait::class);

        $request = new Request();
        $request->query->set('page', $given);

        $page = TestUtil::callMethod($trait, 'getPage', [$request]);

        self::assertSame($expected, $page);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenPageIsOutOfBounds()
    {
        $paginator = Phake::mock(Paginator::class);

        Phake::when($paginator)->getPage->thenReturn(10);
        Phake::when($paginator)->getMaxPage->thenReturn(2);

        $trait = $this->getMockForTrait(PageAwareTrait::class);

        $this->expectException(RequestedPageOutOfBoundsException::class);
        TestUtil::callMethod($trait, 'validatePagination', [$paginator]);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfPageIsWithinBounds()
    {
        $paginator = Phake::mock(Paginator::class);

        Phake::when($paginator)->getPage->thenReturn(2);
        Phake::when($paginator)->getMaxPage->thenReturn(10);

        $trait = $this->getMockForTrait(PageAwareTrait::class);

        self::assertNull(TestUtil::callMethod($trait, 'validatePagination', [$paginator]));
    }

    /**
     * @return array
     */
    public function pageDataProvider(): array
    {
        return [
            [10, 10],
            [null, 1],
            [-1, 1],
            ['123', 123],
            ['abc', 1],
            ['true', 1],
        ];
    }
}
