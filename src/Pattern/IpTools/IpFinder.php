<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools;

use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\IpTools\Range\Checker;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class IP Finder
 *
 * IpFinder::get();
 * IpFinder::validate($ip);
 *
 * todo : Proxy finder with system truste symfony !!
 *
 */
class IpFinder
{

    /**
     * @var null
     */
    private static $ip = NULL;


    /**
     * Get Current Active Client IP Address
     *
     * @return bool|null|string
     */
    public static function get()
    {
        if (!isset(self::$ip)) {
            self::$ip = self::_check_ip();
        }
        return self::$ip;
    }

    /**
     * @param $name
     *
     * @return string
     */
    private static function _get($name)
    {
        if (isset($_SERVER)) {
            if (isset($_SERVER[$name])) {
                return $_SERVER[$name];
            }
        } else {
            if (getenv($name)) {
                return getenv($name);
            }
        }

        return FALSE;
    }

    /**
     * @return bool|string
     */
    private static function _check_ip(): bool|string
    {
//        return self::_get('REMOTE_ADDR');
//        if (Request::createFromGlobals()->getClientIp()) {
//            return Request::createFromGlobals()->getClientIp();
//        }

        $headers = [
            'x-real-ip',
            'CF-Connecting-IP',
            'HTTP_CLIENT_IP',
//            'REMOTE_ADDR',
            'X_FORWARDED_FOR',
            'X_FORWARDED',
            'FORWARDED_FOR',
            'FORWARDED'
        ];

        foreach ($headers as $header) {
            $ip = self::_get($header);
            if ($ip) {
                // ip fist is real ip
                return is_array($ip) ? $ip[0] : $ip;
            }
        }

        return 'UNKNOWN';
    }

    /**
     * Validate Given IP Address
     *
     * @param $ip
     *
     * @return bool
     */
    public static function validate($ip)
    {
        return (filter_var($ip, FILTER_VALIDATE_IP)) ? TRUE : FALSE;
    }

    // This need Range tool : class !!!
    public static function checkerIpInRange(array $p): bool
    {
        $ip      = IpFinder::get();

        if ( $ip === 'UNKNOWN') {
            return false;
        }
        $checker = Checker::forIp($ip);

        foreach ($p as $v) {
            $checker->setRange($v);
            if ($checker->check()) {
                return true;
            }
        }

        return false;
    }
}