<?php

namespace PmAnalyticsPackage\api\Google\Models;

use PmAnalyticsPackage\api\Google\GoogleAnalytics;

class Dealership
{

    public static $is_google_web_account = null;
    public static function getGoogleAnalyticsClient()
    {
        return GoogleAnalytics::getGoogleAnalyticsClient(self::$is_google_web_account);
    }
}
