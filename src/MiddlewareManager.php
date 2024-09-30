<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function in_array;
use function sprintf;

final class MiddlewareManager implements MiddlewareManagerInterface
{
    /**
     * @param class-string[] $coreMiddlewares
     * @param class-string[] $routeMiddlewares
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly MiddlewarePipelineInterface $middlewarePipeline,
        private readonly array $coreMiddlewares = [],
        private readonly array $routeMiddlewares = [],
    ) {
        $this->initialPipeline();
    }

    private function initialPipeline(): void
    {
        if (!$this->middlewarePipeline->isPipeLineEmpty()) {
            $this->middlewarePipeline->makePipelineEmpty();
        }

        foreach ($this->coreMiddlewares as $middlewareFQDN) {
            $this->middlewarePipeline->add(
                $this->container->get($middlewareFQDN)
            );
        }
    }

    public function add(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (!class_exists($middleware)) {
                throw new MiddlewareException(sprintf('Adding %s middleware is not a valid class', $middleware));
            }

            $this->middlewarePipeline->add(
                $this->container->get($middleware)
            );
        }
    }

    public function remove(string $middlewareFQDN): void
    {
        $this->middlewarePipeline->remove(
            $this->container->get($middlewareFQDN)
        );
    }

    public function dispatch(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->middlewarePipeline->process($request, $handler);
    }

    public function isRouteMiddlewareValid(string $middlewareFQDN): bool
    {
        return in_array($middlewareFQDN, $this->routeMiddlewares, true);
    }
}
