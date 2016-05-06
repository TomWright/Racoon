<?php

namespace Racoon\Api;


use Racoon\Api\Auth\ApiKeyAuthenticator;
use Racoon\Api\Auth\AuthInterface;
use Racoon\Api\Exception\Exception;
use Racoon\Api\Response\Format\FormatterInterface;
use Racoon\Api\Response\Format\JsonFormatter;
use Racoon\Api\Router\Router;

class App
{

    /**
     * @var string
     */
    protected $jsonKeyName = 'json';

    /**
     * @var AuthInterface
     */
    protected $authenticator;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var string
     */
    protected $requestClass;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var FormatterInterface
     */
    protected $responseFormatter;

    public function __construct()
    {
        $this->setRequestClass('\\Recoon\\Api\\Request');
        $this->authenticator = new ApiKeyAuthenticator();
        $this->router = new Router();
        $this->responseFormatter = new JsonFormatter();
    }


    public function createRequest()
    {
        $reflectionClass = new \ReflectionClass($this->getRequestClass());
        $this->request = $reflectionClass->newInstance();
    }


    public function run()
    {
        $this->createRequest();

        $displayException = null;
        $controllerResponse = null;
        $httpResponseCode = 200;

        try {
            $json = isset($_REQUEST[$this->getJsonKeyName()]) ? $_REQUEST[$this->getJsonKeyName()] : null;
            $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
            $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
            $this->request
                ->setRequestJson($json)
                ->setHttpMethod($requestMethod)
                ->setUri($uri);
            $this->authenticator->authenticate($this->request);
            $this->router->init();
            $controllerResponse = $this->request->process($this->router);
        } catch (Exception $e) {
            if ($e->shouldDisplayAsError()) {
                $displayException = $e;
                $httpResponseCode = $e->getCode();
                if ($httpResponseCode === 0) {
                    $httpResponseCode = 500;
                }
            } else {
                throw $e;
            }
        }

        $this->request->setEndTime(microtime(true));

        $response = $this->generateResponse($controllerResponse, $displayException);
        $formattedResponse = null;

        try {
            $formattedResponse = $this->responseFormatter->format($response);
        } catch (Exception $e) {
            // Attach the request to the exception.
            if (! is_object($e->getRequest())) {
                $e->setRequest($this);
            }

            throw $e;
        }

        $contentType = $this->responseFormatter->getContentType();
        if ($contentType !== null) {
            header("Content-Type: {$contentType}");
        }
        http_response_code($httpResponseCode);

        echo $formattedResponse;
    }


    /**
     * @param null $controllerResponse
     * @param Exception|null $exception
     * @return \stdClass
     */
    protected function generateResponse($controllerResponse = null, Exception $exception = null)
    {
        $response = new \stdClass();
        $response->success = (! is_object($exception));
        $response->message = null;
        if (is_object($exception)) {
            $response->message = $exception->getMessage();
        }
        $response->parameters = [];
        $response->received = $this->request->getFullRequest();
        $response->time_elapsed = number_format($this->request->getElapsedTime(true), 3);
        $response->response = $controllerResponse;

        return $response;
    }


    /**
     * @return string
     */
    public function getJsonKeyName()
    {
        return $this->jsonKeyName;
    }


    /**
     * @param string $jsonKeyName
     */
    public function setJsonKeyName($jsonKeyName)
    {
        $this->jsonKeyName = $jsonKeyName;
    }


    /**
     * @return AuthInterface
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }


    /**
     * @param AuthInterface $authenticator
     */
    public function setAuthenticator(AuthInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }


    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }


    /**
     * @return FormatterInterface
     */
    public function getResponseFormatter()
    {
        return $this->responseFormatter;
    }


    /**
     * @param FormatterInterface $responseFormatter
     */
    public function setResponseFormatter($responseFormatter)
    {
        $this->responseFormatter = $responseFormatter;
    }


    /**
     * @return string
     */
    public function getRequestClass()
    {
        return $this->requestClass;
    }


    /**
     * @param string $requestClass
     */
    public function setRequestClass($requestClass)
    {
        $this->requestClass = $requestClass;
    }


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

}