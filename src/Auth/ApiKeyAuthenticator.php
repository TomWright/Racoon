<?php

namespace Racoon\Api\Auth;


use Racoon\Api\Exception\AuthenticationException;
use Racoon\Api\Request;

/**
 * Basic authenticator which can be used to set a static list of API keys.
 * Class ApiKeyAuthenticator
 * @package Racoon\Api\Auth
 */
class ApiKeyAuthenticator implements AuthInterface
{

    /**
     * @var string
     */
    protected $apiKeyName;

    /**
     * @var string[]
     */
    protected $validApiKeys = [];


    public function __construct()
    {
        $this->setApiKeyName('Api-Key');
    }


    /**
     * @param Request $request
     * @return bool
     * @throws AuthenticationException
     */
    public function authenticate(Request $request)
    {
        $apiKey = $request->getHeader($this->getApiKeyName(), null);

        if (! (is_string($apiKey) && strlen($apiKey) > 0)) {
            throw new AuthenticationException($request, 'API Key not found.');
        }

        if (! in_array($apiKey, $this->getValidApiKeys())) {
            throw new AuthenticationException($request, 'Invalid API Key.');
        }

        return true;
    }


    /**
     * @return string
     */
    public function getApiKeyName()
    {
        return $this->apiKeyName;
    }


    /**
     * @param string $apiKeyName
     */
    public function setApiKeyName($apiKeyName)
    {
        $this->apiKeyName = $apiKeyName;
    }


    /**
     * @return string[]
     */
    public function getValidApiKeys()
    {
        return $this->validApiKeys;
    }


    /**
     * @param string[] $validApiKeys
     */
    public function setValidApiKeys($validApiKeys)
    {
        $this->validApiKeys = $validApiKeys;
    }


    /**
     * @param string $apiKey
     */
    public function addValidApiKey($apiKey)
    {
        if (! in_array($apiKey, $this->validApiKeys)) {
            $this->validApiKeys[] = $apiKey;
        }
    }

}