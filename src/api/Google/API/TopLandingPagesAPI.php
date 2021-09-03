<?php

namespace PmAnalyticsPackage\api\Google\API;

use PmAnalyticsPackage\api\Google\Models\Dealership;

class TopLandingPagesAPI extends GoogleAnalyticsAPI
{
    private $chartArray = array();

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
            'ga:entrances',
            'ga:bounces',
            'ga:pageviews',
            'ga:sessions',
            'ga:sessionDuration'
        ];
        $dateFormat = 'Y-m-d';

        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                   // Google Profile Id (Not the Google Analytics Id)
            $this->reportStartDate->format($dateFormat),                               // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportEndDate->format($dateFormat),                                    // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            implode(',', $metrics),  // The metrics data to be retrieved from the API
            array(
                'dimensions' => 'ga:landingPagePath',
                'sort' => '-ga:entrances'
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
             * $rows[0] landingPagePath
             * $rows[1] ga:entrances
             * $rows[2] ga:bounces
             * $rows[3] ga:pageviews
             * $rows[4] ga:sessions
             * $rows[5] ga:sessionDuration
             */
            $numRows = count($rows);
            $cont = 0;
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                $landingPagePath = $rows[$i][0];
                $this->setLandingPage($landingPagePath, $rows[$i]);
                // if ($i <= 8)
                //     $this->setLandingPage($landingPagePath, $rows[$i]);
                // else
                //     $cont += $rows[$i][1];
            }
            // $this->chartArray['others'] = $cont;

            $this->response['chart'] = $this->chartArray;
            $this->response['table'] = $this->analyticsArray;

            return $this->response;
            // print_r($this->analyticsArray);
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    private function setLandingPage($landingPagePath, $row)
    {
        $this->chartArray[$landingPagePath] = $row[1];
        $this->analyticsArray[$landingPagePath]['pageviews'] = $row[3];
        $this->analyticsArray[$landingPagePath]['sessions'] = $row[4];
        $this->analyticsArray[$landingPagePath]['bounces'] = $row[2];
        $this->analyticsArray[$landingPagePath]['sessionDuration'] = $row[5];
    }
}
