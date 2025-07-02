<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\UseCase;

use Ovlk\Chatbot\Services\MetaApiService;
use Ovlk\Chatbot\Services\PromotionService;

final class WebhookUseCase
{
    public function __construct(
        private PromotionService $promotionService,
        private MetaApiService $metaApiService
    ) {}

    public function execute(mixed $body): void
    {
        if (isset($body['object']) && $body['object'] === 'whatsapp_business_account') {
            foreach ($body['entry'] as $entry) {
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'messages') {
                        $value = $change['value'];
                        $message = $value['messages'][0];
                        $this->processMessage($message, $value['metadata']['phone_number_id']); // Passar o phone_number_id
                    }
                }
            }
        }
    }

    private function processMessage(array $message, string $phoneNumberId): void
    {
        $messageType = $message['type'];
        $from = $message['from'];

        // CASO 1: Resposta de um Flow (seu código atual)
        if ($messageType === 'interactive' && $message['interactive']['type'] === 'nfm_reply') {
            $this->handleFlowSubmission($message, $phoneNumberId, $from);

            return;
        }

        // CASO 2: Mensagem de Texto ou Resposta de Botão
        if ($messageType === 'text' || ($messageType === 'interactive' && $message['interactive']['type'] === 'button_reply')) {
            $this->handleUserResponse($message, $from, $phoneNumberId);

            return;
        }

        // Se for qualquer outro tipo de mensagem inicial, podemos enviar a saudação
        $this->sendInitialPrompt($from, $phoneNumberId);
    }

    /**
     * Lida com as respostas de texto ou botão do usuário ("Sim" / "Não")
     */
    private function handleUserResponse(array $message, string $from, string $phoneNumberId): void
    {
        // Se for resposta de botão, o texto está em interactive.button_reply.id
        // Se for texto, está em text.body
        $userReply = '';
        if ($message['type'] === 'interactive' && $message['interactive']['type'] === 'button_reply') {
            $userReply = strtolower($message['interactive']['button_reply']['id']);
        } elseif ($message['type'] === 'text') {
            $userReply = strtolower(trim($message['text']['body']));
        }

        if ($userReply === 'sim') {
            // Inicia o WhatsApp Flow
            $this->metaApiService->sendFlow($from, $phoneNumberId, 'Seu Flow ID aqui', 'SEU_TOKEN_DO_FLOW');

        } elseif ($userReply === 'nao') {
            // Se despede
            $this->metaApiService->sendMessage($from, 'Tudo bem! Se mudar de ideia, é só mandar uma mensagem.', $phoneNumberId);
        } else {
            // Resposta padrão caso não entenda
            $this->sendInitialPrompt($from, $phoneNumberId);
        }
    }

    /**
     * Envia a mensagem inicial com botões Sim/Não
     */
    private function sendInitialPrompt(string $to, string $phoneNumberId): void
    {
        $interactiveMessage = [
            'type' => 'button',
            'body' => [
                'text' => 'Olá! Bem-vindo à promoção. Deseja se cadastrar?',
            ],
            'action' => [
                'buttons' => [
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'sim',
                            'title' => 'Sim',
                        ],
                    ],
                    [
                        'type' => 'reply',
                        'reply' => [
                            'id' => 'nao',
                            'title' => 'Não',
                        ],
                    ],
                ],
            ],
        ];

        $this->metaApiService->sendInteractiveMessage($to, $phoneNumberId, $interactiveMessage);
    }

    /**
     * Processa os dados recebidos do Flow (seu código original, um pouco modificado)
     */
    private function handleFlowSubmission(array $message, string $phoneNumberId): void
    {
        $flowResponse = json_decode($message['interactive']['nfm_reply']['response_json'], true);

        // CORREÇÃO IMPORTANTE: a chave do comprovante no seu flow.json é 'comprovante', não 'receipt_image'
        // E o componente se chama 'photo_picker', não 'comprovante'. Ajuste o flow.json ou o código.
        // Assumindo que você ajuste o payload do flow.json para: "receipt_id": "${purchase_form.photo_picker}"
        if (! isset($flowResponse['receipt_id'])) {
            // Logar erro ou enviar mensagem para o usuário
            return;
        }

        $registrationData = [
            'name' => $flowResponse['name'].' '.$flowResponse['sobrenome'], // Concatenando nome e sobrenome
            'cpf' => $flowResponse['cpf'],
            'email' => $flowResponse['email'],
            'receipt_image_id' => $flowResponse['receipt_id'],
            'whatsapp_number' => $message['from'],
            'meta_message_id' => $message['id'],
            // Adicionar os outros campos do flow se for salvá-los
        ];

        $this->promotionService->createRegistration($registrationData);

        // Mensagem de sucesso
        $this->metaApiService->sendMessage($message['from'], 'Cadastro realizado com sucesso! Em breve você receberá mais informações.', $phoneNumberId);
    }
}
