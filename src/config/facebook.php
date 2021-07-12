<?php

use Dotenv\Dotenv;

$dotenv = new Dotenv('./');
$dotenv->safeLoad();

return [
    'config' => [
        'app_id' => $_ENV['FACEBOOK_APP_ID'],
        'app_secret' => $_ENV['FACEBOOK_APP_SECRET'],
        'access_token' => $_ENV['FACEBOOK_ACCESS_TOKEN'],
        'default_graph_version' => $_ENV['FACEBOOK_DEFAULT_GRAPH_VERSION'],
    ],
];
