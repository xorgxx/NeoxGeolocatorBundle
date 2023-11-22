<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    
    class GeolocatorFactory
    {
        /**
         * @var RouterInterface
         */
        private $router;
        /**
         * @var ParameterBagInterface
         */
        private $parameterBag;
        /**
         * @var HttpClientInterface
         */
        private $httpClient;
        /**
         * @var RequestStack
         */
        private $requestStack;
        
        private KernelInterface $kernel;
        /**
         * @var CacheInterface
         */
        private CacheInterface $cache;
        
        private NeoxGeoBagService $neoxGeoBagService;
        
        private array $CDN;
        private array $FILTER;
        
        
        /**
         * @param RouterInterface $router
         * @param ParameterBagInterface $parameterBag
         * @param HttpClientInterface $httpClient
         * @param RequestStack $requestStack
         * @param CacheInterface $cache
         */
        public function __construct(
            RouterInterface         $router,
            ParameterBagInterface   $parameterBag,
            HttpClientInterface     $httpClient,
            RequestStack            $requestStack,
            CacheInterface          $cache,
            KernelInterface         $kernel,
            NeoxGeoBagService       $neoxGeoBagService,
        )
        {
            
            $this->router                   = $router;
            $this->parameterBag             = $parameterBag;
            $this->httpClient               = $httpClient;
            $this->requestStack             = $requestStack;
            $this->cache                    = $cache;
            $this->kernel                   = $kernel;
            $this->neoxGeoBagService        = $neoxGeoBagService;
//            $this->CDN              = $this->getParameter("neox_geolocator.cdn");
//            $this->FILTER           = $this->getParameter("neox_geolocator.filter");
            
        }
        
        /**
         * @return CacheInterface
         */
        public function getGeolocatorService(): geolocatorAbstract
        {
            
            $cdnToServiceMap = [
                "check.getipintel.net"  => "getipintelService",
                "ip-api.com"            => "ipApiService",
                "findip.net"            => "findIpService",
            ];
            
            $neoxGeoBag     = $this->getNeoGeoService()->getneoxBag();
//            $cdnValue       = $this->parameterBag->get('neox_geolocator.cdn')["api_use"];
            $nameService    = $cdnToServiceMap[$neoxGeoBag->getCdn()["api_use"]] ?? "ipApiService";
            
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
                    $neoxGeoBag
                );
                return $serviceInstance;
            }
        }
        
        private function getNeoGeoService(){
            return new  neoxGeoBagService($this->requestStack, $this->parameterBag);
        }
    }