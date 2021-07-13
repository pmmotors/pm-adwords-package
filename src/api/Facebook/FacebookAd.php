<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;

class FacebookAd
{
    public function __construct($ad_id)
    {
        $ad = new Ad($ad_id);
        $ad->getSelf(array(
            AdFields::NAME,
            AdFields::ADLABELS
        ));

        echo $ad->name;
        echo $ad->adlabels;
        print_r($ad);
        exit();
    }
}
