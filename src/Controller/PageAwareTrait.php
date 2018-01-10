<?php

namespace DeployTracker\Controller;

use DeployTracker\Exception\RedirectToRouteException;
use DeployTracker\Repository\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait PageAwareTrait
{
    /**
     * @param Request $request
     * @return int
     */
    protected function getPage(Request $request): int
    {
        $page = (int) $request->query->get('page', 1);

        return $page < 1 ? 1 : $page;
    }

    /**
     * @param Request $request
     * @param int $maxPage
     * @return void
     */
    protected function validatePaging(Request $request, int $maxPage)
    {
        $page = $this->getPage($request);

        if ($maxPage > 0 && $page > $maxPage) {
            $route = $request->attributes->get('_route');
            $routeParams = $request->attributes->get('_route_params');
            $parameters = array_merge($routeParams, ['page' => $maxPage]);

            throw new RedirectToRouteException($route, $parameters);
        }
    }
}
