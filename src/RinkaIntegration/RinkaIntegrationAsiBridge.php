<?php
/**
 * Connects integration site with RinkaAsi library
 */
class RinkaIntegrationAsiBridge {

    /**
     *
     * @var RinkaAsi
     */
    protected $asi = null;

    /**
     *
     * @var RinkaIntegrationConfig
     */
    protected $config = null;

    /**
     *
     * @var array
     */
    public $validationErrors = array();


    /**
     *
     * @param RinkaAsi $asi
     */
    public function __construct(RinkaAsi $asi, RinkaIntegrationConfig $config) {
        $this->asi    = $asi;
        $this->config = $config;
    }

    /**
     * Returns data for announcement insertion template by specified category and optionally
     * values to be filled in input fields.
     *
     * @param array $categoryPath
     * @param array $values [Optional]
     * @return array
     */
    public function getInsertForm(array $categoryPath, array $values = array()) {
        $Category = $this->asi->getCategories()->getCategory($categoryPath);

        $locationList         = $Category->getCityList();
        $locationDescriptions = $Category->getLocationDescriptions();

        return array(
            'fields'  => $Category->getAllFields(),
            'values'  => array(),
            'contacts' => array(
                'location' => $this->getLocations(),
            ),
        );
    }

    /**
     *
     * @return array
     */
    public function getLocations() {
        $Category = $this->asi->getCategories();

        $locationList         = $Category->getCityList();
        $locationDescriptions = $Category->getLocationDescriptions();

        $cityList = $this->config->getAvailableCityList();
        if ($cityList !== null) {
            $newLocations = array();
            foreach ($locationList as $country => $citiesInCountry) {
                foreach ($citiesInCountry as $name => $key) {
                    if (in_array($name, $cityList) || in_array(array($country, $name), $cityList)) {
                        $newLocations[$country][$name] = $key;
                    }
                }
            }
            return array(
                'list'              => $newLocations,
                'descriptions'      => $locationDescriptions,
            );
        } else {
            return array(
                'list'              => $locationList,
                'descriptions'      => $locationDescriptions,
            );
        }
    }

    /**
     * Returns data for category tree template. Links in tree point to $url with query parameter
     * $categoryKeyInUrl set to category_key/path/with/slashes
     *
     * @param string $url
     * @param string $category                  perka/automobiliai
     * @param string $onlyLeaf
     * @return array
     */
    public function getCategoryTree($url, $category = '', $onlyLeaf = true) {
        $categories = $this->asi->getCategoryTree();
        $tree = $this->makeTree(
            $url,
            $categories,
            array(
                'selected' => $category,
                'onlyLeaf' => $onlyLeaf,
            )
        );
        return $tree['children'];
    }

    public function getCategoryTitles(array $categoryAsArray) {
        $Category = $this->asi->getCategories();
        $titles = array();
        foreach ($categoryAsArray as $categoryKey) {
            try {
                $Category = $Category->getCategory($categoryKey);
                $titles[] = $Category->getDescription();
            } catch (RinkaAsiException $e) {
                return $titles;
            }
        }
        return $titles;
    }

    /**
     * Returns category tree
     *
     * @param string $url
     * @param RinkaAsiCategoryTree $CategoryTree
     * @param string $prefix
     * @param array $options
     * @return array
     */
    private function makeTree($url, RinkaAsiCategoryTree $CategoryTree, $options = array()) {
        $defaults = array(
            'onlyLeaf' => true,
            'selected' => false,
        );
        $options = array_merge($defaults, $options);

        $description = $CategoryTree->getDescription();
        if ($description === null) {
            $description = 'Visi';
        }
        $category = $CategoryTree->getRelation();

        $data = array(
            'title' => $description,
            'selected' => ($category && $category == $options['selected']),
            'current' => ($category && $category == $options['selected']),
        );
        $categoryList = $CategoryTree->getChildren();
        if (!empty($categoryList)) {
            $children = array();
            $order = false;
            foreach ($categoryList as $SubCategory) {
                $child = $this->makeTree(
                    $url, $SubCategory, $options
                );
                if (empty($child['children']) && empty($child['link'])) {
                    continue;
                }
                $child['no'] = count($children);
                $children[] = $child;
                if ($child['selected']) {
                    $data['selected'] = true;
                }
                if (isset($child['link'])) {
                    $order = true;
                }
            }
            if (count($children) > 0) {
                usort($children, array($this, 'sortTreeBySelected'));
                $data['children'] = $children;
            }
        }

        if ((!$options['onlyLeaf'] || empty($data['children'])) && $category !== null) {
            $url = parse_url($url);

            $parsedQuery = array();
            if (!empty($url['query'])) {
                parse_str($url['query'], $parsedQuery);
            }

            $parsedQuery['category'] = $category;
            unset($parsedQuery['page']); // unset the page param, keep the limit and other
            $url['query'] = http_build_query($parsedQuery, null, '&');
            $data['link'] = $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . $url['query'];
        }

        return $data;
    }

    private function sortTreeBySelected($a, $b) {
        $result = (isset($b['selected']) && $b['selected'] ? 1 : 0) - (isset($a['selected']) && $a['selected'] ? 1 : 0);
        if ($result === 0) {
            return $a['no'] < $b['no'] ? -1 : 1;
        } else {
            return $result;
        }
    }

    /**
     * Inserts announcement using RinkaAsi.
     *
     * @param string $category          Contains category path
     * @param array  $postData		All data posted by insert form
     * @param array  $images     	Image list
     * @throws RinkaIntegrationException on error
     *
     * @return boolean
     */
    public function insertAnnouncement($category, array $postData, array $images) {
        try {
            $categories = $this->asi->getCategories();
            $category = $categories->getCategory(explode('/', $category));
            $exportDocument = $this->asi->createExportDocument();

            $entry = $exportDocument->createNewInsertEntry($category);
            $entry->setLocalId(microtime(true));
            $entry->setUserIp($_SERVER['REMOTE_ADDR']);       
            
            if (!empty($postData['nuomojama_nuo'])) {
                try {
                    $nuomojamaNuo = new DateTime($postData['nuomojama_nuo']);
                    $nuomojamaNuo = $nuomojamaNuo->format('Y-m-d');
                } catch (Exception $e) {
                    $this->validationErrors[] = 'nuomojama_nuo';
                }
            }
            
            foreach ($postData['phone_numbers'] as $key => $phoneNumber) {
                try {
                    $phoneNumber = preg_replace('/[^\d]+/', '', $phoneNumber);

                    if (empty($phoneNumber)) {
                        $this->validationErrors[] = 'phone_numbers['.$key.']';
                    }

                    $entry->addContact('telefonas', $phoneNumber);
                } catch (RinkaAsiException $e) {
                    $this->validationErrors[] = 'phone_numbers['.$key.']';
                }
            }

            try {
                $entry->addContact('el_pastas', $postData['email']);
            } catch (RinkaAsiException $e) {
                $this->validationErrors[] = 'email';
            }

            try {
                $entry->addContact('miestas', explode('/', $postData['city']));
            } catch (RinkaAsiException $e) {
                $this->validationErrors[] = 'city';
            }

            if (!empty($postData['publish_date'])) {
                try {
                    $publishFrom = new DateTime($postData['publish_date']);

                    $entry->setPublishDate($publishFrom->format('Y-m-d 00:00:00'));
                } catch (Exception $e) {
                    $this->validationErrors[] = 'publish_date';
                }
            }

            if (!empty($postData['publish_until'])) {
                try {
                    $publishUntil = new DateTime($postData['publish_until']);

                    if ($publishFrom >= $publishUntil) {
                        throw new Exception('');
                    }

                    $entry->setPublishUntil($publishUntil->format('Y-m-d 00:00:00'));
                } catch (Exception $e) {
                    $this->validationErrors[] = 'publish_until';
                }
            }

            $entry->setSourceBase($this->config->getConfigValue(array('sourceBase')));

            unset($postData['phone_numbers']);
            unset($postData['city']);
            unset($postData['email']);
            unset($postData['country']);
            unset($postData['city']);
            unset($postData['submit']);
            unset($postData['publish_date']);
            unset($postData['publish_until']);

            // validate multiselect
            $fields = $category->getAllFields();
            foreach ($fields as $fieldName => $field) {
                if ($field->isRequired() && empty($postData[$fieldName])) {
                    $this->validationErrors[] = $fieldName;
                }
            }

            foreach ($postData as $fieldName => $fieldValue) {                
                $addFieldIndex = false;
                $value = $fieldValue;

                $fieldInfo = $category->getField($fieldName);
                $valueTypes = $fieldInfo->getValueTypes();

                if (count($valueTypes) > 1) {
                    $addFieldIndex = true;
                    $hasSelect = false;
                    $hasAFieldNotSelectFilled = false;
                    $hasOtherFieldsBesidesSelect = false;

                    $i = 0;
                    foreach ($valueTypes as $ValueType) {
                        if ($ValueType->getTypeName() == 'select') {
                            $hasSelect = true;
                        } else {
                            $hasOtherFieldsBesidesSelect = true;
                            if (!empty($value[$i])) {
                                $hasAFieldNotSelectFilled = true;
                            }
                        }

                        if ($ValueType->getTypeName() == 'checkbox') {
                            $value[$i] = (bool) $value[$i];
                        }
                        $i++;
                    }

                    if (
                           (($hasSelect && !$hasAFieldNotSelectFilled && $hasOtherFieldsBesidesSelect)
                        || (!$hasAFieldNotSelectFilled && $hasOtherFieldsBesidesSelect))
                        && !$ValueType->isRequired()
                    ) {
                        continue;
                    }

                } else {
                    $fieldInfo = $category->getField($fieldName);
                    $valueType = current($fieldInfo->getValueTypes());

                    if (empty($value) && !$valueType->isRequired()) {
                        continue;
                    }

                    $ValueType = reset($valueTypes);
                    switch ($ValueType->getTypeName()) {
                        case 'checkbox':
                            $value = array((bool) $value);
                            break;
                        case 'city_list':
                            unset($value['tmp']);
                            $_value = array();

                            $value['custom'] = (!isset($value['custom']) || (empty($value['custom']) && !is_array($value['custom'])))
                                ? array()
                                : $value['custom'];

                            foreach ($value as $k => $v) {
                                if ($k == 'custom' || empty($v)) {
                                    continue;
                                }
                                $type = substr($k, 5);

                                $_value[] = array(
                                    'custom' => (in_array($type, $value['custom']) ? 'user' : 'none'),
                                    'type'   => $type,
                                    'value'  => $v,
                                );
                            }

                            $value = array($_value);
                            break;                        
                        default:
                            $value = array($value);
                            break;
                    }
                }

                try {
                    $entry->setFieldValueArray($fieldName, $value);
                } catch (RinkaAsiException $e) {
                    $this->validationErrors[] = $fieldName . ($addFieldIndex ? '[0]' : '');
                }
            }

            try {
                $this->validateImages($images);
            } catch (RinkaIntegrationException $e) {
                $this->validationErrors[] = 'images';
            }

            if (!empty($this->validationErrors)) {
                return false;
            }

            $_images = $this->saveImages($images);
            foreach ($_images as $image) {
                $entry->addImage($image['urlPath']);
            }


            $response = $this->tryToSubmitExportDocument($exportDocument);
            $this->deleteSavedImages($_images);
            return $response['summary'];
        } catch(RinkaAsiException $e) {
            $this->deleteSavedImages($_images);
            throw new RinkaIntegrationException($e->getMessage());
        }
    }

    /**
     * Try to submit export document
     *
     * @param RinkaAsiExportDocument $ExportDocument
     * @param int $counter
     * @return string
     * @throws RinkaAsiException
     */
    private function tryToSubmitExportDocument(RinkaAsiExportDocument $ExportDocument, $retryCount = 0)
    {
        try {
            $response = $this->asi->submitExportDocument($ExportDocument);

            return $response;
        } catch(RinkaAsiException $e) {
            if ($retryCount > 3) {
                throw $e;
            }
            sleep(1);
            return $this->tryToSubmitExportDocument($ExportDocument, ++$retryCount);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     *
     * @param array $images
     */
    private function deleteSavedImages($images) {
        foreach ($images as $image) {
            @unlink($image['path']);
        }
    }

    /**
     *
     * @param array $images
     * @throws RinkaIntegrationException
     */
    private function validateImages($images) {
        $allowedTypes      = $this->config->getConfigValue(array('image', 'allowedTypes'));
        $maxSize           = $this->config->getConfigValue(array('image', 'maxSize'));

        foreach ($images['name'] as $key => $imageName) {
            if (empty($images['tmp_name'][$key])) {
                continue;
            }

            $fileInfo = getimagesize($images['tmp_name'][$key]);

            if (!isset($allowedTypes[$fileInfo['mime']])) {
                throw new RinkaIntegrationException('Paveikslėlio formatas neatitinka reikalavimų (.PNG, .GIF, .JPG, .BMP).');
            }

            if ($images['size'][$key] > $maxSize) {
                throw new RinkaIntegrationException('Per didelis piešinėlio dydis.');
            }
        }
    }

    /**
     *
     * @param array $images
     * @return array
     * @throws RinkaIntegrationException
     */
    private function saveImages($images) {
        $allowedTypes      = $this->config->getConfigValue(array('image', 'allowedTypes'));
        $saveDirectoryPath = $this->config->getPath('imageUpload');
        $urlPath           = $this->config->getUrl('imageUpload');

        $_images = array();
        foreach ($images['name'] as $key => $imageName) {
            if (empty($imageName)) {
                continue;
            }

            $fileInfo = getimagesize($images['tmp_name'][$key]);
            $imageFileName = substr(md5(microtime()), @rand(20), 10) . '.' . $allowedTypes[$fileInfo['mime']];

            if (!move_uploaded_file($images['tmp_name'][$key], $saveDirectoryPath . DIRECTORY_SEPARATOR . $imageFileName)) {
                throw new RinkaIntegrationException('Blogos failų teisės arba nerasta laikina paveikslėlių direktorija');
            }

            $_images[] = array(
                'name'     => $imageName,
                'path'     => $saveDirectoryPath . DIRECTORY_SEPARATOR . $imageFileName,
                'urlPath'  => $urlPath . DIRECTORY_SEPARATOR . $imageFileName,
            );
        }

        return $_images;
    }

    /**
     * Receives announcements from RinkaAsi with provided filter.
     *
     * @uses RinkaAsi
     * @param RinkaAsiFilter $filter
     * @return array                    For instance:
     *                                  [
     *                                      'totalAds' => 5,
     *                                      'ads' => [
     *                                          RinkaAsiAnnouncement,
     *                                          RinkaAsiAnnouncement,
     *                                      ]
     *                                  ]
     *
     */
    public function getAnnouncements(RinkaAsiFilter $filter) {
        return $this->asi->getFilteredAnnouncements($filter);
    }

}
