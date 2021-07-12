<?php

use PmAnalyticsPackage\api\Facebook\Facebook;
use PmAnalyticsPackage\AdWords\AdWordsConnection;
use Carbon\Carbon;
use PmAnalyticsPackage\api\AdWords\AdWordsReportOverview;

require __DIR__ . '/vendor/autoload.php';

$ad_words_id = "104-280-2798";

//Getting session for each customerId 
// $apiAdWords = new AdWordsConnection();
// $apiAdWords->getSession($ad_words_id);

// $report = new AdWordsReportOverview(
//     '338-219-1963',
//     Carbon::createFromDate(2021, 1, 7),
//     Carbon::createFromDate(2021, 8, 7),
//     'spectrumcollision.com'
// );

// $report->getReport();

var_dump(Facebook::validateAccountId('1369958279705871'));
