<?php

namespace PmAnalyticsPackage\api\Google\API;

use PmAnalyticsPackage\api\Google\Models\Dealership;

class OrganicTrafficAPI extends GoogleAnalyticsAPI
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
        $analyticsRows = array(
            'sessions' => 0,
            'pageViews' => 0,
            'bounceRate' => 0,
            'sessionDuration' => 0,
        );

        // All the media types available
        $mediaTypes = array(
            'search' => $analyticsRows,
            'display' => $analyticsRows,
            'video' => $analyticsRows,
            'social' => $analyticsRows,
            'other' => $analyticsRows
        );

        // $this->analyticsArray = array(
        //     'desktop' => $mediaTypes,
        //     'mobile' => $mediaTypes
        // );
    }

    public function getAnalyticsData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to http://ga-dev-tools.appspot.com/explorer/
         * dimensions=ga:landingPagePath
         * metrics=ga:entrances,ga:bounces
         * sort=-ga:entrances
         */

        $metrics = [
            /**
             * ga:pageviews,ga:sessionDuration,ga:exits
             */
            'ga:pageviews',
            'ga:sessionDuration',
            'ga:organicSearches',
            'ga:sessions',
            'ga:bounces'
        ];
        $dateFormat = 'Y-m-d';

        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                   // Google Profile Id (Not the Google Analytics Id)
            $this->reportStartDate->format($dateFormat),                               // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportEndDate->format($dateFormat),                                    // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            implode(',', $metrics),  // The metrics data to be retrieved from the API
            array(
                'dimensions' => 'ga:channelGrouping,ga:landingPagePath',
                'sort' => '-ga:pageviews'
            )
        );
    }

    public function setAnalyticsArray()
    {
        try {
            $results = $this->getAnalyticsData();
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
        // print_r($rows);
        if (!empty($results) && $rows &&  count($rows) > 0) {
            /**
             * $rows[0] channelGrouping
             * $rows[1] landingPagePath
             * $rows[2] pageviews
             * $rows[3] sessionDuration
             * $rows[4] organicSearches
             * $rows[5] sessions
             * $rows[6] bounces
             */
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            // este loop sirve para obtener el top traffic visited pages, aún falta monthly visits and new users from organic traffic
            for ($i = 0; $i < $numRows; $i++) {
                if ($rows[$i][0] === "Organic Search") {
                    $this->analyticsArray['organic'][$rows[$i][1]]['pageviews'] += $rows[$i][2];
                    $this->analyticsArray['organic'][$rows[$i][1]]['sessionDuration'] += $rows[$i][2];
                    $this->analyticsArray['organic'][$rows[$i][1]]['sessions'] += $rows[$i][5];
                    $this->analyticsArray['organic'][$rows[$i][1]]['bounces'] += $rows[$i][6];
                }
            }

            print_r($this->analyticsArray);
            // $this->response['chart'] = $this->chartArray;
            // $this->response['table'] = $this->analyticsArray;

            // return $this->response;
            // print_r($this->analyticsArray);
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }
}
