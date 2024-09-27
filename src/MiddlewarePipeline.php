<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

class MiddlewarePipeline implements MiddlewareInterface, MiddlewarePipelineInterface
{
    /**
     * @var SplQueue<MiddlewareInterface>
     */
    private SplQueue $pipeline;

    public function __construct()
    {
        $this->pipeline = new SplQueue();
    }

    public function __clone()
    {
        $this->pipeline = clone $this->pipeline;
    }

    public function isPipeLineEmpty(): bool
    {
        return $this->pipeline->isEmpty();
    }

    public function add(MiddlewareInterface $middleware): void
    {
        $this->pipeline->enqueue($middleware);
    }

    public function remove(MiddlewareInterface $middleware): void
    {
        $tempQueue = new SplQueue();

        while (!$this->isPipeLineEmpty()) {
            $item = $this->pipeline->dequeue();
            if ($item !== $middleware) {
                $tempQueue->enqueue($item);
            }
        }

        $this->pipeline = $tempQueue;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $next = new NextMiddleware($this->pipeline, $handler);

        return $next->handle($request);
    }
}
