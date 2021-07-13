<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Object\AdCreative;
use FacebookAds\Object\Fields\AdCreativeFields;

class FacebookAdCreative
{
    public function __construct($adset_id)
    {
        $creative = new AdCreative($adset_id);

        $ad = $creative->getSelf(array(
            AdCreativeFields::NAME,
            //AdCreativeFields::OBJECT_STORY_ID,
            //AdCreativeFields::LINK_URL
        ));
        print_r($ad);
        exit;
    }
}
