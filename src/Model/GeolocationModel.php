<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Model;
    
    class GeolocationModel
    {
        public string $status;
        public string $continent;
        public string $continentCode;
        public string $country;
        public string $countryCode;
        public string $regionName;
        public string $city;
        public string $zip;
        public float $lat;
        public float $lon;
        public string $reverse;
        public bool $mobile;
        public bool $proxy;
        public bool $hosting;
        public string $query;
        public bool $valid;
        
        
        public function __construct(
            $status         = 'success',
            $continent      = 'Europe',
            $continentCode  = 'EU',
            $country        = 'Bulgaria',
            $countryCode    = 'BG',
            $regionName     = 'Sofia-Capital',
            $city           = 'Sofia',
            $zip            = '1000',
            $lat            = 42.6951,
            $lon            = 23.325,
            $reverse        = 'unn-156-146-55-226.cdn77.com',
            $mobile         = false,
            $proxy          = true,
            $hosting        = true,
            $query          = '156.146.55.226',
            $valid          = true
        ) {
            $this->status       = $status;
            $this->continent    = $continent;
            $this->continentCode  = $continentCode;
            $this->country      = $country;
            $this->countryCode  = $countryCode;
            $this->regionName   = $regionName;
            $this->city         = $city;
            $this->zip          = $zip;
            $this->lat          = $lat;
            $this->lon          = $lon;
            $this->reverse      = $reverse;
            $this->mobile       = $mobile;
            $this->proxy        = $proxy;
            $this->hosting      = $hosting;
            $this->query        = $query;
            $this->valid        = $valid;
        }
        
        public function getStatus(): string
        {
            return $this->status;
        }
        
        public function setStatus(string $status): GeolocationModel
        {
            $this->status = $status;
            return $this;
        }
        
        public function getContinent(): string
        {
            return $this->continent;
        }
        
        public function setContinent(string $continent): GeolocationModel
        {
            $this->continent = $continent;
            return $this;
        }
        
        public function getContinentCode(): string
        {
            return $this->continentCode;
        }
        
        public function setContinentCode(string $continentCode): GeolocationModel
        {
            $this->continentCode = $continentCode;
            return $this;
        }
        
        public function getCountry(): string
        {
            return $this->country;
        }
        
        public function setCountry(string $country): GeolocationModel
        {
            $this->country = $country;
            return $this;
        }
        
        public function getCountryCode(): string
        {
            return $this->countryCode;
        }
        
        public function setCountryCode(string $countryCode): GeolocationModel
        {
            $this->countryCode = $countryCode;
            return $this;
        }
        
        public function getRegionName(): string
        {
            return $this->regionName;
        }
        
        public function setRegionName(string $regionName): GeolocationModel
        {
            $this->regionName = $regionName;
            return $this;
        }
        
        public function getCity(): string
        {
            return $this->city;
        }
        
        public function setCity(string $city): GeolocationModel
        {
            $this->city = $city;
            return $this;
        }
        
        public function getZip(): string
        {
            return $this->zip;
        }
        
        public function setZip(string $zip): GeolocationModel
        {
            $this->zip = $zip;
            return $this;
        }
        
        public function getLat(): float
        {
            return $this->lat;
        }
        
        public function setLat(float $lat): GeolocationModel
        {
            $this->lat = $lat;
            return $this;
        }
        
        public function getLon(): float
        {
            return $this->lon;
        }
        
        public function setLon(float $lon): GeolocationModel
        {
            $this->lon = $lon;
            return $this;
        }
        
        public function getReverse(): string
        {
            return $this->reverse;
        }
        
        public function setReverse(string $reverse): GeolocationModel
        {
            $this->reverse = $reverse;
            return $this;
        }
        
        public function isMobile(): bool
        {
            return $this->mobile;
        }
        
        public function setMobile(bool $mobile): GeolocationModel
        {
            $this->mobile = $mobile;
            return $this;
        }
        
        public function isProxy(): bool
        {
            return $this->proxy;
        }
        
        public function setProxy(bool $proxy): GeolocationModel
        {
            $this->proxy = $proxy;
            return $this;
        }
        
        public function isHosting(): bool
        {
            return $this->hosting;
        }
        
        public function setHosting(bool $hosting): GeolocationModel
        {
            $this->hosting = $hosting;
            return $this;
        }
        
        public function getQuery(): string
        {
            return $this->query;
        }
        
        public function setQuery(string $query): GeolocationModel
        {
            $this->query = $query;
            return $this;
        }
        
        public function isValid(): bool
        {
            return $this->valid;
        }
        
        public function setValid(bool $valid): GeolocationModel
        {
            $this->valid = $valid;
            return $this;
        }
        
        public static function fromJson($json)
        {
            $data = json_decode($json, true);
            
            if ($data === 'fail') {
                $data['status']        = 'success';
                $data['continent']     = 'Europe';
                $data['continentCode'] = 'EU';
                $data['country']       = 'France';
                $data['countryCode']   = 'FR';
                $data['regionName']    = 'Paris';
                $data['city']          = 'Paris';
                $data['zip']           = '75000';
                $data['lat']           = 40.6951;
                $data['lon']           = 20.325;
                $data['reverse']       = 'unn-156-146-55-226.cdn';
                $data['mobile']        = false;
                $data['proxy']         = false;
                $data['hosting']       = false;
                $data['query']         = '156.146.55.226';
                $data['valid']         = true;
                return $data;
            }

            return new self(
                $data['status']  ,
                $data['continent'],
                $data['continentCode'],
                $data['country'],
                $data['countryCode'],
                $data['regionName'],
                $data['city'],
                $data['zip'],
                $data['lat'],
                $data['lon'],
                $data['reverse'],
                $data['mobile'],
                $data['proxy'],
                $data['hosting'],
                $data['query'],
                $data['valid'] = true
            );
        }
        
        public function toArray()
        {
            return [
                'status'        => $this->status,
                'continent'     => $this->continent,
                'continentCode' => $this->continentCode,
                'country'       => $this->country,
                'countryCode'   => $this->countryCode,
                'regionName'    => $this->regionName,
                'city'          => $this->city,
                'zip'           => $this->zip,
                'lat'           => $this->lat,
                'lon'           => $this->lon,
                'reverse'       => $this->reverse,
                'mobile'        => $this->mobile,
                'proxy'         => $this->proxy,
                'hosting'       => $this->hosting,
                'query'         => $this->query,
                'valid'         => $this->valid,
            ];
        }
    }