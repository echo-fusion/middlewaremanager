<?php

declare(strict_types=1);

use EchoFusion\MiddlewareManager\NextMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class NextMiddlewareTest extends TestCase
{
    private SplQueue $queue;

    private RequestHandlerInterface $fallbackHandler;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->queue = new SplQueue();
        $this->fallbackHandler = $this->createMock(RequestHandlerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    public function testHandleWithEmptyQueueUsesFallbackHandler(): void
    {
        $this->fallbackHandler
            ->expects(self::once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($this->response);

        $nextMiddleware = new NextMiddleware($this->queue, $this->fallbackHandler);

        $result = $nextMiddleware->handle($this->request);

        self::assertSame($this->response, $result);
    }

    public function testHandleProcessesNextMiddleware(): void
    {
        $middleware = $this->createMock(MiddlewareInterface::class);

        $middleware
            ->expects(self::once())
            ->method('process')
            ->with($this->request, self::isInstanceOf(NextMiddleware::class))
            ->willReturn($this->response);

        $this->queue->enqueue($middleware);

        $nextMiddleware = new NextMiddleware($this->queue, $this->fallbackHandler);

        $result = $nextMiddleware->handle($this->request);

        self::assertSame($this->response, $result);
    }

    public function testHandleThrowsDomainExceptionWhenQueueIsNull(): void
    {
        $this->expectException(DomainException::class);

        $nextMiddleware = new NextMiddleware($this->queue, $this->fallbackHandler);
        $nextMiddleware->handle($this->request);

        $nextMiddleware->handle($this->request);
    }
}
