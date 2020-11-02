Laravel PSR-15 Middleware Bridge
=====

[![Latest Version](https://img.shields.io/github/release/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://github.com/softonic/laravel-psr15-bridge/releases)
[![Software License](https://img.shields.io/badge/license-Apache%202.0-blue.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/softonic/laravel-psr15-bridge/master.svg?style=flat-square)](https://travis-ci.org/softonic/laravel-psr15-bridge)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://scrutinizer-ci.com/g/softonic/laravel-psr15-bridge/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://scrutinizer-ci.com/g/softonic/laravel-psr15-bridge)
[![Total Downloads](https://img.shields.io/packagist/dt/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://packagist.org/packages/softonic/laravel-psr15-bridge)

This package provides a Laravel middleware bridge for [PSR-15](https://www.php-fig.org/psr/psr-15/) inspired in [jshannon63/laravel-psr15-middleware](https://github.com/jshannon63/laravel-psr15-middleware).

Installation
-------

To install, use composer:

```
composer require softonic/laravel-psr15-bridge
```

You are ready to use it!

Usage
-------

The bridge adapter receive a PSR-15 middleware via injection, so the bridge is transparent for Laravel and you can use
it as any other middleware.


Example based on [OpenApi Validation Middleware](https://github.com/hkarlstrom/openapi-validation-middleware):

Wrapping [OpenApi Validation Middleware](https://github.com/hkarlstrom/openapi-validation-middleware) within the bridge.
```php
// app/Providers/AppServiceProvider.php

use HKarlstrom\Middleware\OpenApiValidation;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->bind(OpenApiValidation::class, function () {
        $validator = new \HKarlstrom\Middleware\OpenApiValidation('schema.json');
    
        return Psr15MiddlewareAdapter::adapt($validator);
    });
}
```

Now you can use it anywhere or for example generate an alias.

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    ...
    'openapi-validation' => OpenApiValidation::class,
];
```

Check [laravel middleware](https://laravel.com/docs/5.7/middleware) for more information.

How it works
------------

In the next diagram you can see the request and response flow.

![psr-15 bridge flow](doc/bridge_flow.png)

As you can see, when you execute `Psr15MiddlewareAdapter::adapt($validator);`, you are adding an envelop to the psr-15
middleware that converts the request and response transparently for the middleware and the laravel itself.


Testing
-------

`softonic/laravel-psr15-bridge` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](http://cs.sensiolabs.org/).

To run the tests, run the following command from the project folder.

``` bash
$ docker-compose run test
```

License
-------

The Apache 2.0 license. Please see [LICENSE](LICENSE) for more information.

[PSR-2]: http://www.php-fig.org/psr/psr-2/
[PSR-4]: http://www.php-fig.org/psr/psr-4/
