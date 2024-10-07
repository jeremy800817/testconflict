<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use Snap\IEntityStore;
Use Snap\IEntity;
Use Snap\App;
Use Snap\store\dbDatastore as dbDatastore;
Use Snap\object\SnapObject;
Use Snap\object\PriceAdjuster;
/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class priceAdjusterStore extends dbdatastore
{
    /**
     * Retrieves the price adjuster for a partner based on a given price provider.
     *
     * @param PriceProvider $priceProvider The price provider to retrieve the price adjuster for.
     *
     * @return array An array of PriceAdjuster objects for the partner.
     *
     * @throws \Snap\api\exception\PriceAdjusterNotFound If the price adjuster for the given partner and price provider is not found.
     */
    public function getForPartnerByPriceProvider($priceProvider)
    {
        $priceAdjusterTier = PriceAdjuster::getTier();
        $partnerPriceAdjuster = [];
        
        foreach ($priceAdjusterTier as $tier) {
        
            $priceProviderId = $priceProvider->id;
            $priceAdjustKey = (PriceAdjuster::TIER_NONPEAK == $tier) ? "{PriceAdjustNonPeak}:{$priceProviderId}" : "{PriceAdjustPeak}:{$priceProviderId}";
            $priceAdjustCache = $this->app->getCache($priceAdjustKey);
            
            if (empty($priceAdjustCache)) {
                $priceAdjuster = $this->searchTable()
                                ->select()
                                ->where('priceproviderid', $priceProviderId)
                                ->andWhere('tier', $tier)
                                ->orderBy('id', 'desc')
                                ->one();
                if ($priceAdjuster) {
                    array_push($partnerPriceAdjuster, $priceAdjuster);
                    if (preg_match('/(1|on|yes)/i', $this->app->getConfig()->{'otc.pricestream.adjust'})) {
                        $this->app->setCache($priceAdjustKey, $priceAdjuster->toCache());
                    }
                }                
            } else {
                array_push($partnerPriceAdjuster, $this->create()->fromCache($priceAdjustCache));
            }
        }

        if(0 == count($partnerPriceAdjuster)) {
            throw \Snap\api\exception\PriceAdjusterNotFound::fromTransaction($priceProvider, ['priceproviderid' => $priceProvider->id]);
        }
        
        return $partnerPriceAdjuster;
    }
}
?>