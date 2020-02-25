<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

class NextHandlerFactory
{
    public function getHandler(
        HttpFoundationFactory $httpFoundationFactory,
        PsrHttpFactory $psrHttpFactory,
        Request $request,
        \Closure $next
    ) {
        return new NextHandlerAdapter(
            $httpFoundationFactory,
            $psrHttpFactory,
            $request,
            $next
        );
    }
}
