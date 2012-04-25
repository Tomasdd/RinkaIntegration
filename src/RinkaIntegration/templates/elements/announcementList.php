<?php $paginator->show(); ?>
<?php foreach ($announcements as $announcement): ?>
    <div style="padding: 20px 0; text-align: left">
        <h3><a href="<?php echo $this->config->getUrl('announcementList', array('id' => $announcement->getId())); ?>">
            <?php $this->write($announcement->getTitle()); ?>
        </a></h3>
        <div>
    		<span><?php echo date('Y-m-d', strtotime($announcement->getPublishDate())); ?>&nbsp;&nbsp;</span>
    		<span><a href="<?php $this->write($announcement->getUrl()); ?>"
                title="<?php $this->write($announcement->getTitle()); ?>" target="_blank">Skelbimas su kontaktais</a></span>
        </div>
        <div style="color: #666; padding: 4px 0">
            <strong>Kategorija: </strong><span><?php
                $this->write($bridge->getCategoryTitles($announcement->getCategory()), '</span> &#8227; <span>');
            ?></span>
        </div>
        <div style="float:left; text-align: center; padding: 0 4px 4px 0">
            <?php
                $previewImage = $announcement->getPreviewImage();
            ?>
            <a class="lightview" style="background: none"
                href="<?php echo $this->config->getUrl('announcementList', array('id' => $announcement->getId())); ?>">
                <img style="width: 100px; max-height: 200px" src="<?php echo (!empty($previewImage)) ? $this->escape($previewImage) : ($this->config->getUrl('assets') . '/images/no_image.gif'); ?>" alt="" />
            </a><br /><strong>
            <?php
                $price = $announcement->getField('Kaina');
                if ($price !== null) {
                    $this->write($price);
                } else {
                    echo '- LTL';
                }
            ?></strong>
        </div>
        <?php
            $matches = array();
            $description = $announcement->getDescription();
            if (preg_match('/^((.|\s)*) Kaina - (\d(\&nbsp\;)?\s?)+ LTL\.\s*$/', $description, $matches)) {
                $description = $matches[1];
            }
        ?>
		<?php $this->write($description); ?>
        <div style="clear: both"></div>
    </div>
<?php endforeach; ?>
<?php $paginator->show(); ?>