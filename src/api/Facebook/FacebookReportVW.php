<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Object\Fields\AdsInsightsFields;
use FacebookAds\Object\values\AdsInsightsBreakdownsValues;

class FacebookReportVW
{
    /**
     * @var object The result from the Facebook Account object
     */
    private $account;

    /**
     * @var string The date for the report. This is Yesterday's date
     */
    private $reportDate;

    /**
     * @var string The account name for this AdWords report
     */
    private $accountName;

    /**
     * @var array Holds all the values of Facebook for the report
     */
    private $facebookArray;

    /**
     * @var string Holds all the error messages that have occurred
     */
    private $errorMsg = '';

    /**
     * @var string Holds all the warning messages that have occurred
     */
    private $warningMsg = '';
    public function __construct($facebook_id, $reportStartDate, $reportEndDate, $accountName)
    {
        $this->reportStartDate = $reportStartDate;
        $this->reportEndDate = $reportEndDate;
        $this->accountName = $accountName;

        Facebook::facebookInit();

        if (!empty($facebook_id)) {
            $facebookAccount = Facebook::FacebookAdAccount($facebook_id);
        } else {
            $facebookAccount = '';
        }

        $this->account = &$facebookAccount;

        // Initialize the Facebook Array
        $this->initFacebookArray();
        // Set the Facebook Array
        if (!empty($this->account)) {
            $this->setFacebookArray();
        }
    }

    /**
     * Initializes the facebookArray array that is going to contain all the data
     */
    private function initFacebookArray()
    {
        // The values of the columns of the VW Report for Facebook data
        $facebookRows = array(
            'impressions' => 0,
            'clicks' => 0,
            'cost' => 0,
            'ctr' => 0,
            'cpc' => 0
        );
        // All the media types available
        $mediaTypes = array(
            'social' => $facebookRows,
            'other' => $facebookRows
        );
        // The Device Type for the VW Report is either "Desktop" or "Mobile"
        $this->facebookArray = array(
            'desktop' => $mediaTypes,
            'mobile' => $mediaTypes,
            'total_cost' => 0
        );
    }

    /**
     * Get the Insights Parameters that are going to be used to retrieve the data from Facebook API
     *
     * @return array The Insights Parameters
     */
    private function getInsightsParams()
    {
        $dateFormat = 'Y-m-d';
        return array(
            'time_range' => array(
                'since' => $this->reportStartDate->format($dateFormat),
                'until' => $this->reportEndDate->format($dateFormat),
            ),
            'breakdowns' => array(
                AdsInsightsBreakdownsValues::PUBLISHER_PLATFORM,
                AdsInsightsBreakdownsValues::IMPRESSION_DEVICE,
                //InsightsBreakdowns::PLACEMENT,
                //InsightsBreakdowns::IMPRESSION_DEVICE,
            )
        );
    }

    /**
     * Get all the Insights Fields needed for the report
     *
     * @return array The Insights Fields need for the report
     */
    private function getInsightsFields()
    {
        return array(
            AdsInsightsFields::IMPRESSIONS,            // Impressions
            AdsInsightsFields::INLINE_LINK_CLICKS,     // Clicks
            AdsInsightsFields::SPEND,                  // Total Cost
        );
    }

    /**
     * Get all the report data from Facebook's API
     *
     * @return array A multidimensional array containing all the reports data from Facebook's API
     */
    private function getDataFromFacebookAPI()
    {
        $data = array();
        // try{
        $stats = $this->account->getInsights($this->getInsightsFields(), $this->getInsightsParams());
        $data = $stats->getLastResponse()->getContent();
        //print_r($data);
        // } catch(Facebook\Exceptions\FacebookResponseException $e){
        //     // When Graph returns an error
        //     echo '<p class="errorMessage">Facebook returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook returned an error: ' . $e->getMessage() . PHP_EOL;
        // } catch(Facebook\Exceptions\FacebookSDKException $e) {
        //     // When validation fails or other local issues
        //     echo '<p class="errorMessage">Facebook SDK returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
        // } catch(\FacebookAds\Exception\Exception $e){
        //     echo '<p class="errorMessage">Facebook SDK returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
        // }
        return $data;
    }

    /**
     * Gets all the values for Impressions, Clicks, Cost and Device and puts it into the Facebook Array
     */
    private function setDeviceMedia()
    {
        $data = $this->getDataFromFacebookAPI();
        if (!empty($data['data'])) {
            /**
             * ad['impressions'] are the impressions
             * ad['inline_link_clicks'] are the clicks
             * ad['spend'] is the cost
             * ad['date_start'] is the start date
             * ad['date_stop'] is the end date
             * ad['placement'] is the placement of the ad. ie mobile_feed, desktop_feed, right_hand, mobile_external_only
             * ad['impression_device'] is the device where the ad was shown. ie. desktop, iphone, ipad, ipod, android_smartphone, android_tablet, other
             */

            foreach ($data['data'] as $ad) {
                // Is this a desktop ad?
                if ($ad['impression_device'] === 'desktop') {
                    $this->facebookArray['desktop']['social']['impressions'] += $ad['impressions'];
                    $this->facebookArray['desktop']['social']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['desktop']['social']['cost'] += $ad['spend'];
                }
                // If not, then it is a mobile ad
                else {
                    $this->facebookArray['mobile']['social']['impressions'] += $ad['impressions'];
                    $this->facebookArray['mobile']['social']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['mobile']['social']['cost'] += $ad['spend'];
                }
                $this->facebookArray['total_cost'] += $ad['spend'];
            }
        }
        // There is No Facebook Data
        else {
            echo '<p class="errorMessage">No Facebook results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Facebook results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    /**
     * Sets all the values from Facebook's data into the $facebookArray array
     */
    private function setFacebookArray()
    {
        // Get all the Impressions, Clicks and Cost for Destop/Social and Mobile/Social
        $this->setDeviceMedia();
        // Get the Desktop/Social CTR and CPC
        $this->facebookArray['desktop']['social']['ctr'] = FacebookReportVW::calculateCTR($this->facebookArray['desktop']['social']['clicks'], $this->facebookArray['desktop']['social']['impressions']);
        $this->facebookArray['desktop']['social']['cpc'] = FacebookReportVW::calculateCPC($this->facebookArray['desktop']['social']['cost'], $this->facebookArray['desktop']['social']['clicks']);
        // Get the Mobile/Social CTR and CPC
        $this->facebookArray['mobile']['social']['ctr'] = FacebookReportVW::calculateCTR($this->facebookArray['mobile']['social']['clicks'], $this->facebookArray['mobile']['social']['impressions']);
        $this->facebookArray['mobile']['social']['cpc'] = FacebookReportVW::calculateCPC($this->facebookArray['mobile']['social']['cost'], $this->facebookArray['mobile']['social']['clicks']);
    }

    /**
     * @return array The Facebook array containing all the data for the report
     */
    public function getFacebookArray()
    {
        return $this->facebookArray;
    }

    /**
     * @return string Returns all the error messages that happened with the Google Analytics API
     */
    public function getErrorMessage()
    {
        return $this->errorMsg;
    }

    /**
     * @return string Returns all the warning messages that happened with the Google Analytics API
     */
    public function getWarningMessage()
    {
        return $this->warningMsg;
    }

    /**
     * Calculates the Click-through-rate (CTR)
     *
     * @param $clicks integer The number of clicks
     * @param $impressions integer The number of impressions
     * @return float Returns the CTR
     */
    public static function calculateCTR($clicks, $impressions)
    {
        if ($impressions > 0) {
            return round(($clicks / $impressions), 4);
        }
        return 0;
    }

    /**
     * Calculates the Cost to advertise per click (CPC)
     *
     * @param $cost float The cost amount
     * @param $clicks integer The number of clicks
     * @return float Returns the CPC rounded to the decimal
     */
    public static function calculateCPC($cost, $clicks)
    {
        if ($clicks > 0) {
            return round(($cost / $clicks), 4);
        }
        return 0;
    }

    /**
     * Calculates the Cost per thousand impressions (CPM)
     *
     * @param $cost float The cost amount
     * @param $impressions integer The number of impressions
     * @return float Returns the CPM rounded to the decimal
     */
    public static function calculateCPM($cost, $impressions)
    {
        if ($impressions > 0) {
            return round(($cost / ($impressions / 1000)), 2);
        }
        return 0;
    }
}
