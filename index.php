<?php

use Router\Router;
use Symfony\Component\Yaml\Yaml;

session_start();

require __DIR__ . '/vendor/autoload.php';

$config = Yaml::parse(file_get_contents('config/config.yml'));

$loader = new Twig_Loader_Filesystem('views/');
$twig = new Twig_Environment($loader, array(
    // 'cache' => 'cache/twig/',
    'cache' => false,
    'debug' => true,
));
$twig->addExtension(new Twig_Extension_Debug());

$bundles = array(
    // ...
    new WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle(),// register bundle
);


$router = new Router($config['routes'], $twig);
if (!empty($_GET['action']))
    $router->callAction($_GET['action']);
else
    $router->callAction($config['defaut_route']);
