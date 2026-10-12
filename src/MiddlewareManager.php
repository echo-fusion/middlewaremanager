<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use EchoFusion\Contracts\MiddlewareManager\MiddlewareManagerInterface;
use EchoFusion\Contracts\MiddlewareManager\MiddlewarePipelineInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function in_array;
use function is_subclass_of;
use function sprintf;

final class MiddlewareManager implements MiddlewareManagerInterface
{
    /**
     * The middleware objects this manager put in the pipeline, by class, so remove() can take out the same objects.
     *
     * @var array<class-string, list<MiddlewareInterface>>
     */
    private array $added = [];

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
            $this->addToPipeline($middlewareFQDN);
        }
    }

    public function add(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            if (!class_exists($middleware)) {
                throw new MiddlewareException(sprintf('Adding %s middleware is not a valid class', $middleware));
            }

            $this->addToPipeline($middleware);
        }
    }

    /**
     * Adds a stand-in that builds the real middleware from the container when a request reaches it.
     *
     * @param class-string $middlewareFQDN
     *
     * @throws MiddlewareException when the class is not a PSR-15 middleware
     */
    private function addToPipeline(string $middlewareFQDN): void
    {
        if (!is_subclass_of($middlewareFQDN, MiddlewareInterface::class)) {
            throw new MiddlewareException(sprintf('%s is not a PSR-15 middleware (it must implement %s).', $middlewareFQDN, MiddlewareInterface::class));
        }

        $middleware = new LazyMiddleware($this->container, $middlewareFQDN);

        $this->middlewarePipeline->add($middleware);
        $this->added[$middlewareFQDN][] = $middleware;
    }

    public function remove(string $middlewareFQDN): void
    {
        // the container may build a new object on every get(), so remove the objects that were added
        foreach ($this->added[$middlewareFQDN] ?? [] as $middleware) {
            $this->middlewarePipeline->remove($middleware);
        }

        unset($this->added[$middlewareFQDN]);
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
