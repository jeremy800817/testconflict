<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Calvin(calvin.thien@ace2u.com)
 * @version 1.0
 */
class priceproviderhandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'priceprovider');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

		$this->mapActionToRights('list', 'list');

        $this->mapActionToRights('fillform', 'add');
        $this->mapActionToRights('fillform', 'edit');

        $this->mapActionToRights('getPriceProviderStatus', 'list');
        $this->mapActionToRights('startPriceProvider', 'list');
        $this->mapActionToRights('stopPriceProvider', 'list');
        $this->mapActionToRights('startSpecificPriceProviderGroup', 'list');
        $this->mapActionToRights('stopSpecificPriceProviderGroup', 'list');

        $this->mapActionToRights('getPriceProviderGroup', 'list');
        
		$this->app = $app;

		$priceproviderStore = $app->priceproviderfactory();
		$this->addChild(new ext6gridhandler($this, $priceproviderStore, 1 ));
	}

    /**
	* function to populate selection data into form
	**/
	function fillform( $app, $params) {

      
        $object = $app->priceproviderFactory()->getById($params['id']);
        $pricesourcetagcodes= $this->app->tagFactory()->searchTable()->select()->execute();
        
        $pricesourcecode=array();
        foreach( $pricesourcetagcodes as $pricesourcetagcode) {
            if($pricesourcetagcode->category == 'PriceSource'){
                $pricesourcecode[]= array( 'id' => $pricesourcetagcode->id, 'name' => $pricesourcetagcode->code, 'category' => $pricesourcetagcode->category);
            }
            
        }
        //$pricesourcecode = \Snap\object\Tag::getCategory();
        //print_r($pricesourcecode);
		echo json_encode([
			'success' => true,
			'pricesourcecode' => $pricesourcecode,
			'relationship' => $relationship,
			'gender' => $gender,
			'nokgender' => $gender,
			'marital' => $marital,
			'ethnic' => $ethnic,
			'smoke' => $smoke,
			'picture' => $picture,
			'deadcause' => $deadcause,
			'deadloc' => $deadloc,
			'patienttype' => $patienttype,
			'cardiodoc' => $cardiodoc,
            'filetype' => $filetype,
            'type' => $announcementtype,
		]);
    }
    
    function getPriceProviderStatus($app, $params) {
		$permission = $app->hasPermission('/root/system/priceprovider');
		if($permission) {
            $priceproviderobj=$this->app->priceproviderStore()->getById($params['id']);
            $priceprovidermanager=$this->app->priceManager();
            $return = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);


            // Check if there is response
            if($return == 1){
                $isRunning = 1;
            }else {
                $isRunning = 0;
            }

			echo json_encode(array('success' => true, 'isrunning'=> $isRunning));
		}
		else {
			$response = array('success' => false, 'errmsg' => 'sorry, no permission');
			echo json_encode($response);
		}
	}

    function startPriceProvider($app, $params) {
        $permission = $app->hasPermission('/root/system/priceprovider');
        if($permission) {
            $priceproviderobj=$this->app->priceproviderStore()->getById($params['id']);
            //$priceprovidermanager=$this->app->priceManager();
            //$return = $priceprovidermanager->startPriceCollector($priceproviderobj);

           
            $priceprovidermanager = $this->app->priceManager();
            //$return = $priceprovidermanager->startPriceCollector($priceproviderobj);

            $checkstatus = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);

            // Check if it is currently running
            if($checkstatus == 1){
                $isRunningCheck = true;
                
            }else {
                
                $isRunningCheck = false;

                // From Controller.php
                // Runs job file snaplib/job/PriceCollectionJob.php with defined parameter
                //$app->startCLIJob("PriceCollectionJob.php", "startprovider=".$priceproviderobj->id);
                $app->startCLIJob(SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'job' . DIRECTORY_SEPARATOR . 'PriceCollectionJob.php', [ '_start_collector_real_' => $priceproviderobj->id]);

            }
            

            echo json_encode(array('success' => true, 'isrunning'=> $isRunningCheck));
        }
        else {
            $response = array('success' => false, 'errmsg' => 'sorry, no permission');
            echo json_encode($response);
        }
    }

    function stopPriceProvider($app, $params) {
        $permission = $app->hasPermission('/root/system/priceprovider');
        if($permission) {
            $priceproviderobj=$this->app->priceproviderStore()->getById($params['id']);
            $priceprovidermanager=$this->app->priceManager();

            
            $checkstatus = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);


            // Check if it is currently running
            if($checkstatus == 1){
                $isStoppedCheck = true;

                $return = $priceprovidermanager->stopPriceCollector($priceproviderobj);
                
            }else {
                $isStoppedCheck = false;
            }


           

            //$priceproviderobj->status = $status;
            
            //print_r($priceproviderobj->status);         
            //$savePriceProviderStatus = $this->app->priceProviderStore()->save($priceproviderobj);
            


            echo json_encode(array('success' => true, 'isstopped'=> $isStoppedCheck));
        }
        else {
            $response = array('success' => false, 'errmsg' => 'sorry, no permission');
            echo json_encode($response);
        }
    }

    
    function startSpecificPriceProviderGroup($app, $params) {
        $permission = $app->hasPermission('/root/system/priceprovider');
        if($permission) {
            // Get price provider group 
            // $priceprovidertag = $this->app->tagFactory()->searchTable()->select()->where('category', 'PriceProvider')->andWhere('value', $params['value'])->one();
            
            // $tagId = $priceprovidertag->id;
            $records = explode(",",$params['id']);
            // $priceproviders=$this->app->priceproviderStore()->searchTable()->select()->where('providergroupid',$tagId)->execute();
            $priceproviders=$this->app->priceproviderStore()->searchTable()->select()->whereIn('id', $records)->execute();
            $priceprovidermanager=$this->app->priceManager();

            // For loop 
            foreach($priceproviders as $priceprovider){
                $checkstatus = $priceprovidermanager->isPriceCollectorRunning($priceprovider);

                // Check if it is currently running
                if($checkstatus == 1){
                    $isRunningCheck = true;
                    
                }else {
                    
                    $isRunningCheck = false;
    
                    // From Controller.php
                    // Runs job file snaplib/job/PriceCollectionJob.php with defined parameter
                    //$app->startCLIJob("PriceCollectionJob.php", "startprovider=".$priceproviderobj->id);
                    $app->startCLIJob(SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'job' . DIRECTORY_SEPARATOR . 'PriceCollectionJob.php', [ '_start_collector_real_' => $priceprovider->id]);
    
                }
            }
           


           

            //$priceproviderobj->status = $status;
            
            //print_r($priceproviderobj->status);         
            //$savePriceProviderStatus = $this->app->priceProviderStore()->save($priceproviderobj);
            


            echo json_encode(array('success' => true, 'isrunning'=> $isRunningCheck));
        }
        else {
            $response = array('success' => false, 'errmsg' => 'sorry, no permission');
            echo json_encode($response);
        }
    }

    function stopSpecificPriceProviderGroup($app, $params) {
        $permission = $app->hasPermission('/root/system/priceprovider');
        if($permission) {
            // Get price provider group 
            // $priceprovidertag = $this->app->tagFactory()->searchTable()->select()->where('category', 'PriceProvider')->andWhere('value', $params['value'])->one();
            
            // $tagId = $priceprovidertag->id;
            $records = explode(",",$params['id']);
            // $priceproviders=$this->app->priceproviderStore()->searchTable()->select()->where('providergroupid',$tagId)->execute();
            $priceproviders=$this->app->priceproviderStore()->searchTable()->select()->whereIn('id', $records)->execute();
            $priceprovidermanager=$this->app->priceManager();

            // For loop 
            foreach($priceproviders as $priceprovider){
                $checkstatus = $priceprovidermanager->isPriceCollectorRunning($priceprovider);


                // Check if it is currently running
                if($checkstatus == 1){
                    $isStoppedCheck = true;
    
                    $return = $priceprovidermanager->stopPriceCollector($priceprovider);
                    
                }else {
                    $isStoppedCheck = false;
                }
            }
           


           

            //$priceproviderobj->status = $status;
            
            //print_r($priceproviderobj->status);         
            //$savePriceProviderStatus = $this->app->priceProviderStore()->save($priceproviderobj);
            


            echo json_encode(array('success' => true, 'isstopped'=> $isStoppedCheck));
        }
        else {
            $response = array('success' => false, 'errmsg' => 'sorry, no permission');
            echo json_encode($response);
        }
    }

    //get the price provider list based on groups
    function getPriceProviderGroup($app,$params){
        // Get price provider group 
        $priceprovidertag = $this->app->tagFactory()->searchTable()->select()->where('category', 'PriceProvider')->andWhere('value', $params['value'])->one();
    
        $tagId = $priceprovidertag->id;
        // $priceproviderobj=$this->app->priceproviderStore()->getById($params['id']);
        $priceproviders=$this->app->priceproviderStore()->searchView()->select()->where('providergroupid',$tagId)->execute();
        $priceprovidermanager=$this->app->priceManager();
        
        $providerdata=array();
        foreach ($priceproviders as $priceprovider){
            // check if its running
            $return = $priceprovidermanager->isPriceCollectorRunning($priceprovider);


            // Check if there is response
            if($return == 1){
                $isRunning = 1;
            }else {
                $isRunning = 0;
            }

             $arr = array('id'=>$priceprovider->id,'name'=>$priceprovider->name,'providergroupcode'=>$priceprovider->providergroupcode, 'status'=>$isRunning);
             array_push($providerdata,$arr);
        }
        //$providerdata = array(array('id'=>'2917155','name'=>'PKB'));
		return json_encode(array('success' => true, 'providerdata' => $providerdata));
	}

    	/**
	* function to massage data before listing
	**/
	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();
  
		foreach ($records as $key => $record) {
            
            $priceproviderobj=$this->app->priceproviderStore()->getById($records[$key]['id']);
            $priceprovidermanager=$this->app->priceManager();
            $return = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);


            // Check if there is response
            if($return == 1){
                $isRunning = 1;
            }else {
                $isRunning = 0;
            }

			//$records[$key]['isrunning'] = $priceprovidermanager->isPriceCollectorRunning($priceproviderobj);
            $records[$key]['isrunning'] = $isRunning;

		}

		return $records;
	}

    /*
    function restartPriceProvider($app, $params) {
        $permission = $app->hasPermission('/root/system/priceprovider');
        if($permission) {
            $priceproviderobj=$this->app->priceproviderStore()->getById($params['id']);
            $priceprovidermanager=$this->app->priceManager();
            $return = $priceprovidermanager->stopPriceCollector($priceproviderobj);


            echo json_encode(array('success' => true));
        }
        else {
            $response = array('success' => false, 'errmsg' => 'sorry, no permission');
            echo json_encode($response);
        }
    } */

/*
	public function onPreListing($objects, $params, $records)
    {
		/*
        foreach ($records as $key => $record) {
			$rekod[$key] = $record;

        }

		$rekod = App::getInstance()->tagFactory()->searchTable()->select()
                    ->order(desc, )
                    ->execute();
		$length = $records.length();
		print_r($length);
		foreach ($records as $key => $record) {
			$rekod[$key] = $record;

        }
        return $rekod;
    }*/

/*
	function onPreQueryListing($params, $sqlHandle, $fields) {

		$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');

		//$sqlHandle->andWhere('id', 1);

		// Today's date
		$dateToday = date('Y-m-d H:i:s');
		//$timeDateToday = strtotime($dateToday);
		// Date 1 week ago
		$dateWeek = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d")-5, date("Y")));
		//print_r($dateWeek);



		if(!$permission){
				 	$sqlHandle->andWhere('pricesourceon', '<=' ,$dateToday)
								->andWhere('pricesourceon', '>=' ,$dateWeek);
								//->andWhere('id', '<=', 8);
								//->andWhere('id', '<=', 8);

								// print_r($total);
		}

		return array($params, $sqlHandle, $fields);

	} */

}

?>
