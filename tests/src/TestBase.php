<?php

namespace Racoon\Api\Test;

use PHPUnit_Framework_TestCase;
use Racoon\Api\Auth\ApiKeyAuthenticator;
use stdClass;

class TestBase extends PHPUnit_Framework_TestCase
{

    /**
     * @param array $requestData
     * @param array $baseData
     * @return TestApp
     */
    protected function getApp(array $requestData = [], array $baseData = [])
    {
        $app = new TestApp();

        $request = new stdClass();
        foreach ($baseData as $key => $val) {
            $request->{$key} = $val;
        }

        $request->request = new stdClass();
        foreach ($requestData as $key => $val) {
            $request->request->{$key} = $val;
        }

        $app->setUri('/');
        $app->setHttpMethod('GET');
        $app->setJsonString(json_encode($request));

        return $app;
    }

}