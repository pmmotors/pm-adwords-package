<?php

namespace Api\AdWords;

use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSessionBuilder;
use Google\AdsApi\Common\OAuth2TokenBuilder;
use Google\AdsApi\Common\ConfigurationLoader;

use Google\AdsApi\AdWords\v201809\mcm\CustomerService;
use Google\AdsApi\AdWords\v201809\cm\Selector;
use Google\AdsApi\AdWords\v201809\cm\ApiException;
use Google\AdsApi\AdWords\v201809\cm\AuthenticationErrorReason;

class AdWordsConnection
{

    public static function getSession($customerId)
    {
        $configuration = (new ConfigurationLoader())->fromFile('adwords.ini');

        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->from($configuration)
            ->build();

        // Construct an API session configured from a properties file and the
        // OAuth2 credentials above.
        $session = (new AdWordsSessionBuilder())->from($configuration)->withOAuth2Credential($oAuth2Credential)
            ->withClientCustomerID($customerId)
            ->build();
        echo "Session correct";
        return $session;
    }

    public function __construct()
    {
    }

    public function validateAccountId($accountId)
    {
        $configuration = (new ConfigurationLoader())->fromFile('google-ads.ini');

        // Generate a refreshable OAuth2 credential for authentication.
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->from($configuration)
            ->build();

        $session = (new AdWordsSessionBuilder())
            ->from($configuration)
            ->withOAuth2Credential($oAuth2Credential)
            ->withClientCustomerID($accountId)
            ->build();
        $selector = new Selector();
        $selector->setFields(['Id', 'Name']);
        try {
            //TEST SERVICE CALL
            $service = (new AdWordsServices())->get($session, CustomerService::class);
            $service->getCustomers();
        } catch (ApiException $apiException) {
            if (!$apiException->getErrors()[0]->getReason() == AuthenticationErrorReason::CUSTOMER_NOT_FOUND) {
                throw $apiException; // validation can't be evaluated.
            }
            return false;
        }
        return true;
    }
}
