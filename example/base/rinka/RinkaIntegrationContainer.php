<?php

class RinkaIntegrationContainer {
    protected $domainCityRelation = array();
    protected $underConstruction = false;
    protected $adminEmail = 'rinka.dev@evp.lt';
    protected $showErrors = false;
    protected $errorString = 'Atsiprašome, skelbimų sistema laikinai neveikia';
    protected $config = null;

    public function __construct() {
        $configuration = include dirname(__FILE__) . '/configuration.php';
        if (!$configuration) {
            $this->underConstruction = true;
        } else {
            if (isset($configuration['domainCityRelation'])) {
                $this->domainCityRelation = $configuration['domainCityRelation'];
            }
            if (isset($configuration['adminEmail'])) {
                $this->adminEmail = $configuration['adminEmail'];
            }
            if (isset($configuration['showErrors'])) {
                $this->showErrors = $configuration['showErrors'];
            }
            if (isset($configuration['errorString'])) {
                $this->errorString = $configuration['errorString'];
            }
            if (isset($configuration['config'])) {
                $this->config = $configuration['config'];
            }
            if (isset($configuration['underConstruction'])) {
                $this->underConstruction = $configuration['underConstruction'];
            }
        }
    }

    protected function getIntegrationObject() {
        if (!class_exists('RinkaIntegration')) {
            include $this->config['RinkaIntegration']['paths']['rinka_integration'] . '/RinkaIntegration.php';
        }
        if (!class_exists('RinkaIntegration')) {
            throw new Exception('RinkaIntegration class not found');
        }
        return RinkaIntegration::createFromConfig($this->config);
    }

    protected function getDefaultFilter() {
        if (!class_exists('RinkaIntegration')) {
            include $this->config['RinkaIntegration']['paths']['rinka_integration'] . '/RinkaIntegration.php';
        }
        if (!class_exists('RinkaIntegration')) {
            throw new Exception('RinkaIntegration class not found');
        }

        $filter = new RinkaAsiFilter();

        $domain = str_replace('www.', '', $_SERVER['SERVER_NAME']);
        if (isset($this->domainCityRelation[$domain])) {
            if (!is_array($this->domainCityRelation[$domain])) {
                $cities = array($this->domainCityRelation[$domain]);
            } else {
                $cities = $this->domainCityRelation[$domain];
            }
            foreach ($cities as $city) {
                try {
                    $filter->addCity(array('lietuva', $city));
                } catch (RinkaAsiException $E) {
                    // do nothing
                }
            }
        }

        $filter->setOrders(array(
            RinkaAsiFilter::ORDER_BY_SITE  => $domain,
            RinkaAsiFilter::ORDER_BY_DATE  => RinkaAsiFilter::ORDER_DESC,
        ));

        return $filter;
    }

    protected function getErrorText() {
        return $this->errorString;
    }

    protected function error($e) {
        if ($this->showErrors) {
            return (string) $e;
        } else {
            if ($this->adminEmail !== null) {
                mail($this->adminEmail, 'Klaida Rinka skelbimų įskiepyje', (string) $e . "\n\n\n" . print_r(array(
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'SERVER' => $_SERVER,
                ), true));
            }

            header('Location: /');
            exit;
        }
    }

    protected function isUnderConstruction() {
        return $this->underConstruction;
    }

    public function getAnnouncementBox() {
        if ($this->isUnderConstruction()) {
            return $this->getErrorText();
        }

        try {
            return $this->getIntegrationObject()->getAnnouncementsBox($this->getDefaultFilter());
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    public function announcementsList() {
        if ($this->isUnderConstruction()) {
            return $this->getErrorText();
        }

        try {
            return $this->getIntegrationObject()->getAnnouncementsList($this->getRequestParams(), $this->getDefaultFilter());
        } catch (Exception $e) {
            return $this->error( $e);
        }
    }

    public function insert() {
        if ($this->isUnderConstruction()) {
            return $this->getErrorText();
        }

        $city = $this->getDefaultFilter()->getCity();
        if ($city === null) {
            $city = '';
        } else {
            $city = implode('/', $city);
        }

        try {
            return $this->getIntegrationObject()->handleInsert(array_merge(array('city' => $city), $this->getRequestParams()));
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    protected function getRequestParams() {
        $get = array();
        parse_str($_SERVER['QUERY_STRING'], $get);  // a little hack for systems, where $_GET is emptied beforehand
        return $get;
    }
}

