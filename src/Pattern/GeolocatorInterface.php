<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Entity\Geolocation;
    
    interface GeolocatorInterface
    {
        
        public function Geolocator(): Geolocation;
        
        public function getInfoCdn() : Geolocation;
        
    }