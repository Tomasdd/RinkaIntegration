<?php
//don't allow other scripts to grab and execute our file
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

if (!class_exists('RinkaIntegrationContainer')) {
    $file = dirname(__FILE__) . '/../../../components/com_rinka/RinkaIntegrationContainer.php';
    if (!file_exists($file)) {
        die('Klaida: Nerastas reikiamas failas');
    } else {
        include $file;
    }
}
if (class_exists('RinkaIntegrationContainer')) {
    $container = new RinkaIntegrationContainer();
    $defaultCities = $container->getDefaultCities();
    $isUnderConstruction = $container->isUnderConstruction();
    $config = $container->getConfiguration();
    $username = $config['RinkaAsi']['username'];
    $password = $config['RinkaAsi']['password'];
    $announcementListUrl = $config['RinkaIntegration']['urls']['announcementList'];
    $insertAnnouncementUrl = $config['RinkaIntegration']['urls']['insertAnnouncement'];

    if (count($_POST) > 0) {
        $data = compact('defaultCities', 'isUnderConstruction', 'username', 'password', 'announcementListUrl', 'insertAnnouncementUrl');
        foreach ($_POST as $key => $value) {
            if (isset($data[$key])) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if ($v == '') {
                            unset($value[$k]);
                        }
                    }
                    $value = array_values($value);
                }
                $data[$key] = $value;
            }
        }

        $saveFilename = realpath(dirname(__FILE__) . '/../../../components/com_rinka/config.local.php');
        if (!file_put_contents($saveFilename, "<?php return unserialize('" . strtr(serialize($data), array('\\' => '\\\\', "'" => "\\'")) . "');\n")) {
            echo 'Error while writing configuration. Please ensure that this directory is writable: ' . dirname($saveFilename);
        }

        // save $data
        extract($data);
    }
} else {
    die('System error');
}
?>
<script>window.jQuery || document.write('<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"><\/script>')</script>
<script>jQuery.noConflict();</script>
<form action="" method="post">
    <div style="clear: both; margin: 5px 0">
        <label>Numatytieji miestai:</label> <a href="javascript:void(0)" onclick="jQuery(this).nextAll('div:hidden').first().clone().appendTo(jQuery(this).parent()).show()">Pridėti</a><br />
        <?php $firstCity = array_shift($defaultCities); if (is_array($firstCity)) $firstCity = $firstCity[1]; ?>
        <input type="text" name="defaultCities[]" value="<?php echo htmlspecialchars($firstCity, ENT_QUOTES, 'UTF-8'); ?>" />
        <div style="display: none"><input type="text" name="defaultCities[]" value="" /> <a href="javascript:void(0)" onclick="jQuery(this).parent().remove()">Išimti</a></div>
        <?php foreach ($defaultCities as $city): ?>
            <?php if (is_array($city)) $city = $city[1]; ?>
            <div><input type="text" name="defaultCities[]" value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" /> <a href="javascript:void(0)" onclick="jQuery(this).parent().remove()">Išimti</a></div>
        <?php endforeach; ?>
    </div>
    <div style="clear: both; margin: 5px 0">
        <input type="hidden" name="isUnderConstruction" value="0" />
        <label style="width: 500px; display: block;">Išjungti paslaugą (rodys tik klaidos pranešimą): <input style="float: right;" type="checkbox" name="isUnderConstruction" value="1" <?php if ($isUnderConstruction): ?>checked="checked" <?php endif; ?>/></label>
    </div>
    <div style="clear: both; margin: 5px 0">
        <label style="width: 500px; display: block;">Vartotojo vardas rinka.lt paslaugai: <input style="float: right;" type="text" name="username" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" /></label>
    </div>
    <div style="clear: both; margin: 5px 0">
        <label style="width: 500px; display: block;">Vartotojo slaptažodis rinka.lt paslaugai: <input style="float: right;" type="password" name="password" value="<?php echo htmlspecialchars($password, ENT_QUOTES, 'UTF-8'); ?>" /></label>
    </div>
    <div style="clear: both; margin: 5px 0">
        <label style="width: 500px; display: block;">Skelbimų puslapio URL: <input style="float: right;" type="text" name="announcementListUrl" value="<?php echo htmlspecialchars($announcementListUrl, ENT_QUOTES, 'UTF-8'); ?>" /></label>
    </div>
    <div style="clear: both; margin: 5px 0">
        <label style="width: 500px; display: block;">Skelbimų įdėjimo puslapio URL: <input style="float: right;" type="text" name="insertAnnouncementUrl" value="<?php echo htmlspecialchars($insertAnnouncementUrl, ENT_QUOTES, 'UTF-8'); ?>" /></label>
    </div>
    <div style="clear: both; margin: 5px 0">
        <input type="submit" value="Išsaugoti" />
    </div>
</form>