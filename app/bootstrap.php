<?php

declare(strict_types=1);

use Core\App;
use Core\Container;
use Core\Database;
use GuzzleHttp\Client;
use Http\Controller\WebhookController;
use Ovlk\Chatbot\Services\MetaApiService;
use Ovlk\Chatbot\Services\PromotionService;
use Ovlk\Chatbot\UseCase\WebhookUseCase;

$container = new Container;

// /////////////////////////////////////////////////
// Core
// /////////////////////////////////////////////////
$container->bind(Database::class, function () {
    $db = config('database', 'db');
    $username = config('database', 'username');
    $password = config('database', 'password');

    return new Database($db, $username, $password);
});

$container->bind(Client::class, function () {
    return new Client([
        'base_uri' => 'https://graph.facebook.com/v20.0/',
    ]);
});

// /////////////////////////////////////////////////
// Services
// /////////////////////////////////////////////////
$container->bind(MetaApiService::class, function () {
    $metaConfig = [
        'meta_access_token' => config('cloud-api', 'meta_access_token'),
        'meta_phone_number_id' => config('cloud-api', 'meta_phone_number_id'),
    ];
    $httpClient = App::resolve(Client::class);

    return new MetaApiService($metaConfig, $httpClient);
});

$container->bind(PromotionService::class, function () {
    return new PromotionService(
        App::resolve(Database::class),
        App::resolve(MetaApiService::class)
    );
});

// /////////////////////////////////////////////////
// UseCases
// /////////////////////////////////////////////////
$container->bind(WebhookUseCase::class, function () {
    return new WebhookUseCase(
        App::resolve(PromotionService::class),
        App::resolve(MetaApiService::class)
    );
});

// /////////////////////////////////////////////////
// Controllers
// /////////////////////////////////////////////////
$container->bind(WebhookController::class, function () {
    return new WebhookController(
        App::resolve(WebhookUseCase::class)
    );
});

App::setContainer($container);
