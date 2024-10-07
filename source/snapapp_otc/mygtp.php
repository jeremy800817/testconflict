<?php
if (!defined('SNAP_APP_MODE')) {
    define('SNAP_APP_MODE', 0x01);
}
if (!defined('SNAP_HANDLER_CLASS')) {
    define('SNAP_HANDLER_CLASS', '\Snap\handler\MyGtpApiHandler');
}
define('SNAPAPP_DBACTION_USERID', 4); //Api Request Entry User

$_snapconfigs = [
    "(migaprod|migasapprod)"       => "production.ini",
    "easigold"                     => "easigold.ini",
    "gopayz"                       => "gopayz.ini",
    "onegold"                      => "onegold.ini",
    "onecall"                      => "onecall.ini",
    "mgold"                        => "mgold.ini",
    "koponas"                      => "koponas.ini",
    "otc-uat.ace2u.com:8443"       => "posarrahnu.ini",
    "posarrahnugolduatapi.ace2u.com" => "otc_posarrahnu.ini"
];

// Default is config.ini
foreach ($_snapconfigs as $domainRegex => $configFileName) {
    $regex = '/' . $domainRegex . '/i';
    if (preg_match($regex, $_SERVER['HTTP_HOST'])) {
        define('SNAP_CONFIG',dirname(__FILE__).DIRECTORY_SEPARATOR.$configFileName);
        break;
    }
}
unset ($_snapconfigs);

include('brain.php');
?>
