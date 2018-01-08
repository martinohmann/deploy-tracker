<?php

namespace DeployTracker\Repository;

interface ItemsPerPageAwareInterface
{
    /**
     * @return int
     */
    public function getItemsPerPage(): int;
}
