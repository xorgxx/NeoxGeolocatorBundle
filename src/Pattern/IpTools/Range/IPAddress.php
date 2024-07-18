<?php

    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range;
    class IPAddress
    {
        protected $ipAddress;

        public function __construct($ipAddress)
        {
            if (!$this->ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP)) {
                die('Invalid IP');
            }
        }

        public static function fromIPString($ipAddress)
        {
            return new self($ipAddress);
        }

        public function toLongInteger()
        {
            return ip2long($this->ipAddress);
        }

        public function getIPAddress()
        {
            return $this->ipAddress;
        }
    }
