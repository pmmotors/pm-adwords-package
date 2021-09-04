<?php

namespace PmAnalyticsPackage\api\Google\Controllers;

use PmAnalyticsPackage\api\Google\API\OrganicTrafficAPI;

class OrganicTrafficReport extends AnalyticsController
{
    public function getAnalyticsData($dealerCode, $startDate, $endDate)
    {
        self::$reportType = 'organic-traffic';
        $format = 'Y-m-d';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $dealer = self::dealerType($dealerCode);

        $ga = new OrganicTrafficAPI(
            $dealer,
            $startDate,
            $endDate,
            $dealer[0]['account_name']
        );
    }
}
