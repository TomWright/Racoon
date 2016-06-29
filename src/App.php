<?php

namespace Racoon\Api;


use Racoon\Api\Auth\AuthInterface;
use Racoon\Api\Auth\NoAuthenticator;
use Racoon\Api\Exception\Exception;
use Racoon\Api\Response\Format\FormatterInterface;
use Racoon\Api\Response\Generate\DetailedResponse;
use Racoon\Api\Response\Generate\GeneratorInterface;
use Racoon\Api\Response\Format\JsonFormatter;
use Racoon\Router\Router;

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

    /**
     * @var GeneratorInterface
     */
    protected $responseGenerator;

    /**
     * @var bool
     */
    protected $requiresSchema;

    public function __construct()
    {
        $this->setRequestClass('\\Racoon\\Api\\Request');
        $this->authenticator = new NoAuthenticator();
        $this->router = new Router();
        $this->responseFormatter = new JsonFormatter();
        $this->responseGenerator = new DetailedResponse();
        $this->setRequiresSchema(false);
    }


    public function createRequest()
    {
        $reflectionClass = new \ReflectionClass($this->getRequestClass());
        $this->request = $reflectionClass->newInstance();
        $this->getResponseGenerator()->setRequest($this->request);
    }


    public function run()
    {
        $this->createRequest();

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
            $this->request->process($this->router, $this->getRequiresSchema());
        } catch (\Exception $e) {
            if (method_exists($e, 'shouldDisplayAsError') && is_callable([$e, 'shouldDisplayAsError'])) {
                if ($e->shouldDisplayAsError()) {
                    $this->request->setDisplayException($e);
                } else {
                    throw $e;
                }
            }
        }

        $this->request->setEndTime(microtime(true));

        $response = $this->getResponseGenerator()->generate();
        $httpResponseCode = $this->getResponseGenerator()->getHttpResponseCode();

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
     * @return GeneratorInterface
     */
    public function getResponseGenerator()
    {
        return $this->responseGenerator;
    }


    /**
     * @param GeneratorInterface $responseGenerator
     */
    public function setResponseGenerator($responseGenerator)
    {
        $this->responseGenerator = $responseGenerator;
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


    /**
     * @return boolean
     */
    public function getRequiresSchema()
    {
        return $this->requiresSchema;
    }


    /**
     * @param boolean $requiresSchema
     */
    public function setRequiresSchema($requiresSchema)
    {
        $this->requiresSchema = $requiresSchema;
    }

}