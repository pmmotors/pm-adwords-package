<?php

namespace PmAnalyticsPackage\api\Helpers;

use Dotenv\Dotenv;

class CurlHelper
{
    public static function getDealerByDealerCode($dealerCode)
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

    public static function getDealerByGoogleProfileId($googleId)
    {
        $dotenv = Dotenv::createImmutable('./');
        $dotenv->safeLoad();

        $url = $_ENV['URL_PIXEL_MOTION_DEMO'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'dealerships/google_id/' . $googleId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $dealership = curl_exec($ch);
        curl_close($ch);

        $dealership = json_decode($dealership, true);
        return $dealership;
    }
}
