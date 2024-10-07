<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\api\mygtp\MyGtpApiSender;
use Snap\api\wallet\BaseWallet;
use Snap\App;
use Snap\TLogging;

/**
 * Handler class for all MyGtp Wallet API to GTP.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 */
class MyGtpWalletApiHandler implements \Snap\IHandler
{
    use TLogging;
    private $app = null;

    function __construct(App $app)
    {
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
        // Get POST body data
        $postData = file_get_contents("php://input");
        $this->logDebug(_METHOD_." Parameter send to MyGtpWalletApiHandler are {$postData}");

        if (defined('WALLET_HANDLER_CLASS')) {
            try {
                $walletClassString = WALLET_HANDLER_CLASS;
                if (filter_var($this->app->getConfig()->{'development'}, FILTER_VALIDATE_BOOLEAN)) {
                    $walletClassString .= "UAT";
                }
                $handlerClass = BaseWallet::getInstance($walletClassString);
                $data = $handlerClass->handleRequest($params, $postData);
            } catch (\Exception $e) {
                $this->logDebug("Caught exception: " . $e->getMessage());
                $data = ['success' => false, 'error_message' => $e->getMessage()];
            }

            $sender = MyGtpApiSender::getInstance("Json", null);
            $sender->response($app, $data);  
        } else {
            $this->log(__CLASS__ . ": No wallet handler class defined for this request.", SNAP_LOG_ERROR);
            $this->log("POST: $postData", SNAP_LOG_ERROR);
            $this->log("params: " . json_encode($params), SNAP_LOG_ERROR);
            $this->log("END logging for " . __CLASS__, SNAP_LOG_ERROR);
            return;
        }
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
