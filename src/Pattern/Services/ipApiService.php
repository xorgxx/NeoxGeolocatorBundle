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
            
            // check ip
            // $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
            // $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $data   = "";
            if ( $this->getLimiter() ) {
                $currentIp      = $this->getRealIp();
                $api            = "http://" . $this->CDN["api_use"] . "/json/$currentIp?fields=status,message,continent,continentCode,country,countryCode,regionName,city,zip,lat,lon,reverse,mobile,proxy,hosting,query";
                // todo: check if this expires !!!
                $response_      = $this->httpClient->request('GET', $api );
                $data = $response_->getContent();
            }
 
            return GeolocationModel::fromJson($data);

        }
        
        private function getLimiter(): bool {
            
            /**
             * @var ItemAdapter $Item
             */
            $Item2  = $this->cache->get( "counter" , function (ItemInterface $item) {
                $item->expiresAfter( (int) 60); // 3600 = 1 hour
                return 0;
            });
            
            $Item2++;
            
            if( $Item2 < 43 ) {
                /** @var CacheItem $item */
                $Item   = $this->cache->getItem( "counter" );
                $expire = $Item->getMetadata()['expiry'];
                $this->cache->delete( "counter" );
                $interval = new \DateInterval("PT{$expire}S");
                $Item2  = $this->cache->get( "counter" , function (ItemInterface $item) use ($expire, $Item2) {
                    $interval = new \DateTime("@$expire", new \DateTimeZone("Europe/Paris"));
                    $item->expiresAt( $interval ); // 3600 = 1 hour
                    return $Item2;
                });
                return true;
                };
            
            return false;
        }

    }