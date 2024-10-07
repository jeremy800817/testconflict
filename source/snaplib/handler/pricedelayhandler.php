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
use Snap\InputException;

class pricedelayHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/system', 'pricedelay');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        
        $this->mapActionToRights("prefillform", "add");
        $this->mapActionToRights("getLatestData", "add");


        $this->app = $app;
        $currentStore = $app->priceProviderStore();
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }

    public function prefillform($app, $params){
        return $this->getPriceDelayList($app, $params);
    }
  
    public function getPriceDelayList($app, $params){
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

    public function getLatestData($app, $params){
        $priceproviderid = $params['id'];
        $data = $this->app->priceProviderStore()->searchTable()->select()->where('priceproviderid', $priceproviderid )->orderBy('id','desc')->one();
        return json_encode([
            'success' => true,
            'data' => $data->toArray(),
        ]);
    }

    /**
     * Handle connecting to the PriceAdjustor engine to update the lastest adjustment settings.
     * @param  PriceAdjuster     $savedRec   Object that has been updated.
     * @param  array             $params     None.
     */
    public function onPostAddEditCallback($priceAdjustor, $params) {
        $priceProvider = $this->app->priceProviderStore()->getById($priceAdjustor->priceproviderid);
        $this->app->priceManager()->adjustPriceEngineDelayParameters($priceProvider, $priceAdjustor);
    }
}