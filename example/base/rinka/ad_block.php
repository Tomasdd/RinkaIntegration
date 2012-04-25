<?php
if (!class_exists('RinkaIntegrationContainer')) {
    include dirname(__FILE__) . '/RinkaIntegrationContainer.php';
}
if (class_exists('RinkaIntegrationContainer')) {
    $container = new RinkaIntegrationContainer();
    $html = $container->getAnnouncementBox();

    $javascript = <<<JAVASCRIPT
document.getElementById('adBoxContainer').innerHTML = [CODE];
JAVASCRIPT;

    $expires = 120;
    header("Pragma: public");
    header("Cache-Control: maxage=" . $expires);
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
    header('Content-type: text/javascript');
    echo str_replace('[CODE]', json_encode($html), $javascript);
}