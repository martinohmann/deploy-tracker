<?php

namespace DeployTracker\Repository;

use Symfony\Component\HttpFoundation\Request;

trait FilterableTrait
{
    /**
     * @param Request $request
     * @return array
     */
    public function getFiltersFromRequest(Request $request): array
    {
        $filters = [];

        foreach ($this->getAvailableFilters() as $filter) {
            if ($request->query->has($filter)) {
                $filters[$filter] = $request->query->get($filter);
            }
        }

        return $filters;
    }
}
