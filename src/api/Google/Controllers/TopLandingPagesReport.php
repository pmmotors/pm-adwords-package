<?php

namespace PmAnalyticsPackage\api\Google\Controllers;

use PmAnalyticsPackage\api\Google\API\TopLandingPagesAPI;

class TopLandingPagesReport extends AnalyticsController
{
    public function getAnalyticsData($dealerCode, $startDate, $endDate)
    {
        self::$reportType = 'top-landing-pages';
        $format = 'Y-m-d';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $dealer = self::dealerType($dealerCode);

        $ga = new TopLandingPagesAPI(
            $dealer,
            $startDate,
            $endDate,
            $dealer[0]['account_name'],
            self::$reportType
        );

        return $ga->response;
    }
}
