<?php

use Racoon\Api\Auth\ApiKeyAuthenticator;
use Racoon\Api\Exception\AuthenticationException;
use Racoon\Api\Request;
use Racoon\Api\Test\TestBase;

class ApiKeyAuthenticatorTest extends TestBase
{

    protected function getApp(array $requestData = [], array $headers = [])
    {
        $app = parent::getApp($requestData, $headers);
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
        $headers = array();
        $auth = new ApiKeyAuthenticator();
        $request->setRequest($requestData);

        $auth->setValidApiKeys(['valid1', 'valid2']);
        
        $valid = true;
        try {
            $headers['Api-Key'] = 'asdasdasd';
            $request->setHeaders($headers);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $valid = true;
        try {
            $headers['Api-Key'] = 'valid1';
            $request->setHeaders($headers);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $headers['Api-Key'] = 'valid2';
            $request->setHeaders($headers);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $headers['Api-Key'] = 'valid3';
            $request->setHeaders($headers);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $auth->addValidApiKey('valid3');

        $valid = true;
        try {
            $headers['Api-Key'] = 'valid3';
            $request->setHeaders($headers);
            $auth->authenticate($request);
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }


    public function testApiKeyValidatorValid()
    {
        $valid = true;
        $app = $this->getApp([], ['Api-Key' => 'valid']);
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
        $app = $this->getApp([], ['Api-Key' => 'invalid']);
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