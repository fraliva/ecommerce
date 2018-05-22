<?php 

session_start();

// Configurado com o composer
require_once("vendor/autoload.php");

use \Slim\Slim;

// Chamada para uma nova aplicação do slim
$app = new Slim();

// Debug configurado para mostrar todos os erros detalhados
$app->config('debug', true);

require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run();

 ?>