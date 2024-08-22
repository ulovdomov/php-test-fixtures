# Php Test fixtures

Php Test fixtures for PhpUnit

## Installation

Add following to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ulovdomov/php-test-fixtures"
    }
  ]
}
```

And run:

```shell
composer require --dev ulovdomov/test-fixtures
```

## Usage

### Get exchange rates

```php
$client = new \UlovDomov\HttpClient\GuzzleHttpClient();

//create rest client or get it from DI container created by extension
$restClient = new \UlovDomov\ExchangeRatesSdk\ExchangeRatesClient(
    apiUrl: 'https://data.kurzy.cz',
    httpClient: $client,
);

$date = new \DateTime('2024-10-01');
$bankCode = \UlovDomov\ExchangeRatesSdk\BankCode::CNB;

/** @var array<\UlovDomov\ExchangeRatesSdk\ExchangeRate> $rates */
$rates = $restClient->getExchangeRates($date, $bankCode);
```

Example of ExchangeRate object:
```txt
14 => UlovDomov\ExchangeRatesSdk\ExchangeRate #48
   |  from: 'CZK'
   |  to: 'ZAR'
   |  amount: 1
   |  rate: 1.257
   |  buy: null
   |  sell: null
```

## Development

### First setup

1. Run for initialization
```shell
make init
```
2. Run composer install
```shell
make composer
```

Use tasks in Makefile:

- To log into container
```shell
make docker
```
- To run code sniffer fix
```shell
make cs-fix
```
- To run PhpStan
```shell
make phpstan
```
- To run tests
```shell
make phpunit
```