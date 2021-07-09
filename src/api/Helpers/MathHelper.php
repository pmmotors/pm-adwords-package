<?php

namespace PmAnalyticsPackage\api\Helpers;

class MathHelper
{
    /**
     * AdWords return the cost in micro format and we need to change it to standard format (base)
     *
     * @param $micro integer The cost in micro format
     * @return float The cost in the standard format (base)
     */
    public static function microToBase($micro)
    {
        return $micro / 1000000;
    }

    public static function secondsToHMS($seconds)
    {
        $t = round($seconds);
        return sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);
    }
}
