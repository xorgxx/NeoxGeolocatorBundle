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
        
        protected array $CDN;
        protected array $FILTER;
        protected GeolocationModel  $Geolocation;
        protected NeoxBag           $neoxBag;
        private $ffff;
        CONST NAME = "geolocator - ";
        
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
        
        protected function setFilter(): void
        {
            $filter             = array_merge($this->neoxBag->getFilterLocal(), $this->neoxBag->getFilterContinents());
            $item               = $this->Geolocation->toArray();
            
            $filteredData       = array_filter($item, function ( $itemz ) use ($filter) {
                return in_array($itemz, $filter );
            });
            $filteredCount      = count($filteredData);
            
            if ($filteredCount === 1) {
                $this->Geolocation->setValid(false);
            }
            
            $this->setFilterConnection();
            $this->setFilterCrawler();
            
        }
        
        protected function setFilterLocal(): void
        {
            $local    = $this->neoxBag->getFilterLocal();
            if (!empty($local) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getCountryCode(), $local, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
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
            $continents    = $this->neoxBag->getFilterContinents();
            if (!empty($continents) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getContinent(), $continents, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        protected function setFilterCrawler(): void
        {
            $crawler    = $this->neoxBag->getCrawler();
            if (!empty($crawler) && $this->Geolocation->getStatus() !== "fail" && $this->stringContainsSubstringFromArray($this->Geolocation->getReverse(), $crawler) ) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(true);
            }
        }
        
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
            $key    = $this->requestStack->getSession()->getId();
            
            // First check have no id session yet so we nock one to pass, expire will be very short
            // lake this next clic anyware be check againe !!! this time id session will be create
            $timer  = $this->neoxBag->getTimer();
            if ( !$key ) {
                $key    = uniqid("pass_", true);
                $timer  = 5;
            }
       
            // Redis manage storage with expiration !!
            $value  = $this->cache->get( self::NAME . $key, function (ItemInterface $item) use ($timer) {
                $geolocation    = $this->Geolocator();
                $timer          = $geolocation->getStatus() === "fail" ? 10 : $timer;
                $item->expiresAfter( (int) $timer); // 3600 = 1 hour
                return $geolocation;
            });
            
            if (!$value->isValid()) {
                $route = $this->neoxBag->getNameRouteUnauthorized();
                return $this->router->generate($route);
            }
            
            return true;
        }
        
        protected function senApi( string $api ){
            return $this->httpClient->request('GET', $api, ['timeout' => 20]);
        }
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        protected function getRealIp( ): ?string
        {
            // in dev mode mock
//            if ( $this->kernel->getEnvironment() === 'dev') {
//                // for test  Bulgary "156.146.55.226"
//                return $this->neoxBag->getIpLocalDev() ;
//            }
            
            $request    = $this->requestStack->getCurrentRequest();
            $ip         = $request->getClientIp();
            
            if (!$request) return null; // or throw an exception
            
            $ip         = $request->getClientIp();
            
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP) && $ip != '127.0.0.1') return $ip;
            
            $ipHeaders = ['X-Real-IP', 'CF-Connecting-IP', 'X-Forwarded-For'];
            foreach ($ipHeaders as $header) {
                if ($request->headers->has($header)) {
                    $ip = $header === 'X-Forwarded-For' ?
                        trim(explode(',', $request->headers->get($header), 2)[0]) :
                        $request->headers->get($header);
                    
                    if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
            
            return $this->neoxBag->getIpLocalDev();
        }
        
        private function stringContainsSubstringFromArray($mainString, $substringsArray): bool
        {
            foreach ($substringsArray as $substring) {
                if (str_contains($mainString, $substring)) {
                    return true; // Return true if any substring is found
                }
            }
            return false; // Return false if none of the substrings are found
        }
        
        protected function getLimiter(string $name, int $expire = 60): bool {
            
            $key = "counter-$name";
            /**
             * @var ItemAdapter $Item
             */
            $Item2  = $this->cache->get( $key, function (ItemInterface $item) use($expire) {
                $item->expiresAfter( (int) $expire); // 3600 = 1 hour
                return 0;
            });
            
            $Item2++;
            
            if( $Item2 < 43 ) {
                
                /** @var CacheItem $item */
                $Item       = $this->cache->getItem( $key );
                $expire     = $Item->getMetadata()['expiry'];
                $this->cache->delete( "counter" );
                $interval   = new \DateInterval("PT{$expire}S");
                $Item2      = $this->cache->get( "counter" , function (ItemInterface $item) use ($expire, $Item2) {
                    $interval = new \DateTime("@$expire", new \DateTimeZone("Europe/Paris"));
                    $item->expiresAt( $interval ); // 3600 = 1 hour
                    return $Item2;
                });
                return true;
            };
            
            return false;
        }
        
        protected function buildClass( string $nameService){
            $className      = "NeoxGeolocator\\NeoxGeolocatorBundle\\Pattern\\Services\\" . $nameService;
            if (class_exists($className)) {
                // Utilisez la réflexion pour instancier la classe du service
                $reflectionClass = new \ReflectionClass($className);
                $serviceInstance = $reflectionClass->newInstance(
                    $this->router,
                    $this->parameterBag,
                    $this->httpClient,
                    $this->requestStack,
                    $this->cache,
                    $this->kernel,
                );
                return $serviceInstance;
            }
        }
        
        protected function getParameter($key): UnitEnum|float|array|bool|int|string|null
        {
            return $this->parameterBag->get($key);
        }
    }