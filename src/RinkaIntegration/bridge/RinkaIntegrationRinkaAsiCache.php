<?php

class RinkaIntegrationRinkaAsiCache implements RinkaAsiCacheInterface {

    protected $cache;
    protected $expireAfter;

    public function __construct(RinkaIntegrationCacheInterface $cache, $expireAfter = 60) {
        $this->cache = $cache;
        $this->expireAfter = $expireAfter;
    }

    public function load($name) {
        try {
            return $this->cache->load($name);
        } catch (RinkaIntegrationCacheFileException $e) {
            return null;
        }
    }

    public function save($name, $value) {
        $this->cache->save($name, $value, $this->expireAfter);
    }
}