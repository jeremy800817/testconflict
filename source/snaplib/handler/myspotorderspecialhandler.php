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
use Snap\object\Order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\Partner;
use Snap\object\rebateConfig;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myspotorderspecialHandler extends orderHandler {

	
	function __construct(App $app) {
		parent::__construct($app);

		$orderStore = $app->orderFactory();

		$this->currentStore = $orderStore;

		// $this->mapActionToRights('exportExcel', '/root/go/order/export');
		// $this->mapActionToRights('detailview', '/root/go/order/list');
		// $this->mapActionToRights('cancelOrder', '/root/go/order/cancel');
		
		$this->mapActionToRights('list', '/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/kasih/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/igold/goldtransaction/list;/root/bursa/goldtransaction/list;/root/bsn/goldtransaction/list;');
		$this->mapActionToRights("fillspecial", "/root/mbb/sale;/root/go/sale;/root/one/sale;/root/onecall/sale;/root/air/sale;/root/mcash/sale;/root/toyyib/goldtransaction/list;/root/nubex/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/kasih/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/igold/goldtransaction/list;/root/bursa/goldtransaction/list;/root/bsn/goldtransaction/list;");
		$this->mapActionToRights('exportExcel', "/root/go/sale;/root/one/sale;/root/onecall/sale;/root/air/sale;/root/mcash/sale;/root/toyyib/goldtransaction/list;/root/nubex/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/kasih/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/igold/goldtransaction/list;/root/bursa/goldtransaction/list;/root/bsn/goldtransaction/list;");

		$this->addChild(new ext6gridhandler($this, $orderStore, 1));
	}

	
	function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();

		// Start Query
		if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mib.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}
		else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
		}
		//added on 13/12/2021
		else if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KODIMAS' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KGOLDAFFI' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KOPONAS' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'WAQAF' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		else if (isset($params['partnercode']) && 'KASIH' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId)
			->andWhere('remarks', 'LIKE', 'Special order%');
        }
		//$gopayzpartnerid = $app->getConfig()->{'gtp.go.partner.id'};


  
        return array($params, $sqlHandle, $fields);
    }


	function onPreListing($objects, $params, $records) {

		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}

		return $records;
	}

	function fillspecial( $app, $params) {
            
                
		$productlists = $this->app->productStore()->searchTable()->select()->execute();
		//$product=array();

		//userType = $this->app->getUserSession()->getUser()->type;
		$userId = $this->app->getUserSession()->getUser()->id;
		$userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
		
		// PartnerId from params
		
		// Start PartnerID
		if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mib.partner.id'};

		}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};

		}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};

		}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};

		}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};

		}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};

		}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
		}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
		}
		else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
		}
		else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
		}
		else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
		}
		else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
		}
		else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};

		}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
		}
		else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
		}
		else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
		}
		else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
		}
		else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
			$partnerId =  $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
			$partnerId =  $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
			// $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			// $partnerId = array();
			// foreach ($partners as $partner){
			// 	array_push($partnerId,$partner->id);
			// }
		}
		/* Unused
		$userlists = $this->app->userStore()->searchTable()->select()
		->where(['partnerid' => $userPartnerId])
		->execute();
		*/

		// Start check price stream
		// Get Price Stream Status
		//$pricestream=$this->app->pricestreamStore()->getById($userPartnerId);
		$pricestreams=$this->app->pricestreamStore()->searchTable()->select()
					//->where(['createdby' => $params['patientid']])
					//->andwhere('status', \Snap\object\Station::STATUS_ACTIVE)
					->limit(1)
					->orderBy('id', desc)
					->execute();
					foreach ($pricestreams as $pricestream){
						$pricestream = $pricestream->createdon->format('Y-m-d h:i:s');
					 }
					$latest_pricestream_time = strtotime($pricestream);
					 
					// Do checking 
					$pricestream_time_difference = time() - $latest_pricestream_time;
					 
					// 
					if($pricestream_time_difference <= 60000){
						$status = 'online';
					} else{
						$status = 'offline';
					}
		// Do checking
		// End check price stream

		// ********************************************** Check and acquire vendor customer SAP codes ***************************************************************** //
			
		$apimanager=$this->app->apiManager();
		$requestvendor = array(
			'version' => '1.0',
			'action' => 'partnerlist',
			'option' => 'vendor',
			//'code' => 'VTK108',

		);
		
		$requestcustomer = array(
			'version' => '1.0',
			'action' => 'partnerlist',
			'option' => 'customer',
			//'code' => 'VTK108',

		);


		//$username = $app->getConfig()->{'gtp.sap.username'};
		//$password = $app->getConfig()->{'gtp.sap.password'};

		//print_r("=============");
		$apireturnvendor = $apimanager->sapBusinessList('1.0', $requestvendor);
		$apireturncustomer = $apimanager->sapBusinessList('1.0', $requestcustomer);
		//print_r($apireturn);
		//print_r("@@@@@@@@@@@@@@@@@@");
		//print_r($apireturn);
		//print_r($apireturn['ocrd'][1]['cardCode']);
		//print_r($apireturn['ocrd'][1]['cardType']);

		// Extract ocrd data out
		$apifilteredvendor = $apireturnvendor['ocrd'];
		$apifilteredcustomer = $apireturncustomer['ocrd'];
		//print_r($apifiltered[1]['cardCode']);
		//print_r($apifiltered);

		$apicodesvendor = array();
		$apicodescustomer = array();
		//$apicodesvendor[] = array("id" => 0, "name" => "Not required");
		//$apicodescustomer[] = array("id" => 0, "name" => "Not required");

		foreach ($apifilteredvendor as $key=>$result) {

				$codename = $apifilteredvendor[$key]['cardCode']." ".$apifilteredvendor[$key]['cardName'];
				//print_r($apifiltered[$key]['cardCode']);
				$apicodesvendor[] = array("id" => $apifilteredvendor[$key]['cardCode'], "name" => $apifilteredvendor[$key]['cardName']);


		}

		foreach ($apifilteredcustomer as $key=>$result) {

				$codename = $apifilteredcustomer[$key]['cardCode']." ".$apifilteredcustomer[$key]['cardName'];
				//print_r($apifiltered[$key]['cardCode']);
				$apicodescustomer[] = array("id" => $apifilteredcustomer[$key]['cardCode'], "name" => $apifilteredcustomer[$key]['cardName']);

		} 
		// *********************************************** End Checking ********************************************************************** //



		// Get SalespersonPartner List
		$salesmanpartnerlists = $this->app->partnerStore()->searchTable()->select()
		->where(['id' => $partnerId])
		->execute();
		foreach($salesmanpartnerlists as $salesmanpartnerlist) {        
	
			// $userlists = $this->app->userStore()->searchTable()->select()
			// ->where(['partnerid' => $salesmanpartnerlist->id])
			// ->execute();
			// foreach($userlists as $userlist) {
			// 	$users[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $salesmanpartnerlist->id, 'type' => $userlist->type, );
			// }
			

			$salesmanpartner[]= array( 'id' => $salesmanpartnerlist->id, 'name' => $salesmanpartnerlist->name);
			
		}     

		$partnerlists = $this->app->partnerStore()->searchTable()->select()
			//->where(['salespersonid' => $userId])
			->where(['id' => $partnerId])
			->execute();

		foreach($partnerlists as $partnerlist) {
			
			$filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid);
			
		}
		foreach($filteredpartner as $fpt){
			// if($fpt['saleid'] == $userId){
			// 	$customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode']);
			// }
			$customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode']);
		}

		// Get Product listing
		$partner=$this->app->partnerStore()->getById($userPartnerId);
		foreach($customer as $cust) {        
			//print_r($cust);
			//print_r("=="); 
			// Get Partner Service

			// Old Service record
			//$partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $cust['partnerid'])->execute();
			
			// New Service record
			$partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $cust['id'])->execute();
			if($partnerservices){
				foreach($partnerservices as $service){
					$servicerecords[] = $service->toArray();
					//print_r($servicerecords);
					//print_r("@@@@");
					// $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
				}
				
				$temp = array_unique(array_column($servicerecords, 'id'));
				$servicerecords = array_intersect_key($servicerecords, $temp);
				//print_r("==");
				//print_r($servicerecords);
			}

		}

	
		foreach($servicerecords as $servicerecord) {    

			$productobj = $this->app->productStore()->getById($servicerecord['productid']);

			// Check if product is digital gold
			// Only keep digital gold until further notice
			if($productobj->code == 'DG-999-9'){
				
				$productobj = $this->app->productStore()->getById($servicerecord['productid']);

				// Get The objects of individual records to acquire balance
				//$partnerofrecord = $this->app->partnerStore()->getById($servicerecord['partnerid']); 
				//$productofrecord = $this->app->productStore()->getById($servicerecord['productid']);
	
				$buyamount = $this->getTotalTransactionWeight($servicerecord['partnerid'], $servicerecord['productid'], 'CompanySell');
				$sellamount = $this->getTotalTransactionWeight($servicerecord['partnerid'], $servicerecord['productid'], 'CompanyBuy');
				
				$buybalance = $servicerecord['dailybuylimitxau'] - $buyamount;
				$sellbalance = $servicerecord['dailyselllimitxau'] - $sellamount;
	
				$product[]= array( 'id' => $servicerecord['productid'], 'name' => $productobj->name, 'partnerid' => $servicerecord['partnerid'], 'refineryfee' => $servicerecord['refineryfee'], 'premiumfee' => $servicerecord['premiumfee'], 'dailybuylimitxau' => $servicerecord['dailybuylimitxau'], 'dailyselllimitxau' => $servicerecord['dailyselllimitxau'], 
				'buybalance' => $buybalance, 'sellbalance' => $sellbalance, 'sellclickminxau' => $servicerecord['sellclickminxau'], 'sellclickmaxxau' => $servicerecord['sellclickmaxxau'], 'buyclickminxau' => $servicerecord['buyclickminxau'], 'buyclickmaxxau' => $servicerecord['buyclickmaxxau'],);
	
				// Get Refinery Fee/ Premium Fee
				$fees[]= array('id' =>$servicerecord['productid'], 'refineryfee' =>$servicerecord['refineryfee'], 'premiumfee' => $servicerecord['premiumfee']);
	
				// Get Product Permissions
				$permissions[]= array('id' =>$servicerecord['productid'], 'canbuy' =>$productobj->companycanbuy, 'cansell' =>$productobj->companycansell, 'byweight' =>$productobj->trxbyweight, 'bycurrency' =>$productobj->trxbycurrency,  'weight' =>$productobj->weight, 
				'partnerCanBuy' => $servicerecord['canbuy'],  'partnerCanSell' => $servicerecord['cansell'],  'partnerCanQueue' => $servicerecord['canqueue'],  'partnerCanRedeem' => $servicerecord['canredeem'], 'partnerid' => $servicerecord['partnerid'], );
			}

			
		} 
			

			/*
			// Get Refinery Fee/ Premium Fee
			foreach($productlists as $productlist) {        
				$productobj = $this->app->productStore()->getById($productlist->id);
				$fees[]= array('id' => $productlist->id, 'refineryfee' =>  0.00, 'premiumfee' =>  0.00);
			}     

			// Get Product Permissions
			foreach($productlists as $productlist) {        
				$productobj = $this->app->productStore()->getById($productlist->id);
				$permissions[]= array('id' =>$productlist->id, 'canbuy' =>$productlist->companycanbuy, 'cansell' =>$productlist->companycansell, 'byweight' =>$productlist->trxbyweight, 'bycurrency' =>$productlist->trxbycurrency,  'weight' =>$productlist->weight, 
				'partnerCanBuy' =>  0.00,  'partnerCanSell' =>  0.00,  'partnerCanQueue' => 0.00,  'partnerCanRedeem' => 0.00, );
			}   */ 

			 // Get Customer Listing Filtered by Customer TYPE
			 //$customerdailylimit[]= array( 'id' => 0, 'name' => '-', 'dailybuylimitxau' => 0, 'dailyselllimitxau' => 0, );
			 //$customer[]= array( 'id' => 0, 'name' => 'No Customer Data');
			
			
		
	
		
		echo json_encode([ 'success' => true, 'permissions' => $permissions ,'fees' =>$fees , 'items' => $product, 'customers' => $customer, 'customerdailylimit' => $customerdailylimit, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer, 'status' => $status]);
	}

	
	function exportExcel($app, $params){
		
		try {
			//$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

			// Start Query
			if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mib.partner.id'};
				$modulename = 'MIB_SPECIAL_TRADE';
			}if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
				$modulename = 'BMMB_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
				$modulename = 'GO_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
				$modulename = 'ONECENT_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
				$modulename = 'ONECALL_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
				$modulename = 'AIR_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
				$modulename = 'MCASH_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
				$modulename = 'TOYYIB_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
				$modulename = 'NUBEX_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
				$modulename = 'HOPE_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
				$modulename = 'MBSB_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
				$modulename = 'REDGOLD_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
				$modulename = 'WAVPAYGOLD_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
				$modulename = 'NOOR_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
				$modulename = 'POSARRAHNUGOLD_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
				$modulename = 'BSN_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
				$modulename = 'IGOLD_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
				$modulename = 'BURSA_SPECIAL_TRADE';
			}else if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
				// $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				// $sqlHandle->andWhere('partnerid', $partnerId);
				$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
				$modulename = 'PITIH_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
				$modulename = 'KOPETRO_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
				$modulename = 'KOPTTR_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
				$modulename = 'PITIHAFFI_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
				$modulename = 'BUMIRA_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KODIMAS' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
				$modulename = 'KGOLD_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KGOLDAFFI' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
				$modulename = 'KGOLDAFFI_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KOPONAS' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
				$modulename = 'KiGA_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'WAQAF' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
				$modulename = 'ANNURGOLD_SPECIAL_TRADE';
			}
			else if (isset($params['partnercode']) && 'KASIH' == $params['partnercode']) {
				$partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
				$modulename = 'KASIHGOLD_SPECIAL_TRADE';
			}
			
			$header = json_decode($params["header"]);
			$dateRange = json_decode($params["daterange"]);
			$type = json_decode($params["type"]);
	
			$d1 = (new \DateTime($dateRange->startDate))->format('Ymd');
			$d2 = (new \DateTime($dateRange->endDate))->format('Ymd');

			if ($d1 == $d2) {
				$modulename = $modulename . '_' . $d1;
			} else {
				$modulename = $modulename . '_' . $d1 . '-' . $d2;
			}
			//$modulename = 'BMMB_ORDER';
	
			// $conditions = ['ordpartnerid' => $partnerId];
			// Maybe not used
			$dateStart = $dateRange->startDate; 
			$dateEnd = $dateRange->endDate;
	
			$dateStart = new \DateTime($dateStart, $this->app->getUserTimezone()); 
			$dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone()); 
			$dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
			$startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
			$startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
			$endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
			$endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

			// End maybe not used ^

			$conditions = function ($q) use ($startAt, $endAt, $partnerId) {
				$q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
				$q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
				$q->andWhere('partnerid', $partnerId);
				$q->andWhere('remarks', 'LIKE', 'Special order%');
				
			};
			$prefix = $this->currentStore->getColumnPrefix();
			foreach ($header as $key => $column) {

				// Overwrite index value with expression
				$original = $column->index;
				if ('status' === $column->index) {
					
					$header[$key]->index = $this->currentStore->searchTable(false)->raw(
						"CASE WHEN `{$prefix}status` = " . Order::STATUS_PENDING . " THEN 'Pending'
						 WHEN `{$prefix}status` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
						 WHEN `{$prefix}status` = " . Order::STATUS_PENDINGPAYMENT . " THEN 'Pending Payment'
						 WHEN `{$prefix}status` = " . Order::STATUS_PENDINGCANCEL . " THEN 'Pending Cancel'
						 WHEN `{$prefix}status` = " . Order::STATUS_CANCELLED . " THEN 'Cancelled'
						 WHEN `{$prefix}status` = " . Order::STATUS_COMPLETED . " THEN 'Completed'
						 WHEN `{$prefix}status` = " . Order::STATUS_EXPIRED . " THEN 'Expired' END as `{$prefix}status`"
					);
					$header[$key]->index->original = $original;
				}
			}
	
			$this->app->reportingManager()->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null );
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
    }

	function getTotalTransactionWeight($partnerid, $productid, $transType){
		$now = new \DateTime;
		$now = \Snap\common::convertUTCToUserDatetime($now);
		$index = '{Orders_'.$partnerid.'_'.$transType.'_'.$now->format('Ymd').'}';
	
		//$total_amount = $this->app->getCache($index);
		//if (!$total_amount){
			$total_amount = $this->getTransactionWeightFromDB($partnerid, $productid, $transType);
			//$this->app->setCache($index, $total_amount, 86400 /* 1 day */);
		//}
		return $total_amount;
	}

	function getTransactionWeightFromDB($partnerid, $productid, $transType)
	{
		$now = new \DateTime;
		$now = \Snap\common::convertUTCToUserDatetime($now);
		$startAt = new \DateTime($now->format('Y-m-d 00:00:00'));
		$startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
		$endAt = new \DateTime($now->format('Y-m-d 23:59:59'));
		$endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

		// using value from VIEW , totalamount = amount+fee
		$total_spot_amount = $this->app->orderStore()->searchTable(false)->select()
			->addFieldSum('xau', 'total_amount')
			->where('partnerid', $partnerid)
			->where('productid', $productid)
			// ->andWhere('isspot', 1)
			->andWhere('type', $transType)
			->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
			->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
			->first();

		$total_queue_amount = $this->app->orderQueueStore()->searchTable(false)->select()
			->addFieldSum('xau', 'total_amount')
			->where('partnerid', $partnerid)
			->where('productid', $productid)
			->andWhere('ordertype', $transType)
			->andWhere('expireon', '>=', $startAt->format('Y-m-d H:i:s'))
			->first();

		$total_amount = ($total_spot_amount['total_amount'] + $total_queue_amount['total_amount']);
		return $total_amount;
	}
	
}
