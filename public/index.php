<?php

$autoloadPath1 = __DIR__ . '/../../../autoload.php';
$autoloadPath2 = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoloadPath1)) {
    require_once $autoloadPath1;
} else {
    require_once $autoloadPath2;
}

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

$app = AppFactory::create();
$renderer = new PhpRenderer(__DIR__ . '/../templates');

$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) use ($renderer) {
    return $renderer->render($response, 'index.phtml');
});

$app->run();
