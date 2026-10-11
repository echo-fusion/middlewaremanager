# MiddlewareManager

This is a flexible package for managing middleware in PHP applications. It enables you to create a middleware pipeline, where middleware processes a request and passes it on to the next, providing a clean and organized way to handle middleware logic.

This package is compliant with PSR-1, PSR-2, PSR-7 and PSR-15.

## Installation

Install the package via Composer:

```bash
composer require echo-fusion/middlewaremanager
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.1
* PHP 8.2
* PHP 8.3

## Usage

Here’s how to use the MiddlewareManager to set up and run a middleware pipeline.

### 1. Define handler

Define your handlers in proper directory and pass one of them to middleware manager to run it at the end of the pipeline:

```php
use Psr\Http\Message\ResponseFactoryInterface
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MyMiddlewareHandler implements RequestHandlerInterface {
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // write your code here
        
        return $this->responseFactory->createResponse();
    }
}
```

### 2. Define middlewares

Define your middlewares in proper directory in separate files:

```php
use EchoFusion\MiddlewareManager\MiddlewareManager;
use EchoFusion\MiddlewareManager\MiddlewarePipeline;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // ex: manipulate request
        $request = $request->withAttribute('key','value');
        
        return $handler->handle($request);
    }
}

class OtherMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response =  $handler->handle($request);
        
        // ex: manipulate response
        return $this->convertedResponse($response);
    }
}

```

### 3. Run MiddlewareManager

To add middleware to the manager, you have two options:
- Add middleware as 'core middleware', which always runs before any middleware added later.
- Add middleware dynamically (e.g., useful for adding route-specific middleware).

Here is an example of how you can run MiddlewareManager:

Note: Don't forget to instantiate below injected dependencies in your container.

```php
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use EchoFusion\MiddlewareManager\MiddlewarePipelineInterface;

function (ContainerInterface $container, ServerRequestInterface $request, MiddlewarePipelineInterface $pipeline) {

    // optional
    $coreMiddlewares = [
        CoreMiddleware::class,
    ];
    
    $middlewareManager = new MiddlewareManager(
        $container, 
        $pipeline, 
        $coreMiddlewares
    );
    
    // add or remove your necessary middlewares if you want
    $middlewareManager->add(OtherMiddleware::class);
    $middlewareManager->remove(OtherMiddleware::class);
    // ...
    
    // instantiate handler directly or get from container to resolve possible dependencies
    $handler = new MyMiddlewareHandler();
    
    $response = $middlewareManager->dispatch($request, $handler);
    
    echo $response;
}
```

## Testing

Testing includes PHPUnit and PHPStan (Level 7).

``` bash
$ composer test
```

## Credits
Developed and maintained by [Amir Shadanfar](https://github.com/amir-shadanfar).  
Connect on [LinkedIn](https://www.linkedin.com/in/amir-shadanfar).

## License

The MIT License (MIT). Please see [License File](https://github.com/echo-fusion/middlewaremanager/blob/main/LICENSE) for more information.

