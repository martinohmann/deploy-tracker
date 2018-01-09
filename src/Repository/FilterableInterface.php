<?php

namespace DeployTracker\Repository;

use Symfony\Component\HttpFoundation\Request;

interface FilterableInterface
{
    /**
     * @return array
     */
    public function getAvailableFilters(): array;

    /**
     * @param Request $request
     * @return array
     */
    public function getFiltersFromRequest(Request $request): array;
}
