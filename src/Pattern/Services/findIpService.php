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
        public function getInfoCdn(): Geolocation{
            
            // check ip 
            // https://api.findip.net/66.228.119.72/?token=xxxxxxxxxx

            $currentIp      = $this->getRealIp();
            $api            = "http://api." . $this->neoxBag->getCdn()["api_use"] . "/$currentIp/?token=" . $this->neoxBag->getCdn()['api_key'];
            // todo: check if this expires !!!
            $data           = json_decode($this->senApi( $api ), true);
            $geolocation   = new Geolocation();
            $geolocation->setstatus('success')               // = ;
                ->setcontinent($data["continent"]["names"]["fr"] ?? 'Europe' )          // = 'Europe';
                ->setcontinentCode($data["continent"]["code"] ?? 'EU')             // = 'EU';
                ->setcountry($data["country"]["names"]["en"] ?? 'France')              // = 'France';
                ->setcountryCode($data["country"]["iso_code"] ?? 'FR')             // = 'FR';
                ->setregionName($data["subdivisions"][0]["names"]["en"] ?? 'Paris')   // = 'Paris';
                ->setcity($data["city"]["names"]["en"] ?? 'Paris')                    // = 'Paris';
                ->setzip($data["postal"]["code"] ?? '75000')                          // = '75000';
                ->setlat($data["location"]["latitude"] ?? 40.6951)                    // = 40.6951;
                ->setlon($data["location"]["longitude"] ?? 20.325)                   // = 20.325;
                ->setreverse($data["traits"]["isp"] ?? 'unn-156-146-55-226.cdn')                       // = 'unn-156-146-55-226.cdn';
                ->setmobile('nc')                                 // = false;
                ->setproxy($data["traits"]["connection_type"] === 'Corporate')     // = false;
                ->sethosting($data["traits"]["user_type"] === 'hosting')           // = false;
                ->setquery($currentIp)           // = '156.146.55.226';
                ->setvalid(true)            // = true;
            ;
            
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
        

    }