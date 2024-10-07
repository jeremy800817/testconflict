<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\InputException;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @version 1.0
 */
class commonvaultitemHandler extends vaultitemhandler {
	
	function __construct(App $app) {
		
		parent::__construct($app);
		//$this->mapActionToRights('list', '/root/common/vault;');
	}

	function onPreQueryListing($params,$sqlHandle, $records) {
		$app = App::getInstance();
		
		if($params['partnercode'] == "KTP") {
			$partnercode = $this->app->getConfig()->{'ktp.pkb.partner.id'};
			$shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group','=', $partnercode)->execute();
			$partnerId = array();
			foreach ($shareDgvPartners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('sharedgv', true)->andWhere('partnerid','IN',$partnerId);
		}
		else {
			$shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
			$sqlHandle->andWhere('sharedgv', true);
		}

		//$sqlHandle->andWhere('sharedgv', true);
        return array($params, $sqlHandle, $records);   
	}
	
}
