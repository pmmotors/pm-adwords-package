<?php

namespace PmAnalyticsPackage\api\Google\Controller;

use PmAnalyticsPackage\api\Google\GoogleAnalyticsAPI;
use Dotenv\Dotenv;

class AnalyticsController
{
    public function getAnalyticsData($dealerCode, $startDate, $endDate)
    {
        $format = 'Y-m-d';
        $startDate = \DateTime::createFromFormat($format, $startDate);
        $endDate = \DateTime::createFromFormat($format, $endDate);
        $dealer = $this->getDealer($dealerCode);

        $ga = new GoogleAnalyticsAPI(
            $dealerCode,
            $startDate,
            $endDate,
            $dealer->account_name
        );

        $output = [
            'sessions' => 0,
            'users' => 0,
            'newUsers' => 0
        ];

        foreach ($ga->analyticsArray as $device => $networks) {
            foreach ($networks as $network => $data) {
                foreach ($output as $key => $value) {
                    $output[$key] += $data[$key] ?? 0;
                }
            }
        }
        $output['google_profile_id'] = $dealer->google_profile_id;
        $output['account_name'] = $dealer->account_name;
        return $output;
    }

    private function getDealer($dealerCode)
    {
        $dotenv = Dotenv::createImmutable('./');
        $dotenv->safeLoad();

        $url = $_ENV['URL_PIXEL_MOTION_DEMO'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'dealerships/' . $dealerCode);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $dealership = curl_exec($ch);
        curl_close($ch);

        $dealership = json_decode($dealership, true);
        return $dealership;
    }
}
