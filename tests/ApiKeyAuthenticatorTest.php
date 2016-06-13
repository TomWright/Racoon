<?php


use Racoon\Api\App;
use Racoon\Api\Auth\ApiKeyAuthenticator;
use Racoon\Api\Exception\AuthenticationException;

class ApiKeyAuthenticatorTest extends PHPUnit_Framework_TestCase
{

    public function testApiKeyAuthenticatorKeys()
    {
        $app = new App();

        $authenticator = new ApiKeyAuthenticator();
        $authenticator->addValidApiKey('this_is_valid');

        $app->setAuthenticator($authenticator);
        $app->createRequest();

        $request = $app->getRequest();
        $requestData = new stdClass();
        $requestData->api_key = 'this_is_invalid';
        $requestData->request = new stdClass();
        $request->setRequest($requestData);

        $valid = null;
        try {
            $valid = $authenticator->authenticate($app->getRequest());
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $request = $app->getRequest();
        $requestData = new stdClass();
        $requestData->api_key = 'this_is_valid';
        $requestData->request = new stdClass();
        $request->setRequest($requestData);

        $valid = null;
        try {
            $valid = $authenticator->authenticate($app->getRequest());
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }

}