<?php

namespace Racoon\Api\Router;


use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Racoon\Api\Exception\InvalidRouteException;
use Racoon\Api\Exception\NotFoundException;
use Racoon\Api\Exception\RouterException;

class Router
{

    /**
     * @var string
     */
    protected $currentUri;

    /**
     * @var string[]
     */
    protected $routeFiles = [];

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var DispatcherResult
     */
    protected $dispatcherResult;

    /**
     * @var string
     */
    protected $defaultMethod;

    public function __construct()
    {
    }


    /**
     * @return $this
     */
    public function init()
    {
        $this->loadRoutes();
        return $this;
    }


    /**
     * Load in the route files and set the dispatcher.
     */
    protected function loadRoutes()
    {
        $this->dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routeFiles as $routeFile) {
                if (file_exists($routeFile)) {
                    require $routeFile;
                } else {
                    throw new RouterException($this->request, 'Route file does not exist: ' . $routeFile);
                }
            }
        });
    }

    /**
     * @return \string[]
     */
    public function getRouteFiles()
    {
        return $this->routeFiles;
    }


    /**
     * @param \string[] $routeFiles
     * @return $this
     */
    public function setRouteFiles($routeFiles)
    {
        $this->routeFiles = $routeFiles;
        return $this;
    }


    /**
     * @param string $routeFile
     * @return $this
     */
    public function addRouteFile($routeFile)
    {
        if (! in_array($routeFile, $this->routeFiles)) {
            $this->routeFiles[] = $routeFile;
        }
        return $this;
    }


    /**
     * @return string
     */
    public function getDefaultMethod()
    {
        return $this->defaultMethod;
    }


    /**
     * @param string $defaultMethod
     * @return $this
     */
    public function setDefaultMethod($defaultMethod)
    {
        $this->defaultMethod = $defaultMethod;
        return $this;
    }


    /**
     * Process the routes against the given request method and request URI,
     * and set the DispatcherResult.
     * @param string $httpMethod
     * @param string $uri
     * @return $this
     * @throws InvalidRouteException
     * @throws NotFoundException
     */
    public function processRoutes($httpMethod, $uri)
    {
        // Strip the query string from the $uri if there is one.
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);
        $this->setCurrentUri($uri);
        
        $this->dispatcherResult = new DispatcherResult();
        
        $info = $this->dispatcher->dispatch($httpMethod, $uri);
        
        switch ($info[0]) {
            case Dispatcher::NOT_FOUND:
                throw new NotFoundException(null, 'No matching route was found.');
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new NotFoundException(null, 'Route method not allowed.', 401);
                break;
            case Dispatcher::FOUND:
                $handler = $info[1];
                $this->dispatcherResult->setVars((isset($info[2]) && is_array($info[2])) ? $info[2] : array());

                $handlerArray = explode('@', $handler);
                $controller = (isset($handlerArray[0]) && ! is_null($handlerArray[0]) && strlen($handlerArray[0]) > 0) ? $handlerArray[0] : null;
                $method = (isset($handlerArray[1]) && ! is_null($handlerArray[1]) && strlen($handlerArray[1]) > 0) ? $handlerArray[1] : null;

                if ($controller === null) {
                    throw new InvalidRouteException(null, "Missing Handler Class... Handler \"{$handler}\" requires format of \"SomeClass@someMethod\"");
                }
                if ($method === null) {
                    if ($this->defaultMethod !== null && strlen($this->defaultMethod) > 0) {
                        $method = $this->defaultMethod;
                    } else {
                        throw new InvalidRouteException(null, "Missing Handler Method... Handler \"{$handler}\" requires format of \"SomeClass@someMethod\"");
                    }
                }

                $this->dispatcherResult
                    ->setClass($controller)
                    ->setMethod($method)
                    ->init()
                    ->validate();
                break;
        }

        return $this;
    }


    /**
     * @return DispatcherResult
     */
    public function getDispatcherResult()
    {
        return $this->dispatcherResult;
    }


    /**
     * @return string
     */
    public function getCurrentUri()
    {
        return $this->currentUri;
    }


    /**
     * @param string $currentUri
     */
    protected function setCurrentUri($currentUri)
    {
        $this->currentUri = $currentUri;
    }

}