<?php

namespace PmAnalyticsPackage\api\Google\Controllers;

use PmAnalyticsPackage\api\Google\GoogleAnalyticsAPI;
use Dotenv\Dotenv;
use PmAnalyticsPackage\api\Helpers\CurlHelper;

class AnalyticsController
{
    public function getAnalyticsData($dealerCode, $startDate, $endDate)
    {
        $format = 'Y-m-d';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $dealer = self::dealerType($dealerCode);

        $ga = new GoogleAnalyticsAPI(
            $dealer,
            $startDate,
            $endDate,
            $dealer[0]['account_name']
        );

        // $output = [
        //     'sessions' => 0,
        //     'users' => 0,
        //     'newUsers' => 0
        // ];

        $output = [
            'ga:sessions' => 0,
            'ga:pageviews' => 0,
            'ga:sessionDuration' => 0,
            'ga:bounceRate' => 0
        ];

        $results = [];
        // print_r($ga->analyticsArray);
        foreach ($ga->analyticsArray as $device => $channels) {
            foreach ($channels as $channel => $data) {
                foreach ($data as $metrics => $metric) {
                    $results[$channel][$metrics] += $metric;
                }
            }
        }
        print_r($results);
        // foreach ($ga->analyticsArray as $device => $networks) {
        //     foreach ($networks as $network => $data) {
        //         foreach ($output as $key => $value) {
        //             $output[$key] += $data[$key] ?? 0;
        //         }
        //     }
        // }
        // $output['google_profile_id'] = $dealer[0]['google_profile_id'];
        // $output['account_name'] = $dealer[0]['account_name'];
        return $output;
    }

    public static function dealerType($dealer)
    {
        $dealer = CurlHelper::getDealerByDealerCode($dealer);

        if (count($dealer)) {
            return $dealer;
        }
        return CurlHelper::getDealerByGoogleProfileId($dealer);
    }
}
