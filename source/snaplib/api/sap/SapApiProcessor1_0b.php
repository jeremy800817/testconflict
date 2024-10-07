<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\sap;

use Snap\api\param\SapApiParam1_0b;
use Snap\IApiProcessor;
use Snap\api\param\ApiParam;
use \Snap\object\Redemption;
use \Snap\object\VaultLocation;
/*spreadsheet/excel*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


/**
 * This processor class defines a main factory method to provide customisation on different API versions.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.Sap
 */
class SapApiProcessor1_0b extends SapApiProcessor1_0
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

    public function setCalledFromHttp(bool $value) {
        $this->calledFromHttp = $value;
    }

    public function getCalledFromHttp() {return $this->calledFromHttp;}

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

    public function onReceiveReplenishmentReply($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $this->log("Running the method " . __METHOD__ . " based on receive action from SAP - " . json_encode($decodedData), SNAP_LOG_ERROR);
            $action = $requestParams['action'];
            $version = $requestParams['version'];
            $itemCodeArray = [];
            $getOriginalParams = [];

            $replenishmentArray = [];
            foreach ($requestParams['body'] as $aRequest) {
                foreach ($decodedData as $aValue) {
                    $itemsapcode = $aValue['product']; //product obj
                    $partner = $aValue['partner']; //partner obj
                    $branch = $aValue['branch']; //branch obj

                    if ($itemsapcode->sapitemcode == $aRequest['itemCode']) {
                        $productid = $itemsapcode->id;
                        $itemCodeArray[$aRequest['itemCode']]['product'] = $itemsapcode; //push product obj to array 
                    }
                    if ($partner->sapcompanysellcode1 == $aRequest['customerId'] || $partner->sapcompanybuycode1 == $aRequest['customerId'] || $partner->sapcompanybuycode2 == $aRequest['customerId'] || $partner->sapcompanysellcode2 == $aRequest['customerId']) {
                        $partnerid = $partner->id;
                    }
                    if ($branch->sapcode == $aRequest['bankId']) {
                        $branchid = $branch->id;
                    }
                }

                $aRequest['productid'] = $productid;
                $aRequest['partnerid'] = $partnerid;
                $aRequest['serialno'] = $aRequest['serialNum'];
                $aRequest['sapwhscode'] = $aRequest['whsCode'];
                $aRequest['saprefno'] = $aRequest['refNo'];
                $aRequest['branchid'] = $branchid;

                $replenishmentArray[] = $aRequest;

                $itemCodeArray[$aRequest['itemCode']]['request'][] = $aRequest; //push serialnoarray to array 
                $getOriginalParams[] = $aRequest; // this is to pass original params to createOutputApiParam()
            }

            $replenishItem = $app->replenishmentManager()->doReplenishment($replenishmentArray);
            
            if ($replenishItem) {
                foreach ($itemCodeArray as $aItem) {
                    $aItem['success'] = 'Y';
                    $aItem['message'] = '';
                    foreach ($aItem['request'] as $key => $aNum) {
                        $getOriginalParams[$key]['version'] = $version;
                        $getOriginalParams[$key]['action'] = $action;
                        $passOriginal = $getOriginalParams[$key];

                        $responseParams[] = $this->createOutputApiParam('replenishmentreplyresponse', $passOriginal, $aItem);
                    }
                }
            } else {
                $this->log("Replenishent reply return false.", SNAP_LOG_DEBUG);
                throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => "Unable to proceed with replenishment reply."]);
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
                $responseParams[] = $this->createOutputApiParam('replenishmentreplyresponse', $aRequest, $decodedData[$key]);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
    }

    public function onReceiveReplenishmentComplete($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $this->log("Running the method " . __METHOD__ . " based on receive action from SAP - " . json_encode($decodedData), SNAP_LOG_ERROR);
            $action = $requestParams['action'];
            $version = $requestParams['version'];
            $itemCodeArray = [];
            $getOriginalParams = [];
            $replenishmentArray = [];
            foreach ($requestParams['body'] as $aRequest) {
                foreach ($decodedData as $aValue) {
                    $itemsapcode = $aValue['product']; //product obj
                    $partner = $aValue['partner']; //partner obj
                    $branch = $aValue['branch']; //branch obj

                    if ($itemsapcode->sapitemcode == $aRequest['itemCode']) {
                        $productid = $itemsapcode->id;
                        $itemCodeArray[$aRequest['itemCode']]['product'] = $itemsapcode; //push product obj to array 
                    }
                    if ($partner->sapcompanysellcode1 == $aRequest['customerId'] || $partner->sapcompanybuycode1 == $aRequest['customerId'] || $partner->sapcompanybuycode2 == $aRequest['customerId'] || $partner->sapcompanysellcode2 == $aRequest['customerId']) {
                        $partnerid = $partner->id;
                    }
                    if ($branch->sapcode == $aRequest['bankId']) {
                        $branchid = $branch->id;
                    }
                }

                $aRequest['serialNum'] = $aRequest['serialNum'];
                $aRequest['itemCode'] = $aRequest['itemCode'];

                $replenishmentArray[] = $aRequest;

                $itemCodeArray[$aRequest['itemCode']]['request'][] = $aRequest; //push serialnoarray to array 
                $getOriginalParams[] = $aRequest; // this is to pass original params to createOutputApiParam()
            }

            $replenishItem = $app->replenishmentManager()->recievingFromSAP($replenishmentArray);

            if ($replenishItem) {
                foreach ($itemCodeArray as $aItem) {
                    $aItem['success'] = 'Y';
                    $aItem['message'] = '';
                    foreach ($aItem['request'] as $key => $aNum) {
                        $getOriginalParams[$key]['version'] = $version;
                        $getOriginalParams[$key]['action'] = $action;
                        $passOriginal = $getOriginalParams[$key];

                        $responseParams[] = $this->createOutputApiParam('replenishmentcompleteresponse', $passOriginal, $aItem);
                    }
                }
            } else {
                $this->log("Replenishent complete return false.", SNAP_LOG_DEBUG);
                throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => "Unable to proceed with replenishment complete."]);
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
                $responseParams[] = $this->createOutputApiParam('replenishmentcompleteresponse', $aRequest, $decodedData[$key]);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
    }

    public function onReceiveVaultItemRequest($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $this->log("Running the method " . __METHOD__ . " based on receive action from SAP - " . json_encode($decodedData), SNAP_LOG_ERROR);
        $action = $requestParams['action'];
        $version = $requestParams['version'];
        $updateRequestParams = $requestParams['body'];
        $updateRequestParams[0]['action'] = $requestParams['action'];
        $updateRequestParams[0]['version'] = $requestParams['version'];
        $customerId = $requestParams['body'][0]['customerId'];
        try {
            //$partner = $app->partnerStore()->getByField('sapcompanysellcode1', $customerId);
            $partner = $app->partnerStore()->searchTable()
                ->select()
                ->where('sapcompanysellcode1', $customerId)
                ->orWhere('sapcompanysellcode2', $customerId)
                ->orWhere('sapcompanybuycode1', $customerId)
                ->orWhere('sapcompanybuycode2', $customerId)
                ->one();
            $partnerid = $partner->id;

            $vaultItem = $app->bankvaultManager()->sapQueryVaultItems($partnerid, $version);

            if ($vaultItem == null) {
                $this->log("Vaulitem return empty.", SNAP_LOG_DEBUG);
                throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => "There is no data."]);
            } else {
                $i=1;
                foreach ($vaultItem as $key => $aVaultItem) {
                    if(empty($aVaultItem['deliveryordernumber']) || $aVaultItem['deliveryordernumber'] == null) $aVaultItem['deliveryordernumber'] = '';
                    else $aVaultItem['deliveryordernumber'] = $aVaultItem['deliveryordernumber'];

                    if(empty($aVaultItem['location']) || $aVaultItem['location'] == null) $aVaultItem['location'] = '';
                    else $aVaultItem['location'] = $aVaultItem['location'];

                    $aVaultItem['count'] = $i;
                    $aVaultItem['success'] = 'Y';
                    $aVaultItem['message'] = '';
                    $i++;
                    $responseParams[] = $this->createOutputApiParam('vaultitemrequestresponse', $updateRequestParams[0], $aVaultItem);
                }
                $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
                $sender->response($app, $responseParams);
                return $responseParams;
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage(), SNAP_LOG_DEBUG);
            foreach ($requestParams['body'] as $key => $aRequest) {
                $aRequest['action'] = $action;
                $aRequest['version'] = $version;
                $decodedData['serialnumber'] = 'null';
                $decodedData['location'] = 'null';
                $decodedData['customerId'] = $customerId;
                $decodedData['DoDocNum'] = 'null';
                $decodedData['success'] = 'N';
                $decodedData['message'] = $e->getMessage();
                $responseParams[] = $this->createOutputApiParam('vaultitemrequestresponse', $aRequest, $decodedData);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
    }

    public function notifyNewOrder($app, $order)
    {
        if (preg_match('/[0-9\.]+b$/', $order->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.dgneworder.url'};
            $action = sprintf("company%sDigitalOrder", $order->isCompanyBuy() ? "Buy" : "Sell");

            //$getPartner = $order->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $order->partnerid);

            if($getPartner->sapcompanysellcode1 == $getPartner->sapcompanybuycode1) $customerId = $getPartner->sapcompanysellcode1;
            else {
                if($action == 'companyBuyDigitalOrder') $customerId = $getPartner->sapcompanybuycode1;
                else $customerId = $getPartner->sapcompanysellcode1;
            }

            //06/06/2023 - Calvin say the itemCode is suppose to be DG-999-9-BG.
            // BG is the sap DG code for Bursa
            // therefore will do the same way as mygtp project,, which is to append
            // product code and sap dg code
            $product = $app->productStore()->getByField('id',$order->productid);
            $mypartner = $app->mypartnersettingStore()->getByField('partnerid', $order->partnerid);
            $productAddName = $product->sapitemcode."-".$mypartner->sapdgcode;

            $decodedData['customerId'] = $customerId;
            $decodedData['order'] = $order;
            $decodedData['product'] = $order->getProduct();
            $decodedData['partner'] = $order->getPartner();
            $decodedData['datetosend'] = $order->createdon->format('Y-m-d\TH:i:s').'+08:00';
            // add new param to send to the create output 
            $decodedData['itemCode'] = $productAddName;


            $responseParams = $this->createOutputApiParam($action, ['version' => $order->apiversion], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $this->log(__METHOD__."({$order->orderno}) SAP HTTP send START.", SNAP_LOG_DEBUG);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            $this->log(__METHOD__."({$order->orderno}) SAP HTTP send END.", SNAP_LOG_DEBUG);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyCancelOrder($app, $order)
    {
        if (preg_match('/[0-9\.]+b$/', $order->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.dgcancelorder.url'};
            $action = sprintf("%s_cancel", $order->isCompanyBuy() ? "buy" : "sell");

            //$getPartner = $order->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $order->partnerid);

            if($getPartner->sapcompanysellcode1 == $getPartner->sapcompanybuycode1) $customerId = $getPartner->sapcompanysellcode1;
            else {
                if($action == 'buy_cancel') $customerId = $getPartner->sapcompanybuycode1;
                else $customerId = $getPartner->sapcompanysellcode1;
            }
            $decodedData['customerId'] = $customerId;
            $decodedData['order'] = $order;
            $decodedData['datetosend'] = $order->createdon->format('Y-m-d\TH:i:s');

            $responseParams = $this->createOutputApiParam($action, ['version' => $order->apiversion], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyNewRedemptionBranch($app, $redemption)
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.redemption.url'}; //edit if variable url is different in config.ini
            $action = 'redemptionbranch';
            $items = json_decode($redemption->items);

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            foreach ($items as $x => $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->weight); //grab product table using weight==denomination
                //$xauquantity = str_replace("g","",$aItem->denomination);
                $aRedemption['itemCode'] = $product->sapitemcode;
                $aRedemption['bankId'] = str_pad($redemption->branchid, 5, '0', STR_PAD_LEFT); //need to add 0 when length less than 5
                $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $x + 1);
                $aRedemption['serialNum'] = $this->convertSerialNoToSap($aItem->serialnumber);
                $aRedemption['quantity'] = (float)$aItem->weight;
                $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                $aRedemption['customerId'] = $customerId;
                $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');

                $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyNewRedemptionDelivery($app, $redemption)
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            /*$now                = new \DateTime('now', $app->getUserTimezone());
            $currentdatetime    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone());
            $dateToSend         = $currentdatetime->format('Y-m-d\TH:i:s').'+08:00';*/
            $url = $app->getConfig()->{'gtp.sap.redemption.url'};
            $action = 'redemptiondelivery';
            $items = json_decode($redemption->items);

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            $redemptionArray = [];
            $i = 1;
            foreach ($items as $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
                //$xauquantity = str_replace("g"," ",$aItem->denomination);
                if ($aItem->quantity > 1) {
                    for ($j = 1; $j <= $aItem->quantity; $j++) {
                        $aRedemption['itemCode'] = $product->sapitemcode;
                        $aRedemption['bankId'] = ($redemption->type == Redemption::TYPE_APPOINTMENT ? $redemption->appointmentbranchid : 'null');
                        $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                        //$aRedemption['quantity'] = (int)$xauquantity;
                        $aRedemption['quantity'] = (float)$aItem->denomination;
                        $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                        $aRedemption['customerId'] = $customerId;
                        $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
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
                    $aRedemption['customerId'] = $customerId;
                    $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                    $i++;
                    $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                }
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    /*20200817 these two function are additional redemption api. Please delete this comment when this function already used or delete it if it not been used anymore.*/
    public function notifyNewRedemptionSPDelivery($app, $redemption)
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            /*$now                = new \DateTime('now', $app->getUserTimezone());
            $currentdatetime    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone());
            $dateToSend         = $currentdatetime->format('Y-m-d\TH:i:s').'+08:00';*/
            $url = $app->getConfig()->{'gtp.sap.redemption.url'};
            $action = 'redemptionspdelivery';
            $items = json_decode(json_decode($redemption->items, true));

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            $redemptionArray = [];
            $i = 1;
            foreach ($items as $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
                //$xauquantity = str_replace("g"," ",$aItem->denomination);
                if ($aItem->quantity > 1) {
                    for ($j = 1; $j <= $aItem->quantity; $j++) {
                        $aRedemption['itemCode'] = $product->sapitemcode;
                        $aRedemption['bankId'] = 'null';
                        $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                        //$aRedemption['quantity'] = (int)$xauquantity;
                        $aRedemption['quantity'] = (float)$aItem->denomination;
                        $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                        $aRedemption['customerId'] = $customerId;
                        $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                        $i++;
                        $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                    }
                } else {
                    $aRedemption['itemCode'] = $product->sapitemcode;
                    $aRedemption['bankId'] = 'null';
                    $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                    //$aRedemption['quantity'] = (int)$xauquantity;
                    $aRedemption['quantity'] = (float)$aItem->denomination;
                    $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                    $aRedemption['customerId'] = $customerId;
                    $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                    $i++;
                    $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                }
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyNewRedemptionPreAppointment($app, $redemption)
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            /*$now                = new \DateTime('now', $app->getUserTimezone());
            $currentdatetime    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone());
            $dateToSend         = $currentdatetime->format('Y-m-d\TH:i:s').'+08:00';*/
            $url = $app->getConfig()->{'gtp.sap.redemption.url'};
            $action = 'redemptionpreappointment';
            $items = json_decode(json_decode($redemption->items, true));

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            $redemptionArray = [];
            $i = 1;
            foreach ($items as $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
                //$xauquantity = str_replace("g"," ",$aItem->denomination);
                if ($aItem->quantity > 1) {
                    for ($j = 1; $j <= $aItem->quantity; $j++) {
                        $aRedemption['itemCode'] = $product->sapitemcode;
                        $aRedemption['bankId'] = $redemption->appointmentbranchid;
                        $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                        //$aRedemption['quantity'] = (int)$xauquantity;
                        $aRedemption['quantity'] = (float)$aItem->denomination;
                        $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                        $aRedemption['customerId'] = $customerId;
                        $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                        $i++;
                        $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                    }
                } else {
                    $aRedemption['itemCode'] = $product->sapitemcode;
                    $aRedemption['bankId'] = $redemption->appointmentbranchid;
                    $aRedemption['refNo'] = $redemption->redemptionno . '-' . sprintf("%02d", $i);
                    //$aRedemption['quantity'] = (int)$xauquantity;
                    $aRedemption['quantity'] = (float)$aItem->denomination;
                    $aRedemption['partnerrefid'] = (string)$redemption->partnerrefno;
                    $aRedemption['customerId'] = $customerId;
                    $aRedemption['datetosend'] = $redemption->createdon->format('Y-m-d\T00:00:00');
                    $i++;
                    $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
                }
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }
    /*20200817 these two function are additional redemption api. Please delete this comment when this function already used or delete it if it not been used anymore.END*/

    public function notifyReverseRedemption($app, $redemption)
    {
        if(preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
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
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, [ 'url' => $url ]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyNewBuyBack($app, $buyback)
    {
        if (preg_match('/[0-9\.]+b$/', $buyback->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.buyback.url'};
            $action = 'buybackminted';
            $items = json_decode($buyback->items);

            //$getPartner = $buyback->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $buyback->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            $i = 1;
            foreach ($items as $key => $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
                /*get branch code*/
                $branch = $app->partnerStore()->getRelatedStore('branches');
                $getBranch = $branch->getByField('id', $buyback->branchid);
                /*get branch code end*/
                //$xauquantity = str_replace("g"," ",$aItem['denomination']);
                $aBuyBack['itemCode'] = $product->sapitemcode;
                $aBuyBack['serialNum'] = $this->convertSerialNoToSap($aItem->serialno);
                //$aBuyBack['quantity'] = (int)$xauquantity;
                $aBuyBack['quantity'] = (float)$aItem->denomination;
                $aBuyBack['unitPrice'] = floatval($buyback->price);
                $aBuyBack['refNo'] = $buyback->buybackno . '-' . sprintf("%02d", $i);
                $aBuyBack['bankId'] = $getBranch->code;
                $aBuyBack['customerId'] = $customerId;
                $aBuyBack['partnerrefid'] = (string)$buyback->partnerrefno;
                $responseParams[] = $this->createOutputApiParam($action, ['version' => $buyback->apiversion], $aBuyBack);
                $i++;
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyReverseBuyBack($app, $buyback)
    {
        if(preg_match('/[0-9\.]+b$/', $buyback->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.reversalbuyback.url'};
            $action = 'buybackreversal';
            $items = json_decode($buyback->items, true);
            foreach ($items as $aItem) {
                $reverseSAPNo = (int)$aItem['sapreverseno'];
            }
            $decodedData['absEntry'] = $reverseSAPNo;
            $decodedData['refNo'] = 'reverse_buyback_'.$reverseSAPNo;
            $decodedData['partnerrefid'] = (string)$buyback->partnerrefno;
            $responseParams = $this->createOutputApiParam($action, ['version' => $buyback->apiversion ], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, [ 'url' => $url ]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyReconcile($app, $transaction){
        if(preg_match('/[0-9\.]+b$/', '1.0b')) {
            //PO - buy
            //SO - sell
            $transactionDecode = json_decode($transaction,true);
            $url = $app->getConfig()->{'gtp.sap.reconciled.url'};
            $action = array('documentporequest','documentsorequest');
            $addToArray = array();
            $date = $transactionDecode['currentdate'];
            $partner['id'] = $transactionDecode['partnerid'];
            $partner['code'] = $transactionDecode['partnercode'];

            $getPartner = $app->partnerStore()->getByField('id', $partner['id']);
            $customerId = $getPartner->sapcompanysellcode1;

            if(null != $transactionDecode['server']) $extraFlag = "#####".strtoupper($transactionDecode['server'])."#####";
            
            //$getDate          = date('Y-m-d h:i:s',$date);
            $getDate            = date('Y-m-d h:i:s',strtotime("-1 days",$date));
            //$goFormat         = date('Y-m-d h:i:s',$getDateBefore);

            $createDate         = date_create($getDate);
            //$dateRequest      = date_format($createDate,"Y-m-d\T00:00:00");//change to current date as batch send to SAP at 12.30am
            $dateRequest        = date_format($createDate,"Y-m-d\T00:00:00");//change to current date as batch send to SAP at 12.30am
            $convertDate        = strtotime($dateRequest);

            foreach($action as $aAction){
                $decodedData['docDateFrom'] = $dateRequest;
                $decodedData['docDateTo'] = $dateRequest;
                $decodedData['customerId'] = $customerId;

                $responseParams = $this->createOutputApiParam($aAction, ['version' => '1.0b'], $decodedData);
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
                $checkingTransaction = $app->ftpprocessorManager()->checkTransactionReconcile($sapList,$date,$partner,$extraFlag);
            } else {
                throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => "No transaction from SAP."]);
            }
            //return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function convertSerialNoToSap($serialno)
    {
        $length = strlen($serialno);
        $alphaLength = 3;
        $alphaMaxLength = 6;
        $noLength = 8;

        //split letter and numbers
        preg_match_all('/(\d)|(\w)/', $serialno, $matches);

        $numbers = implode($matches[1]);
        $letters = implode($matches[2]);

        //check if there is spaces inside letters
        $updateSerialno .= (preg_match('/\s/',$letters)) ? str_replace(' ', '', $letters) : $letters;

        //check length of string for number and remove spaces
        $updateSerialno .= (preg_match('/\s/',$numbers)) ? str_replace(' ', '', $numbers) : $numbers;

        return $updateSerialno;
    }

    public function notifyUpdateReplenish($app, $transaction,$version,$flag)
    {
        $transformToArray = json_decode($transaction,true);
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.replenishment.url'}; //edit if variable url is different in config.ini
            $action = 'tfreplenish';
            $partner = $transformToArray['partner'];
            /*[partner] => Array(
                    [id] => 1
                    [code] => MBISMY@SIT
                    [name] => Maybank Islamic Gold Account
                )
            */
            $getPartner = $app->partnerStore()->getByField('id', $partner['id']);
            $customerId = $getPartner->sapcompanysellcode1;

            $detailCount        = $transformToArray[0]['header_totalline']+1;
            $fileDate           = $transformToArray[0]['header_filedate'];
            $date               = date('Y-m-d h:i:s',$transformToArray['currentdate']);
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            $ftpHeader = $transformToArray[0]['header_code'].str_pad($transformToArray[0]['header_filename'],10, ' ', STR_PAD_RIGHT).$transformToArray[0]['header_filedate'].$transformToArray[0]['header_filesequence'].$transformToArray[0]['header_totalline'];
            $summaryChecking = $app->ftpprocessorManager()->summaryChecking($transformToArray);

            /*this is for summary tab in PHYINV excel report*/
            $summaryInventory = $app->ftpprocessorManager()->summaryInventory($transformToArray);

            $t=time();
            $timeUnix = date("His",$t);
            $countAvailable = 0;
            $countOtherAvailable = 0;
            for($i=1;$i<$detailCount;$i++){
                $transArray = $transformToArray[$i];
                /*01 – NEW/02 – AVAILABLE (received)/03 – REDEEM/04 – RETURN/05 – LOST/STOLEN/06 – MISMATCH/07 – DAMAGE*/
                $status = $transArray['detail_status'];
                $serialnum = $transArray['detail_serialno'];
                $branchid = $transArray['detail_branchid'];
                $loadingdate = $transArray['detail_loadingdate'];
                $denomation = $transArray['detail_denomination'];
                //A=1gm/B=5gm/C=10gm/D=50gm/E=100gm/F=1000gm
                if('A' == $denomation) $itemCode = 'GS-999-9-1g';
                if('B' == $denomation) $itemCode = 'GS-999-9-5g';
                if('C' == $denomation) $itemCode = 'GS-999-9-10g';
                if('D' == $denomation) $itemCode = 'GS-999-9-50g';
                if('E' == $denomation) $itemCode = 'GS-999-9-100g';
                if('F' == $denomation) $itemCode = 'GS-999-9-1000g';

                if('A' == $denomation) $quantity = 1;
                if('B' == $denomation) $quantity = 5;
                if('C' == $denomation) $quantity = 10;
                if('D' == $denomation) $quantity = 50;
                if('E' == $denomation) $quantity = 100;
                if('F' == $denomation) $quantity = 1000;

                /*thisis for excel*/
                /*get branch name*/
                $branch = $app->partnerStore()->getRelatedStore('branches');
                $getBranch = $branch->getByField('code', $transArray['detail_branchid']);
                /*get branch name end*/
                $transArray['detail_branchname'] = $getBranch->name;
                $transArray['detail_itemCode'] = $itemCode;
                $transArray['detail_quantity'] = $quantity;
                /*thisis for excel*/

                if($status == 02){ //send to SAP only available to move minted to branch
                    $sendToSap[$countAvailable][$serialnum][] = $transArray; //get only transaction send to SAP
                    $returnArray['itemCode'] = $itemCode;
                    $returnArray['serialNum'] = $serialnum;
                    $returnArray['quantity'] = $quantity;
                    $returnArray['bankId'] = $branchid;
                    $returnArray['customerId'] = $customerId;
                    $returnArray['refNo'] = 'rpl_'.$fileDate.'_'.$i.'_'.$serialnum.'_'.$timeUnix;

                    $responseParams[] = $this->createOutputApiParam($action, ['version' => $version ], $returnArray);
                    $countAvailable++;
                } else {
                    $mbbInv[$countOtherAvailable][$transArray['detail_serialno']][] = $transArray; //other transaction than available
                    $countOtherAvailable++;
                }
            }


            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            if(!empty($responseParams)) $data = $sender->response($app, $responseParams, [ 'url' => $url ]);
            else $descExcel .= "There are no data for PHYINV date ".$fileDate.". \n"; 

            if (isset($data) && !$this->sapReturnVerify($data,'PHYINV')){ 
                $descExcel .= "There are unexpected errors response from SAP. Please check. \n"; 

                if(isset($data['data'][0]['error'])) $descExcel .= $data['data'][0]['error']."\n";
                foreach($data['data'] as $aData){
                    if(isset($aData['serialNum'])) $saveErrorSerialNo[] = $aData['serialNum']; // save in array to take it out when continue to change status
                    /*get branch name*/
                    $branch = $app->partnerStore()->getRelatedStore('branches');
                    $getBranch = $branch->getByField('code', $aData['bankId']);
                    /*get branch name end*/
                    $putToArr[$aData['serialNum']][] = array(
                        "Id" => $aData['id'],
                        "itemCode" => $aData['itemCode'],
                        "serialNum" => $aData['serialNum'],
                        "quantity" => $aData['quantity'],
                        "unitPrice" => $aData['unitPrice'],
                        "whsCode" => $aData['whsCode'],
                        "action" => $aData['action'],
                        "bankId" => $aData['bankId'],
                        "branchName" => $getBranch->name,
                        "customerId" => $aData['customerId'],
                        "refNo" => $aData['refNo'],
                        "success" => $aData['success'],
                        "message" => $aData['message'],
                        "createdDate" => $aData['createdDate'],
                        "data1" => $aData['data1'],
                        "date2" => $aData['date2'],
                        "data3" =>$aData['data3'],
                        "arguments" => $aData['arguments'],
                    );
                }
            } else {
                if(isset($data['data'][0]['Message'])) $descExcel .= $data['data'][0]['Message']." \n";
                else $descExcel .= "All transaction are success request to SAP. \n";
            }

            foreach ($sendToSap as $key=>$value){
                foreach ($value as $aValue){
                    if(empty($saveErrorSerialNo) && !isset($data['data'][0]['error'])) { //checking if there is list of error and other errors
                        $responseSuccessArray[] = $aValue[0];  
                    }
                    else {
                        if (!empty($saveErrorSerialNo) && !in_array($aValue[0]['detail_serialno'], $saveErrorSerialNo)) { //remove error list
                            $responseSuccessArray[] = $aValue[0]; 
                        }
                    }
                }
            }

            $replenishComplete = $app->ftpprocessorManager()->completeReplenishSAP($responseSuccessArray,$partner);

            if($replenishComplete['unableToSave']){
                $descExcel .= "List of serial number getting error to update in GTP replenishment table.\n";
                foreach($replenishComplete['unableToSave'] as $anUpdate){
                    $descExcel .= $anUpdate."\n";
                }
            }
            $descExcel .= "\nSUCCESSFULLY READING.\n";

            $reportpath = $app->getConfig()->{'gtp.ftp.report'};
            $pathToSave = $reportpath.'PHYINV_'.$fileDate.'.xlsx';
            $filename = 'PHYINV_'.$fileDate.'.xlsx';
            $spreadsheet = new Spreadsheet();
            /*1st sheet*/
            $sheet6 = $spreadsheet->getActiveSheet();
            $sheet6->setTitle('DESCRIPTION DETAILS');
            $sheet6->setCellValue('A1', 'Description for PHYINV at '.$dateOfData.' based on '.$ftpHeader.' if any:');
            $sheet6->getColumnDimension('A')->setAutoSize(true);
            $sheet6->setCellValue('A3', $descExcel);
            $sheet6->getStyle('A3')->getAlignment()->setWrapText(true);

            /*2nd sheet*/
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('SUCCESS');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'Successful Minted Goldbar request to SAP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'Comparison between BURSA and SAP inventory');
            $sheet->setCellValue('A4', 'Loading Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B4', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C4', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D4', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E4', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F4', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $j=5;
            foreach($replenishComplete['statusAvailable'] as $key=>$value){
                $sheet->setCellValue('A'.$j, " ".$value['detail_loadingdate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B'.$j, $value['detail_itemCode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C'.$j, $value['detail_serialno'])->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D'.$j, $value['detail_quantity'])->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E'.$j, " ".$value['detail_branchid'])->getColumnDimension('E')->setAutoSize(true);
                $sheet->setCellValue('F'.$j, $value['detail_branchname'])->getColumnDimension('F')->setAutoSize(true);
                $j++;
            }

            //3rd sheet
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(2);
            $sheet2->setTitle('UNSUCCESS');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'Unsuccessful Minted Goldbar request to SAP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet2->mergeCells('A2:I2');
            $sheet2->setCellValue('A2', 'Comparison between BURSA and SAP inventory');
            $sheet2->setCellValue('A4', 'Loading Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B4', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C4', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D4', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E4', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet2->setCellValue('F4', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $sheet2->setCellValue('G4', 'Message from SAP')->getColumnDimension('G')->setAutoSize(true);
            $k=5;
            foreach($putToArr as $key=>$value){
                $sheet2->setCellValue('A'.$k, " ".$value[0]['createdDate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet2->setCellValue('B'.$k, $value[0]['itemCode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet2->setCellValue('C'.$k, $value[0]['serialNum'])->getColumnDimension('C')->setAutoSize(true);
                $sheet2->setCellValue('D'.$k, $value[0]['quantity'])->getColumnDimension('D')->setAutoSize(true);
                $sheet2->setCellValue('E'.$k, " ".$value[0]['bankId'])->getColumnDimension('E')->setAutoSize(true);
                $sheet2->setCellValue('F'.$k, $value[0]['branchName'])->getColumnDimension('F')->setAutoSize(true);
                $sheet2->setCellValue('G'.$k, $value[0]['message'])->getColumnDimension('G')->setAutoSize(true);
                $k++;
            }

            //4th sheet
            $spreadsheet->createSheet();
            $sheet3 = $spreadsheet->setActiveSheetIndex(3);
            $sheet3->setTitle('UNMATCH');
            $sheet3->mergeCells('A1:I1');
            $sheet3->setCellValue('A1', 'Successful request to SAP however does not exist in GTP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet3->mergeCells('A2:I2');
            $sheet3->setCellValue('A2', 'Comparison between BURSA and GTP inventory after SAP request is successful');
            $sheet3->setCellValue('A4', 'Loading Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B4', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C4', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet3->setCellValue('D4', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet3->setCellValue('E4', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet3->setCellValue('F4', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $l=5;
            foreach($replenishComplete['invalidSN'] as $aInvalid){
                $sheet3->setCellValue('A'.$l, " ".$value['detail_loadingdate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet3->setCellValue('B'.$l, $value['detail_itemCode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet3->setCellValue('C'.$l, $value['detail_serialno'])->getColumnDimension('C')->setAutoSize(true);
                $sheet3->setCellValue('D'.$l, $value['detail_quantity'])->getColumnDimension('D')->setAutoSize(true);
                $sheet3->setCellValue('E'.$l, " ".$value['detail_branchid'])->getColumnDimension('E')->setAutoSize(true);
                $sheet3->setCellValue('F'.$l, $value['detail_branchname'])->getColumnDimension('F')->setAutoSize(true);
                $l++;
            }

            //5th sheet
            $spreadsheet->createSheet();
            $sheet5 = $spreadsheet->setActiveSheetIndex(4);
            $sheet5->setTitle('OTHER STATUS');
            $sheet5->mergeCells('A1:I1');
            $sheet5->setCellValue('A1', 'Minted Goldbar status other than "AVAILABLE" as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet5->setCellValue('A3', 'Loading Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet5->setCellValue('B3', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet5->setCellValue('C3', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet5->setCellValue('D3', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet5->setCellValue('E3', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet5->setCellValue('F3', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $sheet5->setCellValue('G3', 'Status')->getColumnDimension('G')->setAutoSize(true);
            $n=4;
            foreach($mbbInv as $key=>$anOther){
                foreach ($anOther as $aValue){
                    /*01 – NEW
                    02 – AVAILABLE (received)
                    03 – REDEEM
                    04 – RETURN
                    05 – LOST/STOLEN
                    06 – MISMATCH
                    07 – DAMAGE
                    */
                    if($aValue[0]['detail_status'] == 1) $statusName = "NEW";
                    elseif($aValue[0]['detail_status'] == 2) $statusName = "AVAILABLE";
                    elseif($aValue[0]['detail_status'] == 3) $statusName = "REDEEM";
                    elseif($aValue[0]['detail_status'] == 4) $statusName = "RETURN";
                    elseif($aValue[0]['detail_status'] == 5) $statusName = "LOST/STOLEN";
                    elseif($aValue[0]['detail_status'] == 6) $statusName = "MISMATCH";
                    elseif($aValue[0]['detail_status'] == 7) $statusName = "DAMAGE";
                    $sheet5->setCellValue('A'.$n, " ".$aValue[0]['detail_loadingdate'])->getColumnDimension('A')->setAutoSize(true);
                    $sheet5->setCellValue('B'.$n, $aValue[0]['detail_itemCode'])->getColumnDimension('B')->setAutoSize(true);
                    $sheet5->setCellValue('C'.$n, $aValue[0]['detail_serialno'])->getColumnDimension('C')->setAutoSize(true);
                    $sheet5->setCellValue('D'.$n, $aValue[0]['detail_quantity'])->getColumnDimension('D')->setAutoSize(true);
                    $sheet5->setCellValue('E'.$n, " ".$aValue[0]['detail_branchid'])->getColumnDimension('E')->setAutoSize(true);
                    $sheet5->setCellValue('F'.$n, $aValue[0]['detail_branchname'])->getColumnDimension('F')->setAutoSize(true);
                    $sheet5->setCellValue('G'.$n, $statusName)->getColumnDimension('G')->setAutoSize(true);
                }
                $n++;
            }

            //6th sheet
            $spreadsheet->createSheet();
            $sheet4 = $spreadsheet->setActiveSheetIndex(5);
            $sheet4->setTitle('BURSA INVENTORY SUMMARY');
            $sheet4->mergeCells('A1:I1');
            $sheet4->setCellValue('A1', 'Inventory Summary by Branch based on MBB Summary as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet4->setCellValue('A3', 'Branch Name')->getColumnDimension('A')->setAutoSize(true);
            $sheet4->setCellValue('B3', 'A(1gm)')->getColumnDimension('B')->setAutoSize(true);
            $sheet4->setCellValue('C3', 'B(5gm)')->getColumnDimension('C')->setAutoSize(true);
            $sheet4->setCellValue('D3', 'C(10gm)')->getColumnDimension('D')->setAutoSize(true);
            $sheet4->setCellValue('E3', 'D(50gm)')->getColumnDimension('E')->setAutoSize(true);
            $sheet4->setCellValue('F3', 'E(100gm)')->getColumnDimension('F')->setAutoSize(true);
            $sheet4->setCellValue('G3', 'F(1000gm)')->getColumnDimension('G')->setAutoSize(true);
            $m=4;
            foreach($summaryInventory as $key=>$anInventory){
                if(!empty($key)){
                    /*get branch name*/
                    $branch = $app->partnerStore()->getRelatedStore('branches');
                    $getBranch = $branch->getByField('code', $key);
                    /*get branch name end*/
                    $branchname = $getBranch->name."(".$key.")";
                    $denomA = $anInventory['A'];
                    $denomB = $anInventory['B'];
                    $denomC = $anInventory['C'];
                    $denomD = $anInventory['D'];
                    $denomE = $anInventory['E'];
                    $denomF = $anInventory['F'];

                    $sheet4->setCellValue('A'.$m, $branchname)->getColumnDimension('A')->setAutoSize(true);
                    $sheet4->setCellValue('B'.$m, " ".$denomA)->getColumnDimension('B')->setAutoSize(true);
                    $sheet4->setCellValue('C'.$m, " ".$denomB)->getColumnDimension('C')->setAutoSize(true);
                    $sheet4->setCellValue('D'.$m, " ".$denomC)->getColumnDimension('D')->setAutoSize(true);
                    $sheet4->setCellValue('E'.$m, " ".$denomD)->getColumnDimension('E')->setAutoSize(true);
                    $sheet4->setCellValue('F'.$m, " ".$denomE)->getColumnDimension('F')->setAutoSize(true);
                    $sheet4->setCellValue('G'.$m, " ".$denomF)->getColumnDimension('G')->setAutoSize(true);
                    $m++;
                } 
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyUpdateReturn($app, $transaction,$version,$flag)
    {
        $transformToArray = json_decode($transaction,true);
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.replenishment.url'}; //edit if variable url is different in config.ini
            $action = 'tfreturn';

            $urlKilobar = $app->getConfig()->{'gtp.sap.kilobar.url'}; //edit if variable url is different in config.ini
            $actionKilobar = 'goldreturn';

            $partner = $transformToArray['partner'];
            /*[partner] => Array(
                    [id] => 1
                    [code] => MBISMY@SIT
                    [name] => Maybank Islamic Gold Account
                )
            */
            $getPartner = $app->partnerStore()->getByField('id', $partner['id']);
            $customerId = $getPartner->sapcompanysellcode1;

            $detailCount = $transformToArray[0]['header_totalline']+1;
            $fileDate = $transformToArray[0]['header_filedate'];
            $date               = date('Y-m-d h:i:s',$transformToArray['currentdate']);
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd,"-1 day");
            $dateOfData         = date_format($modifyDateEnd,"d-m-Y");
            $ftpHeader = $transformToArray[0]['header_code'].str_pad($transformToArray[0]['header_filename'],10, ' ', STR_PAD_RIGHT).$transformToArray[0]['header_filedate'].$transformToArray[0]['header_filesequence'].$transformToArray[0]['header_totalline'];

            $t=time();
            $timeUnix = date("His",$t);

            for($i=1;$i<$detailCount;$i++){
                $transArray = $transformToArray[$i];
                if('A' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-1g';
                    $quantity = 1;
                }
                if('B' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-5g';
                    $quantity = 5;
                }
                if('C' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-10g';
                    $quantity = 10;
                }
                if('D' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-50g';
                    $quantity = 50;
                }
                if('E' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-100g';
                    $quantity = 100;
                }
                if('F' == $transArray['detail_denomination']) {
                    $itemCode = 'GS-999-9-1000g';
                    $quantity = 1000;
                }
                $reason = strtolower($transArray['detail_returnreason']);
                /*thisis for excel*/
                /*get branch name*/
                $branch = $app->partnerStore()->getRelatedStore('branches');
                $getBranch = $branch->getByField('code', $transArray['detail_branchid']);
                /*get branch name end*/
                $transArray['detail_branchname'] = $getBranch->name;
                $transArray['detail_itemCode'] = $itemCode;
                $transArray['detail_quantity'] = $quantity;
                $transArray['detail_reason'] = $reason;
                /*thisis for excel*/

                $checkKiloOrMinted = $app->ftpprocessorManager()->checkReturnCategory($transArray,$partner);
                if(isset($checkKiloOrMinted['dgvreturn'])) $kilobarList[] = $checkKiloOrMinted['dgvreturn'];
                if(isset($checkKiloOrMinted['mintedreturn'])) $mintedList[] = $checkKiloOrMinted['mintedreturn'];
                if(isset($checkKiloOrMinted['notfound'])) $notFoundList[] = $checkKiloOrMinted['notfound'];

                $fullList[$transArray['detail_serialno']][] = $transArray;
            }

            $countMinted = 1;
            $countKilobar = 1;
            $branch = $app->partnerStore()->getRelatedStore('branches');

            if(count($mintedList) > 0){
                foreach($mintedList as $aMintedList){
                    $serialno = $aMintedList['detail_serialno'];
                    $branchid = $aMintedList['detail_branchid'];
                    $reason = strtolower($aMintedList['detail_reason']);

                    if($reason != 'buyback'){
                        $returnArray['itemCode'] = $aMintedList['detail_itemCode'];
                        $returnArray['serialNum'] = $serialno;
                        $returnArray['quantity'] = $aMintedList['detail_quantity'];
                        $returnArray['bankId'] = $branchid;
                        $returnArray['returnReason'] = $aMintedList['detail_reason'];
                        $returnArray['customerId'] = $customerId;
                        $returnArray['refNo'] = 'return_'.$fileDate.'_'.$countMinted.'_'.$serialno.'_'.$timeUnix;
                        $responseParams[] = $this->createOutputApiParam($action, ['version' => $version ], $returnArray);
                        $sendToLogisticAfterSuccess[] = $aMintedList;
                    } else {
                        /*proceed with logistic buyback*/
                        $sendToLogistic[] = $aMintedList;
                    }
                    $countMinted++;
                }

                if(!empty($sendToLogistic)){
                    /*send buyback to logistic*/
                    $logisticInsert = $app->ftpprocessorManager()->logisticReturn($sendToLogistic,$partner);
                }

                if(!empty($sendToLogisticAfterSuccess)){
                    $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
                    $data = $sender->response($app, $responseParams, [ 'url' => $url ]);

                    if (isset($data) && !$this->sapReturnVerify($data,'PHYRTN')){ 
                        $descExcel .= "There are unexpected errors response for minted from SAP. Please check. \n"; 
                        if(isset($data['data'][0]['error'])) $descExcel .= $data['data'][0]['error']."\n";
                        foreach($data['data'] as $aData){
                            if(isset($aData['serialNum'])) $saveErrorSerialNo[] = $aData['serialNum']; // save in array to take it out when continue to change status
                            /*get branch name*/
                            $getBranch = $branch->getByField('code', $aData['bankId']);
                            /*get branch name end*/
                            $putToArr[$aData['serialNum']][] = array(
                                "Id" => $aData['id'],
                                "itemCode" => $aData['itemCode'],
                                "serialNum" => $aData['serialNum'],
                                "quantity" => $aData['quantity'],
                                "unitPrice" => $aData['unitPrice'],
                                "whsCode" => $aData['whsCode'],
                                "action" => $aData['action'],
                                "bankId" => $aData['bankId'],
                                "branchName" => $getBranch->name,
                                "customerId" => $aData['customerId'],
                                "refNo" => $aData['refNo'],
                                "success" => $aData['success'],
                                "message" => $aData['message'],
                                "createdDate" => $aData['createdDate'],
                                "data1" => $aData['data1'],
                                "date2" => $aData['date2'],
                                "data3" =>$aData['data3'],
                                "arguments" => $aData['arguments'],
                            );
                        }
                    } else {
                        /*to send for logistic after success return sap*/
                        $logisticInsert = $app->ftpprocessorManager()->logisticReturn($sendToLogisticAfterSuccess,$partner);
                        //unableToSaveMinted/notFoundMinted/unableToSaveLogistic/unableToSaveBuybackLogistic/alreadySaveLogistic
                        if(isset($data['data'][0]['Message'])) $descExcel .= $data['data'][0]['Message']." \n";
                        else $descExcel .= "All minted transaction are success request to SAP. \n";
                    }
                }
            } 

            if(count($kilobarList) > 0){
                foreach($kilobarList as $aKilobarList){
                    $serialno = $aKilobarList['detail_serialno'];
                    if(!empty($aKilobarList['detail_branchid']) || $aKilobarList['detail_branchid'] != null) $branchid = $aKilobarList['detail_branchid'];
                    else $branchid = 'null';
                    $getVaultObj = $app->vaultItemStore()->getByField('serialno', $serialno);
                    $decodedData['vaultItem'] = $getVaultObj;
                    $decodedData['product'] = 'DG-999-9';
                    $decodedData['branchid'] = $branchid;
                    $decodedData['refNo'] = $getVaultObj->deliveryordernumber."_".$getVaultObj->id.'_'.$timeUnix;
                    $decodedData['whsCode'] = $app->getConfig()->{'gtp.vault.sapcode'};
                    $decodedData['customerId'] = $customerId;
                    $responseParamsKilobar[] = $this->createOutputApiParam($actionKilobar, ['version' => $version ], $decodedData);
                    $countKilobar++;
                }

                $senderKilobar = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
                $dataKilobar = $senderKilobar->response($app, $responseParamsKilobar, [ 'url' => $urlKilobar ]);

                if (isset($dataKilobar) && !$this->sapReturnVerify($dataKilobar,'PHYRTN')){ 
                    $descExcel .= "There are unexpected errors response for DGV from SAP. Please check. \n"; 
                    if(isset($dataKilobar['data'][0]['error'])) $descExcel .= $dataKilobar['data'][0]['error']."\n";
                    foreach($dataKilobar['data'] as $aData){
                        if(isset($aData['serialNum'])) $saveErrorSerialNo[] = $aData['serialNum']; // save in array to take it out when continue to change status
                        /*get branch name*/
                        $getBranch = $branch->getByField('code', $aData['bankId']);
                        /*get branch name end*/
                        $putToArr[$aData['serialNum']][] = array(
                            "Id" => $aData['id'],
                            "itemCode" => $aData['itemCode'],
                            "serialNum" => $aData['serialNum'],
                            "quantity" => $aData['quantity'],
                            "unitPrice" => $aData['unitPrice'],
                            "whsCode" => $aData['whsCode'],
                            "action" => $aData['action'],
                            "bankId" => $aData['bankId'],
                            "branchName" => $getBranch->name,
                            "customerId" => $aData['customerId'],
                            "refNo" => $aData['refNo'],
                            "success" => $aData['success'],
                            "message" => $aData['message'],
                            "createdDate" => $aData['createdDate'],
                            "data1" => $aData['data1'],
                            "date2" => $aData['date2'],
                            "data3" =>$aData['data3'],
                            "arguments" => $aData['arguments'],
                        );
                    }
                } else {
                    /*update status vaultitem*/
                    $vaultitem = $app->ftpprocessorManager()->kilobarDVGReturn($kilobarList,$partner);
                    if(isset($dataKilobar['data'][0]['Message'])) $descExcel .= $dataKilobar['data'][0]['Message']." \n";
                    else $descExcel .= "All kilobar transaction are success request to SAP. \n";
                }
            }

            if(count($notFoundList) > 0){
                foreach($notFoundList as $notFound){
                    $saveErrorSerialNo[] = $notFound['detail_serialno'];
                    /*get branch name*/
                    $getBranch = $branch->getByField('code', $notFound['detail_branchid']);
                    /*get branch name end*/
                    $putToArr[$notFound['detail_serialno']][] = array(
                        "itemCode" => $notFound['detail_itemCode'],
                        "serialNum" => $notFound['detail_serialno'],
                        "quantity" => $notFound['detail_quantity'],
                        "data3" =>$notFound['detail_reason'],
                        "message" => "Unable to find in GTP",
                    );
                } 
            }
            
            foreach ($fullList as $key=>$value){
                foreach ($value as $aValue){
                    if(empty($saveErrorSerialNo) && (!isset($data['data'][0]['error']) || !isset($dataKilobar['data'][0]['error']))) {
                        $responseSuccessArray[] = $aValue; //checking if there is list of error
                    }
                    else {
                        if (!empty($saveErrorSerialNo) && !in_array($aValue['detail_serialno'], $saveErrorSerialNo)) {
                            $responseSuccessArray[] = $aValue; //remove error list
                        }
                    }
                }
            }
            $descExcel .= "\nSUCCESSFULLY READING.\n";

            $mergeData = array_merge_recursive($data , $dataKilobar);            
            $reportpath = $app->getConfig()->{'gtp.ftp.report'};
            $pathToSave = $reportpath.'PHYRTN_'.$fileDate.'.xlsx';
            $filename = 'PHYRTN_'.$fileDate.'.xlsx';
            $spreadsheet = new Spreadsheet();
            /*1st sheet*/
            $sheet3 = $spreadsheet->getActiveSheet();
            $sheet3->setTitle('DESCRIPTION DETAILS');
            $sheet3->setCellValue('A1', 'Description for PHYRTN at '.$dateOfData.' based on '.$ftpHeader.' if any:');
            $sheet3->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('A3', $descExcel);
            $sheet3->getStyle('A3')->getAlignment()->setWrapText(true);

            //2nd sheet
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex(1);
            $sheet->setTitle('SUCCESS');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', 'Successful Minted Goldbar return request to SAP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', 'Comparison between BURSA and SAP inventory. Noted: Buyback minted does not trigger SAP when process PHYRTN ftp.');
            $sheet->setCellValue('A4', 'Return Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B4', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C4', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D4', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E4', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet->setCellValue('F4', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $sheet->setCellValue('G4', 'Reason of Return')->getColumnDimension('G')->setAutoSize(true);
            $j=5;
            foreach($responseSuccessArray as $key=>$value){
                $sheet->setCellValue('A'.$j, " ".$value['detail_returndate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B'.$j, $value['detail_itemCode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C'.$j, $value['detail_serialno'])->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D'.$j, $value['detail_quantity'])->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E'.$j, " ".$value['detail_branchid'])->getColumnDimension('E')->setAutoSize(true);
                $sheet->setCellValue('F'.$j, $value['detail_branchname'])->getColumnDimension('F')->setAutoSize(true);
                $sheet->setCellValue('G'.$j, $value['detail_reason'])->getColumnDimension('G')->setAutoSize(true);
                $j++;
            }   

            //3rd sheet
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(2);
            $sheet2->setTitle('UNSUCCESS');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'Unsuccessful Minted Goldbar return request to SAP as at '.$dateOfData.' based on '.$ftpHeader);
            $sheet2->mergeCells('A2:I2');
            $sheet2->setCellValue('A2', 'Comparison between BURSA and SAP inventory');
            $sheet2->setCellValue('A4', 'Return Date')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B4', 'Item Code')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C4', 'Serial Number')->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D4', 'Item Deno')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E4', 'Bank Id')->getColumnDimension('E')->setAutoSize(true);
            $sheet2->setCellValue('F4', 'Bank Name')->getColumnDimension('F')->setAutoSize(true);
            $sheet2->setCellValue('G4', 'Reason of Return')->getColumnDimension('G')->setAutoSize(true);
            $sheet2->setCellValue('H4', 'Message from SAP')->getColumnDimension('H')->setAutoSize(true);
            $k=5;
            foreach($putToArr as $key=>$value){
                $sheet2->setCellValue('A'.$k, " ".$value[0]['createdDate'])->getColumnDimension('A')->setAutoSize(true);
                $sheet2->setCellValue('B'.$k, $value[0]['itemCode'])->getColumnDimension('B')->setAutoSize(true);
                $sheet2->setCellValue('C'.$k, $value[0]['serialNum'])->getColumnDimension('C')->setAutoSize(true);
                $sheet2->setCellValue('D'.$k, $value[0]['quantity'])->getColumnDimension('D')->setAutoSize(true);
                $sheet2->setCellValue('E'.$k, " ".$value[0]['bankId'])->getColumnDimension('E')->setAutoSize(true);
                $sheet2->setCellValue('F'.$k, $value[0]['branchName'])->getColumnDimension('F')->setAutoSize(true);
                $sheet2->setCellValue('G'.$k, $value[0]['data3'])->getColumnDimension('G')->setAutoSize(true);
                $sheet2->setCellValue('H'.$k, $value[0]['message'])->getColumnDimension('H')->setAutoSize(true);
                $k++;
            }   

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);

            return $mergeData;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyFORecon($app, $transaction,$version,$flag)
    {
        $transformToArray = json_decode($transaction,true);
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $reconFOItem = $app->ftpprocessorManager()->reconFO($transformToArray,$flag);
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyDailyDVG($app, $transaction,$version,$flag)
    {
        $transformToArray = json_decode($transaction,true);
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $utilisedItem = $app->ftpprocessorManager()->utilisedDVG($transformToArray,$flag);            
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyDailyTrans($app, $transaction,$version,$flag)
    {
        $transformToArray = json_decode($transaction,true);
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $utilisedItem = $app->ftpprocessorManager()->dailyReconTrans($transformToArray,$flag);
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyCourierRecon($app, $transaction)
    {
        /*
        MBB response at email 'Fwd: MIGA-i FTP progress':
        PHYCOUREC
        Maybe for your keeping purposes
        */
    }

    public function getItemList($app, $partner,$version)
    {
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.itemlist.url'}; //edit if variable url is different in config.ini
            $action = 'stocklist';
            $decodedData['partner'] = $partner;

            $responseParams = $this->createOutputApiParam($action, ['version' => $version ], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, [ 'url' => $url ]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function getWarehouseList($app, $partner,$version)
    {
        if(preg_match('/[0-9\.]+b$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.warehouselist.url'}; //edit if variable url is different in config.ini
            $action = 'whslist';
            $decodedData['partner'] = $partner;

            $responseParams = $this->createOutputApiParam($action, ['version' => $version ], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, [ 'url' => $url ]);

            $getResponse = $data['data'];
            foreach($getResponse as $key=>$value){
                $inventory[$value['BinCode']][$value['ItemCode']] = array(
                    'OnHandQty' =>$value['OnHandQty'],
                    'OpenQty' =>$value['OpenQty'],
                );

                $denom[$value['ItemCode']] += $value['OnHandQty'];
            }
            $data['data']['inventoryList'] = $inventory; // added massaging data
            $data['data']['denominationList'] = $denom; // added massaging data

            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                        'action_type' => $apiParam->getActionType(),
                        'processor_class' => __CLASS__]);
        }
    }

    public function notifyReturnKilobar($app, $vaultitem)
    {
        $action = "goldreturn";
        $url = $app->getConfig()->{'gtp.sap.kilobar.url'};
        if(is_array($vaultitem)){
            $i = 1;
            foreach($vaultitem as $key=>$value){
                $partner = $app->partnerStore()->getByField('id',$value['partnerid']);
                if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
                $vaultitemobj = $app->vaultitemStore()->getByField('id',$value['id']);
                $getVersionChoice = explode(',', $app->getConfig()->{'gtp.version.list'}); //this is to get version of partner. set at config.ini
                foreach($getVersionChoice as $key=>$value){
                    $splitVersion = explode('||', $value); 
                    if($partner->sapcompanysellcode1 == $splitVersion[0]) {
                        $version = $splitVersion[1];
                        if($splitVersion[2]) $addName = '-'.$splitVersion[0];
                    }
                }
                $decodedData['vaultItem'] = $vaultitemobj;
                $decodedData['product'] = 'DG-999-9'.$addName;
                $decodedData['refNo'] = $value['deliveryordernumber']."_".$value['id'];
                /*added for dynamic value*/
                $decodedData['whsCode'] = $app->getConfig()->{'gtp.vault.sapcode'};
                $decodedData['customerId'] = $sapcustomerid;
                /**/
                $responseParams[] = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
                $i++;
            }
        } else {
            $partner = $app->partnerStore()->getByField('id',$vaultitem->partnerid);
            if($partner->sapcompanysellcode1 == $partner->sapcompanybuycode1) $sapcustomerid = $partner->sapcompanysellcode1;
            $getVersionChoice = explode(',', $app->getConfig()->{'gtp.version.list'}); //this is to get version of partner. set at config.ini
            foreach($getVersionChoice as $key=>$value){
                $splitVersion = explode('||', $value); 
                if($partner->sapcompanysellcode1 == $splitVersion[0]) {
                    $version        = $splitVersion[1];
                    if($splitVersion[2]) $addName = '-'.$splitVersion[0];
                }
            }
            $decodedData['vaultItem'] = $vaultitem;
            $decodedData['product'] = 'DG-999-9'.$addName;
            $decodedData['refNo'] = $vaultitem->deliveryordernumber."_".$vaultitem->id;
            /*added for dynamic value*/
            $decodedData['whsCode'] = $app->getConfig()->{'gtp.vault.sapcode'};
            $decodedData['customerId'] = $sapcustomerid;
            /**/
            $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
        }
        $sender = \Snap\api\sap\SapApiSender::getInstance('http', null);
        $data = $sender->response($app, $responseParams, ['url' => $url]);
        return $data;
    }

    public function notifyNewReserveSerialNum($app, $redemption) //bursa not use this one because only do redemption delivery
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.reserveserialnum.url'}; //edit if variable url is different in config.ini
            $action = 'reserveserialnum';
            $items = json_decode($redemption->items);

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            foreach ($items as $x => $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->denomination); //grab product table using weight==denomination
                //$xauquantity = str_replace("g","",$aItem->denomination);
                $aRedemption['itemCode']    = $product->sapitemcode;
                $aRedemption['serialNum']   = $this->convertSerialNoToSap($aItem->serialno);
                $aRedemption['quantity']    = (float)$aItem->denomination;
                $aRedemption['whsCode']     = 'MIB_BNK'; //if bursa use this function, need to ask SAP the value for this parameter
                $aRedemption['bankId']      = $redemption->branchid;
                $aRedemption['customerId']  = $customerId;

                $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
            }

            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function notifyNewUnreserveSerialNum($app, $redemption) //bursa not use this one because only do redemption delivery
    {
        if (preg_match('/[0-9\.]+b$/', $redemption->apiversion)) { //digital gold
            $url = $app->getConfig()->{'gtp.sap.unreserveserialnum.url'}; //edit if variable url is different in config.ini
            $action = 'unreserveserialnum';
            $items = json_decode($redemption->items);

            //$getPartner = $redemption->getPartner();
            $getPartner = $app->partnerStore()->getByField('id', $redemption->partnerid);
            $customerId = $getPartner->sapcompanysellcode1;

            foreach ($items as $x => $aItem) {
                $product = $app->productStore()->getByField('weight',$aItem->weight); //grab product table using weight==denomination
                //$xauquantity = str_replace("g","",$aItem->denomination);
                $aRedemption['itemCode'] = $product->sapitemcode;
                $aRedemption['serialNum'] = $this->convertSerialNoToSap($aItem->serialnumber);
                $aRedemption['quantity'] = (float)$aItem->weight;
                $aRedemption['whsCode'] = 'MIB_BNK'; //if bursa use this function, need to ask SAP the value for this parameter
                $aRedemption['bankId'] = str_pad($redemption->branchid, 5, '0', STR_PAD_LEFT); //need to add 0 when length less than 5
                $aRedemption['customerId'] = $customerId;

                $responseParams[] = $this->createOutputApiParam($action, ['version' => $redemption->apiversion], $aRedemption);
            }
            $sender = \Snap\api\sap\SapApiSender::getInstance('http', $responseData);
            $data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }
}
