<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Middleware\AuthAdminMiddleware;
use App\Models\User;
use Hcode\PageAdmin;
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
| HOME E LOGIN
|--------------------------------------------------------------------------
*/
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('HOME');
    return $response;
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

/*
|--------------------------------------------------------------------------
| DASHBOARD E LOGOUT
|--------------------------------------------------------------------------
*/
$app->get('/admin', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/dashboard.twig');
})->add(new AuthAdminMiddleware());

$app->get('/admin/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', '/ecommerce/public/admin/login')->withStatus(302);
});

/*
|--------------------------------------------------------------------------
| USUÁRIOS - LISTAGEM
|--------------------------------------------------------------------------
*/
$app->get('/admin/users', function (Request $request, Response $response) use ($twig) {
    $users = User::listAll(); 
    return $twig->render($response, 'admin/users.twig', [
        'users' => $users
    ]); 
})->add(new AuthAdminMiddleware());

/*
|--------------------------------------------------------------------------
| USUÁRIOS - CRIAÇÃO
|--------------------------------------------------------------------------
*/
// CORRIGIDO: Agora ele mostra o formulário em vez de redirecionar
$app->get('/admin/users/create', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/users-create.twig');
})->add(new AuthAdminMiddleware());

// Rota para salvar o novo usuário
$app->post('/admin/users/create', function (Request $request, Response $response) {
    $data = (array)$request->getParsedBody();
    $user = new User();
    
    $data['inadmin'] = (isset($data['inadmin'])) ? 1 : 0;
    
    $user->setData($data);
    $user->save(); 

    // Redirecionamento após salvar
    return $response->withHeader('Location', '/ecommerce/public/admin/users')->withStatus(302);
})->add(new AuthAdminMiddleware());

/*
|--------------------------------------------------------------------------
| USUÁRIOS - EDIÇÃO E DELETE (ROTAS COM VARIÁVEIS POR ÚLTIMO)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| USUÁRIOS - EDIÇÃO
|--------------------------------------------------------------------------
*/
// Rota GET para abrir o formulário
$app->get('/admin/users/{id}', function (Request $request, Response $response, array $args) use ($twig) {
    $user = new User();
    $user->get((int)$args['id']);
    
    // Importante: verifique se o arquivo users-update.twig existe nesta pasta
    return $twig->render($response, 'admin/users-update.twig', [
        'user' => $user->getValues() 
    ]);
})->add(new AuthAdminMiddleware());

// Rota POST para salvar a alteração
$app->post('/admin/users/{id}', function (Request $request, Response $response, array $args) {
    $data = (array)$request->getParsedBody();
    
    $user = new User();
    $user->get((int)$args['id']);
    $user->setData($data);
    
    // Certifique-se de que o método update() no User.php usa a Procedure correta
    $user->update();

    return $response->withHeader('Location', '/ecommerce/public/admin/users')->withStatus(302);
})->add(new AuthAdminMiddleware());

/*
|--------------------------------------------------------------------------
| USUÁRIOS - EXCLUSÃO
|--------------------------------------------------------------------------
*/
$app->get('/admin/users/{id}/delete', function (Request $request, Response $response, array $args) {
    $user = new User();
    $user->get((int)$args['id']);
    $user->delete();

    return $response->withHeader('Location', '/ecommerce/public/admin/users')->withStatus(302);
})->add(new AuthAdminMiddleware());

$app->get('/admin/forgot', function (Request $request, Response $response) use ($twig) {

    $success = $_SESSION['success'] ?? null;
    $error   = $_SESSION['error'] ?? null;

    unset($_SESSION['success'], $_SESSION['error']);

    return $twig->render($response, 'admin/forgot.twig', [
        'success' => $success,
        'error'   => $error
    ]);
});


/*
|--------------------------------------------------------------------------
| ADMIN - ESQUECI A SENHA (POST)
|--------------------------------------------------------------------------
*/
$app->post('/admin/forgot', function (Request $request, Response $response) {

    $data = (array) $request->getParsedBody();

    try {

        if (!isset($data['email']) || $data['email'] === '') {
            throw new Exception('Informe um email válido.');
        }

        User::getForgot($data['email']);

        $_SESSION['success'] = 'Enviamos um email com instruções para redefinir sua senha.';

        return $response
            ->withHeader('Location', '/ecommerce/public/admin/forgot/sent')
            ->withStatus(302);

    } catch (Exception $e) {

        $_SESSION['error'] = $e->getMessage();

        return $response
            ->withHeader('Location', '/ecommerce/public/admin/forgot')
            ->withStatus(302);
    }
});


$app->get('/admin/forgot/sent', function (Request $request, Response $response) use ($twig) {

    $success = $_SESSION['success'] ?? null;

    if (!$success) {
        return $response
            ->withHeader('Location', '/ecommerce/public/admin/login')
            ->withStatus(302);
    }

    unset($_SESSION['success']);

    return $twig->render($response, 'admin/forgot-sent.twig', [
        'success' => $success
    ]);
});

$app->get('/admin/categories', function (
    Request $request,
    Response $response
) use ($twig) {

    $categories = \App\Models\Category::listAll();

    return $twig->render($response, 'admin/categories.twig', [
        'categories' => $categories
    ]);

})->add(new AuthAdminMiddleware());


$app->run();