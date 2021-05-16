# Sputnik Geocoder provider
[![Latest Stable Version](https://poser.pugx.org/demollc/sputnik-provider/v/stable)](https://packagist.org/packages/demollc/sputnik-provider)
[![Total Downloads](https://poser.pugx.org/demollc/sputnik-provider/downloads)](https://packagist.org/packages/demollc/sputnik-provider)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

This is the Sputnik provider from DemoLLC. 

### Install

```bash
composer require demollc/sputnik-provider
```

## Usage

The API may require an API key. [See here for more information](http://api.sputnik.ru/maps/jsapi/).

```php
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
$httpClient = new \Http\Adapter\Guzzle7\Client();
$geocoder = new \Geocoder\Provider\Sputnik\Sputnik($httpClient, null, '<your-api-key>');

$result = $geocoder->geocodeQuery(GeocodeQuery::create('ул. Генерала Лизюкова, 4, Воронеж'));
$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates(...));
```
### Note
Reverse Query endpoint is frequently down and methods are created by manual.

### Contribute

Contributions are very welcome!
