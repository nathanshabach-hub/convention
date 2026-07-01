<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Http\LegacyServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LegacyRequestMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$request instanceof LegacyServerRequest) {
            $request = LegacyServerRequest::fromRequest($request);
        }

        return $handler->handle($request);
    }
}