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
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }
        
        $nameRoute		= $event->getRequest()->get('_route');
        if (!$this->containsKeyword($nameRoute, ['profile', '_wd'])) {
            if (!$this->geolocatorFactory->getGeolocatorService()->checkIpPing()) {
                throw new TooManyRequestsHttpException(3600,'Too Many request. |-> BANNIS.');
            }
        }

    }

    public function onKernelController(ControllerArgumentsEvent $event): void
    {
        // Early return if it's not the master request
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }
        
        $request            = $event->getRequest();
        $controller         = $request->attributes?->get('_controller');
        $redirectRequired   = $request->server?->get('REDIRECT_URL') === "/unauthorized";
        
        // Early return if the controller is a profiler controller or a redirect is required
        if ($this->isProfilerController($controller) || $redirectRequired) {
            return;
        }
        
        $nameRoute          = $request->get('_route');
        $excludedRoutes     = ['profile', '_wd'];
        
        // Early return if the nameRoute doesn't contain any of the excluded keywords
        if ($this->containsKeyword($nameRoute, $excludedRoutes)) {
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
    
    private function containsKeyword($haystack, array $keywords): bool
    {
        return array_reduce($keywords, static function (bool $carry, string $keyword) use ($haystack): bool {
                return $carry || strpos($haystack, $keyword) !== false; // Check if keyword in string
            }, false) && array_reduce($keywords, static function (bool $carry, string $keyword) use ($haystack): bool {
                return $carry || strpos($haystack, $keyword) === 0; // Check if keyword at start of string
            }, false);
        
//        return array_reduce($keywords, static function (bool $carry, string $keyword) use ($haystack): bool {
//            return $carry || stripos($haystack, $keyword) !== false;
//        }, false);
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST               => ['onKernelRequest', 1],
            KernelEvents::CONTROLLER_ARGUMENTS  => 'onKernelController',
        ];
    }
}