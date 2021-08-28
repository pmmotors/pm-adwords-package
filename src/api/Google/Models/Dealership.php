<?php

namespace PmAnalyticsPackage\api\Google\Model;

use PmAnalyticsPackage\api\Google\GoogleAnalytics;

class Dealership
{

    private $is_google_web_account;
    public function getGoogleAnalyticsClient()
    {
        return GoogleAnalytics::getGoogleAnalyticsClient($this->is_google_web_account);
    }
}
