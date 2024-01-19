<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Entity;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Entity\Traits\TimeStampable;
    use NeoxGeolocator\NeoxGeolocatorBundle\Repository\GeolocationRepository;
    use Doctrine\ORM\Mapping as ORM;
    use Doctrine\DBAL\Types\Types;
    
    #[ORM\Entity(repositoryClass: GeolocationRepository::class)]
    #[ORM\HasLifecycleCallbacks]
    
    class Geolocation
    {
        use TimeStampable;
        
        #[ORM\Id]
        #[ORM\GeneratedValue]
        #[ORM\Column]
        private ?int $id = null;
        #[ORM\Column(length: 150)]
        public string $status;
        #[ORM\Column(length: 150)]
        public string $continent;
        #[ORM\Column(length: 150)]
        public string $continentCode;
        #[ORM\Column(length: 150)]
        public string $country;
        #[ORM\Column(length: 150)]
        public string $countryCode;
        #[ORM\Column(length: 150)]
        public string $regionName;
        #[ORM\Column(length: 150)]
        public string $city;
        #[ORM\Column(length: 150)]
        public string $zip;
        #[ORM\Column(type: Types::FLOAT)]
        public float $lat;
        #[ORM\Column(type: Types::FLOAT)]
        public float $lon;
        #[ORM\Column(length: 255)]
        public string $reverse;
        #[ORM\Column(nullable: true)]
        public ?bool $mobile;
        #[ORM\Column(nullable: true)]
        public ?bool $proxy;
        #[ORM\Column(nullable: true)]
        public ?bool $hosting;
        #[ORM\Column(length: 150)]
        public string $query;
        #[ORM\Column(nullable: true)]
        public bool $valid;
        #[ORM\Column(length: 150,nullable: true)]
        public ?string $route;
        
        
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
            $valid          = true,
            $route          = "nc"
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
            $this->route        = $route;
        }
        
        public function getId(): ?int
        {
            return $this->id;
        }
        
        public function getStatus(): string
        {
            return $this->status;
        }
        
        public function setStatus(string $status): self
        {
            $this->status = $status;
            return $this;
        }
        
        public function getContinent(): string
        {
            return $this->continent;
        }
        
        public function setContinent(string $continent): self
        {
            $this->continent = $continent;
            return $this;
        }
        
        public function getContinentCode(): string
        {
            return $this->continentCode;
        }
        
        public function setContinentCode(string $continentCode): self
        {
            $this->continentCode = $continentCode;
            return $this;
        }
        
        public function getCountry(): string
        {
            return $this->country;
        }
        
        public function setCountry(string $country): self
        {
            $this->country = $country;
            return $this;
        }
        
        public function getCountryCode(): string
        {
            return $this->countryCode;
        }
        
        public function setCountryCode(string $countryCode): self
        {
            $this->countryCode = $countryCode;
            return $this;
        }
        
        public function getRegionName(): string
        {
            return $this->regionName;
        }
        
        public function setRegionName(string $regionName): self
        {
            $this->regionName = $regionName;
            return $this;
        }
        
        public function getCity(): string
        {
            return $this->city;
        }
        
        public function setCity(string $city): self
        {
            $this->city = $city;
            return $this;
        }
        
        public function getZip(): string
        {
            return $this->zip;
        }
        
        public function setZip(string $zip): self
        {
            $this->zip = $zip;
            return $this;
        }
        
        public function getLat(): float
        {
            return $this->lat;
        }
        
        public function setLat(float $lat): self
        {
            $this->lat = $lat;
            return $this;
        }
        
        public function getLon(): float
        {
            return $this->lon;
        }
        
        public function setLon(float $lon): self
        {
            $this->lon = $lon;
            return $this;
        }
        
        public function getReverse(): string
        {
            return $this->reverse;
        }
        
        public function setReverse(string $reverse): self
        {
            $this->reverse = $reverse;
            return $this;
        }
        
        public function isMobile(): ?bool
        {
            return $this->mobile;
        }
        
        public function setMobile(?bool $mobile): self
        {
            $this->mobile = $mobile;
            return $this;
        }
        
        public function isProxy(): ?bool
        {
            return $this->proxy;
        }
        
        public function setProxy(?bool $proxy): self
        {
            $this->proxy = $proxy;
            return $this;
        }
        
        public function isHosting(): ?bool
        {
            return $this->hosting;
        }
        
        public function setHosting(?bool $hosting): self
        {
            $this->hosting = $hosting;
            return $this;
        }
        
        public function getQuery(): string
        {
            return $this->query;
        }
        
        public function setQuery(string $query): self
        {
            $this->query = $query;
            return $this;
        }
        
        public function isValid(): bool
        {
            return $this->valid;
        }
        
        public function setValid(bool $valid): self
        {
            $this->valid = $valid;
            return $this;
        }
        
        public function getRoute(): ?string
        {
            return $this->route;
        }
        
        public function setRoute(?string $route): Geolocation
        {
            $this->route = $route;
            return $this;
        }
        
        

        /**
         * @throws \JsonException
         */
        public static function fromJson($json, string $url= "mock"): self
        {
            $data = json_decode($json, true, 512, JSON_ERROR_NONE) ? : null;
//            if ($data === 'fail' || $data === '') {
//                $data['status']        = 'mock';
//                $data['continent']     = 'Europe';
//                $data['continentCode'] = 'EU';
//                $data['country']       = 'France';
//                $data['countryCode']   = 'FR';
//                $data['regionName']    = 'Paris';
//                $data['city']          = 'Paris';
//                $data['zip']           = '75000';
//                $data['lat']           = 40.6951;
//                $data['lon']           = 20.325;
//                $data['reverse']       = 'unn-156-146-55-226.cdn';
//                $data['mobile']        = false;
//                $data['proxy']         = false;
//                $data['hosting']       = false;
//                $data['query']         = '156.146.55.226';
//                $data['valid']         = true;
//                return $data;
//            }

            return new self(
                $url ,
                $data['continent'] ?? 'Europe',
                $data['continentCode'] ?? 'EU',
                $data['country'] ?? 'France',
                $data['countryCode'] ?? 'FR',
                $data['regionName'] ?? 'Paris' ,
                $data['city'] ?? 'Paris',
                $data['zip'] ?? '75100',
                $data['lat'] ?? 40.6951,
                $data['lon'] ?? 20.325,
                $data['reverse'] ?? 'unn-156-146-55-226.cdn',
                $data['mobile'] ?? false,
                $data['proxy']  ?? false,
                $data['hosting'] ?? false,
                $data['query'] ?? '156.146.55.226',
                $data['valid'] = true,
                $data['route'] = "nc"
            );
        }
        
        public function toArray(): array
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
                'route'         => $this->route,
            ];
        }
    }