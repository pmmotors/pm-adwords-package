
# PMAnalytics Package

Library for managing analytics and be installed in another projects

## CONFIGURATION FOR DEVELOP IN THIS PROJECT:

## SET UP
* Clone this repository

* Create container:
```bash
    docker-compose up -d
```

* Access the container: 
```bash
    docker-compose exec php-apache bash
```
* Install dependencies:

```bash
    composer install
```

* Open a browser on `localhost:8000`

## CONFIGURATION FOR ADD THIS PROJECT AS A DEPENDENCY:

## Installation

Install via composer running

```bash
  composer require pmmotors/pm-analytics-package
```

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
## Installation

Install my-project with npm

```bash
  npm install my-project
  cd my-project
```
    
## Usage/Examples

```javascript
import Component from 'my-project'

function App() {
  return <Component />
}
```

  