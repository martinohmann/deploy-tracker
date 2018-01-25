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
     * @var int
     */
    private $defaultPage;

    /**
     * @param string $paramName
     * @param int $defaultPage
     */
    public function __construct(string $paramName = 'page', int $defaultPage = 1)
    {
        $this->paramName = $paramName;
        $this->defaultPage = $defaultPage;
    }

    /**
     * @param Request $request
     * @return int
     */
    public function resolve(Request $request): int
    {
        $page = (int) $request->query->get($this->paramName, $this->defaultPage);

        return $page < 1 ? 1 : $page;
    }
}
