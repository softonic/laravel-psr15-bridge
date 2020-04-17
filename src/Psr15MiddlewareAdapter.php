<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Closure;
use Illuminate\Http\Response;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;

class Psr15MiddlewareAdapter
{
    /**
     * @var MiddlewareInterface
     */
    private $psr15Middleware;

    /**
     * @var PsrHttpFactory
     */
    private $psrHttpFactory;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    /**
     * @var NextHandlerFactory
     */
    private $nextHandlerFactory;

    public function __construct(
        NextHandlerFactory $nextHandlerFactory,
        PsrHttpFactory $psrHttpFactory,
        HttpFoundationFactory $httpFoundationFactory,
        MiddlewareInterface $psr15Middleware
    ) {
        $this->psr15Middleware       = $psr15Middleware;
        $this->psrHttpFactory      = $psrHttpFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->nextHandlerFactory    = $nextHandlerFactory;
    }

    /**
     * Builder to do the class developer friendly.
     *
     * @param MiddlewareInterface $psr15Middleware
     *
     * @return Psr15MiddlewareAdapter
     */
    public static function adapt(MiddlewareInterface $psr15Middleware): Psr15MiddlewareAdapter
    {
        $psr17Factory = new Psr17Factory();

        return new self(
            new NextHandlerFactory(),
            new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory),
            new HttpFoundationFactory(),
            $psr15Middleware
        );
    }

    /**
     * Handle an incoming request.
     *
     * Transform current FoundationRequest to PSR-7 to allow the PSR-15 to process it and wait for their response
     * that will be adapted from PSR-7 to HttpFoundation to allow previous middleware to process it.
     *
     * @param Request $foundationRequest
     * @param Closure $next
     *
     * @return Response
     */
    public function handle(Request $foundationRequest, Closure $next): Response
    {
        $psr7Request = $this->getPsr7Request($foundationRequest);
        $nextAdapted = $this->getNextExecutionHandlerAdapter($foundationRequest, $next);

        $response = $this->psr15Middleware->process($psr7Request, $nextAdapted);

        return $this->getResponse($response);
    }

    /**
     * Hook the next execution handler to intercept it.
     *
     * The handler adapt the request and response to the needed objects
     * to allow PSR-15 and Laravel middleware executions.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return NextHandlerAdapter
     */
    private function getNextExecutionHandlerAdapter(Request $request, Closure $next): NextHandlerAdapter
    {
        return $this->nextHandlerFactory->getHandler(
            $this->httpFoundationFactory,
            $this->psrHttpFactory,
            $request,
            $next
        );
    }

    /**
     * Transform an HttpFoundation request to a PSR-7 request.
     *
     * @param Request $request
     *
     * @return ServerRequestInterface
     */
    protected function getPsr7Request(Request $request): ServerRequestInterface
    {
        return $this->psrHttpFactory->createRequest($request);
    }

    /**
     * Transform a PSR-7 response to a HttpFoundation response.
     *
     * @param ResponseInterface $psr7Response
     *
     * @return Response
     */
    protected function getResponse(ResponseInterface $psr7Response): Response
    {
        $response = new Response();
        $foundationResponse = $this->httpFoundationFactory->createResponse($psr7Response);

        foreach ($foundationResponse->headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        $response->setContent($foundationResponse->getContent());
        $response->setProtocolVersion($foundationResponse->getProtocolVersion());
        $response->setStatusCode($foundationResponse->getStatusCode());
        $response->setCharset($foundationResponse->getCharset() ?? '');

        foreach ($foundationResponse->headers->getCookies() as $cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }
}
