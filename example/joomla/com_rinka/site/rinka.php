<?php
//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!class_exists('RinkaIntegrationContainer')) {
    $file = dirname(__FILE__) . '/RinkaIntegrationContainer.php';
    if (!file_exists($file)) {
        echo 'Klaida: Nerastas reikiamas failas';
    } else {
        include $file;
    }
}
if (class_exists('RinkaIntegrationContainer')) {
    $container = new RinkaIntegrationContainer();
    echo isset($_GET['task']) && $_GET['task'] == 'insert' ? $container->insert() : $container->announcementsList();
}