<?php

declare(strict_types=1);

/*
 * @license    MIT License
 */

namespace Geocoder\Provider\Sputnik;

use Geocoder\Collection;
use Geocoder\Exception\UnsupportedOperation;
use Geocoder\Http\Provider\AbstractHttpProvider;
use Geocoder\Model\AddressBuilder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Geocoder\Provider\Sputnik\Model\SputnikAddress;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Http\Client\HttpClient;

/**
 * @author Demollc <support@demollc.pw>
 */
final class Sputnik extends AbstractHttpProvider implements Provider
{
    /**
     * @var string
     */
    const GEOCODE_ENDPOINT_URL = 'http://search.maps.sputnik.ru/search/addr?format=json&q=%s';

    /**
     * @var string
     */
    const REVERSE_ENDPOINT_URL = 'http://whatsthere.maps.sputnik.ru/point?lat=%F&lan=%F';

    /**
     * @var string
     */
    private $toponym;

    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @param HttpClient $client an HTTP adapter
     * @param string $toponym toponym biasing only for reverse geocoding (optional)
     * @param string|null $apiKey API Key
     */
    public function __construct(HttpClient $client, string $toponym = null, string $apiKey = null)
    {
        parent::__construct($client);

        $this->toponym = $toponym;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $address = $query->getText();

        // This API doesn't handle IPs
        if (filter_var($address, FILTER_VALIDATE_IP)) {
            throw new UnsupportedOperation(
                'The Sputnik provider does not support IP addresses, only street addresses.'
            );
        }

        $url = sprintf(self::GEOCODE_ENDPOINT_URL, urlencode($address));

        return $this->executeQuery($url, $query->getLimit(), $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $coordinates = $query->getCoordinates();
        $longitude = $coordinates->getLongitude();
        $latitude = $coordinates->getLatitude();
        $url = sprintf(self::REVERSE_ENDPOINT_URL, $longitude, $latitude);

        $toponym = $query->getData('toponym', $this->toponym);
        if ($toponym !== null) {
            $url = sprintf('%s&houses=true', $url);
        }

        return $this->executeQuery($url, $query->getLimit(), $query->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Sputnik';
    }

    /**
     * @param string $url
     * @param int $limit
     * @param string $locale
     *
     * @return AddressCollection
     */
    private function executeQuery(string $url, int $limit, string $locale = null): AddressCollection
    {
        if ($locale !== null) {
            $url = sprintf('%s&lang=%s', $url, str_replace('_', '-', $locale));
        }

        if ($this->apiKey !== null) {
            $url = sprintf('%s&apikey=%s', $url, $this->apiKey);
        }

        $url = sprintf('%s&results=%d', $url, $limit);
        $content = $this->getUrlContents($url);
        $json = json_decode($content, true);

        if (empty($json) || isset($json['error']) ||
            (isset($json['response']) && $json['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'] === '0')
        ) {
            return new AddressCollection([]);
        }

        $data = $json['result']['address'];

        $locations = [];
        foreach ($data as $item) {
            $builder = new AddressBuilder($this->getName());
            $flatArray = ['pos' => ' '];

            if (isset($item['features'][0]['properties']['address_components'])) {
                $components = [];
                foreach ($item['features'][0]['properties']['address_components'] as $add_comp) {
                    $components[$add_comp['type']] = $add_comp['value'];
                }
                $item['features'][0]['properties']['address_components'] = $components;
            }

            array_walk_recursive(
                $item['features'],

                /**
                 * @param string $value
                 */
                function ($value, $key) use (&$flatArray) {
                    $flatArray[$key] = $value;
                }
            );

            $coordinates = [$flatArray[0], $flatArray[1]];
            $builder->setCoordinates((float)$coordinates[1], (float)$coordinates[0]);

            $builder->setStreetName($flatArray['street'] ?? null);
            $builder->setSubLocality($flatArray['place'] ?? null);
            $builder->setLocality($flatArray['region'] ?? null);
            $builder->setCountry($flatArray['country'] ?? null);

            /** @var SputnikAddress $location */
            $location = $builder->build(SputnikAddress::class);
            $location = $location->withPrecision($flatArray['precision'] ?? null);
            $location = $location->withName($flatArray['name'] ?? null);
            $location = $location->withKind($flatArray['kind'] ?? null);
            $locations[] = $location;
        }

        return new AddressCollection($locations);
    }
}
