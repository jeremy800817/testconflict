<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

use Snap\App;
use Snap\object\Partner;
use Snap\InputException;

/**
 *
 * @author Shahanas <shahanas@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class partnerHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/system', 'partner');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("edit", "edit");
        $this->mapActionToRights("delete", "delete");
        $this->mapActionToRights("freeze", "freeze");
        $this->mapActionToRights("unfreeze", "unfreeze");
        $this->mapActionToRights("isunique", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        $this->mapActionToRights("fillform", "add");
        $this->mapActionToRights("prefillform", "edit");
        $this->mapActionToRights("prefillform", "add");
        $this->mapActionToRights("exportExcel", "list");


        $this->app = $app;
        $currentStore = $app->partnerStore();
        $this->currentStore = $app->partnerStore();
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
        $usertype = $this->app->getUserSession()->getUser()->type;
        if (!$usertype == "Operator") {
            exit();
        }
        //var_dump($userType = $this->app->getUserSession()->getUser()->type);
        // Special params to filter koperasi partners
        if($params['getKoperasiPartners']){
            $sqlHandle->andWhere('parent', 0);
            return array($params, $sqlHandle, $fields);
        }
    }

    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */

    function onPreAddEditCallback($object, $params) {
        $paramsservices=json_decode($params['serviceparams']);
        $paramsbranches=json_decode($params['branchparams']);
        if(count($paramsservices) > 0) {

            // Load all first
            $servicesToRemove = $object->getServices();
            
            foreach ($paramsservices as $service) {

                // Exclude  service that still exist
                if ($servicesToRemove[$service->productid]) {
                    unset($servicesToRemove[$service->productid]);
                }

                $productobj=$this->app->productStore()->getById($service->productid);
                $object->registerService(
                    $productobj,
                    $service->id,
                    $service->partnersapgroup,
                    $service->refineryfee,
                    $service->premiumfee,
                    $service->includefeeinprice,
                    $service->canbuy,
                    $service->cansell,
                    $service->canqueue,
                    $service->canredeem,
                    $service->buyclickminxau,
                    $service->buyclickmaxxau,
                    $service->sellclickminxau,
                    $service->sellclickmaxxau,
                    $service->dailybuylimitxau,
                    $service->dailyselllimitxau,
                    $service->redemptionpremiumfee,
                    $service->redemptioncommission,
                    $service->redemptioninsurancefee,
                    $service->redemptionhandlingfee, 
                    $service->specialpricetype,
                    $service->specialpricecondition,
                    $service->specialpricecompanybuyoffset,
                    $service->specialpricecompanyselloffset
                );
            }

            foreach($servicesToRemove as $service) {
                $productobj=$this->app->productStore()->getById($service->productid);
                $object->unregisterService($productobj);
            }

            return $object;
        }

        if(count($paramsbranches) > 0) {

            // Load all first
            $branchesToRemove = $object->getBranches();

            foreach ($paramsbranches as $branch) {

                // Exclude branch that still exist
                if ($branchesToRemove[$branch->code]) {
                    unset($branchesToRemove[$branch->code]);
                }

                $object->registerBranch($branch->id,
                $branch->code,
                $branch->name,
                $branch->sapcode,
                $branch->address,
                $branch->postcode,
                $branch->city,
                $branch->contactno,
                $branch->status);
            }

            foreach($branchesToRemove as $branch) {                
                $object->unregisterBranch($branch);
            }
            return $object;
        }
        return $object;
    }

    public function onPostAddEditCallback($savedRec, $params)
    {       
        if ($this->app->getController() instanceof \Snap\mygtpcontroller ||
        $this->app->getController() instanceof \Snap\bsncontroller || 
        $this->app->getController() instanceof \Snap\alrajhicontroller ||
        $this->app->getController() instanceof \Snap\posarrahnucontroller) {
            $handler = new \Snap\handler\mypartnerhandler($this->app);
            $handler->updateSettings($savedRec, $params);

            $handler->updateSapFeeSettings($savedRec, $params);

        }

        return $savedRec;
    }

    function fillform( $app, $params) {
        $servicerecord = $branchrecord=$productrecord=array();
        if($params['id']) {
            $partnerservices = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $params['id'])->execute();
            if($partnerservices){
                foreach($partnerservices as $service){
                    $servicerecord[] = $service->toArray();
                   // $products = $app->partnerStore()->getRelatedStore('services')->getRelatedStore('product')->searchTable()->select()->where('id', $service->productid)->execute();
                }
            }
            $partnerbranches = $app->partnerStore()->getRelatedStore('branches')->searchTable()->select()->where('partnerid', $params['id'])->execute();
            if($partnerbranches){
                foreach($partnerbranches as $branch){
                    $branchrecord[] = $branch->toArray();
                }
            }

            // Call SAP codes from API

            
            $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
            $apimanager=$this->app->apiManager();
            // $requestvendor = array(
            //     'version' => '1.0',
            //     'action' => 'partnerlist',
            //     'option' => 'vendor',
            //     //'code' => 'VTK108',

            // );
            
            // $requestcustomer = array(
            //     'version' => '1.0',
            //     'action' => 'partnerlist',
            //     'option' => 'customer',
            //     //'code' => 'VTK108',

            // );


            //$username = $app->getConfig()->{'gtp.sap.username'};
            //$password = $app->getConfig()->{'gtp.sap.password'};

            //print_r("=============");
            // $apireturnvendor = $apimanager->sapBusinessList('1.0', $requestvendor);
            // $apireturncustomer = $apimanager->sapBusinessList('1.0', $requestcustomer);
            //print_r($apireturn);
            //print_r("@@@@@@@@@@@@@@@@@@");
            //print_r($apireturn);
            //print_r($apireturn['ocrd'][1]['cardCode']);
            //print_r($apireturn['ocrd'][1]['cardType']);

            // Extract ocrd data out
            // $apifilteredvendor = $apireturnvendor['ocrd'];
            // $apifilteredcustomer = $apireturncustomer['ocrd'];
            //print_r($apifiltered[1]['cardCode']);
            //print_r($apifiltered);

            $apicodesvendor = array();
            $apicodescustomer = array();
            //$apicodesvendor[] = array("id" => 0, "name" => "Not required");
            //$apicodescustomer[] = array("id" => 0, "name" => "Not required");

            // foreach ($apifilteredvendor as $key=>$result) {

            //         $codename = $apifilteredvendor[$key]['cardCode']." ".$apifilteredvendor[$key]['cardName'];
            //         //print_r($apifiltered[$key]['cardCode']);
            //         $apicodesvendor[] = array("id" => $apifilteredvendor[$key]['cardCode'], "name" => $codename);


            // }

            // foreach ($apifilteredcustomer as $key=>$result) {

            //         $codename = $apifilteredcustomer[$key]['cardCode']." ".$apifilteredcustomer[$key]['cardName'];
            //         //print_r($apifiltered[$key]['cardCode']);
            //         $apicodescustomer[] = array("id" => $apifilteredcustomer[$key]['cardCode'], "name" => $codename);

            // } 
            
            /*
            foreach ($apifiltered as $key=>$result) {
                //print_r($result);
                //print_r("===========");

                //print_r($apifiltered[$key]);
                if ($apifiltered[$key]['cardType'] == "C"){

                    $codename = $apifiltered[$key]['cardCode']." ".$apifiltered[$key]['cardName'];
                    //print_r($apifiltered[$key]['cardCode']);
                    $apicodescustomer[] = array("id" => $key, "name" => $codename);

                }else if ($apifiltered[$key]['cardType'] == "S" ){

                    $codename = $apifiltered[$key]['cardCode']." ".$apifiltered[$key]['cardName'];
                    //print_r($apifiltered[$key]['cardCode']);
                    $apicodesvendor[] = array("id" => $key, "name" => $codename);

                }else {

                }
                //print_r($apifiltered[$key]['cardCode']);
    			//$results[$key] = $result;

    		}*/

            //print_r($results);
            //print_r($results['ocrd']);

            //$apicodesvendor[] = array("id" => $params['id'], "name" => "Not required");
            //$apicodescustomer[] = array("id" => $params['id'], "name" => "Not for sale ");

            //$apicodes[] = array("id" => $staff->id, "name" => $staff->name);

            //$apicodes = $apimanager->sapBusinessList('1.0', $request);

            // Add 
           
            $parent = \Snap\object\Partner::getPartnerParentStatus();
            // Check if have group then enable show
            $partner=$this->app->partnerStore()->getById($params['id']);
            if($partner->group){
                $group = true;
            }else{
                $group = false;
            }
            // get partner parent record
            $partner = $this->app->partnerStore()->getById($params['id']);
            if($partner){
                $parentid = $partner->parent;
            }else{
                $parentid = null;
            }

            $settings = [];
            $sapsettings = [];
            $sapbpcodes = [];
            if ($this->app->getController() instanceof \Snap\mygtpcontroller || 
            $this->app->getController() instanceof \Snap\bsncontroller || 
            $this->app->getController() instanceof \Snap\alrajhicontroller ||
            $this->app->getController() instanceof \Snap\posarrahnucontroller ) {
                $handler = new \Snap\handler\mypartnerhandler($this->app);
                $settings = $handler->getSettings($params['id']);
                $return = $handler->getSapSettings($params['id']);
                $sapsettings = $return['sapsettings'];
                $sapbpcodes = $return['sapbpcodes'];
            }
        }
        echo json_encode([ 'success' => true, 'servicerecord' => $servicerecord ,'branchrecord' => $branchrecord,'products' => $productrecord, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer, 'settings' => $settings, 'sapsettingsrecord' => $sapsettings, 'sapbpcodes' => $sapbpcodes, 'parent' => $parent, 'group' => $group, 'parentid' => $parentid]);
    }

     function prefillform( $app, $params) {

            $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
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
                    $apicodesvendor[] = array("id" => $apifilteredvendor[$key]['cardCode'], "name" => $codename);


            }

            foreach ($apifilteredcustomer as $key=>$result) {

                    $codename = $apifilteredcustomer[$key]['cardCode']." ".$apifilteredcustomer[$key]['cardName'];
                    //print_r($apifiltered[$key]['cardCode']);
                    $apicodescustomer[] = array("id" => $apifilteredcustomer[$key]['cardCode'], "name" => $codename);

            }
            
            /*
            foreach ($apifiltered as $key=>$result) {
                //print_r($result);
                //print_r("===========");

                //print_r($apifiltered[$key]);
                if ($apifiltered[$key]['cardType'] == "C"){

                    $codename = $apifiltered[$key]['cardCode']." ".$apifiltered[$key]['cardName'];
                    //print_r($apifiltered[$key]['cardCode']);
                    $apicodescustomer[] = array("id" => $key, "name" => $codename);

                }else if ($apifiltered[$key]['cardType'] == "S" ){

                    $codename = $apifiltered[$key]['cardCode']." ".$apifiltered[$key]['cardName'];
                    //print_r($apifiltered[$key]['cardCode']);
                    $apicodesvendor[] = array("id" => $key, "name" => $codename);

                }else {

                }
                //print_r($apifiltered[$key]['cardCode']);
    			//$results[$key] = $result;

    		}*/

            //print_r($results);
            //print_r($results['ocrd']);

            //$apicodesvendor[] = array("id" => $params['id'], "name" => "Not required");
            //$apicodescustomer[] = array("id" => $params['id'], "name" => "Not for sale ");

            //$apicodes[] = array("id" => $staff->id, "name" => $staff->name);

            //$apicodes = $apimanager->sapBusinessList('1.0', $request);

            $parent = \Snap\object\Partner::getPartnerParentStatus();
        
        echo json_encode([ 'success' => true, 'servicerecord' => $servicerecord ,'branchrecord' => $branchrecord,'products' => $productrecord, 'apicodesvendor' => $apicodesvendor, 'apicodescustomer' => $apicodescustomer, 'parent' => $parent,]);
     }

     public function getSapVendorCodes($app,$params){
        //  $permission = $this->app->hasPermission('/root/dg999/redemption/edit');
         try{
             $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
             $apimanager=$this->app->apiManager();
             $request = array(
                 'version' => '1.0',
                 'action' => 'partnerlist',
                 'option' => 'vendor',

             );

             $log = $apimanager->sapBusinessList('1.0', $request);

         }catch (\Exception  $e){
             throw new \Snap\InputException(gettext("Sorry, Unable to Acquire Data"), \Snap\InputException::GENERAL_ERROR, 'permission');
         }
     }

     public function getSapCustomerCodes($app,$params){
        //  $permission = $this->app->hasPermission('/root/dg999/redemption/edit');
         try{
             $redemptionobj=$this->app->redemptionStore()->getById($params['id']);
             $apimanager=$this->app->apiManager();
             $request = array(
                 'version' => '1.0',
                 'action' => 'partnerlist',
                 'option' => 'vendor',

             );

             $log = $apimanager->sapBusinessList('1.0', $request);

         }catch (\Exception  $e){
             throw new \Snap\InputException(gettext("Sorry, Unable to Acquire Data"), \Snap\InputException::GENERAL_ERROR, 'permission');
         }
     }

     function exportExcel($app, $params){
		
        $modulename = 'PARTNER_LIST';
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        $partnerStore = $app->partnerStore();
        //parent::$children = [];
        $this->currentStore = $partnerStore;

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if('createdon' === $column->index){
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "DATE(`{$prefix}createdon`) as `{$prefix}createdon`"
                );
                $header[$key]->index->original = $original;
            }
            if ('corepartner' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}corepartner` = " . 0 . " THEN 'Non Core'
                     WHEN `{$prefix}corepartner` = " . 1 . " THEN 'Core' END as `{$prefix}corepartner`"
                );
                $header[$key]->index->original = $original;
            }
            if ('autosubmitorder' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}autosubmitorder` = " . 0 . " THEN 'Inactive'
                     WHEN `{$prefix}autosubmitorder` = " . 1 . " THEN 'Active' END as `{$prefix}autosubmitorder`"
                );
                $header[$key]->index->original = $original;
            }
            if ('autocreatematchedorder' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}autocreatematchedorder` = " . 0 . " THEN 'Inactive'
                     WHEN `{$prefix}autocreatematchedorder` = " . 1 . " THEN 'Active' END as `{$prefix}autocreatematchedorder`"
                );
                $header[$key]->index->original = $original;
            }
            if ('status' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . Partner::STATUS_PENDING . " THEN 'Pending'
                     WHEN `{$prefix}status` = " . Partner::STATUS_ACTIVE . " THEN 'Active'
                     WHEN `{$prefix}status` = " . Partner::STATUS_REJECTED . " THEN 'Rejected' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }
        }
        
        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null);
    
    }
    

}


?>
