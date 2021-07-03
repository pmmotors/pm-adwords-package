<?php

require __DIR__ . '/vendor/autoload.php';

$ad_words_id = "104-280-2798";
//Getting session for each customerId        
AdWords\AdWordsConnection::getSession($ad_words_id);
