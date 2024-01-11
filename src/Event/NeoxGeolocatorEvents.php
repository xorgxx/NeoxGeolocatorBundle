<?php
    
    /*
     * This file is part of the GeolockBundle package.
     */
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Event;
    
    use App\Entity\SubscriberNewsLetter;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
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
        
        public GeolocationModel $geolocationModel;
        
        public function __construct( GeolocationModel $geolocationModel)
        {
            $this->geolocationModel = $geolocationModel;
        }
        
        public function getGeolocationModel(): GeolocationModel
        {
            return $this->geolocationModel;
        }
        
        
    }
