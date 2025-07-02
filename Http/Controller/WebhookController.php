<?php

declare(strict_types=1);

namespace Http\Controller;

use Ovlk\Chatbot\UseCase\WebhookUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WebhookController
{
    public function __construct(private WebhookUseCase $webhookUseCase) {}

    public function handle(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        // Log para debug
        file_put_contents('webhook_log.txt', json_encode($body, JSON_PRETTY_PRINT)."\n", FILE_APPEND);

        $this->webhookUseCase->execute($body);

        $response->getBody()->write('EVENT_RECEIVED');

        return $response->withStatus(200);
    }

    public function verify(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $mode = $queryParams['hub_mode'] ?? null;
        $token = $queryParams['hub_verify_token'] ?? null;
        $challenge = $queryParams['hub_challenge'] ?? null;

        $verifyToken = config('cloud-api', 'meta_verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            $response->getBody()->write($challenge);

            return $response->withStatus(200);
        }

        return $response->withStatus(403);
    }
}
