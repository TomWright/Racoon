<?php

namespace Racoon\Api;


abstract class Controller
{

    /**
     * @var Request
     */
    protected $request;


    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }


    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

}