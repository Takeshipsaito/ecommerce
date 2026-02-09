<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

echo "PHP rodando<br>";

require_once __DIR__ . '/../vendor/autoload.php';

echo "autoload carregado<br>";

echo "<pre>";
print_r(get_declared_classes());
