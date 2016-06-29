<?php


namespace Racoon\Api\Response\Generate;


use Racoon\Api\Request;

interface GeneratorInterface
{

    /**
     * @param Request $request
     * @return mixed
     */
    public function setRequest(Request $request);


    /**
     * @return Request
     */
    public function getRequest();

    /**
     * @return mixed
     */
    public function generate();


    /**
     * @return int
     */
    public function getHttpResponseCode();
    
}