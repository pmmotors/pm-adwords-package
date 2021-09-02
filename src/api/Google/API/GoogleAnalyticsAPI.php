<?php

namespace PmAnalyticsPackage\api\Google\API;

abstract class GoogleAnalyticsAPI
{
    private $errorMsg = '';
    private $analytics;
    private $profileId;
    private $reportStartDate;
    private $reportEndDate;
    private $analyticsReportType;
    private $analyticsResults;
    public $analyticsArray;
    private $accountName;

    public abstract function initAnalyticsArray();

    public abstract function getAnalyticsData();

    public abstract function setAnalyticsArray();
}
