<?php

use PmAnalyticsPackage\AdWords\AdWordsConnection;
use PmAnalyticsPackage\api\AdWords\AdWordsReportMaserati;

require __DIR__ . '/vendor/autoload.php';

$ad_words_id = "104-280-2798";

//Getting session for each customerId 
// $apiAdWords = new AdWordsConnection();
// $apiAdWords->getSession($ad_words_id);

$maserati = new AdWordsReportMaserati(
    '338-219-1963',
    '07/01/2021',
    '07/08/2021',
    'spectrumcollision.com'
);

$maserati->getReport();
