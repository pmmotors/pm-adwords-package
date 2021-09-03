<?php

namespace PmAnalyticsPackage\api\Google\API;

use PmAnalyticsPackage\api\Google\Models\Dealership;

class TrafficSourcesAPI extends GoogleAnalyticsAPI
{
    private $trafficByDevice = array(
        'desktop' => 0,
        'tablet' => 0,
        'mobile' => 0
    );

    private $referral = array();

    public function __construct($dealer, $reportStartDate, $reportEndDate, $accountName)
    {
        $this->analytics = isset($dealer) ?
            Dealership::getGoogleAnalyticsClient($dealer) :
            null;

        $this->profileId = isset($dealer) ?
            $dealer[0]['google_profile_id'] : '';
        $this->reportStartDate = $reportStartDate;
        $this->reportEndDate = $reportEndDate;
        $this->accountName = $accountName;

        $this->initAnalyticsArray();
        if (empty($this->profileId))
            return;

        $this->setAnalyticsArray();
    }

    public function initAnalyticsArray()
    {
        // The values of the columns of the VW Report for the Analytics data
        $analyticsRows = array(
            'sessions' => 0,
            'users' => 0,
            'newUsers' => 0,
            // 'submissions' => 0,
            // 'calls' => 0,
            // 'bounces' => 0,
            'pageViews' => 0,
            // 'avgTimeOnPage' => 0
        );
        // All the media types available
        $mediaTypes = array(
            'search' => $analyticsRows,
            'display' => $analyticsRows,
            'video' => $analyticsRows,
            'social' => $analyticsRows,
            'other' => $analyticsRows
        );
        // The Device Type for the VW Report is either "Desktop" or "Mobile"
        $this->analyticsArray = array(
            'desktop' => $mediaTypes,
            'mobile' => $mediaTypes
        );
        $this->response = array(
            'device' => array(),
            'sources' => array()
        );
    }

    public function getAnalyticsData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to http://ga-dev-tools.appspot.com/explorer/
         */

        $metrics = [
            'ga:sessions',
            'ga:pageviews',
            'ga:sessionDuration',
            'ga:bounceRate'
        ];

        /**
         * [0] -> deviceCategory => 'desktop - mobile - tablet'
         * [1] -> source => 'google - facebook'
         * [2] -> medium => 'referral - organic - cpc'
         * [3] -> 'ga:sessions'
         * [4] -> 'ga:pageviews'
         * [5] -> 'ga:sessionDuration'
         * [6] -> 'ga:bounceRate' 
         */
        $dateFormat = 'Y-m-d';

        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                   // Google Profile Id (Not the Google Analytics Id)
            $this->reportStartDate->format($dateFormat),                               // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportEndDate->format($dateFormat),                                    // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            implode(',', $metrics),  // The metrics data to be retrieved from the API
            array(
                'dimensions' => 'ga:deviceCategory,ga:source,ga:medium',
                'sort' => '-ga:sessions'
            )               // The dimension data to be retrieved from the API.
        );
    }

    public function setAnalyticsArray()
    {
        // get the multi dimensional array from GA API
        try {
            $results = $this->getAnalyticsData();
            // print_r($results);
            // dd($formSubmissionsResult);
        } catch (apiServiceException $e) {
            // Error from the API.
            echo '<p class="errorMessage">There was an API error: ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (\Exception $e) {
            echo '<p class="errorMessage">There was a general error: ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }

        $rows = $results->getRows();
        // print_r($rows);
        if (!empty($results) && $rows &&  count($rows) > 0) {
            /**
             * $rows[0] string The value from ga:deviceCategory
             * $rows[1] string The value from ga:channelGrouping
             * $rows[2] integer The value from ga:sessions
             * $rows[3] integer The value from ga:users
             * $rows[4] integer The value from ga:bounces
             */
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                $deviceType = $rows[$i][0];
                $mediaType = $this->getChannelGrouping($rows[$i]);
                $source = $rows[$i][1];
                $this->setDeviceMediaTraffic($deviceType, $mediaType, $rows[$i]);
                $this->contTrafficByDevice($rows[$i]);
                $this->setReferralSources($source, $rows[$i]);
            }

            // print_r($this->analyticsArray);
            print_r($this->referralSources);
            $this->response['device'] = $this->trafficByDevice;
            $this->response['sources'] = $this->analyticsArray;
            $this->response['referral_sources'] = $this->referralSources;

            // print_r($this->response);
            return $this->response;
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    private function getChannelGrouping($row)
    {
        /**
         * These include Organic, CPC (AKA cost per click for digital ads), Display (for display ads specifically), Direct, Referral, Social, Email, and (Other).
         */
        $channel = $row[2];
        switch ($row[2]) {
            case 'cpc':
                $channel = "paid";
                break;

            case '(not set)':
            case '(none)':
                $channel = "other";
        }
        return $channel;
    }

    private function setDeviceMediaTraffic($deviceType, $mediaType, $row)
    {
        $this->analyticsArray[$deviceType][$mediaType]['sessions'] += $row[3];
        $this->analyticsArray[$deviceType][$mediaType]['pageviews'] += $row[4];
        $this->analyticsArray[$deviceType][$mediaType]['sessionDuration'] += $row[5];
        $this->analyticsArray[$deviceType][$mediaType]['bounceRate'] += $row[6];
    }

    private function setReferralSources($source, $row)
    {
        $this->referralSources[$source]['sessions'] += $row[3];
        $this->referralSources[$source]['pageviews'] += $row[4];
        $this->referralSources[$source]['sessionDuration'] += $row[5];
        $this->referralSources[$source]['bounceRate'] += $row[6];
    }

    private function contTrafficByDevice($row)
    {
        switch ($row[0]) {
            case 'desktop':
                $this->trafficByDevice['desktop'] += 1;
                break;

            case 'mobile':
                $this->trafficByDevice['mobile'] += 1;
                break;

            case 'tablet':
                $this->trafficByDevice['tablet'] += 1;
                break;
        }
    }
}
