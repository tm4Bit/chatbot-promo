<?php

return [
    'displayErrorDetails' => getenv('DISPLAY_ERROR_DETAILS') ?: true,
    'logErrors' => getenv('LOG_ERRORS') ?: true,
    'logErrorDetails' => getenv('LOG_ERRORS_DETAILS') ?: true,
];
