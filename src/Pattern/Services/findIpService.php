<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\Services;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
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
        public function Geolocator(): geolocationModel
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
        public function getInfoCdn(): GeolocationModel{
            
            // check ip 
            // https://api.findip.net/66.228.119.72/?token=xxxxxxxxxx

            $currentIp  = $this->getRealIp();
            $api        = "http://api." . $this->neoxBag->getCdn()["api_use"] . "/$currentIp/?token=" . $this->neoxBag->getCdn()['api_key'];
            // todo: check if this expires !!!
            $response_          = $this->httpClient->request('GET', $api );
            $o                  = json_decode($response_->getContent(), true, 512, JSON_THROW_ON_ERROR);
            
            $geolocationModel   = new GeolocationModel();
            $geolocationModel->setstatus('success')               // = ;
                ->setcontinent($o["continent"]["names"]["fr"])          // = 'Europe';
                ->setcontinentCode($o["continent"]["code"])             // = 'EU';
                ->setcountry($o["country"]["names"]["en"])              // = 'France';
                ->setcountryCode($o["country"]["iso_code"])             // = 'FR';
                ->setregionName($o["subdivisions"][0]["names"]["en"])   // = 'Paris';
                ->setcity($o["city"]["names"]["en"])                    // = 'Paris';
                ->setzip($o["postal"]["code"])                          // = '75000';
                ->setlat($o["location"]["latitude"])                    // = 40.6951;
                ->setlon($o["location"]["longitude"])                   // = 20.325;
                ->setreverse($o["traits"]["isp"])                       // = 'unn-156-146-55-226.cdn';
                ->setmobile('nc')                                 // = false;
                ->setproxy(($o["traits"]["connection_type"] == 'Corporate' ? true : false))     // = false;
                ->sethosting(($o["traits"]["user_type"] == 'hosting' ? true : false))           // = false;
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
            
            return $geolocationModel;
        }
        

    }