<?php

namespace PmAnalyticsPackage\api\Google\Controllers;

use PmAnalyticsPackage\api\Google\API\OrganicTrafficUsersVisitsAPI;

class OrganicTrafficUsersVisitsReport extends AnalyticsController
{

    public function getAnalyticsData($dealerCode, $startDate, $endDate)
    {
        $format = 'Y-m-d';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $dealer = self::dealerType($dealerCode);

        $ga = new OrganicTrafficUsersVisitsAPI(
            $dealer,
            $startDate,
            $endDate,
            $dealer[0]['account_name']
        );

        $ga->analyticsArray['google_profile_id'] = $dealer[0]['google_profile_id'];
        $ga->analyticsArray['account_name'] = $dealer[0]['account_name'];

        return $ga->analyticsArray;
    }
}
