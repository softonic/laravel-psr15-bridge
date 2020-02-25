<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Closure;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

class NextHandlerFactory
{
    public function getHandler(
        HttpFoundationFactory $httpFoundationFactory,
        PsrHttpFactory $psrHttpFactory,
        Request $request,
        Closure $next
    ): NextHandlerAdapter {
        return new NextHandlerAdapter(
            $httpFoundationFactory,
            $psrHttpFactory,
            $request,
            $next
        );
    }
}
