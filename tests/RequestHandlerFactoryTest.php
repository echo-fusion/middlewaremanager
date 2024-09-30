<?php

declare(strict_types=1);

use EchoFusion\MiddlewareManager\RequestHandlerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webmozart\Assert\InvalidArgumentException;

final class RequestHandlerFactoryTest extends TestCase
{
    private ContainerInterface $container;

    private ResponseFactoryInterface $responseFactory;

    private ServerRequestInterface $request;

    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
    }

    public function testInvokeReturnsRequestHandler(): void
    {
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(ResponseFactoryInterface::class)
            ->willReturn($this->responseFactory);

        $this->responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->willReturn($this->response);

        $factory = new RequestHandlerFactory();
        $handler = $factory($this->container);

        self::assertInstanceOf(RequestHandlerInterface::class, $handler);
        self::assertSame($this->response, $handler->handle($this->request));
    }

    public function testInvokeThrowsExceptionForInvalidResponseFactory(): void
    {
        $this->container
            ->expects(self::once())
            ->method('get')
            ->with(ResponseFactoryInterface::class)
            ->willReturn(new \stdClass()); // Return an invalid object type

        $this->expectException(InvalidArgumentException::class);

        $factory = new RequestHandlerFactory();
        $factory($this->container);
    }
}
