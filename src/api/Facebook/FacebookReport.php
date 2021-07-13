<?php

namespace PmAnalyticsPackage\api\Facebook;

use FacebookAds\Object\Fields\AdsInsightsFields;
use FacebookAds\Object\Values\AdsInsightsBreakdownsValues;

class FacebookReport
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

    /**
     * Construct for Facebook's Report
     *
     * @param $account object Facebook's account object
     * @param $reportDate string Date formatted YYYY-MM-DD
     * @param $accountName string The account name
     */
    public function __construct(&$account, $reportDate, $endReportDate, $accountName, $type = 'insights')
    {
        $this->account = &$account;
        $this->reportDate = $reportDate;
        $this->endReportDate = $endReportDate;
        $this->accountName = $accountName;

        // Initialize the Facebook Array
        if ($type == 'insights') {
            $this->initFacebookArray();

            // Set the Facebook Array
            if (!empty($this->account)) {
                $this->setDeviceMedia();
            }
        } else {
            $this->initProfitCenterFacebookArray();

            // Set the Facebook array
            if (!empty($this->account)) {
                $this->setDeviceMediaProfitCenter();
            }
        }
    }

    /**
     * Initializes the facebookArray array that is going to contain all the data
     */
    private function initFacebookArray()
    {
        // The values of the columns of the Chrysler Report for Facebook data
        $facebookRows = array(
            'impressions' => 0,
            'clicks' => 0,
            'cost' => 0
        );
        // All the media types available
        $mediaTypes = array(
            'display' => $facebookRows
        );
        // The Device Type for the Chrysler Report is either "Desktop" or "Mobile"
        $this->facebookArray = array(
            'desktop' => $mediaTypes,
            'mobile' => $mediaTypes
        );
    }

    /**
     * Initializes the facebookArray array that is going to contain all the data
     */
    private function initProfitCenterFacebookArray()
    {
        // The values of the columns of the Chrysler Report for Facebook data
        $facebookRows = array(
            'impressions' => 0,
            'clicks' => 0,
            'cost' => 0
        );

        // Profit Center Types
        $this->facebookArray = array(
            'new' => $facebookRows,
            'used' => $facebookRows,
            'cpov' => $facebookRows,
            'service' => $facebookRows
        );
    }

    /**
     * Get the Insights Parameters that are going to be used to retrieve the data from Facebook API
     *
     * @return array The Insights Parameters
     */
    public function getInsightsParams()
    {
        return array(
            'time_range' => array(
                'since' => $this->reportDate,
                'until' => $this->endReportDate,
            ),
            'breakdowns' => array(
                AdsInsightsBreakdownsValues::DEVICE_PLATFORM, //InsightsBreakdowns::PLACEMENT,
                AdsInsightsBreakdownsValues::IMPRESSION_DEVICE, //InsightsBreakdowns::IMPRESSION_DEVICE
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
            AdsInsightsFields::IMPRESSIONS,   //InsightsFields::IMPRESSIONS,            // Impressions
            AdsInsightsFields::INLINE_LINK_CLICKS,   //InsightsFields::INLINE_LINK_CLICKS,     // Clicks
            AdsInsightsFields::SPEND,      //InsightsFields::SPEND,                  // Total Cost
        );
    }

    /**
     * Get the Insights Parameters that are going to be used to retrieve the data from Facebook API
     *
     * @return array The Insights Parameters
     */
    private function getInsightsParamsProfitCenter()
    {
        return array(
            'time_range' => array(
                'since' => $this->reportDate,
                'until' => $this->endReportDate,
            ),
            'breakdowns' => array(
                //                AdsInsightsBreakdownsValues::DEVICE_PLATFORM, //InsightsBreakdowns::PLACEMENT,
                //                AdsInsightsBreakdownsValues::IMPRESSION_DEVICE, //InsightsBreakdowns::IMPRESSION_DEVICE
            ),
            'level' => 'ad', //ad campaign
            //            'action_breakdowns' => array(
            //                AdsInsightsActionBreakdownsValues::ACTION_DEVICE//ACTION_LINK_CLICK_DESTINATION
            //            )
        );
    }

    /**
     * Get all the Insights Fields needed for the report
     *
     * @return array The Insights Fields need for the report
     */
    private function getInsightsFieldsProfitCenter()
    {
        return array(
            AdsInsightsFields::IMPRESSIONS,
            AdsInsightsFields::INLINE_LINK_CLICKS,
            AdsInsightsFields::SPEND,
            AdsInsightsFields::CAMPAIGN_ID,
            AdsInsightsFields::CAMPAIGN_NAME,
            AdsInsightsFields::ADSET_ID,
            AdsInsightsFields::ADSET_NAME,
            AdsInsightsFields::AD_ID,
            AdsInsightsFields::AD_NAME,
            AdsInsightsFields::PLACE_PAGE_NAME,
            // AdsInsightsFields::TOTAL_ACTION_VALUE,
            AdsInsightsFields::CLICKS, //less clicks than inline :/
            //AdsInsightsFields::ACTIONS,
            AdsInsightsFields::ACTION_VALUES,
            //AdsInsightsFields::UNIQUE_ACTIONS
        );
    }

    /**
     * Get all the report data from Facebook's API
     *
     * @return array A multidimensional array containing all the reports data from Facebook's API
     */
    public function getDataFromFacebookAPI($type = 'insights')
    {
        $data = array();
        // try {
        if ($type == 'insights') {
            $stats = $this->account->getInsights($this->getInsightsFields(), $this->getInsightsParams());
        } else {
            $stats = $this->account->getInsights($this->getInsightsFieldsProfitCenter(), $this->getInsightsParamsProfitCenter());
            //$stats = $this->account->getInsights([AdsInsightsFields::ACTIONS],['action_breakdowns' => [AdsInsightsActionBreakdownsValues::ACTION_DEVICE]]);//$this->getInsightsParamsProfitCenter());
        }
        //            print_r($stats);exit();
        $data = $stats->getLastResponse()->getContent();
        // } catch (FacebookResponseException $e) {
        // // When Graph returns an error
        //     echo '<p class="errorMessage">Facebook Graph returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook Graph returned an error: ' . $e->getMessage() . PHP_EOL;
        // } catch (FacebookSDKException $e) {
        // // When validation fails or other local issues
        //     echo '<p class="errorMessage">Facebook SDK/Validation returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook SDK/Validation returned an error: ' . $e->getMessage() . PHP_EOL;
        // } catch (\Exception $e) {
        //     echo '<p class="errorMessage">Facebook SDK returned an error: ' . $e->getMessage() . '</p>' . PHP_EOL;
        //     $this->errorMsg .= 'Facebook SDK returned an error: ' . $e->getMessage() . PHP_EOL;
        // }
        return $data;
    }

    /**
     * Gets all the values for Impressions, Clicks, Cost and Device and puts it into the Facebook Array
     */
    private function setDeviceMediaProfitCenter()
    {
        $data = $this->getDataFromFacebookAPI('profitCenter');
        //        print_r($data);
        //        return;
        //        //exit();
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
                if (
                    strpos(strtolower($ad['campaign_name']), 'service') !== false || strpos(strtolower($ad['adset_name']), 'service') !== false
                    || strpos(strtolower($ad['ad_name']), 'service') !== false
                ) {
                    $this->facebookArray['service']['impressions'] += $ad['impressions'];
                    $this->facebookArray['service']['clicks'] += $ad['inline_link_clicks']; //$ad['clicks']; why not?
                    $this->facebookArray['service']['cost'] += $ad['spend'];
                } else if (
                    strpos(strtolower($ad['campaign_name']), 'certified') !== false || strpos(strtolower($ad['adset_name']), 'certified') !== false
                    || strpos(strtolower($ad['ad_name']), 'certified') !== false
                ) {
                    $this->facebookArray['cpov']['impressions'] += $ad['impressions'];
                    $this->facebookArray['cpov']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['cpov']['cost'] += $ad['spend'];
                } else if (
                    strpos(strtolower($ad['campaign_name']), 'used') !== false  || strpos(strtolower($ad['adset_name']), 'used') !== false
                    || strpos(strtolower($ad['ad_name']), 'used') !== false
                ) {
                    $this->facebookArray['used']['impressions'] += $ad['impressions'];
                    $this->facebookArray['used']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['used']['cost'] += $ad['spend'];
                } else {
                    $this->facebookArray['new']['impressions'] += $ad['impressions'];
                    $this->facebookArray['new']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['new']['cost'] += $ad['spend'];
                }
            }
        } // There is No Facebook Data
        else {
            echo '<p class="errorMessage">No Facebook results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Facebook results found for account: ' . $this->accountName . PHP_EOL;
        }
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
                    $this->facebookArray['desktop']['display']['impressions'] += $ad['impressions'];
                    $this->facebookArray['desktop']['display']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['desktop']['display']['cost'] += $ad['spend'];
                } else {
                    // If not, then it is a mobile ad
                    $this->facebookArray['mobile']['display']['impressions'] += $ad['impressions'];
                    $this->facebookArray['mobile']['display']['clicks'] += $ad['inline_link_clicks'];
                    $this->facebookArray['mobile']['display']['cost'] += $ad['spend'];
                }
            }
        } // There is No Facebook Data
        else {
            echo '<p class="errorMessage">No Facebook results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Facebook results found for account: ' . $this->accountName . PHP_EOL;
        }
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
}
