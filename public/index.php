<?php

/**
 * Front controller
 * 
 * PHP version 7.3.11
 */
require_once dirname(__DIR__) . "/vendor/autoload.php";

error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

$router = new Core\Router();

$router ->add('', ['controller' => 'Home', 'action' => 'index']);
$router ->add('{controller}/{action}');
$router ->add('{controller}/{id:\d+}/{action}');
//don't add / before admin
$router ->add('admin/{controller}/{action}', ['namespace' => 'Admin']);

$url = $_SERVER['QUERY_STRING'];

$router->dispatch($url);
/*
echo '<pre>';
var_dump($router->getRoutes());
echo '</pre>';
*/



