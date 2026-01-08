Laravel PSR-15 Middleware Bridge
=====

[![Latest Version](https://img.shields.io/github/release/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://github.com/softonic/laravel-psr15-bridge/releases)
[![Software License](https://img.shields.io/badge/license-Apache%202.0-blue.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/softonic/laravel-psr15-bridge/tests.yml?branch=master&style=flat-square)](https://github.com/softonic/laravel-psr15-bridge/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/softonic/laravel-psr15-bridge.svg?style=flat-square)](https://packagist.org/packages/softonic/laravel-psr15-bridge)

This package provides a Laravel middleware bridge for [PSR-15][PSR-15] inspired in [jshannon63/laravel-psr15-middleware](https://github.com/jshannon63/laravel-psr15-middleware).

## Requirements

- PHP >= 8.5
- Laravel 12.x

Installation
-------

To install, use composer:

```
composer require softonic/laravel-psr15-bridge
```

You are ready to use it!

Usage
-------

The bridge adapter receive a [PSR-15][PSR-15] middleware via injection, so the bridge is transparent for Laravel and you can use
it as any other middleware.


Example based on [OpenApi Validation Middleware](https://github.com/hkarlstrom/openapi-validation-middleware):

Wrapping [OpenApi Validation Middleware](https://github.com/hkarlstrom/openapi-validation-middleware) within the bridge.
```php
// app/Providers/AppServiceProvider.php

use Softonic\Laravel\Middleware\Psr15Bridge\Psr15MiddlewareAdapter;

/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->bind(OpenApiValidation::class, function () {

        // Setup your PSR-15 middleware here
        $validator = new \HKarlstrom\Middleware\OpenApiValidation('schema.json');
    
        // Return it wrapped in the adapter to make Laravel accept it
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

Check [laravel middleware](https://laravel.com/docs/12.x/middleware) for more information.

How it works
------------

In the next diagram you can see the request and response flow.

![psr-15 bridge flow](doc/bridge_flow.png)

As you can see, when you execute `Psr15MiddlewareAdapter::adapt($validator);`, you are adding an envelop to the PSR-15
middleware that converts the request and response transparently for the middleware format Laravel expects.


Testing
-------

`softonic/laravel-psr15-bridge` has a [PHPUnit](https://phpunit.de) test suite and a coding style compliance test suite using [PHP CS Fixer](https://cs.symfony.com/).

To run the tests, run the following command from the project folder.

``` bash
$ docker compose run --rm test
```

To run PHPUnit only:

``` bash
$ docker compose run --rm phpunit
```

To check code style:

``` bash
$ docker compose run --rm php composer run phpcs
```

To fix code style issues:

``` bash
$ docker compose run --rm fixcs
```

License
-------

The Apache 2.0 license. Please see [LICENSE](LICENSE) for more information.

[PSR-15]: https://www.php-fig.org/psr/psr-15/
