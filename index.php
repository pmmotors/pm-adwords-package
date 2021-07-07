<?php

use Api\AdWords\AdWordsConnection;

require __DIR__ . '/vendor/autoload.php';


$ad_words_id = "104-280-2798";

//Getting session for each customerId 
$apiAdWords = new AdWordsConnection();
$apiAdWords->getSession($ad_words_id);
// $apiAdWords->validateAccountId($ad_words_id);
