<?php

namespace PmAnalyticsPackage\AdWords;

use Exception;
use Google\AdsApi\AdWords\Reporting\v201809\ReportDownloader;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\AdWords\Reporting\v201806\DownloadFormat;
use Google\AdsApi\AdWords\Reporting\v201806\ReportDefinition;
use Google\AdsApi\AdWords\Reporting\v201806\ReportDefinitionDateRangeType;
use Google\AdsApi\AdWords\ReportSettingsBuilder;
use Google\AdsApi\AdWords\v201806\cm\DateRange;
use Google\AdsApi\AdWords\v201806\cm\Selector;
use Google\AdsApi\Common\ConfigurationLoader;
use Google\AdsApi\Common\OAuth2TokenBuilder;

abstract class AdWordsReportV2
{

    private $startDate;
    private $endDate;
    // private $adWordsArray = [];
    private $errorMsg = "";

    /**
     * @var string account Id for adwords
     */
    protected $clientId;

    /**
     * @var string The account name for this AdWords report
     */
    private $accountName;

    /**
     * @var string The path to the /data folder outside of the webroot
     */
    private $dataSourcePath = '/app/report/adwords/';

    public abstract function getReport();

    /**
     * @param clientId String
     * @param startDate Carbon
     * @param endDate Carbon
     * @param accountName String
     */
    public function __construct($clientId, $startDate, $endDate, $accountName)
    {
        $this->clientId = $clientId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->accountName = htmlentities($accountName);
    }

    public function getApiData($field, $reportType, $predicate = null)
    {
        try {
            // if we don't have a client ID, then don't try to get data from AdWords
            if (empty($this->clientId)) {
                return;
            }

            $session = $this->getAdwordsSession();
            $reportDefinition = $this->getReportDefinition($field, $reportType, $predicate);
            $data = $this->downloadReport($session, $reportDefinition);
            return $data;
        } catch (Exception $e) {
            echo '<p class="errorMessage"> There was a general error in AdWords API for <strong>' . $this->accountName . '</strong>' .
                'Error: ' . $e->getMessage() . '</p>' . PHP_EOL;
            $this->errorMsg = 'There was a general error in AdWords API for ' . $this->accountName . 'Error' .
                $e->getMessage() . PHP_EOL;
            return [];
        }
    }

    private function getAdwordsSession()
    {
        $configuration = (new ConfigurationLoader())->fromFile('adwords.ini');

        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->from($configuration)
            ->build();

        // See: ReportSettingsBuilder for more options (e.g., suppress headers)
        // or set them in your adsapi_php.ini file.
        $reportSettings = (new ReportSettingsBuilder())
            ->from($configuration)
            ->includeZeroImpressions(false)
            ->build();

        // See: AdWordsSessionBuilder for setting a client customer ID that is
        // different from that specified in your adsapi_php.ini file.
        // Construct an API session configured from a properties file and the OAuth2
        // credentials above.
        $session = (new AdWordsSessionBuilder())
            ->from($configuration)
            ->withOAuth2Credential($oAuth2Credential)
            ->withReportSettings($reportSettings)
            ->withClientCustomerID($this->clientId)
            ->build();

        return $session;
    }

    /**
     * @param Array $fields 
     * @param reportType Adwords constants like ReportDefinitionReportType::CAMPAIGN_PERFORMANCE_REPORT
     * @param  $predicates Array of Predicate objects
     */
    private function getReportDefinition($fields, $reportType, $predicates)
    {
        // Create dateRange
        $dateFormat = 'Ymd';
        $dateRange = new DateRange($this->startDate->format($dateFormat), $this->endDate->format($dateFormat));

        // Create selector
        $selector = new Selector();
        $selector->setFields($fields);
        $selector->setDateRange($dateRange);

        // Create report definition
        $reportDefinition = new ReportDefinition();
        $reportDefinition->setSelector($selector);
        $reportDefinition->setReportName('ADW report');
        $reportDefinition->setDateRangeType(ReportDefinitionDateRangeType::CUSTOM_DATE);
        $reportDefinition->setReportType($reportType);
        $reportDefinition->setDownloadFormat(DownloadFormat::XML);

        if ($predicates) {
            $selector->setPredicates(
                $predicates
                // [
                //     new Predicate('AdGroupType', PredicateOperator::IN, [AdGroupType::SEARCH_STANDARD]),
                //     new Predicate('Status', PredicateOperator::IN, [AdGroupStatus::ENABLED]),
                //     new Predicate('CampaignStatus', PredicateOperator::IN, [CampaignStatus::ENABLED])
                // ]
            );
        }

        return $reportDefinition;
    }

    /**
     * Download the report from AdWords
     *
     * @param session Ad Words Session based on Client ID
     * @param reportDefinition ReportDefinition Object
     */
    private function downloadReport($session, $reportDefinition)
    {
        // $filePath = storage_path() . $this->dataSourcePath . 'report.xml';
        $reportDownloader = new ReportDownloader($session);
        $reportDownloadResult = $reportDownloader->downloadReport($reportDefinition);

        // $reportDownloadResult->saveToFile($filePath);
        $reportResult = $reportDownloadResult->getAsString();
        $xml = simplexml_load_string($reportResult);
        $data = json_decode(json_encode((array) $xml), TRUE);

        // $this->deleteReportAndLog($filePath);
        return $data;
        // return file($filePath);
    }

    /**
     * Delete the AdWords CSV file (report.csv) and the Log file (report_download.log)
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
        $logFile = $this->dataSourcePath . 'report_download.log';

        // if there is a log file, delete it
        if (is_file($logFile)) {
            if (!unlink($logFile)) {
                echo '<p>There was an error deleting the Report Download Log file for ' . $this->accountName . '</p>' . PHP_EOL;
                $this->warningMsg .= 'There was an error deleting the Report Download Log file for ' . $this->accountName . PHP_EOL;
            }
        }
    }

    protected function validateApiData($apiData)
    {
        if (empty($apiData)) {
            $this->errorMsg .= "No data";
            return false;
        }
        return array_key_exists('row', $apiData['table']);
    }


    protected function validateApiDataRow($apiDataRow)
    {
        if (empty($apiDataRow)) {
            return false;
        }
        return is_array($apiDataRow);
    }
}
