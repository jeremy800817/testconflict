<?php
if ('bursagoldprodapi.ace2u.com' == $_SERVER['HTTP_HOST'] ) {
    define('SNAPAPP_CONFIGFILE', '/usr/local/nginx/html/gtp/source/snapapp/bursa.ini');
    define('SNAP_CONFIG', '/usr/local/nginx/html/gtp/source/snapapp/bursa.ini');
} 
// elseif('bursagolduat.ace2u.com' == $_SERVER['HTTP_HOST'] || 'bursagolduatapi.ace2u.com' == $_SERVER['HTTP_HOST']){
//     define('SNAPAPP_CONFIGFILE', '/usr/local/nginx/html/gtp/source/snapapp/bursa.ini');
//     define('SNAP_CONFIG', '/usr/local/nginx/html/mygtp_unified/source/snapapp/bursa.ini');
// }

define('SNAP_APP_MODE', 0x01);
define('SNAP_HANDLER_CLASS', '\Snap\handler\BursaApiHandler');
define('SNAPAPP_DBACTION_USERID', 4); //Api Request Entry User
include('brain.php');
?>