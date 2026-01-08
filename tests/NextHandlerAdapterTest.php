<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NextHandlerAdapterTest extends TestCase
{
    #[Test]
    public function whenHandledItShouldAdaptTheRequestForNextMiddlewareAndResponseForThePrevious(): void
    {
        $psr7Request = $this->createStub(ServerRequestInterface::class);
        $psr7Response = $this->createStub(ResponseInterface::class);
        $request = new Request();
        $response = new Response();

        $httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        $httpFoundationFactory->expects($this->once())
            ->method('createRequest')
            ->with($psr7Request)
            ->willReturn(new Request());

        $psrFactory = $this->createMock(PsrHttpFactory::class);
        $psrFactory->expects($this->once())
            ->method('createResponse')
            ->with($response)
            ->willReturn($psr7Response);

        $next = function ($transformedRequest) use ($request, $response) {
            $this->assertSame($request, $transformedRequest);
            return $response;
        };

        $next = new NextHandlerAdapter(
            $httpFoundationFactory,
            $psrFactory,
            $request,
            $next
        );

        $nextResponse = $next->handle($psr7Request);
        $this->assertSame($psr7Response, $nextResponse);
    }
}
