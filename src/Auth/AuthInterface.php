<?php

namespace Racoon\Api\Auth;


use Racoon\Api\Request;

interface AuthInterface
{

    /**
     * @param Request $request
     * @return bool
     */
    public function authenticate(Request $request);
    
}