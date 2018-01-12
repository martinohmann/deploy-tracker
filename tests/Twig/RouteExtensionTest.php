<?php

namespace DeployTracker\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use DeployTracker\Twig\RouteExtension;

class RouteExtensionTest extends TestCase
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Request
     */
    private $request;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->request = new Request();
        $this->requestStack = new RequestStack();
    }

    /**
     * @test
     */
    public function shouldReturnCurrentRouteName()
    {
        $this->request->attributes->set('_route', 'some_route');
        $this->requestStack->push($this->request);

        $extension = new RouteExtension($this->requestStack);

        self::assertSame('some_route', $extension->getRouteName());
    }

    /**
     * @test
     */
    public function shouldReturnCurrentRouteParams()
    {
        $routeParams = ['someparam' => 'somevalue'];

        $this->request->attributes->set('_route_params', $routeParams);
        $this->requestStack->push($this->request);

        $extension = new RouteExtension($this->requestStack);

        self::assertSame($routeParams, $extension->getRouteParams());
    }

    /**
     * @test
     */
    public function shouldAddAdditionalParamsToRouteParams()
    {
        $initialRouteParams = ['someparam' => 'somevalue', 'x' => 'y'];

        $this->request->attributes->set('_route_params', $initialRouteParams);
        $this->requestStack->push($this->request);

        $extension = new RouteExtension($this->requestStack);

        $additionalParams = ['t' => 'u'];
        $routeParams = $extension->getRouteParams($additionalParams);
        $expectedRouteParams = $initialRouteParams + $additionalParams;

        self::assertEquals(array_keys($expectedRouteParams), array_keys($routeParams));
        self::assertEquals('u', $routeParams['t']);
        self::assertEquals('somevalue', $routeParams['someparam']);
        self::assertEquals('y', $routeParams['x']);
    }

    /**
     * @test
     */
    public function shouldRemoveParamsFromRouteParams()
    {
        $initialRouteParams = ['someparam' => 'somevalue', 'x' => 'y'];

        $this->request->attributes->set('_route_params', $initialRouteParams);
        $this->requestStack->push($this->request);

        $extension = new RouteExtension($this->requestStack);

        $routeParams = $extension->getRouteParams([], ['x']);

        self::assertCount(1, $routeParams);
        self::assertArrayNotHasKey('x', $routeParams);
    }
}
