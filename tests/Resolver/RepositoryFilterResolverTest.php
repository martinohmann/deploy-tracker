<?php

namespace DeployTracker\Tests\Resolver;

use PHPUnit\Framework\TestCase;
use Phake;
use DeployTracker\Repository\FilterableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use DeployTracker\Resolver\RepositoryFilterResolver;
use DeployTracker\Tests\TestUtil;

class RepositoryFilterResolverTest extends TestCase
{
    /**
     * @test
     * @dataProvider filterDataProvider
     */
    public function shouldRetrieveAllowedFiltersFromRequest($availFilters, $requestParams, $expected)
    {
        $request = new Request();
        $request->query = new ParameterBag($requestParams);

        $repository = Phake::mock(FilterableInterface::class);

        Phake::when($repository)->getAvailableFilters->thenReturn($availFilters);

        $resolver = new RepositoryFilterResolver();
        $filters = $resolver->resolve($repository, $request);

        TestUtil::assertArraysContainSameElements($this, $expected, $filters);
    }

    /**
     * @return array
     */
    public function filterDataProvider(): array
    {
        return [
            [
                ['one'],
                [],
                []
            ],
            [
                ['one'],
                ['one' => 'two'],
                ['one' => 'two'],
            ],
            [
                ['one'],
                ['one' => 'two', 'two' => 'three'],
                ['one' => 'two'],
            ],
            [
                ['one', 'two'],
                ['one' => 'two', 'two' => 'three'],
                ['one' => 'two', 'two' => 'three'],
            ],
            [
                ['two'],
                ['one' => 'two', 'two' => 'three'],
                ['two' => 'three'],
            ]
        ];
    }
}
