<?php

class RinkaIntegrationCacheFile implements RinkaIntegrationCacheInterface {

    /**
     * @var RinkaIntegrationConfig
     */
    protected $config = null;

    /**
     * @var array
     */
    protected $cache = array();

    public function __construct($config) {
        $this->config = $config;
    }

    public function save($key, $value, $expire = null) {
        $expire = isset($expire) ? $expire : $this->config->getConfigValue(array('cache', 'defaultExpire'));

        return $this->saveInfo($key, array(
            'expire' => time() + $expire,
            'content' => $value,
        ));
    }

    public function load($key, $force = false) {
        $info = $this->loadInfo($key, $force);
        if ($info === null) {
            return null;
        } else {
            return $info['content'];
        }
    }

    public function delayExpire($key, $timeInSeconds) {
        $info = $this->loadInfo($key, true);
        if ($info === null) {
            return $this->saveInfo($key, array(
                'expire' => time() + $timeInSeconds,
                'content' => null,
                'cache_lock' => true,
            ));
        } else {
            $info['expire'] = time() + $timeInSeconds;
            return $this->saveInfo($key, $info);
        }
    }

    protected function getFilename($key) {
        return $this->config->getPath('cache') . DIRECTORY_SEPARATOR . 'n' . md5($key) . '.php';
    }

    protected function saveInfo($key, $info) {
        $this->cache[$key] = $info;
        return file_put_contents(
            $this->getFilename($key),
            "<?php return unserialize('" . strtr(serialize($info), array("\\" => "\\\\", "'" => "\\'")) . "');"
        ) !== false;
    }

    protected function loadFromFile($key) {
        $cacheFile = $this->getFilename($key);
        if (is_file($cacheFile)) {
            return include $cacheFile;
        } else {
            return null;
        }
    }

    protected function loadInfo($key, $force) {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        } else {
            $cache = $this->loadFromFile($key);
            if (isset($cache['cache_lock']) && $cache['cache_lock']) {
                throw new RinkaIntegrationCacheFileException('Cache is currently being generated');
            }
            $this->cache[$key] = $cache;
        }
        if ($this->isValid($cache, $force)) {
            return $cache;
        } else {
            return null;
        }
    }

    protected function isValid($cache, $force) {
        if ($cache === null || !isset($cache['expire']) || !array_key_exists('content', $cache)) {
            return false;
        } elseif ($force) {
            return true;
        } else {
            return time() < $cache['expire'];
        }
    }
}


class RinkaIntegrationCacheFileException extends Exception {

}
