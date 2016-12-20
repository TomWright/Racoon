<?php

namespace Racoon\Api\Test;

use PHPUnit_Framework_TestCase;
use Racoon\Api\Auth\ApiKeyAuthenticator;
use stdClass;

class TestBase extends PHPUnit_Framework_TestCase
{

    /**
     * @param array $requestData
     * @param array $headers
     * @return TestApp
     */
    protected function getApp(array $requestData = [], array $headers = [])
    {
        $app = new TestApp();

        $request = new stdClass();
        foreach ($requestData as $key => $val) {
            $request->{$key} = $val;
        }

        $app->setUri('/');
        $app->setHttpMethod('GET');
        $app->setRequestData($request);
        $app->setHeaderData($headers);

        return $app;
    }

}