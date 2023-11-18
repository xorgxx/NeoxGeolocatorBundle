<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Services;
    
    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;
    use JsonException;
    use phpDocumentor\Reflection\Types\Boolean;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\DomCrawler\Crawler;
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
        )
        {
            
            $this->router           = $router;
            $this->parameterBag     = $parameterBag;
            $this->httpClient       = $httpClient;
            $this->requestStack     = $requestStack;
            $this->cache            = $cache;
            $this->CDN              = $this->getParameter("neox_geolocator.cdn");
            $this->FILTER           = $this->getParameter("neox_geolocator.filter");
            
        }
        
        /**
         * @throws GuzzleException
         * @throws ClientExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ServerExceptionInterface
         * @throws TransportExceptionInterface
         * @throws JsonException
         */
        public function getGeoLock($ipCheck = null): void
        {
            
            // check ip
//        $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
//        $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $currentIp = $this->getRealIp();
            
            // http://ip-api.com/docs/api:json#test
            // https://adresse.data.gouv.fr/api-doc/adresse
            // https://vpn-proxy-detection.ipify.org/
//            $response_ = $this->httpClient->request('GET', $this->CDN["ip_info"] . $currentIp, [
//                'decode_content' => false
//            ]);
            $response_      = $this->httpClient->request('GET', $this->CDN["ip_info"] . $currentIp);
            $status         = $response_->getStatusCode(); // 200
            $data['data']   = json_decode($response_->getContent(), false, 512, JSON_THROW_ON_ERROR);// '{"id": 1420053, "name": "guzzle", ...}'
            $data['ip']     = $currentIp;
            // For test only ======================
            $data['valid']  = true;
            // filter on place, country so if is allowed [fr, en]
            $countryCode = $this->FILTER;
            
            if (!empty($data) && $data['data']->status !== "fail" && !in_array($data['data']->countryCode, $countryCode["local"], true)) {
                // Send the modified response object to the event this country is not allowed
                $data['valid'] = false;
            }
            $this->requestStack->getSession()->set('country', $data);
        }
        
        /**
         * @param $zipCode
         *
         * @return array|null
         * @throws GuzzleException*@throws JsonException
         * @throws JsonException
         */
        public function getAround($zipCode): ?array
        {
            // check ip
            // https://www.villes-voisines.fr/getcp.php?cp=91190&rayon=10
            $r          = null;
            $client     = new Client();
            $response_  = $client->request('GET', 'https://www.villes-voisines.fr/getcp.php?cp=' . $zipCode . '&rayon=50');
            $headerType = $response_->getHeaderLine('content-type'); // 'application/json; charset=utf8'
            $data = json_decode($response_->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR); // '{"id": 1420053, "name": "guzzle", ...}'
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
         * @throws GuzzleException
         * @throws JsonException
         * @throws ClientExceptionInterface
         * @throws RedirectionExceptionInterface
         * @throws ServerExceptionInterface
         * @throws TransportExceptionInterface
         */
        public function isAuthorize(): bool
        {
            $t = $this->requestStack->getSession()->get('country');
            if ($t) {
                return $t['valid'];
            }
            $this->getGeoLock();
            return $this->requestStack->getSession()->get('country')['valid'];
        }
        
        /**
         * @return mixed
         */
        public function getIpInfo(): mixed
        {
            return $this->requestStack->getSession()->get('country');
        }
        
        /**
         * @throws RedirectionExceptionInterface
         * @throws ClientExceptionInterface
         * @throws TransportExceptionInterface
         * @throws ServerExceptionInterface
         * @throws GuzzleException|JsonException
         * @throws InvalidArgumentException
         */
        public function checkAuthorize($ip = null): bool|string
        {
            // cache to optimize ux : check very 1 hour security raison
            $key = $this->requestStack->getSession()->getId();
            if ($key) {
                $value = $this->cache->get($key, function (ItemInterface $item) {
                    $timer = $this->getParameter('neox_geolocator.timer');
                    $item->expiresAfter( (int) $timer ); // 3600 = 1 hour
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
         * @throws TransportExceptionInterface
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
            
            if ($request->getClientIp() === "127.0.0.1") {
                $ip = $this->httpClient->request('GET', $this->CDN["ip"])->getContent();
            }
            
            $prefixe = "192.";
            if (str_starts_with($ip, $prefixe)) {
                return $this->httpClient->request('GET', $this->CDN["ip"])->getContent();
            }
            return $ip;
        }
    }