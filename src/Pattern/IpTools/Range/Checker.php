<?php

    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range;

    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\Range;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\IPAddress;

    /**
     * Base on morsvox/ip-range-checker !!!
     *
     * use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\Checker;
     *
     * $ip      = '192.168.0.22';
     * $checker = Checker::forIp($ip);
     *
     *  $checker->setRange([
     *      '192.168.0.1',
     *      '192.168.0.28'
     *  ]);
     *  $checker->check() will return true for IPs between
     *  192.168.0.1 and 192.168.0.28
     *  192.168.0.19 will return TRUE
     *
     *  $checker->setRange('192.168.0.*');
     *  $checker->check() will return TRUE for IPs between
     *  192.168.0.1 and 192.168.0.255
     *  192.168.0.41 will return TRUE
     *  192.168.1.41 will return FALSE
     *
     *  $checker->setRange('192.168.0.4-192.168.0.54');
     *  $checker->check() will return TRUE for IPs between
     *  192.168.0.4 and 192.168.0.54
     *  192.168.0.41 will return TRUE
     *  192.168.0.61 will return FALSE
     */
    class Checker
    {
        protected $ipAddress;

        protected $range;

        public function __construct($ipAddress)
        {
            $this->ipAddress = IPAddress::fromIPString($ipAddress);
        }

        public static function forIp($ipAddress)
        {
            return new Checker($ipAddress);
        }

        public function getRange()
        {
            return $this->range;
        }

        public function setRange($range)
        {
            $createRange = function ($startStr, $endStr = null) {
                $start = IPAddress::fromIPString(trim($startStr));
                $end   = $endStr ? IPAddress::fromIPString(trim($endStr)) : $start;
                return Range::fromIPs($start, $end);
            };

            $handleWildcard = function ($range) use ($createRange) {
                $startStr = str_replace('*', '0', $range);
                $endStr   = str_replace('*', '255', $range);
                return $createRange($startStr, $endStr);
            };

            if (is_array($range)) {
                $this->range = $createRange($range[ 0 ], $range[ 1 ]);
            } elseif (strpos($range, '-')) {
                list($startStr, $endStr) = explode('-', $range);
                $this->range = $createRange($startStr, $endStr);
            } elseif (substr_count($range, '*') > 0) {
                $this->range = $handleWildcard($range);
            } else {
                $this->range = $createRange($range);
            }
        }


//        public function setRange($range)
//        {
//            if (is_array($range)) {
//                $start = IPAddress::fromIPString($range[ 0 ]);
//                $end   = IPAddress::fromIPString($range[ 1 ]);
//            } else if (strpos($range, '-')) {
//                $ranges = explode('-', $range);
//                $start  = IPAddress::fromIPString($ranges[ 0 ]);
//                $end    = IPAddress::fromIPString($ranges[ 1 ]);
//            } else {
//                if (substr_count($range, '*') == 2) {
//                    $startFirstRun = str_replace('.*.', '.0.', $range);
//                    $endFirstRun   = str_replace('.*.', '.255.', $range);
//                    $start         = IPAddress::fromIPString(str_replace('*', '0', $startFirstRun));
//                    $end           = IPAddress::fromIPString(str_replace('*', '255', $endFirstRun));
//                } else if (substr_count($range, '*') == 1) {
//                    $start = IPAddress::fromIPString(str_replace('*', '0', $range));
//                    $end   = IPAddress::fromIPString(str_replace('*', '255', $range));
//                }
//            }
//            $this->range = Range::fromIPs($start, $end);
//        }

        public function check()
        {
            return $this->range->inRange($this->ipAddress);
        }
    }
