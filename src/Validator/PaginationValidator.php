<?php

namespace DeployTracker\Validator;

use DeployTracker\ORM\Tools\Pagination\Paginator;
use DeployTracker\Exception\RequestedPageOutOfBoundsException;

class PaginationValidator
{
    /**
     * @param Paginator $paginator
     * @return void
     * @throws RequestedPageOutOfBoundsException
     */
    public function validate(Paginator $paginator)
    {
        $page = $paginator->getPage();
        $maxPage = $paginator->getMaxPage();

        if ($maxPage > 0 && $page > $maxPage) {
            throw new RequestedPageOutOfBoundsException($page, $maxPage);
        }
    }
}
