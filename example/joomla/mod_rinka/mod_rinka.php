<?php
//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!class_exists('RinkaIntegrationContainer')) {
    $file = dirname(__FILE__) . '/../../components/com_rinka/RinkaIntegrationContainer.php';
    if (!file_exists($file)) {
        echo 'Klaida: Nerastas rinka.lt komponentas';
    } else {
        include $file;
    }
}
if (class_exists('RinkaIntegrationContainer')) {
    $container = new RinkaIntegrationContainer();
    echo $container->getAnnouncementBox();
}