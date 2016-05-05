<?php

namespace Racoon\Api\Exception;


use Racoon\Api\Request;

class RouterException extends Exception
{

    public function __construct(Request $request = null, $message, \Exception $previous = null)
    {
        parent::__construct($request, false, $message, 500, $previous);
    }

}