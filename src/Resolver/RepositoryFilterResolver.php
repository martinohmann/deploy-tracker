<?php

namespace DeployTracker\Resolver;

use DeployTracker\Repository\FilterableInterface;
use Symfony\Component\HttpFoundation\Request;

class RepositoryFilterResolver
{
    /**
     * @param FilterableInterface $repository
     * @param Request $request
     * @return array
     */
    public function resolve(FilterableInterface $repository, Request $request): array
    {
        $filters = [];

        foreach ($repository->getAvailableFilters() as $filter) {
            if ($request->query->has($filter)) {
                $filters[$filter] = $request->query->get($filter);
            }
        }

        return $filters;
    }
}
