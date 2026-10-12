<?php

declare(strict_types=1);

use EchoFusion\Contracts\MiddlewareManager\MiddlewarePipelineInterface;
use EchoFusion\MiddlewareManager\MiddlewareManager;
use EchoFusion\MiddlewareManager\MiddlewareManagerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class MiddlewareManagerFactoryTest extends TestCase
{
    public function testInvokeReturnsMiddlewareManager()
    {
        $container = $this->createMock(ContainerInterface::class);
        $middlewarePipeline = $this->createMock(MiddlewarePipelineInterface::class);

        $container->method('get')
            ->willReturn($middlewarePipeline);

        $factory = new MiddlewareManagerFactory();
        $middlewareManager = $factory($container);

        $this->assertInstanceOf(MiddlewareManager::class, $middlewareManager);
    }

    public function testAnEntryThatIsNotAPipelineIsRejected(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->willReturn(new \stdClass());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');

        (new MiddlewareManagerFactory())($container);
    }
}
