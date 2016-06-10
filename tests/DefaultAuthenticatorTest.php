<?php


use Racoon\Api\App;

class DefaultAuthenticatorTest extends PHPUnit_Framework_TestCase
{

    public function testDefaultAuthenticatorIsNoAuthenticator()
    {
        $app = new App();

        $authenticator = $app->getAuthenticator();
        $expectedAuthenticator = '\\Racoon\\Api\\Auth\\NoAuthenticator';

        $this->assertTrue(is_a($authenticator, $expectedAuthenticator));

        $app->createRequest();

        $this->assertTrue($authenticator->authenticate($app->getRequest()));
    }

}