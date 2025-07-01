<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\Controller;

use Ovlk\Chatbot\Service\PromotionService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WebhookController
{
    private PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function handle(Request $request, Response $response): Response
    {
        $body = $request->getParsedBody();

        // Log para debug
        file_put_contents('webhook_log.txt', json_encode($body, JSON_PRETTY_PRINT)."\n", FILE_APPEND);

        if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {
            foreach ($body['entry'] as $entry) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $message = $change['value']['messages'][0];
                        $this->processMessage($message);
                    }
                }
            }
        }

        $response->getBody()->write('EVENT_RECEIVED');

        return $response->withStatus(200);
    }

    private function processMessage(array $message): void
    {
        // Verifica se é uma resposta de um Flow
        if ($message['type'] === 'interactive' && $message['interactive']['type'] === 'nfm_reply') {

            $flowResponse = json_decode($message['interactive']['nfm_reply']['response_json'], true);

            $registrationData = [
                'name' => $flowResponse['name'],
                'cpf' => $flowResponse['cpf'],
                'email' => $flowResponse['email'],
                'receipt_image_id' => $flowResponse['receipt_image'], // O Flow retorna o ID da mídia
                'whatsapp_number' => $message['from'],
                'meta_message_id' => $message['id'],
            ];

            $this->promotionService->createRegistration($registrationData);
        }
    }

    public function verify(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $mode = $queryParams['hub_mode'] ?? null;
        $token = $queryParams['hub_verify_token'] ?? null;
        $challenge = $queryParams['hub_challenge'] ?? null;

        $verifyToken = $_ENV['META_VERIFY_TOKEN'];

        if ($mode === 'subscribe' && $token === $verifyToken) {
            $response->getBody()->write($challenge);

            return $response->withStatus(200);
        }

        return $response->withStatus(403);
    }
}
