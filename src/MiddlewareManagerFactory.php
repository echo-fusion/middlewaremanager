<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use EchoFusion\Contracts\MiddlewareManager\MiddlewareManagerInterface;
use EchoFusion\Contracts\MiddlewareManager\MiddlewarePipelineInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use function sprintf;

class MiddlewareManagerFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareManagerInterface
    {
        $middlewarePipeline = $container->get(MiddlewarePipelineInterface::class);
        if (!$middlewarePipeline instanceof MiddlewarePipelineInterface) {
            throw new InvalidArgumentException(sprintf('The container entry %s must implement %s.', MiddlewarePipelineInterface::class, MiddlewarePipelineInterface::class));
        }

        return new MiddlewareManager($container, $middlewarePipeline);
    }
}
