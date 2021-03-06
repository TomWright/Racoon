<?php

namespace Racoon\Api;


use Racoon\Api\Auth\AuthInterface;
use Racoon\Api\Auth\NoAuthenticator;
use Racoon\Api\Exception\Exception;
use Racoon\Api\Exception\InvalidJsonException;
use Racoon\Api\Response\Format\FormatterInterface;
use Racoon\Api\Response\Generate\DetailedResponse;
use Racoon\Api\Response\Generate\GeneratorInterface;
use Racoon\Api\Response\Format\JsonFormatter;
use Racoon\Router\Router;

/**
 * The main Racoon API class, through which the entire application should accessed and processed.
 * @package Racoon\Api
 */
class App
{

    /**
     * Where Racoon should look in the GET / POST array for the JSON request string.
     * @var string
     */
    protected $jsonKeyName = 'json';

    /**
     * Where Racoon is going to look for the json input data.
     * Available values: request, body
     * @var string
     */
    protected $jsonInputMethod = 'request';

    /**
     * The Authenticator that should be used by the application.
     * @var AuthInterface
     */
    protected $authenticator;

    /**
     * The current Request in the Racoon Application.
     * @var Request
     */
    protected $request;

    /**
     * The name of the Request class Racoon should use.
     * This allows you to add additional functionality to the Request.
     * @var string
     */
    protected $requestClass;

    /**
     * The current Router object being used by the Application.
     * @var Router
     */
    protected $router;

    /**
     * The Formatter to be used by Racoon when formatting the request response.
     * @var FormatterInterface
     */
    protected $responseFormatter;

    /**
     * The Generator to be used by Racoon when putting the request response together.
     * @var GeneratorInterface
     */
    protected $responseGenerator;

    /**
     * Defines whether or not the Controller requires a Schema to be set.
     * @var bool
     */
    protected $requiresSchema;


    /**
     * App constructor.
     * Set some default options.
     */
    public function __construct()
    {
        $this->setRequestClass('\\Racoon\\Api\\Request');
        $this->authenticator = new NoAuthenticator();
        $this->router = new Router();
        $this->responseFormatter = new JsonFormatter();
        $this->responseGenerator = new DetailedResponse();
        $this->setRequiresSchema(false);
    }


    /**
     * Creates the Request for Racoon to use.
     */
    public function createRequest()
    {
        $reflectionClass = new \ReflectionClass($this->getRequestClass());
        $this->request = $reflectionClass->newInstance();
        $this->getResponseGenerator()->setRequest($this->request);
    }


    /**
     * @throws Exception\InvalidJsonException
     */
    protected function setupRequest()
    {
        $headers = false;
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        }
        if ($headers === false) {
            $headers = array();
        }
        $this->request->setHeaders($headers);

        $contentType = $this->request->getHeader('Content-Type', null);

        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;

        if ($contentType === 'application/json') {
            $json = $this->getJsonStringFromRequest();
            $this->request->setRequestJson($json);
        } else {
            $data = $this->getRequestDataFromHttp();
            $this->request->setRequest($data);
        }

        $this->request
            ->setHttpMethod($requestMethod)
            ->setUri($uri);
    }


    /**
     * @return string|null
     */
    protected function getJsonStringFromRequest()
    {
        $json = file_get_contents('php://input');
        return $json;
    }


    /**
     * @return array
     */
    protected function getRequestDataFromHttp()
    {
        $getData = (isset($_GET) && is_array($_GET)) ? $_GET : array();
        $postData = (isset($_POST) && is_array($_POST)) ? $_POST : array();
        $fileData = (isset($_FILES) && is_array($_FILES)) ? $_FILES : array();

        $data = (object) (array_merge($getData, $postData));
        $data->files = (object) $fileData;
        foreach ($data->files as $key => $val) {
            $data->files->{$key} = (object) $data->files->{$key};
        }

        return $data;
    }


    /**
     * Runs the Application.
     * @throws Exception
     * @throws \Exception
     */
    public function run()
    {
        if ($this->getRequest() === null) {
            $this->createRequest();
        }

        try {
            $this->setupRequest();
            $this->authenticator->authenticate($this->request);
            $this->router->init();
            $this->request->process($this->router, $this->getRequiresSchema());
        } catch (\Exception $e) {
            if (method_exists($e, 'shouldDisplayAsError') && is_callable([$e, 'shouldDisplayAsError'])) {
                if ($e->shouldDisplayAsError()) {
                    $this->request->setDisplayException($e);
                } else {
                    http_response_code(500);
                    throw $e;
                }
            } else {
                http_response_code(500);
                throw $e;
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

            http_response_code(500);
            throw $e;
        }

        $contentType = $this->responseFormatter->getContentType();
        if (! headers_sent()) {
            if ($contentType !== null) {
                header("Content-Type: {$contentType}");
            }
            http_response_code($httpResponseCode);
        }

        echo $formattedResponse;
    }


    /**
     * Returns where Racoon should look in the GET / POST array for the JSON request string.
     * @return string
     */
    public function getJsonKeyName()
    {
        return $this->jsonKeyName;
    }


    /**
     * Sets where Racoon should look in the GET / POST array for the JSON request string.
     * @param string $jsonKeyName
     */
    public function setJsonKeyName($jsonKeyName)
    {
        $this->jsonKeyName = $jsonKeyName;
    }


    /**
     * Returns the Authenticator that should be used by the application.
     * @return AuthInterface
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }


    /**
     * Sets the Authenticator that should be used by the application.
     * @param AuthInterface $authenticator
     */
    public function setAuthenticator(AuthInterface $authenticator)
    {
        $this->authenticator = $authenticator;
    }


    /**
     * Returns the Router being used by the Application.
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }


    /**
     * Returns the Formatter to be used by Racoon when formatting the request response.
     * @return FormatterInterface
     */
    public function getResponseFormatter()
    {
        return $this->responseFormatter;
    }


    /**
     * Sets the Formatter to be used by Racoon when formatting the request response.
     * @param FormatterInterface $responseFormatter
     */
    public function setResponseFormatter($responseFormatter)
    {
        $this->responseFormatter = $responseFormatter;
    }


    /**
     * Returns the Generator to be used by Racoon when putting the request response together.
     * @return GeneratorInterface
     */
    public function getResponseGenerator()
    {
        return $this->responseGenerator;
    }


    /**
     * Sets the Generator to be used by Racoon when putting the request response together.
     * @param GeneratorInterface $responseGenerator
     */
    public function setResponseGenerator($responseGenerator)
    {
        $this->responseGenerator = $responseGenerator;
    }


    /**
     * Returns the name of the Request class Racoon should use.
     * @return string
     */
    public function getRequestClass()
    {
        return $this->requestClass;
    }


    /**
     * Sets the name of the Request class Racoon should use.
     * @param string $requestClass
     */
    public function setRequestClass($requestClass)
    {
        $this->requestClass = $requestClass;
    }


    /**
     * Returns the current Request being used by Racoon.
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * Returns whether or not a Schema is required for the current Request.
     * @return boolean
     */
    public function getRequiresSchema()
    {
        return $this->requiresSchema;
    }


    /**
     * Sets whether or not a Schema is required for the current Request.
     * @param boolean $requiresSchema
     */
    public function setRequiresSchema($requiresSchema)
    {
        $this->requiresSchema = $requiresSchema;
    }

}