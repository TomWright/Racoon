<?php

use Racoon\Api\Auth\ApiKeyAuthenticator;
use Racoon\Api\Exception\AuthenticationException;
use Racoon\Api\Request;
use Racoon\Api\Test\TestBase;

class ApiKeyAuthenticatorTest extends TestBase
{

    protected function getApp(array $requestData = [], array $baseData = [])
    {
        $app = parent::getApp($requestData, $baseData);
        $auth = new ApiKeyAuthenticator();
        $auth->addValidApiKey('valid');
        $app->setAuthenticator($auth);
        return $app;
    }


    public function testNoApiKeyProvided()
    {
        $request = new Request();
        $requestData = new stdClass();
        $auth = new ApiKeyAuthenticator();

        $auth->setValidApiKeys(['valid1', 'valid2']);

        $valid = true;
        try {
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);
    }


    public function testAuthenticatorWorks()
    {
        $request = new Request();
        $requestData = new stdClass();
        $auth = new ApiKeyAuthenticator();

        $auth->setValidApiKeys(['valid1', 'valid2']);
        
        $valid = true;
        try {
            $requestData->api_key = 'asdasdasd';
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $valid = true;
        try {
            $requestData->api_key = 'valid1';
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $requestData->api_key = 'valid2';
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $requestData->api_key = 'valid3';
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $auth->addValidApiKey('valid3');

        $valid = true;
        try {
            $requestData->api_key = 'valid3';
            $request->setRequest($requestData);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }


    public function testApiKeyValidatorValid()
    {
        $valid = true;
        $app = $this->getApp([], ['api_key' => 'valid']);
        try {
            $output = $app->run();
            if (strpos($output, 'Invalid API Key') !== false) {
                $valid = false;
            }
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }


    public function testApiKeyValidatorInvalid()
    {
        $valid = true;
        $app = $this->getApp([], ['api_key' => 'invalid']);
        try {
            $output = $app->run();
            if (strpos($output, 'Invalid API Key') !== false) {
                $valid = false;
            }
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);
    }

}