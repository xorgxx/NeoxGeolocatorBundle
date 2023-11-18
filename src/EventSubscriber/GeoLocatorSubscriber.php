<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\EventSubscriber;

use NeoxGeolocator\NeoxGeolocatorBundle\Services\GeolocalisatorService;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeoLocatorSubscriber implements EventSubscriberInterface
{
    /**
     * @var GeolocalisatorService
     */
    private GeolocalisatorService $geolocalisator;

    /**
     * @param GeolocalisatorService $geolocator
     */
    public function __construct(GeolocalisatorService $geolocator)
    {
        $this->geolocalisator = $geolocator;
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
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        
        $nameRoute		= $event->getRequest()->get('_route');
        $UnAuthorize    = $this->geolocalisator->checkAuthorize();
        if ($UnAuthorize && $nameRoute !== "Seo_unauthorized") {
            $response = new RedirectResponse($UnAuthorize, 307);
            $event->setResponse($response);
        }

    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1],
        ];
    }
}