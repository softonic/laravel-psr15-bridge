<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class Psr15MiddlewareAdapterTest extends TestCase
{
    #[Test]
    public function whenHandledItShouldAdaptTheRequestForNextMiddlewareAndResponseForThePrevious(): void
    {
        $psr7Request  = $this->createStub(ServerRequestInterface::class);
        $psr7Response = $this->createStub(ResponseInterface::class);
        $request      = new \Symfony\Component\HttpFoundation\Request();
        $response     = new \Symfony\Component\HttpFoundation\Response();

        $nextHandlerFactory = $this->createMock(NextHandlerFactory::class);
        $nextHandlerAdapter = $this->createMock(NextHandlerAdapter::class);
        $nextHandlerFactory->expects($this->once())
            ->method('getHandler')
            ->willReturn($nextHandlerAdapter);

        $httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        $httpFoundationFactory->expects($this->once())
            ->method('createResponse')
            ->with($psr7Response)
            ->willReturn($response);

        $psrFactory = $this->createMock(PsrHttpFactory::class);
        $psrFactory->expects($this->once())
            ->method('createRequest')
            ->with($request)
            ->willReturn($psr7Request);

        $psr15Middleware = new class () implements MiddlewareInterface {
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $handler->handle($request);
            }
        };

        $nextHandlerAdapter->expects($this->once())
            ->method('handle')
            ->willReturn($psr7Response);

        $psr15MiddlewareAdapter = new Psr15MiddlewareAdapter(
            $nextHandlerFactory,
            $psrFactory,
            $httpFoundationFactory,
            $psr15Middleware
        );
        $resultResponse         = $psr15MiddlewareAdapter->handle($request, function (Request $request, $next) {
            $this->assertTrue(
                false,
                'Never will be executed because the nextHandlerAdapter is mocked'
            );
        });
    }
}
