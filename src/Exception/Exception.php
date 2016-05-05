<?php

namespace Racoon\Api\Exception;


use Racoon\Api\Request;

class Exception extends \Exception
{

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var bool
     */
    protected $displayAsError;

    public function __construct(Request $request = null, $displayAsError = false, $message, $code, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->setRequest($request);
        $this->setDisplayAsError($displayAsError == true);
    }


    /**
     * @return Request|null
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @param Request|null $request
     */
    public function setRequest($request = null)
    {
        $this->request = $request;
    }


    /**
     * @return boolean
     */
    public function shouldDisplayAsError()
    {
        return $this->displayAsError;
    }


    /**
     * @param boolean $displayAsError
     */
    public function setDisplayAsError($displayAsError)
    {
        $this->displayAsError = $displayAsError;
    }

}