<?php

namespace Racoon\Api\Auth;


use Racoon\Api\Exception\AuthenticationException;
use Racoon\Api\Request;

/**
 * An Authenticator that allows everyone through.
 * Class ApiKeyAuthenticator
 * @package Racoon\Api\Auth
 */
class NoAuthenticator implements AuthInterface
{

    /**
     * @param Request $request
     * @return bool
     * @throws AuthenticationException
     */
    public function authenticate(Request $request)
    {
        return true;
    }

}