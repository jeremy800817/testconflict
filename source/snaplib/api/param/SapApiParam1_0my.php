<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */
Namespace Snap\api\param;

use Snap\api\param\converter\ApiParamConverter;
use Snap\api\param\extractor\ApiParamExtractor;
use Snap\api\param\extractor\SapApiParamExtractor;
use Snap\api\param\validator\ApiParamValidator;
use Snap\api\param\validator\SapApiParamValidator;

/**
 * This class specifically provide overrides for GTP api protocol.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class SapApiParam1_0my extends ApiParam
{
    const ACTION_CONVERSION = "conversion";
    const ACTION_SPOTBUY    = "spotbuy";
    const ACTION_SPOTSELL   = "spotsell";
    const ACTION_CANCELPO   = "cancelpo";
    const ACTION_ADMINFEE   = "adminfee";
    const ACTION_STORAGEFEE = "storagefee";
    const ACTION_BUYINVOICE = "buy_invoice";
    const ACTION_SELLINVOICE= "sell_invoice";
    const ACTION_GOLDRETURN = "goldreturn";

    public function __construct()
    {
        // Conversion
        $this->registerParameter(self::ACTION_CONVERSION, 'id', '', null, 'constant|0|int');
        $this->registerParameter(self::ACTION_CONVERSION, 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter(self::ACTION_CONVERSION, 'serialNum', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter(self::ACTION_CONVERSION, 'unitPrice', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'whsCode', '', null, 'fromResult|whsCode');
        $this->registerParameter(self::ACTION_CONVERSION, 'action', '', null, 'constant|redeem_ace');
        $this->registerParameter(self::ACTION_CONVERSION, 'bankId', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter(self::ACTION_CONVERSION, 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter(self::ACTION_CONVERSION, 'success', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'message', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'data1', '', null, 'fromResult|datetosend');
        $this->registerParameter(self::ACTION_CONVERSION, 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter(self::ACTION_CONVERSION, 'data3', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_CONVERSION, 'arguments', '', null, 'constant|__null__');

        // Transaction
        foreach ([self::ACTION_SPOTBUY, self::ACTION_SPOTSELL] as $action) {
            if($action == self::ACTION_SPOTBUY) $actionSap = 'buy';
            else $actionSap = 'sell';
            $this->registerParameter($action, 'id', '', null, 'constant|0|int');
            $this->registerParameter($action, 'postingDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'deliveryDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'documentDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'itemCode', '', null, 'fromResult|itemCode');
            $this->registerParameter($action, 'serialNum', '', null, 'constant|__null__');
            $this->registerParameter($action, 'quantity', '', null, 'fromResult|quantity'); //xau gram
            $this->registerParameter($action, 'unitPrice', '', null, 'fromResult|finalprice');
            $this->registerParameter($action, 'whsCode', '', null, 'constant|__null__');
            $this->registerParameter($action, 'action', '', null, 'constant|'.$actionSap);
            $this->registerParameter($action, 'bankId', '', null, 'constant|__null__');
            $this->registerParameter($action, 'customerId', '', null, 'fromResult|customerId');
            $this->registerParameter($action, 'refNo', '', null, 'fromResult|refNo'); 
            $this->registerParameter($action, 'success', '', null, 'constant|__null__');
            $this->registerParameter($action, 'message', '', null, 'constant|__null__');
            $this->registerParameter($action, 'createdDate', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data1', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data2', '', null, 'fromResult|partnerrefid');
            $this->registerParameter($action, 'data3', '', null, 'constant|__null__');
            $this->registerParameter($action, 'arguments', '', null, 'constant|__null__');
        }

        // Admin Fee & Storage Fee
        foreach ([self::ACTION_BUYINVOICE, self::ACTION_SELLINVOICE] as $action) {
            $this->registerParameter($action, 'id', '', null, 'constant|0|int');
            $this->registerParameter($action, 'postingDate', '', null, 'fromResult|PostingDate');
            $this->registerParameter($action, 'deliveryDate', '', null, 'fromResult|DeliveryDate');
            $this->registerParameter($action, 'documentDate', '', null, 'fromResult|DocumentDate');
            $this->registerParameter($action, 'itemCode', '', null, 'fromResult|itemCode');
            $this->registerParameter($action, 'serialNum', '', null, 'constant|__null__');
            $this->registerParameter($action, 'quantity', '', null, 'fromResult|quantity'); //xau gram
            $this->registerParameter($action, 'unitPrice', '', null, 'fromResult|unitPrice');
            $this->registerParameter($action, 'whsCode', '', null, 'constant|__null__');
            $this->registerParameter($action, 'action', '', null, 'constant|'.$action);
            $this->registerParameter($action, 'bankId', '', null, 'constant|__null__');
            $this->registerParameter($action, 'customerId', '', null, 'fromResult|customerId');
            $this->registerParameter($action, 'refNo', '', null, 'fromResult|refNo'); 
            $this->registerParameter($action, 'success', '', null, 'constant|__null__');
            $this->registerParameter($action, 'message', '', null, 'constant|__null__');
            $this->registerParameter($action, 'createdDate', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data1', '', null, 'fromResult|data1');
            $this->registerParameter($action, 'data2', '', null, 'fromResult|data2');
            $this->registerParameter($action, 'data3', '', null, 'constant|__null__');
            $this->registerParameter($action, 'arguments', '', null, 'constant|__null__');
        }

        //redemption reverse
        $this->registerParameter('redemptionreversal', 'action', '', null, 'constant|redeem_ace');
        $this->registerParameter('redemptionreversal', 'absEntry', '', null, 'fromResult|absEntry');
        $this->registerParameter('redemptionreversal', 'refNo', '', null, 'fromResult|refNo');
        $this->registerParameter('redemptionreversal', 'success', '', null, 'constant|__null__');
        $this->registerParameter('redemptionreversal', 'message', '', null, 'constant|__null__');
        $this->registerParameter('redemptionreversal', 'createdDate', '', null, 'constant|__null__'); 
        $this->registerParameter('redemptionreversal', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('redemptionreversal', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('redemptionreversal', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('redemptionreversal', 'arguments', '', null, 'constant|__null__');
        //redemption reverse end

        //Get shared minted list
        $this->registerParameter('sharedminted', 'id', '', null, 'constant|0|int');
        //$this->registerParameter('whslist', 'customerId', '', null, 'constant|MIB');
        $this->registerParameter('sharedminted', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('sharedminted', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('sharedminted', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('sharedminted', 'data2', '', null, 'constant|__null__');
        $this->registerParameter('sharedminted', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('sharedminted', 'arguments', '', null, 'constant|__null__');
        //Get shared minted list

        /*SAP RECONCILE*/
        foreach(['documentporequest' => 'PO', 
                'documentsorequest' => 'SO'] as $action => $actionCode) {
            $this->registerParameter($action, 'docType', '', null, 'constant|'.$actionCode);
            $this->registerParameter($action, 'docNum', '', null, 'constant|__null__');
            $this->registerParameter($action, 'customerId', '', null, 'fromResult|customerId');
            $this->registerParameter($action, 'cardCode', '', null, 'constant|__null__');
            $this->registerParameter($action, 'docDateFrom', '', null, 'fromResult|docDateFrom');
            $this->registerParameter($action, 'docDateTo', '', null, 'fromResult|docDateTo');
            $this->registerParameter($action, 'data1', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data2', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data3', '', null, 'constant|__null__');
            $this->registerParameter($action, 'arguments', '', null, 'constant|__null__');
        }
        /**/

        // Register our endpoint for SAP
        $this->registerGtpEndpoints();
    }

    protected function registerGtpEndpoints()
    {
        //New Serial request
        $this->registerParameter('newserial', 'Id', 'numeric', null, '');
        $this->registerParameter('newserial', 'itemCode', 'required;productSapCode', 'toProduct', '');
        $this->registerParameter('newserial', 'serialNum', 'required;string', '', '');
        $this->registerParameter('newserial', 'whsCode', 'required;string', '', '');
        $this->registerParameter('newserial', 'bankId', '', null, 'constant|__null__');
        $this->registerParameter('newserial', 'DoDocNum', '', '', '');
        $this->registerParameter('newserial', 'customerId', 'required;partnerSapCode', 'toPartner', '');
        $this->registerParameter('newserial', 'createdDate', '', null, 'constant|__null__');

        $this->registerParameter('newserialresponse', 'Id', 'numeric', null, 'fromRequest|Id');
        $this->registerParameter('newserialresponse', 'itemCode', 'required;partnerCode;', null, 'fromObject|product|sapitemcode');
        $this->registerParameter('newserialresponse', 'serialNum', '', null, 'fromRequest|serialNum');
        $this->registerParameter('newserialresponse', 'whsCode', '', null, 'fromRequest|whsCode');
        $this->registerParameter('newserialresponse', 'bankId', '', null, 'constant|__null__');
        $this->registerParameter('newserialresponse', 'DoDocNum', '', null, 'toDoDocNumVerify|DoDocNum');
        $this->registerParameter('newserialresponse', 'customerId', '', null, 'fromObject|partner|sapcompanybuycode1');
        $this->registerParameter('newserialresponse', 'success', '', null, 'fromResult|success');
        $this->registerParameter('newserialresponse', 'message', '', null, 'fromResult|message');
        $this->registerParameter('newserialresponse', 'createdDate', '', '', 'toCurrentTime');
        //New Serial request end

        //Receive gold item request
        $this->registerParameter('goldbar_receive', 'Id', 'numeric', null, '');
        $this->registerParameter('goldbar_receive', 'itemCode', 'required;productSapCode', 'toProduct', '');
        $this->registerParameter('goldbar_receive', 'serialNum', 'required;string', '', '');
        $this->registerParameter('goldbar_receive', 'whsCode', 'required;string', '', '');
        $this->registerParameter('goldbar_receive', 'bankId', '', null, 'constant|__null__');
        $this->registerParameter('goldbar_receive', 'DoDocNum', '', '', '');
        $this->registerParameter('goldbar_receive', 'customerId', 'required;partnerSapCode', 'toPartner', '');
        $this->registerParameter('goldbar_receive', 'createdDate', '', null, 'constant|__null__');

        $this->registerParameter('goldbar_receiveresponse', 'Id', '', null, 'fromRequest|Id');
        $this->registerParameter('goldbar_receiveresponse', 'itemCode', '', null, 'fromObject|product|sapitemcode');
        $this->registerParameter('goldbar_receiveresponse', 'serialNum', '', null, 'fromRequest|serialNum');
        $this->registerParameter('goldbar_receiveresponse', 'whsCode', '', null, 'fromRequest|whsCode');
        $this->registerParameter('goldbar_receiveresponse', 'bankId', '', null, 'constant|__null__');
        $this->registerParameter('goldbar_receiveresponse', 'DoDocNum', '', null, 'toDoDocNumVerify|DoDocNum');
        $this->registerParameter('goldbar_receiveresponse', 'customerId', '', null, 'fromObject|partner|sapcompanybuycode1');
        $this->registerParameter('goldbar_receiveresponse', 'success', '', null, 'fromResult|success');
        $this->registerParameter('goldbar_receiveresponse', 'message', '', null, 'fromResult|message');
        $this->registerParameter('goldbar_receiveresponse', 'createdDate', '', '', 'toCurrentTime');
        //Receive gold item request

        //vaultitemrequest
        $this->registerParameter('vaultitemrequest', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('vaultitemrequest', 'customerId', 'required;partnerSapCode', 'toPartner', '');
        $this->registerParameter('vaultitemrequest', 'createdDate', '', null, 'constant|__null__');

        $this->registerParameter('vaultitemrequestresponse', 'Id', '', null, 'fromResult|count');
        $this->registerParameter('vaultitemrequestresponse', 'serialNum', '', null, 'fromResult|serialnumber');
        $this->registerParameter('vaultitemrequestresponse', 'whsCode', '', null, 'fromResult|location');
        $this->registerParameter('vaultitemrequestresponse', 'customerId', '', null, 'fromRequest|customerId');
        $this->registerParameter('vaultitemrequestresponse', 'DoDocNum', '', null, 'fromResult|deliveryordernumber');
        $this->registerParameter('vaultitemrequestresponse', 'success', '', null, 'fromResult|success');
        $this->registerParameter('vaultitemrequestresponse', 'message', '', null, 'fromResult|message');
        $this->registerParameter('vaultitemrequestresponse', 'createdDate', '', '', 'toCurrentTime');
        //vaultitemrequest

        //Return – Back IN to SN table from Bank
        $this->registerParameter(self::ACTION_GOLDRETURN, 'id', '', null, 'constant|0|int');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'itemCode', '', null, 'fromResult|product');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'serialnum', '', null, 'fromObject|vaultItem|serialno');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'quantity', '', null, 'fromObject|vaultItem|weight');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'unitPrice', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'whsCode', '', null, 'fromResult|whsCode'); //constant|WHQ
        $this->registerParameter(self::ACTION_GOLDRETURN, 'action', '', null, 'constant|gold_return');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'bankId', '', null, 'constant|__null__'); //'fromObject|partner|'.$sapCode
        $this->registerParameter(self::ACTION_GOLDRETURN, 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'refNo', '', null, 'fromResult|refNo');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'success', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'message', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'completed', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'data1', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'data2', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'data3', '', null, 'constant|__null__');
        $this->registerParameter(self::ACTION_GOLDRETURN, 'arguments', '', null, 'constant|__null__');
        //Return – Back IN to SN table from Bank
    }

    /**
     * Returns the validator that is to be used for this class
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiPAramValidator
     */
    protected function getValidator($app) : ApiParamValidator
    {
        if(!$this->validator) {
            $this->validator = new SapApiParamValidator($app);
        }
        return $this->validator;
    }

    /**
     * Returns the converter that will be used to translate api parameters into objects
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiParamConveter
     */
    protected function getConverter($app) : ApiParamConverter
    {
        if(!$this->conveterr) {
            $this->conveter = new \Snap\api\param\converter\SapApiParamConverter($app);
        }
        return $this->conveter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  param\validator\App    $app     App object
     * @param  param\converter\string $config  param\validator\Configuration to be used for this validator
     * @return param\extractor\SapApiParamExtractorparam\converter\
     */
    protected function getExtractor($app) : ApiParamExtractor
    {
        if(!$this->extractor) {
            $this->extractor = new SapApiParamExtractor($app);
        }
        return $this->extractor;
    }

    public function decodeActionType($params)
    {
        if(isset($this->paramsMap[$params['action']])) {
            return $params['action'];
        }
        return null;
    }
}