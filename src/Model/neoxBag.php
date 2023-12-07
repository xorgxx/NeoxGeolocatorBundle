<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Model;
    
    class neoxBag
    {
        public ?string      $ip_local_dev               = null;
        public ?string      $custome_api                = null;
        public ?array       $cdn                        = [];
        public ?array       $filterLocal                = [];
        public ?array       $filterConnection           = [];
        public ?array       $filterContinents           = [];
        public ?array       $crawler                    = [];
        public ?string      $name_route_unauthorized    = null;
        public ?int         $timer                      = null;
        public ?string      $check_vpn                  = null;
        public ?array       $check_ping                 = [];
        
        public function getIpLocalDev(): ?string
        {
            return $this->ip_local_dev;
        }
        
        public function setIpLocalDev(?string $ip_local_dev): neoxBag
        {
            $this->ip_local_dev = $ip_local_dev;
            return $this;
        }
        
        public function getCustomeApi(): ?string
        {
            return $this->custome_api;
        }
        
        public function setCustomeApi(?string $custome_api): neoxBag
        {
            $this->custome_api = $custome_api;
            return $this;
        }
        
        public function getCdn(): ?array
        {
            return $this->cdn;
        }
        
        public function setCdn(?array $cdn): neoxBag
        {
            $this->cdn = $cdn;
            return $this;
        }
        
        public function getFilterLocal(): ?array
        {
            return $this->filterLocal;
        }
        
        public function setFilterLocal(?array $filterLocal): neoxBag
        {
            $this->filterLocal = $filterLocal;
            return $this;
        }
        
        public function getFilterConnection(): ?array
        {
            return $this->filterConnection;
        }
        
        public function setFilterConnection(?array $filterConnection): neoxBag
        {
            $this->filterConnection = $filterConnection;
            return $this;
        }
        
        public function getFilterContinents(): ?array
        {
            return $this->filterContinents;
        }
        
        public function setFilterContinents(?array $filterContinents): neoxBag
        {
            $this->filterContinents = $filterContinents;
            return $this;
        }
        
        public function getCrawler(): ?array
        {
            return $this->crawler;
        }
        
        public function setCrawler(?array $crawler): neoxBag
        {
            $this->crawler = $crawler;
            return $this;
        }
        
        public function getNameRouteUnauthorized(): ?string
        {
            return $this->name_route_unauthorized;
        }
        
        public function setNameRouteUnauthorized(?string $name_route_unauthorized): neoxBag
        {
            $this->name_route_unauthorized = $name_route_unauthorized;
            return $this;
        }
        
        public function getTimer(): ?int
        {
            return $this->timer;
        }
        
        public function setTimer(?int $timer): neoxBag
        {
            $this->timer = $timer;
            return $this;
        }
        
        public function getCheckVpn(): ?string
        {
            return $this->check_vpn;
        }
        
        public function setCheckVpn(?string $check_vpn): neoxBag
        {
            $this->check_vpn = $check_vpn;
            return $this;
        }
        
        public function getCheckPing(): ?array
        {
            return $this->check_ping;
        }
        
        public function setCheckPing(?array $check_ping): neoxBag
        {
            $this->check_ping = $check_ping;
            return $this;
        }
        
    }
    