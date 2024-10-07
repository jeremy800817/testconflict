<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\wallet;

use Snap\IFpxOperation;
use Snap\IWalletOperation;
use Snap\IObservable;
use Snap\object\ApiLogs;
use Snap\TLogging;
use Snap\TObservable;

abstract class BaseWallet implements IFpxOperation, IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    protected $app = null;

    protected function __construct($app) {
        $this->app = $app;
        
        // Attach observers
        $this->attach($app->mygtptransactionManager());
        $this->attach($app->mygtpconversionManager());
    }

    /**
     * Returns an instance of the FPX handler
     * 
     * @return BaseWallet
     */
    final static public function getInstance($handlerName) {
        $matches = [];
        if (preg_match('/^\\\\Snap\\\\api\\\\wallet\\\\(.*)$/',$handlerName, $matches)) {
            $handlerName = $matches[1];
        }

        $classNameArr = explode('\\', __CLASS__);
        array_pop($classNameArr);
        $classNameArr[] = $handlerName;

        $className = implode('\\', $classNameArr);
        if(class_exists($className)) {
            $app = \Snap\App::getInstance();
            $app->logDebug("Instantiating Wallet handler $className");
            return new $className($app);
        }
    }

    /**
     * Processes callback/requests from FPX 
     * 
     */
    abstract public function handleRequest($params, $postBody);

    /**
     * Signs the data to be sent to FPX
     * 
     * @param array $data
     * 
     * @return string
     */
    abstract protected function signData($data);

    /**
     * Verify signed data received from Wallet
     * 
     * @param string $signedData    The data to be verified
     * @param string $plainData     The data to be verified against
     * 
     * @return boolean
     */
    abstract protected function verifySignedData($signedData, $plainData);

    /**
     * Log string to apilogs
     * 
     * @param string          $responseString
     * @param MyPaymentDetail $paymentDetail
     * @return void
     */
    protected function logApiResponse($responseString, $paymentDetail)
    {
        $this->app->startCLIJob("MyGtpFpxApiLogsResponseJob.php", [
            'gatewayrefno'  => $paymentDetail->id,
            'response'      => $responseString
        ]);
    }

    /**
     * @param string            $requestData   The request data to be sent
     * @param MyPaymentDetail   $paymentDetail The associated paymentdetail
     * 
     * @return void
     */
    protected function logApiRequest($requestData, $paymentDetail)
    {
        $log = $this->app->apilogStore()->create([
            'type'  => ApiLogs::TYPE_MYGTP_WALLET,
            'fromip' => 'localhost',
            'requestdata' => $requestData,
            'systeminitiate' => 1,
            'refobject' => array_pop(explode('\\', MyPaymentDetail::class)),
            'refobjectid' => $paymentDetail->id,
            'status'    => 1
        ]);

        $log = $this->app->apilogStore()->save($log);
    }

    /**
     * Method to check if a Wallet should expire an Order / Transaction
     *
     * @param  $paymentDetail
     * @return boolean
     */
    public function shouldExpire($paymentDetail)
    {
        return false;
    }
}


?>