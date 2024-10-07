<?php

namespace Snap\api\sap;

use Snap\api\param\SapApiParam1_0my;
use Snap\object\Redemption;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 04-Jan-2021
*/

class SapApiProcessor1_0my extends SapApiProcessor1_0
{
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
        $action = $requestParams['action'];

        $methodName = 'onReceive' . $action;
        if (method_exists($this, $methodName)) {
            return call_user_func_array([$this, $methodName], [$app, $apiParam, $decodedData, $requestParams]);
        }
        throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
            'action_type' => $apiParam->getActionType(),
            'processor_class' => __CLASS__
        ]);
    }

    public function notifyNewOrder($app, $order)
    {
        $url = $app->getConfig()->{'gtp.sap.dgneworder.url'};
        $action = ($order->isCompanyBuy() ? SapApiParam1_0my::ACTION_SPOTBUY : SapApiParam1_0my::ACTION_SPOTSELL);

        $product = $app->productStore()->getByField('id',$order->productid);

        //need to get from partner because GoGold have different add name in product DG-999-9
        $partner = $app->partnerStore()->getByField('id', $order->partnerid);
        if($action == SapApiParam1_0my::ACTION_SPOTBUY) $partnersapcode = $partner->sapcompanybuycode1;
        else $partnersapcode = $partner->sapcompanysellcode1; 

        $mypartner = $app->mypartnersettingStore()->getByField('partnerid', $order->partnerid);
        $productAddName = $product->sapitemcode."-".$mypartner->sapdgcode;

        $pricegold      = $order->price;
        $discount       = $order->discountprice != 0 ? $order->discountprice : 0;
        $finalprice     = $pricegold + ($discount);

        $decodedData['order']           = $order;
        $decodedData['product']         = $order->getProduct();
        $decodedData['partner']         = $order->getPartner();
        $decodedData['quantity']        = (float)$order->xau;
        $decodedData['itemCode']        = $productAddName;
        $decodedData['customerId']      = $partnersapcode;
        $decodedData['refNo']           = $order->orderno;
        $decodedData['partnerrefid']    = (string)$order->partnerrefid;
        $decodedData['datetosend']      = $order->createdon->format('Y-m-d\TH:i:s').'+08:00';
        $decodedData['finalprice']      = $finalprice;

        $responseParams = $this->createOutputApiParam($action, ['version' => $order->apiversion], $decodedData);
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
        $this->log(__METHOD__."({$order->orderno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
        $data = $sender->response($app, $responseParams, ['url' => $url]);
        $this->log(__METHOD__."({$order->orderno}) SAP HTTP send END.", SNAP_LOG_DEBUG);
        return $data;
    }

    public function notifyNewRedemptionDelivery($app, $redemption)
    {
        /*$now                = new \DateTime('now', $app->getUserTimezone());
        $currentdatetime    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone());
        $dateToSend         = $currentdatetime->format('Y-m-d\TH:i:s').'+08:00';*/
        $url = $app->getConfig()->{'gtp.sap.redemption.url'};
        $action = SapApiParam1_0my::ACTION_CONVERSION;
        $items = json_decode($redemption->items);
        $redemptionArray = [];
        $i = 1;
        foreach ($items as $aItem) {
            $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
            $mypartner = $app->mypartnersettingStore()->getByField('partnerid', $redemption->partnerid);
            $partner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
            //$xauquantity = str_replace("g"," ",$aItem->denomination);
            if ($aItem->quantity > 1) {
                for ($j = 1; $j <= $aItem->quantity; $j++) {
                    $aRedemption['itemCode'] = $product->sapitemcode;
                    $aRedemption['bankId'] = ($redemption->type == Redemption::TYPE_APPOINTMENT ? $redemption->appointmentbranchid : 'null');
                    $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                    //$aRedemption['quantity'] = (int)$xauquantity;
                    $aRedemption['quantity'] = (float)$aItem->denomination;
                    $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                    /*added for dynamic value*/
                    $aRedemption['whsCode'] = $mypartner->sapmintedwhs;
                    $aRedemption['customerId'] = $sapcustomerid;
                    $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                    /**/
                    $i++;
                    $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                }
            } else {
                $aRedemption['itemCode'] = $product->sapitemcode;
                $aRedemption['bankId'] = ($redemption->type == Redemption::TYPE_APPOINTMENT ? $redemption->appointmentbranchid : 'null');
                $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                //$aRedemption['quantity'] = (int)$xauquantity;
                $aRedemption['quantity'] = (float)$aItem->denomination;
                $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                /*added for dynamic value*/
                $aRedemption['whsCode'] = $mypartner->sapmintedwhs;
                $aRedemption['customerId'] = $sapcustomerid;
                $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                /**/
                $i++;
                $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
            }
        }
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
        $data = $sender->response($app, $responseParams, ['url' => $url]);
        return $data;
    }

    public function notifyFeeCharge($app, $partner, $fee, $xau, $type, $requestParams, $category, $partnersapsetting)
    {
        //if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
        //$type = admin_fee/storage_fee/conversion_fee/processing_fee/
        $url            = $app->getConfig()->{'gtp.sap.misctransaction.url'};

        if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
        $mypartner      = $app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $adminfee       = $mypartner->adminfeeperannum;
        $storagefee     = $mypartner->storagefeeperannum;
        $time           = strtotime("now");

        if($type == 'processing_fee'){
            $i=1;
            //$fee is object of dailyGoldTransaction from MyGtpProcesSapTransactionFeeJob
            foreach($fee as $aTransaction){
                if($category == 'order') {
                    $refNoToUse = $aTransaction->ordorderno;
                }
                elseif($category == 'conversion') {
                    $refNoToUse = $aTransaction->rdmredemptionno;
                }
                $mCharged       = $requestParams['fpxcharge'];
                $netAmount      = floatval($aTransaction->pdtcustomerfee) - $mCharged;

                $decodedData['PostingDate']  = $aTransaction->createdon->format('Y-m-d');
                $decodedData['DeliveryDate'] = $aTransaction->createdon->format('Y-m-d');
                $decodedData['DocumentDate'] = $aTransaction->createdon->format('Y-m-d');
                $decodedData['unitPrice']    = $netAmount; //need to convert to number because it is string
                $decodedData['refNo']        = $refNoToUse."_".$i."_".$time;
                $decodedData['quantity']     = (float)$xau; //need to convert to number because it is string
                $decodedData['data1']        = $type."_".$category;
                $decodedData['data2']        = $requestParams['bpcode'];
                $decodedData['itemCode']     = $partnersapsetting->itemcode;
                /*added for dynamic value*/
                $decodedData['customerId']   = $sapcustomerid;
                /**/
                $i++;
                $responseParams = $this->createOutputApiParam($partnersapsetting->action, $requestParams, $decodedData);

                $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
                $data = $sender->response($app, $responseParams, ['url' => $url]);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send END.", SNAP_LOG_DEBUG);
            }

        } elseif($type == 'admin_fee'){
            //check if adminfeeperannum have value > 0. if 0, dont have to send. If not, send.
            if($adminfee != 0){
                if($partnersapsetting->action == 'buy_invoice') $remarks = 'adminbuyinvoice';
                elseif($partnersapsetting->action == 'sell_invoice') $remarks = 'adminsellinvoice';

                $convertToDecimal = number_format($fee->price,'2','.','');
                $decodedData['PostingDate']  = $fee->chargedon->format('Y-m-d');
                $decodedData['DeliveryDate'] = $fee->chargedon->format('Y-m-d');
                $decodedData['DocumentDate'] = $fee->chargedon->format('Y-m-d');
                $decodedData['unitPrice']    = floatval($convertToDecimal); //need to convert to number because it is string
                $decodedData['refNo']        = $fee->refno."-".$time;
                $decodedData['quantity']     = (float)$xau; //need to convert to number because it is string
                $decodedData['data1']        = $remarks;
                $decodedData['data2']        = $requestParams['bpcode'];
                $decodedData['itemCode']     = $partnersapsetting->itemcode;
                /*added for dynamic value*/
                $decodedData['customerId']   = $sapcustomerid;
                $responseParams = $this->createOutputApiParam($partnersapsetting->action, $requestParams, $decodedData);

                $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
                $data = $sender->response($app, $responseParams, ['url' => $url]);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send END.", SNAP_LOG_DEBUG);
            } else {
                $this->log("Not sending admin fee for ".$sapcustomerid." as value for adminfeeperannum is 0.", SNAP_LOG_DEBUG);
                /*to cater when partner dont have admin fee*/
                $data = array(
                        'requestData' => '',
                        'url' => '',
                        'responseData' => '',
                        'data' => array(0 => array('success' => 'Y')),
                        'statusCode' => 200
                    );
            }
        } elseif($type == 'storage_fee'){
            if($storagefee != 0){
                if($partnersapsetting->action == 'buy_invoice') $remarks = 'storagebuyinvoice';
                elseif($partnersapsetting->action == 'sell_invoice') $remarks = 'storagesellinvoice';
            
                $convertToDecimal = number_format($fee->price,'2','.','');
                $decodedData['PostingDate']  = $fee->chargedon->format('Y-m-d');
                $decodedData['DeliveryDate'] = $fee->chargedon->format('Y-m-d');
                $decodedData['DocumentDate'] = $fee->chargedon->format('Y-m-d');
                $decodedData['unitPrice']    = floatval($convertToDecimal); //need to convert to number because it is string
                $decodedData['refNo']        = $fee->refno."-".$time;
                $decodedData['quantity']     = (float)$xau; //need to convert to number because it is string
                $decodedData['data1']        = $remarks;
                $decodedData['data2']        = $requestParams['bpcode'];
                $decodedData['itemCode']     = $partnersapsetting->itemcode;
                /*added for dynamic value*/
                $decodedData['customerId']   = $sapcustomerid;
                $responseParams = $this->createOutputApiParam($partnersapsetting->action, $requestParams, $decodedData);

                $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
                $data = $sender->response($app, $responseParams, ['url' => $url]);
                $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send END.", SNAP_LOG_DEBUG);

            } else {
                $this->log("Not sending storage fee for ".$sapcustomerid." as value for storagefeeperannum is 0.", SNAP_LOG_DEBUG);
                /*to cater when partner dont have storage fee*/
                $data = array(
                        'requestData' => '',
                        'url' => '',
                        'responseData' => '',
                        'data' => array(0 => array('success' => 'Y')),
                        'statusCode' => 200
                    );
            }
        } elseif($type == 'conversion_fee'){
            $remarks = $type." for ".$xau."g";

            $convertToDecimal = number_format($requestParams['total_amount'],'2','.','');
            $decodedData['PostingDate']  = $fee->createdon->format('Y-m-d');
            $decodedData['DeliveryDate'] = $fee->createdon->format('Y-m-d');
            $decodedData['DocumentDate'] = $fee->createdon->format('Y-m-d');
            $decodedData['unitPrice']    = floatval($convertToDecimal); //need to convert to number because it is string
            $decodedData['refNo']        = $fee->refno."-".$time;
            //$decodedData['refNo']        = $fee->refno;
            //$decodedData['quantity']     = (float)$xau; //need to convert to number because it is string
            $decodedData['quantity']     = (float)1;
            //$decodedData['type']         = $type;
            $decodedData['data1']        = $remarks;
            $decodedData['data2']        = $requestParams['bpcode'];
            $decodedData['itemCode']     = $partnersapsetting->itemcode;
            /*added for dynamic value*/
            $decodedData['customerId']   = $sapcustomerid;
            $responseParams = $this->createOutputApiParam($partnersapsetting->action, $requestParams, $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
            $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            $this->log(__METHOD__ . "({$fee->refno}) SAP HTTP send END.", SNAP_LOG_DEBUG);
        }

        return $data;
    }

    public function notifyReverseRedemption($app, $redemption)
    {
        $url = $app->getConfig()->{'gtp.sap.reversalredemption.url'};
        $action = 'redemptionreversal';
        $items = json_decode($redemption->items, true);
        foreach ($items as $aItem) {
            $reverseSAPNo = (int)$aItem['sapreverseno'];
        }
        $decodedData['absEntry'] = $reverseSAPNo;
        $decodedData['refNo'] = 'reverse_redeem_'.$reverseSAPNo;
        $decodedData['partnerrefid'] = (string)$redemption->partnerrefno;
        $responseParams = $this->createOutputApiParam($action, ['version' => $redemption->apiversion ], $decodedData);
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
        $data = $sender->response($app, $responseParams, [ 'url' => $url ]);
        return $data;
    }

    public function onReceiveNewSerial($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $this->log("Running the method " . __METHOD__ . " based on receive action from SAP - " . json_encode($decodedData), SNAP_LOG_ERROR);
            $itemCodeArray = [];
            $getOriginalParams = [];
            foreach ($requestParams['body'] as $aRequest) {
                foreach ($decodedData as $aValue) {
                    $partnersapcode = $aValue['partner']; //partner obj
                    $itemsapcode = $aValue['product']; //product obj
                    if ($itemsapcode->sapitemcode == $aRequest['itemCode'] && $partnersapcode->sapcompanybuycode1 == $aRequest['customerId']) {
                        $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['partner'] = $partnersapcode; //push partner obj to array 
                        $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['product'] = $itemsapcode; //push product obj to array 
                    }
                }
                $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['request'][] = $aRequest; //push serialnoarray to array 
                $getOriginalParams[$aRequest['serialNum']] = $aRequest; // this is to pass original params to createOutputApiParam()
            }

            foreach ($itemCodeArray as $aItem) { //call each of itemCode/product
                foreach ($aItem as $aSerial) {
                    /*get DoDocNum*/
                    foreach($aSerial['request'] as $aData){
                       if($aData['DoDocNum'] != '') $receiveCase[] = $aData;
                    }
                    $receiveObject = json_encode($receiveCase);
                    /*get DoDocNum end*/
                    $serialObject = json_encode($aSerial['request']); //return array of serial request to obj array then decode when call manager
                    $vaultItem = $app->bankvaultManager()->onSapNotifyReceiveNewSerial($aSerial['partner'], json_decode($serialObject));
                    if($vaultItem && $receiveCase != null) {
                        /*send to received*/
                        $vaultItemReceived = $app->bankvaultManager()->onSapNotifyItemAvailable($aSerial['partner'], json_decode($receiveObject));
                        /*send to received end*/ 
                    } 

                    foreach ($vaultItem as $aVault) {
                        $aSerial['vaultItem'] = $aVault;
                        $aSerial['success'] = 'Y';
                        $aSerial['message'] = '';
                        foreach ($aSerial['request'] as $aNum) {
                            $getOriginalParams[$aVault->serialno]['version'] = $requestParams['version'];
                            $getOriginalParams[$aVault->serialno]['action'] = $requestParams['action'];
                            $passOriginal = $getOriginalParams[$aVault->serialno];
                        }
                        $responseParams[] = $this->createOutputApiParam('newserialresponse', $passOriginal, $aSerial);
                    }
                }
            }

            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), SNAP_LOG_DEBUG);
            $action = $apiParam->getActionType();
            $version = $requestParams['version'];
            foreach ($requestParams['body'] as $key => $aRequest) {
                $aRequest['action'] = $action;
                $aRequest['version'] = $version;
                $decodedData[$key]['success'] = 'N';
                $decodedData[$key]['message'] = $e->getMessage();
                $responseParams[] = $this->createOutputApiParam('newserialresponse', $aRequest, $decodedData[$key]);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
    }

    public function onReceiveGoldbar_receive($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $this->log("Running the method " . __METHOD__ . " based on receive action from SAP - " . json_encode($decodedData), SNAP_LOG_ERROR);
            $itemCodeArray = [];
            $getOriginalParams = [];
            foreach ($requestParams['body'] as $aRequest) {
                foreach ($decodedData as $aValue) {
                    $partnersapcode = $aValue['partner']; //partner obj
                    $itemsapcode = $aValue['product']; //product obj
                    if ($itemsapcode->sapitemcode == $aRequest['itemCode'] && $partnersapcode->sapcompanybuycode1 == $aRequest['customerId']) {
                        $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['partner'] = $partnersapcode; //push partner obj to array 
                        $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['product'] = $itemsapcode; //push product obj to array 
                    }
                }
                $itemCodeArray[$aRequest['customerId']][$aRequest['itemCode']]['request'][] = $aRequest; //push serialnoarray to array 
                $getOriginalParams[$aRequest['serialNum']] = $aRequest; // this is to pass original params to createOutputApiParam()
            }

            foreach ($itemCodeArray as $aItem) { //call each of itemCode/product
                foreach ($aItem as $aSerial) {
                    $serialObject = json_encode($aSerial['request']); //return array of serial request to obj array then decode when call manager
                    $vaultItem = $app->bankvaultManager()->onSapNotifyItemAvailable($aSerial['partner'], json_decode($serialObject));
                    foreach ($vaultItem as $aVault) {
                        $aSerial['vaultItem'] = $aVault;
                        $aSerial['success'] = 'Y';
                        $aSerial['message'] = '';
                        foreach ($aSerial['request'] as $aNum) {
                            $getOriginalParams[$aVault->serialno]['version'] = $requestParams['version'];
                            $getOriginalParams[$aVault->serialno]['action'] = $requestParams['action'];
                            $passOriginal = $getOriginalParams[$aVault->serialno];
                        }
                        $responseParams[] = $this->createOutputApiParam('goldbar_receiveresponse', $passOriginal, $aSerial);
                    }
                }
            }

            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        } catch (\Exception $e) {
            $this->log($e->getMessage(), SNAP_LOG_DEBUG);
            $action = $apiParam->getActionType();
            $version = $requestParams['version'];
            foreach ($requestParams['body'] as $key => $aRequest) {
                $aRequest['action'] = $action;
                $aRequest['version'] = $version;
                $decodedData[$key]['success'] = 'N';
                $decodedData[$key]['message'] = $e->getMessage();
                $responseParams[] = $this->createOutputApiParam('goldbar_receiveresponse', $aRequest, $decodedData[$key]);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
    }

    public function notifyReturnKilobar($app, $vaultitem)
    {
        $action = "goldreturn";
        if(is_array($vaultitem)){
            $i = 1;
            foreach($vaultitem as $key=>$value){
                if(0 == $value['partnerid']) {
                    $partnerid = $app->getConfig()->{'gtp.go.partner.id'};
                    $url = $app->getConfig()->{'gtp.sap.kilobar.common.url'};
                }
                else {
                    $partnerid = $value['partnerid'];
                    $url = $app->getConfig()->{'gtp.sap.kilobar.url'};
                }
                $partner = $app->partnerStore()->getByField('id', $partnerid);
                if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
                $mypartner = $app->mypartnersettingStore()->getByField('partnerid', $partnerid);
                if($mypartner) {
                    $sapdgcode = $mypartner->sapdgcode;
                    $sapkilobarwhs = $mypartner->sapkilobarwhs;
                    /*if($sapcustomerid == 'GOPAYZ') $sapwhscode = 'GPZ_VLT'; //declare because partnerwarehouse name different than sapcustomer name
                    else $sapwhscode = $sapcustomerid.'_VLT';*/
                }
                $vaultitemobj = $app->vaultitemStore()->getByField('id',$value['id']);
                $version = '1.0my';
                $decodedData['vaultItem'] = $vaultitemobj;
                $decodedData['product'] = 'DG-999-9-'.$sapdgcode;
                $decodedData['refNo'] = $value['deliveryordernumber']."_".$value['id'];
                /*added for dynamic value*/
                $decodedData['whsCode'] = $sapkilobarwhs;
                $decodedData['customerId'] = $sapcustomerid;
                /**/
                $responseParams[] = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
                $i++;
            }
        } else {
            if(0 == $vaultitem->partnerid) {
                $partnerid = $app->getConfig()->{'gtp.go.partner.id'};
                $url = $app->getConfig()->{'gtp.sap.kilobar.common.url'};
            }
            else {
                $partnerid = $vaultitem->partnerid;
                $url = $app->getConfig()->{'gtp.sap.kilobar.url'};
            }
            $partner = $app->partnerStore()->getByField('id', $partnerid);
            if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
            $mypartner = $app->mypartnersettingStore()->getByField('partnerid', $partnerid);
            if($mypartner) {
                $sapdgcode = $mypartner->sapdgcode;
                $sapkilobarwhs = $mypartner->sapkilobarwhs;
                /*if($sapcustomerid == 'GOPAYZ') $sapwhscode = 'GPZ_VLT'; //declare because partnerwarehouse name different than sapcustomer name
                else $sapwhscode = $sapcustomerid.'_VLT';*/
            }
            $version = '1.0my';
            $decodedData['vaultItem'] = $vaultitem;
            $decodedData['product'] = 'DG-999-9-'.$sapdgcode;
            $decodedData['refNo'] = $vaultitem->deliveryordernumber."_".$vaultitem->id;
            /*added for dynamic value*/
            $decodedData['whsCode'] = $sapkilobarwhs;
            $decodedData['customerId'] = $sapcustomerid;
            /**/
            $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
        }
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
        $data = $sender->response($app, $responseParams, ['url' => $url]);
        return $data;
    }

    public function getSharedMintedList($app, $customerid, $version){
        $url = $app->getConfig()->{'gtp.sap.itemlist.url'};
        $action = 'sharedminted';
        $decodedData['customerId'] = $customerid;
        $responseParams = $this->createOutputApiParam($action, ['version' => $version ], $decodedData);
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
        $data = $sender->response($app, $responseParams, [ 'url' => $url ]);

        $getResponse = $data['data'];

        foreach($getResponse as $key=>$value){
            $denom[$value['ItemCode']][(string)$value['Total Qty']] += $value['Total Qty'];
            //reason to do string to keep decimal point for dinar. exp:4.25.
        }

        foreach($denom as $key => $aValue){
            /*divide the denom because the sum is xau sum*/
            foreach($aValue as $key2 => $aSubValue){
                if((float)$key2 != 0){
                    $updateValue[$key] = $aSubValue/(float)$key2;
                    //convert to float to divide to get quantity
                }
            }
        }

        $return['data']['inventoryList'] = $getResponse; // added massaging data
        $return['data']['denominationList'] = $updateValue;

        return $return;
    }

    public function generateMintedReport($app, $customerid, $version){
        $url = $app->getConfig()->{'gtp.sap.itemlist.url'};
        $action = 'sharedminted';
        $decodedData['customerId'] = $customerid;
        $responseParams = $this->createOutputApiParam($action, ['version' => $version ], $decodedData);
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
        $data = $sender->response($app, $responseParams, [ 'url' => $url ]);

        $getResponse = $data['data'];
        $return['data']['inventoryList'] = $getResponse; // added massaging data

        return $return;
    }

    public function notifyReconcile($app, $transaction){
        //PO - buy
        //SO - sell
        $transactionDecode  = json_decode($transaction,true);
        $url                = $app->getConfig()->{'gtp.sap.reconciled.url'};
        $action             = array('documentporequest','documentsorequest');
        $addToArray         = array();
        $date               = $transactionDecode['currentdate'];
        $partner['id']      = $transactionDecode['partnerid'];
        $partner['code']    = $transactionDecode['partnercode'];
        /*$startdate          = $transactionDecode['startdate'];
        $enddate            = $transactionDecode['enddate'];
        $getDate            = date('Y-m-d\T00:00:00',strtotime($enddate));*/
        //$getDate            = date('Y-m-d h:i:s',$date);

        

        $partnerObj = $app->partnerStore()->getByField('id', $partner['id']);
        $partnersapcode = $partnerObj->sapcompanybuycode1; 
        $partner['sapcode']    = $partnersapcode;

        if(null != $transactionDecode['server']) $extraFlag = "#####".strtoupper($transactionDecode['server'])."#####";
        $filename = $transactionDecode['filename'];
        $reportpath = $app->getConfig()->{'mygtp.acereport.dailytransaction'};
        
        //$getDate          = date('Y-m-d h:i:s',$date);
        //2023-04-26: The reconcile report still only receive only data 2 days before instead of the yesterday data. i.e:
        //current date: 2023-04-26
        //data received: 2023-04-24 
        //data on 2023-04-25 is missing from report
        //current $getDate use to -2 days of current date and used for both docDateFrom and docDateTo
        //so add another param $getDateTo to use -1 day of current date and use for docDateTo

        //2023-05-05: Ace requested to receive only yesterday data. therefore change $getDate data from -2 days to -1 day.
        //so now $getDate and $getDateTo will have same value.
        $getDate            = date('Y-m-d h:i:s',strtotime("-1 day",$date));
        $getDateTo          = date('Y-m-d h:i:s',strtotime('-1 day',$date)); //new variable for docDateTo
        //$goFormat         = date('Y-m-d h:i:s',$getDateBefore);

        $createDate         = date_create($getDate);
        $createDateTo       = date_create($getDateTo); //new variable for docDateTo

        //2023-04-26: not sure why its two times the same thing? later need to reconfirm with dk
        $dateRequest      = date_format($createDate,"Y-m-d\T00:00:00");//change to current date as batch send to SAP at 12.30am
        $dateRequest        = date_format($createDate,"Y-m-d\T00:00:00");//change to current date as batch send to SAP at 12.30am
        $dateRequestTo      = date_format($createDateTo,"Y-m-d\T00:00:00"); //new variable for docDateTo

        //$convertDate        = strtotime($dateRequest);

        foreach($action as $aAction){
            $decodedData['docDateFrom'] = $dateRequest;
            //2023-04-26: change so that $ddecodedData['docDateTo'] will use the new variable dateRequestTo
            // $decodedData['docDateTo'] = $dateRequest;
            $decodedData['docDateTo'] = $dateRequestTo;
            $decodedData['customerId'] = $partnersapcode;

            $responseParams = $this->createOutputApiParam($aAction, ['version' => '1.0my'], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $this->log(__METHOD__."({$aAction}) SAP HTTP send START.", SNAP_LOG_DEBUG);
            $data = $sender->response($app, $responseParams, ['url' => $url]);

            if(!empty($data['data'])){
                array_push($addToArray,$data['data']);
            } 
            $this->log(__METHOD__."({$aAction}) SAP HTTP send END.", SNAP_LOG_DEBUG);
        }

        if(!empty($addToArray)){
            foreach($addToArray as $key=>$value){
                foreach($value as $aValue){
                    $sapList[] = $aValue;
                }
            }
            $checkingTransaction = $app->reportingManager()->checkTransactionReconcile($sapList,$date,$partner,$extraFlag,$filename,$reportpath);
        } else {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => "No transaction from SAP."]);
        }
        //return $data;
    }
}


?>