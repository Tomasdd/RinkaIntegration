<script src="<?php echo $this->config->getUrl('assets'); ?>/js/jquery.min.js" type="text/javascript"></script>
<script src="<?php echo $this->config->getUrl('assets'); ?>/js/lightbox/jquery.lightbox.js" type="text/javascript"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/css/RinkaIntegration.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/js/lightbox/css/lightbox.css" />
<script type="text/javascript">
jQuery(function() {
    var lb = '<?php echo $this->config->getUrl('assets'); ?>/js/lightbox/images/';
    jQuery('a.lightbox').lightbox({
        fitToScreen: true,
        loopImages: false,
        fileLoadingImage: lb + 'loading.gif',
        fileBottomNavCloseImage: lb + 'close.gif',
        strings: {
            help: ' \u2190 / P - praeitas paveikslėlis\u00a0\u00a0\u00a0\u00a0\u2192 / K - kitas paveikslėlis\u00a0\u00a0\u00a0\u00a0ESC / X - uždaryti galeriją',
            prevLinkTitle: 'praeitas paveikslėlis',
            nextLinkTitle: 'kitas paveikslėlis',
            prevLinkText: '&laquo; Praeitas',
            nextLinkText: 'Kitas &raquo;',
            closeTitle: 'uždaryti galeriją',
            image: 'Paveikslėlis ',
            of: ' iš ',
            download: 'Atsisiųsti'
        }
    });
});
</script>
<div>
    <h1 class="news-title"><?php $this->write($announcement->getTitle()); ?></h1>
    <div style="color: #666; padding: 4px 0">
        <strong>Kategorija: </strong><?php
            $this->write($bridge->getCategoryTitles($announcement->getCategory()), '</span> &#8227; <span>');
        ?>
    </div>
    <div style="color: #666; padding: 4px 0">
        <strong>Paskelbta: </strong><?php echo date('Y-m-d', strtotime($announcement->getPublishDate())); ?>
    </div>
    <h2>Aprašymas</h2>
    <div style="margin: 20px 15px 20px 5px; text-align: justify">
    <?php $this->write($announcement->getDescription()); ?>
        </div>
    <?php if (count($announcement->getImageList()) > 0): ?>
        <h2>Nuotraukos</h2>
        <div style="margin-top: 10px">
            <?php foreach ($announcement->getImageList() as $image): ?>
            <a href="<?php $this->write($image); ?>" target="_blank" style="background: none" class="lightbox" rel="gallery">
                <img class="ad-image" src="<?php $this->write($image); ?>" alt="" style="height: 60px; float: left; padding: 0 10px 10px 0" />
            </a>
            <?php endforeach; ?>
            <div style="clear: both"></div>
        </div>
    <?php endif; ?>
    <h2>Duomenys</h2>
    <div style="padding: 2px">
        <table>
            <?php foreach ($announcement->getFieldList() as $field): ?>
                <tr>
                    <td style="padding-right: 20px; vertical-align: top">
                        <strong><?php $this->write($field['title']); ?></strong>:
                    </td>
                    <td style="vertical-align: top">
                        <?php if (is_array($field['value'])): ?>
                            <ul><li><?php $this->write($field['value'], '</li><li>'); ?></li></ul>
                        <?php else: ?>
                            <?php $this->write($field['value']); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <div style="margin-top: 20px">
        <a title="<?php $this->write($announcement->getTitle()); ?>" href="<?php $this->write($announcement->getUrl()); ?>"
            target="_blank" style="font-size: larger">Pilnas skelbimas su kontaktais</a>
    </div>
    <div style="margin-top: 20px">
        <a href="javascript:history.go(-1)">Grįžti atgal</a>
    </div>
</div>
<div style="clear: both"></div>