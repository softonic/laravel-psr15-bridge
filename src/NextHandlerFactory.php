<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;

class NextHandlerFactory
{
    public function getHandler(
        HttpFoundationFactory $httpFoundationFactory,
        DiactorosFactory $diactorosFactory,
        Request $request,
        \Closure $next
    ) {
        return new NextHandlerAdapter(
            $httpFoundationFactory,
            $diactorosFactory,
            $request,
            $next
        );
    }
}
