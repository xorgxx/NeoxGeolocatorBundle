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
    
    class ipApiService extends geolocatorAbstract implements GeolocatorInterface
    {
        public function Geolocator(): geolocationModel
        {
            
            // get geolocation
            $this->Geolocation = $this->getInfoCdn();
            
            // set filter Local
            $this->setFilterLocal();
            
            // set filter Connection
            $this->setFilterConnection();
            
            // set filter contement
            $this->setFilterContinents();
            
            // set crawler
            $this->setFilterCrawler();
            
//            $this->requestStack->getSession()->set('geolocator', $this->Geolocation);
            
            return $this->Geolocation;
            
            // TODO: Implement Geolocator() method.
        }
        
        public function getInfoCdn(): GeolocationModel{
            
            // check ip
            // $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
            // $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $currentIp  = $this->getRealIp();
            $api        = "http://" . $this->CDN["api_use"] . "/json/$currentIp?fields=status,message,continent,continentCode,country,countryCode,regionName,city,zip,lat,lon,reverse,mobile,proxy,hosting,query";
            // todo: check if this expires !!!
            $response_      = $this->httpClient->request('GET', $api );

            return GeolocationModel::fromJson($response_->getContent());

        }
        

    }