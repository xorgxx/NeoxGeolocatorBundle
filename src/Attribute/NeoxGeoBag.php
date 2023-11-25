<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Attribute;
    
    use Attribute;
    use NeoxGeolocator\NeoxGeolocatorBundle\Model\neoxBag;
    
    /**
     *  https://www.geeksforgeeks.org/html-meta-tag/
     *
     *  This attribute is used to define the name of the property.
     *  name        : <meta name="keywords" content="Meta Tags, Metadata" />
     *
     *  This attribute is used to get the HTTP response message header.
     *  http-equiv  : <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
     *
     *  This attribute is used to specify properties value.
     *  content     : <meta content="width=device-width, initial-scale=1, maximum-scale=1">
     *
     *  This attribute is used to specify a character encoding for an HTML file.
     *  charset     : <meta charset="character_set">
     *
     *  Determines a scheme to be utilized to decipher the value of the substance attribute.
     *  scheme: <meta name="keywords" content="Meta Tags, Metadata" scheme="ISBN" />
     *
     *
     *      <html {{ neox_seo_html_attributes() }}>
     *      <head {{ neox_seo_head_attributes() }}>
     *          {{ neox_seo_title() }}
     *          {{ neox_seo_metadatas() }}
     *          {{ neox_seo_link_canonical() }}
     *          {{ neox_seo_lang_alternates() }}
     */
    
    #[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
    class NeoxGeoBag extends neoxBag
    {
        public function __construct(
            public ?string      $ip_local_dev               = null,
            public ?array       $cdn                        = [],
            public ?array       $filterLocal                = [],
            public ?array       $filterConnection           = [],
            public ?array       $filterContinents           = [],
            public ?array       $crawler                    = [],
            public ?string      $name_route_unauthorized    = null,
            public ?int         $timer                      = null,
            public ?string      $check_vpn                  = null,
        ) {}
    }