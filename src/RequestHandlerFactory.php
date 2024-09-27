<?php

declare(strict_types=1);

namespace EchoFusion\MiddlewareManager;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webmozart\Assert\Assert;

class RequestHandlerFactory
{
    public function __invoke(ContainerInterface $container): RequestHandlerInterface
    {
        $responseFactory = $container->get(ResponseFactoryInterface::class);
        Assert::isInstanceOf($responseFactory, ResponseFactoryInterface::class);

        return new class($responseFactory) implements RequestHandlerInterface {
            public function __construct(private readonly ResponseFactoryInterface $responseFactory)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->responseFactory->createResponse();
            }
        };
    }
}
