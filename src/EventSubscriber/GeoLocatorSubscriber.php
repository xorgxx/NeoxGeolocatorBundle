<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle\EventSubscriber;

use NeoxGeolocator\NeoxGeolocatorBundle\Pattern\GeolocatorFactory;
use ReflectionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class GeoLocatorSubscriber implements EventSubscriberInterface
{
    public function __construct( GeolocatorFactory $geolocatorFactory )
    {
        $this->geolocatorFactory    = $geolocatorFactory;
    }
    
    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ReflectionException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $test = $event->getRequest()->server->get("HTTP_USER_AGENT");
        
        list($request, $controller, $redirectRequired, $nameRoute) = $this->handleRequest($event);
        
        // Early return if it's not the master request | for DEV mode return if the controller is a profiler controller or a redirect is required
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() ||
            $this->isProfilerController($controller) ||
            $redirectRequired ||
            $test === "Symfony BrowserKit") 
        {
            return;
        }
        
        // Early return if the controller is a profiler controller or a redirect is required
        if ($this->isProfilerController($controller) || $redirectRequired || $this->isRouteNameExclude($nameRoute)) {
            return;
        }
        
        if (!$this->geolocatorFactory->getGeolocatorService()->checkIpPing()) {
            throw new TooManyRequestsHttpException(3600,'Too Many request. |-> BANNIS.');
        }
    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        $test = $event->getRequest()->server->get("HTTP_USER_AGENT");
        
        list($request, $controller, $redirectRequired, $nameRoute) = $this->handleRequest($event);
        
        // Early return if it's not the master request | return if the controller is a profiler controller or a redirect is required
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType() ||
            $this->isProfilerController($controller) ||
            $this->isRouteNameExclude($nameRoute) ||
            $redirectRequired ||
            $test === "Symfony BrowserKit")
        {
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
    
    private function isRouteNameExclude($CurrentNameRoute): bool
    {
        $nameRouteExclude       = $this->geolocatorFactory->getGeolocatorService()->getNeoxBag()->getNameRouteExclude() ?? [];
        $filteredGeoData        = count(array_intersect([$CurrentNameRoute], $nameRouteExclude)) > 0;
        return $filteredGeoData;
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