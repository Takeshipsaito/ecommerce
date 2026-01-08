<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;

class AuthAdminMiddleware
{
    public function __invoke(Request $request, RequestHandlerInterface $handler): Response
    {
        if (!isset($_SESSION['admin'])) {
            $response = new SlimResponse();
            return $response
                ->withHeader('Location', '/admin/login')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}

