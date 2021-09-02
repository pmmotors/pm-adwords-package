<?php

namespace PmAnalyticsPackage\api\Google\Controllers;

use PmAnalyticsPackage\api\Google\GoogleAnalyticsAPI;
use Dotenv\Dotenv;
use PmAnalyticsPackage\api\Helpers\CurlHelper;

abstract class AnalyticsController
{
    public abstract function getAnalyticsData($dealerCode, $startDate, $endDate);

    public static function dealerType($dealer)
    {
        $dealer = CurlHelper::getDealerByDealerCode($dealer);

        if (count($dealer)) {
            return $dealer;
        }
        return CurlHelper::getDealerByGoogleProfileId($dealer);
    }
}
