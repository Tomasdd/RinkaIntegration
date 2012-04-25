<?php
if (!class_exists('RinkaIntegrationContainer')) {
    include dirname(__FILE__) . '/RinkaIntegrationContainer.php';
}
if (class_exists('RinkaIntegrationContainer')) {
    $container = new RinkaIntegrationContainer();
    return $container->insert();
}