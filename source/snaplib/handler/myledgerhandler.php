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
use Snap\object\MyLedger;
use Snap\InputException;
use Snap\object\account;
use Snap\object\MyAccountHolder;
use Snap\object\Partner;
use Snap\object\rebateConfig;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myledgerHandler extends CompositeHandler {

	
	function __construct(App $app) {
			
		$this->app = $app;

		$this->mapActionToRights('exportExcel', '/root/bmmb/promo/list;/root/go/promo/list;/root/one/promo/list;/root/onecall/promo/list;/root/air/promo/list;/root/mcash/promo/list;/root/toyyib/promo/list;/root/ktp/promo/list;/root/kopetro/promo/list;/root/kopttr/promo/list;/root/pkbaffi/promo/list;/root/bumira/promo/list;/root/nubex/promo/list;/root/hope/promo/list;/root/mbsb/promo/list;/root/red/promo/list;/root/kodimas/promo/list;/root/kgoldaffi/promo/list;/root/wavpay/promo/list;/root/koponas/promo/list;/root/noor/promo/list;/root/waqaf/promo/list;/root/kasih/promo/list;/root/posarrahnu/promo/list;/root/igold/promo/list;/root/bursa/promo/list;/root/bsn/promo/list;');
		$this->mapActionToRights('detailview', '/root/bmmb/promo/list;/root/go/promo/list;/root/one/promo/list;/root/onecall/promo/list;/root/air/promo/list;/root/mcash/promo/list;/root/toyyib/promo/list;/root/ktp/promo/list;/root/kopetro/promo/list;/root/kopttr/promo/list;/root/pkbaffi/promo/list;/root/bumira/promo/list;/root/nubex/promo/list;/root/hope/promo/list;/root/mbsb/promo/list;/root/red/promo/list;/root/kodimas/promo/list;/root/kgoldaffi/promo/list;/root/wavpay/promo/list;/root/koponas/promo/list;/root/noor/promo/list;/root/waqaf/promo/list;/root/kasih/promo/list;/root/posarrahnu/promo/list;/root/igold/promo/list;/root/bursa/promo/list;/root/bsn/promo/list;');
		$this->mapActionToRights('list', '/root/bmmb/promo/list;/root/go/promo/list;/root/one/promo/list;/root/onecall/promo/list;/root/air/promo/list;/root/mcash/promo/list;/root/toyyib/promo/list;/root/ktp/promo/list;/root/kopetro/promo/list;/root/kopttr/promo/list;/root/pkbaffi/promo/list;/root/bumira/promo/list;/root/nubex/promo/list;/root/hope/promo/list;/root/mbsb/promo/list;/root/red/promo/list;/root/kodimas/promo/list;/root/kgoldaffi/promo/list;/root/wavpay/promo/list;/root/koponas/promo/list;/root/noor/promo/list;/root/waqaf/promo/list;/root/kasih/promo/list;/root/posarrahnu/promo/list;/root/igold/promo/list;/root/bursa/promo/list;/root/bsn/promo/list;');
		$this->mapActionToRights('getMerchantList', '/root/bmmb/promo/list;/root/go/promo/list;/root/one/promo/list;/root/onecall/promo/list;/root/air/promo/list;/root/mcash/promo/list;/root/toyyib/promo/list;/root/ktp/promo/list;/root/kopetro/promo/list;/root/kopttr/promo/list;/root/pkbaffi/promo/list;/root/bumira/promo/list;/root/nubex/promo/list;/root/hope/promo/list;/root/mbsb/promo/list;/root/red/promo/list;/root/kodimas/promo/list;/root/kgoldaffi/promo/list;/root/wavpay/promo/list;/root/koponas/promo/list;/root/noor/promo/list;/root/waqaf/promo/list;/root/kasih/promo/list;/root/posarrahnu/promo/list;/root/igold/promo/list;/root/bursa/promo/list;/root/bsn/promo/list;');
		$this->mapActionToRights('uploadbulkpaymentresponse', '/root/bmmb/promo/list;/root/go/promo/list;/root/one/promo/list;/root/onecall/promo/list;/root/air/promo/list;/root/mcash/promo/list;/root/toyyib/promo/list;/root/ktp/promo/list;/root/kopetro/promo/list;/root/kopttr/promo/list;/root/pkbaffi/promo/list;/root/bumira/promo/list;/root/nubex/promo/list;/root/hope/promo/list;/root/mbsb/promo/list;/root/red/promo/list;/root/kgoldaffi/promo/list;/root/wavpay/promo/list;/root/koponas/promo/list;/root/noor/promo/list;/root/waqaf/promo/list;/root/kasih/promo/list;/root/posarrahnu/promo/list;/root/igold/promo/list;/root/bursa/promo/list;/root/bsn/promo/list;');
		
		$myledgerStore = $app->myledgerfactory();

		$this->currentStore = $myledgerStore;
		//parent::$children = [];
		$this->addChild(new ext6gridhandler($this, $myledgerStore, 1 ));

		// parent::__construct($app);
	}

	
	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		//$object = $app->orderfactory()->getById($params['id']);
		
		$object = $this->app->myledgerStore()->searchView()->select()
			->where('id', $params['id'])
			->one();


		$transactionOn = $object->transactiondate ? $object->transactiondate->format('Y-m-d H:i:s') : '0000-00-00 00:00:00';
		//$confirmedOn = $object->confirmon ? $object->confirmon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

		// if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		// else $modifieduser = 'System';
		// if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		// else $createduser = 'System';

		// Status
		if ($object->status == 0){
			$statusname = 'Inactive';
		}else if ($object->status == 1){
			$statusname = 'Active';
		}else if ($object->status == 2){
			$statusname = 'Failed';
		}else {
			$statusname = 'Unidentified';
		}
	
		$debit = number_format($object->debit,2);
		$credit = number_format($object->credit,2);
		$orderGoldPrice = number_format($object->ordgoldprice,3);
		$amountIn = number_format($object->amountin,2);
		$amountOut = number_format($object->amountout,2);
		
		$detailRecord['default'] = [ "ID" => $object->id,
									'Full Name' => $object->achfullname,
									'My Kad No' => $object->achmykadno,
									'Account Code' => $object->achaccountholdercode,
									'Type' => $object->type,
									'Type ID' => $object->typeid,
									'Partner' => $object->partnername,
									'Partner Code' => $object->partnercode,
									
									'Debit' => $debit,
									'Credit' => $credit,
									'Reference No' => $object->refno,
									'Order Gold Price' => $orderGoldPrice,
									'Amount In' => $amountIn,
									'Amount Out' => $amountOut,
									'Remarks' => $object->remarks,
                                    'Transaction On' => $transactionOn,

									'Status' => $statusname,
									// 'Created on' => $object->createdon->format('Y-m-d H:i:s'),
									// 'Created by' => $createduser,
									// 'Modified on' => $object->modifiedon->format('Y-m-d H:i:s'),
									// 'Modified by' => $modifieduser,
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
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
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
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
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
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
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
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		// End

		//$sqlHandle->andWhere('partnerid', $bmmbpartnerid);
		// Add filter to promo type only
		$sqlHandle->andWhere('type', MyLedger::TYPE_PROMO);
		$sqlHandle->andWhere('remarks', 'LIKE', '%TR%');

        return array($params, $sqlHandle, $fields);
    }

	function exportExcel($app, $params){
		
		try {
			//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

			// Start Query
			if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
				$modulename = 'BMMB_PROMO';
			}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
				$modulename = 'GO_PROMO';
			}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
				$modulename = 'ONECENT_PROMO';
			}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
				$modulename = 'ONECALL_PROMO';
			}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
				$modulename = 'AIR_PROMO';
			}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
				$modulename = 'MCASH_PROMO';
			}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
				$modulename = 'TOYYIB_PROMO';
			}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
				$modulename = 'NUBEX_PROMO';
			}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
				$modulename = 'HOPE_PROMO';
			}else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
				$modulename = 'MBSB_PROMO';
			}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
				$modulename = 'REDGOLD_PROMO';
			}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
				$modulename = 'WAVPAYGOLD_PROMO';
			}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
				$modulename = 'NOOR_PROMO';
			}else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
				$modulename = 'POSARRAHNU_PROMO';
			}else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
				$modulename = 'BSN_PROMO';
			}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
				$modulename = 'IGOLD_PROMO';
			}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
				$modulename = 'BURSA_PROMO';
			}
			//added on 13/12/2021
			else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'PITIH_PROMO';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('partnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPETRO_PROMO';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('partnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				$modulename = 'KOPTTR_PROMO';
				// $partnerId = [
				//  	 //$this->app->getConfig()->{'gtp.go.partner.id'},
				//  	 //$this->app->getConfig()->{'gtp.one.partner.id'}
				//  	$params['selected']
				// ];
				$partnerId = explode(",",$params['selected']);
				//$sqlHandle->andWhere('partnerid', 'IN', $ktpPartnerId_s);
			}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
				$modulename = 'PITIHAFFI_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
				$modulename = 'BUMIRA_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
				$modulename = 'KGOLD_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
				$modulename = 'KGOLDAFFI_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
				$modulename = 'KIGA_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
				$modulename = 'ANNURGOLD_PROMO';
				$partnerId = explode(",",$params['selected']);
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
				$modulename = 'KASIHGOLD_PROMO';
				$partnerId = explode(",",$params['selected']);
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
			$modulename = $modulename.'_PROMO';

			if (isset($params['partnercode']) && 'KTP' === $params['partnercode']){
			 	$conditions = ["partnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']){
			 	$conditions = ["partnerid", "IN", $partnerId];

			}else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
		   	}
			else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']){
				$conditions = ["partnerid", "IN", $partnerId];
			}
			else{
			 	$conditions = ['partnerid' => $partnerId];
			}
			//$conditions = ['partnerid' => $partnerId];
			$prefix = $this->currentStore->getColumnPrefix();
			foreach ($header as $key => $column) {

				// Overwrite index value with expression
				$original = $column->index;
				if ('status' === $column->index) {
					
					//add new status
					$header[$key]->index = $this->currentStore->searchTable(false)->raw(
						"CASE WHEN `{$prefix}status` = " . MyLedger::STATUS_INACTIVE . " THEN 'Inactive'
						 WHEN `{$prefix}status` = " . MyLedger::STATUS_ACTIVE . " THEN 'Active' END as `{$prefix}status`"
					);
					$header[$key]->index->original = $original;
				}
				// if ('isnotifyrecipient' === $column->index) {
                
				// 	$header[$key]->index = $this->currentStore->searchTable(false)->raw(
				// 		"CASE WHEN `{$prefix}isnotifyrecipient` = " . 0 . " THEN 'No'
				// 		 WHEN `{$prefix}isnotifyrecipient` = " . 1 . " THEN 'Yes' END as `{$prefix}isnotifyrecipient`"
				// 	);
				// 	$header[$key]->index->original = $original;
				// }
			}

			// Add custom conditions for:
			// 1) Type = 'promo'
			// 2) Ref no LIKE %TR%
			$conditions_2 = ['type' => MyLedger::TYPE_PROMO];
			$conditions_3 = ["remarks", "LIKE", '%TR%'];

			// Need new custom report to filter promo only
			$this->app->reportingManager()->generateCustomTransactionReport($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, $conditions_2, $conditions_3, '' );
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
}
