<?php

use PmAnalyticsPackage\AdWords\AdWordsConnection;
use PmAnalyticsPackage\api\AdWords\AdWordsReportMaserati;
use Carbon\Carbon;
use PmAnalyticsPackage\api\AdWords\AdWordsReportBudget;

require __DIR__ . '/vendor/autoload.php';

$ad_words_id = "104-280-2798";

//Getting session for each customerId 
// $apiAdWords = new AdWordsConnection();
// $apiAdWords->getSession($ad_words_id);

// $maserati = new AdWordsReportMaserati(
//     '338-219-1963',
//     Carbon::createFromDate(2021, 1, 7),
//     Carbon::createFromDate(2021, 8, 7),
//     'spectrumcollision.com'
// );

// $maserati->getReport();

$budget = new AdWordsReportBudget(
    '338-219-1963',
    Carbon::createFromDate(2021, 1, 7),
    Carbon::createFromDate(2021, 8, 7),
    'spectrumcollision.com'
);

$budget->getReport();
