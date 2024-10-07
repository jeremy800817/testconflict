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
 * Specialised GTP API processor for Maybank implementation
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpApiProcessor1_0d1 extends GtpApiProcessor
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

}
?>