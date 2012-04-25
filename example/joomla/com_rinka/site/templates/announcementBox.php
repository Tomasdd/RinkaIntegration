<link rel="stylesheet" type="text/css" href="<?php echo $this->config->getUrl('assets'); ?>/css/RinkaIntegration.css" />

<div class="announcements_box" style="width: auto; border: none">
    <ul class="announcements_box_menu" style="margin: 0">
        <li>
            <a href="<?php echo $this->config->getUrl('announcementList'); ?>">Visi skelbimai</a>
        </li>
        <li>
            <a href="<?php echo $this->config->getUrl('insertAnnouncement'); ?>">Skelbti</a>
        </li>
    </ul>

    <div class="clear" style="margin-bottom: 15px"></div>

    <?php foreach (array('Siūlo' => 'tab1', 'Ieško' => 'tab2') as $title => $tab): ?>
        <h3 style="font-size: 16px"><?php $this->write($title); ?></h3>
        <ul style="margin: 0;">
            <?php foreach ($announcements[$tab]['ads'] as $announcement): ?>
                <li class="announcements_box_announcement" style="width: auto">
                    <a style="font-weight: bold" href="<?php echo $this->config->getUrl('announcementList', array('id' => $announcement->getId())); ?>">
                        <?php $this->write($announcement->getTitle()); ?>
                    </a><br />
                    <a target="_blank" style="font-size: 80%" title="<?php $this->write($announcement->getTitle()); ?>" href="<?php $this->write($announcement->getUrl()); ?>">
                        Pilnas skelbimas su kontaktais
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endforeach; ?>
</div>