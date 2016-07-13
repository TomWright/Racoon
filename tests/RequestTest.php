<?php


use Racoon\Api\Exception\InvalidJsonException;
use Racoon\Api\Request;
use Racoon\Api\Test\TestBase;

class RequestTest extends TestBase
{

    public function testNoJsonGiven()
    {
        $request = new Request();

        $valid = true;
        try {
            $request->setRequestJson('');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);
    }


    public function testInvalidJsonGiven()
    {
        $request = new Request();

        $valid = true;
        try {
            $request->setRequestJson('{"something":"something"');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $valid = true;
        try {
            $request->setRequestJson('{something: something}');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);

        $valid = true;
        try {
            $request->setRequestJson('{something:"something"');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);
    }


    public function testValidJsonGiven()
    {
        $request = new Request();

        $valid = true;
        try {
            $request->setRequestJson('{"something":"something"}');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $request->setRequestJson('{}');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }


    public function testJsonStringIsSet()
    {
        $request = new Request();
        $json = '{"x":"y"}';
        $request->setRequestJson($json);
        $jsonString = $request->getJsonString();
        $this->assertEquals($json, $jsonString);
    }

}