<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////

Namespace Snap\handler;

USe Snap\App;
use Snap\object\partner;
use Snap\object\PriceAdjuster;
use Snap\InputException;

class priceadjusterHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/system', 'priceadjuster');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        
        $this->mapActionToRights("prefillform", "add");
        $this->mapActionToRights("prefillformquick", "add");
        $this->mapActionToRights("getLatestData", "add");
        $this->mapActionToRights("addallquickadjuster", "add");


        $this->app = $app;
        $currentStore = $app->priceadjusterStore();
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }

    public function prefillform($app, $params){
        return $this->getPriceProviderList($app, $params);
    }
  
    public function getPriceProviderList($app, $params){
        $output = [];
        $priceproviders = $this->app->priceproviderStore()->searchTable()->select(['id','name'])->execute();
        foreach ($priceproviders as $priceprovider){
            array_push($output, $priceprovider->toArray());
        }

        return json_encode([
            "success" => true,
            "priceproviders" => $output,
        ]);
    }

    // Add pre query listing
    function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();
		
		// End OTC Partner
		if (isset($params['filter'])){
			// check filter case 
			switch($params['filter']){
				case 'otc':
					// filter for approval view
					// If filter is available add filter to settings
                    if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                        //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                        $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
                        $providerCode = 'INTLX.BSN';
                    }
                    else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
                        //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                        $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
                        $providerCode = 'INTLX.ALRAJHI';
                    }
                    else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
                        //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                        $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
                        $providerCode = 'INTLX.PosGold';
                    }
      
                    $priceprovider = $this->app->priceproviderStore()->searchTable()->select()->where('code', $providerCode)->one();
                    
                    $sqlHandle->andWhere('priceproviderid', $priceprovider->id);
                    // End
					break;
				case 'individual':
					// filter for individual
					break;
				default:
					// execute for default
					// $sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
			}
		}
		

		//$sqlHandle->andWhere('ordpartnerid', $bmmbpartnerid);
  
        return array($params, $sqlHandle, $fields);
    }
    
    public function getLatestData($app, $params){
        // $time = $params['time'];
        // $timeend = $params['timeend'];
        $tier = $params['tier'];
        // $newTime = new \DateTime($time, $this->app->getUserTimezone());
        // $newTime = \Snap\common::convertUserDatetimeToUTC($newTime);
        // $newTime = $newTime->format("H:i:s");
        // $newTimeEnd = new \DateTime($timeend, $this->app->getUserTimezone());
        // $newTimeEnd = \Snap\common::convertUserDatetimeToUTC($newTimeEnd);
        // $newTimeEnd = $newTimeEnd->format("H:i:s");

        $priceproviderid = $params['providerid'];
        $data = $this->app->priceadjusterStore()->searchTable()->select()->where('priceproviderid', $priceproviderid )
            ->andWhere('tier', $params['tier'])
            ->orderBy('id','desc')->one();
        // $data = $this->app->priceadjusterStore()->searchTable()->select()->where('priceproviderid', $priceproviderid )
        //     ->andWhere('effectiveon', 'like', '%'.$newTime)
        //     ->andWhere('effectiveendon', 'like', '%'.$newTimeEnd)
        //     ->orderBy('id','desc')->one();
        
        if (!$data){
            $data = $this->app->priceadjusterStore()->searchTable()->select()->where('priceproviderid', $priceproviderid )->orderBy('id','desc')->one();
            $data = $data->toArray();
            // $data['effectiveon'] = $data['effectiveon']->format('H:i:s');
            // $data['effectiveendon'] = $data['effectiveendon']->format('H:i:s');
            return json_encode([
                'success' => false,
                'data' => $data,
                'errorMessage' => 'Invalid Price Adjuster Data, Contact Administative',
            ]);
        }else{
            $data = $data->toArray();
            $data['effectiveon'] = $data['effectiveon']->format('H:i:s');
            $data['effectiveendon'] = $data['effectiveendon']->format('H:i:s');
        }
        return json_encode([
            'success' => true,
            'data' => $data,
        ]);
    }

    function onPreAddEditCallback($object, $params) {
		$effectiveon = new \DateTime($params['effectiveon']);
		$effectiveendon = new \DateTime($params['effectiveendon']);
        $params['effectiveon'] = $effectiveon->format('Y-m-d H:i:s');
        $params['effectiveendon'] = $effectiveendon->format('Y-m-d H:i:s');
        // print_r($object);exit;
        $object->effectiveon = $params['effectiveon'];
        $object->effectiveendon = $params['effectiveendon'];
        $object->uuid = $this->priceprovider_uuid();
        // Save tier
        $object->tier = (int)$params['hours'];

		return $object;
	}

    /**
     * Handle connecting to the PriceAdjustor engine to update the lastest adjustment settings.
     * @param  PriceAdjuster     $savedRec   Object that has been updated.
     * @param  array             $params     None.
     */
    public function onPostAddEditCallback($priceAdjustor, $params) {
        $priceProvider = $this->app->priceProviderStore()->getById($priceAdjustor->priceproviderid);
        $return = $this->app->priceManager()->adjustPriceEngineParameters($priceProvider, $priceAdjustor);
        if ($return){
            $return['success'] = true;
        }else{
            // revert
            $return['error'] = true;
            $return['errMessage'] = 'Unable to update price adjuster on SNAPPY. ';
        }
        return json_encode($return);
    }


    public function prefillformquick($app, $params){
        $priceadjusters = $this->getPriceAdjusters_quick($app, $params);

        return $priceadjusters;
    }
    private function getPriceAdjusters_quick($app, $params){

        // tier 1 7am- 6pm
        // tier 2 6pm - 12am
        $tiers = $this->getPriceTierNew();

        $now = new \DateTime(gmdate("Y-m-d\TH:i:s\Z", time()));
        $output = [];

        if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $providerCode = 'INTLX.BSN';
            $priceproviders = $this->app->priceproviderStore()->searchTable()->select(['id','name','code'])->where('code', $providerCode)->orderBy("index","asc")->execute();
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            $providerCode = 'INTLX.ALRAJHI';
            $priceproviders = $this->app->priceproviderStore()->searchTable()->select(['id','name','code'])->where('code', $providerCode)->orderBy("index","asc")->execute();
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $providerCode = 'INTLX.PosGold';
            $priceproviders = $this->app->priceproviderStore()->searchTable()->select(['id','name','code'])->where('code', $providerCode)->orderBy("index","asc")->execute();
        }
        else{
            $priceproviders = $this->app->priceproviderStore()->searchTable()->select(['id','name','code'])->orderBy("index","asc")->execute();
        }
       
        foreach ($priceproviders as $priceprovider){
            // array_push($output, $priceprovider->toArray());

            foreach ($tiers as $index => $tier){
                // $priceadjuster = $this->app->priceadjusterStore()->searchTable()->select()
                //     ->where('priceproviderid', $priceprovider->id)
                //     ->andWhere('effectiveon', 'like', '%'.$tier['starttime'])
                //     ->andWhere('effectiveendon', 'like', '%'.$tier['endtime'])
                //     ->orderBy('id', 'desc')
                //     ->one();
                $priceadjuster = $this->app->priceadjusterStore()->searchTable()->select()
                    ->where('priceproviderid', $priceprovider->id)
                    ->andWhere('tier', $tier['tier'])
                    ->orderBy('id', 'desc')
                    ->one();
                    
                // print_r($priceadjuster->toArray());exit;
                if ($priceadjuster){
                    // $output[$index][] = $priceadjuster->toArray();
                    $output[$priceadjuster->priceproviderid][$index] = $priceadjuster->toArray();
                    $output[$priceadjuster->priceproviderid]['channel_code'] = $priceprovider->code;
                    $output[$priceadjuster->priceproviderid]['provider_id'] = $priceprovider->id; // use for form submit
                }
            }

        }
        $output = array_values($output);
        // print_r(array_values($output));exit;

        return json_encode([
            "success" => true,
            "priceadjusters" => $output,
        ]);
    }

    public function addallquickadjuster($app, $params){
        // tier 1 7am- 6pm
        // tier 2 6pm - 12am
        $tiers = $this->getPriceTier();
        $now = new \DateTime(gmdate("Y-m-d\TH:i:s\Z", time()));

        // Get Current date
        $day_part  = date("Y-m-d");
        $format = 'Y-m-d H:i:s';

        date_default_timezone_set("Asia/Shanghai");
        // $userTimezone = new \DateTimeZone('Asia/Shanghai');

        $providers = $params['provider'];
        foreach ($providers as $provider_id => $provider){
            $storeProvider = $this->app->priceproviderStore()->getById($provider_id);
            if ($storeProvider){
                foreach ($provider as $provider_id => $tier){
                    // get price adjuster and add new based on THIS with replace designated quickadjuster values (buyspread, sellspread);
                    $base_price_adjuster = $this->app->priceadjusterStore()->getById($provider_id);
                    // $times = $tier[$index];
                    
                    // Do tier check
                    // Set time based on tier
                    // If 1 = tier 2
                    $t1EffectiveTime = $tier['tier1effectiveon'] . ':00';
                    $t1EffectiveEndTime = $tier['tier1effectiveendon'] . ':59';
            
                    $t2EffectiveTime = $tier['tier2effectiveon'] . ':00';
                    $t2EffectiveEndTime = $tier['tier2effectiveendon'] . ':59';
                    // Create tier dates
                    $tier1EffectiveOn = \DateTime::createFromFormat($format, $day_part.' '.$t1EffectiveTime);
                    $tier1EffectiveEndOn = \DateTime::createFromFormat($format, $day_part.' '.$t1EffectiveEndTime);
                    $tier2EffectiveOn = \DateTime::createFromFormat($format, $day_part.' '.$t2EffectiveTime);
                    $tier2EffectiveEndOn = \DateTime::createFromFormat($format, $day_part.' '.$t2EffectiveEndTime);
            
                    // // Set format to correct timezone
                    // $tier1EffectiveOn = $tier1EffectiveOn->setTimezone($userTimezone);
                    // $tier1EffectiveEndOn = $tier1EffectiveEndOn->setTimezone($userTimezone);
                    // $tier2EffectiveOn = $tier2EffectiveOn->setTimezone($userTimezone);
                    // $tier2EffectiveEndOn = $tier2EffectiveEndOn->setTimezone($userTimezone);

                    if($base_price_adjuster->tier == 1){

                        $effectiveOn = $tier2EffectiveOn;
                        $effectiveEndOn = $tier2EffectiveEndOn;

                    }else{
                        // Else null/ 0 = tier 1
                        $effectiveOn = $tier1EffectiveOn;
                        $effectiveEndOn = $tier1EffectiveEndOn;
                    }

                    // Filter tier usepercent 
                    if($tier['usepercent']){

                        $usepercent = 1;

                    }else{
                        // Else null/ 0 = tier 1
                        $usepercent = 0;
                    }

                    // Filter tier usepercent 
                    if($tier['usespreadcopy']){
                        $usespreadcopy = 1;
                    }else{
                        // Else null/ 0 = tier 1
                        $usespreadcopy = 0;
                    }

                    $new_price_adjuster = $this->app->priceadjusterStore()->create([
                        'uuid' => $this->priceprovider_uuid(),
                        'priceproviderid' => $base_price_adjuster->priceproviderid,
                        'fxbuypremium' => $base_price_adjuster->fxbuypremium,
                        'fxsellpremium' => $base_price_adjuster->fxsellpremium,
                        'buymargin' => $base_price_adjuster->buymargin,
                        'sellmargin' => $base_price_adjuster->sellmargin,
                        'refinefee' => $base_price_adjuster->refinefee,
                        'supplierpremium' => $base_price_adjuster->supplierpremium,
                        'buyspread' => $tier['buyspread'],
                        'sellspread' => $tier['sellspread'],
                        'effectiveon' => $effectiveOn,
                        'effectiveendon' => $effectiveEndOn,
                        'tier' => $base_price_adjuster->tier,
                        'usepercent' => $usepercent,
                        'buypercent' =>  $tier['buypercentage'],
                        'sellpercent' =>  $tier['sellpercentage'],

                        'usespreadcopy' => $usespreadcopy,
                        'buyspreadoriginal' =>  $tier['buyspreadoriginal'],
                        'sellspreadoriginal' =>  $tier['sellspreadoriginal'],
                        // 'effectiveon' => $base_price_adjuster->effectiveon,
                        // 'effectiveendon' => $base_price_adjuster->effectiveendon,
                    ]);

                    $save = $this->app->priceadjusterStore()->save($new_price_adjuster);
                    if ($save){
                        $priceAdjustor = $save;
                        // update on SNAPPY
                        $priceProvider = $this->app->priceProviderStore()->getById($priceAdjustor->priceproviderid);
                        $snappyreturn = $this->app->priceManager()->adjustPriceEngineParameters($priceProvider, $priceAdjustor);
                        //add into cache
                        if (preg_match('/(1|on|yes)/i', $this->app->getConfig()->{'otc.pricestream.adjust'})) {
                            $priceAdjustKey = (PriceAdjuster::TIER_NONPEAK == $base_price_adjuster->tier) ? "{PriceAdjustNonPeak}:{$priceAdjustor->priceproviderid}" : "{PriceAdjustPeak}:{$priceAdjustor->priceproviderid}";
                            $this->app->setCache($priceAdjustKey, $priceAdjustor->toCache());
                        }
                        if ($snappyreturn){
                            $return['success'] = true;
                        }else{
                            $return['success'] = false;
                        }
                    }else{
                        $return['success'] = false;
                    }
                }
            }
        }
        echo json_encode($return);
        // print_r($params);exit;
    }

    private function getPriceTier(){
        // tier 1 7am- 6pm
        // tier 2 6pm - 12am
        $tiers = [
            'tier1' => [
                'starttime' => '23:00:00',
                'endtime' => '09:59:59',
            ],
            'tier2' => [
                'starttime' => '10:00:00',
                'endtime' => '22:59:59',
            ],
        ];
        return $tiers;
    }

    private function getPriceTierNew(){
        // tier 1 7am- 6pm
        // tier 2 6pm - 12am
        $tiers = [
            'tier1' => [
                'tier' => PriceAdjuster::TIER_PEAK
            ],
            'tier2' => [
                'tier' => PriceAdjuster::TIER_NONPEAK
            ],
        ];
        return $tiers;
    }

    private function priceprovider_uuid($data = null){
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s%s', str_split(bin2hex($data), 4));
    }

}