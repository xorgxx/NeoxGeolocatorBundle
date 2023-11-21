<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
    use Psr\Cache\InvalidArgumentException;
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
        protected GeolocationModel $Geolocation;
        
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
        )
        {
            
            $this->router           = $router;
            $this->parameterBag     = $parameterBag;
            $this->httpClient       = $httpClient;
            $this->requestStack     = $requestStack;
            $this->cache            = $cache;
            $this->kernel           = $kernel;
            $this->CDN              = $this->getParameter("neox_geolocator.cdn");
            $this->FILTER           = $this->getParameter("neox_geolocator.filter");
            
        }
            
        
        protected function setFilterLocal(){
            $local    = $this->FILTER["local"];
            if (!empty($local) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getCountryCode(), $local, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        
        protected function setFilterConnection(){
            $connection    = $this->FILTER["connection"];
            if (!empty($connection) && $this->Geolocation->getStatus() !== "fail" ) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(!$this->Geolocation->isProxy());
            }
        }
        
        protected function setFilterContinents(){
            $continents    = $this->FILTER["continents"];
            if (!empty($continents) && $this->Geolocation->getStatus() !== "fail" && !in_array($this->Geolocation->getContinent(), $continents, true)) {
                // Send the modified response object to the event this country is not allowed
                $this->Geolocation->setValid(false);
            }
        }
        
        protected function setFilterCrawler(){
            $crawler    = $this->getParameter("neox_geolocator.crawler");
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
                $timer = $this->getParameter('neox_geolocator.timer');
                $item->expiresAfter( (int) $timer); // 3600 = 1 hour
                return $this->Geolocator();
            });
            
            if (!$value->isValid()) {
                $route = $this->getParameter("neox_geolocator.name_route_unauthorized");
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
        
        protected function getParameter($key): UnitEnum|float|array|bool|int|string|null
        {
            return $this->parameterBag->get($key);
        }
    }