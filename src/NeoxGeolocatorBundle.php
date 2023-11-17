<?php

namespace NeoxGeolocator\NeoxGeolocatorBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NeoxGeolocatorBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}