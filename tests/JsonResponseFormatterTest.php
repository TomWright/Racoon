<?php

use Racoon\Api\Exception\ResponseFormattingException;
use Racoon\Api\Response\Format\JsonFormatter;
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


    public function testFormatterResult()
    {
        $formatter = new JsonFormatter();
        $response = new stdClass();
        $response->x = 'This is X';
        $formatted = $formatter->format($response);

        $this->assertTrue(is_string($formatted));

        $decoded = json_decode($formatted);
        $this->assertTrue(is_object($decoded));

        $this->assertTrue($response == $decoded);
    }


    public function testJsonEncodeFailure()
    {
        $formatter = new JsonFormatter();
        $response = new stdClass();
        $response->x = "\xB1\x31";

        $encoded = true;
        try {
            $formatter->format($response);
        } catch (ResponseFormattingException $e) {
            $encoded = false;
        }
        $this->assertFalse($encoded);
    }

}