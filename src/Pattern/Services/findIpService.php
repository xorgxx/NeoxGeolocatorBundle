<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\Services;

use NeoxGeolocator\NeoxGeolocatorBundle\Entity\Geolocation;
use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\geolocatorAbstract;
use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class findIpService extends geolocatorAbstract implements GeolocatorInterface
{
    public function Geolocator(): Geolocation
    {
        // get geolocation
        $this->Geolocation = $this->getInfoCdn();

        // optimised
        $this->setFilter();

        return $this->Geolocation;
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getInfoCdn(): Geolocation
    {

        // check ip
        // https://api.findip.net/66.228.119.72/?token=xxxxxxxxxx

        $currentIp = $this->getRealIp();
        $api = "http://api." . $this->neoxBag->getCdn()[ "api_use" ] . "/$currentIp/?token=" . $this->neoxBag->getCdn()[ 'api_key' ];
        // todo: check if this expires !!!
        $data = json_decode($this->senApi($api), true);
        $geolocation = new Geolocation();
        $geolocation->setStatus('findip')
                    ->setContinent($this->getValue($data, "continent.names.fr", 'Europe'))
                    ->setContinentCode($this->getValue($data, "continent.code", 'EU'))
                    ->setCountry($this->getValue($data, "country.names.en", 'France'))
                    ->setCountryCode($this->getValue($data, "country.iso_code", 'FR'))
                    ->setRegionName($this->getValue($data, "subdivisions.0.names.en", 'Paris'))
                    ->setCity($this->getValue($data, "city.names.en", 'Paris'))
                    ->setZip($this->getValue($data, "postal.code", '75000'))
                    ->setLat($this->getValue($data, "location.latitude", 40.6951))
                    ->setLon($this->getValue($data, "location.longitude", 20.325))
                    ->setReverse($this->getValue($data, "traits.isp", 'unn-156-146-55-226.cdn'))
                    ->setMobile($this->getValue($data, "traits.mobile", false))
                    ->setProxy($this->getValue($data, "traits.connection_type", '') === 'Corporate')
                    ->setHosting($this->getValue($data, "traits.user_type", '') === 'hosting')
                    ->setQuery($currentIp)
                    ->setValid(true);;

        //            array (
        //                'autonomous_system_number' => 212238,
        //                'autonomous_system_organization' => 'Datacamp Limited',
        //                'connection_type' => 'Corporate',
        //                'isp' => 'Datacamp Limited',
        //                'organization' => 'Cdnext SOF',
        //                'user_type' => 'hosting',
        //            )

        return $geolocation;
    }

    private function getValue(array $data = null, string $path, $default = null)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!is_array($data) || !array_key_exists($key, $data)) {
                return $default;
            }
            $data = $data[ $key ];
        }
        return $data;
    }


}