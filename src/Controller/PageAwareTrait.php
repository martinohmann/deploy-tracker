<?php

namespace DeployTracker\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\Collection;
use DeployTracker\Repository\ItemsPerPageAwareInterface;

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
     * @param Collection $collection
     * @param ItemsPerPageAwareInterface $repository
     * @return int
     */
    protected function getMaxPage(Collection $collection, ItemsPerPageAwareInterface $repository): int
    {
        return ceil($collection->count() / $repository->getItemsPerPage());
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
