<?php

declare(strict_types=1);

use EchoFusion\MiddlewareManager\MiddlewarePipeline;
use EchoFusion\MiddlewareManager\NextMiddleware;
use EchoFusion\MiddlewareManager\Tests\Dummy\DummyMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewarePipelineTest extends TestCase
{
    private MiddlewarePipeline $pipeline;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    private RequestHandlerInterface $handler;

    protected function setUp(): void
    {
        $this->pipeline = new MiddlewarePipeline();
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
    }

    public function testPipelineIsInitiallyEmpty(): void
    {
        self::assertTrue($this->pipeline->isPipeLineEmpty());
    }

    public function testAddMiddlewareToPipeline(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $this->pipeline->add($middleware);

        self::assertFalse($this->pipeline->isPipeLineEmpty());
    }

    public function testRemoveMiddlewareFromPipeline(): void
    {
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $this->pipeline->add($middleware1);
        $this->pipeline->add($middleware2);

        // Remove one middleware and check if the pipeline still contains the other
        $this->pipeline->remove($middleware1);

        $this->pipeline->process($this->request, $this->handler);

        self::assertFalse($this->pipeline->isPipeLineEmpty());
    }

    public function testRemoveMiddlewareLeavesPipelineEmpty(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->pipeline->add($middleware);
        $this->pipeline->remove($middleware);

        self::assertTrue($this->pipeline->isPipeLineEmpty());
    }

    public function testProcessMiddlewarePipeline(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $middleware
            ->expects(self::once())
            ->method('process')
            ->with($this->request, self::isInstanceOf(NextMiddleware::class))
            ->willReturn($this->response);

        $this->pipeline->add($middleware);

        $result = $this->pipeline->process($this->request, $this->handler);

        self::assertSame($this->response, $result);
    }

    public function testClonePipelineClonesQueue(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);
        $this->pipeline->add($middleware);

        $clonedPipeline = clone $this->pipeline;

        // Ensure that both original and cloned pipelines have the same state
        self::assertFalse($clonedPipeline->isPipeLineEmpty());
        self::assertFalse($this->pipeline->isPipeLineEmpty());
    }

    public function testMakePipelineEmpty()
    {
        $middlewarePipeline = new MiddlewarePipeline();

        $middlewarePipeline->add(new DummyMiddleware());

        $middlewarePipeline->makePipelineEmpty();

        $this->assertTrue(
            $middlewarePipeline->isPipeLineEmpty(),
            'The pipeline should be empty after calling makePipelineEmpty'
        );
    }
}
