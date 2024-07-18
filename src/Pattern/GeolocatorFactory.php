<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\neoxBag;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\IpFinder;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\Checker;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\HttpKernel\KernelInterface;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    
    #[\AllowDynamicProperties]
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
         * @param EventDispatcherInterface $dispatcher
         */
        public function __construct(
            RouterInterface         $router,
            ParameterBagInterface   $parameterBag,
            HttpClientInterface     $httpClient,
            RequestStack            $requestStack,
            CacheInterface          $cache,
            KernelInterface         $kernel,
            NeoxGeoBagService       $neoxGeoBagService,
            EventDispatcherInterface $dispatcher,
        )
        {
            
            $this->router                   = $router;
            $this->parameterBag             = $parameterBag;
            $this->httpClient               = $httpClient;
            $this->requestStack             = $requestStack;
            $this->cache                    = $cache;
            $this->kernel                   = $kernel;
            $this->neoxGeoBagService        = $neoxGeoBagService;
            $this->dispatcher               = $dispatcher;
//            $this->CDN              = $this->getParameter("neox_geolocator.cdn");
//            $this->FILTER           = $this->getParameter("neox_geolocator.filter");
        
        }
        
        /**
         * @throws \ReflectionException
         */
        public function getGeolocatorService(): GeolocatorAbstract
        {
            $neoxGeoBag         = $this->getNeoGeoService()->getNeoxBag();
            $cdnToServiceMap    = $this->prepareCdnToServiceMap();
            $classname          = $this->prepareClassName($cdnToServiceMap, $neoxGeoBag);
            
            return $this->createServiceClassInstance($classname, $neoxGeoBag);
        }
        
        private function prepareCdnToServiceMap(): array
        {
            return [
                "check.getipintel.net"  => "getipintelService",
                "ip-api.com"            => "ipApiService",
                "findip.net"            => "findIpService",
            ];
        }
        
        private function prepareClassName(array $cdnToServiceMap, neoxBag $neoxGeoBag): string
        {
            $service    = $cdnToServiceMap[$neoxGeoBag->getCdn()["api_use"]] ?? "ipApiService";
            $namespace  = "NeoxGeolocator\\NeoxGeolocatorBundle\\Pattern\\Services\\";
            
            return $neoxGeoBag->getCustomeApi() ?: ($namespace . $service);
        }
        
        /**
         * @throws \ReflectionException
         */
        private function createServiceClassInstance($className, $neoxGeoBag)
        {
            if (class_exists($className)) {
                return (new \ReflectionClass($className))->newInstance(
                    $this->router,
                    $this->parameterBag,
                    $this->httpClient,
                    $this->requestStack,
                    $this->cache,
                    $this->kernel,
                    $neoxGeoBag,
                    $this->dispatcher
                );
            }
        }
        
        private function getNeoGeoService(): NeoxGeoBagService
        {
            return new  neoxGeoBagService($this->requestStack, $this->parameterBag);
        }
    }