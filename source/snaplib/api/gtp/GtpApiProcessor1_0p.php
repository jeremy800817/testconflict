<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\api\gtp\GtpApiProcessor;
Use Snap\api\param\ApiParam;
use Snap\api\sap\SapApiProcessor;
Use \Snap\object\Order;
Use \Snap\object\OrderQueue;
Use \Snap\object\Redemption;


/**
 * Specialised GTP API processor for POS implementation
 *
 * @author Dianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpApiProcessor1_0p extends GtpApiProcessor
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
        switch($apiParam->getActionType()) {
            case 'price_acebuy':
            case 'price_acesell':
                return $this->doPriceQuery($app, $apiParam, $decodedData, $requestParams, $apiParam->getActionType());
                break;
            case 'buyback':
                return $this->doBuyBack($app, $apiParam, $decodedData, $requestParams, $apiParam->getActionType());
                break;
        }
        return parent::process($app, $apiParam, $decodedData, $requestParams);
    }

    private function doPriceQuery($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $buyOrSell)
    {
        try {
            $toSell = preg_match('/sell/i', $buyOrSell);
            $version = strtolower($requestParams['version']);
            $priceValidation = $app->priceManager()->getSpotPrice($decodedData['partner'], $decodedData['product'], $requestParams['reference'], ($toSell ? false : true), $version);
            $decodedData['priceValidation'] = $priceValidation;

            $responseParams = $this->createOutputApiParam('queryPriceResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    private function doBuyBack($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try{
            $version = strtolower($requestParams['version']);
            $getItemArray = json_decode($requestParams['item'],true);

            $branch = $decodedData['partner']->getBranch($requestParams['branch_id']);
            if ($branch){
                $branchid = $branch->id;
                $branch_remarks = $branch->name;
            }else{
                // if pos/operator dint update their `NEW` branch in GTP system.
                $branchid = $requestParams['branch_id'];
                $branch_remarks = $requestParams['branch_name'];
            }

            $buybackValidation = $app->buybackManager()->doBuyback($decodedData['partner'], $version, $requestParams['reference_no'], $branchid, $getItemArray, $requestParams['buyback_xau'], $requestParams['buyback_total_price'], count($getItemArray), 0, $branch_remarks, $requestParams['buyback_goldprice'], $requestParams['price_request_id'],$decodedData['product']);
            $decodedData['buyback'] = $buybackValidation;

            // $confirmBuyBack = $app->buybackManager()->confirmBuyback($buybackValidation);
            // $decodedData['confirmBuyBack'] = $confirmBuyBack;

            $app->GoodsReceivedNoteManager()->createDraftGRN($buybackValidation, $requestParams['branch_id'], $requestParams['reference_no'], $decodedData['product']->id, $getItemArray);

            $responseParams = $this->createOutputApiParam('buybackResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }

    /*private function doBuyBack($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $buyback)
    {
        try{
            $version = strtolower($requestParams['version']);
            $getItemArray = json_decode($requestParams['item'],true);

            $orderValidation = $app->spotorderManager()->bookOrder($decodedData['partner'], $version, $requestParams['reference_no'], Order::TYPE_COMPANYBUY, $decodedData['product'], $requestParams['price_request_id'], null, 'weight', $requestParams['buyback_xau'], $requestParams['buyback_goldprice'], $requestParams['buyback_total_price'], '', $requestParams['reference'], $requestParams['timestamp'],false);
            $decodedData['order'] = $orderValidation;

            $confirmOrder = $app->spotorderManager()->confirmBookOrder($orderValidation);
            $decodedData['confirmorder'] = $confirmOrder;

            $app->GoodsRecievedNoteManager()->createDraftGRN($confirmOrder, $requestParams['branch_id'], $requestParams['reference_no'], $requestParams['product'], $getItemArray);

            $responseParams = $this->createOutputApiParam('spotOrderResponse', $requestParams, $decodedData);
            $sender = \Snap\api\gtp\GtpApiSender::getInstance('Json', $responseData);
            $sender->response($app, $responseParams);
            return $responseParams;
        }
        catch(\Snap\api\exception\ApiException $e) {
            return $this->formatException($e);
        }
    }*/
}
?>