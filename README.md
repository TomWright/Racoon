# Racoon API Framework

[![Build Status](https://travis-ci.org/TomWright/Racoon.svg?branch=master)](https://travis-ci.org/TomWright/Racoon)
[![Total Downloads](https://poser.pugx.org/racoon/api/d/total.svg)](https://packagist.org/packages/racoon/api)
[![Latest Stable Version](https://poser.pugx.org/racoon/api/v/stable.svg)](https://packagist.org/packages/racoon/api)
[![Latest Unstable Version](https://poser.pugx.org/racoon/api/v/unstable.svg)](https://packagist.org/packages/racoon/api)
[![License](https://poser.pugx.org/racoon/api/license.svg)](https://packagist.org/packages/racoon/api)

Racoon is a basic API framework designed to make it quick and easy to put together a simple PHP API. [View the documentation][racoon-docs].

## Getting Started

### Autoloading
Racoon depends on autoloading provided by [Composer][composer]. If you do not use Composer then you will have to set up your own PSR autoloader or start using Composer.

### Quickstart

Create an application, add some routes and run the application.

index.php
```php
// Create an instance of Racoon
$app = new Racoon\Api\App();

$router = $app->getRouter();
$router->addRouteFile('/path/to/my_routes.php');

$app->run();
```

/path/to/my_routes.php
```php
$r->addRoute(['GET', 'POST'], '/users/list', '\\MyApp\\Users@list');
```

/MyApp/Users.php
```php
namespace MyApp;

class Users extends \Racoon\Api\Controller
{

    public function list()
    {
        $userList = [
            'Tom',
            'John',
            'Jess',
            'Jo',
        ];
        return $userList;
    }

}
```

## Routing
Racoon uses [racoon/router][racoon/router] to deal with routing.

### Defining where routes are stored

Routes need to be added in a routes file, which should be added to the `Router`.

```php
$router = $app->getRouter();

$router->addRouteFile('/path/to/some_routes.php');
```

If you want to store routes in multiple locations you can do it as follows.

```php
$router = $app->getRouter();

$router
    ->addRouteFile('/path/to/some_routes.php')
    ->addRouteFile('/path/to/more_routes.php')
    ->addRouteFile('/path/to/even_more_routes.php');
```

If you define multiple route locations, they will be included/added in the same order as you define them.

### Setting up routes
Inside one of the route files that have been added to the router you need to define your routes in the following format.

```php
$httpRequestMethod = ['GET', 'POST'];
$requestUri = '/users/list';
$handlerString = '\\MyApp\\Users@list';
$r->addRoute($httpRequestMethod, $requestUri, $handlerString);
```

#### Handler Namespaces

If like me, all of your application controllers are under one namespace, you can use the following method to simplify your routes file.

```php
$r->setHandlerNamespace('\\MyApp\\Controllers');
```

Before:
```php
$r->addRoute('GET', '/asd', '\\MyApp\\Controllers\\Example@one');
$r->addRoute('GET', '/fgh', '\\MyApp\\Controllers\\Example@two');
$r->addRoute('GET', '/jkl', '\\MyApp\\Controllers\\Example@three');
```

After:
```php
$r->setHandlerNamespace('\\MyApp\\Controllers');
$r->addRoute('GET', '/asd', '\\Example@one');
$r->addRoute('GET', '/fgh', '\\Example@two');
$r->addRoute('GET', '/jkl', '\\Example@three');
```

#### HTTP Request Method
The HTTP Request Method(s) that the route should match. This can be any HTTP request type such as `GET` or `POST`.

Can be a `string` or an `array` of `string`s.

#### Request URI
The request URI that the route should match.

You can define the request URI in multiple ways.

```php
'/users/list'
'/users/get/{userId}'
'/users/get/{userId:\d+}'
```

For more information see the [FastRoute Route Docs][fastroute-route-docs]

Any wildcards/placeholders defined here will be passed into the Controller/Handler method.

#### Handler String
The handler string defines the class and method that should be executed should the current request match a route.

The required format is `\MyApp\Users@list` where `\MyApp\Users` is the full class name including the namespace, and `list` is the method inside of that class which you want to be executed.

## Controllers
Your controllers should extend `\Racoon\Api\Controller`.

### The Request
When your `Controller` is first instantiated it won't have access to the `Request` object, however, you can create a `public function setupRequest()` which will be called directly after adding the `Request` to the `Controller`. This will then allow you to set up your `Controller` based on any parameters stored in the `Request`.

### Your Response
You shouldn't `echo` anything from your `Controller`. Instead, your method should `return` something and let Racoon deal with it.

#### Response Formatter
Racoon allows you choose how your response is to be formatted. By default the `\Racoon\Api\Response\Format\JsonFormatter` will be used.

You are free to use any Formatter you want, as long as it implements `\Racoon\Api\Response\Format\FormatterInterface`.

To use a different Formatter just do the following, where `\Racoon\Api\Response\Format\JsonFormatter` is your chosen Formatter.
```php
$formatter = new \Racoon\Api\Response\Format\JsonFormatter();
$app->setResponseFormatter($formatter);
```

## Returning Errors
In order to easily provide an error response back for a request all you need to do is throw an `\Racoon\Api\Exception`.

```php
throw new \Racoon\Api\Exception(
    Request $request = null, // Not required, but could be useful at another time.
    $displayAsError = false, // True to provide the $message back in the response. False if the framework shouldn't handle it.
    $message, // Your error message.
    $code = 0, // If this is not 0 and $displayAsError is True, the http response code will be set to this.
    \Exception $previous = null // If this is as a result of a previous Exception you can pass that in here.
);
```

If you extend `\Racoon\Api\Exception` however, you can simply things massively. To demonstrate this we can take a look at the `\Racoon\Api\AuthenticationException`.

```php
namespace Racoon\Api\Exception;

use Racoon\Api\Request;

class AuthenticationException extends Exception
{

    public function __construct(Request $request = null, $message, \Exception $previous = null)
    {
        parent::__construct($request, true, $message, 401, $previous);
    }

}
```

This means that we can display an error as follows.

```php
throw new \Racoon\Api\AuthenticationException(null, 'Missing API Key.');
```

## Authentication
By default Racoon doesn't authenticate any requests that come into your application, but it can be easily set up.

You can override the authentication method by setting a new `Authenticator` before running the app.

```php
$authenticator = new \Racoon\Api\Auth\ApiKeyAuthenticator();
$app->setAuthenticator($authenticator);
```

### Available Authenticators

#### NoAuthenticator
This `Authenticator` will allow any request through as it does no authentication.

```php
$authenticator = new \Racoon\Api\Auth\NoAuthenticator();
$app->setAuthenticator($authenticator);
```

#### ApiKeyAuthenticator
This `Authenticator` allows you to specify an array of valid API Keys which it will consider valid.

```php
$authenticator = new \Racoon\Api\Auth\ApiKeyAuthenticator();
$authenticator->setApiKeyName('api_key'); // Tells it to look under api_key to find the api key.
$authenticator->addValidApiKey('dsdasdasdasd'); // Add a valid API key.
$app->setAuthenticator($authenticator);
```

### Creating Custom Authenticators
You are free to create your own `Authenticator`, just make sure it implements `\Racoon\Api\Auth\AuthInterface`.

## Schema
The have the ability to create a `Schema` to easily validate incoming requests, as well as provide some basic documentation to the end user when they are trying out API requests.

A `Schema` is made up of one or more `Item`s and will be `valid` if all items pass their constraints.

The following should be run from a `Controller` and will set up and validate the `Schema`.
Username will need to be a string between 2 and 20 characters, and password will need to be a string with at least 6 characters.

```php
use Racoon\Api\Schema\Schema;
use Racoon\Api\Schema\Translator;

$schema = Schema::create([
    Translator::item('username')->isString(2, 20)->returnItem(),
    Translator::item('password')->isString(6)->returnItem(),
]);

$this->validateSchema($schema);
```

You can build more robust rules by doing something like this...
Username must be a string between 2 and 4 characters, OR between 10 and 12 characters.
```php
use Racoon\Api\Schema\Schema;
use Racoon\Api\Schema\Translator;

$schema = Schema::create([
    Translator::item('username')->isString(2, 4)->alt()->isString(10, 12)->returnItem(),
]);

$this->validateSchema($schema);
```

The validation is done using [TomWright/Validator][tomwright-validator] so for more information please see the related [GitHub page][tomwright-validator].

## Request
The `Request` object contains most of the information about the current request such as the request URI, the data provided in the request, as well as the current `Schema`. The `Request` object can also be accessed from your `Controller` using `$this->getRequest()`.

If for some reason you want to expand on the functionality of Racoon so as to store more data about the `Request` you can extend `\Racoon\Api\Request` and then tell Racoon to use your `Request` class instead.

```php
namespace MyApp;

class Request extends \Racoon\Api\Request
{
}

$app->setRequestClass('\\MyApp\\Request');
```

### Fetching Request Data
You can get the input data by using `$request->getRequestData()`, however there are also 2 helper methods to streamline things for you a little in the Controller.

```php
// Fetches the username from the input data, and if it doesn't exist it will return 'unknown'.
$request->getOptionalRequestData('username', 'unknown');

// Fetches the username from the input data.
// If it doesn't exist or has a length of 0, an error message will be shown to the client.
$request->getRequiredRequestData('username');
```

## Using the API
Using the API is simple.

All data is passed to Racoon as JSON in a `GET` or `POST` variable named `json`.

If you want to send data under a different name rather than `json`, you can do the following.

```php
$app->setJsonKeyName('new_json_key');
```

Let's say we have got things set up for `mydomain.com`, and we have a route that matches `/users/get` with a required item of `user_id`. If we were to make a `GET` request, the request should look something like this....

```
http://mydomain.com/users/get?json={"request":{"user_id": 5}}
```

Notice that the `request` is in it's own object inside of `json`. This allows you to separate authentication/test parameters from the actual data you want to use in your Controller.

E.g.

```
http://mydomain.com/users/get?json={"api_key": "easSdasesfWasd","request":{"user_id": 5}}
```

## Response
When working with Racoon you will always get a formatted response that looks something like the following (depending on which Response Formatter you use).

```json
{
    "success": false,
    "message": "Missing required field: asd",
    "schema": {
        "first_name": "first_name must be a string, be at least 3 characters in length."
    },
    "received": {
        "api_key": "qweqwe",
        "request": {
            "first_name": "Tom"
        }
    },
    "time_elapsed": "110.129",
    "response": null
}
```

### Success
True if no Exception was thrown. False if an Exception was thrown.

### Message
The message set by the Controller, or the Exception message.

### Schema
The Schema that was set up in the Controller.

### Received
The data that was sent to the API.

### Time Elapsed
The time (in ms) that it took for the API request to be processed.

### Response
The data that was returned by the Controller.

### HTTP Response Code
In addition to the above, if an `Exception` is thrown that is set up to return the error, the `code` of that `Exception` will be used to set the HTTP response code.


[racoon/router]: https://github.com/TomWright/RacoonRouter
[nikic/fast-route]: https://github.com/nikic/FastRoute
[fastroute-route-docs]: https://github.com/nikic/FastRoute#defining-routes
[composer]: https://getcomposer.org
[tomwright-validator]: https://github.com/TomWright/Validator
[racoon-docs]: https://rawgit.com/TomWright/Racoon/master/docs/api/index.html