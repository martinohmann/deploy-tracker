<?php

namespace DeployTracker\Controller;

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
     * @param int $page
     * @param int $maxPage
     * @return bool
     */
    protected function shouldRedirectToMaxPage(int $page, int $maxPage): bool
    {
        return $maxPage > 0 && $page > $maxPage;
    }

    /**
     * @param Request $request
     * @param int $maxPage
     * @return Response
     */
    protected function redirectToMaxPage(Request $request, int $maxPage): Response
    {
        $route = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params');

        return $this->redirectToRoute($route, array_merge($routeParams, ['page' => $maxPage]));
    }
}
