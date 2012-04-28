<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/css/RinkaIntegration.css" />

<script type="text/javascript">
    var assetsUrl = '<?php echo $this->config->getUrl('assets'); ?>';
    var base      = '<?php echo $this->config->getUrl('base'); ?>';
</script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/htree.js?20120221" type="text/javascript"></script>

<script type="text/javascript">
    var countryAndCityList         = <?php echo json_encode($location['list']); ?>;
    var countryAndCityDescriptions = <?php echo json_encode($location['descriptions']); ?>;

    jQuery(function() {
        jQuery('#countryList').change();
    });

    function countryChange(selectedCountry) {
        if (selectedCountry === '') {
            jQuery('#cityList').html('');
            return;
        }

        var selectedCity = jQuery('#cityList option:selected')[0];
        jQuery('#cityList').html('');

        jQuery.each(countryAndCityList[selectedCountry], function(key, value) {
            var selected = '';
            if (selectedCity !== undefined) {
                selected = (selectedCity.value === selectedCountry +'/'+ key) ? 'selected="selected"' : '';
            }
            var option = '<option value="'+ selectedCountry +'/'+ key +'" '+ selected +'>'+ countryAndCityDescriptions[key] +'</option>';
            jQuery('#cityList').append(option);
        });
    }
</script>

<div>
    <a href="<?php echo $this->config->getUrl('insertAnnouncement'); ?>">Įdėti naują skelbimą</a>
</div>

<div style="font-size: 12px">
    <?php echo $this->element('tree', array('tree' => $categoryTree)); ?>
</div>

<div id="functions">
    <form method="get" action="">
        <?php $this->writeHiddenQueryParameters($getData, array('category', 'page', 'order', 'limit', 'country', 'city', 'submit')); ?>
        <input type="hidden" name="category" value="<?php echo $formValues['category']; ?>" />

        <div class="order">
            Rikiavimas:
            <select name="order">
                <?php foreach ($orders as $order => $description): ?>
                    <option value="<?php echo $order; ?>" <?php if ($formValues['order'] == $order): ?> selected="selected" <?php endif; ?>><?php echo $this->write($description); ?></option>
                <?php endforeach; ?>
            </select>

            Rodyti po:
            <select name="limit">
                <?php foreach (array(5, 10, 20, 50, 100) as $limit): ?>
                    <option value="<?php echo $limit; ?>"  <?php if ($formValues['limit'] == $limit): ?> selected="selected" <?php endif; ?>><?php echo $limit; ?></option>
                <?php endforeach; ?>
            </select>

        </div>
        <div class="filter">
            <?php $selectedCountry = !empty($city) ? current($city) : ''; ?>

            Šalis
            <select name="country" id="countryList" onchange="countryChange(this.options[this.options.selectedIndex].value)">
                <option value="" <?php echo (!$selectedCountry) ? 'selected="selected"' : null; ?>>-</option>
                <?php foreach ($location['list'] as $country => $cityList): ?>
                    <option value="<?php $this->write($country); ?>"
                            <?php if (!empty($selectedCountry) && $selectedCountry == $country): ?> selected="selected" <?php endif; ?>>
                        <?php $this->write($location['descriptions'][$country]); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            Miestas
            <select id="cityList" name="city">
                <?php foreach ($location['list'] as $country => $cityList): ?>
                    <?php foreach ($cityList as $cityName => $cityInfo): ?>
                        <?php
                        $selected = (!empty($city) && $city == array($country, $cityName))
                            ? 'selected="selected"'
                            : '';
                        ?>
                        <option value="<?php $this->write($country .'/'. $cityName); ?>" <?php echo $selected; ?>>
                            <?php $this->write($location['descriptions'][$cityName]); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </select>

            Paieška
            <input type="text" name="fulltext_search" value="<?php echo $formValues['fulltext_search']; ?>" />
            <input type="submit" name="submit" value="Filtruoti" style="width: 100px" />
        </div>
    </form>
</div>

<div id="announcements">
    <?php
    if (!empty($announcements)) {
        echo $this->element('announcementList', array(
            'announcements' => $announcements,
            'paginator'     => $paginator,
        	'bridge' => $bridge,
        ));
    } else {
        echo $this->element('announcementNotFound');
    }
    ?>
</div>
<div style="clear: both"></div>