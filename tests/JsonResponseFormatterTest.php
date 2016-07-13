<?php

use Racoon\Api\Test\TestBase;
use Racoon\Router\RouteCollector;

class JsonResponseFormatterTest extends TestBase
{

    public function testControllerResponseIsJson()
    {
        $app = $this->getApp();
        $app->setUri('/hello');

        $app->getRouter()->addRouteCallable(function(RouteCollector $r) {
            $r->addRoute('GET', '/hello', '\\Racoon\\Api\\Test\\TestController@sayHello');
        });

        $json = $app->run();
        $this->assertTrue(is_string($json));

        $output = json_decode($json);
        $this->assertTrue(is_object($output));
    }

    
    public function testControllerResponseFormatterIsJson()
    {
        $app = $this->getApp();
        $this->assertTrue(is_a($app->getResponseFormatter(), '\\Racoon\\Api\\Response\\Format\\JsonFormatter'));
    }

}