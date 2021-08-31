<?php

namespace PmAnalyticsPackage\api\Google\Models;

use PmAnalyticsPackage\api\Google\GoogleAnalytics;

class Dealership
{
    public static function getGoogleAnalyticsClient($dealer)
    {
        return GoogleAnalytics::getGoogleAnalyticsClient($dealer);
    }
}
