<?php

namespace Racoon\Api;


use Racoon\Api\Exception\Exception;
use Racoon\Api\Exception\InvalidJsonException;
use Racoon\Router\DispatcherResult;
use Racoon\Router\Router;
use Racoon\Api\Schema\Schema;

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

    /**
     * @var string[]
     */
    protected $headers;

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DispatcherResult
     */
    protected $dispatcherResult;

    /**
     * @var string|null
     */
    protected $responseMessage;

    /**
     * @var mixed
     */
    protected $controllerResponse;

    /**
     * An exception to be displayed to the user.
     * @var \Exception
     */
    protected $displayException;

    public function __construct()
    {
        $this->setStartTime(microtime(true));
    }


    /**
     * Returns a piece of optional data from the input data.
     * @param string $name
     * @param null|mixed $default
     * @return mixed|null
     */
    public function getOptionalRequestData($name, $default = null)
    {
        $data = $this->getRequestData();
        $result = $default;
        if (isset($data->{$name}) && mb_strlen($data->{$name}, 'UTF-8') > 0) {
            $result = $data->{$name};
        }
        return $result;
    }


    /**
     * Returns a piece of required data from the input data.
     * @param string $name
     * @param string $errorMessage The error message to be shown to the user. [parameter] will be replaced with the missing parameter name.
     * @return mixed|null
     * @throws Exception
     */
    public function getRequiredRequestData($name, $errorMessage = 'Missing required parameter: [parameter]')
    {
        $result = $this->getOptionalRequestData($name, null);
        if ($result === null) {
            $errorMessage = str_replace('[parameter]', $name, $errorMessage);
            throw new \Racoon\Api\Exception\Exception($this, true, $errorMessage, 400);
        }
        return $result;
    }


    /**
     * @param bool $fullRequest
     * @return null|object
     */
    public function getRequestData($fullRequest = false)
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
    public function getFullRequestData()
    {
        return $this->getRequestData(true);
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
     * @return $this
     */
    public function setHttpMethod($httpMethod)
    {
        $this->httpMethod = $httpMethod;
        return $this;
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
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }


    /**
     * Process the current request.
     * @param Router $router
     * @param bool $requiresSchema
     * @return mixed
     * @throws Exception
     */
    public function process(Router $router, $requiresSchema = false)
    {
        $controllerResponse = null;

        try {

            $dispatcherResult = $router
                ->processRoutes($this->getHttpMethod(), $this->getUri())
                ->getDispatcherResult();

            $this->setDispatcherResult($dispatcherResult);

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

            if ($requiresSchema && ! is_object($this->getSchema())) {
                throw new Exception($this, false, "Requires Schema is set to true but no Schema is present.");
            }

        } catch (Exception $e) {
            // Attach the request to the exception.
            if (! is_object($e->getRequest())) {
                $e->setRequest($this);
            }

            throw $e;
        }

        $this->setControllerResponse($controllerResponse);

        return $this;
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


    /**
     * @return Schema
     */
    public function getSchema()
    {
        return $this->schema;
    }


    /**
     * @param Schema $schema
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }


    /**
     * @return DispatcherResult
     */
    public function getDispatcherResult()
    {
        return $this->dispatcherResult;
    }


    /**
     * @param DispatcherResult $dispatcherResult
     */
    public function setDispatcherResult($dispatcherResult)
    {
        $this->dispatcherResult = $dispatcherResult;
    }


    /**
     * @return null|string
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }


    /**
     * @param null|string $responseMessage
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
    }


    /**
     * @return mixed
     */
    public function getControllerResponse()
    {
        return $this->controllerResponse;
    }


    /**
     * @param mixed $controllerResponse
     */
    protected function setControllerResponse($controllerResponse)
    {
        $this->controllerResponse = $controllerResponse;
    }


    /**
     * @return \Exception
     */
    public function getDisplayException()
    {
        return $this->displayException;
    }


    /**
     * @param \Exception $displayException
     */
    public function setDisplayException($displayException)
    {
        $this->displayException = $displayException;
    }


    /**
     * @return \string[]
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * @param \string[] $headers
     */
    public function setHeaders($headers)
    {
        if (! is_array($headers)) {
            $headers = array();
        }
        $this->headers = $headers;
    }


    /**
     * @param string $header
     * @param null $defaultValue
     * @return null|string
     */
    public function getHeader($header, $defaultValue = null)
    {
        if (array_key_exists($header, $this->headers)) {
            return $this->headers[$header];
        }
        return $defaultValue;
    }

}