<?php

use Racoon\Api\Auth\ApiKeyAuthenticator;
use Racoon\Api\Exception\AuthenticationException;
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