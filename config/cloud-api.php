<?php

return [
    'meta_access_token' => getenv('META_ACCESS_TOKEN'),
    'meta_phone_number_id' => getenv('META_PHONE_NUMBER_ID'),
    'meta_verify_token' => getenv('META_VERIFY_TOKEN') ?: '',
];
