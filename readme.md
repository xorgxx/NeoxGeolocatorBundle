# NeoxGeolocatorBundle { Symfony 6 }
This bundle provides additional tools geolocator in your application.
Its main goal is to make it simple for you to manage integration additional tools & acte as firwall!

[![2023-11-28-22-27-57.png](https://i.postimg.cc/c473bp6F/2023-11-28-22-27-57.png)](https://postimg.cc/4mdnKF6c)

Let say you want not people from "South America" access to your application or people 
access with Vpn, Proxy, Tor or you want only people from "Paris". Geolocation IT'S for you !!
Even you cant filter mobile !! so if all filter will render "Seo_unauthorized" !! and not will access to your web site

## Installation BETA VERSION !!
Install the bundle for Composer !! as is still on beta version !!

````
  composer require xorgxx/neox-geolocator-bundle
  or 
  composer require xorgxx/neox-geolocator-bundle:0.*
````

Make sure that is register the bundle in your AppKernel:
```php
Bundles.php
<?php

return [
    .....
    NeoxGeolocator\NeoxGeolocatorBundle\NeoxGeolocatorBundle::class => ['all' => true],
    .....
];
```

**NOTE:** _You may need to use [ symfony composer dump-autoload ] to reload autoloading_

 ..... Done 🎈

## Configuration
* Install and configure  ==> [Symfony config](https://symfony.com/doc/current/notifier.html#installation)
* Creat neox_geolocator.yaml in config folder
```
└─── config
│   └─── packages
│       └─── neox_geolocator.yaml
|       └─── ..... 
```
## neox_seo.yaml
It set automatique but you can custom (by default)
```
   neox_geolocator:
      ip_local_dev: "156.146.55.226" # for test  Bulgary "156.146.55.226"
      forcer: false # this is to force to get new geolocator same timer it's userfull.
      check_ping:  # false | true it will protect agains "death ping" !!!
          on: false       # on off
          expire: 10      # check expiration to reste timer
          ping:   5       # after x ping on "expire" value it will banni for xxx
          banni: 700      # in seconde banni time
      cdn:
          api_use: "findip.net" # ip-api.com freemium,  check.getipintel.net FREE, https://findip.net/ free
          api_key: "xxxxxxxxxxxxxxxxxx"
      filter:
          # Local how can in website | rule order read = 1
          local:
              - 'FR'
              - 'BG'
   
          # Continents how can in website | rule order read = 2
          continents:
              - "Europe"
              - "North America"
      #            - "South America"
      #            - "Asia"  
          # Connection how cant in website !!! | rule order read = 3
          connection:
              - "vpn"
              - "proxy"
              
      # Crawler how can in website !! | rule order read = 4
      crawler:
          - "Googlebot"
          - "Bingbot"
          - "YandexBot"
          - "AppleBot"
          - "DuckDuckBot"
          - "BaiduSpider"
          - "SpiderSogou"
          - "FacebookExternalHit"
          - "Exabot"
          - "Swiftbot"
          - "SlurpBot"
          - "CCBot"
          - "GoogleOther"
          - "Google-InspectionTool"
        
      name_route_unauthorized: "Seo_unauthorized"
      timer: 10
      check_vpn: "seo_check_vpn" >>> name route to redirect when detect no id session yet 
```
## How to use ?
 * Well it pretty match all !!
 * be aware that i use Redis to store session information !!! it may have impact in your application.
 * Cache system to put expirAfter to be shure that the key will expire after xx and force the controlle againe.

just need to create route & template to `name_route_unauthorized: "Seo_unauthorized"`
````php
    /**
     * @Route("/unauthorized", name="Seo_unauthorized")
     * @param Request $request
     * @return Response
     */
     
    #[NeoxGeoBag( forcer: true, filterLocal: ["RU", "GB"], filterContinents: ["Asia"])]   
    public function unauthorized(Request $request, CacheItemPoolInterface  $adapter): Response
    {
        $session    = $request->getSession();
        $Geolocator = $adapter->getItem(geolocatorAbstract::NAME . $session->getId());
        $metadata   = $Geolocator->getMetadata();
        
        if ( $Geolocator && $Geolocator->isHit() && array_key_exists('expiry', $metadata)) {
            $expirationTimestamp    = $metadata['expiry'];
            $expirationDateTime     = $date = new DateTime("@$expirationTimestamp");
        };
        
        $i = $Geolocator->get("value");
        return $this->render('unauthorized.html.twig', [
            "Geolocator"        => $Geolocator->get("value"),
            "timer"             => $expirationDateTime ?? null,
        ]);
    }
````

````twig
    {% block HEADERSLIDER %}
    
        <section id="slider" class="slider-element min-vh-100 page-section slide-img include-header"
                 data-animate="img-to-right"
                 style="background: url('{{ imgErrorBackg }}') center center no-repeat; background-size: cover;">
            <div class="slider-inner">
                <div class="vertical-middle">
    
                	<div class="container-fluid clearfix vertical-middle" style="z-index: 6">
					<div class="heading-block center topmargin nobottomborder">

						{% if Geolocator is not null %}
							<i style="line-height:unset !important;"
							   class="fa-2xl fi fi-{{ Geolocator.countryCode|lower == 'en' ? 'gb' : Geolocator.countryCode|lower }}"></i>
							{% if Geolocator.valid %}
								<h1>{{ 'unauthorized.title'|trans }} </h1>
								<span><i class="{{ Geolocator.valid ? 'text-success' : 'text-danger' }} fa-solid {{ Geolocator.valid ? 'fa-check-square' : 'fa-window-close' }}"></i> <strong>{{ Geolocator.country }}</strong>, {{ Geolocator.city }}</span>

							{% else %}
								<h1>{{ 'unauthorized.fail.title'|trans }} </h1>
								<span><i class="{{ Geolocator.valid ? 'text-success' : 'text-danger' }} fa-solid {{ Geolocator.valid ? 'fa-check-square' : 'fa-window-close' }}"></i> <strong>{{ Geolocator.country }}</strong>, {{ Geolocator.city }}</span>
								{% if Geolocator.proxy|default(0) %}
									<span><i class="{{ Geolocator.proxy|default(0) ? 'text-danger' : 'text-success' }} fa-solid {{ Geolocator.proxy|default(0) ? 'fa-window-close' : 'fa-check-square' }}"></i> Proxy / Vpn / Tor : {{ Geolocator.proxy|default(0) ? 'Detecté !' : ' - ' }}</span>
								{% endif %}
								<span>{{ 'unauthorized.fail.subtitle'|trans }}</span>

								<div class="row justify-content-center col-mb-50">
									<div class="col-sm-6 col-lg-4">
									</div>

									<div class="col-sm-6 col-lg-4">
										<div class="feature-box fbox-center fbox-light fbox-plain">
											<div class="fbox-icon">
												<a href="#"><i class="icon-time"></i></a>
											</div>
											<div class="fbox-content">
												<h3>{{ 'unauthorized.title-time'|trans |raw }}</h3>
												<p>{{ 'unauthorized.subtitle-time'|trans |raw }}</p>
											</div>
										</div>
									</div>
								</div>
							{% endif %}
							<small>- {{ timer|format_datetime('short', timezone='Europe/Paris') }} -</small>

						{% else %}
							<span><i class="text-danger fa-solid fa-window-close"></i> Proxy / Vpn / Tor : Detecté !</span>
						{% endif %}
					</div>

				</div>
            </div>
            </div>
        </section>
    {% endblock %}
````

Exemple twig check_vpn 

````php
    .....
    
    {% block javascripts  %}
        <script>
         // Wait 0.5 seconds (500 milliseconds) then redirect
            setTimeout(function() {
                window.location.href = "/";
            }, 500);
        </script>
   
    {% endblock %}

````
## Advanced Usage
* you have your onwn provider service to get geolocation ? no propblem :

```
   neox_geolocator:
      ....
      custome_api: app\services\IpApiService # path to your file
      ....
```
This file have structure :

```php
    namespace App\Services;
    
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\GeolocationModel;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\geolocatorAbstract;
    use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorInterface;
    use Psr\Cache\InvalidArgumentException;
    use Symfony\Component\Cache\Adapter\FilesystemAdapter;
    use Symfony\Component\Cache\CacheItem;
    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
    use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
    
    class ipApiService extends geolocatorAbstract implements GeolocatorInterface
    {
        public function Geolocator(): geolocationModel
        {
            
            // get geolocation
            $this->Geolocation = $this->getInfoCdn();
            
            // set filter Local
            $this->setFilterLocal();
            
            // set filter contement
            $this->setFilterContinents();
            
            // set filter Connection
            $this->setFilterConnection();
            
            // set crawler
            $this->setFilterCrawler();
            
//            $this->requestStack->getSession()->set('geolocator', $this->Geolocation);
            
            return $this->Geolocation;
            
            // TODO: Implement Geolocator() method.
        }
        
        public function getInfoCdn(): GeolocationModel{
            
            // check ip
            // $currentIp = $ipCheck ?: $this->httpClient->request('GET', $this->CDN["ip"] )->getContent();
            // $currentIp      = $this->requestStack->getCurrentRequest()->getClientIp();
            $data   = "";
            if ( $this->getLimiter('ipapi') ) {
                $currentIp      = $this->getRealIp();
                $api            = "http://" . $this->neoxBag->getCdn()["api_use"] . "/json/$currentIp?fields=status,message,continent,continentCode,country,countryCode,regionName,city,zip,lat,lon,reverse,mobile,proxy,hosting,query";
                // todo: check if this expires !!!
                $response_      = $this->httpClient->request('GET', $api );
                $data           = $response_->getContent();
                
                # for adaptation data 2 options
                # FIRST OPTION
                return GeolocationModel::fromJson($data); 
                
                # SECOND OPTION
                $geolocationModel   = new GeolocationModel();
                    $geolocationModel->setstatus('success')               // = ;
                    ->setcontinent($o["continent"]["names"]["fr"])          // = 'Europe';
                    ->setcontinentCode($o["continent"]["code"])             // = 'EU';
                    ->setcountry($o["country"]["names"]["en"])              // = 'France';
                    ->setcountryCode($o["country"]["iso_code"])             // = 'FR';
                    ->setregionName($o["subdivisions"][0]["names"]["en"])   // = 'Paris';
                    ->setcity($o["city"]["names"]["en"])                    // = 'Paris';
                    ->setzip($o["postal"]["code"])                          // = '75000';
                    ->setlat($o["location"]["latitude"])                    // = 40.6951;
                    ->setlon($o["location"]["longitude"])                   // = 20.325;
                    ->setreverse($o["traits"]["isp"])                       // = 'unn-156-146-55-226.cdn';
                    ->setmobile('nc')                                 // = false;
                    ->setproxy(($o["traits"]["connection_type"] == 'Corporate' ? true : false))     // = false;
                    ->sethosting(($o["traits"]["user_type"] == 'hosting' ? true : false))           // = false;
                    ->setquery($currentIp)           // = '156.146.55.226';
                    ->setvalid(true)            // = true;
                ;
            
              return $geolocationModel;
            }else{
                /** @var geolocatorAbstract $class */
                $class = $this->buildClass("findIpService");
                return  $class->Geolocator();
            }
        }
    }

```

## Tools !


## Contributing
If you want to contribute \(thank you!\) to this bundle, here are some guidelines:

* Please respect the [Symfony guidelines](http://symfony.com/doc/current/contributing/code/standards.html)
* Test everything! Please add tests cases to the tests/ directory when:
    * You fix a bug that wasn't covered before
    * Annotation !!
## Todo
* Packagist

## Thanks