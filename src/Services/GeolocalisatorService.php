<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Services;
    
//    use GuzzleHttp\Client;
//    use GuzzleHttp\Exception\GuzzleException;
    use JsonException;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
//    use phpDocumentor\Reflection\Types\Boolean;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
//    use Symfony\Component\DomCrawler\Crawler;
    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\RequestStack;
    use Symfony\Component\Routing\RouterInterface;
    use Symfony\Contracts\Cache\CacheInterface;
    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
    use Symfony\Contracts\HttpClient\HttpClientInterface;
    use Symfony\Component\HttpKernel\KernelInterface;
    use UnitEnum;
    
    class GeolocalisatorService
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
        
        /**
         * @throws ClientExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ServerExceptionInterface
         * @throws TransportExceptionInterface
         */
        public function getGeoLock($ipCheck = null): null|GeolocationModel
        {
            
            // check ip
//        $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
//        $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $currentIp = $this->getRealIp();
            
            // http://ip-api.com/docs/api:json#test
            // https://adresse.data.gouv.fr/api-doc/adresse
            // https://vpn-proxy-detection.ipify.org/
            
            // get geolocation
            $Geolocation = $this->getInfoCdn($currentIp);
            
            // set filter Local
            $this->setFilterLocal($Geolocation);
            
            // set filter Connection
            $this->setFilterConnection($Geolocation);
            
            // set filter contement
            $this->setFilterContinents($Geolocation);
            
            $this->requestStack->getSession()->set('geolocator', $Geolocation);
            
            return $Geolocation;
        }
        
        private function setFilterLocal(GeolocationModel $Geolocation){
            $local    = $this->FILTER["local"];
            if (!empty($local) && $Geolocation->getStatus() !== "fail" && !in_array($Geolocation->getCountryCode(), $local, true)) {
                // Send the modified response object to the event this country is not allowed
                $Geolocation->setValid(false);
            }
        }
        
        private function setFilterConnection(GeolocationModel $Geolocation){
            $connection    = $this->FILTER["connection"];
            if (!empty($connection) && $Geolocation->getStatus() !== "fail" ) {
                // Send the modified response object to the event this country is not allowed
                $Geolocation->setValid(!$Geolocation->isProxy());
            }
        }
        
        private function setFilterContinents(GeolocationModel $Geolocation){
            $continents    = $this->FILTER["continents"];
            if (!empty($continents) && $Geolocation->getStatus() !== "fail" && !in_array($Geolocation->getContinent(), $continents, true)) {
                // Send the modified response object to the event this country is not allowed
                $Geolocation->setValid(false);
            }
        }
        
        /**
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        private function getInfoCdn(string $currentIp){
            
            // in dev mode mock
            if ( $this->kernel->getEnvironment() === 'dev') {
                // for test  Bulgary "156.146.55.226"
                $currentIp      = $this->getParameter("neox_geolocator.ip_local_dev") ;
            }
            
            $response_      = $this->httpClient->request('GET', $this->CDN["ip_info"] . $currentIp . "?fields=status,message,continent,continentCode,country,countryCode,regionName,city,zip,lat,lon,reverse,mobile,proxy,hosting,query");
//            $status         = $response_->getStatusCode(); // 200
            return GeolocationModel::fromJson($response_->getContent());
        }
        
        private function getVpnCdn(string $currentIp){
            // just in case we need new check vpn proxy blablabla
            // http://check.getipintel.net/check.php?ip=xxxxxx&contact=dede@aol.com&format=json&flags=m
            $response_ = $this->httpClient->request('GET', $this->CDN["check_vpn"] . "?ip=$currentIp&contact=dede@aol.com&format=json&flags=m");
            $status = $response_->getStatusCode(); // 200
            $data['vpn']   = json_decode($response_->getContent(), false, 512, JSON_THROW_ON_ERROR);// '{"id": 1420053, "name": "guzzle", ...}'
            return $data;
        }
        
        /**
         * @param $zipCode
         *
         * @return array|null
         * *@throws TransportExceptionInterface|JsonException
         */
        public function getAround($zipCode): ?array
        {
            // check ip
            // https://www.villes-voisines.fr/getcp.php?cp=91190&rayon=10
            $r          = null;
//            $client     = new Client();
            $response_  = $this->httpClient->request('GET', 'https://www.villes-voisines.fr/getcp.php?cp=' . $zipCode . '&rayon=50');
            $data = json_decode($response_->getContent(), false, 512, JSON_THROW_ON_ERROR); // '{"id": 1420053, "name": "guzzle", ...}'
            if ($data) {
                foreach ($data as $item) {
                    $r[$item->code_postal] = $item->code_postal;
                }
            }
            sort($r);
            return $r;
        }
        
        
        /**
         * @return bool
         * @throws ClientExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ServerExceptionInterface
         * @throws TransportExceptionInterface
         */
        public function isAuthorize(): bool
        {
            $t = $this->requestStack->getSession()->get('geolocator');
            if ($t instanceof  GeolocationModel) {
                return $t->isValid();
            }
            $t = $this->getGeoLock();
            return $t->isValid();
        }
        
        /**
         * @return mixed
         */
        public function getIpInfo(): mixed
        {
            return $this->requestStack->getSession()->get('geolocator');
        }
        
        /**
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws InvalidArgumentException
         */
        public function checkAuthorize($ip = null): bool|string
        {
            // cache to optimize ux : check very 1 hour security raison
            $key = $this->requestStack->getSession()->getId();
            if ($key) {
                $value = $this->cache->get($key, function (ItemInterface $item) {
                    $timer = $this->getParameter('neox_geolocator.timer');
                    $item->expiresAfter( (int) $timer); // 3600 = 1 hour
                    $this->getGeoLock();
                    return true;
                });
            }
            if (!$this->isAuthorize()) {
                $route = $this->getParameter("neox_geolocator.name_route_unauthorized");
                return $this->router->generate($route);
            }
            return false;
        }
        
        private function getParameter($key): UnitEnum|float|array|bool|int|string|null
        {
            return $this->parameterBag->get($key);
        }
        
        /**
         * @throws ServerExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         */
        private function getRealIp( ): ?string
        {
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
    }