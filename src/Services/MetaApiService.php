<?php

declare(strict_types=1);

namespace Ovlk\Chatbot\Services;

use GuzzleHttp\Client;

class MetaApiService
{
    private string $accessToken;

    private string $phoneNumberId;

    public function __construct(mixed $metaInfo, private Client $client)
    {
        $this->accessToken = $metaInfo['meta_access_token'];
        $this->phoneNumberId = $metaInfo['meta_phone_number_id'];
    }

    public function sendMessage(string $to, string $text): void
    {
        $this->client->post("{$this->phoneNumberId}/messages", [
            'headers' => [
                'Authorization' => "Bearer {$this->accessToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'text' => ['body' => $text],
            ],
        ]);
    }

    public function downloadMedia(string $mediaId, string $destinationPath): bool
    {
        // 1. Obter a URL da mídia
        $response = $this->client->get($mediaId, [
            'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
        ]);

        $mediaData = json_decode($response->getBody()->getContents(), true);
        $mediaUrl = $mediaData['url'];

        if (! $mediaUrl) {
            return false;
        }

        // 2. Fazer o download do arquivo
        $this->client->get($mediaUrl, [
            'headers' => ['Authorization' => "Bearer {$this->accessToken}"],
            'sink' => $destinationPath, // Salva o arquivo diretamente no caminho
        ]);

        return file_exists($destinationPath);
    }
}
