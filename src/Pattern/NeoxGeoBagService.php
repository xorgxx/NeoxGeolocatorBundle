<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Attribute\NeoxGeoBag;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\neoxBag;
    use ReflectionClass;
    use ReflectionException;
    use ReflectionMethod;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\HttpFoundation\RequestStack;
    
    class NeoxGeoBagService
    {
        private array $neoxBagParams    = [];
        public ?neoxBag $neoxBag        = null;
        private string $controller;
        private string $action;
        
        public function __construct(private readonly RequestStack $requestStack, private readonly ParameterBagInterface $parameterBag)
        {
        
        }

        /**
         * @throws ReflectionException
         */
        public function getneoxBag(): neoxBag
        {
            if (!$this->neoxBag) {
                $this->setNeoxBag();
            }
            return $this->neoxBag;
        }

        /**
         * @throws ReflectionException
         */
        public function setNeoxBag(): neoxBag
        {
            // first apply seo settings from configuration
            $this->setNeoxBagParams();
            
            // then apply the controller and method attributes
            $attributes = $this->getAttributesFromControllerAndMethod();
            foreach ($attributes as $attribute) {
                $data = $attribute->newInstance();
                foreach ($data as $key => $value) {
                    if ($value) {
                        $setter = "set" . $this->toCamelCase($key);
                        $this->neoxBag->$setter($value);
                    }
                }
            }
            return $this->neoxBag;
        }
        
        private function setNeoxBagParams(): void
        {
            $this->neoxBag           = new neoxBag();
            $this->neoxBagParams     = $this->parameterBag->get('neox_geolocator');
            $this->neoxBag
                ->setIpLocalDev($this->neoxBagParams ['ip_local_dev'] ?? null)
                ->setCustomeApi($this->neoxBagParams ['custome_api'] ?? null)
                ->setCdn($this->neoxBagParams ['cdn'] ?? null)
                ->setFilterLocal($this->neoxBagParams ['filter']['local'] ?? [])
                ->setFilterConnection($this->neoxBagParams ['filter']['connection'] ?? [])
                ->setFilterContinents($this->neoxBagParams ['filter']['continents'] ?? [])
                ->setCrawler($this->neoxBagParams ['crawler'] ?? [])
                ->setNameRouteExclude($this->neoxBagParams ['name_route_exclude'] ?? null)
                ->setNameRouteUnauthorized($this->neoxBagParams ['name_route_unauthorized'] ?? null)
                ->setTimer($this->neoxBagParams ['timer'] ?? null)
                ->setCheckVpn($this->neoxBagParams ['check_vpn'] ?? null)
                ->setCheckPing($this->neoxBagParams ['check_ping'] ?? [])
                ->setForcer($this->neoxBagParams ['forcer'] ?? false)
            ;
        }

        /**
         * @throws ReflectionException
         */
        private function getAttributesFromControllerAndMethod(): array
        {
            $this->getInfoAboutCurrentRequest();

            // if the controller is empty, we don't need to get the attributes
            if ($this->controller === "null") {
                return [];
            }
            
            $classAttributes    = (new ReflectionClass($this->controller))->getAttributes(NeoxGeoBag::class);
            $methodAttributes   = (new ReflectionMethod($this->controller, $this->action))->getAttributes(NeoxGeoBag::class);
            return array_merge($classAttributes, $methodAttributes);
        }
        
        private function getInfoAboutCurrentRequest(): void
        {
            $request = $this->requestStack->getCurrentRequest();
            
            if ($request) {
                $controllerName = $request->attributes->get('_controller');
                if ($controllerName) {
                    list($this->controller, $this->action) = explode('::', $controllerName);
                }else{
                    // if the controller is empty, create a fake array to avoid errors
                    list($this->controller, $this->action) = ["null", "null"];
                }

            }
        }
        
        private function toCamelCase($str): string
        {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }
    }