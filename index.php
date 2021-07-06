<?php

require __DIR__ . '/vendor/autoload.php';

// Read .env file
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// $mode = $_ENV['MODE'];

$ad_words_id = "104-280-2798";
//Getting session for each customerId        
Api\AdWords\AdWordsConnection::getSession($ad_words_id);
// $configs = include('config.php');
