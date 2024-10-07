<?php
// if ('migaprod.ace2u.com' == $_SERVER['HTTP_HOST']) {
//     define('SNAPAPP_CONFIGFILE', '/usr/local/nginx/html/gtp/source/snapapp/production.ini');
// }
define('SNAP_APP_MODE', 0x01);
define('SNAP_HANDLER_CLASS', '\Snap\handler\ApiHandler');
define('SNAPAPP_DBACTION_USERID', 4); //Api Request Entry User
include('brain.php');
?>