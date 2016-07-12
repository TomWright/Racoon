<?php


use Racoon\Api\App;
use Racoon\Api\Test\TestBase;

class DefaultAuthenticatorTest extends TestBase
{

    public function testDefaultValidatorValid()
    {
        $valid = true;
        $app = $this->getApp();
        try {
            $output = $app->run();
            if (strpos($output, 'Invalid API Key') !== false) {
                $valid = false;
            }
        } catch (AuthenticationException $e) {
            $valid = false;
        }
        $this->assertTrue($valid);
    }


    public function testDefaultAuthenticatorIsNoAuthenticator()
    {
        $app = $this->getApp();

        $authenticator = $app->getAuthenticator();
        $expectedAuthenticator = '\\Racoon\\Api\\Auth\\NoAuthenticator';

        $this->assertTrue(is_a($authenticator, $expectedAuthenticator));
    }

}