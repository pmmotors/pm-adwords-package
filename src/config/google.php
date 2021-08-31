<?php

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable('./');
$dotenv->safeLoad();

return [
    /*
    |----------------------------------------------------------------------------
    | Google application name
    |----------------------------------------------------------------------------
    */
    'application_name' => $_ENV['GOOGLE_APPLICATION_NAME'],

    /*
    |----------------------------------------------------------------------------
    | Google OAuth 2.0 access
    |----------------------------------------------------------------------------
    |
    | Keys for OAuth 2.0 access, see the API console at
    | https://developers.google.com/console
    |
    */
    'client_id'       => $_ENV['GOOGLE_CLIENT_ID'],
    'client_secret'   => $_ENV['GOOGLE_CLIENT_SECRET'],
    'redirect_uri'    => $_ENV['GOOGLE_REDIRECT'],
    'scopes'          => array('https://www.googleapis.com/auth/analytics.readonly'), //[],
    'access_type'     => 'offline', //'online',
    'approval_prompt' => 'auto',
    'refresh_token'   => $_ENV['GOOGLE_REFRESH_TOKEN'],
    /*
    |----------------------------------------------------------------------------
    | Google developer key
    |----------------------------------------------------------------------------
    |
    | Simple API access key, also from the API console. Ensure you get
    | a Server key, and not a Browser key.
    |
    */
    'developer_key' => $_ENV['GOOGLE_DEVELOPER_KEY'],

    /*
    |----------------------------------------------------------------------------
    | Google service account
    |----------------------------------------------------------------------------
    |
    | Set the credentials JSON's location to use assert credentials, otherwise
    | app engine or compute engine will be used.
    |
    */
    'service' => [
        /*
        | Enable service account auth or not.
        */
        'enable' => $_ENV['GOOGLE_SERVICE_ENABLED'],

        /*
        | Path to service account json file
        */
        'file' => $_ENV['GOOGLE_SERVICE_ACCOUNT_JSON_LOCATION']
    ],
];
