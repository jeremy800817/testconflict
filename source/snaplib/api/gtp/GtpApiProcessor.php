<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\IApiProcessor;
Use Snap\api\param\ApiParam;

/**
 * This processor class defines a main factory method to provide customisation on different API versions.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpApiProcessor implements IApiProcessor
{
    protected $calledFromHttp = true;

    public function formatException($e, $customizeMsg=''){
       $mapping = [
            'Snap\api\exception\ApiExtractorDataNotFound' => 100001,
            'Snap\api\exception\ApiParamCurrencyInvaliid' => 10002,
            'Snap\api\exception\ApiParamDigestMismtach' => 10003,
            'Snap\api\exception\ApiParamInvalidDatetimeFormat' => 10004,
            'Snap\api\exception\ApiParamNotNumeric' => 10005,
            'Snap\api\exception\ApiParamNotOneOf' => 10006,
            'Snap\api\exception\ApiParamPartnerInvalid' => 10007,
            'Snap\api\exception\ApiParamProductInvaliid' => 10008,
            'Snap\api\exception\ApiParamRequestNotFound' => 10009,
            'Snap\api\exception\ApiParamRequired' => 10010,
            'Snap\api\exception\ApiParamStringValue' => 10011,
            'Snap\api\exception\ApiProcessorNoActionFound' => 10012,
            'Snap\api\exception\ApiProcessorNotFound' => 10013,
            'Snap\api\exception\ApiParamBranchInvaliid' => 10014,
            'Snap\api\exception\FutureOrderIdMismatched' => 10100,
            'Snap\api\exception\OrderDenominationException' => 10200,
            'Snap\api\exception\OrderDuplicatedException' => 10201,
            'Snap\api\exception\OrderInvalidAction' => 10202,
            'Snap\api\exception\OrderPriceDataExpired' => 10203,
            'Snap\api\exception\OrderPriceDataInvalid' => 10204,
            'Snap\api\exception\OrderTransactionLimitExceeded' => 10205,
            'Snap\api\exception\OrderTransactionUnrecognised' => 10206,
            'Snap\api\exception\PartnerCodeNotAvailable' => 10300,
            'Snap\api\exception\PartnerNotActiveException' => 10301,
            'Snap\api\exception\PartnerOrderModeMismatch' => 10302,
            'Snap\api\exception\PartnerUnableToTransactionProduct' => 10303,
            'Snap\api\exception\PriceCollectorAPINotFound' => 10400,
            'Snap\api\exception\PriceCollectorAPIProviderNotActive' => 10401,
            'Snap\api\exception\PriceCollectorInterfaceMismatch' => 10402,
            'Snap\api\exception\PriceProviderNotFound' => 10403,
            'Snap\api\exception\PriceStreamDataStale' => 10404,
            'Snap\api\exception\PriceValidationNotGenerated' => 10405,
            'Snap\api\exception\TradingHourOutOfBounds' => 10500,
            'Snap\api\exception\VaultItemAvailability' => 10600,
            'Snap\api\exception\VaultItemError' => 10601,
            'Snap\api\exception\VaultItemInvalidAction' => 10602,
            'Snap\api\exception\VaultItemInvalidAvailable' => 10603,
            'Snap\api\exception\RedemptionError' => 10700,
            'Snap\api\exception\BuybackError' => 10800,
            'Snap\api\exception\RedemptionReversalError' => 10900,
            'Snap\api\exception\BuybackReversalError' => 11000,
            'Snap\api\exception\RefDuplicatedException' => 12000,
            'Snap\api\exception\GeneralException' => 20000,
            'Exception' => 30000,
            'Error' => 40000,
            'PDOException' => 50000,
            
        ];

        if(get_class($e)) {
            $expClass = get_class($e);
        }
        else {
            $expClass = 'Snap\api\exception\GeneralException';
        }

        if($customizeMsg !='') $errMsg = $customizeMsg;
        else $errMsg = $e->getMessage();
        
        //check if point empty, dont return point. difference between field & code?
        if('' != $e->getCode()) {
            $result = json_encode([
                'status' => 0,
                'error' => array(
                    'id' => $mapping[$expClass],
                    'point' => $e->getCode(),
                    'msg' => $errMsg,
                    
                )
            ]);
        }
        else {
            $result = json_encode([
                'status' => 0,
                'error' => array(
                    'id' => $mapping[$expClass],
                    'msg' => $errMsg,
                    
                )
            ]);
        }
        echo $result; //MBB return response
        return $result; //GTP apilogs save
    }

    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * 
     * @param  String $version Version number that we would like the params to get
     * @return GtpApiProcessor derived class
     */
    static function getInstance($version)
    {
        $className = __CLASS__ . preg_replace('/\./', '_', strtolower($version));
        \Snap\App::getInstance()->log("Instantiating GTP processor $className...", SNAP_LOG_ERROR);
        if(class_exists($className)) {
            return new $className;
        }
        return new self;
    }

    public function setCalledFromHttp(bool $value) {
        $this->calledFromHttp = $value;
    }

    public function getCalledFromHttp() {return $this->calledFromHttp;}

    /**
     * Main method to process the incoming request.  Implemented class can get relevant
     * information about the action to be taken etc from the apiParam and then call the
     * appropriate manager to execute the main business logics.
     * 
     * @param  App                      $app           App Class
     * @param  \Snap\api\param\ApiParam $apiParam      Api parameter object containing the decoded data
     * @param  array                    $decodedData   Decoded and converted data
     * @param  array                    $requestParams Original raw data from the API request
     * @return \Snap\api\param\ApiParam     The response type represented as apiParams.
     */
    public function process($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                    'action_type' => $apiParam->getActionType(),
                    'processor_class' => __CLASS__]);
    }

    /**
     * Helper factory method to create and return api param object
     * @param  array  $requestParams        The input parameters to get version information
     * @param  string $responseActionType   Desired output response format
     * @param  string $responseParams       Data to be formatted
     * @return ApiParam                     Created object.
     */
    protected function createOutputApiParam($responseActionType, $requestParams, $responseParams)
    {
        $outputApiParam = \Snap\api\param\GtpApiParam::getInstance($requestParams['version']);
        $outputApiParam->setActionType($responseActionType);
        $formattedResponse = $outputApiParam->encode($this->app, $responseActionType, $responseParams, $requestParams);
        return $formattedResponse;
    }

    /**
     * Disallow instantiation of the class through new.
     */
    private function __construct()
    {
    }
}
?>