<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 26-Apr-2021
*/

namespace Snap\api\payout;

use Snap\IObservable;
use Snap\IPayoutProviderOperation;
use Snap\object\ApiLogs;
use Snap\object\MyDisbursement;
use Snap\TLogging;
use Snap\TObservable;

abstract class BasePayout implements IPayoutProviderOperation, IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    protected $app = null;

    protected function __construct($app) {
        $this->app = $app;
        
        // Attach observers
        $this->attach($app->mygtpdisbursementManager());
    }

    /**
     * Returns an instance of the FPX handler
     * 
     * @return BasePayout
     */
    final static public function getInstance($handlerName) {
        $matches = [];
        if (preg_match('/^\\\\Snap\\\\api\\\\payout\\\\(.*)$/',$handlerName, $matches)) {
            $handlerName = $matches[1];
        }

        $classNameArr = explode('\\', __CLASS__);
        array_pop($classNameArr);
        $classNameArr[] = $handlerName;

        $className = implode('\\', $classNameArr);
        if(class_exists($className)) {
            $app = \Snap\App::getInstance();
            $app->logDebug("Instantiating Payout handler $className");
            return new $className($app);
        }
    }

    /**
     * Handles incoming response from payout provider
     * 
     * @param array $params 
     * @param string $body 
     * @return mixed 
     */
    abstract public function handleResponse($params, $body);

    /**
     * Log string to apilogs
     * 
     * @param string          $responseString
     * @param MyDisbursement $disbursement
     * @return void
     */
    protected function logApiResponse($responseString, $disbursement)
    {
        $this->app->startCLIJob("MyGtpPayoutApiLogsResponseJob.php", [
            'gatewayrefno'  => $disbursement->id,
            'response'      => $responseString
        ]);
    }

    /**
     * @param string            $requestData   The request data to be sent
     * @param MyDisbursement   $disbursement The associated disbursement
     * 
     * @return void
     */
    protected function logApiRequest($requestData, $disbursement)
    {
        $log = $this->app->apilogStore()->create([
            'type'  => ApiLogs::TYPE_MYGTP_WALLET,
            'fromip' => 'localhost',
            'requestdata' => $requestData,
            'systeminitiate' => 1,
            'refobject' => array_pop(explode('\\', MyDisbursement::class)),
            'refobjectid' => $disbursement->id,
            'status'    => 1
        ]);

        $log = $this->app->apilogStore()->save($log);
    }

    /**
     * Method used to handle expired Order / Transaction
     *
     * @param  $disbursement
     * @return void
     */
    public function handleExpiredTransaction($disbursement)
    {
        
    }

}
?>