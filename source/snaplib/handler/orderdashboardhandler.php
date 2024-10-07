<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\partner;
use Snap\InputException;
/**
 *
 * @author Shahanas <shahanas@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class orderdashboardHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        //parent::__construct('/root/trading/gtp', 'order');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("edit", "edit");
        $this->mapActionToRights("delete", "delete");
        $this->mapActionToRights("freeze", "freeze");
        $this->mapActionToRights("unfreeze", "unfreeze");
        $this->mapActionToRights("isunique", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "/root/gtp/cust");
        $this->mapActionToRights("fillspecial", "/root/gtp/sale");
        $this->mapActionToRights("fillunfulfilled", "/root/gtp/unfulfilledorder");
        $this->mapActionToRights("getFormDetails", "edit");
        $this->mapActionToRights("getFormDetails", "add");
        $this->mapActionToRights("initDailyLimit", "/root/gtp/limits");
        //$this->mapActionToRights("getPODetail", "/root/gtp/unfulfilledorder/edit");
        //$this->mapActionToRights("getPOList", "edit");
        //$this->mapActionToRights("getPOList", "add");


        $this->app = $app;
        $currentStore = $app->partnerStore();
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
        $usertype = $this->app->getUserSession()->getUser()->type;
        if (!$usertype == "Operator") {
            exit();
        }
        //var_dump($userType = $this->app->getUserSession()->getUser()->type);
    }

    function fillform( $app, $params) {
        
            
            $productlists = $this->app->productStore()->searchTable()->select()->execute();
            //$product=array();
        
            //userType = $this->app->getUserSession()->getUser()->type;
            $userId = $this->app->getUserSession()->getUser()->id;
            $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
            $userType = $this->app->getUserSession()->getUser()->type;

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


            
            //if userpartnerId is empty (no partnerid in user) 
            if($userPartnerId == 0){
                 // Get Product listing
                $partner=$this->app->partnerStore()->getById($userPartnerId);
                /*
                foreach($productlists as $productlist) {        
            
                    $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
                }        */
                
                $product[]= array( 'id' => 0, 'name' => 'No Product');

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
                }     

                $hasPartnerId = false;
                
            }else {
                 // Get Product listing
                $partner=$this->app->partnerStore()->getById($userPartnerId);

                           
                // Get Partner Service
                $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $userPartnerId)->execute();
                if($partnerservices){
                    foreach($partnerservices as $service){
                        $servicerecords[] = $service->toArray();
                        // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                    }
                }

                foreach($servicerecords as $servicerecord) {    

                    $productobj = $this->app->productStore()->getById($servicerecord['productid']);
                    
                    $product[]= array( 'id' => $servicerecord['productid'], 'name' => $productobj->name, 'partnerid' => $servicerecord['partnerid'], 'refineryfee' => $servicerecord['refineryfee'], 'premiumfee' => $servicerecord['premiumfee'], 'dailybuylimitxau' => $servicerecord['dailybuylimitxau'], 'dailyselllimitxau' => $servicerecord['dailyselllimitxau'],);
                } 
                
                // Old List
                /*
                foreach($productlists as $productlist) {        
            
                    $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
                }     */   
                
                // Get Refinery Fee/ Premium Fee
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $fees[]= array('id' =>$productlist->id, 'refineryfee' =>$partner->getRefineryFee($productobj), 'premiumfee' => $partner->getPremiumFee($productobj));
                }     

                // Get Product Permissions
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $permissions[]= array('id' =>$productlist->id, 'canbuy' =>$productlist->companycanbuy, 'cansell' =>$productlist->companycansell, 'byweight' =>$productlist->trxbyweight, 'bycurrency' =>$productlist->trxbycurrency,  'weight' =>$productlist->weight, 
                    'partnerCanBuy' => $partner->canBuy($productobj),  'partnerCanSell' => $partner->canSell($productobj),  'partnerCanQueue' => $partner->canQueue($productobj),  'partnerCanRedeem' => $partner->canRedeem($productobj), );
                }     
                
                // End Refinery Fee/ Premium Fee

                $hasPartnerId = true;
            }
           
        
        echo json_encode([ 'success' => true, 'permissions' => $permissions ,'fees' =>$fees , 'items' => $product, 'status' => $status, 'usertype' => $userType, 'haspartnerid' => $hasPartnerId]);
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

        function fillspecialold( $app, $params) {
            
                
            $productlists = $this->app->productStore()->searchTable()->select()->execute();
            //$product=array();

            //userType = $this->app->getUserSession()->getUser()->type;
            $userId = $this->app->getUserSession()->getUser()->id;
            $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
            
            
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
 
            //if userpartnerId is empty (no partnerid in user) 
            if($userPartnerId == 0){


                // Get SalespersonPartner List
                $salesmanpartnerlists = $this->app->partnerStore()->searchTable()->select()
                ->where(['salespersonid' => $userId])
                ->execute();
                foreach($salesmanpartnerlists as $salesmanpartnerlist) {        
            
                    $userlists = $this->app->userStore()->searchTable()->select()
                    ->where(['partnerid' => $salesmanpartnerlist->id])
                    ->execute();
                    foreach($userlists as $userlist) {
                        $users[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $salesmanpartnerlist->id, 'type' => $userlist->type, );
                    }
                   

                    $salesmanpartner[]= array( 'id' => $salesmanpartnerlist->id, 'name' => $salesmanpartnerlist->name);
                    
                }     
                $temp = array_unique(array_column($users, 'id'));
                $users = array_intersect_key($users, $temp);

                /* ****************************************** OLD filter customer *************************************************
                foreach($users as $user) {        
                    
                    if($user['type'] == "Customer"){
                        $customer[]= array( 'id' => $user['id'], 'name' => $user['name'], 'partnerid' => $user['partnerid']);
                    }
                        
                      
                } ******************************************************* */

                $partnerlists = $this->app->partnerStore()->searchTable()->select()
                    //->where(['salespersonid' => $userId])
                    ->execute();

                foreach($partnerlists as $partnerlist) {
                    
                    $filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid);
                    
                }
                foreach($filteredpartner as $fpt){
                    if($fpt['saleid'] == $userId){
                        $customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode']);
                    }
                }
                //print_r($users);
                // Get Customer daily limits
                foreach($users as $user) {        
                    if($user['type'] == "Customer"){

                        $partner=$this->app->partnerStore()->getById($user['partnerid']);
                        $customerdailylimit[]= array( 'id' => $user['id'], 'name' => $user['name'], 'dailybuylimitxau' => $partner->dailybuylimitxau, 'dailyselllimitxau' =>  $partner->dailyselllimitxau, );
                    }
                   
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
                /*
                // Get Partner Service
                $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $userPartnerId)->execute();
                if($partnerservices){
                    foreach($partnerservices as $service){
                        $servicerecords[] = $service->toArray();
                        // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                    }
                }*/

                $spotordermanager=$this->app->spotorderManager(); 
			
                foreach($servicerecords as $servicerecord) {    

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
                    'partnerCanBuy' => $servicerecord['canbuy'],  'partnerCanSell' => $servicerecord['cansell'],  'partnerCanQueue' => $servicerecord['canqueue'],  'partnerCanRedeem' => $servicerecord['canredeem'], );
                    
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
                
                
            }else {

                // Get SalespersonPartner List
                $salesmanpartnerlists = $this->app->partnerStore()->searchTable()->select()
                ->where(['salespersonid' => $userId])
                ->execute();
                foreach($salesmanpartnerlists as $salesmanpartnerlist) {        
            
                    $userlists = $this->app->userStore()->searchTable()->select()
                    ->where(['partnerid' => $salesmanpartnerlist->id])
                    ->execute();
                    foreach($userlists as $userlist) {
                        $users[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $salesmanpartnerlist->id, 'type' => $userlist->type, );
                    }
                   

                    $salesmanpartner[]= array( 'id' => $salesmanpartnerlist->id, 'name' => $salesmanpartnerlist->name);
                    
                }     
                $temp = array_unique(array_column($users, 'id'));
                $users = array_intersect_key($users, $temp);

                $partnerlists = $this->app->partnerStore()->searchTable()->select()
                    //->where(['salespersonid' => $userId])
                    ->execute();

                foreach($partnerlists as $partnerlist) {
                    
                    $filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid);
                    
                }
                foreach($filteredpartner as $fpt){
                    if($fpt['saleid'] == $userId){
                        $customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'],  'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode']);
                    }
                }
                //foreach($users as $user) {        
                    /* ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman **********************
                    if($user['type'] == "Customer"){
                        $customer[]= array( 'id' => $user['id'], 'name' => $user['name'], 'partnerid' => $user['partnerid']);
                    } ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman ********************** */
                            
                //}
                //print_r($users);
                // Get Customer daily limits

                  /* ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman **********************
                foreach($users as $user) {        
                    if($user['type'] == "Customer"){

                        $partner=$this->app->partnerStore()->getById($user['partnerid']);
                        $customerdailylimit[]= array( 'id' => $user['id'], 'name' => $user['name'], 'dailybuylimitxau' => $partner->dailybuylimitxau, 'dailyselllimitxau' =>  $partner->dailyselllimitxau, );
                    }
                   
                }  ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman ********************** */

                //print_r($users);
                //print_r($customerdailylimit);
                //print_r($salesmanpartner);
                // Get Product listing
                $partner=$this->app->partnerStore()->getById($userPartnerId);
                
                foreach($customer as $cust) {        
                    //print_r($cust);
                    //print_r("=="); 
                    // Get Partner Service
                    
                    // old service
                    //$partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $cust['partnerid'])->execute();
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
                /*
                // Get Partner Service
                $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $userPartnerId)->execute();
                if($partnerservices){
                    foreach($partnerservices as $service){
                        $servicerecords[] = $service->toArray();
                        // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                    }
                }*/

                $spotordermanager=$this->app->spotorderManager(); 
			
                foreach($servicerecords as $servicerecord) {    

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
                } 
                // Old list 
                /*
                foreach($productlists as $productlist) {        
            
                    $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
                }*/        
                
                // Get Refinery Fee/ Premium Fee
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $fees[]= array('id' =>$productlist->id, 'refineryfee' =>$partner->getRefineryFee($productobj), 'premiumfee' => $partner->getPremiumFee($productobj));
                }     

                // Get Product Permissions
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $permissions[]= array('id' =>$productlist->id, 'canbuy' =>$productlist->companycanbuy, 'cansell' =>$productlist->companycansell, 'byweight' =>$productlist->trxbyweight, 'bycurrency' =>$productlist->trxbycurrency,  'weight' =>$productlist->weight, 
                    'partnerCanBuy' => $partner->canBuy($productobj),  'partnerCanSell' => $partner->canSell($productobj),  'partnerCanQueue' => $partner->canQueue($productobj),  'partnerCanRedeem' => $partner->canRedeem($productobj), );
                }     

                
              
                /* Incorrect partner list
                 // Get Customer Listing Filtered by Customer TYPE
                 foreach($userlists as $userlist) {        
                    if($userlist->type == "Customer"){
                        $customer[]= array( 'id' => $userlist->id, 'name' => $userlist->name);
                    }
                   
                }  */
                
                
                
                // End Refinery Fee/ Premium Fee
            }
        
        
        echo json_encode([ 'success' => true, 'permissions' => $permissions ,'fees' =>$fees , 'items' => $product, 'customers' => $customer, 'customerdailylimit' => $customerdailylimit, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer, 'status' => $status]);
    }

    function fillspecial( $app, $params) {
            
                    
            $productlists = $this->app->productStore()->searchTable()->select()->execute();
            //$product=array();

            //userType = $this->app->getUserSession()->getUser()->type;
            $userId = $this->app->getUserSession()->getUser()->id;
            $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
            
            
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

            //if userpartnerId is empty (no partnerid in user) 
            if($userPartnerId == 0){
                
                // Check if enable corepartner trades
                // Check if has permission
                if($this->app->hasPermission('/root/gtp/coretrades')){
                    // Get all GTP Core Partners
                    $core_partners = $this->app->partnerStore()->searchTable()->select()->where('corepartner', 1)->execute();
                    foreach ($core_partners as $arr){
                        // New Service record
                        $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $arr->id)->execute();
                        // get priceprovidercode
                        $priceprovider = $app->priceproviderStore()->searchTable()->select()->where('pricesourceid', $arr->pricesourceid)->one();
                        $customer[] = array('id' => $arr->id, 'name' => $arr->name, 'buycode' => $arr->buycode, 'sellcode' => $arr->sellcode, 'priceprovidercode' => $priceprovider->code, );
                        $customerdailylimit[]= array( 'id' => $arr->id, 'name' => $arr->name, 'dailybuylimitxau' => $arr->dailybuylimitxau, 'dailyselllimitxau' =>  $arr->dailyselllimitxau, );


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

                        // Get The objects of individual records to acquire balance
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
                        'partnerCanBuy' => $servicerecord['canbuy'],  'partnerCanSell' => $servicerecord['cansell'],  'partnerCanQueue' => $servicerecord['canqueue'],  'partnerCanRedeem' => $servicerecord['canredeem'], 'partnerid' => $servicerecord['partnerid'],);
                        
                    }   

                }else{
                    // Start old
                    // Get SalespersonPartner List
                    $salesmanpartnerlists = $this->app->partnerStore()->searchTable()->select()
                    ->where(['salespersonid' => $userId])
                    ->execute();
                    foreach($salesmanpartnerlists as $salesmanpartnerlist) {        

                        $userlists = $this->app->userStore()->searchTable()->select()
                        ->where(['partnerid' => $salesmanpartnerlist->id])
                        ->execute();
                        foreach($userlists as $userlist) {
                            $users[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $salesmanpartnerlist->id, 'type' => $userlist->type, );
                        }
                    

                        $salesmanpartner[]= array( 'id' => $salesmanpartnerlist->id, 'name' => $salesmanpartnerlist->name);
                        
                    }     
                    $temp = array_unique(array_column($users, 'id'));
                    $users = array_intersect_key($users, $temp);

                    /* ****************************************** OLD filter customer *************************************************
                    foreach($users as $user) {        
                        
                        if($user['type'] == "Customer"){
                            $customer[]= array( 'id' => $user['id'], 'name' => $user['name'], 'partnerid' => $user['partnerid']);
                        }
                            
                        
                    } ******************************************************* */

                    $partnerlists = $this->app->partnerStore()->searchTable()->select()
                        //->where(['salespersonid' => $userId])
                        ->execute();

                    foreach($partnerlists as $partnerlist) {
                        
                        $filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid, 'pricesourceid' => $partnerlist->pricesourceid);
                        
                    }
                    foreach($filteredpartner as $fpt){
                        if($fpt['saleid'] == $userId){
                            // get priceprovidercode
                            $priceprovider = $this->app->priceproviderStore()->searchTable()->select()->where('pricesourceid', $fpt['pricesourceid'])->one();

                            $customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode'], 'priceprovidercode' => $priceprovider->code,);
                        }
                    }
                    //print_r($users);
                    // Get Customer daily limits
                    foreach($users as $user) {        
                        if($user['type'] == "Customer"){

                            $partner=$this->app->partnerStore()->getById($user['partnerid']);
                            $customerdailylimit[]= array( 'id' => $user['id'], 'name' => $user['name'], 'dailybuylimitxau' => $partner->dailybuylimitxau, 'dailyselllimitxau' =>  $partner->dailyselllimitxau, );
                        }
                    
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

                    $spotordermanager=$this->app->spotorderManager(); 

                    foreach($servicerecords as $servicerecord) {    

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
                    // End old
                }
               
                
            }else {

                // Get SalespersonPartner List
                $salesmanpartnerlists = $this->app->partnerStore()->searchTable()->select()
                ->where(['salespersonid' => $userId])
                ->execute();
                foreach($salesmanpartnerlists as $salesmanpartnerlist) {        
            
                    $userlists = $this->app->userStore()->searchTable()->select()
                    ->where(['partnerid' => $salesmanpartnerlist->id])
                    ->execute();
                    foreach($userlists as $userlist) {
                        $users[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $salesmanpartnerlist->id, 'type' => $userlist->type, );
                    }
                

                    $salesmanpartner[]= array( 'id' => $salesmanpartnerlist->id, 'name' => $salesmanpartnerlist->name);
                    
                }     
                $temp = array_unique(array_column($users, 'id'));
                $users = array_intersect_key($users, $temp);

                $partnerlists = $this->app->partnerStore()->searchTable()->select()
                    //->where(['salespersonid' => $userId])
                    ->execute();

                foreach($partnerlists as $partnerlist) {
                    
                    $filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid, 'pricesourceid' => $partnerlist->pricesourceid);
                    
                }
                foreach($filteredpartner as $fpt){
                    if($fpt['saleid'] == $userId){
                        // get priceprovidercode
                        $priceprovider = $this->app->priceproviderStore()->searchTable()->select()->where('pricesourceid', $fpt['pricesourceid'])->one();

                        $customer[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode'], 'priceprovidercode' => $priceprovider->code,);
                    }
                }
                //foreach($users as $user) {        
                    /* ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman **********************
                    if($user['type'] == "Customer"){
                        $customer[]= array( 'id' => $user['id'], 'name' => $user['name'], 'partnerid' => $user['partnerid']);
                    } ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman ********************** */
                            
                //}
                //print_r($users);
                // Get Customer daily limits

                /* ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman **********************
                foreach($users as $user) {        
                    if($user['type'] == "Customer"){

                        $partner=$this->app->partnerStore()->getById($user['partnerid']);
                        $customerdailylimit[]= array( 'id' => $user['id'], 'name' => $user['name'], 'dailybuylimitxau' => $partner->dailybuylimitxau, 'dailyselllimitxau' =>  $partner->dailyselllimitxau, );
                    }
                
                }  ********************************* OLD CUSTOMER - NOT USED - GETS ALL CUSTOMER associated with partners associated with salesman ********************** */

                //print_r($users);
                //print_r($customerdailylimit);
                //print_r($salesmanpartner);
                // Get Product listing
                $partner=$this->app->partnerStore()->getById($userPartnerId);
                
                foreach($customer as $cust) {        
                    //print_r($cust);
                    //print_r("=="); 
                    // Get Partner Service
                    
                    // old service
                    //$partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $cust['partnerid'])->execute();
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
                /*
                // Get Partner Service
                $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $userPartnerId)->execute();
                if($partnerservices){
                    foreach($partnerservices as $service){
                        $servicerecords[] = $service->toArray();
                        // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                    }
                }*/

                $spotordermanager=$this->app->spotorderManager(); 
            
                foreach($servicerecords as $servicerecord) {    

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
                } 
                // Old list 
                /*
                foreach($productlists as $productlist) {        
            
                    $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
                }*/        
                
                // Get Refinery Fee/ Premium Fee
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $fees[]= array('id' =>$productlist->id, 'refineryfee' =>$partner->getRefineryFee($productobj), 'premiumfee' => $partner->getPremiumFee($productobj));
                }     

                // Get Product Permissions
                foreach($productlists as $productlist) {        
                    $productobj = $this->app->productStore()->getById($productlist->id);
                    $permissions[]= array('id' =>$productlist->id, 'canbuy' =>$productlist->companycanbuy, 'cansell' =>$productlist->companycansell, 'byweight' =>$productlist->trxbyweight, 'bycurrency' =>$productlist->trxbycurrency,  'weight' =>$productlist->weight, 
                    'partnerCanBuy' => $partner->canBuy($productobj),  'partnerCanSell' => $partner->canSell($productobj),  'partnerCanQueue' => $partner->canQueue($productobj),  'partnerCanRedeem' => $partner->canRedeem($productobj),'partnerid' => $servicerecord['partnerid'], );
                }     

                
            
                /* Incorrect partner list
                // Get Customer Listing Filtered by Customer TYPE
                foreach($userlists as $userlist) {        
                    if($userlist->type == "Customer"){
                        $customer[]= array( 'id' => $userlist->id, 'name' => $userlist->name);
                    }
                
                }  */
                
                
                
                // End Refinery Fee/ Premium Fee
            }
        
        
        echo json_encode([ 'success' => true, 'permissions' => $permissions ,'fees' =>$fees , 'items' => $product, 'customers' => $customer, 'customerdailylimit' => $customerdailylimit, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer, 'status' => $status]);
    }

    function fillunfulfilled( $app, $params) {
        
        $userType = $this->app->getUserSession()->getUser()->type;
        //userType = $this->app->getUserSession()->getUser()->type;
        $userId = $this->app->getUserSession()->getUser()->id;
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;

        $user=$this->app->userStore()->getById($userId);


        if($user->isOperator()){
            /*// Get all Customer Name for Admin
            $userlists = $this->app->userStore()->searchTable()->select()
                    ->where(['type' => 'Customer'])
                    ->execute();
            
            foreach($userlists as $userlist) {
                $partner=$this->app->partnerStore()->getById($userlist->id);
                $customers[]= array( 'id' => $userlist->id, 'name' => $userlist->name, 'partnerid' => $userlist->partnerid);
            }*/
            $partnerlists = $this->app->partnerStore()->searchTable()->select()
            ->execute();

            foreach($partnerlists as $partnerlist) {
                
                $partners[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1);
            }
            
        }else if($user->isSale()){
            // Get all Customer Name for Salesman
            $partnerlists = $this->app->partnerStore()->searchTable()->select()
            //->where(['salespersonid' => $userId])
            ->execute();

            foreach($partnerlists as $partnerlist) {
                    
                $filteredpartner[]= array( 'id' => $partnerlist->id, 'name' => $partnerlist->name, 'buycode' => $partnerlist->sapcompanybuycode1, 'sellcode' => $partnerlist->sapcompanysellcode1, 'saleid' => $partnerlist->salespersonid);
                
            }
            foreach($filteredpartner as $fpt){
                if($fpt['saleid'] == $userId){
                    $partners[] = array('id' => $fpt['id'], 'name' => $fpt['name'], 'buycode' => $fpt['buycode'], 'sellcode' => $fpt['sellcode'], );
                }
            }

         
               
        }else{
            // Do nothing
        }
        
     
	

       
        echo json_encode([ 'success' => true, 'usertype' => $userType , 'partners' => $partners, 'traderconstant' => $user->isTrader(), 'operatorconstant' => $user->isOperator(), 'saleconstant' => $user->isSale(), ]);
    }

     function getFormDetails($app, $params) {
        
        $userType = $this->app->getUserSession()->getUser()->type;
        $userId = $this->app->getUserSession()->getUser()->id;
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;

        // Get Refinery Fee
        $partner=$this->app->partnerStore()->getById($userPartnerId);


        $product = $this->app->productStore()->getById($params['productid']);
        $refineryfee = $partner->getRefineryFee($product);
        $premiumfee = $partner->getPremiumFee($product);
        // End Refinery Fee

        echo json_encode([ 'success' => true,  'refineryfee' => $refineryfee, 'premiumfee' => $premiumfee, ]);
    }
     
     function initDailyLimit( $app, $params) {
        
            
            //userType = $this->app->getUserSession()->getUser()->type;
            $userId = $this->app->getUserSession()->getUser()->id;
            $userType = $this->app->getUserSession()->getUser()->type;
            $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
            
            //if userpartnerId is empty (no partnerid in user) 
            if($userPartnerId == 0){
                // Get Product listing
                $partner=$this->app->partnerStore()->getById($userPartnerId);
                foreach($productlists as $productlist) {        
            
                    $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
                }        
                
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
                }     
            }else {
                // Get Partner limits
                $partner=$this->app->partnerStore()->getById($userPartnerId);

                // Search for partner id
                $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $userPartnerId)->execute();
                if($partnerservices){
                    foreach($partnerservices as $service){
                        $servicerecords[] = $service->toArray();
                        //print_r($servicerecords);
                        //print_r("@@@@");
                        // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                    }
                }
                foreach($servicerecords as $servicerecord) {    

                    $productobj = $this->app->productStore()->getById($servicerecord['productid']);

                    // Get The objects of individual records to acquire balance
                    //$partnerofrecord = $this->app->partnerStore()->getById($servicerecord['partnerid']); 
                    //$productofrecord = $this->app->productStore()->getById($servicerecord['productid']);

                    $buyamount = $this->getTotalTransactionWeight($servicerecord['partnerid'], $servicerecord['productid'], 'CompanySell');
                    $sellamount = $this->getTotalTransactionWeight($servicerecord['partnerid'], $servicerecord['productid'], 'CompanyBuy');
                    
                    $buybalance = $servicerecord['dailybuylimitxau'] - $buyamount;
                    $sellbalance = $servicerecord['dailyselllimitxau'] - $sellamount;

                    $products[]= array( 'id' => $servicerecord['productid'], 'name' => $productobj->name, 'partnerid' => $servicerecord['partnerid'], 'refineryfee' => $servicerecord['refineryfee'], 'premiumfee' => $servicerecord['premiumfee'], 'dailybuylimitxau' => $servicerecord['dailybuylimitxau'], 'dailyselllimitxau' => $servicerecord['dailyselllimitxau'], 
                    'buybalance' => $buybalance, 'sellbalance' => $sellbalance, 'sellclickminxau' => $servicerecord['sellclickminxau'], 'sellclickmaxxau' => $servicerecord['sellclickmaxxau'], 'buyclickminxau' => $servicerecord['buyclickminxau'], 'buyclickmaxxau' => $servicerecord['buyclickmaxxau'],);
                }
                
                $dailylimit[]= array('dailybuylimitxau' => $partner->dailybuylimitxau, 'dailyselllimitxau' =>  $partner->dailyselllimitxau, 'premiumfee' =>  $partner->dailybuylimitxau);
            }
        
        
        echo json_encode([ 'success' => true, 'dailylimit' => $dailylimit, 'usertype' => $userType, 'products' => $products]);
    }

    public function getPODetail($app, $params){
        // echo $params['query'];exit;
        $cardCode = $params['query'];
        $version = '1.0';
        $verify = false;
        // $code = 'VTC010'; // customer id *required
        $code = $cardCode; // customer id *required
        $list = $this->app->apiManager()->sapGetOpenPo($version, $verify, $code);
        // $this->app->dd($list);
        echo json_encode($list['opnlist']);
    }

    public function getPOList(){
        $version = '1.0';
        $type = 'purchase';
        $refOrKey = '';
        $isRef = false;
        $list = $this->app->apiManager()->sapGetPostedOrders($version, $type, $refOrKey, $isRef);
        echo json_encode($list);
    }
    
     public function getSapVendorCodes($app,$params){
         $permission = $this->app->hasPermission('/root/dg999/redemption/edit');
         if($permission){
             $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
             $apimanager=$this->app->apiManager();
             $request = array(
                 'version' => '1.0',
                 'action' => 'partnerlist',
                 'option' => 'vendor',

             );

             $log = $apimanager->sapBusinessList('1.0', $request);

         }else{
             throw new \Snap\InputException(gettext("Sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
         }
     }

     public function getSapCustomerCodes($app,$params){
         $permission = $this->app->hasPermission('/root/dg999/redemption/edit');
         if($permission){
             $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
             $apimanager=$this->app->apiManager();
             $request = array(
                 'version' => '1.0',
                 'action' => 'partnerlist',
                 'option' => 'vendor',

             );

             $log = $apimanager->sapBusinessList('1.0', $request);

         }else{
             throw new \Snap\InputException(gettext("Sorry, no permission"), \Snap\InputException::GENERAL_ERROR, 'permission');
         }
     }

}


?>
