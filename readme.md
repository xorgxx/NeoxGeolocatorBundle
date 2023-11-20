# NeoxGeolocatorBundle { Symfony 6 }
This bundle provides additional tools geolocator in your application.
Its main goal is to make it simple for you to manage integration additional tools!

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
      cdn:
          ip: 'https://ipecho.net/plain'
          ip_info: "http://ip-api.com/json/"
      filter:
          # Local how can in website
          local:
              - 'FR'
              - 'BG'
          # Connection how cant in website !!!
          connection:
              - "vpn"
              - "proxy"
          # Continents how can in website
          continents:
              - "Europe"
              - "North America"
      #            - "South America"
      #            - "Asia"
      # Crawler how can in website will empty to refuse all !!
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
      check_vpn: false
```
## How to use ?
 Well it pretty match all !!

just need to create route & template to `name_route_unauthorized: "Seo_unauthorized"`
````php
    /**
     * @Route("/unauthorized", name="Seo_unauthorized")
     * @param Request $request
     * @return Response
     */
    public function unauthorized(Request $request, CacheItemPoolInterface  $adapter): Response
    {
        $session    = $request->getSession();
        $item       = $adapter->getItem($session->getId());
        $metadata   = $item->getMetadata();
        
        if ( $item && $item->isHit() && array_key_exists('expiry', $metadata)) {
            $expirationTimestamp    = $metadata['expiry'];
            $expirationDateTime     = $date = new DateTime("@$expirationTimestamp");
        };
        
        $Geolocator = $request->getSession()->get('geolocator');
        return $this->render('unauthorized.html.twig', [
            "Geolocator"        => $Geolocator,
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
    
                        <i style="line-height:unset !important;"  class="fa-2xl fi fi-{{ Geolocator.countryCode|lower == 'en' ? 'gb' : Geolocator.countryCode|lower }}"></i>
                        {% if Geolocator.valid %}
                            <h1>{{ 'unauthorized.title'|trans }} </h1>
                            <span><i class="{{ Geolocator.valid ? 'text-success' : 'text-danger' }} fa-solid {{ Geolocator.valid ? 'fa-check-square' : 'fa-window-close'}}"></i> <strong>{{ Geolocator.country }}</strong>, {{ Geolocator.city }}</span>
    
                        {% else %}
                            <h1>{{ 'unauthorized.fail.title'|trans }} </h1>
                            <span><i class="{{ Geolocator.valid ? 'text-success' : 'text-danger' }} fa-solid {{ Geolocator.valid ? 'fa-check-square' : 'fa-window-close'}}"></i> <strong>{{ Geolocator.country }}</strong>, {{ Geolocator.city }}</span>
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
    
                                <div class="col-sm-6 col-lg-4">
                                </div>
                            </div>
                        {% endif %}
                        <small>- {{ timer|format_datetime('short', timezone='Europe/Paris') }} -</small>
                    </div>
    
                </div>   
            </div>
            </div>
        </section>
    {% endblock %}
````

Variable 

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