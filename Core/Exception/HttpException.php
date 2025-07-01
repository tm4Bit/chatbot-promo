<?php

declare(strict_types=1);

namespace Core\Exception;

use Exception;
use Throwable;

class HttpException extends Exception
{
    protected int $httpStatusCode;

    /**
     * Construtor da HttpException.
     *
     * @param  string  $message  A mensagem de erro para a exceção.
     * @param  int  $httpStatusCode  O código de status HTTP (ex: 400, 404, 500).
     * @param  int  $code  O código de erro interno da aplicação (opcional, padrão 0).
     * @param  Throwable|null  $previous  A exceção anterior (para encadeamento, opcional).
     */
    public function __construct(
        string $message = '',
        int $httpStatusCode = 500,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $this->httpStatusCode = $httpStatusCode;
        parent::__construct($message, $code, $previous);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
