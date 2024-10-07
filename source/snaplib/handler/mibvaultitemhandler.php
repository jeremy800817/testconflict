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
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\rebateConfig;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mibvaultitemHandler extends vaultitemhandler {

	
	function __construct(App $app) {
		
		parent::__construct($app);
	}

	
	function onPreQueryListing($params,$sqlHandle, $records) {

		$app = App::getInstance();
        
        if (isset($params['partnercode']) && 'MIB' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerid);
        }
        else if (isset($params['partnercode']) && 'BURSA' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerid);
        }

        // Check for the partner of vault
        // $partnerid=$app->getConfig()->{'gtp.mib.partner.id'};   

        //$partnerid=$this->app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        }     
        // $sqlHandle->andWhere('partnerid', $partnerid);      
        return array($params, $sqlHandle, $records);   
	}
	
}
