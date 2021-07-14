
# PMAnalytics Package

Library for managing analytics and be installed in another projects




## Installation

Install via composer running

```bash
  composer require pmmotors/pm-analytics-package
```
Or, editing the `composer.json`:

```bash
"require": {
    "pmmotors/pm-analytics-package": "^1.1",
}
```

Then run `composer update`
## Usage

In this example, pm-reports project will be used:

### Using AdWords

Injectin PmAnalyticsPackage: 
```php

// use App\Api\Analytics\AdWords\AdWordsReportV2;
use PmAnalyticsPackage\api\AdWords\AdWordsReportV2;

class AdWordsReportMaserati extends AdWordsReportV2 {

    ...

}
```

Using AdWordsReportMaserati

```php
$report = new AdWordsReportMaserati(
    $clientId,
    $startDate,
    $endDate,
    $accountName
);

$report->getReport();
```

### Using Facebook

```php

// init al facebook configuration
Facebook::FacebookInit();

// add facebook account
$account = Facebook::FacebookAccount($account_id);

$facebookAd = new FacebookReport(
    $account,
    $startDate,
    $endDate,
    $accountName
);

$facebookAd->getDateFromFacebookAPI();
```

### Using DialogTech

```php
$dialog = new DialogTechReport(
    $reportStarDate,
    $reportEndDate,
    $accountName,
    $phoneNumberArr
);

$dialog->getDialogTechArray();
```

### Using Google

```php
$dataSourcePath = 'path';

$analytics = Google::make('analytics');
$google = new GoogleAnalyticsReport(
    $analytics,
    $profileId,
    $reportDate,
    $accountName,
    $dataSourcePath
);

$google->getAnalyticsArray();

```