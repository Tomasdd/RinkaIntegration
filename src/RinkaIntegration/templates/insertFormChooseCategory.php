<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/css/RinkaIntegration.css" />

<script type="text/javascript">
    var assetsUrl = '<?php echo $this->config->getUrl('assets'); ?>';
    var base      = '<?php echo $this->config->getUrl('base'); ?>';
</script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery-ui.custom.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/htree.js?20120221" type="text/javascript"></script>

<div style="font-size: 12px">
    <?php echo $this->element('tree', array('tree' => $categoryTree, 'compact' => false)); ?>
</div>
<div style="clear: both"></div>