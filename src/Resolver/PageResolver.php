<?php

namespace DeployTracker\Resolver;

use Symfony\Component\HttpFoundation\Request;

class PageResolver
{
    /**
     * @var string
     */
    private $paramName;

    /**
     * @param string $paramName
     */
    public function __construct(string $paramName = 'page')
    {
        $this->paramName = $paramName;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function resolve(Request $request): int
    {
        $page = (int) $request->query->get($this->paramName, 1);

        return $page < 1 ? 1 : $page;
    }
}
