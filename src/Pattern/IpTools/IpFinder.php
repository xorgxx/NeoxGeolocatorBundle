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

//        $p =  Request::createFromGlobals();

        // TODO depreciated =======================
        // https://api.ipify.org?format=json
//            $api            = "https://api.ipify.org?format=json";
//            // todo: problem in same cas to get real ip !!!
//            $data   = $this->senApi( $api );
//            $ip     = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
//            return $ip->ip;
        // in dev mode mock
//        if ( $this->kernel->getEnvironment() === 'dev') {
//            // for test  Bulgary "156.146.55.226"
//            return $this->neoxBag->getIpLocalDev() ;
//        }

        $request = Request::createFromGlobals();
        $ip      = $request->getClientIp();


        if ($request->headers->has('x-real-ip')) {
            return $request->headers->get('x-real-ip');
        }

        if ($request->headers->has('CF-Connecting-IP')) {
            $ip = $request->headers->get('CF-Connecting-IP');
        }

        if ($request->headers->has('X-Forwarded-For')) {
            $ips = explode(',', $request->headers->get('X-Forwarded-For'), 2);
            $ip  = trim($ips[ 0 ]); // The left-most IP address is the original client
        }

        return $ip;
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