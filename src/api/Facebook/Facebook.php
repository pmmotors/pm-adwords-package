<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Http\Exception\AuthorizationException;
use FacebookAds\Object\AdAccount;
use FacebookAds\Api as FacebookAdsApi;

class Facebook
{
    public function __construct()
    {
    }

    public static function facebookInit()
    {
        $configs = include('src/config/facebook.php');

        FacebookAdsApi::init(
            $configs['config']['app_id'],
            $configs['config']['app_secret'],
            $configs['config']['access_token'],
        );
    }

    public static function FacebookAdAccount($facebookId)
    {
        $facebookACT = 'act_' . $facebookId;
        $facebookAccount = new AdAccount($facebookACT);

        return $facebookAccount;
    }

    public function validateAccountId($facebookId)
    {
        $facebookAccount = self::FacebookAdAccount($facebookId);
        try {
            // TEST request
            $facebookAccount->getUsers();
            return true;
        } catch (AuthorizationException $e) {
            return false;
        }
    }
}
