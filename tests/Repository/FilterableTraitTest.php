<?php

namespace DeployTracker\Tests\Repository;

use PHPUnit\Framework\TestCase;
use DeployTracker\Repository\FilterableTrait;
use Symfony\Component\HttpFoundation\Request;

class FilterableTraitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldCollectFiltersFromQueryStringIfPresent()
    {
        $request = new Request();
        $request->query->set('somefilter', 'somevalue');

        $trait = $this->getMockForTrait(FilterableTrait::class);

        $trait->expects($this->any())
            ->method('getAvailableFilters')
            ->will($this->returnValue(['somefilter', 'someotherfilter']));

        self::assertSame(['somefilter' => 'somevalue'], $trait->getFiltersFromRequest($request));
    }
}
