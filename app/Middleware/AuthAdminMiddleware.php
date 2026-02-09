<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AuthAdminMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        if (!isset($_SESSION['admin'])) {
            return (new Response())
                ->withHeader('Location', BASE_URL . '/admin/login')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}
