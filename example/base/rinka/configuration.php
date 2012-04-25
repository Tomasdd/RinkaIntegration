<?php
$templateDir = dirname(__FILE__) . '/templates/';                   // only used in configuration bellow, you can delete this line if $templateDir is not used
return array(
    'domainCityRelation' => array(
        'vilnius.example.com' => array('vilnius', 'vilniaus_r'),
        'kaunas.example.com' => array('kaunas', 'kauno_r'),
    ),
    //'adminEmail' => 'some@email.asd',                             // you can change email, to which errors are sent (not recommended - better leave it to rinka.lt team)
                                                                    // if set to null, errors are not sent to anyone

    'underConstruction' => false,                                   // if this setting is set, users will see error text instead of real system generated code
                                                                    // use this if there are some problems connecting to rinka.lt etc., to turn system off entirely
        // to enable system just for admin (using user-agent with string "rinka-debug"), uncomment next line
        // headers can be changed using Firefox with Modify Headers extension
    // 'underConstruction' => !(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'rinka-debug') !== false),

    'showErrors' => $_SERVER['REMOTE_ADDR'] == '127.0.0.1',         // this setting determines whether errorString or all exception information is showed to end-user
                                                                    // if errors are shown, they are not sent to admin
                                                                    // default setting - show errors only if system is running on localhost

    'errorString' => 'Atsiprašome, skelbimų sistema laikinai neveikia',     // you can change error text, which is displayed in case of error

    'config' => array(                                              // configuration for RinkaAsi and RinkaIntegration libraries; more info in RinkaIntegrationConfig and RinkaAsiConfig classes
        'RinkaAsi' => array(
            'username' => 'YOUR_USERNAME',                          // username and password - set to given ones
            'password' => 'YOUR_PASSWORD',
            'remoteServerBaseUrl' => 'http://rinka.lt/asi/',   // base address of rinka.lt endpoint
            'remoteServerSubmitBaseUrl' => 'http://rinka.lt:8080/asi/',   // base address of rinka.lt endpoint submit
        ),
        'RinkaIntegration' => array(
            'locationServiceUri' => 'http://rinka.lt/loc/',
            'templates' => array(                                   // you can change directory of each template by this setting
                //'announcementBox' => $templateDir,                // this setting would mean, that template 'announcementBox' is found in "__DIR__/templates/announcementBox.php"
                //'elements/announcementList' => $templateDir,      // use this syntax to change element's template location
            ),
            'urls' => array(                                                    // where specific pages can be found in your server
                'base' => 'http://' . $_SERVER['SERVER_NAME'],                  // base address - you can stick with default value
                'announcementList' => '/rinka/announcements.php',                      // address of page with announcement list; there must be "include 'rinka/list.php'" in that file/code
                'insertAnnouncement' => '/rinka/insertAnnouncement.php',               // address of page with announcement insert form; code "include 'rinka/insert.php'" is expected there
                'imageUpload'        => '/rinka/tmp/upload/images',              // address where uploaded images can be found; images are deleted after they are sent to rinka.lt server
                'assets'             => '/rinka/RinkaIntegration/src/RinkaIntegration/assets',    // address of css, js etc files, used in RinkaIntegration library
            ),
            'paths' => array(
                'base'        => dirname(__FILE__),                     // paths in this section are relative to this setting
                'rinka_integration' => dirname(__FILE__) . '/../../../src/RinkaIntegration/',
                'templates'   => '/RinkaIntegration/src/RinkaIntegration/templates',      // you can change templates path entirely - be sure to put all needed templates there
            ),
    		'availableCities' => array(                             // only these cities will be displayed in filtering and announcement insert forms; delete this section to enable all cities
    			array('lietuva', 'vilnius'),
    			array('lietuva', 'vilniaus_r'),
                array('lietuva', 'kaunas'),
                array('lietuva', 'kauno_r'),
    	    ),
        ),
    ),
);
