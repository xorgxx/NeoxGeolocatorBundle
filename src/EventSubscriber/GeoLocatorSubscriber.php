<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\EventSubscriber;

use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorFactory;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class GeoLocatorSubscriber implements EventSubscriberInterface
{
    public function __construct( GeolocatorFactory $geolocatorFactory )
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
    public function onKernelRequest(RequestEvent $event): void
    {
        
        list($request, $controller, $redirectRequired, $nameRoute) = $this->handleRequest($event);
        
        // Early return if it's not the master request | return if the controller is a profiler controller or a redirect is required
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || $this->isProfilerController($controller) || $redirectRequired) {
            return;
        }
        
        // Early return if the controller is a profiler controller or a redirect is required
        if ($this->isProfilerController($controller) || $redirectRequired) {
            return;
        }
        
        if (!$this->geolocatorFactory->getGeolocatorService()->checkIpPing()) {
            throw new TooManyRequestsHttpException(3600,'Too Many request. |-> BANNIS.');
        }
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        
        list($request, $controller, $redirectRequired, $nameRoute) = $this->handleRequest($event);
        
        // Early return if it's not the master request | return if the controller is a profiler controller or a redirect is required
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() || $this->isProfilerController($controller) || $redirectRequired) {
            return;
        }

        $geolocator         = $this->geolocatorFactory->getGeolocatorService()->checkAuthorize();
        
        // Early return if no redirection is required
        if ($geolocator === true || $nameRoute === "Seo_unauthorized") {
            return;
        }
        
        $response           = new RedirectResponse($geolocator);
        $event->setController(fn() => $response);
    }
    
    private function isProfilerController($controller): bool
    {
        return str_starts_with($controller, 'web_profiler.controller.profiler::');
    }
    
    /**
     * @param ControllerArgumentsEvent | RequestEvent $event
     *
     * @return array
     */
    public function handleRequest(RequestEvent|ControllerArgumentsEvent $event): array
    {
        $request            = $event->getRequest();
        $nameRoute          = $request->get('_route');
        $controller         = $request->attributes?->get('_controller');
        $redirectRequired   = $request->server?->get('REDIRECT_URL') === "/unauthorized";
        
        return array($request, $controller, $redirectRequired, $nameRoute);
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST               => ['onKernelRequest', 1],
            KernelEvents::CONTROLLER_ARGUMENTS  => 'onKernelController',
        ];
    }
}