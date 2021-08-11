<?php

use Carbon\Carbon;
use PmAnalyticsPackage\api\AdWords\AdWordsConnection;
use PmAnalyticsPackage\api\AdWords\AdWordsReportBudget;

require __DIR__ . '/vendor/autoload.php';

// Testing AdWords

$ad_words_id = "104-280-2798";

// Getting session for each customerId 
$apiAdWords = new AdWordsConnection();
$sesion = $apiAdWords->getSession($ad_words_id);

// $utilites = new AdWordsUtilities($sesion);
// $utilites->getCampaigns();


$report = new AdWordsReportBudget(
    '338-219-1963',
    Carbon::createFromDate(2021, 1, 7),
    Carbon::createFromDate(2021, 8, 7),
    'spectrumcollision.com'
);

print_r($report->getReport());

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

// $dialog = new DialogTechReport(
//     new Carbon('first day of last month'),
//     new Carbon('last day of last month'),
//     'Gupton Motors Inc',
//     '615-384-2886'
// );

// $dialog->getDialogTechArray();

// Testing Google
// $dataSourcePath = 'src/config/';
// $analytics = Google::make('analytics');
// $google = new GoogleAnalyticsReport(
//     $analytics,
//     '149221862',
//     '2021-03-03',
//     'Woodstock Garage',
//     $dataSourcePath
// );
