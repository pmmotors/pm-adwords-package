<?php

namespace PmAnalyticsPackage\api\DialogTech;

use Dotenv\Dotenv;

class DialogTechReport
{
    /**
     * @var string The Start date for the report.
     */
    protected $reportStartDate;

    /**
     * @var string The End date for the report.
     */
    protected $reportEndDate;

    /**
     * @var string The account name for this AdWords report
     */
    private $accountName;

    /**
     * @var array Holds all the values of the AdWords VW Report
     */
    protected $dialogTechArray;

    /**
     * @var string Holds all the error messages that have occurred
     */
    private $errorMsg = '';

    /**
     * @var string Holds all the warning messages that have occurred
     */
    private $warningMsg = '';

    /**
     * @var string The path to the /data folder outside of the webroot
     */
    private $dataSourcePath = '/app/dialogtech/';

    /**
     * @var string the name of the file
     */
    private $dataReportName = 'dt-report.csv';

    /**
     * @var string api key
     */
    protected $dataAPIKey = null;

    /**
     * @var string the data URL
     */
    protected $datURL = null;

    /**
     * @var array of phone numbers
     */
    protected $phoneNumberArr;

    public function __construct($reportStartDate, $reportEndDate, $accountName, $phoneNumberArr)
    {
        if (is_array($phoneNumberArr)) {
            $phoneNumberArr = implode(',', $phoneNumberArr);
        }
        $phoneNumberArr = array_map('trim', explode(',', $phoneNumberArr));
        $this->reportStartDate = $reportStartDate; // Dialog Tech Date has to be YYYYMMDD
        $this->reportEndDate = $reportEndDate; // Dialog Tech Date has to be YYYYMMDD
        $this->accountName = htmlentities($accountName);
        $this->phoneNumberArr = $phoneNumberArr;

        $configs = include('src/config/dialogtech.php');

        $this->dataAPIKey = $configs['apiKey'];
        $this->dataURL = $configs['url'];

        // initialize the Dialog Tech Array
        $this->initDialogTechArray();
        $this->getReport();
    }

    /**
     * Initializes the DialogTech array that is going to contain all the data
     */
    private function initDialogTechArray()
    {
        // The values of the columns of the VW Report for the AdWords Data
        $dialogTechRows = array(
            'calls' => 0
        );
        // All the media types available
        $mediaTypes = array(
            // Search Network
            'search' => $dialogTechRows,
            'display' => $dialogTechRows,
            'social' => $dialogTechRows
        );
        // The Device Type for the VW Report is either "Desktop" or "Mobile"
        $this->dialogTechArray = array(
            'desktop' => $mediaTypes,
            'mobile' => $mediaTypes
        );
    }

    protected function apiPostData()
    {
        $postData = array(
            'action' => 'report.call_detail',
            //'format' => 'xml', // csv
            'api_key' => $this->dataAPIKey,
            'start_date' => $this->reportStartDate->format('Ymd'),
            'end_date' => $this->reportEndDate->format('Ymd'),
            'date_added' => 1,
            'dnis' => 1,
            'phone_label' => 1,
            'call_type' => 1,
            'call_type_filter' => 'All',
            // 'activity_info' => 1,
            // 'activity_keyword' => 1,
            // 'adj_enhanced' => 1,
            // 'adj_network' => 1,
            // 'adj_switch' => 1,
            // 'ani' => 1,
            // 'call_duration' => 1,
            // 'call_transfer_status' => 1,
            // 'call_type' => 1,
            // 'call_type_value' => 1,
            // 'city' => 1,
            // 'click_description' => 1,
            // 'conversion' => 1,
            // 'conversion_amount' => 1,
            // 'conversion_note' => 1,
            // 'custom_id' => 1,
            // 'custom_value' => 1,
            // 'dnis' => 1,
            // 'domain' => 1,
            // 'enhanced_minutes' => 1,
            // 'extension_adgroup' => 1,
            // 'extension_keywords' => 1,
            // 'findme_number_label' => 1,
            // 'first_activity' => 1,
            // 'first_name' => 1,
            // 'ga_client_id' => 1,
            // 'gclid' => 1,
            // 'geo_lookup_attempt' => 1,
            // 'geo_lookup_result' => 1,
            // 'inbound_ani_type' => 1,
            // 'last_activity' => 1,
            // 'last_name' => 1,
            // 'location_name' => 1,
            // 'netget_status' => 1,
            // 'network_minutes' => 1,
            // 'phone_label' => 1,
            // 'platform' => 1,
            'pool_name' => 1,
            // 'recording' => 1,
            // 'ring_time' => 1,
            // 'search_term' => 1,
            // 'sid' => 1,
            // 'sourceguard' => 1,
            // 'state' => 1,
            // 'street_address' => 1,
            // 'switch_minutes' => 1,
            // 'transfer_to_number' => 1,
            // 'transfer_type' => 1,
            // 'url_tag' => 1,
            // 'valuetrack' => 1,
            // 'zipcode' => 1
        );
        return $postData;
    }

    /**
     * Download the report from DialogTech
     *
     * @param $filePath string The file path where the report is going to be downloaded
     */
    private function downloadReport($filePath)
    {

        $postData = $this->apiPostData();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->dataURL);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); //need this
        $file = fopen($filePath, "w+");
        curl_setopt($curl, CURLOPT_FILE, $file);
        $result = curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Delete the DialogTech CSV file (report.csv) and the Log file (report_download.log)
     *
     * @param $csvFile string The CSV file path and file name to be deleted
     */
    private function deleteReportAndLog($csvFile)
    {
        // delete the csv file
        if (!unlink($csvFile)) {
            echo '<p>There was an error deleting the csv file for ' . $this->accountName . '</p>' . PHP_EOL;
            $this->warningMsg .= 'There was an error deleting the csv file for ' . $this->accountName . PHP_EOL;
        }
        // delete the log file
        // $logFile = dirname(__FILE__) . '/report_download.log';
        // /var/www/storage/app/dialogtech/report_download.log
        $logFile = storage_path() . $this->dataSourcePath . 'report_download.log';
        // if there is a log file, delete it
        if (is_file($logFile)) {
            if (!unlink($logFile)) {
                echo '<p>There was an error deleting the Report Download Log file for ' . $this->accountName . '</p>' . PHP_EOL;
                $this->warningMsg .= 'There was an error deleting the Report Download Log file for ' . $this->accountName . PHP_EOL;
            }
        }
    }

    private function removeNonNumbers($string)
    {
        $number = preg_replace('/\D/', '', $string);
        return $number;
    }

    /**
     * Parse all the data from the Account CSV file and put it into an array
     *
     * @param $csvData array All the data that came in the CSV report
     */
    protected function setCallData($csvData)
    {
        /**
         * $row[0] string Date Added
         * $row[1] call type, could by inbound, .
         * $row[2] string dnis The number dialed
         * $row[3] string The label you assigned to the phone number in phone routing
         * $row[4] string The label you assigned to the phone number in phone routing
         */
        $callTypeIndex = 1;
        $phoneNumberIndex = 2;
        $phoneLableIndex = 3;
        $poolNameIndex = 4;

        foreach ($this->phoneNumberArr as $phoneNumber) {
            $phoneNumber = $this->removeNonNumbers($phoneNumber);
            foreach ($csvData as $key => $row) {
                if ($key > 0 && isset($row[0])) {
                    if (isset($row[$phoneNumberIndex]) && $row[$phoneNumberIndex] === $phoneNumber) {
                        // Search Network
                        $this->dialogTechArray['mobile']['search']['calls']++;

                        if (!array_key_exists($phoneNumber, $this->dialogTechArray['mobile']['search'])) {
                            $this->dialogTechArray['mobile']['search'][$phoneNumber] = ['calls' => 0];
                        }
                        // Search Network
                        $this->dialogTechArray['mobile']['search'][$phoneNumber]['calls']++;
                    }
                }
            }
        }
    }

    protected function getPoolNameFromPhoneLabel($phoneLabel)
    {
        $phoneLabel = strtolower($phoneLabel);
        if (strpos($phoneLabel, 'sales') !== false) return "sales";
        if (strpos($phoneLabel, 'parts') !== false) return "parts";
        if (strpos($phoneLabel, 'service') !== false) return "service";
        return '';
    }

    /**
     * Get the report from the DialogTech API and put it into an array
     *
     * @param $user object The AdWords user needed to get its data
     * @param $reportType string The type of report to download. Either "video" or "account"
     */
    private function getReport()
    {
        $dotenv = new Dotenv('./');
        $dotenv->safeLoad();

        $env = $_ENV['MODE'];

        $csvFile = $env === 'dev' ?
            $this->dataSourcePath . $this->dataReportName
            : storage_path() . $this->dataSourcePath . $this->dataReportName;
        //$csvFile = dirname(__FILE__) . '/'.$this->dataReportName;
        // $csvFile = storage_path() . $this->dataSourcePath . $this->dataReportName;
        $this->downloadReport($csvFile);
        // parse the csv file and extract the data
        if (is_file($csvFile)) {
            $csvData = array_map('str_getcsv', file($csvFile));
            // print_r($csvData);
            // Delete the CSV file and the Log file created by the downloadAccountReport
            $this->deleteReportAndLog($csvFile);
            $this->setCallData($csvData);
        } else {
            echo '<p>There is NO dialog tech CSV file present for ' . $this->accountName . '.</p>' . PHP_EOL;
            $this->errorMsg .= 'There is NO DialogTech file present for ' . $this->accountName . '.';
        }
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
     * @return array The AdWords array containing all the data for the report
     */
    public function getDialogTechArray()
    {
        return $this->dialogTechArray;
    }
}
