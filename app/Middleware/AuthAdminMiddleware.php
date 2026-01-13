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
            $response = new Response();
            return $response
                ->withHeader('Location', '/ecommerce/public/admin/login')
                ->withStatus(302);
        }

        return $handler->handle($request);
    }
}