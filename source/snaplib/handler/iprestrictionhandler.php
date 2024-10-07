<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Dianah(dianah@silverstream.my)
 * @version 1.0
 */
class iprestrictionHandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'ip');
//$this->mapActionToRights('test', 'list');
		$currentStore = $app->iprestrictionFactory();

        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore));
        } else {
            $this->addChild(new ext6gridhandler($this, $currentStore));
        }
	}

/*	function test ($app, $params)
        {
          $otcPricingModel = $app->otcpricingmodelStore()->getById(1);
          $basePrice = '299.38';
          $type = 'CompanySell';

          $price = $app->priceManager()->getOtcPricingModelOriginalPrice($otcPricingModel, $basePrice, $type);

          echo 'price = ' . $price . '<br/>';

}*/

}
