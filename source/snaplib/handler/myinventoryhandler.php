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
class myinventoryHandler extends vaultitemhandler {

	
	function __construct(App $app) {
		
		parent::__construct($app);
	}

	function getSummary($app,$params){
		print_r("adada");
        $items = $this->app->vaultitemStore()->searchTable()->select()->execute();
        //$withdocount=$withoutdocount=$transferringcount=$returncount=0;
        $countInAceHq=$countInAceG4s=$countInMbbG4s=$total=0;

        //Initialize Serial Number Counter
        $withDoSerialNumbers=array();
        $withoutDoSerialNumbers=array();
        $transferringSerialNumbers=array();

        if (count($items) > 0){
            foreach($items as $aItem){     
                if($aItem->vaultlocationid){$withDoCount++; $withDoSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'),  );};
                if(!$aItem->vaultlocationid){$withoutDoCount++; $withoutDoSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );};
                //if($aItem->allocated == 1){$withdocount++;}
                //if($aItem->status == 1 && $aItem->allocated==1){$withoutdocount++;};
                if(2 == $aItem->status && 1 == $aItem->allocated){$transferringCount++; 
                    
                    if(0 == $aItem->vaultlocationid){
                        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();
                        foreach($vaultLocations as $vaultLocation) {        
                            $locations[]= array( 'id' => $vaultLocation->id, 'name' => $vaultLocation->name, 'type' => $vaultLocation->type );
                            $from = $vaultLocation->name;
                        }   

                    }else{
                        $fromVaultLocation = $this->app->vaultlocationStore()->getById($aItem->vaultlocationid);
                        $from =  $fromVaultLocation->name;
                    }

                    $toVaultLocation = $this->app->vaultlocationStore()->getById($aItem->movetovaultlocationid);
                    
                    $transferringSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'from' => $from, 'to' => $toVaultLocation->name, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'),); };
                //if(($aItem->status == 3 || $aItem->status == 4) && $aItem->allocated==0){$returncount++;};	 

                if(1 == $aItem->vaultlocationid){$countInAceHq++; 
                    $aceHqSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), ); 
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );
                };
                if(2 == $aItem->vaultlocationid){$countInAceG4s++; 
                    $aceG4sSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );
                };
                if(3 == $aItem->vaultlocationid){$countInMbbG4s++; 
                    $mbbG4sSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );
                    $totalSerialNumbers[]= array( 'id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon->format('Y-m-d h:i:s'), );
                };

            }
            $total=$countInAceHq+$countInAceG4s+$countInMbbG4s;
        }      
        echo json_encode([ 'success' => true,
                        'withdocount' => $withDoCount,
                        'withoutdocount' => $withoutDoCount,
                        'transferringcount' => $transferringCount,
                        //'returncount'=> $returnCount,
                        'hqcount' => $countInAceHq,
                        'aceg4scount' => $countInAceG4s,
                        'mbbg4scount' => $countInMbbG4s,
                        'total'=> $total,
                        'withdoserialnumbers'=> $withDoSerialNumbers,
                        'withoutdoserialnumbers'=> $withoutDoSerialNumbers,
                        'transferringserialnumbers' => $transferringSerialNumbers,
                        'acehqserialnumbers' => $aceHqSerialNumbers,
                        'aceg4sserialnumbers' => $aceG4sSerialNumbers,
                        'mbbg4sserialnumbers' => $mbbG4sSerialNumbers,
                        'totalserialnumbers' => $totalSerialNumbers,
                        ]);  
    }

	public function getTransferLocationsStart(){    	
        //$vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
        $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->execute();
        $locations=array();
        foreach($vaultLocations as $vaultLocation) {        
            $locations[]= array( 'id' => $vaultLocation->id, 'name' => $vaultLocation->name);
        }      
        foreach($moreVaultLocations as $moreVaultLocation) {        
            $locations[]= array( 'id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
        }    
		echo json_encode(array('locations'=>$locations));    
    }   

	/*
    public function getTransferLocationsIntermediate(){    	
        $locations=array();
        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();
        $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->execute();
        
        foreach($vaultLocations as $vaultLocation) {        
            $locations[]= array( 'id' => $vaultLocation->id, 'name' => $vaultLocation->name, 'type' => $vaultLocation->type );
        }   
        foreach($moreVaultLocations as $moreVaultLocation) {        
            $locations[]= array( 'id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name, 'type' => $moreVaultLocation->type);
        }       
		echo json_encode(array('locations'=>$locations));    
    }*/
    
    public function getTransferLocationsEnd(){    	
        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->andWhere('stl_type', 'Intermediate')->execute();
        //$moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
        $locations=array();
        foreach($vaultLocations as $vaultLocation) {        
            $locations[]= array( 'id' => $vaultLocation->id, 'name' => $vaultLocation->name);
        }   
        foreach($moreVaultLocations as $moreVaultLocation) {        
            $locations[]= array( 'id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
        }       
		echo json_encode(array('locations'=>$locations));    
	}  


	/*
	function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();
		$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

		$sqlHandle->andWhere('partnerid', $bmmbpartnerid);
  
        return array($params, $sqlHandle, $fields);
    }
*/
	
}
