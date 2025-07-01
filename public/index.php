<?php

declare(strict_types=1);

use Core\Exception\HttpException;
use Http\Middleware\ContentTypeHeadersMiddleware;
use Http\Middleware\Cors;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

const BASE_PATH = __DIR__.'/../';
require BASE_PATH.'vendor/autoload.php';
require BASE_PATH.'helpers/functions.php';
require base_path('app/bootstrap.php');

$app = AppFactory::create();
$responseFactory = $app->getResponseFactory();

$app->addBodyParsingMiddleware();
$app->add(new Cors($responseFactory));
$app->add(new ContentTypeHeadersMiddleware);
$app->addRoutingMiddleware();

$errorHandler = function (Request $request, Throwable $exception) use ($app) {
    if ($exception instanceof HttpException) {
        $statusCode = $exception->getHttpStatusCode();
    } elseif ($exception instanceof PDOException) {
        $statusCode = 500;
    } else {
        $statusCode = 500;
    }

    $payload = [
        'error' => $exception->getMessage(),
        'timestamp' => date('Y-m-d H:i:s'),
    ];
    if (ini_get('display_errors') === '1') {
        $payload['exception_type'] = get_class($exception);
        $payload['file'] = $exception->getFile();
        $payload['line'] = $exception->getLine();
    }
    $response = $app->getResponseFactory()->createResponse();
    $response
        ->getBody()
        ->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($statusCode);
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$routes = require base_path('app/routes.php');
$routes($app);

$app->run();
