<?php

namespace Racoon\Api\Test;

use Racoon\Api\Controller;

class TestController extends Controller
{

    public function returnString()
    {
        return 'This is a string';
    }


    public function returnArray()
    {
        return ['This', 'is', 'an', 'array'];
    }


    public function returnInt()
    {
        return 123;
    }

}