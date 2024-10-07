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

use Snap\object\VaultItem;
use Snap\object\VaultLocation;

use Spipu\Html2Pdf\Html2Pdf;
use Snap\api\exception\GeneralException;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myvaultitemHandler extends vaultitemhandler {

	
	function __construct(App $app) {

		parent::__construct($app);

        $this->mapActionToRights('doGoldBarList', '/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/nubex/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getStatusCount', '/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/common/vault/list;/root/mbb/vault/list;/root/ktp/vault/list;/root/nubex/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getVaultDataPartner', '/all/access');
        $this->mapActionToRights('VaultDataAPI', '/all/access');
        $this->mapActionToRights('getStatusCountAPI', '/all/access');
	}

		
	function onPreQueryListing($params,$sqlHandle, $records) {

        $app = App::getInstance();
        
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // Check for the partner of vault
        //$partnerid=$app->getConfig()->{'gtp.bmmb.partner.id'};   

        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
           
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
           
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.air.partner.id'};
           
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};
           
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.nubex.partner.id'};
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }
        //added on 13/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.ktp.partner.id'};
           
        }
        // OTC
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
        }
        // END OTC

        // ** Get vault locations **
        // ** Get vaultlocation for start
        $aceVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('type', 'Start')->execute();
        foreach($aceVaultLocations as $vaultLocation) {        
            $aceVaultLocationId =  intval($vaultLocation->id);
        }     
        // ** Get vaultlocation for end
        $bmmbVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('type', 'End')->andWhere('partnerid', $partnerid)->execute();
        foreach($bmmbVaultLocations as $vaultLocation) {        
            $bmmbVaultLocationId= intval($vaultLocation->id);
        }      
        
        // Get Trasnferring Status
        $status = \Snap\object\VaultItem::getTransferring();

        //$partnerid=$this->app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        }     
        
        if ($partnerid == $userPartnerId){
            $sqlHandle->andWhere('partnerid', $partnerid)
                        ->andWhere(function ($r) use ($aceVaultLocationId, $bmmbVaultLocationId, $status){

                            $r->where(function ($q) use ($aceVaultLocationId, $status){
                                // Filter by location ACE HQ only during transferring
                                $q->where('vaultlocationid', $aceVaultLocationId)
                                ->andWhere('status', $status);
                                  
                            })
                            ->orWhere(function ($q) use ($bmmbVaultLocationId){
                                // Filter location by BMMB
                                $q->where('vaultlocationid', $bmmbVaultLocationId);
                                  
                            });
                        });
                    
        }else {
            $sqlHandle->andWhere('partnerid', $partnerid);  
        }
        
          
        return array($params, $sqlHandle, $records);   
	}
	
    function doGoldBarList($app, $params)
    {
        $partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
        $partner = $app->partnerStore()->getById($partnerID);
        $partnerVault = $app->vaultlocationStore()->searchTable()->select()
                            ->where('partnerid', $partner->id)
                            ->andWhere('type', VaultLocation::TYPE_END)
                            ->andWhere('status', VaultLocation::STATUS_ACTIVE)
                            ->one();

        if (!$partnerVault) {
            throw GeneralException::fromTransaction([], ['message' => gettext("Unable to get partner vault")]);
        }
        $listing = $app->vaultitemStore()->searchTable()->select()->where('vaultlocationid', $partnerVault->id)
                        ->andWhere('status', 'in', [VaultItem::STATUS_ACTIVE, VaultItem::STATUS_TRANSFERRING, VaultItem::STATUS_PENDING_ALLOCATION])
                        ->execute();

        $resourceDir = SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'resource' ;
        $template = file_get_contents($resourceDir . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'barlist_bmmb.html');
        $template = str_replace('##IMAGE##', $resourceDir . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR .'header.jpg' ,$template);
        $template = str_replace('##DATE##', (new \DateTime('now', $app->getUserTimezone()))->format('l - M j, Y') ,$template);

         // Add Balance Calculation for display
        $return = $app->mygtptransactionManager()->getPartnerBalances($partner);

        // Start Calculate balance
        $vaultAmountBmmb = $return['vaultamount'];
        $totalCustomerHoldingBmmb = $return['totalcustomerholding'];
        $totalBalanceBmmb = $return['totalbalance'];

        $template = str_replace('##VAULTAMOUNTBMMB##', $vaultAmountBmmb, $template);
        $template = str_replace('##TOTALCUSTOMERHOLDINGBMMB##', $totalCustomerHoldingBmmb, $template);
        $template = str_replace('##TOTALBALANCEBMMB##', $totalBalanceBmmb, $template);
        // End Calculate Balance
        
        // Split into 10 per page
        $fp = array_splice($listing,0,10);
        $goldBarArr = array_chunk($listing, 15);
        array_unshift($goldBarArr, $fp);
        preg_match('/((?<=##FIRSTPAGE##)[\s\S]*?(?=##ENDFIRSTPAGE##))/', $template, $firstPageMatches);
        preg_match('/((?<=##ROW##)[\s\S]*?(?=##ENDROW##))/', $firstPageMatches[0], $matchRows);

        $replacements = '';
        $goldBars = array_shift($goldBarArr);
        foreach ($goldBars as $index => $bar) {
            $class = $index % 2 == true ? '' : 'even-row';
            $replacements .= str_replace(['##SERIALNO##', '##EVEN##'], [$bar->serialno, $class], $matchRows[0]);
        }
        $replacements = preg_replace('/##ROW##[\s\S]*##ENDROW##/', $replacements, $firstPageMatches[0]);

        $subsequentReplacements = '';
        preg_match('/((?<=##SUBSEQUENTPAGE##)[\s\S]*?(?=##ENDSUBSEQUENTPAGE##))/', $template, $subsequentPageMatches);
        foreach ($goldBarArr as $goldBars) {
            preg_match('/((?<=##ROW##)[\s\S]*?(?=##ENDROW##))/', $subsequentPageMatches[0], $matchRows);
            $subsequentReplacement = '';
            foreach ($goldBars as $index => $bar) {
                $class = $index % 2 == true ? '' : 'even-row';

                $subsequentReplacement .= str_replace(['##SERIALNO##', '##EVEN##'], [$bar->serialno, $class], $matchRows[0]);
            }
            $subsequentReplacements .= preg_replace('/##ROW##[\s\S]*##ENDROW##/', $subsequentReplacement, $subsequentPageMatches[0]);
        }

        $template = preg_replace('/##SUBSEQUENTPAGE##[\s\S]*##ENDSUBSEQUENTPAGE##/', $subsequentReplacements, $template);
        $template = preg_replace('/##FIRSTPAGE##[\s\S]*##ENDFIRSTPAGE##/', $replacements, $template);


        $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array(0,0,0,0));
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($template);

        // For $dest = S, $name is ignored, as we are returning string
        $content = $html2pdf->output('export.pdf', 'D');
       

    }

    public function getStatusCount($app, $params){
        // Please Ignore BMMB G4S naming, they should be the end destination
        // Will rename at another time 

        $userPartnerID = $this->app->getUserSession()->getUser()->partnerid;
        //$partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.go.partner.id'};
            
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.one.partner.id'};
           
        }
        //added on 13/12/2021
        else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.air.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'ktp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group','=', $partnerID)->execute();
            $partnerArray = array();
			foreach ($partners as $partner){
				array_push($partnerArray,$partner->id);
			}
        }
        // OTC
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.bsn.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
            
        }
        // END OTC
        else if (isset($params['partnercode']) && 'common' === $params['partnercode']) {
            // For common vault
            // No check 
            // Assign
            $partnerID = 'common';
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        }

        
        if ($partnerID == null) {
            throw new \Exception("Partner id does not exists");
        }

        // Check if common vault
        if(isset($partners)){
            // For common vault
            // Get Vault Location G4S
            $vaultLocationEnd = $this->app->vaultlocationStore()->searchView()->select()->where('type', 'Intermediate')->one();

            // Check if its has partner or common
            if($partnerArray){
                // KTP
                // Get DGV Items
                $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid','IN',$partnerArray)->andWhere('sharedgv', true)->andWhere('status', VaultItem::STATUS_ACTIVE)->andWhere('weight', 1000)->execute();
            }
            // Common vault main
            else{
                // Common
                // Get DGV Items
                $items = $this->app->vaultitemStore()->searchTable()->select()->where('sharedgv', true)->andWhere('status', VaultItem::STATUS_ACTIVE)->andWhere('weight', 1000)->execute();
            }
         
        }
        // For normal vault cases
        else{
            // For normal
            // Get Vault Location End Partner
            $vaultLocationEnd = $this->app->vaultlocationStore()->searchView()->select()->where('partnerid', $partnerID)->one();

            // Get Items
            $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerID)->where('weight', 1000)->execute();
        }
         // Get Vault Location Start
         $vaultLocationStart = $this->app->vaultlocationStore()->searchView()->select()->where('type', 'Start')->one();


        //Initialize Serial Number Counter
        $logicalSerialNumbers=array();
        $countInAceHQSerialNumbers=array();
        $countInBMMBg4sSerialNumbers=array();

     
        $logical = $countInAceHQ = $countInBMMBg4s = $total = $overall = 0;
        if (count($items) > 0) {
            foreach ($items as $aItem) {

                if ($vaultLocationStart->id == $aItem->vaultlocationid) {
                    $countInAceHQ++;
                    $countInAceHQSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"",  );
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                    $overallSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon !="0000-00-00 00:00:00"? $aItem->allocatedon->format('Y-m-d H:i:s'):"", );
                }
                // End Vault Location
                if ($vaultLocationEnd->id == $aItem->vaultlocationid) {
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

         if(isset($partners)){
             // For special vault
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
                
            ]);
     
        }else{
            // For normal vault 
            // Add balance
            $partner = $this->app->partnerStore()->getById($partnerID);
            $return = $this->app->mygtptransactionManager()->getPartnerBalances($partner);
            
            //Calculate balance
            $vaultAmount = $return['vaultamount'];
            $totalCustomerHolding = $return['totalcustomerholding'];
            $totalBalance = $return['totalbalance'];
            $pendingTransaction = $return['pendingtransaction'];

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
                'vaultAmount' => $vaultAmount,
                'totalCustomerHolding' => $totalCustomerHolding,
                'totalBalance' => $totalBalance,
                'pendingTransaction' => $pendingTransaction,
                
            ]);
           
        }
        

      
    }

    public function getStatusCountAPI($app, $params){

        $userPartnerID = $this->app->getUserSession()->getUser()->partnerid;

        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.go.partner.id'};
            
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.one.partner.id'};
           
        }
        else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.air.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'ktp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group','=', $partnerID)->execute();
            $partnerArray = array();
			foreach ($partners as $partner){
				array_push($partnerArray,$partner->id);
			}
        }
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.bsn.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerID = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
            
        }
        else if (isset($params['partnercode']) && 'common' === $params['partnercode']) {
            $partnerID = 'common';
            $partners = $this->app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        }

        if ($partnerID == null) {
            throw new \Exception("Partner id does not exists");
        }

        $partner= $this->app->partnerStore()->getById($partnerID);
        $partnercode = $partner->code;  
        $responseData = $this->VaultDataAPI($partnercode);

        if(isset($partners)){
            $vaultLocationEnd =  $responseData->vaultLocationEnd2;

            if($partnerArray){
                $items = $responseData->items2;
            }
            else{
                $items = $responseData->items3;
            }
        }
        else{
            $vaultLocationEnd = $responseData->vaultLocationEnd;
            $items = $responseData->items;
	}

        $vaultLocationStart = $responseData->vaultLocationStart;
        $logicalSerialNumbers=array();
        $countInAceHQSerialNumbers=array();
        $countInBMMBg4sSerialNumbers=array();

	//print_r($responseData);exit;
	$logical = $countInAceHQ = $countInBMMBg4s = $total = $overall = 0;
        if (count($items) > 0) {
            foreach ($items as $index => $aItem) {

                if ($vaultLocationStart[0]->id == $aItem->vaultlocationid) {
                    $countInAceHQ++;
                    $allocatedon = $aItem->allocatedon;
                    $countInAceHQSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'deliveryordernumber' => $aItem->deliveryordernumber,
                        'allocatedon' => $allocatedon !== "0000-00-00 00:00:00" ? date('Y-m-d H:i:s', strtotime($allocatedon)) : "",
                    );
                    $totalSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon !== "0000-00-00 00:00:00" ? date('Y-m-d H:i:s', strtotime($allocatedon)) : "",
                    );
                    $overallSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon !== "0000-00-00 00:00:00" ? date('Y-m-d H:i:s', strtotime($allocatedon)) : "",
                    );
                }
                // End Vault Location
		if ($vaultLocationEnd[0]->id == $aItem->vaultlocationid) {
                    $countInBMMBg4s++;
                    $allocatedon = $aItem->allocatedon;
                    $countInBMMBg4sSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'deliveryordernumber' => $aItem->deliveryordernumber,
                        'allocatedon' => $allocatedon->date != "0000-00-00 00:00:00" ? $allocatedon->date : "",
                    );
                    $totalSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon->date != "0000-00-00 00:00:00" ? $allocatedon->date : "",
                    );
                    $overallSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon->date != "0000-00-00 00:00:00" ? $allocatedon->date : "",
                    );
                }
                
                if (
                    $aItem->productid != null &&
                    $aItem->weight != null &&
                    $aItem->partnerid != null &&
                    $aItem->serialno != null &&
                    $aItem->deliveryordernumber == null &&
                    $aItem->allocated == 0 &&
                    ($aItem->allocatedon == '0000-00-00 00:00:00' || $aItem->allocatedon == null) &&
                    $aItem->status == 1
                ) {
                    $logical++;
                    $allocatedon = $aItem->allocatedon;
                    $logicalSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon != "0000-00-00 00:00:00" ? $allocatedon : "",
                    );
                    $overallSerialNumbers[] = array(
                        'id' => $aItem->id,
                        'name' => $aItem->serialno,
                        'allocatedon' => $allocatedon != "0000-00-00 00:00:00" ? $allocatedon : "",
                    );
                }
                
            }
            $total = $countInAceHQ + $countInBMMBg4s;
            $overall = $logical + $countInAceHQ + $countInBMMBg4s;
        }

        if(isset($partners)){
            // For special vault
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
            ]);
     
        }else{
            $partner = $this->app->partnerStore()->getById($partnerID);
            $return = $this->app->mygtptransactionManager()->getPartnerBalances($partner);

            $vaultAmount = $responseData->vaultamount;
            // $totalCustomerHolding = $responseData->totalcustomerholding;
            // $totalBalance = $responseData->totalbalance;
            $totalCustomerHolding =  $return['totalcustomerholding'];
            $totalBalance = round((float)str_replace(',', '', $vaultAmount) - (float)str_replace(',', '', $totalCustomerHolding), 3);
            $pendingTransaction = $responseData->pendingtransaction;

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
                'vaultAmount' => $vaultAmount,
                'totalCustomerHolding' => $totalCustomerHolding,
                'totalBalance' => $totalBalance,
                'pendingTransaction' => $pendingTransaction,
                
            ]);
           
        }              
    }

    public function VaultDataAPI($partnercode){

        try{
            $postdata = [
                "version" => "1.0my",
		        "merchant_id" =>$partnercode,
		        "projectbase" => $this->app->getConfig()->{'projectBase'},
                "action" => "vault_store_inventory"
            ];
        
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];
			
			$proxyUrl = $this->app->getConfig()->{'gtp.server.proxyurl'};
			if ($proxyUrl) {
				$options['proxy'] = $proxyUrl;
			}
        
            $client         = new \GuzzleHttp\Client($options);
            $url            = $this->app->getConfig()->{'mygtp.api.url'};
            // $url            = "http://localhost:8081/gtp/source/snapapp/mygtp.php";
	    $response       = $client->post($url, ['json' => $postdata]);

            return json_decode($response->getBody()->getContents());
        }
        catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }
    }

    public function getVaultDataPartner($app, $params)
    {   
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
           
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
           
        } else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.air.partner.id'};
           
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};
           
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.nubex.partner.id'};
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }
        //added on 13/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.ktp.partner.id'};
           
        }
        // OTC
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
        }
    
        $partner = $this->app->partnerStore()->getById($partnerid);
        $partnercode = $partner->code; 
    
        $responseData = $this->VaultDataAPI($partnercode);

        // print_r($responseData->partner_Gold);
        // exit;
    
        $vaultdata = [];

        foreach ($responseData->partner_Gold as $index => $data) {
            $allocatedon = $data->allocatedon;
            $createdon = $data->createdon;
            $modifiedon = $data->modifiedon;

            $return = [
                'id' => $data->id,
                'partnerid' => $data->partnerid,
                'vaultlocationid' => $data->vaultlocationid,
                'productid' => $data->productid,
                'weight' => $data->weight,
                'brand' => $data->brand,
                'serialno' => $data->serialno,
                'allocated' => $data->allocated,
                'allocatedon' =>  date('Y-m-d H:i:s', strtotime($allocatedon->date)),
                'utilised' => $data->utilised,
                'movetovaultlocationid' => $data->movetovaultlocationid,
                'moverequestedon' => date('Y-m-d H:i:s', strtotime($data->moverequestedon)),
                'movecompletedon' => date('Y-m-d H:i:s', strtotime($data->movecompletedon)),
                'returnedon' =>  date('Y-m-d H:i:s', strtotime($data->returnedon)),
                'newvaultlocationid' => $data->newvaultlocationid,
                'deliveryordernumber' => $data->deliveryordernumber,
                'sharedgv' => $data->sharedgv,
                'createdon' =>  date('Y-m-d H:i:s', strtotime($createdon->date)),
                'createdby' => $data->createdby,
                'modifiedon' =>  date('Y-m-d H:i:s', strtotime($modifiedon->date)),
                'modifiedby' => $data->modifiedby,
                'status' => $data->status,
                'vaultlocationname' => $data->vaultlocationname,
                'vaultlocationtype' => $data->vaultlocationtype,
                'vaultlocationdefault' => $data->vaultlocationdefault,
                'movetolocationpartnerid' => $data->movetolocationpartnerid,
                'movetovaultlocationname' => $data->movetovaultlocationname,
                'newvaultlocationname' => $data->newvaultlocationname,
                'partnername' => $data->partnername,
                'partnercode' => $data->partnercode,
                'productname' => $data->productname,
                'productcode' => $data->productcode,
                'createdbyname' => $data->createdbyname,
                'modifiedbyname' => $data->modifiedbyname                
            ];
            $vaultdata[] = $return;
        }
       
        return json_encode($vaultdata);
    } 

}
