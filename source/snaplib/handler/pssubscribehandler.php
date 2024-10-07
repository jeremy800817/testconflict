<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

Use Snap\App;
use Snap\IHandler;
use Snap\InputException;
/**
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class pssubscribeHandler implements \Snap\IHandler
{
    /** @var App $app */
    private $app = null;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function addChild(IHandler $child)
    {
        //throw new \Exception("Not supported");
    }

    public function getRights($action)
    {
        return '/all/access';
    }

    public function canHandleAction($app, $action)
    {
        return true;
    }
    

    public function doAction($app, $action, $params)
    {
        try {
            if ("subscribe" ===  $action) {
                return $this->subscribeToProductStream($app, $params);
            } else if ("subscribesales" === $action){
                return $this->subscribeToProductStreamSales($app, $params);
            } else if ("subscribenotification" === $action){
                return $this->subscribeToNotification($app, $params);
            } else if ("publishnotification" === $action){
                return $this->postToNotificationChannel($app, $params);
            } else {
                throw new \Exception("Not supported");
            }

        } catch (\Exception $e) {
            // Respond with non 200 (HTTP OK) response to signal failure
            http_response_code(403);
            throw $e;
        }

    }

    private function subscribeToProductStream($app, $params) {
        
        // params['mib'] for mbb streaming.
        if ($params['mib'] == 1){
            $partnerid = $app->getConfig()->{'gtp.mib.partner.id'};
        }else if ($params['pos'] == 1){
            $partnerid = $app->getConfig()->{'gtp.pos1.partner.id'};
        }else if ($params['mks'] == 1){
            $price_provider_id = $app->getConfig()->{'gtp.mks.priceprovider.id'};
        }else if ($params['dealer1'] == 1){
            // $partnerid = $app->getConfig()->{'gtp.dealer1.partner.id'};
            $price_provider_id = $app->getConfig()->{'gtp.dealer1.priceprovider.id'};
        }else{
            $user = $app->getUserSession()->getUser();
            $partnerid = $user->partnerid;
        }

        if ($params['code']){
            $priceprovider = $app->priceproviderStore()->searchTable()->select()->where('code', $params['code'])->one();
            $price_provider_id = $priceprovider->id;
        }
        
        // EXCEPTIONAL CASE FOR PRICESTREAM WITHOUT PARTNER
        if ($price_provider_id){
            $path = $app->getConfig()->{'app.pricestream.subscribe.path'};
            if ($path) {
                // Do an internal redirect to the stream path
                $fullPath = $path . $price_provider_id;
                header("X-Accel-Redirect: $fullPath");
                header('X-Accel-Buffering: no');
            }
            return json_encode(['success' => true]);
        }

        if (0 >= $partnerid) {
            throw new \Exception("User has no partnerid associated.");
        }

        if (0 == strlen($params['pdt'])) {
            throw new \Exception("No product code was given.");
        }

        $partner = $app->partnerStore()->getById($partnerid);
        $product = $app->productStore()->getByField('code', $params['pdt']);

        if (!$product) {
            throw new \Exception("Unable to find product for {$params['pdt']}");
        }

        // Get the priceprovider id to get the price stream
        $priceProvider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);
        $path = $app->getConfig()->{'app.pricestream.subscribe.path'};
        if ($path) {
            // Do an internal redirect to the stream path
            $fullPath = $path . $priceProvider->id;
            header("X-Accel-Redirect: $fullPath");
            header('X-Accel-Buffering: no');
        }

        // The response not appear if using websocket/SSE connections
        return json_encode(['success' => true]);
    }

    private function subscribeToProductStreamSales($app, $params) {
        $user = $app->getUserSession()->getUser();
        //$partnerid = 1;
        $priceProviderSource = $app->getConfig()->{'gtp.mib.partner.id'};
        
        /*if (0 >= $partnerid) {
            throw new \Exception("User has no partnerid associated.");
        }*/

        if (0 == strlen($params['pdt'])) {
            throw new \Exception("No product code was given.");
        }

        //$partner = $app->partnerStore()->getById($partnerid);
        $product = $app->productStore()->getByField('code', $params['pdt']);

        if (!$product) {
            throw new \Exception("Unable to find product for {$params['pdt']}");
        }

        if ($params['code']){
            $priceprovider = $app->priceproviderStore()->searchTable()->select()->where('code', $params['code'])->one();
            $price_provider_id = $priceprovider->id;
        }
        if ($price_provider_id){
            $path = $app->getConfig()->{'app.pricestream.subscribe.path'};
            if ($path) {
                // Do an internal redirect to the stream path
                $fullPath = $path . $price_provider_id;
                header("X-Accel-Redirect: $fullPath");
                header('X-Accel-Buffering: no');
            }
            return json_encode(['success' => true]);
        }

        // Get the priceprovider id to get the price stream
        //$priceProvider = $app->priceproviderStore()->getForPartnerByProduct($partner, $product);
        $path = $app->getConfig()->{'app.pricestream.subscribe.path'};
        if ($path) {
            // Do an internal redirect to the stream path
            $fullPath = $path . $priceProviderSource;
            header("X-Accel-Redirect: $fullPath");
            header('X-Accel-Buffering: no');
        }

        // The response not appear if using websocket/SSE connections
        return json_encode(['success' => true]);
    }
    
    private function subscribeToNotification($app, $params) {
        return $app->notificationManager()->subscribeToNotification($params);
    }
    
    private function postToNotificationChannel($app, $params) {
        return $app->notificationManager()->postToNotificationChannel($params);
    }

}