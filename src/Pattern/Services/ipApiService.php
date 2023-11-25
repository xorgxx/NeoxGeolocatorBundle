<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\Services;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\geolocatorAbstract;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorInterface;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\CacheItem;
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
            
            // optimised
            $this->setFilter();
            
            return $this->Geolocation;
        }
        
        public function getInfoCdn(): GeolocationModel{
            
            // check ip
            // $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
            // $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $data   = "";
            if ( $this->getLimiter('ipapi') ) {
                $currentIp      = $this->getRealIp();
                $api            = "http://" . $this->neoxBag->getCdn()["api_use"] . "/json/$currentIp?fields=status,message,continent,continentCode,country,countryCode,regionName,city,zip,lat,lon,reverse,mobile,proxy,hosting,query";
                // todo: check if this expires !!!
                $response_      = $this->httpClient->request('GET', $api, ['timeout' => 5]);
                $data           = $response_->getContent();
                return GeolocationModel::fromJson($data);
            }else{
                /** @var geolocatorAbstract $class */
                $class = $this->buildClass("findIpService");
                return  $class->Geolocator();
            }
        }
    }