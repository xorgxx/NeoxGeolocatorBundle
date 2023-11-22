<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\EventSubscriber;

use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorFactory;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeoLocatorSubscriber implements EventSubscriberInterface
{
    public function __construct( GeolocatorFactory $geolocatorFactory)
    {
        $this->geolocatorFactory    = $geolocatorFactory;
    }
    
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws GuzzleException
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws InvalidArgumentException
     */
//    public function onKernelRequest(RequestEvent $event): void
//    {
//        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
//            // don't do anything if it's not the master request
//            return;
//        }
//
//        $nameRoute		= $event->getRequest()->get('_route');
//        if (!$this->containsKeyword($nameRoute, ['profile', '_wd'])) {
//            $Geolocator    = $this->geolocatorFactory->getGeolocatorService()->checkAuthorize();
//            if ( $Geolocator !== true && $nameRoute !== "Seo_unauthorized") {
//                $response = new RedirectResponse($Geolocator, 307);
//                $event->setResponse($response);
//            }
//        }
//    }
    
    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        // don't do anything if it's not the master request
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) return;
        
        // This is the main query
        $controller         = $event->getRequest()->attributes->get('_controller');
        $redirectRequired   = $event->getRequest()->server->get('REDIRECT_URL') == "/unauthorized"? true : false;
        
        if ( !$this->isProfilerController($controller) && !$redirectRequired ) {
            $nameRoute		= $event->getRequest()->get('_route');
            if (!$this->containsKeyword($nameRoute, ['profile', '_wd'])) {
                $Geolocator    = $this->geolocatorFactory->getGeolocatorService()->checkAuthorize();
                if ( $Geolocator !== true && $nameRoute !== "Seo_unauthorized") {
                    $response = new RedirectResponse($Geolocator);
                    $event->setController(function () use ($response) {
                        return $response;
                    });
//                    $response = new RedirectResponse($Geolocator, 307);
//                    $event->setResponse($response);
                }
            }
        }
    }
    
    private function isProfilerController($controller): bool
    {
        return str_starts_with($controller, 'web_profiler.controller.profiler::');
    }
    
    private function containsKeyword($haystack, array $keywords)
    {
        foreach ($keywords as $keyword) {
            if (strpos($haystack, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
//            KernelEvents::REQUEST               => ['onKernelRequest', 1],
            KernelEvents::CONTROLLER_ARGUMENTS  => 'onKernelController',
        ];
    }
}