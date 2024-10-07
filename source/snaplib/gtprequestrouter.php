<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
* This class is meant for performing customised application command routing for actions that the users want to do.
* This class will determine which handler will be called to perform the required function
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.base
*/
class GtpRequestRouter extends requestrouter
{
    /**
     * This method will create a request router instance to manage the routing of requests to a chosen handler class in
     * the framework.  You can initialise a generic version of the router which will automtically handle requests.
     *
     * @param constnat $routeType           The type of routing (path based / query based) that this router should act on
     * @param String   $handlerIdentifier   The index number / query key to indicate handler module to use.
     * @param String   $actionIdentifier    The index number / query key to indicate method to call or actions to use during method
     * @param String   $loginPageHandler     The default login page handler if session not found or expired
     * @param String   $expiredPageHandler   The handler class to manage session expiry
     * @param String   $startupPageHandler     The starting page handler to startup the app
     */
    public function __construct($routeType, $handlerIdentifier, $actionIdentifier, $loginPageHandler, $expiredPageHandler, $startupPageHandler)
    {
        parent::__construct($routeType, $handlerIdentifier, $actionIdentifier, $loginPageHandler, $expiredPageHandler, $startupPageHandler);
        $this->setHandlerPathPrefix('Snap\\handler\\');
    }

    public function route(App $app, $contextOrMode)
    {
       
        //Added by Devon on 2019/05/06 to overrive the initial settings to put into brain.
        if (isset($_REQUEST['ss'])) {
            $handlerName = 'main';
            return $this->handleRequest($app, $handlerName, 'list');
        } elseif (! isset($_REQUEST['hdl']) && 'logout' == $_REQUEST['action']) {
            echo "in router";
            exit();
            if ($app->getUserSession()->logout()) {
                $_SESSION['vuser'] = '';
                $app->shutdown();
                if ($app->getConfig()->{'app.hideURL'}) {
                    $loginurl = $_SERVER['PHP_SELF'].'?'.$app->getObsfucationParamName().'='.base64_encode('hdl='.urlencode($loginPageHandler));
                } else {
                    $loginurl = $_SERVER['PHP_SELF'];
                    if (0 < strlen($loginPageHandler)) {
                        $loginurl .= '?hdl='.urlencode($loginPageHandler);
                    }
                }
                echo "<html><head><script>window.top.location.href = '$loginurl';</script></head><body></body></html>";
                return;
            }
        }
        //End add 2019/05/06
        return parent::route($app, $contextOrMode);
    }

    /**
     * Checks whether the permission has been defined
     *
     * @param  App  $app
     * @param  String  $permission Permission that is needed fort the action
     * @return boolean             True if user has permission to perform action.  False otherwise.
     */
    protected function isPermissionOK($app, $permission)
    {
        //return $app->getUsersession() ? $app->hasPermission($permission) : false;
		return $app->hasPermission($permission);
    }

    /**
     * Process and returns the browser parameters.
     * @param  App    $app    Application object
     * @param  String $action Action to be performed
     * @return Array
     */
    protected function getBrowserReturnParams(App $app, $action)
    {
        $params = $_REQUEST;
        if (! isset($params['rot']) || '' == $params['rot']) {
            $params['rot'] = $params['aot'];
        }
        return $params;
    }
}
?>
