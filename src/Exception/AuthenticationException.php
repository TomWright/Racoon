<?php

namespace Racoon\Api\Exception;


use Racoon\Api\Request;

class AuthenticationException extends Exception
{

    public function __construct(Request $request = null, $message, \Exception $previous = null)
    {
        parent::__construct($request, true, $message, 401, $previous);
    }

}