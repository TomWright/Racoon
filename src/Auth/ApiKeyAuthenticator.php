<?php

namespace Racoon\Api\Auth;


use Racoon\Api\Exception\AuthenticationException;
use Racoon\Api\Request;

/**
 * Basic authenticator which serves as an example.
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
        $this->setApiKeyName('api_key');
    }


    /**
     * @param Request $request
     * @return bool
     * @throws AuthenticationException
     */
    public function authenticate(Request $request)
    {
        $data = $request->getFullRequest();
        
        if (! (is_object($data) && isset($data->{$this->getApiKeyName()}))) {
            throw new AuthenticationException($request, 'API Key not found.');
        }

        $apiKey = $data->{$this->getApiKeyName()};
        
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