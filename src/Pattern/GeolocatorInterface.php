<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
    
    interface GeolocatorInterface
    {
        
        public function Geolocator(): GeolocationModel;
        
        public function getInfoCdn() : GeolocationModel ;
        
    }