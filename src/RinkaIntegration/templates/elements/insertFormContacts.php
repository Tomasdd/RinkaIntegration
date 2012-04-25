<div id="insertForm_contactsBlock">
    <div id="insertForm_contactsBlock_phonesBlock">
        <div class="RinkaIntegration_fieldDescription">
            Telefono numeris
        </div>
        <div class="RinkaIntegration_required">*</div>
        <div class="clear"></div>

        <?php
        $countNumbers = isset($postData['phone_numbers']) ? count($postData['phone_numbers']) : 1;
        for ($i=0; $i<$countNumbers; ++$i) {
            $phoneNumberValue = isset($postData['phone_numbers']) ? $postData['phone_numbers'][$i] : '370';
            echo '<div class="insertForm_phoneField">'
                . ' <input type="text" name="phone_numbers[]" value="'. $this->escape($phoneNumberValue) .'" />';

            if ($countNumbers > 1) {
                echo '<a href="javascript:void(0)" onclick="jQuery(this).parent().remove(); return false;">'
                    . '<img src="'. $this->config->getUrl('assets') .'/images/delete.gif" alt="Ištrinti telefono numerį" />'
                    . '</a>';
            }

            if (isset($validationErrors['phone_numbers['.$i.']'])) {
                echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
            }
            echo '</div>';
        }
        ?>
        </div>
    <a href="javascript:void(0)" onclick="addAdditionalElement('insertForm_contactsBlock_phonesBlock', 'insertForm_phoneField_clone')">
        <img src="<?php echo $this->config->getUrl('assets'); ?>/images/add.gif" alt="Pridėti dar vieną telefono numerį" />
        Pridėti dar vieną
    </a>

    <div class="clear"></div>

    <div id="insertForm_contactsBlock_emailBlock">
        <div class="RinkaIntegration_fieldDescription">
            El. paštas
        </div>
        <div class="RinkaIntegration_required">*</div>
        <div class="clear"></div>

        <input type="text" name="email" value="<?php echo isset($postData['email']) ? $this->escape($postData['email']) : ''; ?>" />
        <?php
        if (isset($validationErrors['email'])) {
            echo '<img src="'.$this->config->getUrl('assets').'/images/stop.png" alt="Klaida!" />';
        }
        ?>
    </div>

    <div id="insertForm_contactsBlock_countryBlock">
        <div class="RinkaIntegration_fieldDescription">
            Šalis
        </div>
        <div class="RinkaIntegration_required">*</div>
        <div class="clear"></div>

        <select name="country" id="countryList" onchange="countryChange(this.options[this.options.selectedIndex].value)">
            <?php foreach ($contacts['location']['list'] as $country => $cityList): ?>
                <option value="<?php echo $country; ?>"
                        <?php if (isset($postData['country']) && $postData['country'] == $country): ?> selected="selected" <?php endif; ?>>
                    <?php $this->write($contacts['location']['descriptions'][$country]); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="insertForm_contactsBlock_cityBlock">
        <div class="RinkaIntegration_fieldDescription">
            Miestas
        </div>
        <div class="RinkaIntegration_required">*</div>
        <div class="clear"></div>

        <select id="cityList" name="city">
        <?php foreach ($contacts['location']['list'] as $country => $cityList): ?>
            <?php foreach ($cityList as $cityName => $cityInfo): ?>
                <?php
                $selected = (
                    isset($postData['city']) && $postData['city'] == $country .'/'. $cityName
                    || !isset($postData['city']) && isset($getData['city']) && $getData['city'] == $country .'/'. $cityName
                ) ? 'selected="selected"' : '';
                ?>
                <option value="<?php echo $country .'/'. $cityName; ?>" <?php echo $selected; ?>>
                    <?php $this->write($contacts['location']['descriptions'][$cityName]); ?>
                </option>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </select>
    </div>
</div>