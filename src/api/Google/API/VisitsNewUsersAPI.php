<?php

namespace PmAnalyticsPackage\api\Google\API;

use PmAnalyticsPackage\api\Google\Models\Dealership;

class VisitsNewUsersAPI extends GoogleAnalyticsAPI
{
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
            'submissions' => 0,
            // 'calls' => 0,
            // 'bounces' => 0,
            // 'pageViews' => 0,
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
    }

    /**
     * A call to the Google Analytics to retrieve data from the API.
     * To get more dimensions and metrics, please check out the reference
     * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     *
     * @return array A multi dimensional array from Google Analytics API
     */
    public function getAnalyticsData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to http://ga-dev-tools.appspot.com/explorer/
         */

        $metrics = [
            'ga:sessions',
            'ga:users',
            'ga:newUsers',
            // 'ga:bounces',
            // 'ga:goal1Completions',
            // 'ga:goal20Completions',
            // 'ga:pageviews',
            // 'ga:avgTimeOnPage'
        ];
        $dateFormat = 'Y-m-d';

        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                   // Google Profile Id (Not the Google Analytics Id)
            $this->reportStartDate->format($dateFormat),                               // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportEndDate->format($dateFormat),                                    // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            implode(',', $metrics),  // The metrics data to be retrieved from the API
            array('dimensions' => 'ga:deviceCategory,ga:channelGrouping')               // The dimension data to be retrieved from the API.
        );
    }

    public function getFormSubmissions()
    {
        $metrics = [
            'ga:pageviews',
        ];
        $dateFormat = 'Y-m-d';

        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                   // Google Profile Id (Not the Google Analytics Id)
            $this->reportStartDate->format($dateFormat),                               // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportEndDate->format($dateFormat),                                    // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            implode(',', $metrics),  // The metrics data to be retrieved from the API
            array(
                'dimensions' => 'ga:deviceCategory,ga:channelGrouping',
                'filters' => 'ga:pagePath=~(thank-you|confirm.htm|thankyou)'
            )               // The dimension data to be retrieved from the API.
        );
    }

    /**
     * Retrieve the data necessary for the report from the multi dimensional array from Google Analytics API
     */
    public function setAnalyticsArray()
    {
        // get the multi dimensional array from GA API
        try {
            $results = $this->getAnalyticsData();
            $formSubmissionsResult = $this->getFormSubmissions();
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
        // echo "--- rows sin tocar ---" . PHP_EOL;
        print_r($rows);
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
                $deviceType = $this->getDeviceType($rows[$i]);
                $mediaType = $this->getMediaType($rows[$i]);
                $this->setDeviceMedia($deviceType, $mediaType, $rows[$i]);
            }

            //FORM SUBMI
            $rows = $formSubmissionsResult->getRows();
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                $row = $rows[$i];
                $deviceType = $this->getDeviceType($row);
                $mediaType = $this->getMediaType($row);
                $this->analyticsArray[$deviceType][$mediaType]['submissions'] += $row[2];
            }

            // echo PHP_EOL . "--- analyticsArray ---" . PHP_EOL;
            // print_r($this->analyticsArray);
            //END FORM SUBMI
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    private function getDeviceType($row)
    {
        /**
         * $row[0] string The value from ga:deviceCategory. Possible values are "desktop", "mobile" and "tablet"
         */
        return $row[0] == 'desktop' ? 'desktop' : 'mobile';
    }

    private function getMediaType($row)
    {
        /**
         * $row[1] string The values are "Direct", "Organic Search", "Paid Search", "Referral", "Display" or "Social"
         * The value comes from the ga:channelGrouping dimension
         */

        // This is not required but I'm going to include. This would be "Referral" or "Direct"
        $mediaType = 'other';
        switch ($row[1]) {
            case 'Paid Search':
                $mediaType = 'search';
                break;
            case 'Display':
                $mediaType = 'display';
                break;
            case 'Social':
                $mediaType = 'social';
                break;
        }
        return $mediaType;
    }

    /**
     * Add all the Sessions, Users, Bounces, Submissions and Calls. Then put them into the Analytics array
     *
     * @param $deviceType string The values are either "desktop" or "mobile"
     * @param $mediaType string The values are "search", "display", "social" or "other"
     * @param $row array A row from the results of the Analytics API call
     */
    private function setDeviceMedia($deviceType, $mediaType, $row)
    {
        // $row[2] integer The value of ga:sessions
        $this->analyticsArray[$deviceType][$mediaType]['sessions'] += $row[2];
        // $row[3] integer The value of ga:users
        $this->analyticsArray[$deviceType][$mediaType]['users'] += $row[3];
        // $row[3] integer The value of ga:newUsers
        $this->analyticsArray[$deviceType][$mediaType]['newUsers'] += $row[4];
    }
}
