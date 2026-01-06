<?php 
require_once("vendor/autoload.php");

$app = new \Slim\Slim();
$app->config('debug', true);

$app->get('/', function() {
    $sql = new Hcode\DB\Sql();
    // ATENÇÃO: Adicione "$results =" antes da chamada do select
    $results = $sql->select("SELECT * FROM tb_users");
    
    echo json_encode($results);
});

$app->run();
?>