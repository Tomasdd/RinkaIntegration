<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/css/RinkaIntegration.css" />

<script type="text/javascript">
    var assetsUrl = '<?php echo $this->config->getUrl('assets'); ?>';
    var base      = '<?php echo $this->config->getUrl('base'); ?>';

    var locationServiceUri = '<?php echo $this->config->getConfigValue(array('locationServiceUri')); ?>';
</script>

<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery.min.js" type="text/javascript"></script>-->
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery-ui-1.9.1.custom.min.js" type="text/javascript"></script>
<link rel="stylesheet" href="<?php echo $this->config->getUrl('assets'); ?>/css/jquery-ui.css">
       
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/htree.js?20120221" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/script.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/locationService.js" type="text/javascript"></script>

<script type="text/javascript">
    var countryAndCityList         = <?php echo json_encode($insertForm['contacts']['location']['list']); ?>;
    var countryAndCityDescriptions = <?php echo json_encode($insertForm['contacts']['location']['descriptions']); ?>;


    function countryChange(selectedCountry) {
        var selectedCity = jQuery('#cityList option:selected')[0].value;

        jQuery('#cityList').html('');
        jQuery.each(countryAndCityList[selectedCountry], function(key, value) {
            var selected = (selectedCity === selectedCountry +'/'+ key) ? 'selected="selected"' : '';
            var option = '<option value="'+ selectedCountry +'/'+ key +'" '+ selected +'>'+ countryAndCityDescriptions[key] +'</option>';
            jQuery('#cityList').append(option);
        });
    }

    function changeTextSelectValue(fieldId, selectedValue) {
        if (selectedValue === '##custom##') {
            jQuery('#' + fieldId).val('').css('display', 'block');
        } else {
            jQuery('#' + fieldId).val(selectedValue).css('display', 'none');
        }
    }

    function addAdditionalElement(blockName, fieldName) {
        jQuery('#' + fieldName).clone().css('display', 'block').removeAttr('id').appendTo('#' + blockName);
    }

    jQuery(function() {
        jQuery('#countryList').change();
        jQuery('.textSelectInputs').change();
    });
</script>

<div style="font-size: 12px">
    <?php echo $this->element('tree', array('tree' => $categoryTree)); ?>
</div>
<div id="insertForm">
    <form method="post" action="" enctype="multipart/form-data">
        <div id="insertForm_contactsBlock_wrapper">
            <h3>Kontaktai</h3>
            <?php echo $this->element('insertFormContacts', array(
                'contacts'         => $insertForm['contacts'],
                'postData'         => $postData,
                'getData'          => $getData,
                'validationErrors' => $validationErrors,
            )); ?>
        </div>

        <div id="insertForm_imagesBlock_wrapper">
            <h3>Paveiksliukai</h3>
            <?php echo $this->element('insertFormImages', array(
                'postData'         => $postData,
                'validationErrors' => $validationErrors,
            )); ?>
        </div>

        <div class="clear"></div>

        <div id="insertForm_formFields_wrapper">
            <h3>Skelbimo informacija</h3>
            <?php echo $this->element('insertFormFields', array(
                'fields'           => $insertForm['fields'],
                'postData'         => $postData,
                'validationErrors' => $validationErrors,
            )); ?>
        </div>

        <div class="clear"></div>
        <div id="insertForm_submit_wrapper">
            <input type="submit" name="submit" value="Siųsti skelbimą" />
        </div>
    </form>
</div>

<!-- Additional element blocks -->
<div id="insertForm_phoneField_clone" class="insertForm_phoneField" style="display: none">
    <input type="text" value="370" name="phone_numbers[]" />
    <a href="javascript:void(0)" onclick="jQuery(this).parent().remove(); return false;">
        <img src="<?php echo $this->config->getUrl('assets'); ?>/images/delete.gif" alt="Ištrinti telefono numerį" />
    </a>
</div>

<div id="insertForm_imageField_clone" class="insertForm_imagesBlock_imageField" style="display: none">
    <input type="file" name="images[]" />
    <a href="javascript:void(0)" onclick="jQuery(this).parent().remove(); return false;">
        <img src="<?php echo $this->config->getUrl('assets'); ?>/images/delete.gif" alt="Ištrinti paveikslėlį" />
    </a>
</div>
<!-- #Additional element blocks -->
<div style="clear: both"></div>
