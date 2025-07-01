<?php

declare(strict_types=1);

use Core\App;
use Core\Container;
use Core\Database;
use Ovlk\Chatbot\Controller\WebhookController;
use Ovlk\Chatbot\Service\MetaApiService;
use Ovlk\Chatbot\Service\PromotionService;

$container = new Container;

$container->bind(Database::class, function () {
    $db = config('database', 'db');
    $username = config('database', 'username');
    $password = config('database', 'password');

    return new Database($db, $username, $password);
});

// Serviço da API Meta
$container->bind(MetaApiService::class, function () {
    return new MetaApiService;
});

// Serviço de Promoção
$container->bind(PromotionService::class, function () {
    return new PromotionService(
        App::resolve(Database::class),
        App::resolve(MetaApiService::class)
    );
});

// Controller do Webhook
$container->bind(WebhookController::class, function () {
    return new WebhookController(
        App::resolve(PromotionService::class)
    );
});

App::setContainer($container);
