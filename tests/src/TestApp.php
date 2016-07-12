<?php

namespace Racoon\Api\Test;

use Racoon\Api\App;
use Racoon\Api\Exception\InvalidJsonException;

class TestApp extends App
{

    /**
     * @var string
     */
    protected $jsonString;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $httpMethod;


    /**
     * @return string
     */
    public function getJsonString()
    {
        return $this->jsonString;
    }


    /**
     * @param string $jsonString
     */
    public function setJsonString($jsonString)
    {
        $this->jsonString = $jsonString;
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
     * @throws InvalidJsonException
     */
    protected function setupRequest()
    {
        $this->request
            ->setRequestJson($this->getJsonString())
            ->setHttpMethod($this->getHttpMethod())
            ->setUri($this->getUri());
    }


    /**
     * @return string
     * @throws \Exception
     * @throws \Racoon\Api\Exception\Exception
     */
    public function run()
    {
        ob_start();
        parent::run();
        $output = ob_get_clean();
        return $output;
    }

}