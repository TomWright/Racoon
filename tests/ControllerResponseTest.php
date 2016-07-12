<?php

use Racoon\Api\Test\TestBase;
use Racoon\Router\RouteCollector;

class ControllerResponseTest extends TestBase
{

    public function testControllerResponseMatch()
    {
        $app = $this->getApp();
        $app->setUri('/hello');

        $app->getRouter()->addRouteCallable(function(RouteCollector $r) {
            $r->addRoute('GET', '/hello', '\\Racoon\\Api\\Test\\TestController@sayHello');
        });

        $json = $app->run();

        $output = json_decode($json);

        $valid = $output->response == 'hello';

        $this->assertTrue($valid);
    }


    public function testControllerResponseTypeString()
    {
        $app = $this->getApp();
        $app->setUri('/test');

        $app->getRouter()->addRouteCallable(function(RouteCollector $r) {
            $r->addRoute('GET', '/test', '\\Racoon\\Api\\Test\\TestController@returnString');
        });

        $json = $app->run();

        $output = json_decode($json);

        $valid = is_string($output->response);

        $this->assertTrue($valid);
    }


    public function testControllerResponseTypeArray()
    {
        $app = $this->getApp();
        $app->setUri('/test');

        $app->getRouter()->addRouteCallable(function(RouteCollector $r) {
            $r->addRoute('GET', '/test', '\\Racoon\\Api\\Test\\TestController@returnArray');
        });

        $json = $app->run();

        $output = json_decode($json);

        $valid = is_array($output->response);

        $this->assertTrue($valid);
    }


    public function testControllerResponseTypeInt()
    {
        $app = $this->getApp();
        $app->setUri('/test');

        $app->getRouter()->addRouteCallable(function(RouteCollector $r) {
            $r->addRoute('GET', '/test', '\\Racoon\\Api\\Test\\TestController@returnInt');
        });

        $json = $app->run();

        $output = json_decode($json);

        $valid = is_int($output->response);

        $this->assertTrue($valid);
    }

}