<?php


use Racoon\Api\Exception\InvalidJsonException;
use Racoon\Api\Test\TestBase;

class AppTest extends TestBase
{

    public function testDefaultJsonInputMethod()
    {
        $app = $this->getApp();
        $inputMethod = $app->getJsonInputMethod();
        $this->assertEquals($inputMethod, 'request');
    }

    public function testSettingJsonInputMethod()
    {
        $app = $this->getApp();

        $valid = true;
        try {
            $app->setJsonInputMethod('request');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $app->setJsonInputMethod('body');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);

        $valid = true;
        try {
            $app->setJsonInputMethod('url');
        } catch (InvalidJsonException $e) {
            $valid = false;
        }
        $this->assertFalse($valid);
    }

}