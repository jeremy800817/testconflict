<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\Order;
Use \Snap\object\Partner;
Use \Snap\object\Product;
Use \Snap\object\Logistic;
Use \Snap\object\Buyback;
Use \Snap\object\PriceValidation;

class BuybackManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if($changed instanceof \Snap\manager\LogisticManager) {
            if (Logistic::TYPE_BUYBACK == $state->target->type){
                // no checking here, firmed data from logisticManager;
                // courier tracking/update will be on logisticManager;
                $logistic = $state->target;
                $buybackIds = [];
                $buybacklogistics = $this->app->buybackLogisticStore()->searchTable()->select()->where("logisticid", $logistic->id)->execute();
                foreach ($buybacklogistics as $buybacklogistic){
                    array_push($buybackIds, $buybacklogistic->buybackid);
                }
                $buybacks = $this->app->buybackStore()->searchTable()->select()->whereIn("id", $buybackIds)->execute();

                // _UPDATE/_CHANGE status value
                if (Logistic::STATUS_FAILED != $state->target->status){
                    $this->updateBuybackLogisticStatus($buybacks, $logistic);
                }else{
                    $this->failedLogistic($buybacks, $logistic);
                }
            }

        }
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
    public function doBuyback($partner, $apiVersion, $refid, $branchId, $itemArray, $totalWeight, $totalAmount, $totalQuantity, 
        $fee, $remarks, $lockedinPrice, $priceRequestId, $product = null){
        // getPricestream here

        // api will call sap->sportOrder(TYPE_COMPANYBUYBACK) if return success, only call doBuyback

        // $apiVersion
        try{
            $cacher = $this->app->getCacher();
            $this->app->getDbHandle()->beginTransaction();

            //Ensure no duplicated refid for partner
            $count = $this->app->buybackStore()
                        ->searchTable()
                        ->select(['id'])
                        ->where('partnerid', $partner->id)
                        ->andWhere('partnerrefno', $refid)
                        ->count();
            if($count) {
                throw \Snap\api\exception\RefDuplicatedException::fromTransaction($partner, ['partnerrefno' => $refid, 'field' => 'refid', 'action' => 'buyback']);
            }

            //Support for PriceStream and PriceValidation prices.  
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'), $this->app->getUserTimeZone());
            $store = (preg_match('/^PV.*/', $priceRequestId) ? $this->app->priceValidationStore() : $this->app->priceStreamStore());
            $price = $store->searchTable()->select()->where('uuid', $priceRequestId)->one();
            if(! $price) {
                    $this->log("Error in order $refid for partner {$partner->code} because system unable to find the corresponding price id $priceRequestId in store " . $store->getTableName(), SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId]);                
            }
            if($price instanceof PriceValidation) { //Price validation checking
                $gtpReferencePrice = $price->price;
                if($price->partnerid != $partner->id) { //must be generated by same partner
                    $this->log("Error in order $refid for partner {$partner->code}({$partner->id}) due to ($future_order) has been matched priceValidation partner id {$price->partnerid} mismatched", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                } else if($price->validtill <= $now) { //not expired yet.
                    $this->log("Error in order $refid for partner {$partner->code} due to priceValidation validtill = ".$price->validtill->format('Y-m-d H:i:s').", current time = ".$now->format('Y-m-d H:i:s'), SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataExpired::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                } else if(0 < $price->orderid) { //not used yet
                    $this->log("Error in order $refid for partner {$partner->code} due to priceValidation has been utilised by another order {$price->orderid}", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                }
                if(PriceValidation::REQUEST_COMPANYBUY != $price->requestedtype){ //mismatch of order with price validation
                    $this->log("Error in order $refid for partner {$partner->code} due price validation requestType does not match order type", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                }
            } else {  //price stream.  Have to check.
                $provider = $this->app->priceProviderStore()->getById($price->providerid);
                if($provider->pricesourceid != $partner->id) { //same price source
                    $this->log("Error in order $refid for partner {$partner->code} due to priceStream price source is different from partner price source", SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);                    
                }
                $priceEffectiveUntil = $price->createdon->getTimeStamp() + $partner->orderconfirmallowance;
                if(time() > $priceEffectiveUntil) { //already expired
                    $this->log("Error in order $refid for partner {$partner->code} due to priceStream expired = ".gmdate('Y-m-d H:i:s', $priceEffectiveUntil).", current time = ".gmdate('Y-m-d H:i:s', time()), SNAP_LOG_ERROR);
                    throw \Snap\api\exception\OrderPriceDataExpired::fromTransaction($partner, [ 'priceid' => $priceRequestId, 'expiryDate' => $price->validtill->format('Y-m-d H:i:s')]);
                }
            }

            //If it is PriceStream object, then have to get right data for price
            if(0 == $gtpReferencePrice) {
                $gtpReferencePrice = $price->companybuyppg;
            }
            //Ensure system price is the same as price provided by merchant
            if($lockedinPrice != $gtpReferencePrice) {
                $this->log("Error in order $refid for partner {$partner->code} due to gtp reference price $gtpReferencePrice is not the same as API provided lockedin price $lockedinPrice", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderPriceDataInvalid::fromTransaction($partner, [ 'priceid' => $priceRequestId]);                    
            }
            if ($totalAmount != $partner->calculator()->multiply($totalWeight, $gtpReferencePrice)){
                $this->log("Error in order $refid for partner {$partner->code} due to gtp total amount $totalAmount is not the same as API provided gtpreference multiply gram price ".$partner->calculator()->multiply($totalWeight, $gtpReferencePrice), SNAP_LOG_ERROR);
                throw \Snap\api\exception\BuybackError::fromTransaction([], [ 'message' => "Total price $totalAmount is incorrect."]);        
            }

            $productprice = $this->app->productStore()
                                    ->searchTable()
                                    ->select()
                                    ->where('code', 'DG-999-9')
                                    ->one();
            $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $productprice);
            $bookingPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider);
            $bookingPrice = $bookingPriceObj->companybuyppg;
            $bookingPrice = $partner->calculator()->round($bookingPrice);
            $_now = new \DateTime();
            $_now = \Snap\common::convertUTCToUserDatetime($_now);
            
            if (preg_match('/[0-9\.]+m$/', $apiVersion)){
                // mib have different calculation - ignore product refinery fees, treat as buyback service fee
                // meanwhile pos will treat as order fees (refinery fee)
                $fees = 0;
                $fee = $fee;
            }else{
                $fees = $partner->getRefineryFee($product); 
                $fee = $fees;
            }

            $items = json_encode($itemArray);
            $createBuyback = $this->app->buybackStore()->create([
                'apiversion' => $apiVersion,
                'partnerid' => $partner->id,
                'partnerrefno' => $refid,
                'branchid' => $branchId,
                'buybackno' => $this->generateBuybackNo($cacher),
                'pricestreamid' => ($price instanceof PriceValidation) ? $price->pricestreamid : $price->id,
                'price' => $gtpReferencePrice,
                'totalweight' => $totalWeight, // xau in order_TABLE
                'totalamount' => $partner->calculator()->multiply($totalWeight, $gtpReferencePrice + ($fees)),
                'totalquantity' => $totalQuantity,
                'fee' => $fee,
                'items' => $items,
                'remarks' => $remarks,
                'productid' => $product->id,
                'bookingon' => $_now,
                'bookingprice' => $bookingPrice,
                'bookingpricestreamid' => $bookingPriceObj->id,
                //'confirmby' => $remarks,
                'status' => Buyback::STATUS_PENDING,

                // new sap variant of POSTSO
                // 'xau' =>  // totalweight
                // 'amount' =>  // totalamount
                // new sap variant of POSTSO
            ]);

            $saveBuyback = $this->app->buybackStore()->save($createBuyback);
            
            $this->app->getDbHandle()->commit();
            $observation = new \Snap\IObservation(
                $saveBuyback, 
                \Snap\IObservation::ACTION_NEW, 
                $saveBuyback->status, 
                []);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            //throw \Snap\api\exception\BuybackError::fromTransaction([], [ 'message' => $e->getMessage()]);   
            throw $e;
        }
        return $saveBuyback;
    }

    public function confirmBuyback($buyback){
        try{
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
            if (!$buyback->fromMbb($this->app)){
                $sap_response = $this->app->apiManager()->sapPosBuyback($buyback);
                if (!$this->sapReturnVerify($sap_response)){  
                    $this->log("Buyback - data{$buyback}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                    throw \Snap\api\exception\BuybackError::fromTransaction([], ['message' => 'Unable to proceed buyback.']);
                }
            }else{
                $sap_response = $this->app->apiManager()->sapBuyback($buyback);
                if (!$this->sapReturnVerify($sap_response)){  
                    $this->log("Buyback - data{$buyback}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                    throw \Snap\api\exception\BuybackError::fromTransaction([], ['message' => 'Unable to proceed buyback.']);
                }
                $buybackItems = json_encode($this->formatSapItemToGtpItem($sap_response));  // entire response from SAP, itemized from SAP
                // formatted data from SAP 
                $buyback->items = $buybackItems;
            }
            
            $buyback->status = Buyback::STATUS_CONFIRMED;

            // __PENDING --> 
            $product = $this->app->productStore()
                                    ->searchTable()
                                    ->select()
                                    ->where('code', 'DG-999-9')
                                    ->one();
            //$provider = $this->app->priceProviderStore()->getForPartnerByProduct($saveBuyback->getPartner(), $saveBuyback->getProduct());
            // $provider = $this->app->priceProviderStore()->getForPartnerByProduct($buyback->getPartner(), $product);
            // $confirmPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider);
            $buyback->confirmon = new \DateTime('now', $this->app->getUserTimeZone());
            // $buyback->confirmprice = $confirmPriceObj->companybuyppg;
            // $buyback->confirmpricestreamid = $confirmPriceObj->id;

            $saveBuyback = $this->app->buybackStore()->save($buyback);

            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($saveBuyback) {
                // no rollback, incase SAP failed it still hv records
                $saveBuyback->status = Buyback::STATUS_FAILED;
                $this->app->buybackStore()->save($saveBuyback);
                $this->app->getDbHandle()->commit();
            }
            //throw \Snap\api\exception\BuybackError::fromTransaction([], [ 'message' => $e->getMessage()]);   
            throw $e;
        }
        return $saveBuyback;
    }

    // logistic event
    public function updateBuybackLogisticStatus($buybacks, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();
            foreach ($buybacks as $buyback){
                // if delivered . call sap 
                if ($logistic->status == Logistic::STATUS_COLLECTED){
                    $buyback->status = Buyback::STATUS_COMPLETED;
                    $buyback->collectedon = $logistic->collectedon;
                    $buyback->collectedby = $logistic->collectedby;
                    $this->app->buybackStore()->save($buyback);
                }
                if (Logistic::STATUS_PACKING == $logistic->status || Logistic::STATUS_PROCESSING == $logistic->status){
                    $buyback->status = Buyback::STATUS_PROCESSCOLLECT;
                    $this->app->buybackStore()->save($buyback);
                }
                if ($logistic->status == Logistic::STATUS_COMPLETED){
                    $buyback->status = Buyback::STATUS_COMPLETED;
                    $this->app->buybackStore()->save($buyback);
                }
            }
            
            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }
    // exception on logistic - delivery failed, or any issues
    private function failedLogistic($buybacks, $logistic){
        try{
            $this->app->getDbHandle()->beginTransaction();
            if ($logistic->status == Logistic::STATUS_FAILED){
                // buyback collection failed
                // __PENDING
            }
            $this->app->getDbHandle()->commit();
        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
    }

    public function reverseBuyback($partner, $refid){
        try{

            $this->app->getDbHandle()->beginTransaction();

            $oldStatus = $buyback->status;

            $buyback = $this->app->buybackStore()->searchTable()->select()
                ->where('partnerrefno', $refid)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', "NOT IN", [Buyback::STATUS_FAILED, Buyback::STATUS_COMPLETED, Buyback::STATUS_PROCESSCOLLECT])
                ->one();
            if (!$buyback){
                throw \Snap\api\exception\BuybackReversalError::fromTransaction([], ['message' => 'Invalid Buyback Record.']);
            }

            // check status
            if (!in_array($buyback->status, [Buyback::STATUS_COMPLETED, Buyback::STATUS_CONFIRMED, Buyback::STATUS_PENDING])){
                throw \Snap\api\exception\BuybackReversalError::fromTransaction([], ['message' => 'Unable to proceed buyback reversal. Status not application to perform this action.']);
            }

            $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
            $recordTime = new \DateTime($buyback->createdon->format('Y-m-d H:i:s'));
            $recordTime->modify("+ 1 hours");
            if ($now >= $recordTime){
                throw \Snap\api\exception\BuybackReversalError::fromTransaction([], ['message' => 'Unable to proceed buyback reversal. Transaction time exceed to perform this action.']);
            }

            $sap_response = $this->app->apiManager()->sapReverseBuyback($buyback);
            if (!$this->sapReturnVerify($sap_response)){  
                $this->log("Reverse Buyback - data{$buyback}, sap{$sap_response} - error on sap_response :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_ERROR);
                throw \Snap\api\exception\BuybackReversalError::fromTransaction([], ['message' => 'Unable to proceed buyback reversal.']);
            }

            $buyback->status = Buyback::STATUS_REVERSED;

            $updateBuyback = $this->app->buybackStore()->save($buyback);
            $this->app->getDbHandle()->commit();

            $observation = new \Snap\IObservation(
                $updateBuyback, 
                \Snap\IObservation::ACTION_REVERSE, 
                $oldStatus,
                []);
            $this->notify($observation);

        }catch(\Exception $e){
            if($this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;
        }
        return $buyback;
    }

    private function sapReturnVerify($sap_response){
        // very array data;
        $this->log("Buyback - sapReturnVerify - verify sap return on item status :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
        if (!$sap_response){
            return false;
        }
        if (isset($sap_response->actionSuccess)){
            // sap old 1.0 (gtp_core) return format
            if ($sap_response->actionSuccess == 0 || $sap_response->actionSuccess == false){
                return false;
            }
        }else{
            // sap new (MBB) return format
            if ($sap_response && 'N' == $sap_response[0]['success']){
                return false;
            }
            foreach ($sap_response as $sap_data){
                if ('N' == $sap_data['success']){
                    return false;
                }
            }
        }
        return true;
    }

    // NOTE -- 16-06-2020 -- SAP buyback number is by item, GTP is by (request), SAP item buyback_ref will be `generateBuybackNo+{'-'}+{item}`
    public function generateBuybackNo($cacher, $partner = null, $refid = null){
        $moduleString = 'Buyback';
        $generateStore = $this->app->buybackStore();
        $generateNoPrefix = 'F';
        $format = '%s%s%05d';
        $envPrefix = '';       

        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $format = '%s%s%04d%s';
            $envPrefix = 'D';
        }
        $this->log("generate_".$moduleString."No($refid, {$partner->code}) - into the method ", SNAP_LOG_DEBUG);
        $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));
        $now = \Snap\common::convertUTCToUserDatetime($now);
        $generateNoKey = $moduleString.'No:' . $now->format('Ymd');
        $nextGenerateSequence = $cacher->increment($generateNoKey, 1, 86400);
        $this->log("generate_".$moduleString."No($refid,{$partner->code}) - The date used is " . $now->format('Y-m-d H:i:s') . " and key = " . $generateNoKey, SNAP_LOG_DEBUG);
        if(! $nextGenerateSequence) {
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - the redis key not found.  Generating total orders from DB", SNAP_LOG_DEBUG);
            $utcStartOfDay = new \DateTime($now->format('Y-m-d 00:00:00'));
            $utcStartOfDay = \Snap\common::convertUserDatetimeToUTC($utcStartOfDay);
            //Can't find the key.  We will have to rebuild it.
            $totalDayOrders = $generateStore->searchTable()->select()->where('createdon', '>=', $utcStartOfDay->format('Y-m-d H:i:s'))->count();
            $this->log("generate_".$moduleString."No($refid,{$partner->code}) - total ".$moduleString." from DB = " . $totalDayOrders, SNAP_LOG_DEBUG);
            $cacher->set($generateNoKey, $totalDayOrders + 1, 86400);
            $nextGenerateSequence = $totalDayOrders + 1;
        }
        $nextGenerateSequence = strtoupper(sprintf($format, $generateNoPrefix, $now->format('ymd'), $nextGenerateSequence, $envPrefix));
        $this->log("generate_".$moduleString."No() - Generated sequence $nextGenerateSequence for ".$moduleString." $refid for partner {$partner->code}", SNAP_LOG_DEBUG);
        return $nextGenerateSequence;
    }

    private function formatSapItemToGtpItem($sap_response){
        // itemCode
        // serialNum
        // quantity
        $this->log("Buyback - formatSapItemToGtpItem - format sap_response to GTP data :" . gmdate('Y-m-d H:i:s') , SNAP_LOG_DEBUG);
        $items = [];
        foreach ($sap_response as $sap_item){
            $item['sapreturnid'] = $sap_item['id'];
            $item['code'] = $this->getFieldFromSapItemCode($sap_item['itemCode'], 'code');
            $item['serialnumber'] = $sap_item['serialNum'];
            $item['weight'] = $this->getFieldFromSapItemCode($sap_item['itemCode'], 'weight');
            $item['sapreverseno'] = $sap_item['data1'];
            array_push($items, $item);
        }
        return $items;
    }
    private function getFieldFromSapItemCode($sapItemCode, $column){
        $product = $this->app->productStore()->searchTable()->select()->where('sapitemcode', $sapItemCode)->one();
        return $product->$column;
    }

    public function readImportTender($date, $goldprice, $file, $refid = null, $preview = false, $partnercode = null){
        $cacher = $this->app->getCacher();

       
        $partner_west = $this->app->getConfig()->{'gtp.pos1.partner.id'}; //POS AR-RAHNU SDN BHD (WM/T)
        $partner_east = $this->app->getConfig()->{'gtp.pos2.partner.id'}; //POS AR-RAHNU SDN BHD (EM/T)

        // allocate partner
        if (isset($partnercode) && 'TEKUN' === $partnercode) {
            $partner_west = $this->app->getConfig()->{'gtp.tekun1.partner.id'}; //TEKUN SDN BHD EAST
            $partner_east = $this->app->getConfig()->{'gtp.tekun2.partner.id'}; //TEKUN SDN BHD WEST
    
        } else if (isset($partnercode) && 'POS' === $partnercode) {
            $partner_west = $this->app->getConfig()->{'gtp.pos1.partner.id'}; //POS AR-RAHNU SDN BHD (WM/T)
            $partner_east = $this->app->getConfig()->{'gtp.pos2.partner.id'}; //POS AR-RAHNU SDN BHD (EM/T)
    
        } else if (isset($partnercode) && 'KOPONAS' === $partnercode) {
            $partner_west = $this->app->getConfig()->{'gtp.koponas1.partner.id'}; //KOPONAS(W)@PROD
            $partner_east = $this->app->getConfig()->{'gtp.koponas2.partner.id'}; //KOPONAS(E)@PROD
    
        } else if (isset($partnercode) && 'SAHABAT' === $partnercode) {
            $partner_west = $this->app->getConfig()->{'gtp.sahabat1.partner.id'}; //SAHABAT(W)@PROD
            $partner_east = $this->app->getConfig()->{'gtp.sahabat2.partner.id'}; //SAHABAT(E)@PROD
    
        }
        // process readable array - START
        
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader();
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        //    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load($file['tmp_name']);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        // print_r($sheetData);exit;
        

        $partners = $this->app->PartnerStore()->searchTable()->select()->where('id', 'IN', [$partner_west, $partner_east])->execute();
        
        $items_array = [];
        
        
        // fix memory leak on branches from cache
        $branches = $this->getPartnersBranches($partners);
        foreach ($sheetData as $x => $row){
            if ($row["A"] >= 1){
                // foreach ($row as $y => $cell){
                    //     // print_r($cell);exit;
                // }
                $item_array['index'] = $row["A"];
                $item_array['branch_code'] = $row["B"];
                
                $partnerFromBranch = $this->getPartnerFromBranch_POS($branches, $row['B']);
                if (!$partnerFromBranch){
                    throw new \Exception('Invalid Branch Code, will unable to determine Partner ID.');
                }
                $item_array['branchid'] = $partnerFromBranch['branchid'];
                $item_array['partnerid'] = $partnerFromBranch['partnerid'];
                
                $item_array['branch_name'] = $row["C"];
                $item_array['item_positemno'] = $row["D"];
                
                $item_array['referenceNo'] = $row["D"];
                
                // $item_array['item_details'] = [
                //     "item_purity" => "",
                //     "item_weight" => ""
                // ];
                
                // reset
                $item_array['item_details'] = []; 
                // $item_details_count = 0;
                foreach($row as $y => $cell){
                    $item_details_count++;
                    $check = $this->map_purity($y, $cell);
                    if ($check){
                        $item_array['item_details'][] = $check;
                    }
                    // $item_total_mixed_purity += $check['purity'];
                }
                // $item_mixed_purity = $item_total_mixed_purity / 
                
                $item_array['item_total_weight'] = $row["M"];
                $item_array['item_total_price'] = $row["N"];

                
                $items_array[$item_array['partnerid']][] = $item_array;
            }
        }
        // print_r($items_array);exit;
        // process readable array - END
        

        // seperate items from list based on partner->branch - START
        $buybacks = [];
        foreach ($items_array as $partnerid => $items){
            // reset
            $total_quantity = 0;
            $total_weight = 0;
            $total_price = 0;
            
            $partner = $this->app->PartnerStore()->getById($partnerid);
            $product = $this->app->ProductStore()->getByField('code', 'jewel'); // _PENDING => jewel or DG-999-9
            $refineryFee = $partner->getRefineryFee($product);
            $goldprice = $goldprice;
            $goldprice_after_fees = $goldprice + ($refineryFee); // if discount $refineryFee = -$refineryFee;

            // $_RETURN_sap_rate_cards = getSAPCARD($partner->BBCODE);
            $version = '1.0';
            $params['version'] = $version;
            $params['code'] = $partner->sapcompanybuycode1; // customer id *required
            $params['item'] = '';
            $_RETURN_sap_rate_cards = $this->app->apiManager()->sapGetRateCard($version, $params);
            $sap_rate_cards = $this->formatSapRateCardsReturn($_RETURN_sap_rate_cards);

            foreach ($items as $index => $item){
                $total_quantity ++; // compare excel file
                
                // (
                //     [purity] => 916
                //     [weight] =>   16.06 
                // )
                // print_r($item);exit;
                $calculator = $partner->calculator();
                $item_price = 0;
                foreach ($item['item_details'] as $detail){
                    $rate = $this->getSapRate($sap_rate_cards, $detail['purity']);
                    $purity_rate = $calculator->divide($rate, 100);
                    $purity_goldprice = $calculator->multiply($purity_rate, $goldprice_after_fees);
                    $item_price += $calculator->multiply(floatval($detail['weight']), $purity_goldprice);
                }
                // $item['item_total_price'] = $partner->calculator()->multiply(floatval($item['item_total_weight']), $goldprice);
                $items_array[$partnerid][$index]['item_total_price'] = $item_price; // reset price with rate from sap


                $total_weight += $item['item_total_weight']; // compare excel file
                $total_price += $item_price; // compare excel file
                // echo $item_price.'<br>';

                // debug
                // $compare_value = str_replace(",", "", $item['item_total_price']);
                // if ($item_price != $compare_value) {
                //     if ((floatval($compare_value) - floatval($item_price) > 1) || floatval($compare_value) - floatval($item_price) < 1){

                //         $items_array[$partnerid][$index]['WRONGPRICE'] = $item_price.'-'.$compare_value;
                //     }
                // }
            }

            $items_array[$partnerid]['partner_total_weight'] = $total_weight;
            $items_array[$partnerid]['partner_total_price'] = $total_price;
            $items_array[$partnerid]['partner_total_quantity'] = $total_quantity;

            $total_import_weight += $items_array[$partnerid]['partner_total_weight'];
            $total_import_price += $items_array[$partnerid]['partner_total_price'];
            $total_import_quantity += $items_array[$partnerid]['partner_total_quantity'];

            $items_array[$partnerid]['productid'] = $product->id;

            $items_array[$partnerid]['goldprice'] = $goldprice;
            $items_array[$partnerid]['refinery_fee'] = $refineryFee;
            $items_array[$partnerid]['goldprice_after_fees'] = $goldprice_after_fees;
            $items_array[$partnerid]['partner_total_price'] = $total_price; // transaction price
        }
        // print_r($items_array);exit;

        // compare this 3 value before insert - START
        // use last row of excel
        $total_import_weight;
        $total_import_price;
        $total_import_quantity;
        // [M] =>   1,512.93 
        // [N] =>   336,869.53 

        $excel_summary_row = end($sheetData);
        $excel_total_price = end($excel_summary_row);
        $excel_total_weight = prev($excel_summary_row);

        // echo $total_import_weight; echo "-"; echo $excel_total_weight; echo "<br>";
        // echo $total_import_price; echo "-"; echo $excel_total_price; echo "<br>";
        // exit;
        // compare this 3 value before insert - END

        // -- output
        // items_array = [
        //     "_partnerid" => [
        //          array(items),
        //          partner_total_weight,
        //          partner_total_price,
        //          partner_total_quantity,
        //      ]
        //     "_partnerid" => [
        //          array(items),
        //          partner_total_weight,
        //          partner_total_price,
        //          partner_total_quantity,
        //      ]
        // ]
        // seperate items from list based on partner->branch - END
        $return = [];
        $excel_total_price = str_replace(",", "", $excel_total_price);
        if (number_format($excel_total_price, 2, '.', '') != number_format($total_import_price, 2, '.', '')){
            $return['exception'] = 'Compute:'.number_format($total_import_price, 2, '.', '').' | Excel:'.number_format($excel_total_price, 2, '.', '');
        }


        if ($preview){
            $return['success'] = true;
            $return['preview'] = 'Compute:'.number_format($total_import_price, 2, '.', '').' | Excel:'.number_format($excel_total_price, 2, '.', '');
            return $return;
        }
        // insert START;
        foreach ($items_array as $partnerid => $partneritems){
            $partner = $this->app->partnerStore()->getById($partnerid);
            $productprice = $this->app->productStore()
                ->searchTable()
                ->select()
                ->where('code', 'DG-999-9')
                ->one();
            $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $productprice);
            $bookingPriceObj = $this->app->priceManager()->getLatestSpotPrice($provider);
            $bookingPrice = $bookingPriceObj->companybuyppg;
            $bookingPrice = $partner->calculator()->round($bookingPrice);
            $_now = new \DateTime();
            $_now = \Snap\common::convertUTCToUserDatetime($_now);

            $product = $this->app->ProductStore()->getByField('code', 'jewel'); // _PENDING => jewel or DG-999-9
            
            $date = new \DateTime($date);
            $date = date_format($date, 'Ymd');
            $refid = 'PT'.$date;
            // $items = json_encode($itemArray);
            $createBuyback = $this->app->buybackStore()->create([
                'apiversion' => '1.0p',
                'partnerid' => $partner->id,
                'partnerrefno' => $refid,
                'branchid' => 0,
                'buybackno' => $this->generateBuybackNo($cacher),
                'pricestreamid' => 0,
                'price' => $partneritems['goldprice'],
                'totalweight' => $partneritems['partner_total_weight'], // xau in order_TABLE
                'totalamount' => $partneritems['partner_total_price'],
                'totalquantity' => $partneritems['partner_total_quantity'],
                'fee' => $partneritems['refinery_fee'],
                'items' => '{tender}',
                'remarks' => "Tender",
                'productid' => $partneritems['productid'],
                'bookingon' => $_now,
                'bookingprice' => $bookingPrice,
                'bookingpricestreamid' => $bookingPriceObj->id,
                //'confirmby' => $remarks,
                'status' => Buyback::STATUS_PENDING,
            ]);
    
            $saveBuyback = $this->app->buybackStore()->save($createBuyback);

            $confirm_buyback = $this->confirmBuyback($saveBuyback);

            if ($confirm_buyback){
            }
            $createDraftGrn = $this->app->goodsreceivednoteManager()->createDraftGRN($saveBuyback, 0, null, $product, $partneritems, true);

        }
        // insert END;

         
        if ($createDraftGrn){
            $return['success'] = true;
        }else{
            $return['success'] = false;
        }


        return $return;


        // Bil [A]
        // Pusat Kos
        // Cawangan
        // No Siri Gadaian
        // 24.0K    [E] => 0 
        // 22.8k    [F] => 0
        // 22.0K    [G] =>   4.65 
        // 21.0K    [H] => 0
        // 20.0K    [I] => 0
        // 18.0K    [J] => 0
        // 14.0K    [K] => 0
        // 09.0K    [L] => 0
        // Jumlah    [M] =>   4.65 
        // Jualan    [N] =>   1,016.44 
    }

    private function map_purity($cell, $weight){
        if ($weight <= 0){
            return false;
        }
        switch ($cell) {
            case 'E':
                $purity = 'GS-999-9'; // format to sap_['u_itemcode'] => GS-999/24K
                break;
            case 'F':
                $purity = 'GS-950';
                break;
            case 'G':
                $purity = 'GS-916';
                break;
            case 'H':
                $purity = 'GS-875';
                break;
            case 'I':
                $purity = 'GS-835'; // 833
                break;
            case 'J':
                $purity = 'GS-750/18K';
                break;
            case 'K':
                $purity = 'GS-585/14K';
                break;
            case 'L':
                $purity = 'GS-375/9K';
                break;
            
            default:
                return false;
                break;
        }

        $purities = [
            "purity" => $purity,
            "weight" => $weight
        ];

        return $purities;
    }

    public function getPartnerFromBranch_POS($partner_branches, $branch_code){
        // foreach ($partners as $partner){
        //     $branchid = $partner->getBranch($branch_code);
        //     if ($branchid != 0 && $branchid){
        //         $return['branchid'] = $branchid->id;
        //         $return['partnerid'] = $partner->id;
        //         return $return;
        //     }
        // }
        // return false;
        foreach ($partner_branches as $x => $partner){
            $branchid = array_search($branch_code, $partner);
            if (!empty($branchid)){
                $return['branchid'] = $branchid;
                $return['partnerid'] = $x;
                return $return;
            }
            // foreach ($branches as $branch){
            //     if ($branches->code == $branch_code){
            //         return $partner_branches[$x];
            //     }
            // }
        }
        return false;
    }

    private function getPartnersBranches($partners){
        $return_branches = [];
        foreach ($partners as $x => $partner){
            $branches = $this->app->PartnerStore()->getRelatedStore('branches')->searchTable()->select(["id","code"])->where('partnerid', $partner->id)->execute();
            foreach ($branches as $branch){
                $return_branches[$partner->id][$branch->id] = $branch->code;
            }
        }

        return $return_branches;
    }


    private function formatSapRateCardsReturn($sapRateCards = null){
        // print_r($sapRateCards['ratecard']);exit;
        // _PENDING TEMP
        // format sap return card to this format
        $cards = [
            // '999' => 98, // ` their purity code OR our purity code ` => ` _SAP_RETURN rate value `
            '999' => 100, // ` their purity code OR our purity code ` => ` _SAP_RETURN rate value `
            '950' => 95,
            '916' => 90,
            '875' => 85,
            '835' => 80,
            '750' => 75,
        ];
        // _PENDING TEMP

        $cards = [];
        foreach ($sapRateCards['ratecard'] as $x => $card){
            $cards[$card['u_itemcode']] = $card['u_purity'];
        }
        return $cards;
    }

    private function getSapRate($sapRateCards, $purity){
        return $sapRateCards[$purity];
    }


    /**
     * This is a background job executor to process all orders in certain statuses
     * 
     */
     public function processNewBuybacks($partnerIds = null, $start = null, $end = null)
     {
        if (!$partnerIds){
            return false;
        }
        $cacher = $this->app->getCacher();
        // $start $end MUST be string in UTC time
        if ($start && $end){
            $allPendingOrders = $this->app->buybackStore()->searchTable()->select()
                ->where('status', Buyback::STATUS_PENDING)
                ->andWhere('partnerid', 'IN', $partnerIds)
                ->andWhere('createdon', '>=', $start)
                ->andWhere('createdon', '<=', $end)
                ->execute();
        }else{
            $allPendingOrders = $this->app->buybackStore()->searchTable()->select()
                ->where('status', Buyback::STATUS_PENDING)
                ->andWhere('partnerid', 'IN', $partnerIds)
                ->execute();
        }
        foreach($allPendingOrders as $anOrder) {
            $lockKey = '{pendingBuybackProcessor}:' . $anOrder->id;
            if($cacher->waitForLock($lockKey, 1, 30, 0)) {
                try {
                    $order = $this->confirmBuyback($anOrder);
                    if(Buyback::STATUS_CONFIRMED == $order->status) {
                        $cacher->set('{confirmedBuyback}:'.$order->id, 1, 600 /* 10 minutes */);
                    }
                } catch(\Exception $e) {
                    $this->log("Error automatic processing of pending order {$anOrder->buybackno} with error " . $e->getMessage(), SNAP_LOG_ERROR);
                }
                $cacher->unlock($lockKey);
            }
        }
     }

    // public function read
}
?>