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
    
    class getipintelService extends geolocatorAbstract implements GeolocatorInterface
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
            
            // check ip "http://check.getipintel.net/check.php"
           // http://check.getipintel.net/check.php?ip=66.228.119.72&contact=dede@aol.com&format=json&flags=b
            $currentIp      = $this->getRealIp();
            $api            = "http://" . $this->neoxBag->getCdn()["api_use"] . "/check.php?ip=$currentIp&contact=Your@contact.xyz&format=json&flags=b";
            // todo: check if this expires !!!
            $response_      = $this->senApi( $api );
            $o = json_decode($response_->getContent(), true, 512, JSON_THROW_ON_ERROR);
            return Geolocation::fromJson($response_->getContent());
        }
        

    }