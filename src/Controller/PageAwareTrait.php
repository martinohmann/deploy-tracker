<?php

namespace DeployTracker\Controller;

use DeployTracker\Exception\RequestedPageOutOfBoundsException;
use DeployTracker\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

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
     * @param Paginator $paginator
     * @return void
     */
    protected function validatePagination(Paginator $paginator)
    {
        $page = $paginator->getPage();
        $maxPage = $paginator->getMaxPage();

        if ($maxPage > 0 && $page > $maxPage) {
            throw new RequestedPageOutOfBoundsException($page, $maxPage);
        }
    }
}
