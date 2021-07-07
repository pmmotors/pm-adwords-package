<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('./');
$dotenv->safeLoad();

return [
    'config' => [
        'app_id' => $_ENV('FACEBOOK_APP_ID', null),
        'app_secret' => $_ENV('FACEBOOK_APP_SECRET', null),
        'access_token' => $_ENV('FACEBOOK_ACCESS_TOKEN', null),
        'default_graph_version' => $_ENV('FACEBOOK_DEFAULT_GRAPH_VERSION', 'v2.5'),
    ],
];
