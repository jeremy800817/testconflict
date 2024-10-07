<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\manager;

use \Snap\InputException;
use \Snap\TLogging;
use \Snap\IObserver;
use \Snap\IObservable;
use \Snap\IObservation;
use \Snap\object\Order;
use \Snap\object\Partner;
use \Snap\object\Product;
use \Snap\object\Logistic;
use \Snap\object\Buyback;
use \Snap\object\PriceValidation;

class ItemListingManager implements IObservable, IObserver
{
    use \Snap\TLogging;
    use \Snap\TObservable;

    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
    }

    // flow 
    /** 
     * buyback new table, as collection data, then buyback order will be on ORDER_TABLE_MANAGER
     * buyback will use logistic as TYPE_collect, from branchid = collection_place, tobranchid = ace_hq
     * buyback table will store all serial bar, branchid
     */

    // process ----------------------
    /**
     * @param obj   $partner            order data to proceed buyback
     * @param str   $apiVersion         api version of request
     * @param str   $refid              partner refid for buyback
     * @param str   $branchid           buyback branchid
     * @param obj   $order              buyback Order
     * @param str   $serialno           item(s) for buyback
     * @param obj   $product            product of buyback, minted bar
     * @param obj   $priceStream        current gold price to calculate buyback gold price during process
     * RAW itemArray [item_Array] item_obj[
     *  'serialno' -> optional
     *  denomination (gram)
     *  productid
     * ] 
     */
    // lockedinprice = buyback_goldprice from mbbapi
    public function doItemListing($partner, $apiVersion, $refid, $item, $warehouse)
    {
        // getPricestream here

        // api will call sap->sportOrder(TYPE_COMPANYBUYBACK) if return success, only call doBuyback

        // $apiVersion
        try {
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();

            // $checkTotalWeight = 0;
            // $checkTotalPrice = 0;
            // $checkTotalQuantity = 0;
            // foreach ($itemArray as $item){
            //     $item['denomination']
            // }

            //Support for PriceStream and PriceValidation prices.  

            $partnerlist = array(
                "apiversion" =>  $apiVersion,
                "refid" => $refid,
                "item" => $item,
                "warehouse" => $warehouse
            );
        } catch (\Exception $e) {
            //throw \Snap\api\exception\BuybackError::fromTransaction([], [ 'message' => $e->getMessage()]);   
            throw $e;
        }
        return $partnerlist;
    }

    public function returnItemlist($partnerlist)
    {
        try {
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();
            // format sap structure 
            // saveBuyback->items LOOP
            // $sap_data = [
            //     serialno = item->serialnumber,
            //     quantity = item->weight,
            //     price = priceStream->getPrice($partner->id) * item->weight,
            //     refno = item->buybackno + '-' + @index
            // ];
            // $price = $partner->calculator()->multiply($weight, $gtpReferencePrice)
            $sap_response = $this->app->apiManager()->sapItemList($partnerlist);

            if (!$this->sapReturnVerify($sap_response)) {
                $this->log("Itemlist from sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s'), SNAP_LOG_ERROR);
                throw \Snap\api\exception\BuybackError::fromTransaction([], ['message' => 'Unable to proceed buyback.']);
            }
            $businesslist = json_encode($this->formatSapItemToGtpItem($sap_response));  // entire response from SAP, itemized from SAP

            // formatted data from SAP 


        } catch (\Exception $e) {
            if ($businesslist) {
                // no rollback, incase SAP failed it still hv records
                //$saveBuyback->status = Buyback::STATUS_FAILED;
                // $this->app->buybackStore()->save($saveBuyback);
                //$this->app->getDbHandle()->commit();
            }
            //throw \Snap\api\exception\BuybackError::fromTransaction([], [ 'message' => $e->getMessage()]);   
            throw $e;
        }
        return $businesslist;
    }

    // logistic event
    private function updateBuybackLogisticStatus($buybacks, $logistic)
    {
        try {
            $this->app->getDbHandle()->beginTransaction();
            foreach ($buybacks as $buyback) {
                // if delivered . call sap 
                if ($logistic->status == Logistic::STATUS_COLLECTED) {
                    $buyback->status = Buyback::STATUS_COMPLETED;
                    $buyback->collectedon = $logistic->collectedon;
                    $buyback->collectedby = $logistic->collectedby;
                    $this->app->buybackStore()->save($buyback);
                }
                if (Logistic::STATUS_COLLECTING == $logistic->status || Logistic::STATUS_PROCESSING == $logistic->status) {
                    $buyback->status = Buyback::STATUS_PROCESSCOLLECT;
                    $this->app->buybackStore()->save($buyback);
                }
            }

            $this->app->getDbHandle()->commit();
        } catch (\Exception $e) {
            if ($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }
    // exception on logistic - delivery failed, or any issues
    private function failedLogistic($buybacks, $logistic)
    {
        try {
            $this->app->getDbHandle()->beginTransaction();
            if ($logistic->status == Logistic::STATUS_FAILED) {
                // buyback collection failed
                // __PENDING
            }
            $this->app->getDbHandle()->commit();
        } catch (\Exception $e) {
            if ($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }


    private function sapReturnVerify($sap_response)
    {
        // very array data;
        $this->log("Buyback - sapReturnVerify - verify sap return on item status :" . gmdate('Y-m-d H:i:s'), SNAP_LOG_DEBUG);
        if ($sap_response && 'N' == $sap_response[0]['success']) {
            return false;
        }
        if (!$sap_response) {
            return false;
        }
        foreach ($sap_response as $sap_data) {
            if ('N' == $sap_data['success']) {
                return false;
            }
        }
        return true;
    }

    // NOTE -- 16-06-2020 -- SAP buyback number is by item, GTP is by (request), SAP item buyback_ref will be `generateBuybackNo+{'-'}+{item}`
    public function generateBuybackNo($cacher, $partner = null, $refid = null)
    {
        $this->log("generateBuybackNo($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
        $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $buybackNoKey = 'buybackNo:' . $now->format('Ymd');
        $nextBuybackSequence = $cacher->increment($buybackNoKey, 1, 86400);
        $this->log("generateBuybackNo($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $buybackNoKey, SNAP_LOG_DEBUG);
        if (!$nextBuybackSequence) {
            $this->log("generateBuybackNo($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
            $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
            $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
            //Can't find the key.  We will have to rebuild it.
            $totalDayBuybacks = $this->app->buybackStore()->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
            $this->log("generateBuybackNo($refid,{$partner->code}) - total Buybacks from DB = " . $totalDayBuybacks, SNAP_LOG_DEBUG);
            $cacher->set($buybackNoKey, $totalDayBuybacks + 1, 86400);
            $nextBuybackSequence = $totalDayBuybacks + 1;
        }
        $nextBuybackSequence = strtoupper(sprintf("%s%s%04d", "BB", $now->format('ymd'), $nextBuybackSequence));
        $this->log("generateBuybackNo() - Generated sequence $nextBuybackSequence for buyback $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextBuybackSequence;
    }

    private function formatSapItemToGtpItem($sap_response)
    {
        // itemCode
        // serialNum
        // quantity
        $this->log("BusinessPartnerList - formatSapItemToGtpItem - format sap_response to GTP data :" . gmdate('Y-m-d H:i:s'), SNAP_LOG_DEBUG);
        $items = [];
        foreach ($sap_response as $sap_item) {
            $item['sapcode'] = $sap_item['cardCode'];
            $item['name'] = $sap_item['cardName'];
            $item['cardType'] = $sap_item['cardType'];
            $item['frozenFor'] = $sap_item['frozenFor'];
            $item['aliasName'] = $sap_item['aliasName'];
            array_push($items, $item);
        }
        return $items;
    }
    private function getFieldFromSapItemCode($sapItemCode, $column)
    {
        $product = $this->app->productStore()->searchTable()->select()->where('sapitemcode', $sapItemCode)->one();
        return $product->$column;
    }
}
