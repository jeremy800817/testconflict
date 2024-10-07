<?php

namespace Snap\api\sap;

use Snap\api\param\ApiParam;
use Snap\App;
use Snap\api\sap\SapApiProcessor;
use Snap\object\Order;
use Snap\object\Buyback;
use Snap\object\PartnerBranchMap;
use Snap\object\Product;
use Snap\object\Redemption;
use Snap\object\Partner;
use Snap\api\param\SapApiParam1_0;


class SapApiProcessor1_0 extends SapApiProcessor {
    const IS_HTTP = "isHttp";

    public function process($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        switch ($apiParam->getActionType()) {
            case 'partnerlist':
                return $this->getBusinessList($app, $apiParam, $decodedData, $requestParams);

            case 'itemlist':
                return $this->getItemList($app, $apiParam, $decodedData, $requestParams);

            case 'ratecard':
                return $this->getRateCard($app, $apiParam, $decodedData, $requestParams);

            case 'openpo':
                return $this->getOpenPoList($app, $apiParam, $decodedData, $requestParams);

            case 'salesorder':
            case 'purchaseorder':
                return $this->getPostedOrders($app, $apiParam, $decodedData, $requestParams);

            case 'grndraft':
                return $this->getGrnDraft($app, $apiParam, $decodedData, $requestParams);

            case 'statement':
                return $this->getStatement($app, $apiParam, $decodedData, $requestParams);

        }

        return parent::process($app, $apiParam, $decodedData, $requestParams);
    }

    public function getItemList($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];
        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.itemlist2.url'}; //edit if variable url is different in config.ini
            if (isset($decodedData['warehouse'])) {
                $url .= "/{$decodedData['warehouse']}";

                if (isset($decodedData['item'])) {    
                    $url .= "/{$decodedData['item']}";
                }
            } else if (isset($decodedData['item'])) {
                $url .= "/all/{$decodedData['item']}";
            }

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);
            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function getWarehouseList($app, $partner)
    {
        $version = '1.0m';
        if (preg_match('/[0-9\.]+m$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.warehouselist.url'}; //edit if variable url is different in config.ini
            $action = 'whslist';
            $decodedData['partner'] = $partner;

            $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
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

    public function getBusinessList($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];
        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.businesspartnerlist.url'}; //edit if variable url is different in config.ini
            $data = $decodedData;

            // Append to URL depending on option
            if ($data['option'] == 'vendor') {
                $url = $url . '/s';
            } elseif ($data['option'] == 'customer') {
                $url = $url . '/c';
            }

            // If code is passed, we append that to the URL too
            if (('customer' == $data['option'] || 'vendor' == $data['option']) && isset($data['code'])) {
                $url .= "/{$data['code']}";
            }

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);   // Just return the data directly.
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);
            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Retrives the list of rate cards. 
     * Example format on success : 
     * $data = [
     *      'ratecard => [{
     *          "u_cardcode": "VTP000",
     *          "u_itemcode": "GS-ScrapBar",
     *          "u_purity"  : 0.000000
     *      }, ...]
     * ]
     * 
     * 
     */
    public function getRateCard($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.ratecard.url'}; //edit if variable url is different in config.ini
            if (isset($decodedData['code'])) {
                $url .= "/{$decodedData['code']}";
                if (isset($decodedData['item'])) {
                    $url .= "/{$decodedData['item']}";
                }
            }

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);
            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Retrieve Open PO List
     */
    public function getOpenPoList($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.openpo.url'}; //edit if variable url is different in config.ini

            $url .= $decodedData['verification'] ? "/y" : "/n";
            $url .= "/".$decodedData['code'];

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);

            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Retrieve posted orders
     */
    public function getPostedOrders($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];
        $action = $apiParam->getActionType();

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{ (('salesorder' == $action)  ? 'gtp.sap.salesorder.url' : 'gtp.sap.purchaseorder.url')}; //edit if variable url is different in config.ini
            $url .= $decodedData['isgtpref'] ? "/ref" : "/int";
            $url .= "/{$decodedData['key']}";

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);

            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $action,
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Posts sales order to SAP
     */
    public function postSalesOrder($app, Order $order, Product $product, $originalRequestParams) {
        $version = $originalRequestParams['version'];
        $action = 'postsalesorder';
        $partner = $app->partnerStore()->getByField('id', $order->partnerid);

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.salesorder.url'}; //edit if variable url is different in config.ini

            $sapParams['product']   = $product;
            $sapParams['order']     = $order;
            $sapParams['partner']   = $partner;
            $sapParams['cardcode']  = $partner->sapcompanysellcode1;
            $sapParams['comments']  = (string)$order->partnerrefid;
            $sapParams = array_merge($originalRequestParams, $sapParams);
        
            $responseParams = $this->createOutputApiParam($action, $originalRequestParams, $sapParams);
            $sender = \Snap\api\sap\SapApiSender::getInstance('', $version);

            $data = $sender->request('POST', $url, ['form_params'=>$responseParams]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $action,
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Posts purchase order to SAP
     */
    public function postPurchaseOrder($app, Order $order, Product $product, $originalRequestParams) {
        $version = $originalRequestParams['version'];
        $action = 'postpurchaseorder';
        $partner = $app->partnerStore()->getByField('id', $order->partnerid);

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.purchaseorder.url'}; //edit if variable url is different in config.ini

            $sapParams['product']   = $product;
            $sapParams['order']     = $order;
            $sapParams['partner']   = $partner;
            $sapParams['cardcode']  = $partner->sapcompanybuycode1;
            $sapParams['comments']  = (string)$order->partnerrefid;
            $sapParams = array_merge($originalRequestParams, $sapParams);
        
            $responseParams = $this->createOutputApiParam($action, $originalRequestParams, $sapParams);
            $sender = \Snap\api\sap\SapApiSender::getInstance('', $version);

            $data = $sender->request('POST', $url, ['form_params'=>$responseParams]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $action,
                'processor_class' => __CLASS__
            ]);
        }
    }

    /**
     * Posts sales order to SAP
     */
    public function postPosPurchaseOrder($app, Buyback $buyback, Product $product, Partner $partner, $originalRequestParams) {
        $version = $originalRequestParams['version'];
        $action = 'postpospurchaseorder';

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.purchaseorder.url'}; //edit if variable url is different in config.ini

            $sapParams['product'] = $product;
            $sapParams['buyback']   = $buyback;
            $sapParams['partner']   = $partner;
            $sapParams = array_merge($originalRequestParams, $sapParams);
        
            $responseParams = $this->createOutputApiParam($action, $originalRequestParams, $sapParams);
            $sender = \Snap\api\sap\SapApiSender::getInstance('', $version);

            $data = $sender->request('POST', $url, ['form_params'=>$responseParams]);
            //$data = $sender->response($app, $responseParams, ['url' => $url]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $action,
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function getGrnDraft($app, ApiParam $apiParam, $decodedData, $requestParams) {
        $version = $requestParams['version'];
        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.grndraft.url'}; //edit if variable url is different in config.ini

            $url .= $decodedData['isgtpref'] ? "/ref" : "/int";
            $url .= "/{$decodedData['key']}";

            // $responseParams = $this->createOutputApiParam($action, ['version' => $version], $decodedData);
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);

            $data = $sender->response($app, '', ['url' => $url], 'GET');
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $apiParam->getActionType(),
                'processor_class' => __CLASS__
            ]);
        }
    }

    public function postGrnDraft($app, $gtpNo, $sapCode, array $selectedPos, array $items, $requestParams, $comments = null) {
        $version = $requestParams['version'];
        $action  = "postgrndraft";
        $app = App::getInstance();

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.grndraft.url'}; //edit if variable url is different in config.ini

            $sapParams['gtpNo']   = $gtpNo;
            $sapParams['sapCode'] = $sapCode;
            $sapParams['selectedPO']   = $selectedPos;
            $sapParams['items']   = $items;
            $sapParams['comments']   = $comments;
            $sapParams = array_merge($requestParams, $sapParams);
        
            $responseParams = $this->createOutputApiParam($action, $requestParams, $sapParams);
            // print_r(json_encode($responseParams));exit;
            $sender = \Snap\api\sap\SapApiSender::getInstance('Json', $version);

            $data = $sender->request('POST', $url, ['form_params'=>$responseParams]);
            return $data;
        } else {
            $this->log("No sender available to process this.", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                'action_type' => $action,
                'processor_class' => __CLASS__
            ]);
        }        

    }

    public function getStatement($app, ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = $requestParams['version'];

        if (preg_match('/[0-9\.]+$/', $version)) {
            $url = $app->getConfig()->{'gtp.sap.transactionstatement.url'}; //edit if variable url is different in config.ini
            $sender = \Snap\api\sap\SapApiSender::getInstance('Pdf', $version);

            $postParams = $this->createOutputApiParam("poststatement", $requestParams, $decodedData);

            $data = $sender->request('POST', $url, [
                'form_params' => $postParams,
                self::IS_HTTP => $this->getCalledFromHttp()
            ]);
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