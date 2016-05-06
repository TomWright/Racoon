<?php

namespace Racoon\Api;


use Racoon\Api\Controller;
use Racoon\Api\Exception\Exception;
use Racoon\Api\Exception\InvalidJsonException;
use Racoon\Api\Router\Router;

class Request
{

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @var float
     */
    protected $endTime;

    /**
     * @var null|object
     */
    protected $request;

    /**
     * @var string|null
     */
    protected $jsonString;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var string
     */
    protected $uri;

    public function __construct()
    {
        $this->setStartTime(microtime(true));
    }


    /**
     * @param bool $fullRequest
     * @return null|object
     */
    public function getRequest($fullRequest = false)
    {
        if ($fullRequest) {
            return $this->request;
        } else {
            return (is_object($this->request) && isset($this->request->request)) ? $this->request->request : null;
        }
    }


    /**
     * @return null|object
     */
    public function getFullRequest()
    {
        return $this->getRequest(true);
    }


    /**
     * @param null|object $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }


    /**
     * @param string|null $json
     * @return $this
     * @throws InvalidJsonException
     */
    public function setRequestJson($json)
    {
        $this->setJsonString($json);

        if (! (is_string($json) && strlen($json) > 0)) {
            // $json can't actually be json.
            throw new InvalidJsonException($this, 'Invalid JSON string.');
        }
        $jsonObject = json_decode($json);

        if ($jsonObject !== null) {
            $this->setRequest($jsonObject);
        } else {
            // Could't decode $json string.
            throw new InvalidJsonException($this, 'JSON string could not be decoded.');
        }

        return $this;
    }


    /**
     * @return null|string
     */
    public function getJsonString()
    {
        return $this->jsonString;
    }


    /**
     * @param null|string $jsonString
     * @return $this
     */
    public function setJsonString($jsonString)
    {
        $this->jsonString = $jsonString;
        return $this;
    }


    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }


    /**
     * @param string $httpMethod
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
    }


    /**
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }


    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }


    /**
     * Process the current request.
     * @param Router $router
     * @return mixed
     * @throws Exception
     */
    public function process(Router $router)
    {
        $controllerResponse = null;

        try {

            $dispatcherResult = $router
                ->processRoutes($this->getHttpMethod(), $this->getUri())
                ->getDispatcherResult();

            if ($dispatcherResult->getClassObject() instanceof Controller) {
                $dispatcherResult->getClassObject()->setRequest($this);
            }

            if (method_exists($dispatcherResult->getClassObject(), 'setupRequest') && is_callable([$dispatcherResult->getClassObject(), 'setupRequest'])) {
                call_user_func([$dispatcherResult->getClassObject(), 'setupRequest']);
            }

            $controllerResponse = call_user_func_array([
                $dispatcherResult->getClassObject(),
                $dispatcherResult->getMethod(),
            ], $dispatcherResult->getVars());

        } catch (Exception $e) {
            // Attach the request to the exception.
            if (! is_object($e->getRequest())) {
                $e->setRequest($this);
            }

            throw $e;
        }

        return $controllerResponse;
    }


    /**
     * @return float
     */
    public function getStartTime()
    {
        return $this->startTime;
    }


    /**
     * @param float $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }


    /**
     * @return float
     */
    public function getEndTime()
    {
        return $this->endTime;
    }


    /**
     * @param float $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }


    /**
     * null|float
     */
    public function getElapsedTime($convertToMs = false)
    {
        if ($this->startTime !== null && $this->endTime !== null) {
            $time = ($this->endTime - $this->startTime);
            if ($convertToMs) {
                $time = ($time * 1000);
            }
            return $time;
        }

        return null;
    }

}