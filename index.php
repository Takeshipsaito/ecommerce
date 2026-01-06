<?php 
require_once("vendor/autoload.php");

use Slim\Slim;
use Hcode\Page;
use Hcode\pageadmin;

$app = new Slim();
$app->config('debug', true);

$app->get('/', function() {
    echo "";
    $page = new page();
    $page->setTpl("index");
});

$app->get('/admin', function() {
    echo "";
    $page = new pageadmin();
    $page->setTpl("index");
});

$app->run();
?>