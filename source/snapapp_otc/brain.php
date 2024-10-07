<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
ini_set('display_errors', 1);
define( 'SNAPAPP_DIR', dirname(__FILE__));
define( 'SNAPLIB_DIR', dirname(SNAPAPP_DIR).'/snaplib');
if( ! defined('SNAP_CONFIG')) define('SNAP_CONFIG', SNAPAPP_DIR . DIRECTORY_SEPARATOR . 'config.ini');

/**
 * Available configuration setttings to route through brain
 * SNAP_APP_MODE   -- define this setting to 0x01 if no usersession is desired.
 * SNAP_HANDLER_CLASS  -- The full class name to bypass default request router.
 */
include_once( SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'app.php');
$app = Snap\App::getInstance( SNAP_CONFIG);
try {
    $username = isset($_REQUEST['username']) ? $_REQUEST['username'] : '';
    $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
    $captcha = isset($_REQUEST['captcha']) ? $_REQUEST['captcha'] : null;
    $extraParams = array();
    if(isset($_REQUEST['partnercode'])) $extraParams['partnercode'] = $_REQUEST['partnercode'];
    if(isset($_REQUEST['tzoffset'])) $extraParams['tzoffset'] = $_REQUEST['tzoffset'];
    // $partnercode = isset($_REQUEST['partnercode']) ? $_REQUEST['partnercode'] : '';
    if(! defined('SNAP_APP_MODE')) {
        define('SNAP_APP_MODE', 0);
    }
    $app->run( SNAP_APP_MODE, $username, $password, $captcha, $extraParams);
} catch(Exception $e) {
    if(preg_match('/(1|on|yes)/i', $app->getConfig()->development)) {
        echo "There was an uncaught exception thrown in the system:<Br>" . $e->getMessage();
        echo "<br><br>Stack Trace info:<br>";
        echo nl2br($e->getTraceAsString());
    }
}
?>