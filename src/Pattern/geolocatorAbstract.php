<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Attribute\NeoxGeoBag;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\neoxBag;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Component\Cache\CacheItem;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    use UnitEnum;
    
    Abstract class geolocatorAbstract
    {
        /**
         * @var RouterInterface
         */
        protected $router;
        /**
         * @var ParameterBagInterface
         */
        protected $parameterBag;
        /**
         * @var HttpClientInterface
         */
        protected $httpClient;
        /**
         * @var RequestStack
         */
        protected $requestStack;
        
        protected KernelInterface $kernel;
        /**
         * @var CacheInterface
         */
        protected CacheInterface $cache;
        
        protected array             $CDN;
        protected array             $FILTER;
        protected GeolocationModel  $Geolocation;
        protected NeoxBag           $neoxBag;
        private                     $ffff;
        CONST NAME                  = "geolocator - ";
        CONST FAIL                  = "fail";
        CONST COUNTNAME             = "counter-";
        
        /**
         * @param RouterInterface $router
         * @param ParameterBagInterface $parameterBag
         * @param HttpClientInterface $httpClient
         * @param RequestStack $requestStack
         * @param CacheInterface $cache
         */
        public function __construct(
            RouterInterface       $router,
            ParameterBagInterface $parameterBag,
            HttpClientInterface   $httpClient,
            RequestStack          $requestStack,
            CacheInterface        $cache,
            KernelInterface       $kernel,
            NeoxBag               $neoxBag
        )
        {
            
            $this->router           = $router;
            $this->parameterBag     = $parameterBag;
            $this->httpClient       = $httpClient;
            $this->requestStack     = $requestStack;
            $this->cache            = $cache;
            $this->kernel           = $kernel;
            $this->neoxBag          = $neoxBag;
//            $this->CDN              = $this->neoxBag->getCdn();
//            $this->FILTER           = $this->neoxBag->getFilterLocal() + $this->neoxBag->getFilterConnection() + $this->neoxBag->getFilterContinents();
        
        }
        
 
        use Limitor;
        /**
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws InvalidArgumentException
         */
        public function checkAuthorize(): bool|string
        {
            // cache to optimize ux : check very 1 hour security raison
            // First check have no id session yet so we nock one to pass, expire will be very short
            // lake this next clic anywhere be check again !!! this time id session will be create
            $this->requestStack->getSession()->set("geo","id");
            
            list($timer, $session) = $this->setTimer();
            
            // Force geolocation !!!
            // 1 - delete old one
            // 2 - create new one with new timestamp and geolocator
            if ( $this->neoxBag->getForcer() ) {
                $this->deleteCounterCache(self::NAME . $session);
            }
            
            // Redis manage storage with expiration !!
            $value  = $this->cache->get( self::NAME . $session, function (ItemInterface $item) use ($timer)  {
                $geolocation    = $this->Geolocator();
                $timer          = $geolocation->getStatus() === self::FAIL ? 10 : $timer;
                $item->expiresAfter( (int) $timer); // 3600 = 1 hour
                return $geolocation;
            });
            
            /** @var geolocationModel $value*/
            if (!$value->isValid()) {
                $route = $this->neoxBag->getNameRouteUnauthorized();
                return $this->router->generate($route);
            }
            
            return true;
        }
        
        public function checkIpPing(): bool
        {
            $ipClient = $this->getRealIp();
            return $this->getIpPing($ipClient);
            
        }
        
        /**
         * @throws \Exception
         */
        public function buildClass(string $nameService) : object
        {
            $className = "NeoxGeolocator\\NeoxGeolocatorBundle\\Pattern\\Services\\" . $nameService;
            if (! class_exists($className)) {
                throw new \Exception("Service class '{$className}' does not exist.");
            }
            
            return new $className(
                $this->router,
                $this->parameterBag,
                $this->httpClient,
                $this->requestStack,
                $this->cache,
                $this->kernel
            );
        }
        
        /**
         * Set all filter ......
         */
        
        
        protected function setFilter(): void
        {
            $filter             = $this->strFy(array_merge($this->neoxBag->getFilterLocal(), $this->neoxBag->getFilterContinents()));
            $item               = $this->strFy($this->Geolocation->toArray());
            $filteredData       = $this->getFilteredData($item, $filter);
            
            $this->setFilterConnection();
            $this->setFilterCrawler();
            
        }
        
        protected function setFilterLocal(): void
        {
            $filter             = $this->strFy($this->neoxBag->getFilterLocal());
            $item               = $this->strFy([$this->Geolocation->getCountryCode()]);
            $filteredData       = $this->getFilteredData($item, $filter);
            
        }
        
        protected function setFilterConnection(): void
        {
            $connection    = $this->neoxBag->getFilterConnection();
            if (!empty($connection) && $this->Geolocation->getStatus() !== "fail" && $this->Geolocation->isProxy() ) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        protected function setFilterContinents(): void
        {
            $continents         = $this->strFy($this->neoxBag->getFilterContinents());
            $filteredData       = $this->strFy([$this->Geolocation->getContinent()]);
            $filteredData       = $this->getFilteredData($item, $filter);

        }
        
        protected function setFilterCrawler(): void
        {
            $filter             = $this->strFy($this->neoxBag->getCrawler());
            $item               = $this->strFy([$this->Geolocation->getReverse()]);
            if ( $this->getFilteredData($item, $filter, false) ) {
                $this->Geolocation->setValid(true);
            }
        }
        
        /**
         * @throws TransportExceptionInterface
         */
        protected function senApi(string $api ): ?string
        {
            try {
                $response = $this->httpClient->request('GET', $api, ['timeout' => 20]);
                
                if ($response->getStatusCode() != 200) {
                    // you might also want to log this situation.
                    return null;
                }
                
                return $response->getContent();
            } catch (\Exception $e) {
                // Log the exception message
                //e.g something like $this->logger->error('API request failed: ' . $e->getMessage());
                return null;
            }
            
        }
        
        /**yr
         *
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface*@throws \JsonException
         * @throws TransportExceptionInterface
         * @throws \JsonException
         */
        protected function getRealIp( ): ?string
        {
            
            // https://api.ipify.org?format=json
//            $api            = "https://api.ipify.org?format=json";
//            // todo: problem in same cas to get real ip !!!
//            $data   = $this->senApi( $api );
//            $ip     = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
//            return $ip->ip;
            // in dev mode mock
            if ( $this->kernel->getEnvironment() === 'dev') {
                // for test  Bulgary "156.146.55.226"
                return $this->neoxBag->getIpLocalDev() ;
            }

            $request    = $this->requestStack->getCurrentRequest();
            $ip         = $request->getClientIp();

            if ($request->headers->has('x-real-ip')) {
                return $request->headers->get('x-real-ip');
            }

            if ($request->headers->has('CF-Connecting-IP')) {
                $ip = $request->headers->get('CF-Connecting-IP');
            }

            if ($request->headers->has('X-Forwarded-For')) {
                $ips = explode(',', $request->headers->get('X-Forwarded-For'), 2);
                $ip = trim($ips[0]); // The left-most IP address is the original client
            }

            return $ip;
        }
        
        protected function getParameter($key): UnitEnum|float|array|bool|int|string|null
        {
            return $this->parameterBag->get($key);
        }
        
        /**
         * @param array $geoDataItems
         * @param array $geoDataFilters
         *
         * @return array
         */
        private function getFilteredData(array $geoDataItems, array $geoDataFilters, $validateGeoLocation = true): array
        {
            $filteredGeoData       = array_intersect($geoDataItems, $geoDataFilters);
            if ( $validateGeoLocation ) {
                $isGeoLocationValid            = count($filteredGeoData) > 0;
                $this->Geolocation->setValid($isGeoLocationValid);
            }
            return $filteredGeoData;
        }
        
        /**
         * @param $array
         *
         * @return array|string[]
         */
        private function strFy($array): array
        {
            return array_map(function ($value) {
                if (is_string($value)) {
                    return strtolower($value);
                }
                return $value;
            }, $array);
        }
        
        /**
         * @param string $session
         *
         * @return array
         */
        private function setTimer(): array
        {
            $session    = $this->requestStack->getSession()->getId();
            
            $timer = $this->neoxBag->getTimer();
            if (!$session) {
                $session = uniqid("pass_", true);
                $timer = 30;
            }
            return array($timer, $session);
        }
    }