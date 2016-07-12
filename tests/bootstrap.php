<?php

define('ENVIRONMENT', 'phpunit');

require_once(__DIR__ . '/../vendor/autoload.php');

define('TEST_SRC_PATH', __DIR__ . '/../tests/src');

require_once TEST_SRC_PATH . '/TestApp.php';
require_once TEST_SRC_PATH . '/TestBase.php';
require_once TEST_SRC_PATH . '/TestController.php';