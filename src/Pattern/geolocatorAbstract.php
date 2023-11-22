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
            
        
        protected function setFilterLocal(){
            $local    = $this->neoxBag->getFilterLocal();
            if (!empty($local) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getCountryCode(), $local, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        
        protected function setFilterConnection(){
            $connection    = $this->neoxBag->getFilterConnection();
            if (!empty($connection) && $this->Geolocation->getStatus() !== "fail" && $this->Geolocation->isProxy() ) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        protected function setFilterContinents(){
            $continents    = $this->neoxBag->getFilterContinents();
            if (!empty($continents) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getContinent(), $continents, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        protected function setFilterCrawler(){
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
            
            // Redis manage storage with expiration !!
            $value  = $this->cache->get( self::NAME . $key, function (ItemInterface $item) {
                $timer = $this->neoxBag->getTimer();
                $item->expiresAfter( (int) $timer); // 3600 = 1 hour
                return $this->Geolocator();
            });
            
            if (!$value->isValid()) {
                $route = $this->neoxBag->getNameRouteUnauthorized();
                return $this->router->generate($route);
            }
            return true;
        }
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        protected function getRealIp( ): ?string
        {
            // in dev mode mock
            if ( $this->kernel->getEnvironment() === 'dev') {
                // for test  Bulgary "156.146.55.226"
                return $this->getParameter("neox_geolocator.ip_local_dev") ;
            }
            
            $request    = $this->requestStack->getCurrentRequest();
            $ip         = $request->getClientIp();
            
            if ($request->headers->has('CF-Connecting-IP')) {
                $ip = $request->headers->get('CF-Connecting-IP');
            }
            
            if ($request->headers->has('X-Real-IP')) {
                return $request->headers->get('X-Real-IP');
            }
            
            if ($request->headers->has('X-Forwarded-For')) {
                $ips = explode(',', $request->headers->get('X-Forwarded-For'), 2);
                $ip = trim($ips[0]); // The left-most IP address is the original client
            }
            
            return $ip;
        }
        
        private function stringContainsSubstringFromArray($mainString, $substringsArray) {
            foreach ($substringsArray as $substring) {
                if (strpos($mainString, $substring) !== false) {
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