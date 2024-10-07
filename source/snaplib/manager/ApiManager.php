<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

use Snap\api\param\ApiParam;
use Snap\object\ApiLogs;
use Snap\object\Order;
use Snap\object\MyGoldTransaction;
use Snap\api\param\SapApiParam;
use Snap\api\sap\SapApiProcessor;
Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\PriceProvider;
Use \Snap\object\MyLedger;
use \Snap\object\Redemption;
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Snap\api\mygtp\MyGtpApiSender;

/**
 * This ApiManager class is used to process request and also prepare a suitable adaptor to send requests 
 * to the appropriate client.
 */
class ApiManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * This method will be called by IObserverable object to notify of changes that has been done to it.
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if($changed instanceof \Snap\manager\PriceManager && 
            $state->isNewAction() && 
            ($state->target instanceof \Snap\object\PriceStream || $state->target instanceof \Snap\object\PriceValidation)) {
            $isValidation = (preg_match('/validation/i', array_pop(explode('\\', get_class($state->target))))) ? true : false;
            $this->log(__CLASS__." received notification on new ". ($isValidation? "price validation":"price stream").".  Adding to api log", SNAP_LOG_DEBUG);
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $apilog = $this->app->apilogStore()->create([
                'type' => ($isValidation ? \Snap\object\ApiLogs::TYPE_PRICEVALIDATION : \Snap\object\ApiLogs::TYPE_PRICESTREAM),
                'fromip' => $ip,
                'refobject' => array_pop(explode('\\', get_class($state->target))),
                'refobjectid' => $state->target->id,
                'systeminitiate' => 1,
                'requestdata' => (isset($state->otherParams['provider']) ? $state->otherParams['provider'] : 'Data:  ' .json_encode($_REQUEST)),
                'responsedata' => $state->otherParams['rawPriceData'],
                'status' => 1
            ]);
            $this->app->apilogStore()->save($apilog);
        }
    }

    /**
     * This method will be called on receiving a request for GTP actions.  This method will choose the correct api
     * connector to process the required request and then relay the request to the appropriate manager for further
     * processing.
     * 
     * @param  array $params A key-value pair of data sent in by the requestor
     * 
     */
    public function processGtpRequest($params)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            //Check, validate and extract information from the 
            $version = $params['version'];
            $apiParam = \Snap\api\param\GtpApiParam::getInstance($version);
            $this->logDebug(__METHOD__." API version is {$params['version']} and the class to generate is " . get_Class($apiParam));
            // $digestValidator = new \Snap\api\digest\GtpDigest;
            $decodedData = $apiParam->decode($this->app, $params, null);
            //find the right processor version to handle the API.
            $processor = \Snap\api\gtp\GtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\IApiProcessor) {
                $this->logDebug(__METHOD__." API version {$params['version']} is invalid and the class to generate is cannot be found inside TRY");
                throw \Snap\api\exception\ApiProcessorNotFound::getTransaction($this, ['type' => 'GTP']);
            }
            $log = $this->app->apiLogStore()->create([
                'type' => 'GTP',
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'comments',
                'status' => 1
            ]);
            $apilogs = $this->app->apiLogStore()->save($log);
            $decodedData['apiLogs'] = $apilogs;
            //$params['version'] = strtolower($params['version']);
            $returnData = $processor->process($this->app, $apiParam, $decodedData, $params);

            $editLog = $this->app->apiLogStore()->getById($decodedData['apiLogs']->id);
            $editLog->responsedata = json_encode($returnData);
            $updateLog = $this->app->apiLogStore()->save($editLog);

            return $returnData;
            
        } catch(\Exception $e) {
            //echo get_class($e);
            $processor = \Snap\api\gtp\GtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\GtpApiProcessor) {
                $this->logDebug(__METHOD__." Error generate in Catch at APIManager.");
                $processor->formatException($e);
            }
            
            //Handle API exceptions here.
            $log = $this->app->apiLogStore()->create([
                'type' => 'GTP',
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
            //throw $e;
            //TODO  Error message mapping to API reference.
        }
    }

/**
     * This method will be called on receiving a request for MyGtp actions.  This method will choose the correct api
     * connector to process the required request and then relay the request to the appropriate manager for further
     * processing.
     * 
     * @param  array $params A key-value pair of data sent in by the requestor
     * 
     */
    public function processMyGtpRequest($params)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $jsonData = file_get_contents("php://input");
            $this->logDebug(__METHOD__." Parameter send to apimanager are {$jsonData}");
            $params = array_merge($params, json_decode($jsonData, true) ?? []);
            //Check, validate and extract information from the 
            $version = $params['version'];
            $apiParam = \Snap\api\param\MyGtpApiParam::getInstance($version);
            $this->logDebug(__METHOD__." API version is {$params['version']} and the class to generate is " . get_class($apiParam));

            $decodedData = $apiParam->decode($this->app, $params, null);
            //find the right processor version to handle the API.
            $processor = \Snap\api\mygtp\MyGtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\IApiProcessor) {
                $this->logDebug(__METHOD__." API version {$params['version']} is invalid and the class to generate is cannot be found inside TRY");
                throw \Snap\api\exception\ApiProcessorNotFound::fromTransaction($this, ['type' => 'MyGTP']);
            }
            if (isset($params['password'])) {
                $origParams = $params;
                $params['password'] = '';
            }
            $log = $this->app->apiLogStore()->create([
                'type' => \Snap\object\ApiLogs::TYPE_MYGTP,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'comments',
                'status' => 1
            ]);
            $apilogs = $this->app->apiLogStore()->save($log);
            $decodedData['apiLogs'] = $apilogs;

            $params = isset($params['password']) ? $origParams : $params;
            $returnData = $processor->process($this->app, $apiParam, $decodedData, $params);

            if (filter_var($this->app->getConfig()->{'development'}, FILTER_VALIDATE_BOOLEAN)) {
                $editLog = $this->app->apiLogStore()->getById($decodedData['apiLogs']->id);
                $editLog->responsedata = json_encode($returnData);
                $updateLog = $this->app->apiLogStore()->save($editLog);
            }
        } catch(\Snap\api\exception\ApiException $e) {
            $processor = \Snap\api\mygtp\MyGtpApiProcessor::getInstance($version);
            $formattedException = [];
            if($processor instanceof \Snap\api\mygtp\MyGtpApiProcessor) {
                $this->logDebug(__METHOD__." : Exception thrown.");
                $formattedException = $processor->formatException($e);
                $sender = MyGtpApiSender::getInstance("Json", null);
                $sender->response($this->app, $formattedException);
            }
            
            //Handle API exceptions here.
            $log = $this->app->apiLogStore()->create([
                'type' => \Snap\object\ApiLogs::TYPE_MYGTP,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);

            return $formattedException;
            //throw $e;
            //TODO  Error message mapping to API reference.
        }

        return $returnData;
    }

    /**
     * This method will be called on receiving a request for MyDbbGtp actions.  This method will choose the correct api
     * connector to process the required request and then relay the request to the appropriate manager for further
     * processing.
     * 
     * @param  array $params A key-value pair of data sent in by the requestor
     * 
     */
    public function processMyGtpDbRequest($params)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $jsonData = file_get_contents("php://input");
            $params = array_merge($params, json_decode($jsonData, true) ?? []);

            //Check, validate and extract information from the 
            $version = $params['version'];
            $apiParam = \Snap\api\param\MyGtpApiParam::getInstance($version);
            $this->logDebug(__METHOD__." API version is {$params['version']} and the class to generate is " . get_class($apiParam));

            $decodedData = $apiParam->decode($this->app, $params, null);
            //find the right processor version to handle the API.
            $processor = \Snap\api\mygtp\MyGtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\IApiProcessor) {
                $this->logDebug(__METHOD__." API version {$params['version']} is invalid and the class to generate is cannot be found inside TRY");
                throw \Snap\api\exception\ApiProcessorNotFound::fromTransaction($this, ['type' => 'MyGTP']);
            }
            if (isset($params['password'])) {
                $origParams = $params;
                $params['password'] = '';
            }
            $log = $this->app->apiLogStore()->create([
                'type' => \Snap\object\ApiLogs::TYPE_MYGTP,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'comments',
                'status' => 1
            ]);
            $apilogs = $this->app->apiLogStore()->save($log);
            $decodedData['apiLogs'] = $apilogs;

            $params = isset($params['password']) ? $origParams : $params;
            $returnData = $processor->process($this->app, $apiParam, $decodedData, $params);

            if (filter_var($this->app->getConfig()->{'development'}, FILTER_VALIDATE_BOOLEAN)) {
                $editLog = $this->app->apiLogStore()->getById($decodedData['apiLogs']->id);
                $editLog->responsedata = json_encode($returnData);
                $updateLog = $this->app->apiLogStore()->save($editLog);
            }
        } catch(\Snap\api\exception\ApiException $e) {
            $processor = \Snap\api\mygtp\MyGtpApiProcessor::getInstance($version);
            $formattedException = [];
            if($processor instanceof \Snap\api\mygtp\MyGtpApiProcessor) {
                $this->logDebug(__METHOD__." : Exception thrown.");
                $formattedException = $processor->formatException($e);
                $sender = MyGtpApiSender::getInstance("Json", null);
                $sender->response($this->app, $formattedException);
            }
            
            //Handle API exceptions here.
            $log = $this->app->apiLogStore()->create([
                'type' => \Snap\object\ApiLogs::TYPE_MYGTP,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);

            return $formattedException;
            //throw $e;
            //TODO  Error message mapping to API reference.
        }

        return $returnData;
    }

    /**
     * This method will be called on receiving a request for SAP request.  This method will choose the correct api
     * connector to process the required request and then relay the request to the appropriate manager for further
     * processing.
     * 
     * @param  array $params A key-value pair of data sent in by the requestor
     * 
     */
    public function processSapRequest($params)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $sapData = file_get_contents('php://input');
            $sapJsonData = json_decode($sapData, true);
            if( false === $sapJsonData) {
            }
            $params['body'] = $sapJsonData;
            $version = $params['version'];
            $apiParam = \Snap\api\param\SapApiParam::getInstance($version);
            $apiParam->setActionType($params['action']);
            $this->logDebug(__METHOD__." API version is {$params['version']} and the class to generate is " . get_Class($apiParam));
            // $digestValidator = new \Snap\api\digest\GtpDigest;
            foreach($sapJsonData as $aRequest) {
                $decodedData[] = $apiParam->decode($this->app, $aRequest, null);
            }
            $params['data'] = $decodedData;
            $processor = \Snap\api\sap\SapApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\IApiProcessor) {
                throw \Snap\api\exception\ApiProcessorNotFound::getTransaction($this, ['type' => 'SAP']);
            }
            $returnData = $processor->process($this->app, $apiParam, $decodedData, $params);    
            $log = $this->app->apiLogStore()->create([
                'type' => 'SAP',
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => json_encode($returnData),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
        } catch(\Snap\api\exception\ApiException $e) {
            $processor = \Snap\api\sap\SapApiProcessor::getInstance($version);
            $log = $this->app->apiLogStore()->create([
                'type' => 'SAP',
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);

            foreach($sapJsonData as $aJsonData){
                $aJsonData['success'] = 'N';
                $aJsonData['message'] = $e->getMessage();
                $originalRequest[] = $aJsonData;
                
            }
            $returnRequest = json_encode($originalRequest, JSON_PRETTY_PRINT);
            echo $returnRequest;
            /*if(!$processor instanceof \Snap\SapApiProcessor) {
                //throw \Snap\api\exception\ApiProcessorNotFound::getTransaction($this, ['type' => 'SAP']);
                $processor->formatException($e);
            }*/
            //throw $e;
            //TODO  Error message mapping to API reference.
        }
    }

    /**
     * This method will return an appropriate GTP processor that will be able to understand and 
     * translate the requested action into appropriate function call.
     * 
     * @param  array $params    A key-value pair of data sent in by the requestor
     * @param  string $type     The type of input that is expected from the request
     * @return apiProcessor
     */
    private function getGtpProcessor($params, $type = 'REST')
    {
    }

    /**
     * This method returns the dynamically generated price provider to use for the specified provider object
     * 
     * @param  PricePRovider $provider
     * @return IGtpPriceProvider   Return the provider api to manipulate on
     */
    public function getPriceCollector(PriceProvider $provider, $getInactive = false)
    {
        if( ! $provider->status && $getInactive == false) {
            throw \Snap\api\exception\PriceCollectorAPIProviderNotActive::fromTransaction($provider);
        }
        if(!preg_match('/^(\w+):(.*)/m', $provider->connectinfo, $matches)) {
            throw \Snap\api\exception\PriceCollectorAPINotFound::fromTransaction($provider);
        }
        $apiClassName = $matches[1] . "PriceCollectorApi";
        $collectorApiClass = '\\Snap\\api\\price\\' . $apiClassName;
        if(! class_exists($collectorApiClass)) {
            throw \Snap\api\exception\PriceCollectorAPINotFound::fromTransaction($provider);
        }
        $collectorApi = new $collectorApiClass;
        if(!$collectorApi instanceof \Snap\IGtpPriceCollectorAPI) {
            throw \Snap\api\exception\PriceCollectorInterfaceMismatch::fromTransaction($provider);
        }
        $collectorApi->initialise($this->app, $provider);
        return $collectorApi;
    }


    /**
     * This is the main method that will initiate requests to SAP system.  This method needs to be provided
     * the function name in SapApiSender that needs to be executed and parameters to pass over.  It will automatically
     * create a log entry
     * 
     * @param  String $functionName Name of the function in SapApiSender to run
     * @param  String $apiVersion   Version of the SapProcessor instance to use
     * @param  Array  $params       Contains parameters to run within the functionName
     * @param  Enum   $apiLogType   Log category to classify the API
     * @return Aray                 Data as defined from return of SAP.  Main one is the 'success' key that should return 'Y'
     */
    private function sendRequestToSap($functionName, $apiVersion, $params, $apiLogType)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $processor = \Snap\api\sap\SapApiProcessor::getInstance($apiVersion);
            $processor->setCalledFromHttp(false);
            if(! is_array($params)) {
                $params = [ $params ];
            }

            $functionParams = array_merge([$this->app], $params);
            $response = call_user_func_array([$processor, $functionName], $functionParams);
            $log = $this->app->apiLogStore()->create([
                'type' => $apiLogType,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => "URL: " . $response['url'] . "\nRequest Body:\n" . $response['requestData'],
                'responsedata' => "{$response['responseData']}",
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
        } catch(\Exception $e) {
            if($functionName == 'notifyReconcile'){
                //sendEmail
                $emailSubject = 'ERROR NOTIFICATION FOR SO/PO RECONCILED';
                $bodyEmail .= "There is error when reconciled transaction for SO/PO.\n";
                $bodyEmail .= $e->getMessage()."\n";
                $sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);
            }
            $log = $this->app->apiLogStore()->create([
                'type' => $apiLogType,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
            throw $e;
        }
        return $response['data'];
    }

    private function sendRequestToMBB($functionName, $apiVersion, $params, $apiLogType)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $processor = \Snap\api\gtp\GtpApiProcessor::getInstance($apiVersion);
            $processor->setCalledFromHttp(false);
            if(! is_array($params)) {
                $params = [ $params ];
            }

            $functionParams = array_merge([$this->app], $params);
            $response = call_user_func_array([$processor, $functionName], $functionParams);
            $log = $this->app->apiLogStore()->create([
                'type' => $apiLogType,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => "URL: " . $response['url'] . "\nRequest Body:\n" . json_encode($params),
                'responsedata' => json_encode($response, JSON_PRETTY_PRINT),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
        } catch(\Exception $e) {
            if($functionName == 'notifyReconcile'){
                //sendEmail
                $emailSubject = 'ERROR NOTIFICATION FOR SO/PO RECONCILED';
                $bodyEmail .= "There is error when reconciled transaction for SO/PO.\n";
                $bodyEmail .= $e->getMessage()."\n";
                $sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);
            }
            $log = $this->app->apiLogStore()->create([
                'type' => $apiLogType,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($params),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
            throw $e;
        }
        return $response;
    }

    /**
     * API to actually send notify to MBB when future order match.
     * @param  [type] $partner [description]
     * @return [type] $matchOrder [description]
     */
    public function notifyMerchantMatchOrder($partner, $matchOrder,$pricestreamid){
        $ip = \Snap\Common::getRemoteIP();
        if(0 == strlen($ip)) {
            $ip = 'localhost';
        }
        $version = $matchOrder->apiversion;

        $convertNumber = number_format($matchOrder->pricetarget,2,'.','');
        $spCaseNumber = preg_replace('/[^0-9]/', '', $convertNumber); //MBB received only hex. need to remove '.'
        /*please check with gtpapiparam1_0m if there is changes in*/
        if(null === $matchOrder->remarks || 0 == strlen($matchOrder->remarks)) {
            $msgFO = '';
        } else {
            $msgFO = $matchOrder->remarks;
        }
        $orgRequestParams = array(
            "version"  =>   $version,
            "future_ref_id" => $matchOrder->partnerrefid,           
            "price_id"  => $pricestreamid,
            "total_price"  => $spCaseNumber,
            "future_order_flag" => 'Y',          
            "fo_trans_type"  => 'M',
            "tran_date"  => date('dmY'),
            "status" => '1',           
            "msg"  => $msgFO
        );

        try {
            $processor = \Snap\api\gtp\GtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\IApiProcessor) {
                throw \Snap\api\exception\ApiProcessorNotFound::getTransaction($this, ['type' => 'GTP']);
            }

            $returnData =  $this->sendRequestToMBB('doMatchedFutureOrder', $version, ['partner'=>$partner,'orderqueue'=>$matchOrder, 'orgRequestParams'=>$orgRequestParams], \Snap\object\ApiLogs::TYPE_GTP);

            /*Checking MBB Staus "statusCode"
                0 Success
                200 Host Reject
                300 ESB timeout 
                100 ESB Req processing Fail
                103 ESB Response Processing Fail

                Checking MBB Status "status"
                0 Success
                1 Failed
            */
            $statusCode = $returnData['responseData']['statusCode'];
            $status = $returnData['responseData']['status'];

            if(!$statusCode && $status){
                $this->logDebug(__METHOD__." Success getting result from MBB.");
                return true;
            } else {
                $this->logDebug(__METHOD__." Failed getting result from MBB.");
                return false;
            }
        }
        catch(\Exception $e) {
            $processor = \Snap\api\gtp\GtpApiProcessor::getInstance($version);
            if(!$processor instanceof \Snap\GtpApiProcessor) {
                $this->logDebug(__METHOD__." Error generate in Catch at APIManager.");
                $processor->formatException($e);
            }

            $log = $this->app->apiLogStore()->create([
                'type' => 'GTP',
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => json_encode($orgRequestParams),
                'responsedata' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->apiLogStore()->save($log);
        }
    }

    /**
     * API to actually send a request over to SAP to inform them of new booking order received.
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public function sapBookNewOrder($order)
    {
        return $this->sendRequestToSap('notifyNewOrder', $order->apiversion, $order, \Snap\object\ApiLogs::TYPE_SAPORDER);
    }

    /**
     * API to actually send a request over to SAP to inform them of reverse order received.
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public function sapCancelOrder($order)
    {
        //TODO:  Implement connecting to SAP and delivering the reverse order
        return $this->sendRequestToSap('notifyCancelOrder', $order->apiversion, $order, \Snap\object\ApiLogs::TYPE_SAPCANCELORDER);
        //return ['success' => 'Y'];
    }

    public function sapRedemptionBranch($redemption)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyNewRedemptionBranch', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapRedemptionDelivery($redemption)
    {
        return $this->sendRequestToSap('notifyNewRedemptionDelivery', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    /*20200817 additional redemption api. Please delete this comment when this function already used.*/
    public function sapRedemptionSPDelivery($redemption)
    {
        return $this->sendRequestToSap('notifyNewRedemptionSPDelivery', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapRedemptionPreAppointment($redemption)
    {
        return $this->sendRequestToSap('notifyNewRedemptionPreAppointment', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }
    /*additional redemption api. Please delete this comment when this function already used.END*/

    /* API DUMMY */
    public function sapRedemptionUpdate($redemption, $scheduleInfo)
    {
        return true;
    }

    public function sapReverseRedemption($redemption)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyReverseRedemption', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapBuyback($buyback)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyNewBuyBack', $buyback->apiversion, $buyback, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapReverseBuyback($buyback)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyReverseBuyBack', $buyback->apiversion, $buyback, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapGetItemList($partner,$version)
    {
        return $this->sendRequestToSap('getItemList', $version, [$partner,$version], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapGetSharedMintedList($partner,$version)
    {
        return $this->sendRequestToSap('getSharedMintedList', $version, [$partner,$version], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapGetWarehouseList($partner,$version)
    {
        return $this->sendRequestToSap('getWarehouseList', $version, [$partner,$version], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapPHYINV($transaction,$version,$flag)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyUpdateReplenish', $version, [$transaction,$version,$flag], \Snap\object\FtpLogs::TYPE_IN);
    }

    public function sapPHYRTN($transaction,$version,$flag)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyUpdateReturn', $version, [$transaction,$version,$flag], \Snap\object\FtpLogs::TYPE_IN);
    }

    public function sapDAILYDVG($transaction,$version,$flag)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyDailyDVG', $version, [$transaction,$version,$flag], \Snap\object\FtpLogs::TYPE_IN);
    }

    public function sapDAILYTRN($transaction,$version,$flag)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyDailyTrans', $version, [$transaction,$version,$flag], \Snap\object\FtpLogs::TYPE_IN);
    }

    public function sapFODLYREC($transaction,$version,$flag)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyFORecon', $version, [$transaction,$version,$flag], \Snap\object\FtpLogs::TYPE_IN);
    }

    public function sapPHYCOUREC($transaction)
    {
        /*
        MBB response at email 'Fwd: MIGA-i FTP progress':
        PHYCOUREC
        Maybe for your keeping purposes
        */
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyCourierRecon', '1.0m', $transaction, \Snap\object\FtpLogs::TYPE_IN);
    }

    function checkEmail($email) {
       $find1 = strpos($email, '@');
       $find2 = strpos($email, '.');
       return ($find1 !== false && $find2 !== false && $find2 > $find1);
    }

    public function sendNotifyEmail($bodyEmail,$subject,$emailSend=null,$file=null,$attachment=null){
        $mailer = $this->app->getMailer();
        //$emailSend variable can be email or string from ftp process
        try{
            if($this->checkEmail($emailSend)){
                $mailer->addAddress($emailSend);
                $this->logDebug(__METHOD__." Add email address ".$emailSend." as recipient for ".$subject);
            } else {
                $emailTo = explode(',', $this->app->getConfig()->{'gtp.ftp.sendto'}); //compulsory email
                $emailBCC = $this->app->getConfig()->{'gtp.ftp.sendBCC'}; //compulsory email
                $getEmail = $this->app->getConfig()->{'gtp.ftp.'.$emailSend};

                if(!empty($getEmail)){
                    $additionalEmail = explode(',', $getEmail);
                    $emailTo = array_merge($emailTo, $additionalEmail);
                }

                foreach($emailTo as $anEmail){
                    $mailer->addAddress($anEmail);
                    $this->logDebug(__METHOD__." Add email address ".$anEmail." as recipient for ".$subject);
                }

                if(!empty($emailBCC)){
                    $emailToBCC = explode(',', $emailBCC);
                    foreach($emailToBCC as $aBccEmail){
                        $mailer->addBCC($aBccEmail);
                    }
                }
            }
            
            if($file != null || $attachment != null){
                if(is_array($file)){
                    foreach($file as $key=>$data){
                        $mailer->addAttachment($data[0][0],$data[0][1]);
                    }
                } else {
                    $mailer->addAttachment($file,$attachment);
                }
            }

            $this->logDebug(__METHOD__." Prepare to send email for ".$subject);
            $mailer->Subject = $subject;
            $mailer->Body    = $bodyEmail;
            $mailer->send();
            $this->logDebug(__METHOD__." Sending email to recipient is success for ".$subject);
        } catch (\Exception $e) {
            $this->log(__METHOD__." Sending email to recipient is failed for ".$subject." : ".$e, SNAP_LOG_ERROR);
        }
        
        /*if($mailer->send()) $this->logDebug(__METHOD__." Success sending email {$subject} to receivers.");
        else $this->logDebug(__METHOD__." Failed sending email {$subject} to receivers.");*/
    }

    public function ftpMbb($currentdate,$filename,$partner,$version,$server)
    {
        if(null != $server) $extraFlag = "#####".strtoupper($server)."#####";
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            //Check, validate and extract information from the
            $mbbFtp = new \Snap\api\mbb\MbbFtpParser($this->app,false);
            $this->logDebug(__METHOD__." Calling file for parse {$filename} and the class to generate is " . get_Class($mbbFtp));
            $ftparray = $mbbFtp->parseFile($filename);
            $ftparray['currentdate'] = $currentdate; //currentdate is in strtotime
            //reason converted to array because it return empty when passing obj to sendRequestToSap
            $ftparray['partner'] = array(
                'id' => $partner->id,
                'code' => $partner->code,
                'name' => $partner->name
            );

            /*checking filename inside file header is the same as file cronjob. value is in lowercase*/
            $headerRow = $ftparray[0];
            $headerName = $headerRow['header_filename'];
            if(strtoupper($filename) != $headerName) {
                echo "Filename is invalid.";
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Filename is different.']);
            }

            /*checking date inside file header is the same as date cronjob run*/
            $fileDate   = date('dmY',$currentdate);
            $dateFile = $headerRow['header_filedate'];
            if($fileDate != $dateFile) {
                echo 'Date inside file, '.$dateFile.' is different then date generate '.$fileDate.'.';
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Date inside file, '.$dateFile.' is different then date generate '.$fileDate.'.']);
            }

            /*checking line row number provide in file header is same as number of rows*/
            $totalLineFile = ltrim($headerRow['header_totalline'],0);
            $search = "detail_code";
            $counter = 0;
            foreach($ftparray as $key => $subArray){
                foreach($subArray as $key2 => $aArray){
                    if(strstr($key2,$search)){
                       $counter = $counter+1;
                    }
                }
            }
            if($counter != $totalLineFile){
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Number of line is different. In header show '.$totalLineFile.' while rows have '.$counter]);
            }

            if(is_array($ftparray)) {
                $ftparray = json_encode($ftparray);
            }

            $getFunction = 'sap'.strtoupper($filename);
            $callFunction = $this->$getFunction($ftparray,$version,$extraFlag);
            
            //save sap input
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'responsedata' => json_encode($callFunction),
                'text' => $ftparray,
                'status' => 1
            ]);
            $ftplogs = $this->app->ftplogStore()->save($log);
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);

            //sendEmail
            //$emailSubject = $extraFlag.' ERROR NOTIFICATION FOR '.strtoupper($filename);
            $emailSubject = 'ERROR NOTIFICATION FOR '.strtoupper($filename);
            $bodyEmail .= "There is error when generating file for ".strtoupper($filename).".\n";
            $bodyEmail .= $e->getMessage().".\n";
            $sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);
        }
    }

    public function ftpAceToMbb($filename)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $this->logDebug(__METHOD__." Calling apimanager to save log after success write ".$filename.".");

            if($ftparray !== false) {
                /*This is to add file content to database if success*/
                $path = $this->app->getConfig()->{'gtp.ftp.tombb'}.$filename.'.TXT'; //insert own local path
                $result = true;
                $handle = fopen($path, "r");
                $contents = fread($handle, filesize($path));
                fclose($handle);
                /*This is to add file content to database if success END*/
            } else {
                $this->logDebug(__METHOD__." Unable to grab ftp data ".$filename.".");
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to grab ftp data.']);
            }

            //Register a callback which is applied to each parsed line
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => $contents,
                'status' => 1
            ]);
            $ftplogs = $this->app->ftplogStore()->save($log);
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);

            $emailSubject = 'ERROR NOTIFICATION FOR '.$filename;
            $bodyEmail .= '
                There is error when generating file for '.$filename.'.'.
                $e->getMessage().
                '';
            $sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);
        }
        return $result;
    }

    public function aceBulkPayment($filename)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            $this->logDebug(__METHOD__." Calling apimanager to save log after success write ".$filename.".");

            if($ftparray !== false) {
                /*This is to add file content to database if success*/
                $path = $this->app->getConfig()->{'gtp.bulkpayment.temp'}.$filename.'.TXT'; //insert own local path
                $result = true;
                $handle = fopen($path, "r");
                $contents = fread($handle, filesize($path));
                fclose($handle);
                /*This is to add file content to database if success END*/
            } else {
                $this->logDebug(__METHOD__." Unable to grab ftp data ".$filename.".");
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to grab ftp data.']);
            }

            //Register a callback which is applied to each parsed line
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => $contents,
                'status' => 1
            ]);
            $ftplogs = $this->app->ftplogStore()->save($log);
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);

            $emailSubject = 'ERROR NOTIFICATION FOR '.$filename;
            $bodyEmail .= '
                There is error when generating file for '.$filename.'.'.
                $e->getMessage().
                '';
            $sendEmail = $this->sendNotifyEmail($bodyEmail,$emailSubject);
        }
        return $result;
    }

    public function processMBBResponse($currentdate,$filename)
    {
        try {
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }
            //Check, validate and extract information from the
            $responseTxt = new \Snap\api\mbb\AceBulkPaymentParser($this->app,false);
            $this->logDebug(__METHOD__." Calling file for parse {$filename} and the class to generate is " . get_Class($responseTxt));
            $ftparray = $responseTxt->parseFile($filename);
            if(is_array($ftparray)) {
                $ftpText = json_encode($ftparray);
            }
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => $ftpText,
                'status' => 1
            ]);
            $ftplogs = $this->app->ftplogStore()->save($log); 
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);
        }

        return $ftparray;
    }

    public function ftpGopayz($filename, $ftparray)
    {
        try {            
            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }

            $this->logDebug(__METHOD__." Calling apimanager to save log after success parsing ".$filename.".");

            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,                
                'text' => json_encode($ftparray),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_IN,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);

            //sendEmail
            $emailSubject = 'ERROR NOTIFICATION FOR '.strtoupper($filename);
            $bodyEmail = "There is error when parsing file for ".strtoupper($filename).".\n";
            $bodyEmail .= $e->getMessage().".\n";
            $this->sendNotifyEmail($bodyEmail,$emailSubject);
        }
    }

    public function ftpAceToGopayz($filename)
    {
        try {

            $ip = \Snap\Common::getRemoteIP();
            if(0 == strlen($ip)) {
                $ip = 'localhost';
            }

            $this->logDebug(__METHOD__." Calling apimanager to save log after success write ".$filename.".");

            /*This is to add file content to database if success*/
            $path = $this->app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'}. DIRECTORY_SEPARATOR . $filename; //insert own local path
            $handle = fopen($path, "r");
            $contents = fread($handle, filesize($path));
            fclose($handle);

            if (0 >= strlen($contents)) {
                $this->logDebug(__METHOD__." Unable to grab ftp data ".$filename.".");
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to grab ftp data.']);
            }
        
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => $contents,
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);
        } catch(\Exception $e) {
            $log = $this->app->ftplogStore()->create([
                'type' => \Snap\object\FtpLogs::TYPE_OUT,
                'systeminitiate' => 0,
                'fromip' => $ip,
                'requestdata' => $filename,
                'text' => 'Error: ' . $e->getMessage() . ' Type: '. get_class($e),
                'status' => 1
            ]);
            $this->app->ftplogStore()->save($log);

            $emailSubject = 'ERROR NOTIFICATION FOR '.$filename;
            $bodyEmail = "There is error when generating file for ".strtoupper($filename).".\n";
            $bodyEmail .= $e->getMessage().".\n";
            $this->sendNotifyEmail($bodyEmail,$emailSubject);
        }
    }

    /**
     * Returns the appropriate SAP api sender object to communicate with SAP.
     * @param  string $apiVersion Version number of the API to use for SAP communication
     * @param  mixed  $extra      Additional information that may be needed to determine the correct API for use.
     * @return apiSender object
     */
    public function getSapApiSender($apiVersion, $extra = null)
    {
    }

    /**
     * Returns the appropriate Price api sender object to communicate with Pricing engine.
     * @param  string $apiVersion Version number of the API to use for pricing communication
     * @param  mixed  $extra      Additional information that may be needed to determine the correct API for use.
     * @return apiSender object
     */
    public function getPriceApiSender($apiVersion)
    {
    }

    /**
     * Returns the appropriate SAP api sender object to communicate with GTP.
     * @param  string $apiVersion Version number of the API to use for SAP communication
     * @param  mixed  $extra      Additional information that may be needed to determine the correct API for use.
     * @return apiSender object
     */
    public function getGtpApiSender($apiVersion, $extra = null)
    {
    }

    /**
     * This method will return an appropriate SAP processor that will be able to understand and 
     * translate the requested action into appropriate function call.
     * 
     * @param  array $params    A key-value pair of data sent in by the requestor
     * @param  string $type     The type of input that is expected from the request
     * @return apiProcessor
     */
    private function getSapProcessor($params, $type)
    {
    }

 
   public function sapBusinessList($version, $params)
    {
        $apiParam = SapApiParam::getInstance($version);
        $apiParam->setActionType('partnerlist');
        $data = $apiParam->decode(\Snap\App::getInstance(), $params);
        $params['version'] = $version;
        return $this->sendRequestToSap('getBusinessList', $version, [$apiParam, $data, $params], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapItemList($version, $params)
    {
        $apiParam = SapApiParam::getInstance($version);
        $apiParam->setActionType('itemlist');
        $data = $apiParam->decode(\Snap\App::getInstance(), $params);
        $params['version'] = $version;
        return $this->sendRequestToSap('getItemList', $version, [$apiParam, $data, $params], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapGetRateCard($version, $params)
    {
        $apiParam = SapApiParam::getInstance($version);
        $apiParam->setActionType('ratecard');
        $data = $apiParam->decode(\Snap\App::getInstance(), $params);
        $params['version'] = $version;

        return $this->sendRequestToSap('getRateCard', $version, [ $apiParam, $data, $params], ApiLogs::TYPE_SAP);
    }

    public function sapGetGrnDraft($version, $params)
    {
        $apiParam = SapApiParam::getInstance($version);
        $apiParam->setActionType('grndraft');
        $data = $apiParam->decode(\Snap\App::getInstance(), $params);
        $params['version'] = $version;

        return $this->sendRequestToSap('getGrnDraft', $version, [ $apiParam, $data, $params], ApiLogs::TYPE_SAP);
    }

    public function sapGetStatement($version, $params)
    {
        $apiParam = SapApiParam::getInstance($version);
        $apiParam->setActionType('statement');
        $data = $apiParam->decode(\Snap\App::getInstance(), $params);
        $params['version'] = $version;

        return $this->sendRequestToSap('getStatement', $version, [ $apiParam, $data, $params], ApiLogs::TYPE_SAP);
    }

    public function sapGetOpenPo($version, bool $verify, $code) 
    {
        if ($version == "1.0") {
            $params = [];
            $params['verification'] = $verify;
            $params['code'] = $code;

            $action = 'openpo';
            $apiParam = SapApiParam::getInstance($version);
            $apiParam->setActionType($action);
            $data = $apiParam->decode(\Snap\App::getInstance(), $params);
            $params['version'] = $version;

            return $this->sendRequestToSap("getOpenPoList", $version, [$apiParam, $data, $params], ApiLogs::TYPE_SAP);
        }
    }

    /**
     * Get purchase or sales orders
     * 
     * @param string $type      The type of order. Value is either "purchase" or "sales".
     * @param string $refOrKey  The GTP reference number or internal key
     * @param bool   $isRef     Indicates if using reference number or internal key
     */
    public function sapGetPostedOrders($version, $type, $refOrKey, bool $isRef) 
    {
        if ($version == "1.0") {
            $params = [];
            if ($type =='purchase') {
                $action = "purchaseorder";
            } else if ($type == "sales") {
                $action = "salesorder";
            }

            $params['isgtpref'] = $isRef;
            $params['key'] = $refOrKey;

            $apiParam = SapApiParam::getInstance($version);
            $apiParam->setActionType($action);
            $data = $apiParam->decode(\Snap\App::getInstance(), $params);
            $params['version'] = $version;

            return $this->sendRequestToSap("getPostedOrders", $version, [$apiParam, $data, $params], ApiLogs::TYPE_SAP);
        }
    }

    public function sapPostSO($version, $order, $product, $orgRequestParams) {
        return $this->sendRequestToSap('postSalesOrder', $version, [$order, $product, $orgRequestParams], ApiLogs::TYPE_SAP);
    }

    public function sapPostPO($version, $order, $product, $orgRequestParams) {
        return $this->sendRequestToSap('postPurchaseOrder', $version, [$order, $product, $orgRequestParams], ApiLogs::TYPE_SAP);
    }

    public function sapPosBuyback($buyback)
    {
        $version = '1.0';
        $product = $this->app->productStore()->getByField('code', 'Jewel');
        $partner = $this->app->partnerStore()->getByField('id', $buyback->partnerid);
        $getBranch = $this->app->partnerStore()->getRelatedStore('branches')->getById($buyback->branchid);
        $orgRequestParams = array(
            "version" => $version  
            );
        return $this->sendRequestToSap('postPosPurchaseOrder', $version, [$buyback, $product, $partner, $orgRequestParams], \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapPostGrnDraft($version, $gtpNo, $sapCode, $selectedPos, $items, $requestParams, $comments = null) { 
        return $this->sendRequestToSap('postGrnDraft', $version, [$gtpNo, $sapCode, $selectedPos, $items, $requestParams, $comments], ApiLogs::TYPE_SAP);
    }

    public function sapReconcile($transaction)
    {
        $version = $transaction['version'];
        $convert = json_encode($transaction);
        return $this->sendRequestToSap('notifyReconcile', $version, $convert, \Snap\object\ApiLogs::TYPE_GTP);
    }

    public function sapFeeCharge($partner, $version, $fee, $xau, $type, $requestParams, $category=null)
    {
        $params['version'] = $version;
        $mypartnersap = $this->app->mypartnersapsettingStore()->searchTable()->select()
                            ->where('partnerid',$partner->id)
                            ->andWhere('transactiontype','LIKE','%'.$type.'%')
                            ->andWhere('status', 1) //MyPartnerSapSetting::STATUS_ACTIVE
                            ->execute();
        if(count($mypartnersap) > 0){
            foreach($mypartnersap as $aPartnerSap){
                $mypartnersapcode = $this->app->mypartnersapsettingcodeStore()->searchTable()->select()
                            ->where('partnerid',$aPartnerSap->partnerid)
                            ->andWhere('status', 1) //MyPartnerSapSettingCode::STATUS_ACTIVE
                            ->execute();
                if(count($mypartnersapcode) > 0){
                    foreach($mypartnersapcode as $aSapCode){
                        if($aPartnerSap->tradebpvendor) $sapBpcode = $aSapCode->tradebpvendor;
                        if($aPartnerSap->tradebpcus) $sapBpcode = $aSapCode->tradebpcus;
                        if($aPartnerSap->nontradebpvendor) $sapBpcode = $aSapCode->nontradebpvendor;
                        if($aPartnerSap->nontradebpcus) $sapBpcode = $aSapCode->nontradebpcus;
                    }
                }

                $requestParams['bpcode'] = $sapBpcode;

                $sendToSap[] = $this->sendRequestToSap('notifyFeeCharge', $version, [$partner, $fee, $xau, $type, $requestParams, $category,$aPartnerSap], \Snap\object\ApiLogs::TYPE_SAP);
            }
        } else {
            $sendToSap = array(0 => array('success' => 'Y')); //this is to cater if transaction type not exist
        }

        foreach($sendToSap as $aResponse){
            if((isset($aResponse[0]['success']) && $aResponse[0]['success'] == 'Y') || (isset($aResponse['success']) && $aResponse['success'] == 'Y')) {
                $sendSapResp = array(0 => array('success' => 'Y'));
            } else {
                $sendSapResp = $sendToSap;
            }
        }

        return $sendSapResp;
    }
    
    public function sapReturnKilobar($vaultitem) //need to provide version
    {
        if(0 == $vaultitem->partnerid) $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
        else $partnerid = $vaultitem->partnerid;
        $partner = $this->app->partnerStore()->getByField('id',$partnerid);
        if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomercode = $partner->sapcompanysellcode1;
        $getVersionChoice = explode(',', $this->app->getConfig()->{'gtp.version.list'}); //this is to get version of partner. set at config.ini
        foreach($getVersionChoice as $key=>$value){
            $splitVersion = explode('||', $value); 
            if($sapcustomercode== $splitVersion[0]) {
                $version = $splitVersion[1];
            }
        }
        return $this->sendRequestToSap('notifyReturnKilobar', $version, $vaultitem, \Snap\object\ApiLogs::TYPE_SAP);
    }

    public function sapSharedMintedReport($version)
    {

        $warehouse = 'MINT_WHS';
        return $this->sendRequestToSap('generateMintedReport', $version, [$warehouse, $version], \Snap\object\ApiLogs::TYPE_GTP);
    }

    public function transferOrderBetweenDb($goldTx,$ordTx = null){
        $getOrder = $this->app->orderStore()->getById($goldTx->orderid);
        $partnerGtp = $this->app->partnerStore()->getById($getOrder->partnerid);
        /*check if partner is main / sub. Take the partner group id*/
        $partnerGrpId = $this->app->partnerStore()->getById($partnerGtp->group);
        
        // $projName  = $this->app->getConfig()->{'projectBase'};
        if(isset($goldTx->referralbranchname)) {
            $projName = explode("@", $goldTx->ordpartnercode)[0].'_'.$goldTx->referralbranchname;
        }
        else {
            $projName = explode("@", $goldTx->ordpartnercode)[0];
        }

        $action = 'transferbetweendb';

        if($ordTx) {
            $status = $ordTx->status;
            $ordTrans = $ordTx;
        }
        else {
            $status = $getOrder->status;
            $ordTrans = $getOrder;
        }

        if('CompanySell' == $ordTrans->type){
            $getPaymentDetails  = $this->app->mypaymentdetailStore()->getByField('sourcerefno',$ordTrans->partnerrefid);
            $payDisburseTrx     = $getPaymentDetails;
        } else if ('CompanyBuy' == $ordTrans->type){
            $getDisbursementDetails = $this->app->mydisbursementStore()->getByField('transactionrefno',$ordTrans->partnerrefid);
            $payDisburseTrx         = $getDisbursementDetails;
        }

        /*GET LEDGER*/
        //$getLedger      = $this->app->myledgerStore()->getByField('refno',$ordTrans->partnerrefid);
        $getLedger = $this->app->myledgerStore()->searchTable()->select()
                        ->where('status', 1)
                        ->andWhere('accountholderid',0)
                        ->andWhere('refno', $ordTrans->partnerrefid)
                        ->execute();
        if($getLedger) {
            foreach($getLedger as $aLedger){
                $ledgerTrx  = $aLedger->toArray();
            }
        }

        if($ordTrans->status == Order::STATUS_CONFIRMED) $ordTrans->status = Order::STATUS_PENDING; // OVERWRITE STATUS AS PENDING WHEN TRANSFER
        if($goldTx->status == MyGoldTransaction::STATUS_CONFIRMED) $goldTx->status = MyGoldTransaction::STATUS_PAID; // OVERWRITE STATUS AS PAID WHEN TRANSFER
        //$ordTrans->orderno = $ordTrans->orderno."_".$projName;
        //$ordTrans->partnerrefid = $ordTrans->partnerrefid."_".$projName;
        //$goldTx->refno          = $goldTx->refno."_".$projName;

        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp'};

        if (preg_match('/(1|on|yes)/i', $this->app->getConfig()->{'otc.db.ordertransfer.priceadjust'})) {
            $this->transferOrderBetweenDbAdjustPrice($goldTx, $ordTrans, $payDisburseTrx);
        }
		
		if ($this->app->getStore('otcpricingmodel')) {
			$this->transferOrderBetweenDbOtcPricingModel($goldTx, $ordTrans, $payDisburseTrx);
		}

        $dataArray = [
            'goldtransaction'           => $goldTx->toArray(),
            'ordertransaction'          => $ordTrans->toArray(),
            'paydisbursetransaction'    => $payDisburseTrx ? $payDisburseTrx->toArray() : [],
            'ledgertransaction'         => $ledgerTrx
        ];

        $response = [
            "transactions"     => $dataArray,
            "partnername"      => $projName
	];

	if('BSN' == $this->app->getConfig()->{'projectBase'}) {
			$response["transactions_integrity"] = $this->getTransactionIntegrity();
		}

        $fullUrl = $baseUrl."?action=".$action."&version=".$getOrder->apiversion."&partner=".$partnerGrpId->code;
        $this->log("[Transfer DB Process] Url send ".$fullUrl.". And post transactions ".json_encode($response), SNAP_LOG_DEBUG);
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
		
        $server_output = curl_exec($ch);
        curl_close ($ch);

        if (curl_errno($ch)) {
            $this->log("[Transfer DB Process] Unable to trigger to another db - Response : " . $server_output, SNAP_LOG_DEBUG);
        } 
    }

    /**
	 * Get transaction integrity data for a specific time window.
	 *
	 * @return array transaction integrity data.
	 */
	public function getTransactionIntegrity ()
	{
		$now = new \DateTime('now', $this->app->getServerTimezone());
		$now->setTimezone($this->app->getUserTimezone());

		$startDateTime = new \DateTime($now->format('Y-m-d 00:00:00'), $this->app->getUserTimezone());
		$startDateTime->setTimezone($this->app->getServerTimezone());
		
		$endDateTime = new \DateTime($now->format('Y-m-d 23:59:59'), $this->app->getUserTimezone());
		$endDateTime->setTimezone($this->app->getServerTimezone());
		
		$goldTransactions = $this->app->mygoldtransactionStore()->searchView()->select()
							->where('status', 'IN', [MyGoldTransaction::STATUS_CONFIRMED, MyGoldTransaction::STATUS_FAILED])
							->where('createdon', '>=', $startDateTime->format('Y-m-d H:i:s'))
							->where('createdon', '<=', $endDateTime->format('Y-m-d H:i:s'))
							->execute();
		$data = array();					
		if ($goldTransactions) {
			$refNo = array();
			$total = 0;
			$date = $endDateTime->format('Ymd');
			foreach ($goldTransactions as $goldTransaction) {
				if(isset($goldTransaction->referralbranchname)) {
					$projName = explode("@", $goldTransaction->ordpartnercode)[0].'_'.$goldTransaction->referralbranchname;
				} else {
					$projName = explode("@", $goldTransaction->ordpartnercode)[0];
				}
				$refNo[] = $goldTransaction->refno . '_' . $projName;
				$total += 1;
			}
			$data[$date]['refno'] = implode(",", $refNo);
			$data[$date]['total'] = $total;
		}
		
		return $data;
	}

    /**
     * Transfer the order between the database and adjust its price
     *
     * @param MyGoldTransaction $mygoldtransaction The transaction object to be updated
     * @param Order $order The order object to be updated
     * @param Payment $payment The payment object to be updated
     * @return void
     */
    public function transferOrderBetweenDbAdjustPrice ($mygoldtransaction, $order, $payment)
    {
        if (0 < $order->pricestreamid) $priceStream = $this->app->pricestreamStore()->getById($order->pricestreamid);
        if (0 < $priceStream->priceadjusterid) $priceAdjuster = $this->app->priceadjusterStore()->getById($priceStream->priceadjusterid);
        if ($priceAdjuster) {
            $order->price = $this->app->priceManager()->getOriginalPrice($priceAdjuster, $order->price, $order->type);
            $order->amount = $order->price * $order->xau;
            $order->bookingprice = $this->app->priceManager()->getOriginalPrice($priceAdjuster, $order->bookingprice, $order->type);
            $mygoldtransaction->originalamount = $order->amount;
            $payment->amount = $order->amount;
        }
    }
	
	/**
	 * Transfers an order between the database and OtcPricingModel.
	 *
	 * @param MyGoldTransaction $mygoldtransaction The MyGoldTransaction object.
	 * @param Order $order The Order object.
	 * @param Payment $payment The Payment object.
	 */
	public function transferOrderBetweenDbOtcPricingModel ($mygoldtransaction, $order, $payment)
    {
        if (0 < $order->pricestreamid) $priceStream = $this->app->pricestreamStore()->getById($order->pricestreamid);
        if (0 < $priceStream->priceadjusterid) $otcPricingModel = $this->app->otcpricingmodelStore()->getById($priceStream->priceadjusterid);
        if ($otcPricingModel) {
			$partner = $this->app->partnerStore()->getById($order->partnerid);
			$order->partnerprice = $order->price + $order->discountprice;
			$order->price = $this->app->priceManager()->getOtcPricingModelOriginalPrice($otcPricingModel, $order->price, $order->type);
            $order->amount = $partner->calculator()->multiply($order->xau, $order->price);

            $order->bookingprice = $this->app->priceManager()->getOtcPricingModelOriginalPrice($otcPricingModel, $order->bookingprice, $order->type);
            $mygoldtransaction->originalamount = $order->amount;
        }
    }
    
	/**
	 * Transfers a conversion between databases.
	 *
	 * @param MyConversion $myConversion The MyConversion object.
	 */
    public function transferConversionBetweenDb ($myConversion)
    {
        $vwConversion = $this->app->myconversionStore()->searchView()->select()->where('id', $myConversion->id)->one();
        $redemption = $vwConversion->getRedemption();
        $partner = $this->app->partnerStore()->getById($vwConversion->rdmpartnerid);
        $partnerGroup = $this->app->partnerStore()->getById($partner->group);
        $myPaymentDetail = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $vwConversion->refno);
        $myLedger = $this->app->myledgerStore()->searchTable()->select()
                    ->where('status', MyLedger::STATUS_ACTIVE)
                    ->andWhere('accountholderid',0)
                    ->andWhere('refno', $vwConversion->refno)
                    ->execute();
        
        $arrMyConversion = $vwConversion->toArray(false);
        if ($arrMyConversion) {
            unset($arrMyConversion['id']);
            unset($arrMyConversion['accountholderid']);
        }
        
        $arrRedemption = $redemption->toArray(false);
        if ($arrRedemption) {
            unset($arrRedemption['id']);
        }
        
        $arrMyPaymentDetail = $myPaymentDetail->toArray(false);
        if ($arrMyPaymentDetail) {
            unset($arrMyPaymentDetail['id']);
            unset($arrMyPaymentDetail['accountholderid']);
        }
        
        $arrMyLedger = ($myLedger) ? $myLedger[0]->toArray(false) : '';
        if ($arrMyLedger) {
            unset($arrMyLedger['id']);
        }
        
        $dataArray = [
            'myconversion' => $arrMyConversion,
            'redemption' => $arrRedemption,
            'mypaymentdetail' => $arrMyPaymentDetail,
            'myledger' => $arrMyLedger
        ];

        $params = [
            "transactions" => $dataArray,
            "partnername" => explode("@", $partner->code)[0]
        ];

        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp'};
        $action = 'transferbetweendb';
        $fullUrl = $baseUrl."?action=".$action."&version=".$arrRedemption['apiversion']."&partner=".$partnerGroup->code;
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
		
        $server_output = curl_exec($ch);
        curl_close($ch);

        if (curl_errno($ch)) {
            $errorMessage = " , conversion failed to transfer. Error: " . curl_errno($ch) . " - " . curl_error($ch);
            $this->log(__function__ . $errorMessage, SNAP_LOG_ERROR);
        }
    
    }

    public function transferTransactionBetweenDb($partner,$transaction,$store,$version){
        /*check if partner is main / sub. Take the partner group id*/
        $partnerGrpId = $this->app->partnerStore()->getById($partner->group);
        // $projName  = $this->app->getConfig()->{'projectBase'};
        $projName = explode("@", $partner->code)[0];
        $action = 'transferbetweendb';

        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp'};

        foreach($transaction as $aTransaction){
            /*GET LEDGER*/
            //$getLedger      = $this->app->myledgerStore()->getByField('refno',$ordTrans->partnerrefid);
            $getLedger = $this->app->myledgerStore()->searchTable()->select()
                            ->where('status', 1)
                            ->andWhere('accountholderid',0)
                            ->andWhere('refno', $aTransaction->refno)
                            ->execute();
            if($getLedger) {
                foreach($getLedger as $aLedger){
                    $insertArr[$aTransaction->refno]['ledger']  = $aLedger->toArray();
                }
            }

            $insertArr[$aTransaction->refno]['storageadmin'] = $aTransaction->toArray();
        }

        $dataArray = [
            'storageadmintrx'  => $insertArr,
            'store'            => $store
        ];

        $response = [
            "transactions"     => $dataArray,
            "partnername"      => $projName
        ];

        $fullUrl = $baseUrl."?action=".$action."&version=".$version."&partner=".$partnerGrpId->code;
        $this->log("[Transfer DB Process] Url send ".$fullUrl.". And post transactions ".json_encode($response), SNAP_LOG_DEBUG);
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
		
        $server_output = curl_exec($ch);
        curl_close ($ch);

        if (curl_errno($server_output)) {
            $this->log("[Transfer DB Process] Unable to trigger to another db", SNAP_LOG_DEBUG);
        } 
    }

    public function sapReserveSerialNumber($redemption)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyNewReserveSerialNum', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function sapUnreserveSerialNumber($redemption)
    {
        //return ['serialNo' => $redemption->items, 'redemption_code' => 'REDEMPTIONCODE'];
        return $this->sendRequestToSap('notifyNewUnreserveSerialNum', $redemption->apiversion, $redemption, \Snap\object\ApiLogs::TYPE_APIREDEMPTION);
    }

    public function transferConversionBetweenSvr($conversion, $count = 1){
        $getRedemption = $this->app->redemptionStore()->getById($conversion->redemptionid);
        if($count > 1){
            $redemption_refno = substr($getRedemption->partnerrefno, 0, -1);
			$getPaymentDetails  = $this->app->mypaymentdetailStore()->getByField('sourcerefno',$redemption_refno);
        }
        else{
            $getPaymentDetails  = $this->app->mypaymentdetailStore()->getByField('sourcerefno',$getRedemption->partnerrefno);
        }
        /*GET LEDGER*/
        //$getLedger      = $this->app->myledgerStore()->getByField('refno',$ordTrans->partnerrefid);
        $getLedger = $this->app->myledgerStore()->searchTable()->select()
                        ->where('status', 1)
                        ->andWhere('accountholderid',0)
                        ->andWhere('refno', $getRedemption->partnerrefno)
                        ->execute();
        if($getLedger) {
            foreach($getLedger as $aLedger){
                $ledgerTrx  = $aLedger->toArray();
            }
        }
        //$getPaymentDetails  = $this->app->mypaymentdetailStore()->getByField('sourcerefno',$getRedemption->partnerrefno);
        $partner = $this->app->partnerStore()->getById($getRedemption->partnerid);
        $partnerGtp = $this->app->partnerStore()->getById($partner->group);
        
        $action = 'transferbetweensvr';
        $projName = $this->app->getConfig()->{'projectBase'};
        if($getRedemption->status == Redemption::STATUS_CONFIRMED) $getRedemption->status = Redemption::STATUS_PENDING; // OVERWRITE STATUS AS PENDING WHEN TRANSFER
        // if($conversion->status == MyConversion::STATUS_CONFIRMED) $conversion->status = MyConversion::STATUS_PAID; // OVERWRITE STATUS AS PAID WHEN TRANSFER
        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp.conversion'};
        $dataArray = [
            'conversion'    => $conversion->toArray(),
            'redemption'    => $getRedemption->toArray(),
            'paymentdetail' => $getPaymentDetails->toArray(),
            'ledger'        => $ledgerTrx
        ];
        $response = [
            "transactions"     => $dataArray,
            "partnername"      => $projName
        ];
        $fullUrl = $baseUrl."?action=".$action."&version=".$getRedemption->apiversion."&partner=".$partnerGtp->code;
        $this->log("[Transfer DB Process] Url send ".$fullUrl.". And post transactions ".json_encode($response), SNAP_LOG_DEBUG);
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
		
        $server_output = curl_exec($ch);
        curl_close ($ch);
        $response = json_decode($server_output, true);
        $rdm = $this->app->redemptionStore()->getById($conversion->redemptionid);
        if(isset($response['success'])){
            if($response['success']){
                if(isset($response['data']['redemptionItems'])){
                    $rdm->items = $response['data']['redemptionItems'];
                    $rdm->gtpstatus = \Snap\object\Redemption::GTPSTATUS_SUCCESS;
                    
                    $this->app->redemptionStore()->save($rdm);
                }
                else{
                    $rdm->gtpstatus = \Snap\object\Redemption::GTPSTATUS_PENDINGSAP;
                    $this->app->redemptionStore()->save($rdm);
                }
            }
            else{
                $rdm->gtpstatus = \Snap\object\Redemption::GTPSTATUS_FAILTRANSFER;
                $this->app->redemptionStore()->save($rdm);
            }
        }
        else{
            $rdm->gtpstatus = \Snap\object\Redemption::GTPSTATUS_FAILTRANSFER;
            $this->app->redemptionStore()->save($rdm);
        }
        
        
        if (curl_errno($server_output)) {
            $this->log("[Transfer DB Process] Unable to trigger to another server", SNAP_LOG_DEBUG);
        } 
    }

    public function getLogisticRecordFromGTP($redemption, $partnerCodeGtp){
        $action = 'getlogisticrecord';
        $data = [
            "redemptionno"     => $redemption->redemptionno
        ];

        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp.conversion'};
        $fullUrl = $baseUrl."?action=".$action."&version=".$redemption->apiversion."&merchant_id=".$partnerCodeGtp;
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        
        $response = curl_exec($ch);
        curl_close ($ch);
        return $response;
    }
    public function getRedemptionItemsFromGTP($record){
        $action = 'getredemptionitems';
        $data = [
            'redemptionno' => $redemption->redemptionno
        ];
        $baseUrl = $this->app->getConfig()->{'mygtp.db.gtp.conversion'};
        $fullUrl = $baseUrl."?action=".$action."&version=".$redemption->apiversion;
		
		$proxy = ($this->app->getConfig()->{'gtp.server.proxyurl'}) ? $this->app->getConfig()->{'gtp.server.proxyurl'} : '';
		
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		if (0 < strlen($proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        
        $response = curl_exec($ch);
        curl_close ($ch);
        return $response;
    }
}
?>
