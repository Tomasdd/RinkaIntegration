<div id="insertForm_fieldsBlock">
    <?php
    foreach ($fields as $field) {
        $folderId = $field->getFieldsList()->getCategoryKey();
        $fieldIndex = 0;
        $addFieldIndex = false;
        $multiSelectField = false;

        $valueTypes = $field->getValueTypes();
        foreach ($valueTypes as $valueType) {
            if (get_class($valueType) == 'RinkaAsiValueTypeMultiselect') {
                $multiSelectField = true;
            }
        }

        $required   = $field->isRequired();
        echo '<div class="RinkaIntegration_field">'
            . '<div class="RinkaIntegration_fieldDescription">' . $this->escape($field->getDescription()) . '</div>'
            . ($required ? '<div class="RinkaIntegration_required">*</div>' : '')
            . ($multiSelectField ? '<div class="RinkaIntegration_multiSelectCheckAll"><input type="button" value="Pažymėti visus" onclick="multiSelectCheckAll(this); return false;"></div>' : '')
            . '<div class="clear"></div>';

        if (count($valueTypes) > 1) {
            $addFieldIndex = true;
        }

        foreach ($valueTypes as $valueType) {
            $fieldName = $field->getName() . (($addFieldIndex) ? '['.$fieldIndex.']' : '');

            switch (get_class($valueType)) {
                case 'RinkaAsiValueTypeCityList':
                    $html = '<script type="text/javascript">jQuery(document).ready(function() { locationService.init(\''.$folderId.'\', \''.$fieldName.'\'); });</script>';

                    $html .= '<div id="locationService">';
                    $html .= '<div id="div_loc_country">';
                    $html .= 'Šalis<br/>';
                    $html .= '<select class="city-list-item" id="loc_country" name="'. $fieldName.'[data_country]" onchange="locationService.getChildren(this.options[this.selectedIndex].value, \'country\')">';
                    $html .= '<option value=""></option>';
                    $html .= '<option value="country">---- Kita ----</option>';
                    $html .= '<option value="3">Lietuva</option>';
                    $html .= '</select>';
                    $html .= '</div>';
                    $html .= '</div>';

                    if (!empty($postData[$fieldName])) {
                        $countryNodeId = null;
                        $html .= '<script type="text/javascript">';
                        $html .= 'jQuery(document).ready(function() { function locationServiceBuffer() {';
                        foreach ($postData[$fieldName] as $nodeType => $nodeId) {
                            if ($nodeType == 'tmp') {
                                continue;
                            }

                            $add = '';

                            $nodeType = substr($nodeType, strpos($nodeType, '_') + 1);
                            if ($nodeType == 'country' && $nodeId == (string)(int) $nodeId) {
                                $countryNodeId = $nodeId;
                            }

                            if (!empty($nodeId)) {
                                if (!empty($postData[$fieldName]['tmp'][$nodeType])) {
                                    $add .= 'locationService.selectOption(\'' . $nodeId . '\', \'' . $nodeType . '\', \'' . $countryNodeId . '\'); ';
                                    $nodeId = $nodeType;
                                }
                                $html .= 'locationService.selectOption(\'' . $nodeId . '\', \'' . $nodeType . '\', \'' . $countryNodeId . '\'); ';

                                $html .= $add;
                            }
                        }
                        $html .= '}';

                        $html .= '
                                var processBuffer = function() {
                                    if (locationService.isLocationServiceReady()) {
                                        clearInterval(locationServiceBufferInterval);
                                        locationServiceBuffer();
                                    }
                                }
                                var locationServiceBufferInterval = setInterval(processBuffer, 500);';

                        $html .= '});';
                        $html .= '</script>';
                    }

                    echo $html;
                    break;
                case 'RinkaAsiValueTypeText':
                    $options = $valueType->getOptions();
                    if (!empty($options)) {
                        echo '<select class="textSelectInputs" onchange="changeTextSelectValue(\''.$fieldName.'\', this.options[this.options.selectedIndex].value)">';
                        $wasSelected = false;
                        foreach ($options as $option) {
                            if (isset($postData[$fieldName]) && $option['name'] == $postData[$fieldName]) {
                                $selected = 'selected="selected"';
                                $wasSelected = true;
                            }

                            echo '<option value="'.$option['name'].'" '.$selected.'>'.$this->escape($option['description']).'</option>';
                            $selected = '';
                        }
                        $selected = ((isset($postData[$fieldName]) && !$wasSelected)
                            ? 'selected="selected"'
                            : '');
                        echo '<option value="##custom##" '.$selected.'>Kita</option>';
                        echo '</select>';

                        $value = (isset($postData[$fieldName]) ? $postData[$fieldName] : '');
                        echo '<input id="'.$fieldName.'" type="text" style="display: none" name="'.$fieldName.'" value="'.$this->escape($value).'" />';
                        if (!empty($value) && !$wasSelected) {
                            echo '<script type="text/javascript">jQuery(document).ready(function() { jQuery(\'#'.$fieldName.'\').val(\''. $this->escape($value) .'\'); });</script>';
                        }
                    } else {
                        if ($addFieldIndex) {
                            $value = isset($postData[$field->getName()][$fieldIndex])
                                ? $postData[$field->getName()][$fieldIndex]
                                : '';
                        } else {
                            $value = isset($postData[$fieldName]) ? $postData[$fieldName] : '';
                        }
                        echo '<input type="text" name="'.$fieldName.'" value="'.$this->escape($value).'" />';
                    }
                    break;
                case 'RinkaAsiValueTypeSelect':
                    $options = $valueType->getOptions();
                    echo '<select name="'.$fieldName.'">';
                    foreach ($options as $option) {
                        if ($addFieldIndex) {
                            $selected = (isset($postData[$field->getName()][$fieldIndex]) && $option['name'] == $postData[$field->getName()][$fieldIndex])
                                ? 'selected="selected"'
                                : '';
                        } else {
                            $selected = (isset($postData[$fieldName]) && $option['name'] == $postData[$fieldName])
                                ? 'selected="selected"'
                                : '';
                        }

                        echo '<option value="'.$option['name'].'" '.$selected.'>'.$this->escape($option['description']).'</option>';
                    }
                    echo '</select>';
                    break;
                case 'RinkaAsiValueTypeCheckbox':
                    $checked = (isset($postData[$fieldName]) && $postData[$fieldName])
                        ? 'checked="checked"'
                        : '';
                    echo '<input type="checkbox" name="'.$fieldName.'" '.$checked.'/>';
                    break;
                case 'RinkaAsiValueTypeMultiselect':
                    $options = $valueType->getOptions();
                    echo '<div class="multiselect">';
                    echo '<table>';
                    foreach ($options as $option) {
                        $checked = (isset($postData[$fieldName]) && in_array($option['name'], $postData[$fieldName]))
                            ? 'checked="checked"'
                            : '';

                        echo '<tr>';
                        echo '<td><input id="'.$fieldName.'[]['.$option['name'].']" value="'.$option['name'].'" type="checkbox" name="'.$fieldName.'[]" '.$checked.' /></td>';
                        echo '<td><label for="'.$fieldName.'[]['.$option['name'].']">'.$this->escape($option['description']).'</label></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';

                    break;
            }
            if (isset($validationErrors[$fieldName])) {
                echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
            }
            ++$fieldIndex;
        }
        echo '</div>';
    }
    ?>

    <!-- publish_until -->
    <div class="RinkaIntegration_field">
        <div class="RinkaIntegration_fieldDescription">Skelbimo galiojimo terminai</div>
        <div class="clear"></div>
        &nbsp;&nbsp;Nuo
        <input type="text" name="publish_date" value="<?php echo !empty($postData['publish_date']) ? $postData['publish_date'] : date('Y-m-d'); ?>"/>
        <?php
            if (isset($validationErrors['publish_date'])) {
                echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
            }
        ?>
        iki
        <input type="text" name="publish_until" value="<?php echo !empty($postData['publish_until']) ? $postData['publish_until'] : date('Y-m-d', strtotime('+3 month')); ?>" />
        <?php
            if (isset($validationErrors['publish_until'])) {
                echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
            }
        ?>
    </div>
</div>
