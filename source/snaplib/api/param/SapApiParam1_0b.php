<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\object\Partner;
Use Snap\object\Order;
Use Snap\object\FutureOrder;
Use Snap\api\param\ApiParam;
Use Snap\api\param\validator\ApiParamValidator;
Use Snap\api\param\validator\SapApiParamValidator;
Use Snap\api\param\converter\ApiParamConverter;
Use Snap\api\param\converter\SapApiParamConverter;
Use Snap\api\param\extractor\ApiParamExtractor;
Use Snap\api\param\extractor\SapApiParamExtractor;

/**
 * This class specifically provide overrides for GTP api protocol.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class SapApiParam1_0b extends ApiParam
{
    public function __construct() { 
       foreach(['companyBuyDigitalOrder' => 'sapcompanybuycode1', 
                'companySellDigitalOrder' => 'sapcompanysellcode1'] as $action => $sapCode) {
            if($action == 'companyBuyDigitalOrder') $actionSap = 'buy';
            else $actionSap = 'sell';
            $this->registerParameter($action, 'id', '', null, 'constant|0|int');
            $this->registerParameter($action, 'postingDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'deliveryDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'documentDate', '', null, 'fromResult|datetosend');
            // 06/06/2023 - due to itemcode does not come from product object,
            // adjust the code so that the itemCode will receive directly from result instead.
            // $this->registerParameter($action, 'itemCode', '', null, 'fromObject|product|sapitemcode');
            $this->registerParameter($action, 'itemCode', '', null, 'fromResult|itemCode');
            $this->registerParameter($action, 'serialNum', '', null, 'constant|__null__');
            $this->registerParameter($action, 'quantity', '', null, 'fromObject|order|xau');
            $this->registerParameter($action, 'unitPrice', '', null, 'fromObject|order|price');
            $this->registerParameter($action, 'whsCode', '', null, 'constant|__null__'); //constant|WHQ
            $this->registerParameter($action, 'action', '', null, 'constant|'.$actionSap);
            $this->registerParameter($action, 'bankId', '', null, 'constant|__null__'); //'fromObject|partner|'.$sapCode
            $this->registerParameter($action, 'customerId', '', null, 'fromResult|customerId');
            // $this->registerParameter($action, 'sapcodes', '', null, 'digitalGoldCustomerCode|partner|'.$sapCode);
            $this->registerParameter($action, 'refNo', '', null, 'fromObject|order|orderno');
            $this->registerParameter($action, 'success', '', null, 'constant|__null__');
            $this->registerParameter($action, 'message', '', null, 'constant|__null__');
            $this->registerParameter($action, 'createdDate', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data1', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data2', '', null, 'toPartnerRefId|order');
            $this->registerParameter($action, 'data3', '', null, 'constant|__null__');
            $this->registerParameter($action, 'arguments', '', null, 'constant|__null__');
        }

        //reverseorder
        foreach(['buy_cancel', 'sell_cancel'] as $action) {
            $this->registerParameter($action, 'id', '', null, 'constant|0|int');
            $this->registerParameter($action, 'postingDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'deliveryDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'documentDate', '', null, 'fromResult|datetosend');
            $this->registerParameter($action, 'itemCode', '', null, 'constant|__null__');
            $this->registerParameter($action, 'serialNum', '', null, 'constant|__null__');
            $this->registerParameter($action, 'quantity', '', null, 'constant|__null__');
            $this->registerParameter($action, 'unitPrice', '', null, 'constant|__null__');
            $this->registerParameter($action, 'whsCode', '', null, 'constant|__null__');
            $this->registerParameter($action, 'action', '', null, 'constant|'.$action);
            $this->registerParameter($action, 'bankId', '', null, 'constant|__null__');
            $this->registerParameter($action, 'customerId', '', null, 'fromResult|customerId');
            $this->registerParameter($action, 'refNo', '', null, 'fromObject|order|orderno');
            $this->registerParameter($action, 'success', '', null, 'constant|__null__');
            $this->registerParameter($action, 'message', '', null, 'constant|__null__');
            $this->registerParameter($action, 'createdDate', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data1', '', null, 'constant|__null__');
            $this->registerParameter($action, 'data2', '', null, 'toPartnerRefId|order');
            $this->registerParameter($action, 'data3', '', null, 'constant|__null__');
            $this->registerParameter($action, 'arguments', '', null, 'constant|__null__');
        }

        //REDEMPTION collect at branch
        $this->registerParameter('redemptionbranch', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('redemptionbranch', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('redemptionbranch', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('redemptionbranch', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('redemptionbranch', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('redemptionbranch', 'action', '', null, 'constant|redeem_bank');
        $this->registerParameter('redemptionbranch', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('redemptionbranch', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('redemptionbranch', 'refNo', '', null, 'fromResult|refNo');
        $this->registerParameter('redemptionbranch', 'success', '', null, 'constant|__null__');
        $this->registerParameter('redemptionbranch', 'message', '', null, 'constant|__null__');
        $this->registerParameter('redemptionbranch', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('redemptionbranch', 'data1', '', null, 'fromResult|datetosend');
        $this->registerParameter('redemptionbranch', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('redemptionbranch', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('redemptionbranch', 'arguments', '', null, 'constant|__null__');
        //REDEMPTION collect at branch

        //reserve serial number for redemption purpose
        $this->registerParameter('reserveserialnum', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('reserveserialnum', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('reserveserialnum', 'quantity', '', null, 'fromResult|quantity');
        $this->registerParameter('reserveserialnum', 'whsCode', '', null, 'fromResult|whsCode');
        $this->registerParameter('reserveserialnum', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('reserveserialnum', 'customerId', '', null, 'fromResult|customerId');

        //unreserve serial number for redemption purpose
        $this->registerParameter('unreserveserialnum', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('unreserveserialnum', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('unreserveserialnum', 'quantity', '', null, 'fromResult|quantity');
        $this->registerParameter('unreserveserialnum', 'whsCode', '', null, 'fromResult|whsCode');
        $this->registerParameter('unreserveserialnum', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('unreserveserialnum', 'customerId', '', null, 'fromResult|customerId');

        //REDEMPTION deliver
        $this->registerParameter('redemptiondelivery', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('redemptiondelivery', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('redemptiondelivery', 'serialNum', '', null, 'constant|__null__');
        $this->registerParameter('redemptiondelivery', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('redemptiondelivery', 'whsCode', '', null, 'constant|BG_MINT');
        $this->registerParameter('redemptiondelivery', 'action', '', null, 'constant|redeem_ace');
        // $this->registerParameter('redemptiondelivery', 'action', '', null, 'constant|BURSA');
        $this->registerParameter('redemptiondelivery', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('redemptiondelivery', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('redemptiondelivery', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('redemptiondelivery', 'success', '', null, 'constant|__null__');
        $this->registerParameter('redemptiondelivery', 'message', '', null, 'constant|__null__');
        $this->registerParameter('redemptiondelivery', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('redemptiondelivery', 'data1', '', null, 'fromResult|datetosend');
        $this->registerParameter('redemptiondelivery', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('redemptiondelivery', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('redemptiondelivery', 'arguments', '', null, 'constant|__null__');       
        //REDEMPTION deliver

        /*20200817 additional redemption api. Please delete this comment when this function already used.*/
        $this->registerParameter('redemptionspdelivery', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('redemptionspdelivery', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('redemptionspdelivery', 'serialNum', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('redemptionspdelivery', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'action', '', null, 'constant|redeem_ace');
        $this->registerParameter('redemptionspdelivery', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('redemptionspdelivery', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('redemptionspdelivery', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('redemptionspdelivery', 'success', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'message', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'data1', '', null, 'fromResult|datetosend');
        $this->registerParameter('redemptionspdelivery', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('redemptionspdelivery', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('redemptionspdelivery', 'arguments', '', null, 'constant|__null__');       

        $this->registerParameter('redemptionpreappointment', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('redemptionpreappointment', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('redemptionpreappointment', 'serialNum', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('redemptionpreappointment', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'action', '', null, 'constant|redeem_ace');
        $this->registerParameter('redemptionpreappointment', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('redemptionpreappointment', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('redemptionpreappointment', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('redemptionpreappointment', 'success', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'message', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'data1', '', null, 'fromResult|datetosend');
        $this->registerParameter('redemptionpreappointment', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('redemptionpreappointment', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('redemptionpreappointment', 'arguments', '', null, 'constant|__null__');       
        /*20200817 additional redemption api. Please delete this comment when this function already used.END*/

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

        //BUYBACK
        $this->registerParameter('buybackminted', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('buybackminted', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('buybackminted', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('buybackminted', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('buybackminted', 'unitPrice', '', null, 'fromResult|unitPrice'); //xau gram
        $this->registerParameter('buybackminted', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('buybackminted', 'action', '', null, 'constant|buyback');
        $this->registerParameter('buybackminted', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('buybackminted', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('buybackminted', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('buybackminted', 'success', '', null, 'constant|__null__');
        $this->registerParameter('buybackminted', 'message', '', null, 'constant|__null__');
        $this->registerParameter('buybackminted', 'createdDate', '', null, 'constant|__null__'); 
        $this->registerParameter('buybackminted', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('buybackminted', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('buybackminted', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('buybackminted', 'arguments', '', null, 'constant|__null__');  
        //END BUYBACK

        //buyback reverse
        $this->registerParameter('buybackreversal', 'action', '', null, 'constant|buyback');
        $this->registerParameter('buybackreversal', 'absEntry', '', null, 'fromResult|absEntry');
        $this->registerParameter('buybackreversal', 'refNo', '', null, 'fromResult|refNo');
        $this->registerParameter('buybackreversal', 'success', '', null, 'constant|__null__');
        $this->registerParameter('buybackreversal', 'message', '', null, 'constant|__null__');
        $this->registerParameter('buybackreversal', 'createdDate', '', null, 'constant|__null__'); 
        $this->registerParameter('buybackreversal', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('buybackreversal', 'data2', '', null, 'fromResult|partnerrefid');
        $this->registerParameter('buybackreversal', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('buybackreversal', 'arguments', '', null, 'constant|__null__');
        //buyback reverse end

        //minted gold request tf_request
        $this->registerParameter('tfreplenish', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('tfreplenish', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('tfreplenish', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('tfreplenish', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('tfreplenish', 'unitPrice', '', null, 'constant|0|int'); //xau gram
        $this->registerParameter('tfreplenish', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'action', '', null, 'constant|tf_replenish');
        $this->registerParameter('tfreplenish', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('tfreplenish', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('tfreplenish', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('tfreplenish', 'success', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'message', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'createdDate', '', null, 'constant|__null__'); 
        $this->registerParameter('tfreplenish', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'data2', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('tfreplenish', 'arguments', '', null, 'constant|__null__');
        //end minted gold request tf_request

        //minted gold request tf_return
        $this->registerParameter('tfreturn', 'Id', '', null, 'constant|0|int');
        $this->registerParameter('tfreturn', 'itemCode', '', null, 'fromResult|itemCode');
        $this->registerParameter('tfreturn', 'serialNum', '', null, 'fromResult|serialNum');
        $this->registerParameter('tfreturn', 'quantity', '', null, 'fromResult|quantity'); //xau gram
        $this->registerParameter('tfreturn', 'unitPrice', '', null, 'constant|0|int'); //xau gram
        $this->registerParameter('tfreturn', 'whsCode', '', null, 'constant|__null__');
        $this->registerParameter('tfreturn', 'action', '', null, 'constant|tf_return');
        $this->registerParameter('tfreturn', 'bankId', '', null, 'fromResult|bankId');
        $this->registerParameter('tfreturn', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('tfreturn', 'refNo', '', null, 'fromResult|refNo'); 
        $this->registerParameter('tfreturn', 'success', '', null, 'constant|__null__');
        $this->registerParameter('tfreturn', 'message', '', null, 'constant|__null__');
        $this->registerParameter('tfreturn', 'createdDate', '', null, 'constant|__null__'); 
        $this->registerParameter('tfreturn', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('tfreturn', 'data2', '', null, 'constant|__null__');
        $this->registerParameter('tfreturn', 'data3', '', null, 'fromResult|returnReason');
        $this->registerParameter('tfreturn', 'arguments', '', null, 'constant|__null__');
        //end minted gold request tf_return

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

        //Return – Back IN to SN table from Bank
        $this->registerParameter('goldreturn', 'id', '', null, 'constant|0|int');
        $this->registerParameter('goldreturn', 'itemCode', '', null, 'fromResult|product');
        $this->registerParameter('goldreturn', 'serialnum', '', null, 'fromObject|vaultItem|serialno');
        $this->registerParameter('goldreturn', 'quantity', '', null, 'fromObject|vaultItem|weight');
        $this->registerParameter('goldreturn', 'unitPrice', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'whsCode', '', null, 'fromResult|whsCode'); //constant|WHQ
        $this->registerParameter('goldreturn', 'action', '', null, 'constant|gold_return');
        $this->registerParameter('goldreturn', 'bankId', '', null, 'constant|__null__'); //'fromObject|partner|'.$sapCode
        $this->registerParameter('goldreturn', 'customerId', '', null, 'fromResult|customerId');
        $this->registerParameter('goldreturn', 'refNo', '', null, 'fromResult|refNo');
        $this->registerParameter('goldreturn', 'success', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'message', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'completed', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'data2', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('goldreturn', 'arguments', '', null, 'constant|__null__');
        //Return – Back IN to SN table from Bank

        //Synchronize base on Serial Table# (Periodic, Eg Daily)
        $this->registerParameter('synchronizesn', 'id', '', null, 'constant|0|int');
        $this->registerParameter('synchronizesn', 'itemCode', '', null, 'fromObject|product|sapitemcode');
        $this->registerParameter('synchronizesn', 'serialNumFrom', '', null, '');
        $this->registerParameter('synchronizesn', 'serialNumTo', '', null, '');
        $this->registerParameter('synchronizesn', 'whsCodeFrom', '', null, '');
        $this->registerParameter('synchronizesn', 'whsCodeTo', '', null, '');
        $this->registerParameter('synchronizesn', 'createdDate', '', null, 'constant|__null__');
        //Synchronize base on Serial Table# (Periodic, Eg Daily)

        //Get item list
        $this->registerParameter('stocklist', 'id', '', null, 'constant|0|int');
        //$this->registerParameter('stocklist', 'customerId', '', null, 'constant|MIB');
        $this->registerParameter('stocklist', 'customerId', '', null, 'fromObject|partner|sapcompanybuycode1');
        $this->registerParameter('stocklist', 'createdDate', '', null, 'constant|__null__');
        //Get item list

        //Get warehouse list
        $this->registerParameter('whslist', 'id', '', null, 'constant|0|int');
        //$this->registerParameter('whslist', 'customerId', '', null, 'constant|MIB');
        $this->registerParameter('whslist', 'customerId', '', null, 'fromObject|partner|sapcompanybuycode1');
        $this->registerParameter('whslist', 'createdDate', '', null, 'constant|__null__');
        $this->registerParameter('whslist', 'data1', '', null, 'constant|__null__');
        $this->registerParameter('whslist', 'data2', '', null, 'constant|__null__');
        $this->registerParameter('whslist', 'data3', '', null, 'constant|__null__');
        $this->registerParameter('whslist', 'arguments', '', null, 'constant|__null__');
        //Get warehouse list

        //minted gold request tf_request
        $this->registerParameter('replenishmentreply', 'Id', 'numeric', null, '');
        $this->registerParameter('replenishmentreply', 'itemCode', 'required;productSapCode', 'toProduct', '');
        $this->registerParameter('replenishmentreply', 'serialNum', 'required;string', '', '');
        $this->registerParameter('replenishmentreply', 'whsCode', 'required;string', '', '');
        $this->registerParameter('replenishmentreply', 'bankId', 'required;;branchSapCode', 'toBranch', '');
        $this->registerParameter('replenishmentreply', 'customerId', 'required;partnerSapCode', 'toPartner', '');
        $this->registerParameter('replenishmentreply', 'refNo', '', '', '');
        $this->registerParameter('replenishmentreply', 'createdDate', '', null, 'constant|__null__');

        $this->registerParameter('replenishmentreplyresponse', 'Id', '', null, 'fromRequest|Id');
        $this->registerParameter('replenishmentreplyresponse', 'itemCode', '', null, 'fromObject|product|sapitemcode');
        $this->registerParameter('replenishmentreplyresponse', 'serialNum', '', null, 'fromRequest|serialNum');
        $this->registerParameter('replenishmentreplyresponse', 'whsCode', '', null, 'fromRequest|whsCode');
        $this->registerParameter('replenishmentreplyresponse', 'bankId', '', null, 'fromRequest|bankId');
        $this->registerParameter('replenishmentreplyresponse', 'customerId', '', null, 'fromRequest|customerId');
        $this->registerParameter('replenishmentreplyresponse', 'refNo', '', null, 'fromRequest|refNo');
        $this->registerParameter('replenishmentreplyresponse', 'success', '', null, 'fromResult|success');
        $this->registerParameter('replenishmentreplyresponse', 'message', '', null, 'fromResult|message');
        $this->registerParameter('replenishmentreplyresponse', 'createdDate', '', '', 'toCurrentTime');
        //minted gold request tf_request

        //minted gold request completed
        $this->registerParameter('replenishmentcomplete', 'Id', 'numeric', null, '');
        $this->registerParameter('replenishmentcomplete', 'itemCode', 'required;productSapCode', 'toProduct', '');
        $this->registerParameter('replenishmentcomplete', 'serialNum', 'required;string', '', '');
        $this->registerParameter('replenishmentcomplete', 'whsCode', 'required;string', '', '');
        $this->registerParameter('replenishmentcomplete', 'bankId', 'required;string', '', '');
        $this->registerParameter('replenishmentcomplete', 'customerId', 'required;partnerSapCode', 'toPartner', '');
        $this->registerParameter('replenishmentcomplete', 'refNo', '', '', '');
        $this->registerParameter('replenishmentcomplete', 'createdDate', '', null, 'constant|__null__');

        $this->registerParameter('replenishmentcompleteresponse', 'Id', '', null, 'fromRequest|Id');
        $this->registerParameter('replenishmentcompleteresponse', 'itemCode', '', null, 'fromObject|product|sapitemcode');
        $this->registerParameter('replenishmentcompleteresponse', 'serialNum', '', null, 'fromRequest|serialNum');
        $this->registerParameter('replenishmentcompleteresponse', 'whsCode', '', null, 'fromRequest|whsCode');
        $this->registerParameter('replenishmentcompleteresponse', 'bankId', '', null, 'fromRequest|bankId');
        $this->registerParameter('replenishmentcompleteresponse', 'customerId', '', null, 'fromRequest|customerId');
        $this->registerParameter('replenishmentcompleteresponse', 'refNo', '', null, 'fromRequest|refNo');
        $this->registerParameter('replenishmentcompleteresponse', 'success', '', null, 'fromResult|success');
        $this->registerParameter('replenishmentcompleteresponse', 'message', '', null, 'fromResult|message');
        $this->registerParameter('replenishmentcompleteresponse', 'createdDate', '', '', 'toCurrentTime');
        //minted gold request completed

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
?>
