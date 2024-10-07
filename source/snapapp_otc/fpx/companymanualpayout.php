<?php
// Example file
define('SNAP_APP_MODE', 0x01);
define('SNAP_HANDLER_CLASS', '\Snap\handler\MyGtpPayoutApiHandler');
define('FPX_HANDLER_CLASS', 'CompanyManualPayout');
include(dirname(__FILE__,2).DIRECTORY_SEPARATOR.'mygtp.php');
?>