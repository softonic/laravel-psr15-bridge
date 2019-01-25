<?php

namespace Softonic\Laravel\Middleware\Psr15Bridge;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;

class NextHandlerAdapter implements RequestHandlerInterface
{
    /**
     * @var Request
     */
    private $foundationRequest;

    /**
     * @var Closure
     */
    private $next;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var HttpFoundationFactory
     */
    private $httpFoundationFactory;

    public function __construct(
        HttpFoundationFactory $httpFoundationFactory,
        DiactorosFactory $diactorosFactory,
        Request $foundationRequest,
        Closure $next
    ) {
        $this->diactorosFactory      = $diactorosFactory;
        $this->foundationRequest     = $foundationRequest;
        $this->next                  = $next;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    /**
     * Intercept communication between the PSR-15 middleware and the next middleware/controller.
     *
     * To allow the next execution we need to restore the request to a HttpFoundationRequest
     * and wait for a response that must be adapter to PSR-7 response to allow the
     * PSR-15 middleware process it.
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $psr7Request): ResponseInterface
    {
        $request  = $this->convertRequest($psr7Request, $this->foundationRequest);
        $response = ($this->next)($request);

        return $this->getPsr7Response($response);

    }

    private function convertRequest(ServerRequestInterface $psr7Request, $originalRequest): Request
    {
        $foundation_request = $this->httpFoundationFactory->createRequest($psr7Request);

        $originalRequest->query      = clone $foundation_request->query;
        $originalRequest->request    = clone $foundation_request->request;
        $originalRequest->attributes = clone $foundation_request->attributes;
        $originalRequest->cookies    = clone $foundation_request->cookies;
        $originalRequest->files      = clone $foundation_request->files;
        $originalRequest->server     = clone $foundation_request->server;
        $originalRequest->headers    = clone $foundation_request->headers;

        return $originalRequest;
    }

    /**
     * @param $response
     *
     * @return ResponseInterface|DiactorosFactory|\Zend\Diactoros\Response
     */
    protected function getPsr7Response($response)
    {
        return $this->diactorosFactory->createResponse($response);
    }
}

;
