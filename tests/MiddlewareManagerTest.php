<?php

declare(strict_types=1);

use EchoFusion\MiddlewareManager\MiddlewareException;
use EchoFusion\MiddlewareManager\MiddlewareManager;
use EchoFusion\MiddlewareManager\MiddlewarePipeline;
use EchoFusion\MiddlewareManager\MiddlewarePipelineInterface;
use EchoFusion\MiddlewareManager\Tests\Dummy\DummyMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MiddlewareManagerTest extends TestCase
{
    private ContainerInterface $container;

    private MiddlewarePipelineInterface $pipeline;

    private MiddlewareManager $middlewareManager;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->pipeline = $this->createMock(MiddlewarePipelineInterface::class);
        $this->middlewareManager = new MiddlewareManager($this->container, $this->pipeline);
    }

    public function testInitialPipeline()
    {
        $this->pipeline->method('isPipeLineEmpty')->willReturn(false);

        $this->pipeline->expects($this->once())->method('makePipelineEmpty');

        $this->container->method('get')->willReturn(new DummyMiddleware());

        $this->pipeline->expects($this->once())
            ->method('add')
            ->with(new DummyMiddleware());

        $this->middlewareManager = new MiddlewareManager($this->container, $this->pipeline, [DummyMiddleware::class]);
    }

    public function testAddValidMiddleware()
    {
        $this->pipeline->method('isPipeLineEmpty')->willReturn(true);
        $this->container->method('get')->willReturn(new DummyMiddleware());

        $this->pipeline->expects($this->once())->method('add')->with($this->isInstanceOf(DummyMiddleware::class));

        $this->middlewareManager->add([DummyMiddleware::class]);
    }

    public function testAddInvalidMiddleware()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Adding NonExistentMiddleware middleware is not a valid class');

        $this->middlewareManager->add(['NonExistentMiddleware']);
    }

    public function testRemoveMiddleware()
    {
        $this->pipeline->method('isPipeLineEmpty')->willReturn(true);
        $this->container->method('get')->willReturn(new DummyMiddleware());

        $this->pipeline->expects($this->once())->method('remove')->with($this->isInstanceOf(DummyMiddleware::class));
        $this->middlewareManager->remove(DummyMiddleware::class);
    }

    public function testDispatch()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $handler->method('handle')->willReturn($response);

        $this->container->method('get')->willReturn(new DummyMiddleware());

        $pipeline = new MiddlewarePipeline();
        $this->middlewareManager = new MiddlewareManager($this->container, $pipeline, [DummyMiddleware::class]);
        $pipeline->add(new DummyMiddleware());
        $result = $this->middlewareManager->dispatch($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testIsRouteMiddlewareValid()
    {
        $this->middlewareManager = new MiddlewareManager($this->container, $this->pipeline, [], ['validMiddleware']);

        $this->assertTrue($this->middlewareManager->isRouteMiddlewareValid('validMiddleware'));
        $this->assertFalse($this->middlewareManager->isRouteMiddlewareValid('invalidMiddleware'));
    }
}
