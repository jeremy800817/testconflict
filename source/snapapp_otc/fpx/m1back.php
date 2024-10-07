<?php
define('SNAP_APP_MODE', 0x01);
define('SNAP_HANDLER_CLASS', '\Snap\handler\MyGtpFpxApiHandler');
define('FPX_HANDLER_CLASS', 'M1Pay');
include(dirname(__FILE__,2).DIRECTORY_SEPARATOR.'mygtp.php');
?>