<?php

namespace PmAnalyticsPackage\api\AdWords;

use Google\AdsApi\AdManager\v201802\Money;
use Google\AdsApi\AdWords\AdWordsServices;
use Google\AdsApi\AdWords\AdWordsSession;
use Google\AdsApi\AdWords\v201809\cm\AdGroupService;
use Google\AdsApi\AdWords\v201809\cm\AdService;
use Google\AdsApi\AdWords\v201809\cm\Budget;
use Google\AdsApi\AdWords\v201809\cm\BudgetOperation;
use Google\AdsApi\AdWords\v201809\cm\BudgetService;
use Google\AdsApi\AdWords\v201809\cm\CampaignService;
use Google\AdsApi\AdWords\v201809\cm\Operator;
use Google\AdsApi\AdWords\v201809\cm\OrderBy;
use Google\AdsApi\AdWords\v201809\cm\Paging;
use Google\AdsApi\AdWords\v201809\cm\SortOrder;
use Google\AdsApi\AdWords\v201809\cm\Selector;
use Google\AdsApi\AdWords\v201809\cm\Predicate;
use Google\AdsApi\AdWords\v201809\cm\PredicateOperator;
use Google\AdsApi\AdWords\v201809\cm\AdGroupType;
use Google\AdsApi\AdWords\v201809\cm\AdGroupStatus;
use Google\AdsApi\AdWords\v201809\cm\CampaignStatus;
use Google\AdsApi\AdWords\v201809\cm\AdType;

class AdWordsUtilities
{
    const PAGE_LIMIT = 500;

    public function __construct(AdWordsSession $session)
    {
        $this->adWordsServices = new AdWordsServices();
        $this->session = $session;
    }

    public function getCampaigns()
    {
        $campaingService = $this->adWordsServices->get(
            $this->session,
            CampaignService::class
        );

        // Create selector
        $selector = new Selector();
        $selector->setFields(['Id', 'Name', 'BudgetId', 'Amount']);
        $selector->setOrdering([new OrderBy('Name', SortOrder::ASCENDING)]);
        $selector->setPaging(new Paging(0, self::PAGE_LIMIT));
        $campaigns = [];
        $totalNumEntries = 0;

        do {
            // Make the get request
            $page = $campaingService->get($selector);

            // Display results
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                $campaigns = array_merge($campaigns, $page->getEntries());
                foreach ($page->getEntries() as $campaign) {
                    printf(
                        "Campaign with ID %d and name '%s' was found. \n",
                        $campaign->getId(),
                        $campaign->getName()
                    );
                }
            }

            // Advance the paging index.
            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + self::PAGE_LIMIT
            );
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        printf("Number of results found: %d\n", $totalNumEntries);
        return $campaigns;
    }

    /**
     * [updateBudget description]
     * @param  Integer $budgetId    12321545
     * @param  Float $microAmount 10000 = 0.01 usd
     * @return [type]              [description]
     */
    public function updateBudget($budgetId, $microAmmount)
    {
        echo "BID: $budgetId, ammount: $microAmmount" . PHP_EOL;

        $budgetService = $this->adWordsServices->get($this->session, BudgetService::class);
        $operation = new BudgetOperation();
        $budget = new Budget();
        $budget->setBudgetId($budgetId);
        $money = new Money();
        $money->setMicroAmount($microAmmount);
        $budget->setAmount($money);
        $operation->setOperand($budget);

        $operation->setOperator(Operator::SET);
        $ret = $budgetService->mutate([$operation]);
        dd($ret);
    }

    public function getAdGroups()
    {
        $adGroupService = $this->adWordsServices->get($this->session, AdGroupService::class);

        // Create a selector to select all ad groups for the specified campaign.
        $selector = new Selector();
        $selector->setFields(['Id', 'Name', 'AdGroupType', 'TrackingUrlTemplate', 'FinalUrlSuffix', 'CampaignName', 'Status']);
        $selector->setOrdering([new OrderBy('Name', SortOrder::ASCENDING)]);
        $selector->setPredicates(
            [
                new Predicate('AdGroupType', PredicateOperator::IN, [AdGroupType::SEARCH_STANDARD]),
                new Predicate('Status', PredicateOperator::IN, [AdGroupStatus::ENABLED]),
                new Predicate('CampaignStatus', PredicateOperator::IN, [CampaignStatus::ENABLED])
            ]
        );
        $selector->setPaging(new Paging(0, self::PAGE_LIMIT));
        $adGroups = [];
        $totalNumEntries = 0;

        do {
            // Retrieve ad groups one page at a time, continuing to request pages
            // until all ad groups have been retrieved.
            $page = $adGroupService->get($selector);

            // Print out some information for each ad group.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                $adGroups = array_merge($adGroups, $page->getEntries());
                foreach ($page->getEntries() as $adGroup) {
                    printf(
                        "Ad group with ID %d and name '%s' was found %s.\n",
                        $adGroup->getId(),
                        $adGroup->getName(),
                        $adGroup->getTrackingUrlTemplate()
                    );
                }
            }

            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + self::PAGE_LIMIT
            );
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        printf("Number of results found: %d\n", $totalNumEntries);
        return $adGroups;
    }

    // modified from https://developers.google.com/adwords/api/docs/samples/php/basic-operations#get-expanded-text-ads-in-an-ad-group
    public function getExpanedTextAds($adGroupId)
    {
        $adService = $this->adWordsServices->get($this->session, AdGroupService::class);

        // Create a selector to select all ads for the specified ad group.
        $selector = new Selector();
        $selector->setFields(
            ['Id', 'Status', 'CreativeTrackingUrlTemplate', 'CreativeFinalUrlSuffix']
        );
        $selector->setPredicates([
            new Predicate('AdType', PredicateOperator::IN, [AdType::EXPANDED_TEXT_AD]),
            new Predicate('AdGroupId', PredicateOperator::IN, [$adGroupId])
        ]);
        $selector->setPaging(new Paging(0, self::PAGE_LIMIT));

        $ads = [];
        $totalNumEntries = 0;

        do {
            // Retrieve ad group ads one page at a time, continuing to request pages
            // until all ad group ads have been retrieved.
            $page = $adService->get($selector);

            // Print out some information for each ad group ad.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                $ads = array_merge($ads, $page->getEntries());
            }

            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + self::PAGE_LIMIT
            );
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        printf("Number of results found: %d\n", $totalNumEntries);
        return $ads;
    }

    // modified from https://developers.google.com/adwords/api/docs/samples/php/basic-operations#get-expanded-text-ads-in-an-ad-group
    public function getAds()
    {
        $adService = $this->adWordsServices->get($this->session, AdService::class);

        // Create a selector to select all ads for the specified ad group.
        $selector = new Selector();
        $selector->setFields(
            [
                'Id', 'Url', 'BaseCampaignId', 'CreativeFinalMobileUrls', 'DisplayUrl', 'CreativeFinalUrls', 'CreativeTrackingUrlTemplate',
                'CreativeFinalUrlSuffix', 'CreativeFinalAppUrls', 'CreativeUrlCustomParameters'
            ]
        );

        $selector->setPredicates([new Predicate('AdType', PredicateOperator::IN, [AdType::EXPANDED_TEXT_AD])]);
        $selector->setPaging(new Paging(0, self::PAGE_LIMIT));

        $ads = [];
        $totalNumEntries = 0;

        do {
            // Retrieve ad group ads one page at a time, continuing to request pages
            // until all ad group ads have been retrieved.
            $page = $adService->get($selector);

            // Print out some information for each ad group ad.
            if ($page->getEntries() !== null) {
                $totalNumEntries = $page->getTotalNumEntries();
                $ads = array_merge($ads, $page->getEntries());
                foreach ($page->getEntries() as $entry) {
                    if (
                        $entry->getUrlCustomParameters()
                        || $entry->getFinalUrlSuffix()
                        || $entry->getTrackingUrlTemplate()
                        || $entry->getDisplayUrl()
                        || $entry->getUrl()
                    ) {
                        dd($entry);
                    }
                    # code...
                }
            }
            $selector->getPaging()->setStartIndex(
                $selector->getPaging()->getStartIndex() + self::PAGE_LIMIT
            );
        } while ($selector->getPaging()->getStartIndex() < $totalNumEntries);

        printf("Number of results found: %d\n", $totalNumEntries);
        return $ads;
    }
}
