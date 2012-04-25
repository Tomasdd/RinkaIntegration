<?php

require_once 'RinkaIntegrationConfig.php';
require_once 'RinkaIntegrationAsiBridge.php';
require_once 'RinkaIntegrationTemplate.php';

require_once 'RinkaIntegrationCacheInterface.php';
require_once 'RinkaIntegrationCacheFile.php';

class RinkaIntegrationFactory {

    protected $config = null;


    public function __construct(RinkaIntegrationConfig $config) {
        $this->config = $config;
    }

    /**
     * Creates and returns template object for specified template.
     * Looks for template path in config object.
     *
     * @param string $name	template name without extension
     * @return RinkaIntegrationTemplate
     */
    public function getTemplate($name) {
        return new RinkaIntegrationTemplate(
            $this->config->getTemplatePathByName($name),
            $this
        );
    }

    public function getConfig() {
        return $this->config;
    }

    /**
     * @return RinkaIntegrationAsiBridge
     */
    public function getAsiBridge(RinkaAsi $asi) {
        return new RinkaIntegrationAsiBridge($asi, $this->config);
    }

    /**
     *
     * @param string $module
     * @return RinkaIntegrationCacheInterface
     */
    public function getCache() {
        return new RinkaIntegrationCacheFile($this->config);
    }
}