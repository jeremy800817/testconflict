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
use Snap\object\Partner;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mylogisticHandler extends logisticHandler {
	
	function __construct($app) {
		/*
		//parent::__construct('/root/system', 'logistic');
		$this->mapActionToRights('fillform', '/root/mbb/logistic/add');

		$this->mapActionToRights('fillform', '/root/mbb/logistic/edit');

		$this->mapActionToRights('detailview', '/root/mbb/logistic/list');
        /*
		$this->mapActionToRights('fillform', 'add');
		$this->mapActionToRights('fillform', 'edit');
		$this->mapActionToRights('addToOrder', 'add');
		$this->mapActionToRights('approveOrder', 'approve');
		$this->mapActionToRights('rejectOrder', 'approve');
		$this->mapActionToRights('deliverOrder', 'edit');
		$this->mapActionToRights('completedOrders', 'edit'); 

		$this->mapActionToRights('list', '/root/mbb/logistic/list');

		$this->mapActionToRights('updateLogisticStatus', '/root/mbb/logistic/add');

		$this->mapActionToRights('updateLogisticInformation', '/root/mbb/logistic/add');

		$this->mapActionToRights('updateLogisticAttempts', '/root/mbb/logistic/add');
															
		$this->mapActionToRights('updateAceSalesmanToDelivery', '/root/mbb/logistic/add');
		

		$this->app = $app;

		$logisticStore = $app->logisticFactory();
		$this->addChild(new ext6gridhandler($this, $logisticStore, 1));
		*/

		parent::__construct($app);

    }
    
    function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();

		if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
        	$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();

            $partnerIdUser = $this->getPartnerIdForBranch();

            if($partnerIdUser != 0){
                $sqlHandle->andWhere('partnerid', $partnerIdUser);
            }else{
              
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }
                $sqlHandle->andWhere('partnerid', 'IN', $groupPartnerIds);
            }
        }

  
        return array($params, $sqlHandle, $fields);
    }

	private function getPartnerIdForBranch() {
        $app = App::getInstance();
        $userId = $app->getUserSession()->getUserId();
        $user = $app->userStore()->getById($userId);
        if($user->partnerid > 0 ){
            $partnerid = $user->partnerid;
        }else{
            $partnerid = 0;
        }     
        return $partnerid;
    }

}
