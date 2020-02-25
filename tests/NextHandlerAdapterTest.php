<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class NextHandlerAdapterTest extends TestCase
{
    /**
     * @test
     */
    public function whenHandledItShouldAdaptTheRequestForNextMiddlewareAndResponseForThePrevious()
    {
        $psr7Request = $this->createMock(ServerRequestInterface::class);
        $psr7Response = $this->createMock(ResponseInterface::class);
        $request = new \Symfony\Component\HttpFoundation\Request();
        $response = new \Symfony\Component\HttpFoundation\Response();

        $httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        $httpFoundationFactory->expects($this->once())
            ->method('createRequest')
            ->with($psr7Request)
            ->willReturn(new \Symfony\Component\HttpFoundation\Request());

        $diactorosFactory = $this->createMock(PsrHttpFactory::class);
        $diactorosFactory->expects($this->once())
            ->method('createResponse')
            ->with($response)
            ->willReturn($psr7Response);

        $next = function ($transformedRequest) use ($request, $response) {
            $this->assertSame($request, $transformedRequest);
            return $response;
        };

        $next = new NextHandlerAdapter(
            $httpFoundationFactory,
            $diactorosFactory,
            $request,
            $next
        );

        $nextResponse = $next->handle($psr7Request);
        $this->assertSame($psr7Response, $nextResponse);
    }
}
