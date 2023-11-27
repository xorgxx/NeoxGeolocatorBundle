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
            // First check have no id session yet so we nock one to pass, expire will be very short
            // lake this next clic anyware be check againe !!! this time id session will be create
            $this->requestStack->getSession()->set("geo","id");
            $session    = $this->requestStack->getSession()->getId();

            $timer      = $this->neoxBag->getTimer();
            if ( !$session ) {
                $session    = uniqid("pass_", true);
                $timer      = 30;
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
        
        /**
         * @throws TransportExceptionInterface
         */
        protected function senApi(string $api ): ?string
        {
            try {
                $response_  = $this->httpClient->request('GET', $api, ['timeout' => 20]);
                return $response_->getContent();
            } catch (\Exception $e) {
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
            $api            = "https://api.ipify.org?format=json";
            // todo: problem in same cas to get real ip !!!
            $data   = $this->senApi( $api );
            $ip     = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
            return $ip->ip;
//            // in dev mode mock
//            if ( $this->kernel->getEnvironment() === 'dev') {
//                // for test  Bulgary "156.146.55.226"
//                return $this->neoxBag->getIpLocalDev() ;
//            }
//
//            $request    = $this->requestStack->getCurrentRequest();
//            $ip         = $request->getClientIp();
//
//            if ($request->headers->has('X-Real-IP')) {
//                return $request->headers->get('X-Real-IP');
//            }
//
//            if ($request->headers->has('CF-Connecting-IP')) {
//                $ip = $request->headers->get('CF-Connecting-IP');
//            }
//
//            if ($request->headers->has('X-Forwarded-For')) {
//                $ips = explode(',', $request->headers->get('X-Forwarded-For'), 2);
//                $ip = trim($ips[0]); // The left-most IP address is the original client
//            }
//
//            return $ip;
        }
        
        private function stringContainsSubstringFromArray(string $mainString, array $substringsArray): bool
        {
            return array_reduce($substringsArray, static function(bool $carry, string $substring) use ($mainString): bool {
                return $carry || str_contains($mainString, $substring);
            }, false);
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

        protected function getParameter($key): UnitEnum|float|array|bool|int|string|null
        {
            return $this->parameterBag->get($key);
        }
    }