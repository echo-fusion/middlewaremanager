<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function sprintf;

/**
 * Stands in for a middleware and builds the real one from the container only when a request reaches it,
 * so middleware that is never reached (a request answered earlier in the pipeline) costs nothing.
 */
final class LazyMiddleware implements MiddlewareInterface
{
    private ?MiddlewareInterface $middleware = null;

    /**
     * @param class-string<MiddlewareInterface> $class
     */
    public function __construct(private readonly ContainerInterface $container, private readonly string $class)
    {
    }

    /**
     * @return class-string<MiddlewareInterface>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->resolve()->process($request, $handler);
    }

    /**
     * @throws MiddlewareException when the container does not return a PSR-15 middleware
     */
    private function resolve(): MiddlewareInterface
    {
        if ($this->middleware !== null) {
            return $this->middleware;
        }

        $middleware = $this->container->get($this->class);
        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException(sprintf('%s is not a PSR-15 middleware (it must implement %s).', $this->class, MiddlewareInterface::class));
        }

        return $this->middleware = $middleware;
    }
}
