<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

final class NextMiddleware implements RequestHandlerInterface
{
    private RequestHandlerInterface $fallbackHandler;

    /**
     * @var SplQueue<MiddlewareInterface>|null
     */
    private ?SplQueue $queue;

    public function __construct(SplQueue $queue, RequestHandlerInterface $fallbackHandler)
    {
        $this->queue = clone $queue;
        $this->fallbackHandler = $fallbackHandler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->queue === null) {
            throw new DomainException();
        }

        if ($this->queue->isEmpty()) {
            $this->queue = null;

            return $this->fallbackHandler->handle($request);
        }

        $middleware = $this->queue->dequeue();
        $next = clone $this;
        $this->queue = null;

        return $middleware->process($request, $next);
    }
}
