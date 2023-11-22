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
    
    class getipintelService extends geolocatorAbstract implements GeolocatorInterface
    {
        public function Geolocator(): geolocationModel
        {
            
            // get geolocation
            $this->Geolocation = $this->getInfoCdn();
            
            // set filter Local
            $this->setFilterLocal();
            
            // set filter contement
            $this->setFilterContinents();
            
            // set filter Connection
            $this->setFilterConnection();
            
            // set crawler
            $this->setFilterCrawler();
            
//            $this->requestStack->getSession()->set('geolocator', $this->Geolocation);
            
            return $this->Geolocation;
            
            // TODO: Implement Geolocator() method.
        }
        
        public function getInfoCdn(): GeolocationModel{
            
            // check ip "http://check.getipintel.net/check.php"
           // http://check.getipintel.net/check.php?ip=66.228.119.72&contact=dede@aol.com&format=json&flags=b
            $currentIp  = $this->getRealIp();
            $api        = "http://" . $this->neoxBag->getCdn()["api_use"] . "/check.php?ip=$currentIp&contact=Your@contact.xyz&format=json&flags=b";
            // todo: check if this expires !!!
            $response_      = $this->httpClient->request('GET', $api );
            $o = json_decode($response_->getContent());
            return GeolocationModel::fromJson($response_->getContent());
        }
        

    }