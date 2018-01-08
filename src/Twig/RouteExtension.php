<?php

namespace DeployTracker\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class RouteExtension extends \Twig_Extension
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('route_name', [$this, 'getRouteName']),
            new \Twig_SimpleFunction('route_params', [$this, 'getRouteParams']),
        ];
    }

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->attributes->get('_route');
    }

    /**
     * @param array $additionalParams
     * @param array $removeKeys
     * @return array
     */
    public function getRouteParams(array $additionalParams = [], array $removeKeys = []): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $routeParams = $request->attributes->get('_route_params');
        $routeParams = array_merge($routeParams, $additionalParams);

        return array_filter($routeParams, function ($key) use ($removeKeys) {
            return !in_array($key, $removeKeys);
        }, ARRAY_FILTER_USE_KEY);
    }
}
