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
class gtplogisticHandler extends logisticHandler {
    
    function __construct(App $app) {
		//parent::__construct('/root/system', 'logistic');
		/*
		$this->mapActionToRights('fillform', '/root/gtp/logistic/add');

		$this->mapActionToRights('fillform', '/root/gtp/logistic/edit');

		$this->mapActionToRights('detailview', '/root/gtp/logistic/list');
        /*
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('addToOrder', 'add');
		$this->mapActionToRights('approveOrder', 'approve');
		$this->mapActionToRights('rejectOrder', 'approve');
		$this->mapActionToRights('deliverOrder', 'edit');
		$this->mapActionToRights('completedOrders', 'edit'); 

		$this->mapActionToRights('list', '/root/gtp/logistic/list');

		$this->mapActionToRights('updateLogisticStatus', '/root/gtp/logistic/add');

		$this->mapActionToRights('updateLogisticInformation', '/root/gtp/logistic/add');

		$this->mapActionToRights('updateLogisticAttempts', '/root/gtp/logistic/add');
															
		$this->mapActionToRights('updateAceSalesmanToDelivery', '/root/gtp/logistic/add');

		
		$this->app = $app;

		$logisticStore = $app->logisticFactory();
		$this->addChild(new ext6gridhandler($this, $logisticStore, 1));
		*/

		parent::__construct($app);

	}
    
   
	function onPreQueryListing($params, $sqlHandle, $fields){

		$app = App::getInstance();
		$mbbpartnerid = $app->getConfig()->{'gtp.mib.partner.id'};

        $userType = $this->app->getUserSession()->getUser()->type;
        $userid = $this->app->getUserSession()->getUser()->id;
		$user=$this->app->userStore()->getById($userid);

		$userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // At the moment only sales and operator is involved
        if($user->isOperator()){
            $sqlHandle->andWhere('partnerid', '!=' , $mbbpartnerid);
        } else if($user->isSale()){
            $sqlHandle->andWhere('senderid', $userid)
                      ->andWhere('partnerid', '!=' , $mbbpartnerid);
        } else {
			// filter by customer own partnerid
			//$sqlHandle->andWhere('partnerid', $userPartnerId);
			exit;
        }
		//$sqlHandle->andWhere('partnerid', '!=' , 1);
  
        return array($params, $sqlHandle, $fields);
	}

}
