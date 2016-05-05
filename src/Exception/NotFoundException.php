<?php

namespace Racoon\Api\Exception;


use Racoon\Api\Request;

class NotFoundException extends Exception
{

    public function __construct(Request $request = null, $message, $code = 404, \Exception $previous = null)
    {
        parent::__construct($request, true, $message, $code, $previous);
    }

}