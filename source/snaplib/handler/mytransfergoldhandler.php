<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

use Snap\api\exception\GeneralException;
Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\MyTransferGold;
use Snap\InputException;
use Snap\object\account;
use Snap\object\MyAccountHolder;
use Snap\object\Partner;
use Snap\object\rebateConfig;
use Throwable;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mytransfergoldHandler extends CompositeHandler {

	
	function __construct(App $app) {
			
		$this->app = $app;

		$this->mapActionToRights('exportExcel', '/root/bmmb/transfergold/list;/root/go/transfergold/list;/root/one/transfergold/list;/root/onecall/transfergold/list;/root/air/transfergold/list;/root/mcash/transfergold/list;/root/toyyib/transfergold/list;/root/ktp/transfergold/list;/root/kopetro/transfergold/list;/root/kopttr/transfergold/list;/root/pkbaffi/transfergold/list;/root/bumira/transfergold/list;/root/nubex/transfergold/list;/root/hope/transfergold/list;/root/mbsb/transfergold/list;/root/red/transfergold/list;/root/kodimas/transfergold/list;/root/kgoldaffi/transfergold/list;/root/wavpay/transfergold/list;/root/koponas/transfergold/list;/root/noor/transfergold/list;/root/waqaf/transfergold/list;/root/kasih/transfergold/list;/root/posarrahnu/transfergold/list;/root/igold/transfergold/list;/root/bursa/transfergold/list;/root/bsn/transfergold/list;');
		$this->mapActionToRights('detailview', '/root/bmmb/transfergold/list;/root/go/transfergold/list;/root/one/transfergold/list;/root/onecall/transfergold/list;/root/air/transfergold/list;/root/mcash/transfergold/list;/root/toyyib/transfergold/list;/root/ktp/transfergold/list;/root/kopetro/transfergold/list;/root/kopttr/transfergold/list;/root/pkbaffi/transfergold/list;/root/bumira/transfergold/list;/root/nubex/transfergold/list;/root/hope/transfergold/list;/root/mbsb/transfergold/list;/root/red/transfergold/list;/root/kodimas/transfergold/list;/root/kgoldaffi/transfergold/list;/root/wavpay/transfergold/list;/root/koponas/transfergold/list;/root/noor/transfergold/list;/root/waqaf/transfergold/list;/root/kasih/transfergold/list;/root/posarrahnu/transfergold/list;/root/igold/transfergold/list;/root/bursa/transfergold/list;/root/bsn/transfergold/list;');
		$this->mapActionToRights('list', '/root/bmmb/transfergold/list;/root/go/transfergold/list;/root/one/transfergold/list;/root/onecall/transfergold/list;/root/air/transfergold/list;/root/mcash/transfergold/list;/root/toyyib/transfergold/list;/root/ktp/transfergold/list;/root/kopetro/transfergold/list;/root/kopttr/transfergold/list;/root/pkbaffi/transfergold/list;/root/bumira/transfergold/list;/root/nubex/transfergold/list;/root/hope/transfergold/list;/root/mbsb/transfergold/list;/root/red/transfergold/list;/root/kodimas/transfergold/list;/root/kgoldaffi/transfergold/list;/root/wavpay/transfergold/list;/root/koponas/transfergold/list;/root/noor/transfergold/list;/root/waqaf/transfergold/list;/root/kasih/transfergold/list;/root/posarrahnu/transfergold/list;/root/igold/transfergold/list;/root/bursa/transfergold/list;/root/bsn/transfergold/list;');
		$this->mapActionToRights('getMerchantList', '/root/bmmb/transfergold/list;/root/go/transfergold/list;/root/one/transfergold/list;/root/onecall/transfergold/list;/root/air/transfergold/list;/root/mcash/transfergold/list;/root/toyyib/transfergold/list;/root/ktp/transfergold/list;/root/kopetro/transfergold/list;/root/kopttr/transfergold/list;/root/pkbaffi/transfergold/list;/root/bumira/transfergold/list;/root/nubex/transfergold/list;/root/hope/transfergold/list;/root/mbsb/transfergold/list;/root/red/transfergold/list;/root/kodimas/transfergold/list;/root/kgoldaffi/transfergold/list;/root/wavpay/transfergold/list;/root/koponas/transfergold/list;/root/noor/transfergold/list;/root/waqaf/transfergold/list;/root/kasih/transfergold/list;/root/posarrahnu/transfergold/list;/root/igold/transfergold/list;/root/bursa/transfergold/list;/root/bsn/transfergold/list;');
		$this->mapActionToRights('uploadbulkpaymentresponse', '/root/bmmb/transfergold/list;/root/go/transfergold/list;/root/one/transfergold/list;/root/onecall/transfergold/list;/root/air/transfergold/list;/root/mcash/transfergold/list;/root/toyyib/transfergold/list;/root/ktp/transfergold/list;/root/kopetro/transfergold/list;/root/kopttr/transfergold/list;/root/pkbaffi/transfergold/list;/root/bumira/transfergold/list;/root/nubex/transfergold/list;/root/hope/transfergold/list;/root/mbsb/transfergold/list;/root/red/transfergold/list;/root/kgoldaffi/transfergold/list;/root/wavpay/transfergold/list;/root/koponas/transfergold/list;/root/noor/transfergold/list;/root/waqaf/transfergold/list;/root/kasih/transfergold/list;/root/posarrahnu/transfergold/list;/root/igold/transfergold/list;/root/bursa/transfergold/list;/root/bsn/transfergold/list;');
		$this->mapActionToRights('doTransfer', '/all/access');
		$this->mapActionToRights('printAqad', '/all/access');
		$this->mapActionToRights('checkApprovalStatus', '/all/access');
		$this->mapActionToRights('approveTransfer', '/all/access');
		$this->mapActionToRights('rejectTransfer', '/all/access');
		$this->mapActionToRights('printReceipt','/all/access');
		
		$mytransfergoldStore = $app->mytransfergoldfactory();

		$this->currentStore = $mytransfergoldStore;
		//parent::$children = [];
		$this->addChild(new ext6gridhandler($this, $mytransfergoldStore, 1 ));

		// parent::__construct($app);
	}

	
	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		//$object = $app->orderfactory()->getById($params['id']);
		
		$object = $this->app->mytransfergoldStore()->searchView()->select()
			->where('id', $params['id'])
			->one();


		$isNotifyRecipient = "";

		if($object->isnotifyrecipient > 0) $isNotifyRecipient = "Yes";
		else $isSpot = 'No';

		$transferOn = $object->transferon ? $object->transferon->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
		//$confirmedOn = $object->confirmon ? $object->confirmon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		if($object->cancelon == '0000-00-00 00:00:00' || !$object->cancelon){
			$cancelOn = '0000-00-00 00:00:00';
		}else {
			$cancelOn = $object->cancelon->format('Y-m-d H:i:s');
		}

		if($object->expireon == '0000-00-00 00:00:00' || !$object->expireon){
			$expireOn = '0000-00-00 00:00:00';
		}else {
			$expireOn = $object->expireon->format('Y-m-d H:i:s');
		}

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Pending';
		}else if ($object->status == 1){
			$statusname = 'Success';
		}else if ($object->status == 2){
			$statusname = 'Failed';
		}else {
			$statusname = 'Unidentified';
		}
	
		$finalAcePrice = number_format($object->price,2);
		$weight = number_format($object->xau,3);
		$totalEstValue = number_format($object->amount,2);
		
		$detailRecord['default'] = [ //"ID" => $object->id,
								    'Partner' => $object->partnername,
									'Partner Code' => $object->partnercode,
                                    'Reference No' => $object->refno,
									'Type' => $object->type,

                                    'From' => $object->fromfullname,
                                    'To' => $object->tofullname,
                                   
                                    'From Account Code' => $object->fromaccountholdercode,
									'To Account Code' => $object->toaccountholdercode,

									
									'Contact' => $object->contact,
                                    'Notified Receipient' => $isNotifyRecipient,

                                    'Price' => $finalAcePrice,
                                    'Xau' => $weight,
									'Amount' => $totalEstValue,
                                    
                                    'Message' => $object->message,
									
                                    'Transfer On' => $transferOn,
                                    'Cancel On' => $cancelOn,
                                    'Expire On' => $expireOn,

									'Status' => $statusname,
									'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									'Modified by' => $modifieduser,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

	

		return $records;
	}

	function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();
		//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

		// Start Query
		if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('frompartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$sqlHandle->andWhere('frompartnerid', $partnerId);
		}
		//added on 13/12/2021
		else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('frompartnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			$partnerIdBSN = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerIdBSN)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();

			//check user state permission
            $userId = $this->app->getUserSession()->getUserId();
            $user = $this->app->userStore()->getById($userId);
            
            if($user->state){
				$partners = array();
            
                $partnerArr = $this->app->partnerStore()->searchTable()->select()
                    ->where('state', $user->state)
                    ->andWhere('group', $partnerIdBSN)
                    ->execute();
                
				$partners = $partnerArr;
            }

			if($partnerId != 0){
                $sqlHandle->andWhere('partnerid', $partnerId);
            }else{
				$groupPartnerIds = array();
				foreach ($partners as $partner){
					array_push($groupPartnerIds,$partner->id);
				}
				$sqlHandle->andWhere('partnerid', 'IN', $groupPartnerIds);
			}
            
			// $sqlHandle->andWhere('frompartnerid', $partnerId);
		}
		// End

		if(isset($params['filter'])){
			if(strtolower($params['filter']) == strtolower('approval')){
				$sqlHandle->andWhere('status', MyTransferGold::STATUS_REQUIREAPPROVAL);
			}
		}

		//$sqlHandle->andWhere('frompartnerid', $bmmbpartnerid);
  
        return array($params, $sqlHandle, $fields);
    }

	function exportExcel($app, $params){
		
		try {
			//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

			// Start Query
			if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
				$modulename = 'BMMB_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
				$modulename = 'GO_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
				$modulename = 'ONECENT_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
				$modulename = 'ONECALL_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
				$modulename = 'AIR_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
				$modulename = 'MCASH_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
				$modulename = 'TOYYIB_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
				$modulename = 'NUBEX_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
				$modulename = 'HOPE_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
				$modulename = 'MBSB_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
				$modulename = 'REDGOLD_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
				$modulename = 'WAVPAYGOLD_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
				$modulename = 'NOOR_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
				$modulename = 'POSARRAHNU_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
				$modulename = 'IGOLD_TRANSFERGOLD';
			}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
				$modulename = 'BURSA_TRANSFERGOLD';
			}
			//added on 13/12/2021
			else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'PITIH_TRANSFERGOLD';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('frompartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPETRO_TRANSFERGOLD';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('frompartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPTTR_TRANSFERGOLD';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('frompartnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
				$modulename = 'PITIHAFFI_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
				$modulename = 'BUMIRA_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
				$modulename = 'KGOLD_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
				$modulename = 'KGOLDAFFI_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
				$modulename = 'KIGA_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
				$modulename = 'ANNURGOLD_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
				$modulename = 'KASIHGOLD_TRANSFERGOLD';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']){
				$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'};
				$modulename = 'BSN_TRANSFERGOLD';
			}

			$header = json_decode($params["header"]);
			$dateRange = json_decode($params["daterange"]);
			$type = json_decode($params["type"]);
	
			$d1 = (new \DateTime($dateRange->startDate))->format('Ymd');
			$d2 = (new \DateTime($dateRange->endDate))->format('Ymd');

			// if ($d1 == $d2) {
			// 	$modulename = $modulename . '_' . $d1;
			// } else {
			// 	$modulename = $modulename . '_' . $d1 . '-' . $d2;
			// }
			$modulename = $modulename.'_TRANSFERGOLD';

			if (isset($params['partnercode']) && 'KTP' === $params['partnercode']){
			 	$conditions = ["frompartnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']){
			 	$conditions = ["frompartnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
		   	}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']){
				$conditions = ["frompartnerid", "IN", $partnerId];
			}else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']){
				 $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();

                                 $partnerId = array();
                                 foreach ($partners as $partner){
                                        array_push($partnerId,$partner->id);
			         }

                                 $conditions = ["partnerid", "IN", $partnerId];
			} 
			else{
			 	$conditions = ['frompartnerid' => $partnerId];
			}
			//$conditions = ['frompartnerid' => $partnerId];
			$prefix = $this->currentStore->getColumnPrefix();
			foreach ($header as $key => $column) {

				// Overwrite index value with expression
				$original = $column->index;
				if ('status' === $column->index) {
					
					//add new status
					$header[$key]->index = $this->currentStore->searchTable(false)->raw(
						"CASE WHEN `{$prefix}status` = " . MyTransferGold::STATUS_PENDING . " THEN 'Pending'
						 WHEN `{$prefix}status` = " . MyTransferGold::STATUS_SUCCESS . " THEN 'Sucess'
						 WHEN `{$prefix}status` = " . MyTransferGold::STATUS_REQUIREAPPROVAL . " THEN 'Require Approval'
						 WHEN `{$prefix}status` = " . MyTransferGold::STATUS_TIMEOUTAPPROVAL . " THEN 'Timeout Approval'
						 WHEN `{$prefix}status` = " . MyTransferGold::STATUS_REJECTAPPROVAL . " THEN 'Reject Approval'
						 WHEN `{$prefix}status` = " . MyTransferGold::STATUS_FAILED . " THEN 'Failed' END as `{$prefix}status`"
					);
					$header[$key]->index->original = $original;
				}
				if ('isnotifyrecipient' === $column->index) {
                
					$header[$key]->index = $this->currentStore->searchTable(false)->raw(
						"CASE WHEN `{$prefix}isnotifyrecipient` = " . 0 . " THEN 'No'
						 WHEN `{$prefix}isnotifyrecipient` = " . 1 . " THEN 'Yes' END as `{$prefix}isnotifyrecipient`"
					);
					$header[$key]->index->original = $original;
				}
			}
	
			$this->app->reportingManager()->generateTransactionReport($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, '' );
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
    }
	
	function getMerchantList($app,$params){
		//for now this is dummy data. The id is taken from existing partner
		// $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
		if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ??  $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ??  $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->andWhere('group','=', $partnerId)
            ->execute();;
        }
		elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
			->andWhere('group','=', $partnerId)
            ->execute();
        }
		elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
		// $partners = $app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
        $merchantdata=array();
        foreach ($partners as $partner){
             $arr = array('id'=>$partner->id,'name'=>$partner->name,'parent'=>$partner->parent);
             array_push($merchantdata,$arr);
        }
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}

	function doTransfer($app, $params){
		$response = ['success'=> false, 'message'=>'initializing...'];
		try{
			$senderid = $params['senderid'];
			$receiverid = $params['receiverid'];
			$amounttransfer = $params['amounttransfer'];
			$tellerremarks = $params['tellerremarks'];

			$partnerid = $this->getPartnerid($params);

			$partner = $app->partnerStore()->getByField('id',$partnerid);
			$product = $app->productStore()->getByField('code','DG-999-9'); //digital gold

			$fromAccountHolder = $app->myaccountholderStore()->getByField('id',$senderid);
			$toAccountHolder = $app->myaccountholderStore()->getByField('id', $receiverid);
			$transferXauAmount = $amounttransfer;

			$provider = $app->priceProviderStore()->getForPartnerByProduct($partner, $product);
			$priceManager = $app->priceManager();
				
			$latestPrice = $priceManager->getLatestSpotPrice($provider);


			$midprice= number_format((($latestPrice->companybuyppg + $latestPrice->companybuyppg)/2),2);

			$isawait = false;
			if($transferXauAmount > 30){
				$isawait = true;
				$message = 'Awaiting Approval';
				$tr = $app->mygtptransfergoldManager()->transferApprovalPartOne($fromAccountHolder, $toAccountHolder, 'xau', $transferXauAmount, $midprice, $tellerremarks);
			}
			else{
				$tr = $app->mygtptransfergoldManager()->transfer($fromAccountHolder, $toAccountHolder, 'xau', $transferXauAmount, $midprice, $tellerremarks);
				$message = 'Transfer Success';
			}

			$response['success'] = true;
			$response['isawait'] = $isawait;
			$response['message'] = $message;
			$response['id'] = $tr->id;
			$response['data'] = $tr->toArray();

		}
		catch(Throwable $e){
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		echo json_encode($response);
	}

	function getPartnerid($params){
		$partnerid = 0;

		if (isset($params['partner']) && 'BSN' == $params['partner']) {
			$partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
		}else if (isset($params['partner']) && 'ALRAJHI' == $params['partner']) {
			$partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
		}else if (isset($params['partner']) && 'POSARRAHNU' == $params['partner']) {
			$partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'}  ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
		}
		
		return $partnerid;
	}

	function printAqad($app, $params){
		$fromAccountHolder = $app->myaccountholderStore()->searchTable()->select()->where('id',$params['senderid'])->one();
		$toAccountHolder = $app->myaccountholderStore()->searchTable()->select()->where('id',$params['receiverid'])->one();
		if($params['teller']){
			$teller = $app->userStore()->searchTable()->select()->where('id',$params['teller'])->one();
		}else{
			$teller = $app->userStore()->searchTable()->select()->where('id',$app->getUsersession()->getUserId())->one();
		}
		if($teller->partnerid == 0){
			$branch = $app->partnerStore()->getByField('id',$this->app->getConfig()->{'otc.bsn.partner.id'});
		}else{
			$branch = $app->partnerStore()->getByField('id',$teller->partnerid);
		}

		if($params['transactiondate']){
			$dt = $params['transactiondate'];
		}else{
			$curr_date = new \DateTime("now", new \DateTimeZone("UTC"));
			$curr_date->setTimezone(new \DateTimeZone("Asia/Kuala_Lumpur"));
			$dt = $curr_date->format("Y-m-d H:i:s");
		}

		if (preg_match('/&/', $fromAccountHolder->fullname)) {
			$fromnewfullname = htmlentities($fromAccountHolder->fullname);
		}else{
			$fromnewfullname = $fromAccountHolder->fullname;
		}

		if (preg_match('/&/', $toAccountHolder->fullname)) {
			$tonewfullname = htmlentities($toAccountHolder->fullname);
		}else{
			$tonewfullname = $toAccountHolder->fullname;
		}

		$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/transfergold_confimation_template.docx');
		$templateProcessor->setValues([
			'fromFullname' => $fromnewfullname,
			'fromID' => $fromAccountHolder->mykadno,
			'fromJointApplicantName' => $fromAccountHolder->accounttype == 2 ? $fromAccountHolder->nokfullname: '-',
			'fromJointApplicantID' => $fromAccountHolder->accounttype == 2 ? $fromAccountHolder->nokmykadno: '-',
			'fromGiroAccount' => $fromAccountHolder->accountnumber,
			'fromMyGoldAccountNo' => $fromAccountHolder->accountholdercode,
			'referenceNo' => '',
			'toFullname' => $tonewfullname,
			'toJointApplicantName' => $toAccountHolder->accounttype == 2 ? $toAccountHolder->nokmykadno: '-',
			'toMyGoldAccountNo' => $toAccountHolder->accountholdercode,
			'xau' => number_format($params['xau'],3,'.',''),
			'date' => $dt,
			'branchname' => $branch->name,
			'tellerID' => $teller->username
		]);
		$filename = 'TransferGold'.'_Confirmation_'.$fromAccountHolder->mykadno.'.docx';

		$templateProcessor->saveAs('word/'.$filename);
		$fileUrl = 'word/'.$filename;
		
		//echo $fileUrl;
		$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
		$output = shell_exec($command);

		$pattern = '/pdf\/(.*?)\.pdf/';
		preg_match($pattern, $output, $matches);
		$pdfPath = $matches[1];

		echo 'pdf/'.$pdfPath.'.pdf';
		
	}

	function printReceipt($app,$params){
		$transfergold = $app->mytransfergoldStore()->searchTable()->select()->where('id',$params['id'])->one();

		$fromAccountHolder = $app->myaccountholderStore()->searchTable()->select()->where('id',$transfergold->fromaccountholderid)->one();
		$toAccountHolder = $app->myaccountholderStore()->searchTable()->select()->where('id',$transfergold->toaccountholderid)->one();
		$teller = $app->userStore()->searchTable()->select()->where('id',$app->getUsersession()->getUserId())->one();
		$branch = $app->partnerStore()->searchTable()->select()->where('id',$teller->partnerid);

		$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/transfergold_receipt_template.docx');
		$templateProcessor->setValues([
			'fromFullname' => $fromAccountHolder->fullname,
			'fromID' => $fromAccountHolder->mykadno,
			'fromJointApplicantName' => $fromAccountHolder->accounttype == 2 ? $fromAccountHolder->nokfullname: '-',
			'fromJointApplicantID' => $fromAccountHolder->accounttype == 2 ? $fromAccountHolder->nokmykadno: '-',
			'fromGiroAccount' => $fromAccountHolder->accountnumber,
			'fromMyGoldAccountNo' => $fromAccountHolder->accountholdercode,
			'referenceNo' => $transfergold->refno,
			'toFullname' => $toAccountHolder->fullname,
			'toJointApplicantName' => $toAccountHolder->accounttype == 2 ? $toAccountHolder->nokmykadno: '-',
			'toMyGoldAccountNo' => $toAccountHolder->accountholdercode,
			'xau' => number_format($transfergold->xau,3,'.',''),
			'date' => date('Y-m-d H:i:s'),
			'transactionDate' => $transfergold->createdon->format('Y-m-d H:i:s'),
			'branchname' => $branch->name,
			'tellerID' => $teller->username
		]);
		$filename = 'TransferGold'.'_Receipt_'.$fromAccountHolder->mykadno.'.docx';

		$templateProcessor->saveAs('word/'.$filename);
		$fileUrl = 'word/'.$filename;
		
		//echo $fileUrl;
		$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
                                        $pdfPath = $matches[1];

                                        echo 'pdf/'.$pdfPath.'.pdf';
	}

	public function checkApprovalStatus($app, $params){
		// this function is to check for records that have status::pending_approval
		
		$constStatus = MyTransferGold::STATUS_REQUIREAPPROVAL;
		try{
			if($params['id']){
				// search for gold trx record
				$transfergold = $this->app->mytransfergoldStore()->searchView()->select()
				->where('id', $params['id'])
				->one();
				$data = [];
				// if transaction found, check status
				$isPendingApproval = false;
				if($transfergold){
					$isPendingApproval = $constStatus == $transfergold->status ? true : false;
				}

				if(!$isPendingApproval){
					// check status if transaction is success or fail
					$statusString = $transfergold->getStatusString();
					//do status check and match
					//all status that identify as a successful transaction
					if(in_array($transfergold->status, [MyTransferGold::STATUS_SUCCESS])){
						$isTransferSuccessful = true;
						
					//all status that identify as a unsuccessful transaction
					}else if(in_array($transfergold->status, [MyTransferGold::STATUS_PENDING, MyTransferGold::STATUS_FAILED, MyTransferGold::STATUS_TIMEOUTAPPROVAL, MyTransferGold::STATUS_REJECTAPPROVAL])){
						$isTransferSuccessful = false;
					}
					$data = $transfergold->toArray();
				}
			}else{
				throw new \Exception("Please include search parameters");
			}


			echo json_encode(['success' => true, 'ispendingapproval' => $isPendingApproval, 'istransfersuccessful' => $isTransferSuccessful, 'status' => $transfergold->status, 'statusstring' => $statusString, 'record' => $data]);
		}catch (\Exception $e){
			$this->log("Failed to validate OTC Order Approval Status for goldtransaction ID : ". $params['id'], SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
		
	}

	public function approveTransfer($app, $params){
		$response = ['success' => false, 'message' => 'initialize'];
		try{
			$transfergold = $app->mytransfergoldStore()->searchTable()->select()->where('id',$params['id'])->one();
			if($transfergold){
				$mgr = $app->mygtptransfergoldManager()->transferApprovalPartTwo($transfergold, $params['remarks'], $params['approvalcode']);

				$response['success'] = true;
				$response['message'] = "OK";
			}
			else{
				Throw new \Exception(print_r($params));
			}
		}
		catch(\Throwable $e){
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		echo json_encode($response);
	}

	public function rejectTransfer($app, $params){
		$response = ['success' => false, 'message' => 'initialize'];
		try{
			$transfergold = $app->mytransfergoldStore()->searchTable()->select()->where('id',$params['id'])->one();
			if($transfergold){
				$checker = $app->userStore()->searchTable()->select()->where('id',$app->getUsersession()->getUserId())->one();
				
				$transfergold->status = MyTransferGold::STATUS_REJECTAPPROVAL;
				$transfergold->actionon = New \DateTime('now');
				$transfergold->checker = $checker->username;
				$transfergold = $app->mytransfergoldStore()->save($transfergold);

				$response['success'] = true;
				$response['message'] = "OK";
			}
			else{
				Throw new \Exception(print_r($params));
			}
		}
		catch(\Throwable $e){
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		echo json_encode($response);
	}

	// Perform check to filter records
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
