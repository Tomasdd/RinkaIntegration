<?php

class RinkaIntegrationConfig {

    private $params = array();

    /**
     * Possible keys:
     *          array   templates               Template paths for exceptions.
     *
     *          array   urls                    List of url addresses. Every other key besides base
     *                                          must be a relative path (without the base value).
     *                                          Possible keys:
     *                                              `base`,
     *                                              `announcementList`,
     *                                              `insertAnnouncement`,
     *                                              `imageUpload`,
     *                                              `assets`.
     *
     *          array   paths                   List of pathes. Every other key besides base must be
     *                                          a relative path (without the base value).
     *                                          Possible keys:
     *                                              `base`,
     *                                              `templates`,
     *                                              `imageUpload`.
     *
     *          array   image                   Image settings. Possible (usable) keys:
     *                                              `allowedTypes` An array which syntax is mime => extension, e.g. 'image/png' => 'png'.
     *                                              `maxSize`      Max size of upload image (bytes).
     *
     *          array   cache                   Cache settings. Possible (usable) keys:
     *                                              `defaultExpire`
     *
     *          string  sourceBase [Optional]   This should be your domain name (usually).
     *                                          Later, you can order to show ads added from
     *                                          your website at the top.
     *
     *          int     descriptionLengthBox    Description length in an announcement box.
     *          mixed   availableCities         Cities, that will be available in system.
     *                                              type:
     *                                                  * array of city names or array of 2-valued arrays
     *                                                  * null - all available cities will be displayed
     *                                              example:
     *                                                  array(array('lietuva', 'vilnius'), array('latvija', 'riga'))
     *
     * @param array $params
     */
    public function __construct(array $params = array()) {
        $defaults = array(
            'locationServiceUri'      => 'http://rinka.lt/loc/',
            'templates'               => array(),

            'urls' => array(
                'base'               => dirname('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["SCRIPT_NAME"]),

                'announcementList'   => 'announcement_list.php',
                'insertAnnouncement' => 'insert_announcement.php',
                'imageUpload'        => 'tmp/upload/images',
                'assets'             => 'RinkaIntegration/assets',
            ),

            'paths' => array(
                'base'        => dirname(dirname(__FILE__)),

                'templates'   => 'RinkaIntegration/templates',
                'cache'       => 'tmp/cache',
                'imageUpload' => 'tmp/upload/images',
            ),

            'image' => array(
                'allowedTypes' => array(
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/jpeg' => 'jpg',
                    'image/bmp'  => 'bmp',
                ),
                'maxSize'      => 5242880,  // 5 * 1024 * 1024 = 5MB
            ),

            'cache' => array(
                'defaultExpire' => 60, //seconds
            ),


            'sourceBase'              => str_replace('www.', '', $_SERVER['SERVER_NAME']),
            'descriptionLengthBox'    => 150,

        	'availableCities'         => null,        // null -> all cities are available
        );

        $this->params = $this->arrayMergeRecursively($defaults, $params);
    }

    /**
     * Works like array_merge, just overwrites properly when merging configuration values.
     *
     * @param array $Arr1
     * @param array $Arr2
     * @return array
     */
    private function arrayMergeRecursively($arr1, $arr2) {
        if (!is_array($arr1)) {
            return $arr1;
        }
        foreach ($arr2 as $key => $value) {
            if (array_key_exists($key, $arr1) && !$this->isSolidConfigValue($value)) {
                $arr1[$key] = $this->arrayMergeRecursively($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }

    /**
     * Returns whether specified value is final config value or just grouping of cinfig values.
     * Grouping of values is associative array with non-numeric keys. If all keys in array are numeric,
     * this means, that this is solid value. If value is not an array, it is solid value too.
     *
     * @param mixed $value
     *
     * @return boolean
     */
    private function isSolidConfigValue($value) {
        if (is_array($value)) {
            foreach (array_keys($value) as $key) {
                if (!is_numeric($key)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Looks for defined path for specific template or makes path by default templates location.
     *
     * @param string $templateName
     * @return string Full path to template file without an extension
     */
    public function getTemplatePathByName($templateName) {
        $template = $this->getConfigValue(array('templates', $templateName));
        return (isset($template))
            ? $template . $templateName
            : $this->getPath('templates') . DIRECTORY_SEPARATOR . $templateName;
    }

    /**
     * Overrides specific template to use specified path instead of default templates location.
     *
     * @param string $templateName
     * @param string $templatePath
     */
    public function setTemplatePath($templateName, $templatePath) {
        $this->params['templates'][$templateName] = $templatePath;
    }

    /**
     * Returns a config value.
     *
     * @param array $key    Example: array('imageAllowedTypes', 'image/png')
     * @return mixed
     */
    public function getConfigValue(array $key) {
        if (empty($key)) {
            return null;
        }

        $params = $this->params;
        foreach ($key as $value) {
            if (empty($params[$value])) {
                return null;
            }
            $params = $params[$value];
        }

        return $params;
    }


    /**
     * Returns fully formed url, e.g. http://domain.tld/file.php.
     *
     * @param string $key
     * @param array  $param Additional params
     * @return string
     */
    public function getUrl($key, $param = array()) {
        if ($key == 'base') {
            $url = $this->getConfigValue(array('urls', 'base'));
        } else {
            $url = $this->getConfigValue(array('urls', 'base')) . '/'
                . $this->getConfigValue(array('urls', $key));
        }
        $url = parse_url($url);
        if (count($param) > 0) {
            if (isset($url['query'])) {
                parse_str($url['query'], $parsedQuery);
            } else {
                $parsedQuery = array();
            }
            $url['query'] = http_build_query(array_merge($parsedQuery, $param), null, '&');
        }

        if (!isset($url['path'])) {
            $url['path'] = '';
        }

        return $url['scheme'] . '://' . $url['host'] . $url['path'] . (isset($url['query']) ? '?' . $url['query'] : '');
    }

    /**
     * Returns fully formed path, e.g. /home/user/www/assets/images/upload/.
     *
     * @param string $key
     * @return string
     */
    public function getPath($key, $param = array()) {
        return $this->getConfigValue(array('paths', 'base')) . DIRECTORY_SEPARATOR
                . $this->getConfigValue(array('paths', $key));
    }

    public function getAvailableCityList() {
        return $this->getConfigValue(array('availableCities'));
    }

}
