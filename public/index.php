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
use Slim\Flash\Messages;
use Hexlet\Code\Connection;
use Hexlet\Code\Query;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

session_start();

Valitron\Validator::lang('ru');

$app = AppFactory::create();
$renderer = new PhpRenderer(__DIR__ . '/../templates');
$router = $app->getRouteCollector()->getRouteParser();
$flash = new Messages();

$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) use ($renderer) {
    $params = [
        'url' => [],
        'errors' => []
    ];
    return $renderer->render($response, 'index.phtml', $params);
});

//get /urls
$app->get('/urls', function ($request, $response) use ($renderer, $router) {
    $connection = Connection::get()->connect();
    $pdo = new Query($connection);
    $data = $pdo->getUrls();
    $pageUrl = $router->urlFor('urls');
    $params = [
        'urls' => $data,
        'pageUrl' => $pageUrl
    ];
    return $renderer->render($response, 'urls/index.phtml', $params);
})->setName('urls');

//get /urls/id
$app->get('/urls/{id}', function ($request, $response, $args) use ($router, $renderer, $flash) {
    $id = htmlspecialchars($args['id']);
    $connection = Connection::get()->connect();
    $pdo = new Query($connection);
    if ($pdo->isId($id)) {
        [$data] = $pdo->getUrl($id);
        $messages = $flash->getMessages();
        $pageUrl = $router->urlFor('url', ['id' => $id]);
        $checks = $pdo->getChecks($id);
        $params = [
            'flash' => $messages,
            'url' => $data,
            'pageUrl' => $pageUrl,
            'checks' => $checks
        ];
        return $renderer->render($response, 'urls/show.phtml', $params);
    }

    return $response->write('Not Found')->withStatus(404);
})->setName('url');

//post /urls
$app->post('/urls', function ($request, $response) use ($renderer, $flash, $router) {
    $url = $request->getParsedBodyParam('url');
    $validator = new Valitron\Validator($url);
    $validator->rule('required', 'name')->message('{field} не должен быть пустым')->label('URL');
    $validator->rule('url', 'name')->message('Некорректный {field}')->label('URL');
    $validator->rule('lengthMax', 'name', 255)->message('Некорректный {field}')->label('URL');
    if (!$validator->validate()) {
        $params = [
            'url' => $url,
            'errors' => $validator->errors()
        ];
        return $renderer->render($response->withStatus(422), 'index.phtml', $params);
    }

    $lowerUrl = trim(strtolower($url['name']));
    $urlArray = parse_url($lowerUrl);
    $name = $urlArray['scheme'] . "://" . $urlArray['host'];

    $connection = Connection::get()->connect();
    $pdo = new Query($connection);
    $id = $pdo->getId($name);
    if ($id) {
        $flash->addMessage('success', 'Страница уже существует');
        $currentId = $id;
    } else {
        $flash->addMessage('success', 'Страница успешно добавлена');
        $creadedAt = Carbon::now();
        $pdo->addUrl($name, $creadedAt);
        $currentId = $pdo->getId($name);
    }

    $redirectUrl = $router->urlFor('url', ['id' => $currentId]);
    return $response->withRedirect($redirectUrl);
});

//post /urls/{url_id}/checks
$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($flash, $router) {
    $id = htmlspecialchars($args['url_id']);
    $redirectUrl = $router->urlFor('url', ['id' => $id]);
    $connection = Connection::get()->connect();
    $pdo = new Query($connection);
    [$data] = $pdo->getUrl($id);
    $urlName = $data['name'];
    $client = new Client([
        'timeout' => 5.0
    ]);

    try {
        $clientResp = $client->get($urlName);
    } catch (ConnectException $e) {
        $flash->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response->withRedirect($redirectUrl);
    } catch (RequestException $e) {
        $flash->addMessage('warning', 'Проверка была выполнена успешно, но сервер ответил с ошибкой');
        return $response->withRedirect($redirectUrl);
    }

    $statusCode = $clientResp->getStatusCode();
    $creadedAt = Carbon::now();
    $pdo->addCheck($id, $creadedAt, $statusCode);
    $flash->addMessage('success', 'Страница успешно проверена');
    return $response->withRedirect($redirectUrl);
});

$app->run();
