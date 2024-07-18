<?php

    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range;

    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\IPAddress;

    class Range
    {
        protected $start;
        protected $end;

        public function __construct(IPAddress $start, IPAddress $end)
        {
            $this->start = $start;
            $this->end   = $end;
        }

        public static function fromIPs(IPAddress $start, IPAddress $end)
        {
            return new self($start, $end);
        }

        public function getStart()
        {
            return $this->start;
        }

        public function getEnd()
        {
            return $this->end;
        }

        public function inRange(IPAddress $ipAddress)
        {
            return $ipAddress->toLongInteger() >= $this->start->toLongInteger() &&
                $ipAddress->toLongInteger() <= $this->end->toLongInteger();
        }
    }
