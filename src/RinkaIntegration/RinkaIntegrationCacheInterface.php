<?php

interface RinkaIntegrationCacheInterface {
    public function save($key, $value, $expire = null);
    public function load($key, $force = false);
    public function delayExpire($cacheKey, $timeInSeconds);
}

