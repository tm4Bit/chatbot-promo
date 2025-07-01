<?php

declare(strict_types=1);

namespace Core\Exception;

class HttpNotFoundException extends HttpException
{
    public function __construct($message = 'Not found!')
    {
        parent::__construct($message, 404);
    }
}
