<?php

namespace Racoon\Api\Test;

use PHPUnit_Framework_TestCase;
use Racoon\Api\Auth\ApiKeyAuthenticator;
use stdClass;

class TestBase extends PHPUnit_Framework_TestCase
{

    /**
     * @param array $requestData
     * @param string $apiKey
     * @return TestApp
     */
    public function getApp(array $requestData = [], $apiKey = 'valid')
    {
        $app = new TestApp();

        $auth = new ApiKeyAuthenticator();
        $auth->addValidApiKey('valid');

        $app->setAuthenticator($auth);

        $request = new stdClass();
        $request->api_key = $apiKey;
        $request->request = new stdClass();

        foreach ($requestData as $key => $val) {
            $request->request->{$key} = $val;
        }

        $app->setUri('/');
        $app->setHttpMethod('GET');
        $app->setJsonString(json_encode($request));

        $app->createRequest();

        return $app;
    }

}