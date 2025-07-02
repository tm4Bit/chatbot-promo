<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\UseCase;

use Ovlk\Chatbot\Services\PromotionService;

final class WebhookUseCase
{
    public function __construct(private PromotionService $promotionService) {}

    public function execute(mixed $body): void
    {

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
}
