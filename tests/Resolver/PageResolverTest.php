<?php

namespace DeployTracker\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use DeployTracker\Resolver\PageResolver;
use Symfony\Component\HttpFoundation\Request;

class PageResolverTest extends TestCase
{
    /**
     * @test
     * @dataProvider pageDataProvider
     */
    public function shouldRetrieveCorrectPageFromQueryString($given, $expected)
    {
        $request = new Request();
        $request->query->set('page', $given);

        $resolver = new PageResolver('page');
        $page = $resolver->resolve($request);

        $this->assertSame($expected, $page);
    }

    /**
     * @test
     */
    public function shouldFallbackToDefaultPageIfNotPresentInQueryString()
    {
        $resolver = new PageResolver('page', 42);
        $page = $resolver->resolve(new Request());

        $this->assertSame(42, $page);
    }

    /**
     * @return array
     */
    public function pageDataProvider(): array
    {
        return [
            [null, 1],
            ['2', 2],
            [10, 10],
            ['0.5', 1],
            ['1e5', 100000],
            [0, 1],
            [-1, 1],
            ['-100', 1],
            [-5.5, 1],
            [7.1, 7],
            [7.5, 7],
            [7.9, 7],
        ];
    }
}
