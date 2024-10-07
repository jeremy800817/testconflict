<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use \Snap\TLogging;
USe Snap\App;
/**
 * Handler class for all Commons API to GTP.
 */
class ApiHandler implements \Snap\IHandler {
    private $app = null;
    Use \Snap\TLogging;

    function __construct(App $app) {
        $this->app = $app;
    }

    /**
     * This is the main method that will be used to handle any requests from the handler
     *
     * @param  App    $app    The application instance
     * @param  String $action The action (string) to operate on
     * @param  Array  $params Query parameters parsed in
     * @return void
     */
    public function doAction($app, $action, $params)
    {
        $starttime = microtime(true);
        $this->log("----GTP-processGtpRequest - Start --request ", SNAP_LOG_DEBUG, $params);

        $app->apiManager()->processGtpRequest($params);
        
        $endtime = microtime(true);
        $timediff = number_format($endtime - $starttime, 3, '.', '');
        $this->log("----GTP-processGtpRequest - End --time({".$timediff."})", SNAP_LOG_DEBUG);
    }

    /**
     * This method will return the rights that are applicable for this handler with this particular user type
     *
     * @param  String  $action  Action requested by user
     * @return String   The permission string representing the permissions to check for
     */
    public function getRights($action)
    {
    }

    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action)
    {
    }

    /**
     * This method adds in additional handler to form a composite handler chain that is able to
     * perform certain types of actions.
     *
     * @param IHandler $child The handler that would be added into.
     */
    public function addChild(\Snap\IHandler $child)
    {
    }
}
