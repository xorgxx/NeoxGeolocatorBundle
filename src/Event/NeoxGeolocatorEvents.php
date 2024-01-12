<?php
    
    /*
     * This file is part of the GeolockBundle package.
     */
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Event;
    
    use App\Entity\SubscriberNewsLetter;
    use NeoxGeolocator\NeoxGeolocatorBundle\Entity\Geolocation;
    use NeoxGeolocator\NeoxGeolocatorBundle\Repository\GeolocationRepository;
    use Symfony\Contracts\EventDispatcher\Event;
    
    /**
     * Contains all events thrown in the SubscriberBundle.
     */
    class NeoxGeolocatorEvents extends Event
    {
        const NEOX_GEOLOCATOR_EVENT     = 'neox.geolocator.event';
        const NEOX_GEOLOCATOR_PASS      = 'neox.geolocator.pass';
        const NEOX_GEOLOCATOR_FAIL      = 'neox.geolocator.fail';
        /**
         * The SUBSCRIBE event occurs when the change password process is initialized.
         *
         * This event allows you to modify the default values of the user before binding the form.
         *
         * @Event("App\EventSubscriber\EmailSendSubscriber")
         */
        
        public Geolocation $geolocation;
        
        public function __construct( Geolocation $geolocation)
        {
            $this->geolocation              = $geolocation;

        }
        
        public function getGeolocation(): Geolocation
        {
            return $this->geolocation;
        }
        
    }
