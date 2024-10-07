<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
Use \Snap\object\Documents;
Use \Snap\object\VaultItemTrans;
/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @version 1.0
 */
class vaultitemtranshandler extends CompositeHandler {
	function __construct(App $app) {
		
        //parent::__construct('/root/mbb;/root/bmmb;/root/go;/root/one;', 'vault');
        
        $this->mapActionToRights('list', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getTrans', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/list;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('void', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/list;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit/root/bursa/vault/edit;/root/mbsb/vault/edit;;');
		$this->mapActionToRights('request', '/root/common/vault/request;/root/bmmb/vault/request;/root/go/vault/request;/root/one/vault/request;/root/onecall/vault/request;/root/air/vault/request;/root/mcash/vault/request;/root/bsn/vault/request;/root/alrajhi/vault/request;/root/posarrahnu/vault/request;/root/bursa/vault/request;/root/mbsb/vault/request;');
        $this->mapActionToRights('approve', '/root/common/vault/approve;/root/bmmb/vault/approve;/root/go/vault/approve;/root/one/vault/approve;/root/onecall/vault/approve;/root/air/vault/approve;/root/mcash/vault/approve;/root/bsn/vault/approve;/root/alrajhi/vault/approve;/root/posarrahnu/vault/approve;/root/bursa/vault/approve;/root/mbsb/vault/approve;');
        $this->mapActionToRights('complete', '/root/common/vault/complete;/root/bmmb/vault/complete;/root/go/vault/complete;/root/one/vault/complete;/root/onecall/vault/complete;/root/air/vault/complete;/root/mcash/vault/complete;/root/bsn/vault/complete;/root/alrajhi/vault/complete;/root/posarrahnu/vault/complete;/root/bursa/vault/complete;/root/mbsb/vault/complete;');

        $this->mapActionToRights('requestForTransferItem', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('requestForTransferItemMultiple', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('requestTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('cancelTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('confirmTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('returnToHq', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');      
        $this->mapActionToRights('returnItem', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('requestActivateItemForTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('approvePendingItemForTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('confirmReturn', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;/root/mbsb/vault/edit;');
        $this->mapActionToRights('detailview', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getSummary', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getLocation', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getTransferLocationsStart', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getTransferLocationsIntermediate', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getTransferLocationsEnd', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getTransferLocationsAll', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getStatusCount', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getPendingDocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('createdocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('getPrintDocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');
        $this->mapActionToRights('exportVaultList', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;/root/mbsb/vault/list;');

		
        $this->app = $app;
		$vaultitemtransStore = $app->vaultitemtransfactory();

        $this->currentStore = $vaultitemtransStore;
		$this->addChild(new ext6gridhandler($this, $vaultitemtransStore, 1 ));
    }
    
	function onPreQueryListing($params,$sqlHandle, $records) {

        $app = App::getInstance();
        
        // Start Query
		if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mib.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
            
        }else if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
            
        }else  if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }else  if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
           
        }
		else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
            
        }
		else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$sqlHandle->andWhere('partnerid', $partnerId);
            
        }
		//added on 10 Dec 2021
		else if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
			$partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
			$partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->andWhere('parent','=', '2')->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
		else if (isset($params['partnercode']) && 'common' == $params['partnercode']) {
			
			$shareDgvPartners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
			$partnerId = array();
			foreach ($shareDgvPartners as $partner){
				array_push($partnerId,$partner->id);
			}
			// Also earch for record where partnerid = 0 
			array_push($partnerId, 0);
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        
          
        return array($params, $sqlHandle, $records);   
	}

	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

		$userType = $this->app->getUserSession()->getUser()->type;

		foreach ($records as $key => $record) {
			// Acquire progress percentage.
			if(!$records[$key]['movetovaultlocationname']){
				$records[$key]['movetovaultlocationname'] = "-";
			}
            if(!$records[$key]['newvaultlocationname']){
				$records[$key]['newvaultlocationname'] = "-";
			}
            $child_obj = $this->app->vaultitemtransStore()->getById($record['id']);
            $child = $child_obj->getChild();
            // print_r($child);exit;
            $output_child = '';
            foreach ($child as $item){
                $output_child .= '<tr>';
                $output_child .= 	'<td style="text-align:center; width:200px">'.$item->serialno.'</td>';
                $output_child .= '</tr>';
            }
            $records[$key]['child'] = $output_child;
		}

		return $records;
	}

    function getTrans($app, $params){
        $data = $this->app->vaultitemtransStore()->searchTable()->select()->where('id', $params['id'])->one();
        if ($data){
            $return['success'] = true;
        }
        $return['data'] = $data->toArray();
        $return['data']['replacelist'] = '';
        return json_encode($return);
    }

    function void($app, $params){
		try{
			$vaultitemtrans = $this->app->vaultitemtransStore()->searchTable()->select()->where('id', $params['id'])->one();
			$void = $this->app->bankvaultManager()->voidTransaction($vaultitemtrans);
			if ($void){
				$return['success'] = true;
			}
		}catch(\Exception $e){
			$return['success'] = false;
			$return['errorMessage'] = $e->getMessage();
		}
        return json_encode($return);
    }

	function approve($app, $params){
		try{
			$vaultitemtrans = $this->app->vaultitemtransStore()->getById($params['id']);
			$action = 'confirm';
			$void = $this->app->bankvaultManager()->requestConfirmationTransfer($vaultitemtrans, $action, $checkPreset = true);
			if ($void){
				$return['success'] = true;
			}
		}catch(\Exception $e){
			$return['success'] = false;
			$return['errorMessage'] = $e->getMessage();
		}
        return json_encode($return);
    }

	function complete($app, $params){
		try{
			$vaultitemtrans = $this->app->vaultitemtransStore()->getById($params['id']);
			$action = 'complete';
			$void = $this->app->bankvaultManager()->requestConfirmationTransfer($vaultitemtrans, $action, $checkPreset = true);
			if ($void){
				$return['success'] = true;
			}
		}catch(\Exception $e){
			$return['success'] = false;
			$return['errorMessage'] = $e->getMessage();
		}
        return json_encode($return);
    }

    function requestForTransferItem($app,$params){    
        if($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')){  
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', [$params['serialno']])->execute();
            if($items){
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location = $locationStore->searchTable()->select()->where('id', $params['vaultto'])->one();                 
                try{
                    $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location);
					if ($return[0]->vaultlocationid != 3){
						// return new vaultlocationid as G4S
						// TO-DO - FIX , TEMP - GUI NOT SUPPORT NOW
                        $documents = $app->DocumentsManager()->createPendingDocuments($return[0], 'TransferNote', 'VaultItem');
                    }
                    echo json_encode([ 'success' => true]);  
                }catch(\Snap\api\exception\VaultItemError | \Exception $e){
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
                }             
            }else{
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);  
            } 
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']); 
        }  
    }

    function requestForTransferItemMultiple($app,$params){    
        if($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer')|| $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')){
            // Pending MIB 
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if($items){
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location = $locationStore->searchTable()->select()->where('id', $params['vaultto'])->one();                 
                try{
                    $return = $app->bankvaultManager()->requestMoveItemToLocationBmmb($items, $location);
					if ($return[0]->vaultlocationid != 4){
						// return new vaultlocationid as G4S
						// TO-DO - FIX , TEMP - GUI NOT SUPPORT NOW
                        $documents = $app->DocumentsManager()->createPendingDocuments($return[0], 'TransferNote', 'VaultItem');
                    }
                    echo json_encode([ 'success' => true]);  
                }catch(\Snap\api\exception\VaultItemError  $e){
                    // Handle exceptions
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
                }catch(\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e){
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);                   
                }catch(\Exception  $e){
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
                }               
            }else{
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);  
            } 
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']); 
        }  
    }

    function cancelTransfer($app,$params){    
        if($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')){                   
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if($items){
                try{
                    $return = $app->bankvaultManager()->cancelMoveRequest($items);    
                    echo json_encode(['success' => true]);   
                }catch(\Snap\api\exception\VaultItemError | \Exception $e){
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
                }                                
            }else{
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);  
            } 
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);        
        }         
    }

    public function getStatusCount($app, $params){
        
        $userPartnerID = $this->app->getUserSession()->getUser()->partnerid;
        $partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
        if ($partnerID == null) {
            throw new \Exception("Partner id does not exists");
        }

        //Initialize Serial Number Counter
        $logicalSerialNumbers=array();
        $countInAceHQSerialNumbers=array();
        $countInBMMBg4sSerialNumbers=array();

        $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerID)->where('weight', 1000)->execute();
        $logical = $countInAceHQ = $countInBMMBg4s = $total = $overall = 0;
        if (count($items) > 0) {
            foreach ($items as $aItem) {
                if (1 == $aItem->vaultlocationid) {
                    $countInAceHQ++;
                    $countInAceHQSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"",  );
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                    $overallSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                }

                if (4 == $aItem->vaultlocationid) {
                    $countInBMMBg4s++;
                    $countInBMMBg4sSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"",  );
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                    $overallSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                }

                if ($aItem->productid != null &&
                    $aItem->weight != null &&
                    $aItem->partnerid != null &&
                    $aItem->serialno != null &&
                    $aItem->deliveryordernumber == null &&
                    $aItem->allocated == 0 &&
                    ($aItem->allocatedon == '0000-00-00 00:00:00' || $aItem->allocatedon == null) &&
                    $aItem->status == 1) {
                    $logical++;
                    $logicalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                    $overallSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                };
            }
            $total = $countInAceHQ + $countInBMMBg4s;
            $overall = $logical + $countInAceHQ + $countInBMMBg4s;
        }

        $partner = $this->app->partnerStore()->getById($partnerID);
        $return = $this->app->mygtptransactionManager()->getPartnerBalances($partner);
        
        // Calculate balance
        $vaultAmountBmmb = $return['vaultamount'];
        $totalCustomerHoldingBmmb = $return['totalcustomerholding'];
        $totalBalanceBmmb = $return['totalbalance'];
        $pendingTransactionBmmb = $return['pendingtransaction'];

        echo json_encode([
            'success' => true,
            'logicalCount' => $logical,
            'hqCount' => $countInAceHQ,
            'bmmbG4Scount' => $countInBMMBg4s,
            'total' => $total,
            'overall' => $overall,
            'logicalCountSerialNumbers' => $logicalSerialNumbers,
            'hqCountSerialNumbers' => $countInAceHQSerialNumbers,
            'bmmbG4ScountSerialNumbers' => $countInBMMBg4sSerialNumbers,
            'totalSerialNumbers' => $totalSerialNumbers,
            'overallSerialNumbers' => $overallSerialNumbers,
            'userpartnerid' => $userPartnerID,
            'vaultAmountBmmb' => $vaultAmountBmmb,
            'totalCustomerHoldingBmmb' => $totalCustomerHoldingBmmb,
            'totalBalanceBmmb' => $totalBalanceBmmb,
            'pendingTransactionBmmb' => $pendingTransactionBmmb,
            
        ]);
    }

    function getPrintDocuments($app, $params){
		$vaultitemId = $params['id'];
        $document = $this->app->vaultitemtransStore()->searchTable()->select()->where('id', $vaultitemId)
			->andWhere('status', \Snap\object\VaultItemTrans::STATUS_ACTIVE)
			->one();
        
		if (!$document){
			throw new \Exception('Invalid document. Please create document first');
		}
       
		try{

            if ($document->type == 'TRANSFER' || $document->type == 'TRANSFERCONFIRMATION'){
                $documentTitle = 'Transfer Note';
                $documentHeader = 'Transfer Note';
            }
            if ($document->type == 'TransferNote'){
                $documentTitle = 'Transfer Note';
                $documentHeader = 'Transfer Note (Vault)';
            }
            if ($document->type == 'DeliveryNote'){
                $documentTitle = 'Deliver Note';
                $documentHeader = 'Deliver Note';
            }

			$child = $document->getChild();
            
            // if (!$vaultitem->vaultlocationid){
            //     $addressFrom = '
            //     No. 19-1, Jalan USJ 10/1D,
            //     47620 Subang Jaya, Selangor
            //     ';

            // }else{
            //     $addressFrom = $this->getVaultLocationAddress($vaultitem->vaultlocationid);
				
            // }

            // if (!$vaultitem->movetovaultlocationid){
            //     // $addressFrom = $this->getBranchAddress(14011);
            // }else{
            //     $addressTo = $this->getVaultLocationAddress($vaultitem->movetovaultlocationid);
            // }
            
            // Set from and to locations
            $vaultLocationstart = $this->app->vaultlocationStore()->getById($document->fromlocationid);
            $vaultLocationend = $this->app->vaultlocationStore()->getById($document->tolocationid);
            $fromLocation = $vaultLocationstart->name;
            $toLocation = $vaultLocationend->name;
            // Set Address
            // Cast to Int
            $addressFrom = $this->getVaultLocationAddress($document->fromlocationid);
            $addressTo = $this->getVaultLocationAddress($document->tolocationid);
            /*
			$addressFrom = '
			No. 19-1, Jalan USJ 10/1D,
			47620 Subang Jaya, Selangor
			';

			$addressTo = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
            */
            

			$lists = [];
			foreach ($child as $doc){
				$vaultitem = $this->app->VaultItemStore()->getById($doc->vaultitemid, array(), false, 1);
				$lists[] = [
					"productname" => $vaultitem->productname,
					"serialno" => $vaultitem->serialno,
					"weight" => $vaultitem->weight
				];
			}

			$noteDate = $document->documentdateon;
			$content = $this->documentHTML($document->partnerid, $document->documentno, $documentTitle, $addressFrom, $addressTo, $lists, $noteDate, $fromLocation, $toLocation, $documentHeader, $scheduledOn);	

			echo $content;
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
		
	}

	private function getBranchAddress($branchid){
		$branch = $this->app->partnerStore()->getRelatedStore('branches');;
		$branch = $branch->getByField('code', $branchid);

		$address = $branch->address.',';
		$address .= ($branch->postcode) ? $branch->postcode.',' : '';
		$address .= ($branch->city) ? $branch->city : '';
		return $address;
    }
    
    private function getVaultLocationAddress($vaultlocationid){
        if ($vaultlocationid == 1){
            //ACE HQ
            $address = 'No. 19-1, Jalan USJ 10/1D,
            47620 Subang Jaya, Selangor';
        }
        if ($vaultlocationid == 2){
            //ACE G4S
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }
        if ($vaultlocationid == 3){
            //MBB G4S
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }
        if ($vaultlocationid == 4){
            //BMMB G4S
            $address = '
			Bank Muamalat Malaysia Berhad,<br />
			1st Floor, Podium Block,	
			Menara Bumiputra, 21, Jalan Melaka, 50100 Kuala Lumpur, Wilayah Persekutuan';
        }
        if ($vaultlocationid == 5){
            //GO G4S ??
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }

		return $address;
    }


	private function documentHTML($partnerId, $typenumber, $documentTitle, $addressFrom, $addressTo, $lists, $noteDate, $fromLocation = null, $toLocation = null, $documentHeader = null, $scheduledOn = null){
		
		$noteNo = $typenumber;

        if(null == $fromLocation){
            $fromLocation = "";
        }
        if(null == $toLocation){
            $toLocation = "";
        }
        if(null == $documentHeader){
            $documentHeader = $documentTitle;
        }
        if(null == $scheduledOn || $scheduledOn == '0000-00-00 00:00:00' || !$scheduledOn){
			$scheduledOn = "-";
		}else {
            $scheduledOn = $scheduledOn->format('d-M-Y');
        }
		// "code":"GS-999-9-5g","serialnumber":"SN5-0032","weight":"5.000000"
		
		// Do check on partnerid 
		// If bmmb partner, display custom layout

		$conditionPartnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
		
		if($partnerId == $conditionPartnerId){
			// Do Custom Layout

			$html = '
			<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>ACEGTP: '.$documentTitle.'</title>
				</head>
				<body>
					
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:22pt;"><span style="font-family:Cambria;">ACE Capital Growth Sdn. Bhd.</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:18pt;"><span style="font-family:Cambria;">'.$documentHeader.'</span></p>
					<div style="text-align:center; ">
						<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto; border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
								<tr style="height:0.05pt;">
									<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Date created: </span>'.$noteDate->format('d-M-Y').'</p>
									</td>
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">'.$documentTitle.' No: '.$noteNo.'</span></p>
									</td>
								</tr>
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
								<tr style="height:0.05pt;">
									<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">From: '.$fromLocation.'</span></p>
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
											'.$addressFrom.'
										</p>
									</td>
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">To: '.$toLocation.'</span></p>
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
											'.$addressTo.'
										</p>
									</td>
								</tr>
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">No</span></p>
								</td>
								<td style="width:123.9pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Product Type</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Serial Number</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Quantity</span></p>
								</td>
							</tr>
	
							';
							foreach ($lists as $y => $list){
								$y++;
								$html .= '
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$y.'</span></p>
									</td>
									<td style="width:123.9pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list['productname'].'</span></p>
									</td>
									<td style="width:140.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list['serialno'].'</span></p>
									</td>
									<td style="width:95.65pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">1</span></p>
									</td>
								</tr>
							';
							}
							
							$html .= '
								
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
							<tbody>
								
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Stored By</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Received By</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Pick Up By</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
									</td>
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
									</td>
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
									</td>
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
									</td>
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
							</tbody>
						</table>
					</div>
	
				</body>
				</html>
			';
			// End BMMB Layout
			
		}else{
			// Do Default Layout

			$html = '
			<html lang="en">
				<head>
					<meta charset="UTF-8">
					<meta name="viewport" content="width=device-width, initial-scale=1.0">
					<title>ACEGTP: '.$documentTitle.'</title>
				</head>
				<body>
					
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:22pt;"><span style="font-family:Cambria;">ACE Capital Growth Sdn. Bhd.</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:18pt;"><span style="font-family:Cambria;">'.$documentHeader.'</span></p>
					<div style="text-align:center; ">
						<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto; border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
								<tr style="height:0.05pt;">
									<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Date created: </span>'.$noteDate->format('d-M-Y').'</p>
									</td>
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">'.$documentTitle.' No: '.$noteNo.'</span></p>
									</td>
								</tr>
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
								<tr style="height:0.05pt;">
									<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">From: '.$fromLocation.'</span></p>
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
											'.$addressFrom.'
										</p>
									</td>
									<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">To: '.$toLocation.'</span></p>
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
											'.$addressTo.'
										</p>
									</td>
								</tr>
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
							<tbody>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">No</span></p>
								</td>
								<td style="width:123.9pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Product Type</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Serial Number</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Quantity</span></p>
								</td>
							</tr>
	
							';
							foreach ($lists as $y => $list){
								$y++;
								$html .= '
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$y.'</span></p>
									</td>
									<td style="width:123.9pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list['productname'].'</span></p>
									</td>
									<td style="width:140.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">'.$list['serialno'].'</span></p>
									</td>
									<td style="width:95.65pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">1</span></p>
									</td>
								</tr>
							';
							}
							
							$html .= '
								
							</tbody>
						</table>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
						<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
							<tbody>
								
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Prepared By</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
								</tr>
								<!--<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
								</tr>-->
								<!--<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
								</tr>-->
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
								</tr>
								<!--<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
									</td>
									
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
									</td>
								</tr>-->
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
								<tr style="height:0.05pt;">
									<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
										<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
									</td>
									
								</tr>
							</tbody>
						</table>
					</div>
	
				</body>
				</html>
			';
		}

	

		return $html;
	}

	public function getPendingDocuments($app, $params){
		if (!$params['query']){
			$return = $this->app->DocumentsManager()->getPendingDocuments('TransferNote');
		}
		$return = $this->app->DocumentsManager()->getPendingDocuments($params['query']);
		foreach ($return as $x => $_return){
			// TEMP
			if ($_return['moverequestedon'] != '0000-00-00 00:00:00'){
				$return[$x]['moverequestedondate'] = $_return['moverequestedon']->format('Y-m-d H:i:s');
			}
			if ($_return['movecompletedon'] != '0000-00-00 00:00:00'){
				$return[$x]['movecompletedondate'] = $_return['movecompletedon']->format('Y-m-d H:i:s');
			}
		}
		echo json_encode($return);
	}

	public function createDocuments($app, $params){

        try{
            
            $data = json_decode($params['data']);
            // $params["po"]; // vaultitem(s) id
            // $params["type"]; // document->type
    
            $entities = [];
            foreach ($data->po as $tran){
                $entities[] = $this->app->documentsStore()->searchTable()->select()
                ->where('transactionid', $tran->id)
                ->andWhere('transactiontype', 'VaultItem')
                ->andWhere('type', $data->type)
                ->andWhere('status', 1)
                ->one();
            }
    
            $return = $this->app->DocumentsManager()->createDocuments($entities, $data->type, 'VaultItem', $params['scheduledate'] ?? null);
            
            if ($return){
                // save date to document manager
                // if there is data, save scheduled date 
                // and timestamp for print
                echo json_encode(array('success' => true, 'id' => $return->id));
            }
        }catch(\Snap\api\exception\VaultItemError | \Exception $e){
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
        }      
		
	}

    function exportVaultList($app, $params){
		
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_VAULT';
            
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_VAULT';
            
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_VAULT';
           
        }else if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $modulename = 'MIB_VAULT';
           
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $modulename = 'BSN_VAULT';
           
        }else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            $modulename = 'ALRAJHI_VAULT';
           
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POSARRAHNU_VAULT';
           
        }else  if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.mbsb.partner.id'} ?? $this->app->getConfig()->{'gtp.mbsb.partner.id'};
			$modulename = 'MBSB_VAULT';
           
        }else  if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bursa.partner.id'} ?? $this->app->getConfig()->{'gtp.bursa.partner.id'};
			$modulename = 'BURSA_VAULT';
           
        }else if (isset($params['partnercode']) && 'common' === $params['partnercode']) {
            $partnerid = 0;
            $modulename = 'COMMON_VAULT';
           
        }

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
		
		

		$conditions = ['partnerid' => $partnerid];

		$statusRenderer = [
			0 => "Pending",
			1 => "Active",
			2 => "Transferring",
			3 => "Inactive",
			4 => "Removed",
			5 => "Pending Allocation"
		];

        
        // Custom rendering based on module 
        // load formating decimal START
        if($this->currentStore->getTableName() == 'vaultitem') {
            $prefix = $this->currentStore->getColumnPrefix();
            foreach ($header as $x => $headerColumn){
                // Do custom formatting depending on module
                $original = $headerColumn->index; 
                
				if ('status' === $headerColumn->index) {
                
					$header[$x]->index = $this->currentStore->searchTable(false)->raw(
						"CASE WHEN `{$prefix}status` = " . VaultItemTrans::STATUS_CANCELLED . " THEN 'Pending Payment'
						 WHEN `{$prefix}status` = " . VaultItemTrans::STATUS_ACTIVE . " THEN 'Paid' END as `{$prefix}status`"
					);
					$header[$x]->index->original = $original;
				}
                if ('allocated' === $headerColumn->index) {
                    $header[$x]->index = $this->currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}allocated` = 0 THEN 'No'
                        WHEN `{$prefix}allocated` = 1 THEN 'Yes' END as `{$prefix}allocated`"
                    );
                    $header[$x]->index->original = $original;
                }
                
            }
        }
        

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    }
}

?>
