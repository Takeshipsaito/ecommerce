<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use App\Middleware\AuthAdminMiddleware;
use \Hcode\Model\User;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../app/Views', ['cache' => false]);

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

/*
|--------------------------------------------------------------------------
| Rotas públicas
|--------------------------------------------------------------------------
*/
$app->get('/', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'site/home.twig');
});

/*
|--------------------------------------------------------------------------
| Login admin (SEM middleware)
|--------------------------------------------------------------------------
*/
$app->get('/admin/login[/]', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/login.twig');
});

$app->post('/admin/login', function (Request $request, Response $response) {

    $data = (array)$request->getParsedBody();

    if (
        $data['email'] === 'admin@admin.com' &&
        $data['password'] === '123456'
    ) {
        $_SESSION['admin'] = true;

        return $response
            ->withHeader('Location', '/admin')
            ->withStatus(302);
    }

    return $response
        ->withHeader('Location', '/admin/login')
        ->withStatus(302);
});

/*
|--------------------------------------------------------------------------
| Área administrativa (COM middleware)
|--------------------------------------------------------------------------
*/
$app->get('/admin[/]', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/layout.twig');
})->add(new AuthAdminMiddleware());

$app->run();
