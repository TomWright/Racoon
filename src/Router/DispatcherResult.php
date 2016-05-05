<?php

namespace Racoon\Api\Router;


use Racoon\Api\Exception\InvalidRouteException;

class DispatcherResult
{

    /**
     * @var string
     */
    protected $class;

    /**
     * Instance of $class.
     * @var object|null
     */
    protected $classObject;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string[]|int[]
     */
    protected $vars;

    public function __construct()
    {
    }


    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }


    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }


    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }


    /**
     * @return \int[]|\string[]
     */
    public function getVars()
    {
        return $this->vars;
    }


    /**
     * @param \int[]|\string[] $vars
     * @return $this
     */
    public function setVars($vars)
    {
        $this->vars = $vars;
        return $this;
    }


    /**
     * @return null|object
     */
    public function getClassObject()
    {
        return $this->classObject;
    }


    /**
     * Sets $this->classObject to an instance of $this->class.
     * @return $this
     * @throws InvalidRouteException
     */
    public function init()
    {
        if (! class_exists($this->class)) {
            throw new InvalidRouteException(null, "Route handler \"{$this->class}\" does not exist.");
        }

        $class = new \ReflectionClass($this->class);
        $this->classObject = $class->newInstance();

        return $this;
    }


    /**
     * Validates that the method exists in the class and that it is callable.
     * @return bool
     * @throws InvalidRouteException
     */
    public function validate()
    {
        if (! is_object($this->classObject)) {
            $this->init();
        }

        if (! (method_exists($this->classObject, $this->method) && is_callable([$this->classObject, $this->method]))) {
            throw new InvalidRouteException(null, "Route method \"{$this->method}\" does not exist in handler \"{$this->class}\".");
        }

        return true;
    }

}