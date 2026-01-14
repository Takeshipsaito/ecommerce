<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\AuthAdminMiddleware;
use App\Models\User;
use App\Models\Category;

require __DIR__ . '/../vendor/autoload.php';

session_start();

$app = AppFactory::create();

$app->setBasePath('/ecommerce/public');

$twig = Twig::create(__DIR__ . '/../app/Views', [
    'cache' => false
]);

$app->add(TwigMiddleware::create($app, $twig));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

/*
|--------------------------------------------------------------------------
| LOGIN E LOGOUT
|--------------------------------------------------------------------------
*/
$app->get('/', function (Request $request, Response $response) {
    return $response->withHeader('Location', '/ecommerce/public/admin')->withStatus(302);
});

$app->get('/admin/login', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/login.twig');
});

$app->post('/admin/login', function (Request $request, Response $response) {
    $data = (array) $request->getParsedBody();
    if ($data['email'] === 'admin@admin.com' && $data['password'] === '123456') {
        $_SESSION['admin'] = true;
        return $response->withHeader('Location', '/ecommerce/public/admin')->withStatus(302);
    }
    return $response->withHeader('Location', '/ecommerce/public/admin/login')->withStatus(302);
});

$app->get('/admin/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', '/ecommerce/public/admin/login')->withStatus(302);
});

/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/
$app->get('/admin', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/dashboard.twig');
})->add(new AuthAdminMiddleware());

/*
|--------------------------------------------------------------------------
| CATEGORIAS
|--------------------------------------------------------------------------
*/

// Listagem de Categorias
$app->get('/admin/categories', function (Request $request, Response $response) use ($twig) {
    $categories = Category::listAll();
    return $twig->render($response, 'admin/categories.twig', [
        'categories' => $categories
    ]);
})->add(new AuthAdminMiddleware());

// Tela de Criar Categoria (GET)
$app->get('/admin/categories/create', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/categories-create.twig');
})->add(new AuthAdminMiddleware());

// Salvar Categoria (POST)
$app->post('/admin/categories/create', function (Request $request, Response $response) {
    $data = (array)$request->getParsedBody();
    $category = new Category();
    $category->setData($data);
    $category->save(); 
    return $response->withHeader('Location', '/ecommerce/public/admin/categories')->withStatus(302);
})->add(new AuthAdminMiddleware());

/*
|--------------------------------------------------------------------------
| USUÁRIOS (RESUMIDO)
|--------------------------------------------------------------------------
*/
$app->get('/admin/users', function (Request $request, Response $response) use ($twig) {
    $users = User::listAll(); 
    return $twig->render($response, 'admin/users.twig', ['users' => $users]); 
})->add(new AuthAdminMiddleware());

$app->run();