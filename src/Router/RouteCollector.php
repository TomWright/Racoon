<?php

namespace Racoon\Api\Router;


class RouteCollector extends \FastRoute\RouteCollector
{

    /**
     * @var string|null
     */
    protected $handlerNamespace;

    /**
     * @var null|\stdClass
     */
    protected $currentRouteGroup = null;


    /**
     * @param $route
     * @param callable $callback
     * @param $previousGroup
     * @return \stdClass
     */
    public function createGroup($route, callable $callback, $previousGroup)
    {
        if (is_object($previousGroup)) {
            $route = "{$previousGroup->route}/" . ltrim($route, '/');
        }

        $group = new \stdClass();
        $group->route = $route;
        $group->callback = $callback;
        $group->groups = [];

        return $group;
    }


    /**
     * @param $route
     * @param callable $callback
     */
    public function addGroup($route, callable $callback)
    {
        $route = rtrim($route, '/');
        $previousGroup = $this->currentRouteGroup;

        $this->currentRouteGroup = $this->createGroup($route, $callback, $previousGroup);

        $callback($this);

        $this->currentRouteGroup = $previousGroup;
    }


    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed $handler
     * @param bool $useHandlerNamespace
     */
    public function addRoute($httpMethod, $route, $handler, $useHandlerNamespace = true) {
        if (is_object($this->currentRouteGroup)) {
            $route = "{$this->currentRouteGroup->route}/" . ltrim($route, '/');
        }

        if ($this->handlerNamespace !== null && $useHandlerNamespace) {
            $handler = "{$this->handlerNamespace}\\" . ltrim($handler, '\\');
        }

        return parent::addRoute($httpMethod, $route, $handler);
    }


    /**
     * @param null|string $handlerNamespace
     */
    public function setHandlerNamespace($handlerNamespace)
    {
        $this->handlerNamespace = rtrim($handlerNamespace, '\\');
    }


    /**
     * @return null|string
     */
    public function getHandlerNamespace()
    {
        return $this->handlerNamespace;
    }

}