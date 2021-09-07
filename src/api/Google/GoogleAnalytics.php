<?php

namespace PmAnalyticsPackage\api\Google;

use PmMotors\Google\Facades\Google;
use PmMotors\Google\Client as GoogleClient;
use Dotenv\Dotenv;

class GoogleAnalytics
{
    private $errorMsg = "";
    private $analytics;
    private $profileId;
    private $reportStartDate;
    private $reportEndDate;
    private $analyticsReportType;
    private $analyticsResults;
    private $analyticsArray;

    public function __construct($profileId, $reportStartDate, $reportEndDate, $accountName, $dataSourcePath)
    {
        $this->analytics = Google::make('analytics');
        $this->profileId = $profileId;
        $this->reportDate = $reportStartDate;
        $this->accountName = $accountName;
        $this->dataSourcePath = $dataSourcePath;

        $this->initAnalyticsArray();
    }

    private function initAnalyticsArray()
    {
        $analyticsRows = array(
            'sessionsDeviceType' => [
                'desktop' => 0,
                'mobile' => 0,
                'tablet' => 0,
            ],
            'sessionsChannelGrouping' => [
                'Display' => 0,
                'Organic Search' => 0,
                'total' => 0
            ],
            'users' => 0,
            'pageviews' => [
                'newVehicles' => 0,
                'cpc' => 0,
                'total' => 0
            ],
            'submissions' => 0,
            'bounces' => 0,
            'sessionDuration' => 0,
            'timeOnSite' => 0
        );

        $profitCenterArray = array(
            'new' => $analyticsRows,
            'used' => $analyticsRows,
            'service' => $analyticsRows,
            'cpov' => $analyticsRows,
        );

        $this->analyticsArray = $profitCenterArray;
    }

    public static function getGoogleAnalyticsClient($dealer)
    {
        $dotenv = Dotenv::createImmutable('./');
        $dotenv->safeLoad();

        $env = $_ENV['MODE'];
        if (count($dealer)) {
            if ($env === 'dev') {
                $configs = include('src/config/google.php');
                $google = new GoogleClient($configs);
                return $google->make('analytics');
            }
            $google = new GoogleClient(config('google'));
            return $google->make('analytics');
        }
        if ($env === 'dev') {
            $configs = include('src/config/google-pm-web.php');
            $google = new GoogleClient($configs);
            return $google->make('analytics');
        }
        $google = new GoogleClient(config('google-pm-web'));
        return $google->make('analytics');
    }
}
