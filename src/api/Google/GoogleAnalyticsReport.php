<?php

namespace PmAnalyticsPackage\api\Google;

use Exception;

class GoogleAnalyticsReport
{
    /**
     * @var object The result from the Analytics Service object
     */
    private $analytics;

    /**
     * @var string The Google Profile Id
     */
    private $profileId;

    /**
     * @var string The date for the report. This is Yesterday's date
     */
    private $reportDate;

    /**
     * @var string The account name for this Google Analytics report
     */
    private $accountName;

    /**
     * @var array Holds all the values of the Google Analytics Chrysler Report
     */
    private $analyticsArray;

    /**
     * @var string Holds all the error messages that have occurred
     */
    public $errorMsg = "";

    /**
     * @var string Holds all the warning messages that have occurred
     */
    public $warningMsg = "";

    /**
     * @var string The path to the /data folder outside of the webroot
     */
    private $dataSourcePath;

    /**
     * Construct for Google Analytics Report
     *
     * @param $analytics object Analytics service object
     * @param $profileId string Google Profile ID for the dealership
     * @param $reportDate string Date formatted YYYY-MM-DD
     * @param $accountName string The dealership name
     * @param $dataSourcePath string The path to the /data folder outside of the webroot
     */
    public function __contruct(&$analytics, $profileId, $reportDate, $accountName, $dataSourcePath)
    {
        $this->analytics = &$analytics;
        $this->profileId = $profileId;
        $this->reportDate = $reportDate;
        $this->accountName = $accountName;
        $this->dataSourcePath = $dataSourcePath;

        // Initialize the analytics array
        $this->initAnalyticsArray();
        // Get "Unique Visits", "Visits" and "Bounces"
        $this->setAnalyticsArray();
        // Get "Form Submissions"
        $this->setFormSubmissions();
        // Get "Phone Leads"
        $this->setPhoneLeads();
        // Get new vehicle inventory visits
        $this->setNewVehiclesArray();
        // Get new vehicle inventory visits
        $this->setMediumCPCArray();
    }

    private function initAnalyticsArray()
    {
        // The values of the columns of the Chrysler Report for the Analytics data
        $analyticsRows = array(
            'users'         => 0,           // Unique Visits
            'sessions'      => 0,           // Visits
            'bounces'       => 0,           // Bounces
            'submissions'   => 0,           // Form Submissions
            'calls'         => 0,           // Phone Leads
            'pageViews'     => 0,           // pageViews
        );

        // All the media types available
        $mediaTypes = array(
            'search'        => $analyticsRows,          // Search
            'display'       => $analyticsRows,          // Display
            'video'         => $analyticsRows,          // Video
            'total'         => $analyticsRows,          // Total
            'organic'       => $analyticsRows,          // Organic added by Brad
            'newVehicles'   => $analyticsRows,          // newVehicles added by Brad
            'cpc'           => $analyticsRows,          // cpc added by Brad
            'other'           => $analyticsRows,
        );

        // The Device Type for the Chrysler Report is either "Desktop" or "Mobile"
        $this->analyticsArray = array(
            'desktop'   => $mediaTypes,
            'mobile'    => $mediaTypes,
            'tablet'    => $mediaTypes
        );
    }

    private function setAnalyticsArray()
    {
        // get the multi dimensional array from GA API
        try {
            $results = $this->getAnalyticsData();
        } catch (apiServiceException $e) {
            // Error from the API.
            echo '<p class="errorMessage">There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            echo '<p class="errorMessage">There was a general error : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }

        if (!empty($results) && count($results->getRows()) > 0) {
            /**
             * $rows[0] string The value from ga:deviceCategory (desktop, mobile or tablet)
             * $rows[1] string The value from ga:channelGrouping (We only need Paid Search or Display)
             * $rows[2] string The value from ga:isTrueViewVideoAd (Yes or No)
             * $rows[3] integer The value from ga:users The Unique Visits
             * $rows[4] integer The value from ga:sessions The Visits
             * $rows[5] integer The value from ga:bouces The Bounces
             */
            $rows = $results->getRows();
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                // set "Unique Visits", "Visits" and "Bounces"
                $this->getVisitsAndBounces($rows[$i]);
            }
            // Add up all the Unique Visits, Visits and Bounces
            $this->setTotalVisitsAndBounces();
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    /**
     * A call to the Google Analytics to retrieve data from the API.
     * To get more dimensions and metrics, please check out the reference
     * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     *
     * @return array A multi dimensional array from Google Analytics API
     */
    private function getAnalyticsData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to https://ga-dev-tools.appspot.com/query-explorer/
         */
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                           // Google Profile Id (Not the Google Analytics Id)
            $this->reportDate,                                                                  // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportDate,                                                                  // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            'ga:users,ga:sessions,ga:bounces',                                                  // The metrics data to be retrieved from the API
            array('dimensions' => 'ga:deviceCategory,ga:channelGrouping,ga:isTrueViewVideoAd')  // The dimension data to be retrieved from the API.
        );
    }

    /**
     * Store the "Unique Visits", "Visits" and "Bounces" into the analyticsArray
     *
     * @param $row array A row of the multi dimensional array from GA API results array
     */
    private function getVisitsAndBounces($row)
    {
        /**
         * $row[0] string The values are "desktop", "mobile" or "tablet"
         * This value comes from the ga:deviceCategory dimension
         */
        // Desktop
        if ($row[0] === 'desktop') {
            /**
             * $row[1] string The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
             * This value comes from the ga:channelGrouping dimension
             */
            if ($row[1] === 'Paid Search') {
                $this->setVisitsAndBounces('desktop', 'search', $row);
            }
            // If $row[1] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[1] === 'Display') {
                /**
                 * $row[2] string The values are "Yes" or "No"
                 * This value comes from the ga:isTrueViewVideoAd dimension
                 */
                if ($row[2] === 'No') {
                    $this->setVisitsAndBounces('desktop', 'display', $row);
                }
                // This is a video ad
                else {
                    $this->setVisitsAndBounces('desktop', 'video', $row);
                }
            } else if ($row[1] === 'Organic Search') {
                $this->setVisitsAndBounces('desktop', 'organic', $row);
            }
            // This is Other for Desktop
            else {
                $this->setVisitsAndBounces('desktop', 'other', $row);
            }
        }
        // Mobile
        else if ($row[0] === 'mobile') {
            /**
             * $row[1] string The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
             * This value comes from the ga:channelGrouping dimension
             */
            if ($row[1] === 'Paid Search') {
                $this->setVisitsAndBounces('mobile', 'search', $row);
            }
            // If $row[1] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[1] === 'Display') {
                /**
                 * $row[2] string The values are "Yes" or "No"
                 * This value comes from the ga:isTrueViewVideoAd dimension
                 */
                if ($row[2] === 'No') {
                    $this->setVisitsAndBounces('mobile', 'display', $row);
                }
                // This is a video ad
                else {
                    $this->setVisitsAndBounces('mobile', 'video', $row);
                }
            } else if ($row[1] === 'Organic Search') {
                $this->setVisitsAndBounces('mobile', 'organic', $row);
            }
            // This is Other for Mobile
            else {
                $this->setVisitsAndBounces('mobile', 'other', $row);
            }
        }
        // Tablet
        else if ($row[0] === 'tablet') {
            /**
             * $row[1] string The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
             * This value comes from the ga:channelGrouping dimension
             */
            if ($row[1] === 'Paid Search') {
                $this->setVisitsAndBounces('tablet', 'search', $row);
            }
            // If $row[1] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[1] === 'Display') {
                /**
                 * $row[2] string The values are "Yes" or "No"
                 * This value comes from the ga:isTrueViewVideoAd dimension
                 */
                if ($row[2] === 'No') {
                    $this->setVisitsAndBounces('tablet', 'display', $row);
                }
                // This is a video ad
                else {
                    $this->setVisitsAndBounces('tablet', 'video', $row);
                }
            } else if ($row[1] === 'Organic Search') {
                $this->setVisitsAndBounces('tablet', 'organic', $row);
            }
            // This is Other for Tablet
            else {
                $this->setVisitsAndBounces('tablet', 'other', $row);
            }
        }
    }

    /**
     * Store the values for all the "Unique Visits", "Visits" and "Bounces"
     *
     * @param $device string The device type which is either "desktop" or "mobile"
     * @param $channel string The channel where the ad was displayed. ie. "search", "display" or "video"
     * @param $row array A row from the Analytics API call. $row[3] are users, $row[4] are sessions and $row[5] are bounces
     */
    private function setVisitsAndBounces($device, $channel, $row)
    {
        $this->analyticsArray[$device][$channel]['users'] += $row[3];     // Users
        $this->analyticsArray[$device][$channel]['sessions'] += $row[4];  // Sessions
        $this->analyticsArray[$device][$channel]['bounces'] += $row[5];   // Bounces
    }

    /**
     * Count all the Unique Visits, Visits and Bounces and store them into the Analytics Array
     */
    private function setTotalVisitsAndBounces()
    {
        // Desktop
        $this->analyticsArray['desktop']['total']['users'] = $this->analyticsArray['desktop']['search']['users'] + $this->analyticsArray['desktop']['display']['users'] + $this->analyticsArray['desktop']['video']['users'] + $this->analyticsArray['desktop']['organic']['users'] + $this->analyticsArray['desktop']['other']['users'];
        $this->analyticsArray['desktop']['total']['sessions'] = $this->analyticsArray['desktop']['search']['sessions'] + $this->analyticsArray['desktop']['display']['sessions'] + $this->analyticsArray['desktop']['video']['sessions'] + $this->analyticsArray['desktop']['organic']['sessions'] + $this->analyticsArray['desktop']['other']['sessions'];
        $this->analyticsArray['desktop']['total']['bounces'] = $this->analyticsArray['desktop']['search']['bounces'] + $this->analyticsArray['desktop']['display']['bounces'] + $this->analyticsArray['desktop']['video']['bounces'] + $this->analyticsArray['desktop']['organic']['sessions'] + $this->analyticsArray['desktop']['other']['sessions'];
        // Mobile
        $this->analyticsArray['mobile']['total']['users'] = $this->analyticsArray['mobile']['search']['users'] + $this->analyticsArray['mobile']['display']['users'] + $this->analyticsArray['mobile']['video']['users'] + $this->analyticsArray['mobile']['organic']['users'] + $this->analyticsArray['mobile']['other']['users'];
        $this->analyticsArray['mobile']['total']['sessions'] = $this->analyticsArray['mobile']['search']['sessions'] + $this->analyticsArray['mobile']['display']['sessions'] + $this->analyticsArray['mobile']['video']['sessions'] + $this->analyticsArray['mobile']['organic']['sessions'] + $this->analyticsArray['mobile']['other']['sessions'];
        $this->analyticsArray['mobile']['total']['bounces'] = $this->analyticsArray['mobile']['search']['bounces'] + $this->analyticsArray['mobile']['display']['bounces'] + $this->analyticsArray['mobile']['video']['bounces'] + $this->analyticsArray['mobile']['organic']['bounces'] + $this->analyticsArray['mobile']['other']['bounces'];
        // Tablet
        $this->analyticsArray['tablet']['total']['users'] = $this->analyticsArray['tablet']['search']['users'] + $this->analyticsArray['tablet']['display']['users'] + $this->analyticsArray['tablet']['video']['users'] + $this->analyticsArray['tablet']['organic']['users'] + $this->analyticsArray['tablet']['other']['users'];
        $this->analyticsArray['tablet']['total']['sessions'] = $this->analyticsArray['tablet']['search']['sessions'] + $this->analyticsArray['tablet']['display']['sessions'] + $this->analyticsArray['tablet']['video']['sessions'] + $this->analyticsArray['tablet']['organic']['sessions'] + $this->analyticsArray['tablet']['other']['sessions'];
        $this->analyticsArray['tablet']['total']['bounces'] = $this->analyticsArray['tablet']['search']['bounces'] + $this->analyticsArray['tablet']['display']['bounces'] + $this->analyticsArray['tablet']['video']['bounces'] + $this->analyticsArray['tablet']['organic']['bounces'] + $this->analyticsArray['tablet']['other']['bounces'];
    }

    /**
     * Stores the "Form Submissions" for the report
     *
     * @return void
     */
    private function setFormSubmissions()
    {
        try {
            $results = $this->getFormSubmissionData();
        } catch (apiServiceException $e) {
            echo '<p class="errorMessage">There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            echo '<p class="errorMessage">There was a general error : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }
        if (!empty($results) && count($results->getRows()) > 0) {
            /**
             * $row[0] string The Source / Medium. The are looking for "google / cpc"
             * $row[1] string The Device Category. The values are "desktop", "mobile" or "tablet". This value comes from the ga:deviceCategory dimension
             * $row[2] string The Page. The URI of the pageview
             * $row[3] string The Default Channel Grouping. The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
             * $row[4] string The TrueView Video Ad. The values are "Yes" or "No"
             * $row[5] integer The Pageviews. The number of pageviews from the success page. This value comes from the ga:pageviews metric
             */
            $rows = $results->getRows();
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                // set the Form Submissions
                $this->getFormSubmissions($rows[$i]);
            }
            // Add up all the Form Submissions
            $this->setTotalFormSubmissions();
        }
    }

    /**
     * Get the form submissions from the Google Analytics API
     *
     * @return array A multi dimensional array from Google Analytics API
     */
    private function getFormSubmissionData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to http://ga-dev-tools.appspot.com/explorer/
         */
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                       // Google Profile Id (Not the Google Analytics Id)
            $this->reportDate,                                                              // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportDate,                                                              // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            'ga:pageviews',                                                                 // The metrics data to be retrieved from the API
            array(
                'dimensions' => 'ga:sourceMedium,ga:deviceCategory,ga:pagePath,ga:channelGrouping,ga:isTrueViewVideoAd',
                'filters' => 'ga:sourceMedium==google / cpc;ga:pagePath=@confirm.htm'
            )   // The dimension and filter data to be retrieved from the API.
        );
    }

    /**
     * Store the "Form Submissions" into the analyticsArray. We count the number of pageviews from the success page
     *
     * @param $row array A row of the multi dimensional array from GA API results array
     */
    private function getFormSubmissions($row)
    {
        /**
         * $row[0] string The Source / Medium. The are looking for "google / cpc"
         * $row[1] string The Device Category. The values are "desktop", "mobile" or "tablet". This value comes from the ga:deviceCategory dimension
         * $row[2] string The Page. The URI of the pageview
         * $row[3] string The Default Channel Grouping. The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
         * $row[4] string The TrueView Video Ad. The values are "Yes" or "No"
         * $row[5] integer The Pageviews. The number of pageviews from the success page. This value comes from the ga:pageviews metric
         */
        // Desktop
        if ($row[1] === 'desktop') {
            if ($row[3] === 'Paid Search') {
                $this->analyticsArray['desktop']['search']['submissions'] += $row[5];
            }
            // If $row[3] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[3] === 'Display') {
                if ($row[4] === 'No') {
                    $this->analyticsArray['desktop']['display']['submissions'] += $row[5];
                }
                // This is a video ad
                else {
                    $this->analyticsArray['desktop']['video']['submissions'] += $row[5];
                }
            }
            // This is Other for Desktop
            else {
                $this->analyticsArray['desktop']['other']['submissions'] += $row[5];
            }
        }
        // Mobile
        else {
            if ($row[3] === 'Paid Search') {
                $this->analyticsArray['mobile']['search']['submissions'] += $row[5];
            }
            // If $row[3] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[3] === 'Display') {
                if ($row[4] === 'No') {
                    $this->analyticsArray['mobile']['display']['submissions'] += $row[5];
                }
                // This is a video ad
                else {
                    $this->analyticsArray['mobile']['video']['submissions'] += $row[5];
                }
            }
            // This is Other for Mobile
            else {
                $this->analyticsArray['mobile']['other']['submissions'] += $row[5];
            }
        }
    }

    /**
     * Count all the Form Submissions and store them into the Analytics Array
     */
    private function setTotalFormSubmissions()
    {
        // Desktop
        $this->analyticsArray['desktop']['total']['submissions'] = $this->analyticsArray['desktop']['search']['submissions'] + $this->analyticsArray['desktop']['display']['submissions'] + $this->analyticsArray['desktop']['video']['submissions'];
        // Mobile
        $this->analyticsArray['mobile']['total']['submissions'] = $this->analyticsArray['mobile']['search']['submissions'] + $this->analyticsArray['mobile']['display']['submissions'] + $this->analyticsArray['mobile']['video']['submissions'];
    }

    /**
     * Stores the "Phone Leads" for the report
     *
     * @return void
     */
    private function setPhoneLeads()
    {
        try {
            $results = $this->getPhoneLeadsData();
        } catch (apiServiceException $e) {
            echo '<p class="errorMessage">There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            echo '<p class="errorMessage">There was a general error : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }
        if (!empty($results) && count($results->getRows()) > 0) {
            $rows = $results->getRows();
            $numRows = count($rows);
            // loop through all the rows of the multi dimensional array
            for ($i = 0; $i < $numRows; $i++) {
                // set the Form Submissions
                $this->getPhoneLeads($rows[$i]);
            }
            // Add up all the Form Submissions
            $this->setTotalPhoneLeads();
        }
    }

    /**
     * Store the "Form Submissions" into the analyticsArray. We count the number of pageviews from the success page
     *
     * @param $row array A row of the multi dimensional array from GA API results array
     */
    private function getPhoneLeads($row)
    {
        /**
         * $row[0] string The Device Category. The values are "desktop", "mobile" or "tablet". This value comes from the ga:deviceCategory dimension
         * $row[1] string The Default Channel Grouping. The values are "Paid Search", "Display", "Organic Search", "Direct", "Social" and "Referral"
         * $row[2] string The TrueView Video Ad. The values are "Yes" or "No"
         * $row[3] integer Goal 20 Completions. The number of phone leads
         */
        // Desktop
        if ($row[0] === 'desktop') {
            if ($row[1] === 'Paid Search') {
                $this->analyticsArray['desktop']['search']['calls'] += $row[3];
            }
            // If $row[1] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[1] === 'Display') {
                if ($row[2] === 'No') {
                    $this->analyticsArray['desktop']['display']['calls'] += $row[3];
                }
                // This is a video ad
                else {
                    $this->analyticsArray['desktop']['video']['calls'] += $row[3];
                }
            }
            // This is Other for Desktop
            else {
                $this->analyticsArray['desktop']['other']['calls'] += $row[3];
            }
        }
        // Mobile
        else {
            if ($row[1] === 'Paid Search') {
                $this->analyticsArray['mobile']['search']['calls'] += $row[3];
            }
            // If $row[1] equals display, then it can be for "Display" or "Video" metrics
            else if ($row[1] === 'Display') {
                if ($row[2] === 'No') {
                    $this->analyticsArray['mobile']['display']['calls'] += $row[3];
                }
                // This is a video ad
                else {
                    $this->analyticsArray['mobile']['video']['calls'] += $row[3];
                }
            }
            // This is Other for Mobile
            else {
                $this->analyticsArray['mobile']['other']['calls'] += $row[3];
            }
        }
    }

    /**
     * Count all the Form Submissions and store them into the Analytics Array
     */
    private function setTotalPhoneLeads()
    {
        // Desktop
        $this->analyticsArray['desktop']['total']['calls'] = $this->analyticsArray['desktop']['search']['calls'] + $this->analyticsArray['desktop']['display']['calls'] + $this->analyticsArray['desktop']['video']['calls'];
        // Mobile
        $this->analyticsArray['mobile']['total']['calls'] = $this->analyticsArray['mobile']['search']['calls'] + $this->analyticsArray['mobile']['display']['calls'] + $this->analyticsArray['mobile']['video']['calls'];
    }

    /**
     * Retrieve the data necessary for the report from the multi dimensional array from Google Analytics API
     */
    private function setNewVehiclesArray()
    {
        // get the multi dimensional array from GA API
        try {
            $results = $this->getNewVehiclesData();
        } catch (apiServiceException $e) {
            // Error from the API.
            echo '<p class="errorMessage">There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            echo '<p class="errorMessage">There was a general error : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }

        if (!empty($results) && count($results->getRows()) > 0) {
            /**
             * $rows[0] pageviews
             */
            $rows = $results->getRows();
            $this->analyticsArray['desktop']['newVehicles']['pageViews'] = $rows[0][0];
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    /**
     * A call to the Google Analytics to retrieve data from the API.
     * To get more dimensions and metrics, please check out the reference
     * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     *
     * @return array A multi dimensional array from Google Analytics API
     */
    private function getNewVehiclesData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to https://ga-dev-tools.appspot.com/query-explorer/
         */
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                           // Google Profile Id (Not the Google Analytics Id)
            $this->reportDate,                                                                  // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportDate,                                                                  // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            'ga:pageviews,ga:sessions,ga:users',                                                 // The metrics data to be retrieved from the API
            array('filters' => 'ga:pagePath=~/new-inventory/index.htm')
            //array('dimensions' => 'ga:deviceCategory,ga:channelGrouping,ga:pagePath')
            // 'filters' => 'ga:pagePath=~(/new-inventory/index.htm|/new-vehicles/)')  // The dimension data to be retrieved from the API.
        );
    }

    /**
     * Retrieve the data necessary for the report from the multi dimensional array from Google Analytics API
     */
    private function setMediumCPCArray()
    {
        // get the multi dimensional array from GA API
        try {
            $results = $this->getMediumCPCData();
        } catch (apiServiceException $e) {
            // Error from the API.
            echo '<p class="errorMessage">There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was an API error : ' . $e->getCode() . ' : ' . $e->getMessage() . PHP_EOL;
        } catch (Exception $e) {
            echo '<p class="errorMessage">There was a general error : ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg .= 'There was a general error : ' . $e->getMessage() . PHP_EOL;
        }

        if (!empty($results) && count($results->getRows()) > 0) {
            /**
             * $rows[0] pageviews
             */
            $rows = $results->getRows();
            $this->analyticsArray['desktop']['cpc']['pageViews'] = $rows[0][1];
        }
        // There are no Google Analytics data
        else {
            echo '<p class="errorMessage">No Google Analytics results found for account: <span class="accountName">' . $this->accountName . '</span></p>' . PHP_EOL;
            $this->warningMsg .= 'No Google Analytics results found for account: ' . $this->accountName . PHP_EOL;
        }
    }

    /**
     * A call to the Google Analytics to retrieve data from the API.
     * To get more dimensions and metrics, please check out the reference
     * https://developers.google.com/analytics/devguides/reporting/core/dimsmets
     *
     * @return array A multi dimensional array from Google Analytics API
     */
    private function getMediumCPCData()
    {
        /**
         * To test the metrics, dimensions and/or filters go to https://ga-dev-tools.appspot.com/query-explorer/
         */
        return $this->analytics->data_ga->get(
            'ga:' . $this->profileId,                                                           // Google Profile Id (Not the Google Analytics Id)
            $this->reportDate,                                                                  // Start Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            $this->reportDate,                                                                  // End Date: YYYY-MM-DD, today, yesterday, or 7daysAgo
            'ga:pageviews,ga:sessions,ga:users',                                                 // The metrics data to be retrieved from the API
            array('filters' => 'ga:medium==cpc')
            //array('dimensions' => 'ga:deviceCategory,ga:channelGrouping,ga:pagePath')
            // 'filters' => 'ga:pagePath=~(/new-inventory/index.htm|/new-vehicles/)')  // The dimension data to be retrieved from the API.
        );
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
