<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Models\Category;
use App\Models\User;
use App\Middleware\AuthAdminMiddleware;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('BASE_URL', '/ecommerce/public');
session_set_cookie_params([
    'path' => BASE_URL
]);

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

// Funções Auxiliares
function sendRecoveryEmail($email, $link) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'takeshipereira25@gmail.com';
        $mail->Password = 'ktzw xjav ysag dnif';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('takeshipereira25@gmail.com', 'Ecommerce');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Recuperação de senha';
        $mail->Body = "<h3>Recuperação de senha</h3><p>Clique no link abaixo:</p><a href='$link'>$link</a>";
        $mail->send();
    } catch (Exception $e) {
        error_log($mail->ErrorInfo);
    }
}

$app = AppFactory::create();
$app->setBasePath(BASE_URL);

$twig = Twig::create(__DIR__ . '/../app/Views', ['cache' => false]);
$twig->getEnvironment()->addGlobal('BASE_URL', BASE_URL);

$app->add(TwigMiddleware::create($app, $twig));
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

// --- ROTAS DE LOGIN E LOGOUT ---

$app->get('/admin/login', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/login.twig');
});

$app->post('/admin/login', function (Request $request, Response $response) {
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if ($email === 'admin@admin.com' && $password === '123456') {
        
        $_SESSION['admin'] = true; 
        
        return $response
            ->withHeader('Location', BASE_URL . '/admin')
            ->withStatus(302);
    }
    return $response
        ->withHeader('Location', BASE_URL . '/admin/login')
        ->withStatus(302);
});

$app->get('/admin/logout', function (Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', BASE_URL . '/admin/login')->withStatus(302);
});

// --- ROTAS PROTEGIDAS (DASHBOARD, USERS, CATEGORIES) ---

$app->get('/admin', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/dashboard.twig');
})->add(new AuthAdminMiddleware());

// Categorias
$app->get('/admin/categories', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/categories.twig', ['categories' => Category::listAll()]);
})->add(new AuthAdminMiddleware());

$app->get('/admin/categories/create', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/categories-create.twig');
})->add(new AuthAdminMiddleware());

$app->post('/admin/categories/create', function (Request $request, Response $response) {
    $category = new Category();
    $category->setData($request->getParsedBody());
    $category->save();
    return $response->withHeader('Location', BASE_URL . '/admin/categories')->withStatus(302);
})->add(new AuthAdminMiddleware());

$app->get('/admin/categories/{id}/delete', function (Request $request, Response $response, array $args) {
    $category = new Category();
    $category->get((int) $args['id']);
    $category->delete();
    return $response->withHeader('Location', BASE_URL . '/admin/categories')->withStatus(302);
})->add(new AuthAdminMiddleware());

// Usuários
$app->get('/admin/users', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/users.twig', ['users' => User::listAll()]);
})->add(new AuthAdminMiddleware());

$app->get('/admin/users/create', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response, 'admin/users-create.twig');
})->add(new AuthAdminMiddleware());

$app->post('/admin/users/create', function (Request $request, Response $response) {
    $user = new User();
    $user->setData($request->getParsedBody());
    $user->save();
    return $response->withHeader('Location', BASE_URL . '/admin/users')->withStatus(302);
})->add(new AuthAdminMiddleware());

$app->get('/admin/users/{id}/edit', function (Request $request, Response $response, array $args) use ($twig) {
    $user = new User();
    $user->get((int) $args['id']);
    return $twig->render($response, 'admin/users-update.twig', ['user' => $user->getValues()]);
})->add(new AuthAdminMiddleware());

$app->post('/admin/users/{id}/edit', function (Request $request, Response $response, array $args) {
    $data = $request->getParsedBody();
    $user = new User();
    $user->get((int)$args['id']);
    $data['iduser'] = (int)$args['id'];
    $data['inadmin'] = isset($data['inadmin']) ? 1 : 0;
    $user->setData($data);
    $user->update();
    return $response->withHeader('Location', BASE_URL . '/admin/users')->withStatus(302);
});

$app->get('/admin/users/{id}/delete', function (Request $request, Response $response, array $args) {
    $user = new User();
    $user->get((int) $args['id']);
    $user->delete();
    return $response->withHeader('Location', BASE_URL . '/admin/users')->withStatus(302);
})->add(new AuthAdminMiddleware());

// --- ESQUECI A SENHA ---

$app->get('/admin/forgot', function (Request $request, Response $response) use ($twig) {
    return $twig->render($response,'admin/forgot.twig');
});

$app->post('/admin/forgot', function (Request $request, Response $response) use ($twig) {
    $data = $request->getParsedBody();
    if (empty($data['email'])) {
        return $twig->render($response,'admin/forgot.twig',['error'=>'Informe um e-mail válido']);
    }
    $user = new User();
    $user->loadByEmail($data['email']);
    if ($user->getiduser()) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $user->setreset_token($token);
        $user->setreset_expires($expires);
        $user->update();
        $link = "http://localhost" . BASE_URL . "/admin/forgot/reset/" . $token;
        sendRecoveryEmail($data['email'], $link);
    }
    return $twig->render($response,'admin/forgot.twig',['success'=>'Se o e-mail existir, enviaremos um link']);
});

$app->get('/admin/forgot/reset/{token}', function ($request,$response,$args) use ($twig){
    $user = new User();
    $user->loadByToken($args['token']);
    if(!$user->getiduser() || strtotime($user->getreset_expires()) < time()){
        $response->getBody()->write("Token inválido ou expirado");
        return $response;
    }
    return $twig->render($response,'admin/forgot-reset.twig',['token'=>$args['token']]);
});

$app->post('/admin/forgot/reset', function ($request,$response){
    $data = $request->getParsedBody();
    $user = new User();
    $user->loadByToken($data['token']);
    if(!$user->getiduser()){
        $response->getBody()->write("Token inválido");
        return $response;
    }
    $user->setdespassword(password_hash($data['password'], PASSWORD_DEFAULT));
    $user->setreset_token(null);
    $user->setreset_expires(null);
    $user->update();
    return $response->withHeader('Location', BASE_URL.'/admin/login')->withStatus(302);
});

$app->run();