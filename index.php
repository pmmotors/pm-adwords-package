<?php

use PmAnalyticsPackage\api\Facebook\Facebook;
use PmAnalyticsPackage\api\Facebook\FacebookReport;
use FacebookAds\Object\AdAccount;
use PmAnalyticsPackage\api\DialogTech\DialogTechReport;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

// Testing AdWords

// $ad_words_id = "104-280-2798";

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

// Testing facebook
// Facebook::FacebookInit();

// $account_id = "1369958279705871";
// $account = Facebook::FacebookAdAccount($account_id);
// $facebookAd = new FacebookReport(
//     $account,
//     '2021-01-07',
//     '2021-08-07',
//     'spectrumcollision.com'
// );

// print_r($facebookAd->getDataFromFacebookAPI());

// Testing DialogTech

$dialog = new DialogTechReport(
    new Carbon('first day of last month'),
    new Carbon('last day of last month'),
    'Gupton Motors Inc',
    '615-384-2886'
);

$dialog->getDialogTechArray();
