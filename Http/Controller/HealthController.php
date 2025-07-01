<?php

declare(strict_types=1);

namespace Http\Controller;

use Core\App;
use Core\Database;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HealthController
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        App::resolve(Database::class);
        $payload = json_encode([
            'status' => 'ON',
            'message' => 'Serviço ativo',
        ]);
        $response->getBody()->write($payload);

        return $response;
    }
}
