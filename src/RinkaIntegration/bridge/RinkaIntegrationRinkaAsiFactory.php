<?php

class RinkaIntegrationRinkaAsiFactory extends RinkaAsiFactory {

    /**
     * @var RinkaAsiCacheInterface
     */
    protected $Cache;

    public function __construct(RinkaAsiCacheInterface $Cache) {
        $this->Cache = $Cache;
    }

    public function getCacheObject() {
        return $this->Cache;
    }
}