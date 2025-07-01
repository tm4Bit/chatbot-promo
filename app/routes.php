<?php

declare(strict_types=1);

use Core\App;
use Http\Controller\HealthController;
use Ovlk\Chatbot\Controller\WebhookController;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/up', [HealthController::class, 'handle']);
    });

    // Add more routes here as needed
    $app->group('/api/webhook', function (RouteCollectorProxy $group) {
        $webhookController = App::resolve(WebhookController::class);

        // Rota para verificação do Webhook (exigido pela Meta)
        $group->get('', [$webhookController, 'verify']);

        // Rota para receber as notificações (mensagens, submissões de Flow, etc.)
        $group->post('', [$webhookController, 'handle']);
    });

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request);
    });

};
