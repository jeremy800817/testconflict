<?php
if ('GET' === $_SERVER['REQUEST_METHOD']) {
    $_REQUEST['isfront'] = true;
}
define('SNAP_APP_MODE', 0x01);
define('SNAP_HANDLER_CLASS', '\Snap\handler\MyGtpWalletApiHandler');
define('WALLET_HANDLER_CLASS', 'GoPayz');
include(dirname(__FILE__,2).DIRECTORY_SEPARATOR.'mygtp.php');

?>