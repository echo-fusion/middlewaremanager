<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class MiddlewareManagerFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareManagerInterface
    {
        $middlewarePipeline = $container->get(MiddlewarePipelineInterface::class);
        Assert::isInstanceOf($middlewarePipeline, MiddlewarePipelineInterface::class);

        return new MiddlewareManager($container, $middlewarePipeline);
    }
}
