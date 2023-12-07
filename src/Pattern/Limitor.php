<?php
    
    namespace NeoxGeolocator\NeoxGeolocatorBundle\Pattern;
    
    use Symfony\Contracts\Cache\ItemInterface;
    
    trait Limitor
    {
        protected function getLimiter(string $limiterName, int $expire = 60): bool
        {
            $limiterKey     = self::COUNTNAME . $limiterName;
            $limiterValue   = $this->incrementLimiterValue($limiterKey, $expire);
            
            if ($limiterValue < 43) {
                $limiterValue = $this->updateLimiterValue($limiterKey, $limiterValue);
                return true;
            }
            
            return false;
        }
        
        protected function getIpPing(string $limiterName, int $expire = 10): bool
        {
            $checkPing = $this->neoxBag->getCheckPing();
            if ( $checkPing["on"] ) {
                $expire = $checkPing["expire"];
                $ping   = $checkPing["ping"];
                $bannis = $checkPing["banni"];
                
                $limiterKey     = self::COUNTNAME . $limiterName;
                $limiterValue   = $this->incrementLimiterValue($limiterKey, $expire);
                
                if ( $limiterValue > $ping ) {
                    $this->deleteCounterCache($limiterKey);
                    $expire = $bannis;
                    $this->cache->get($limiterKey, function (ItemInterface $item) use($expire) {
                        $item->expiresAfter($expire);
                        return 20;
                    });
                    return false;
                }
                $limiterValue = $this->updateLimiterValue($limiterKey, $limiterValue);
            }
            return true;
        }
        
        protected function incrementLimiterValue($limiterKey, $expire)
        {
            return $this->cache->get($limiterKey, function (ItemInterface $item) use($expire) {
                    $item->expiresAfter($expire);
                    return 0;
                }) + 1;
        }
        
        private function updateLimiterValue($limiterKey, $limiterValue)
        {
            $expire = $this->getLimiterExpiry($limiterKey);
            $this->deleteCounterCache($limiterKey);
            return $this->resetLimiterValue($limiterKey, $limiterValue, $expire);
        }
        
        private function getLimiterExpiry($limiterKey)
        {
            $item = $this->cache->getItem($limiterKey);
            return $item->getMetadata()['expiry'];
        }
        
        private function deleteCounterCache($limiterKey)
        {
            $this->cache->delete($limiterKey);
        }
        
        private function resetLimiterValue($limiterKey, $limiterValue, $expire)
        {
            return $this->cache->get($limiterKey, function (ItemInterface $item) use ($expire, $limiterValue) {
                $expireDateTime = new \DateTime("@$expire", new \DateTimeZone("Europe/Paris"));
                $item->expiresAt($expireDateTime);
                return $limiterValue;
            });
        }
    }