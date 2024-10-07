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
Use \Snap\object\futureorder;
Use \Snap\object\partner;
Use \Snap\object\product;
Use \Snap\object\PriceStream;
Use \Snap\object\PriceProvider;
Use \Snap\object\PriceValidation;
Use \Snap\object\PriceAdjuster;
Use \Snap\object\Order;
use \Snap\object\OtcPricingModel;

class PriceManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Listen to the following events and update price information as appropriate:
     * 1)  New order created - update the pricevalidation to being used for order.
     * 2)  
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if($changed instanceof \Snap\manager\SpotOrderManager && $state->isNewAction()) {
            //Register and ensure the price validation can not be used anymore.
            if($state->otherParams['priceObject'] instanceof \Snap\object\PriceValidation) {
                $spotOrder = $state->target;
                $priceValidation = $state->otherParams['priceObject'];
                $priceValidation->orderid = $spotOrder->id;
                $this->app->priceValidationStore()->save($priceValidation);
            }
        }
    }
    
    /**
     * This method will return the spot price when merchant request for latest price
     * @param  partner  $partner       The partner that is associated with this order
     * @param  string   $apiVersion    API version used to create the booking
     * @param  product  $product       The product in which the booking is made
     * @param  Enum     $companyBuy    Order transaction type (buy / sell)
     * @return priceValidation
     */
    public function getSpotPrice(Partner $partner, Product $product, $partnerReference, $companyBuy = true, $apiVersion = '', $price_only = false) : PriceValidation
    {
        $this->log("Getting spot buy pricing for {$partner->code} for {$product->code}", SNAP_LOG_ERROR);
        //validity checking
        if(! $partner->status) {
            throw \Snap\api\exception\PartnerNotActiveException::fromTransaction($partner);
        }
        if( ($companyBuy && ! $partner->canSell($product)) ||
            (! $companyBuy && ! $partner->canBuy($product))) {
            throw \Snap\api\exception\PartnerUnableToTransactionProduct::fromTransaction($partner, ['productCode' => $product->code]);
        }
        //Find the appropriate provider to handle this
        
        $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);
        if(! $provider) {
            throw \Snap\api\exception\PriceProviderNotFound::fromTransaction($partner, ['pricesourceid' => $priceSourceId, 'categoryid' => $productCategory]);
        }
        $latestPrice = $this->getLatestSpotPrice($provider);

        if(! $provider->isPriceDataFresh($latestPrice)) {
            throw \Snap\api\exception\PriceStreamDataStale::fromTransaction($partner);
        }
        if ($price_only){
            return $latestPrice;
        }
        //3)  Create a priceValidation to handle this API call.
        $priceValidation = $this->app->priceValidationStore()->create();
        $priceValidation->partnerid = $partner->id;
        $priceValidation->pricestreamid = $latestPrice->id;
        $priceValidation->apiversion = $apiVersion;
        $priceValidation->premiumfee = $partner->getPremiumFee($product);
        $priceValidation->refineryfee = $partner->getRefineryFee($product);
        $priceValidation->requestedtype = $companyBuy ? PriceValidation::REQUEST_COMPANYBUY : PriceValidation::REQUEST_COMPANYSELL;
        $priceValidation->price =  $partner->calculator()->round($companyBuy ? $latestPrice->companybuyppg : $latestPrice->companysellppg);
        $priceValidation->orderid = 0;
        //Added by Devon on 2021/01/15 to temporary store a unverified uuid to avoid duplicate key error in DB 
        $priceValidation->uuid = "T" . hrtime(true);
        //End add 2021/01/15
        $now = new \DateTime();
        $priceValidation->timestamp = $now->format('Y-m-d H:i:s');
        // $hour = intval(floor($partner->orderconfirmallowance / 3600)) + 0;
        // $minute = intval(floor(($partner->orderconfirmallowance - ($hour * 60)) / 60)) + 0;
        // $second = $partner->orderconfirmallowance % 60;
        // $now->add(new \DateInterval("PT{$hour}H{$minute}M{$second}S"));
        $now->add(new \DateInterval("PT{$partner->orderconfirmallowance}S"));
        $priceValidation->validtill = $now->format('Y-m-d H:i:s');
        $priceValidation->reference = $partnerReference;
        $this->app->priceValidationStore()->save($priceValidation);
        if(0 < $priceValidation) {
            $observation = new \Snap\IObservation($priceValidation, \Snap\IObservation::ACTION_NEW, 0, ['rawPriceData' => json_encode($priceValidation->toArray()), 'pricestream' => $priceStream, 'partner' => $partner]);
            $this->notify($observation);
            return $priceValidation;
        }
        throw \Snap\api\exception\PriceValidationNotGenerated::fromTransaction( $priceStream);
    }

    /**
     * This method will be used to connect to the price engine DB to update adjustments that has been set by user.
     * 
     * @param  PriceProvider $priceProvider 
     * @param  PriceAdjuster $priceAdjustor 
     */
    public function adjustPriceEngineParameters($priceProvider, $priceAdjustor)
    {
        if(! preg_match('/Snappy/i', $priceProvider->url)) {
            return;  //nothing to do.  Only do for Snappy type connection
        }
        preg_match_all('/(\S+):\/\/(\S+):(\S+)\?(.*)/m', $priceProvider->url, $matches, PREG_SET_ORDER);
        $matches = $matches[0];
        $snappyParams['host'] = $matches[2] . ':' . $matches[3];
        $options = $matches[4];
        foreach(explode('&', $options) as $anOption) {
            list($key, $value) = explode('=', $anOption);
            $snappyParams[$key] = $value;
        }
        $snappDbPdo = new \Snap\db('mysql', $snappyParams['host'], $snappyParams['username'], $snappyParams['password'], $snappyParams['database'], '');
        $countEntriesSql = sprintf("Select count(*) as total from %s;", $snappyParams['table']);
        $rows = $snappDbPdo->query($countEntriesSql);
        $b4TotalRecords = $rows->fetch(\PDO::FETCH_ASSOC);
        $b4TotalRecords = $b4TotalRecords['total'];

        $updateSql = sprintf("insert into %s (adj_fxbuypremium, adj_fxsellpremium, adj_buymargin, adj_sellmargin, adj_refinefee, adj_supplierpremium, ".
                             "adj_sellspread, adj_buyspread, adj_createDate, adj_createBy, adj_createByName, adj_uuid, adj_effectiveStart, adj_effectiveEnd) VALUES " . 
                             "(%.3f, %.3f, %.3f, %.3f, %.3f, %.3f, %.3f, %.3f, %s, %d, %s, %s, %s, %s);", $snappyParams['table'], 
                             $priceAdjustor->fxbuypremium, 
                             $priceAdjustor->fxsellpremium, 
                             $priceAdjustor->buymargin, 
                             $priceAdjustor->sellmargin, 
                             $priceAdjustor->refinefee, 
                             $priceAdjustor->supplierpremium, 
                             $priceAdjustor->sellspread, 
                             $priceAdjustor->buyspread, 
                             $snappDbPdo->quote($priceAdjustor->createdon->format('Y-m-d H:i:s')), 
                             $snappDbPdo->quote($priceAdjustor->createdby), 
                             $snappDbPdo->quote($this->app->getUsersession()->getUsername()), 
                             $snappDbPdo->quote($priceAdjustor->uuid),
                             $snappDbPdo->quote($priceAdjustor->effectiveon->format('H:i:s')),
                             $snappDbPdo->quote($priceAdjustor->effectiveendon->format('H:i:s')));

        $snappDbPdo->query($updateSql);
        $rows = $snappDbPdo->query($countEntriesSql);
        $afterTotalRecords = $rows->fetch(\PDO::FETCH_ASSOC);
        $afterTotalRecords = $afterTotalRecords['total'];
        if($afterTotalRecords <= $b4TotalRecords) {
            $this->log(__METHOD__."(): Unable to update price adjustment for {$priceProvider->name}", SNAP_LOG_ERROR);
            throw new \Snap\InputException(gettext("Unable to connect to price engine to update the adjusted price"), InputException::FIELD_ERROR, 'uuid');
        }else{
            $this->log(__METHOD__."(): Completed adding data to {$priceProvider->name} price engine.  $afterTotalRecords | $b4TotalRecords", SNAP_LOG_ERROR);
            return true;
        }
    }
    
     /**
     * This method will be used to connect to the price engine DB to update adjustments that has been set by user.
     * 
     * @param  PriceProvider $priceProvider 
     * @param  PriceAdjuster $priceAdjustor 
     */
    public function adjustPriceEngineDelayParameters($priceProvider, $priceDelay)
    {
        if(! preg_match('/Snappy/i', $priceProvider->url)) {
            return;  //nothing to do.  Only do for Snappy type connection
        }
        preg_match_all('/(\S+):\/\/(\S+):(\S+)\?(.*)/m', $priceProvider->url, $matches, PREG_SET_ORDER);
        $matches = $matches[0];
        $snappyParams['host'] = $matches[2] . ':' . $matches[3];
        $options = $matches[4];
        foreach(explode('&', $options) as $anOption) {
            list($key, $value) = explode('=', $anOption);
            $snappyParams[$key] = $value;
        }
        $snappDbPdo = new \Snap\db('mysql', $snappyParams['host'], $snappyParams['username'], $snappyParams['password'], $snappyParams['database'], '');
        $countEntriesSql = sprintf("Select count(*) as total from %s;", $snappyParams['table']);
        $rows = $snappDbPdo->query($countEntriesSql);
        $b4TotalRecords = $rows->fetch(\PDO::FETCH_ASSOC);
        $b4TotalRecords = $b4TotalRecords['total'];

        $updateSql = sprintf("insert into %s (pricesource, delay, createdon, createdby, createByName) VALUES ".
                             "(%s, %d, %s, %d, %s );",
                             $snappyParams['table'], 
                             $priceDelay->pricesource, 
                             $priceDelay->delay,  
                             $snappDbPdo->quote($priceDelay->createdon->format('Y-m-d H:i:s')), 
                             $snappDbPdo->quote($priceDelay->createdby), 
                             $snappDbPdo->quote($this->app->getUsersession()->getUsername()) 
                            );

        $snappDbPdo->query($updateSql);
        $rows = $snappDbPdo->query($countEntriesSql);
        $afterTotalRecords = $rows->fetch(\PDO::FETCH_ASSOC);
        $afterTotalRecords = $afterTotalRecords['total'];
        if($afterTotalRecords <= $b4TotalRecords) {
            $this->log(__METHOD__."(): Unable to update price adjustment for {$priceProvider->name}", SNAP_LOG_ERROR);
            throw new \Snap\InputException(gettext("Unable to connect to price engine to update the adjusted price"), InputException::FIELD_ERROR, 'uuid');
        }
        $this->log(__METHOD__."(): Completed adding data to {$priceProvider->name} price engine.  $afterTotalRecords | $b4TotalRecords", SNAP_LOG_ERROR);
    }
    /**
     * Checks if the transaction can be transaction with the price
     * 
     * @param  partner  $partner          The partner that is associated with this order
     * @param  product  $product          The product in which the booking is made
     * @param  decimal  $givenPrice       The price that to transaction on
     * @param  string   $priceIdString    The price ID to check against
     * @return boolean                    True if can transact, false otherwise.
     */
    public function canTransactWithPrice($partner, $product, $givenPrice, $priceIdString)
    {
    }

    /**
     * Return the PriceStream object given the price reference ID
     * @param  pricevalidation $priceIdReference [description]
     * @return PriceStream
     */
    public function getPriceStreamByRef(PriceValidation $priceIdReference) : PriceStream
    {
    }

    /**
     * This method will handle events when receiving a new price data from the source.
     * @param  PriceProvider $provider          the provider the data is from
     * @param  float         $companybuyppg     The price of company buy per gram
     * @param  float         $companysellppg    The price of company sell per gram
     * @param  float         $rawfxusdbuy          The price of raw buy usd -> myr
     * @param  float         $rawfxusdsell         The price of raw sell usd -> myr
     * @param  number        $providePriceId    The source provider given price ID
     * @param  string        $rawCollectedData  Original data collected from source
     */
    public function onReceiveNewPriceData(PriceProvider $provider, $companybuyppg, $companysellppg, $rawfxusdbuy, $rawfxusdsell, $rawfxsource, $providePriceId, $providerTimestamp, $rawCollectedData)
    {
        if ($rawfxsource == 'nofxfeed'){
            return false;
        }
        
        // set radis cache key for temp value
        $priceStreamKey = 'pricestreamtemp';
        $cacher = $this->app->getCacher();
        $tempValue = $cacher->increment($priceStreamKey, 1);

        //1)  Get price stream store and create new pricestream object
        $priceStream = $this->app->priceStreamStore()->create([
            'uuid' => $tempValue,
            'providerid' => $provider->id,
            'currencyid' => $provider->currencyid,
            'providerpriceid' => $providePriceId,
            'companybuyppg' => $companybuyppg,
            'companysellppg' => $companysellppg,
            'rawfxusdbuy' => $rawfxusdbuy,
            'rawfxusdsell' => $rawfxusdsell,
            'rawfxsource' => $rawfxsource,
            'pricesourceid' => $provider->pricesourceid,
            'pricesourceon' => $providerTimestamp,
            'status' => 1
        ]);
        $updatedPriceStream = $this->app->priceStreamStore()->save($priceStream);

        //2)  Store temporarily into redis for fast access.
        $latestPriceDataKey = '{PriceFeedLast}:' . $provider->id;
        $this->app->setCache($latestPriceDataKey, $priceStream->toCache(), 60);

        $this->postToPriceChannel($updatedPriceStream, $provider);

        //3)  notify of new pricestream object created.
        $notification = new \Snap\IObservation($priceStream, \Snap\IObservation::ACTION_NEW, 0, 
                                            ['rawPriceData' => $rawCollectedData, 'provider' => $provider->connectinfo]);
        $this->notify($notification);
    }

    private function postToPriceChannel(PriceStream $priceStream, PriceProvider $provider) {
        $app = $this->app;
        if ($app->getConfig()->isKeyExists('app.pricestream.publish.uri')) {
            $baseUrl = $app->getConfig()->{'app.pricestream.publish.uri'};
            $fullUrl = $baseUrl  . $provider->id;

            $data = [
                'companybuy'    => $priceStream->companybuyppg,
                'companysell'   => $priceStream->companysellppg,
                // 'rawfxusdbuy'   => $priceStream->rawfxusdbuy,
                // 'rawfxusdsell'  => $priceStream->rawfxusdsell,
                // 'rawfxsource'   => $priceStream->rawfxsource,
                'uuid'          => $priceStream->uuid,
                'timestamp'     => time(),
            ];

            $response = [
                "event"     => "read",
                "data"      => [$data]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_PROXY, '');//set proxy to empty, turn off the default proxy
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            
            if (curl_errno($ch)) {
                $this->log("[PriceManager] Unable to POST to price stream, Curl error: " . curl_errno($ch), SNAP_LOG_DEBUG);
            }
            
            curl_close ($ch);
        }
        
    }

    /**
     * This method returns the latest spot price for the given PriceProvider.  
     * NOTE: 
     * This method is to be used within the business logic managers and not meant for API use.
     * 
     * @param  \Snap\object\PriceProvider $provider
     * @return PriceStream
     */
    public function getLatestSpotPrice(\Snap\object\PriceProvider $provider, $refid = null) : PriceStream
    {
        $this->log("getLatestSpotPrice $refid .start. ", SNAP_LOG_DEBUG);
        //2) Get the last pricestream from Redis cache.
        $latestPriceDataKey = '{PriceFeedLast}:' . $provider->id;
        $priceStreamCache = $this->app->getCache($latestPriceDataKey);
        if(0 == strlen($priceStreamCache) || null == $priceStreamCache) {
            $str = 'priceStreamCache is empty';
            $this->log("Order $refid .select from DB. ", SNAP_LOG_DEBUG);
            $latestPrice = $this->app->priceStreamStore()->searchTable()
                        ->select()
                        ->where('providerid', $provider->id)
                        ->orderby('id', 'DESC')
                        ->one();
        } else {
            $str = 'priceStreamCache found';
            $this->log("getLatestSpotPrice $refid .select from cache. ", SNAP_LOG_DEBUG);
            $latestPrice = $this->app->priceStreamStore()->create();
            $latestPrice->fromCache($priceStreamCache);
        }
        
        //debug
        if (! $latestPrice instanceof \Snap\object\PriceStream) {
            $content = 'latestPriceDataKey: ' . $latestPriceDataKey . '<br/>';
            $content .= 'priceStreamCache: ' . $priceStreamCache . '<br/>';
            $content .= 'providerid: ' . $provider->id . '<br/>';
            $content .= 'str: ' . $str . '<br/>';
            $this->debugEmail($content, __function__);
        }
        
        return $latestPrice;
    }
    
    private function debugEmail ($content, $method) {
        $mailer = $this->app->getMailer();
        $receivers = 'jeremy@silverstream.my,jeff@silverstream.my';
        $receiverArr = explode(',', $receivers);
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            $mailer->addAddress($receiver);
        }

        $mailer->isHTML();
        $mailer->Subject = 'Debug email: ' . $method;
        $mailer->Body    = $content;

        $sent = $mailer->send();
    }

    /**
     * This method returns the 1st spot price after 8:30am for the given PriceProvider.  
     * NOTE: 
     * This method is to be used within the business logic managers and not meant for API use. Calculation of user dashboard
     * 
     * @param  \Snap\object\PriceProvider $provider
     * @return PriceStream
     */
    public function getFirstDaySpotPrice(\Snap\object\PriceProvider $provider, $refid = null) : PriceStream
    {
        $this->log("getFirstDaySpotPrice $provider->id .start. ", SNAP_LOG_DEBUG);
        //2) Get the first pricestream of the day from Redis cache.
        $firstPriceOfDayDataKey = '{PriceFeedFirstOfDay}:' . $provider->id;
        $priceStreamCache = $this->app->getCache($firstPriceOfDayDataKey);
        /*get1stPrice after 8.30am*/
        $getDateTimeFirst = date("Y-m-d 00:30:00");
        $getCurrentDate = date("Y-m-d H:i:s");
        $nextDateTime = date('Y-m-d 00:30:00', strtotime(' +1 day'));

        $convertCurrDate = strtotime($getCurrentDate);
        $convertTomDate = strtotime($nextDateTime);
        $diffTimes = $convertTomDate-$convertCurrDate;

        if(0 == strlen($priceStreamCache) || null == $priceStreamCache) {
            $this->log("getFirstDaySpotPrice $provider->id .select from DB. ", SNAP_LOG_DEBUG);
            $firstPriceOfDay = $this->app->priceStreamStore()->searchTable()
                        ->select()
                        ->where('providerid', $provider->id)
                        ->andWhere('createdon', '>=', $getDateTimeFirst)
                        ->orderby('id', 'ASC')
                        ->one();

            $this->app->setCache($firstPriceOfDayDataKey, $firstPriceOfDay->toCache(), $diffTimes);
        } else {
            $this->log("getFirstDaySpotPrice$provider->id .select from cache. ", SNAP_LOG_DEBUG);
            $firstPriceOfDay = $this->app->priceStreamStore()->create();
            $firstPriceOfDay->fromCache($priceStreamCache);
        }
        return $firstPriceOfDay;
    }

    /**
     * checks if the provided PriceValidation object has been used to create an order.
     * @param  priceValidation  $priceValidation 
     * @return boolean          True if the price validation can be used to do transaction.  False otherwise.
     */
    public function isPriceValidationTransacted($priceValidation)
    {
    }

    /**
     * This method will start and run the price provider provided
     * @param  PriceProvider $priceProvider   Provider to run
     */
    public function startPriceCollector(PriceProvider $priceProvider)
    {
        $priceProvider->status = \Snap\object\SnapObject::STATUS_ACTIVE;
        $this->app->priceProviderStore()->save($priceProvider);
        $api = $this->app->apiManager()->getPriceCollector($priceProvider);
        $api->run();
    }

    /**
     * Stops the running price collector
     * @param  PriceProvider $priceProvider 
     */
    public function stopPriceCollector(PriceProvider $priceProvider)
    {
        $priceProvider->status = \Snap\object\SnapObject::STATUS_INACTIVE;
        $this->app->priceProviderStore()->save($priceProvider);
        $getInactive = true;
        $api = $this->app->apiManager()->getPriceCollector($priceProvider, $getInactive);
        $api->stop();
    }

    /**
     * Checks if the specified price collector is running
     * @param  PriceProvider $priceProvider 
     * @return boolean      Returns true if the collector is running.  False otherwise.
     */
    public function isPriceCollectorRunning(PriceProvider $priceProvider)
    {
        $getInactive = true;
        $api = $this->app->apiManager()->getPriceCollector($priceProvider, $getInactive);
        return $api->isRunning();
    }
    
    /**
     * This method will handle events when receiving a new price data from the source.
     * @param  PriceProvider $provider          the provider the data is from
     * @param  float         $companybuyppg     The price of company buy per gram
     * @param  float         $companysellppg    The price of company sell per gram
     * @param  float         $rawfxusdbuy          The price of raw buy usd -> myr
     * @param  float         $rawfxusdsell         The price of raw sell usd -> myr
     * @param  number        $providePriceId    The source provider given price ID
     * @param  string        $rawCollectedData  Original data collected from source
     * @param  string        $requestParams  api request params
     */
    public function onReceiveNewPriceStreamData(PriceProvider $provider, $companybuyppg, $companysellppg, $rawfxusdbuy, $rawfxusdsell, $rawfxsource, $providePriceId, $providerTimestamp, $rawCollectedData, $requestParams, $priceAdjusterId = 0)
    {
        if ($rawfxsource == 'nofxfeed'){
            return false;
        }
        
        // set radis cache key for temp value
        $priceStreamKey = 'pricestreamtemp';
        $cacher = $this->app->getCacher();
        $tempValue = $cacher->increment($priceStreamKey, 1);

        //1)  Get price stream store and create new pricestream object
        $priceStream = $this->app->priceStreamStore()->create([
            'uuid' => $tempValue,
            'providerid' => $provider->id,
            'currencyid' => $provider->currencyid,
            'providerpriceid' => $providePriceId,
            'priceadjusterid' => $priceAdjusterId,
            'companybuyppg' => $companybuyppg,
            'companysellppg' => $companysellppg,
            'rawfxusdbuy' => $rawfxusdbuy,
            'rawfxusdsell' => $rawfxusdsell,
            'rawfxsource' => $rawfxsource,
            'pricesourceid' => $provider->pricesourceid,
            'pricesourceon' => $providerTimestamp,
            'status' => 1
        ]);
        $updatedPriceStream = $this->app->priceStreamStore()->save($priceStream);


        $this->postToPriceChannel($updatedPriceStream, $provider);
        $this->postToWebAppPriceChannel($updatedPriceStream, $provider);

        //2)  notify of new pricestream object created.
        $notification = new \Snap\IObservation($priceStream, \Snap\IObservation::ACTION_NEW, 0, 
                                            ['rawPriceData' => $rawCollectedData, 'provider' => $requestParams]);
        $this->notify($notification);
    }
	
	private function postToWebAppPriceChannel(PriceStream $priceStream, PriceProvider $provider) {
        $app = $this->app;
        if ($app->getConfig()->isKeyExists('app.pricestream.publish.uri.webapp')) {
            $baseUrl = $app->getConfig()->{'app.pricestream.publish.uri.webapp'};
            //$fullUrl = 'https://172.28.32.147/pricestream/publish/16';
			$fullUrl = $baseUrl  . $provider->id;

            $data = [
                'companybuy'    => $priceStream->companybuyppg,
                'companysell'   => $priceStream->companysellppg,
                // 'rawfxusdbuy'   => $priceStream->rawfxusdbuy,
                // 'rawfxusdsell'  => $priceStream->rawfxusdsell,
                // 'rawfxsource'   => $priceStream->rawfxsource,
                'uuid'          => $priceStream->uuid,
                'timestamp'     => time(),
            ];

            $response = [
                "event"     => "read",
                "data"      => [$data]
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
			curl_setopt($ch, CURLOPT_PROXY, '');//set proxy to empty, turn off the default proxy
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //skip verify the certificate's name against host
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //skip verify the peer's SSL certificate
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($response));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);

            if (curl_errno($ch)) {
                $this->log("[PriceManager] Unable to POST to price stream, Curl error: " . curl_errno($ch), SNAP_LOG_DEBUG);
            }
			
			curl_close ($ch);
        }
        
    }
    
    /**
     * Adjusts the price stream for a partner based on the price adjuster configuration.
     *
     * @param object $partner The partner object.
     * @param object $product The product object.
     * @param float $buyPrice The buy price of the product.
     * @param float $sellPrice The sell price of the product.
     * @return array An array containing the adjusted buy and sell prices and the ID of the price adjuster that was applied.
     */
    public function adjustPriceStream ($partner, $product, $buyPrice, $sellPrice)
    {
        $data = array(
            'companybuyppg' => $buyPrice,
            'companysellppg' => $sellPrice,
            'priceadjusterid' => 0
        );
        
        $priceProvider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);
        if (!$priceProvider) {
            $this->log(__function__ . " Price provider not found for partner", SNAP_LOG_ERROR);
            return $data;
        }
        
        $partnerPriceAdjuster = $this->app->priceAdjusterStore()->getForPartnerByPriceProvider($priceProvider);
        if (0 == count($partnerPriceAdjuster)) {
            $this->log(__function__ . " Partner price adjuster not found for partner", SNAP_LOG_ERROR);
            return $data;
        }
        
        return $this->getAdjustPrice($partnerPriceAdjuster, $buyPrice, $sellPrice);
    }
    
    /**
     * Adjusts the buy and sell prices based on the partner price adjuster.
     *
     * @param array $partnerPriceAdjuster An array of price adjuster objects.
     * @param float $buyPrice The current buy price.
     * @param float $sellPrice The current sell price.
     * @return array An array containing the new buy price, sell price, and price adjuster ID.
     */
    public function getAdjustPrice($partnerPriceAdjuster, $buyPrice, $sellPrice) {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now = $now->format('H:i:s');
        
        foreach ($partnerPriceAdjuster as $priceAdjuster) {
            $effectiveOn = \Snap\Common::convertUserDatetimeToUTC($priceAdjuster->effectiveon)->format('H:i:s');
            $effectiveEndOn = \Snap\Common::convertUserDatetimeToUTC($priceAdjuster->effectiveendon)->format('H:i:s');
            
            $isEffectiveNow = ($now >= $effectiveOn || $now <= $effectiveEndOn);
            
            if (in_array($priceAdjuster->tier, [PriceAdjuster::TIER_PEAK, PriceAdjuster::TIER_NONPEAK]) && $isEffectiveNow) {
                if (PriceAdjuster::TYPE_USEPERCENT == $priceAdjuster->usepercent) {
                    $newBuyPrice = $buyPrice + ($buyPrice * ($priceAdjuster->buypercent / 100));
                    $newSellPrice = $sellPrice + ($sellPrice * ($priceAdjuster->sellpercent / 100));
                } else {
                    $buySpread = $priceAdjuster->buyspread / 1000;
                    $sellSpread = $priceAdjuster->sellspread / 1000;
                    $newBuyPrice = ($buySpread <= 0) ? ($buyPrice - abs($buySpread)) : ($buyPrice + abs($buySpread));
                    $newSellPrice = ($sellSpread <= 0) ? ($buyPrice - abs($sellSpread)) : ($buyPrice + abs($sellSpread));
                }
                
                $priceAdjusterId = $priceAdjuster->id;
                break;
            }
        }
        
        if (!isset($priceAdjusterId)) {
            $newBuyPrice = $buyPrice;
            $newSellPrice = $sellPrice;
            $priceAdjusterId = 0;
        }

        return ['companybuyppg' => $newBuyPrice, 'companysellppg' => $newSellPrice, 'priceadjusterid' => $priceAdjusterId];
    }
    
    /**
     * Calculate the original price based on the provided price adjuster.
     *
     * @param PriceAdjuster $partnerPriceAdjuster The price adjuster to use.
     * @param float $adjustPrice The adjusted price to use.
     * @param string $type The order type.
     * @return float The original price.
     */
    public function getOriginalPrice ($partnerPriceAdjuster, $adjustPrice, $type)
    {
        if (PriceAdjuster::TYPE_USEPERCENT == $partnerPriceAdjuster->usepercent) {
            $priceSpread = (Order::TYPE_COMPANYBUY == $type) ? $partnerPriceAdjuster->buypercent : $partnerPriceAdjuster->sellpercent;
            $multiplier = 1 + ($priceSpread / 100);
            $originalPrice = $adjustPrice / $multiplier;
        } else {
            $priceSpread = (Order::TYPE_COMPANYBUY == $type) ? $partnerPriceAdjuster->buyspread : $partnerPriceAdjuster->sellspread;
            $priceSpread /= 1000;
            $originalPrice = ($priceSpread <= 0) ? ($adjustPrice + abs($priceSpread)) : ($adjustPrice - abs($priceSpread));
        }
        return $originalPrice;
    
    }

	/**
	 * Calculates the base price using the OTC pricing model and adjustments.
	 *
	 * @param PriceProvider $priceProvider The price provider object.
	 * @param float $buyPrice The original buy price.
	 * @param float $sellPrice The original sell price.
	 *
	 * @return array The base price data.
	 */
	public function getOtcPricingModelBasePrice ($priceProvider, $buyPrice, $sellPrice)
	{
		$data = array(
            'companybuyppg' => $buyPrice,
            'companysellppg' => $sellPrice,
            'priceadjusterid' => 0
        );
		
		$priceProviderId = $priceProvider->id;
		
		$baseOtcPricingModelKey = "{BaseOtcPricingModel}:{$priceProviderId}";
        $baseOtcPricingModelCache = $this->app->getCache($baseOtcPricingModelKey);
		
		if (empty($baseOtcPricingModelCache)) {
			$otcPricingModel = $this->app->otcpricingmodelStore()->searchTable()
								->select()
								->where('priceproviderid', $priceProviderId)
								->andWhere('status', OtcPricingModel::STATUS_ACTIVE)
								->orderBy('sellmarginpercent', 'DESC')
								->orderBy('buymarginpercent', 'DESC')
								->one();
			
								
			if (!$otcPricingModel) {
				$this->log(__function__ . " OTC Pricing Model not found for partner", SNAP_LOG_ERROR);
				return $data;
			}
			$this->app->setCache($baseOtcPricingModelKey, $otcPricingModel->toCache());
		} else {
			$otcPricingModel = $this->app->otcpricingmodelStore()->create();
            $otcPricingModel->fromCache($baseOtcPricingModelCache);
		}
		
		$baseSellMarginPercent = $otcPricingModel->sellmarginpercent;
		$baseBuyMarginPercent = $otcPricingModel->buymarginpercent;
		$baseSellMarginAmount = $otcPricingModel->sellmarginamount;
		$baseBuyMarginAmount = $otcPricingModel->buymarginamount;
		
		if ($baseSellMarginPercent) {
			$sellPrice += ($sellPrice * ($baseSellMarginPercent / 100));
		}
		
		if ($baseBuyMarginPercent) {
			$buyPrice += ($buyPrice * ($baseBuyMarginPercent / 100));
		}
		
		if ($baseSellMarginAmount) {
			$sellPrice = ($baseSellMarginAmount <= 0) ? ($sellPrice - abs($baseSellMarginAmount)) : ($sellPrice + abs($baseSellMarginAmount));
		}
		
		if ($baseBuyMarginAmount) {
			$buyPrice = ($baseBuyMarginAmount <= 0) ? ($buyPrice - abs($baseBuyMarginAmount)) : ($buyPrice + abs($baseBuyMarginAmount));
		}
		
		$data = array(
            'companybuyppg' => $buyPrice,
            'companysellppg' => $sellPrice,
            'priceadjusterid' => $otcPricingModel->id
        );

		return $data;
	}
	
	/**
	 * Calculates the original price based on the OTC pricing model and adjustments.
	 *
	 * @param OtcPricingModel $otcPricingModel The OTC pricing model object.
	 * @param float $basePrice The base price.
	 * @param string $type The type of the order (Order::TYPE_COMPANYSELL or Order::TYPE_COMPANYBUY).
	 *
	 * @return float The original price.
	 */
	public function getOtcPricingModelOriginalPrice ($otcPricingModel, $basePrice, $type)
	{
		$baseSellMarginPercent = $otcPricingModel->sellmarginpercent;
		$baseBuyMarginPercent = $otcPricingModel->buymarginpercent;
		$baseSellMarginAmount = $otcPricingModel->sellmarginamount;
		$baseBuyMarginAmount = $otcPricingModel->buymarginamount;
		
		$originalPrice = null;
		
		if (Order::TYPE_COMPANYSELL == $type) {
			if ($baseSellMarginPercent) {
				$multiplier = 1 + ($baseSellMarginPercent / 100);
				$originalPrice = $basePrice / $multiplier;
			}
			
			if ($baseSellMarginAmount) {
				$originalPrice = ($baseSellMarginAmount <= 0) ? ($basePrice + abs($baseSellMarginAmount)) : ($basePrice - abs($baseSellMarginAmount));
			}
		}
		
		if (Order::TYPE_COMPANYBUY == $type) {
			if ($baseBuyMarginPercent) {
				$multiplier = 1 + ($baseBuyMarginPercent / 100);
				$originalPrice = $basePrice / $multiplier;
			}
			
			if ($baseBuyMarginAmount) {
				$originalPrice = ($baseBuyMarginAmount <= 0) ? ($basePrice + abs($baseBuyMarginAmount)) : ($basePrice - abs($baseBuyMarginAmount));
			}
		}

		return round($originalPrice ?? $basePrice, 2, PHP_ROUND_HALF_UP);
	}
}
?>