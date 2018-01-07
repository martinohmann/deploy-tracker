<?php

namespace DeployTracker\Controller;

use Symfony\Component\HttpFoundation\Request;

trait FilterAwareTrait
{
    /**
     * @param Request $request
     * @param array $allowedFilters
     * @return array
     */
    protected function getFilters(Request $request, array $allowedFilters): array
    {
        $filters = [];

        foreach ($allowedFilters as $filter) {
            if ($request->query->has($filter)) {
                $filters[$filter] = $request->query->get($filter);
            }
        }

        return $filters;
    }
}
