<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

use Snap\App;

/**
 * Handler class for all MyGtp API to GTP.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 */
class MyGtpApiHandler implements \Snap\IHandler {
    private $app = null;

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
        $app->apiManager()->processMyGtpRequest($params);
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
