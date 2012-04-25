<?php

class RinkaIntegrationTemplate {

    /**
     *
     * @var string
     */
    protected $pathToFile = null;

    /**
     *
     * @var RinkaIntegrationConfig
     */
    protected $config = null;

    /**
     *
     * @var RinkaIntegrationFactory
     */
    protected $factory = null;


    /**
     * Creates object for specified template by its path.
     *
     * @param string $pathToFile
     */
    public function __construct($pathToFile, RinkaIntegrationFactory $factory) {
        $this->pathToFile = $pathToFile;
        $this->config     = $factory->getConfig();
        $this->factory    = $factory;
    }

    /**
     * Renders this template with specified data and returns result in HTML or any other format
     * in template.
     *
     * @param array $data
     * @return string
     */
    public function render($templateVars = array()) {
        if (!file_exists($this->pathToFile . '.php')) {
            throw new RinkaIntegrationException('Can not find template file: ' . $this->pathToFile . '.php');
        }

        extract($templateVars, EXTR_SKIP);

        ob_start();
        include $this->pathToFile . '.php';
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    public function element($templateName, $templateVars = array()) {
       return $this->factory->getTemplate('elements/' . $templateName)->render($templateVars);
    }

    public function write($value, $glue = null) {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = $this->escape($item);
            }
            echo implode($glue, $value);
        } else {
            echo $this->escape($value);
        }
    }

    public function escape($value) {
        return htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
    }

    public function writeHiddenQueryParameters($getData, $skipKeys = array()) {
        foreach ($getData as $key => $value) {
            if (!in_array($key, $skipKeys)) {
                echo '<input type="hidden" name="', $this->escape($key), '" value="', $this->escape($value), '" />';
            }
        }
    }

}