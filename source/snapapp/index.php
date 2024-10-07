<?php
// if not ktp will user main config.ini
$_snapconfigs = [
    "ktp"                        => "ktp.ini",
    "pitihemas"                  => "ktp.ini"
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

include_once("brain.php");
?>