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
     * @var \stdClass
     */
    protected $requestData;

    /**
     * @var array
     */
    protected $headerData;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $httpMethod;


    /**
     * @return \stdClass
     */
    public function getRequestData()
    {
        return $this->requestData;
    }


    /**
     * @param \stdClass $requestData
     */
    public function setRequestData(\stdClass $requestData)
    {
        $this->requestData = $requestData;
    }


    /**
     * @return array
     */
    public function getHeaderData()
    {
        return $this->headerData;
    }


    /**
     * @param array $headerData
     */
    public function setHeaderData(array $headerData)
    {
        $this->headerData = $headerData;
    }


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
        if ($this->jsonString !== null) {
            $this->request->setRequestJson($this->getJsonString());
        } else {
            $this->request->setRequest($this->getRequestData());
        }


        $this->request
            ->setHeaders($this->getHeaderData())
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