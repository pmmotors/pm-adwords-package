<?php

use Dotenv\Dotenv;

$dotenv = new Dotenv('./');
$dotenv->safeLoad();

return [
    'apiKey' => $_ENV['DT_API_KEY'],
    'url' => $_ENV['DT_URL']
];
