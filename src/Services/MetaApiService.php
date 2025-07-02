<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MetaApiService
{
    private string $accessToken;

    public function __construct(mixed $metaInfo, private Client $client)
    {
        $this->accessToken = $metaInfo['meta_access_token'];
    }

    public function sendMessage(string $to, string $text, string $phoneNumberId): void
    {
        $this->sendRequest($phoneNumberId, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'text' => ['body' => $text],
        ]);
    }

    public function sendInteractiveMessage(string $to, string $phoneNumberId, array $interactivePayload): void
    {
        $this->sendRequest($phoneNumberId, [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => $interactivePayload,
        ]);
    }

    public function sendFlow(string $to, string $phoneNumberId, string $flowId, string $flowToken): void
    {
        $interactivePayload = [
            'type' => 'flow',
            'header' => [
                'type' => 'text',
                'text' => 'Cadastro na Promoção', // Título que aparece no topo do Flow
            ],
            'body' => [
                'text' => 'Por favor, preencha seus dados para participar.', // Subtítulo
            ],
            'footer' => [
                'text' => 'Clique abaixo para começar',
            ],
            'action' => [
                'name' => 'flow',
                'parameters' => [
                    'flow_message_version' => '3',
                    'flow_token' => $flowToken, // Um token único para cada execução do Flow
                    'flow_id' => $flowId,
                    'flow_cta' => 'Cadastrar', // Texto do botão
                    'flow_action' => 'navigate', // Ação inicial
                    'flow_action_payload' => [
                        'screen' => 'JOIN_NOW', // ID da primeira tela do seu flow.json
                    ],
                ],
            ],
        ];

        $this->sendInteractiveMessage($to, $phoneNumberId, $interactivePayload);
    }

    public function downloadMedia(string $mediaId, string $destinationPath): bool
    {
        try {
            // 1. Obter a URL da mídia
            $response = $this->client->get($mediaId, [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
            ]);

            $mediaData = json_decode($response->getBody()->getContents(), true);
            $mediaUrl = $mediaData['url'] ?? null;

            if (! $mediaUrl) {
                return false;
            }

            // 2. Fazer o download do arquivo
            $this->client->get($mediaUrl, [
                'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
                'sink' => $destinationPath, // Salva o arquivo diretamente no caminho
            ]);

            return file_exists($destinationPath);
        } catch (GuzzleException $e) {
            error_log('Falha no download da mídia: '.$e->getMessage());

            return false;
        }
    }

    private function sendRequest(string $phoneNumberId, array $json): void
    {
        try {
            $this->client->post("{$phoneNumberId}/messages", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $json,
            ]);
        } catch (GuzzleException $e) {
            error_log('Falha ao enviar mensagem via Meta API: '.$e->getMessage());
        }
    }
}
