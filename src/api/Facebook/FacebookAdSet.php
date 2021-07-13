<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdFields;

class FacebookAdSet
{
    public function __construct($adset_id)
    {
        $adset = new AdSet($adset_id);

        $ads = $adset->getAds(array(
            AdFields::NAME,
            AdFields::ID,
        ));

        foreach ($ads as $ad) {
            echo $ad->name;
        }
    }
}
