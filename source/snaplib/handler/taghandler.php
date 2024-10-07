<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Rahmah <rahmah@silverstream.my>
 * @version 1.0
 */
class tagHandler extends CompositeHandler {
	function __construct(App $app) {
		//parent::__construct('/root/developer', 'tag');

		$this->mapActionToRights('fillform', '/root/developer/tag/add');
		$this->mapActionToRights('fillform', '/root/developer/tag/edit');
		$this->mapActionToRights('detailview', '/root/developer/tag/list');
		$this->mapActionToRights("getProductCategoryTags", "/root/developer/tag/list;
															/root/system/product/list;
															/root/gtp/cust;/root/gtp/sale;
															/root/gtp/limits;
															/root/gtp/order/list;
															/root/mbb/order/list");
		$this->mapActionToRights("getPriceSourceTags", "/root/developer/tag/list;
														/root/system/product/list;
														/root/gtp/cust;/root/gtp/sale;
														/root/gtp/limits;
														/root/gtp/order/list;
														/root/mbb/order/list");
		$this->mapActionToRights("getCurrencyTags", "/root/developer/tag/list;
													/root/system/product/list;
													/root/gtp/cust;/root/gtp/sale;
													/root/gtp/limits;
													/root/gtp/order/list;
													/root/mbb/order/list");
        $this->mapActionToRights("getTradingScheduleTags", "/root/developer/tag/list");
		$this->mapActionToRights("getLogisticsVendors", "/root/developer/tag/list;/root/mbb/replenishment;/root/system/replenishment;/root/mbb/redemption;/root/mbb/buyback;/root/bmmb/redemption;/root/bmmb/logistic;/root/gtp/logistic;/root/mbb/logistic");

		$this->mapActionToRights("getProviderGroupTags", "/all/access");		
		
		$this->app = $app;
		$tagStore = $app->tagfactory();
		$this->addChild(new ext6gridhandler($this, $tagStore, 1));
	}

	function getRights($action) {		
		if('list' == $action) {
			return "/root/developer/tag;";
		}
		if($action=='getProductCategoryTags'){
			return '/all/access';
		}
		if($action=='getPriceSourceTags'){
			return '/all/access';
		}
		if($action=='getCurrencyTags'){
			return '/all/access';
		}
		if($action=='getTradingScheduleTags'){
			return '/all/access';
		}
		if($action=='getLogisticsVendors'){
			return '/all/access';
		}
		if($action=='getProviderGroupTags'){
			return '/all/access';
		}
		return '/root/developer/tag';
	}

	/**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action) {
        return true;
	}

	/*
        This method is to get the Category to be listing in the form
    */
	function fillform( $app, $params) {
		$category = \Snap\object\Tag::getCategory();
		echo json_encode( ['success' => true,  'category' => $category]);						
	}

	/*
        This method is to get data for view details
    */
	function detailview($app, $params) {
		$object = $app->tagfactory()->getById($params['id']);

		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		$detailRecord['default'] = [ "ID" => $object->id, 
									'Category' => $object->category,	
									'Tag Code' => $object->code,	
									'Description' => $object->description,
									'Value' => $object->value,						
									'Status' => $params['status_text'],	
									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,								
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	function onPreListing($objects, $params, $records) {
		
		foreach ($records as $key => $record) {
			$records[$key]['status_text'] = ($record['status'] == "1" ? "Active" : "Inactive");
		}		
			
		return $records;
	}
	function getLogisticsVendors(){
		$logisticvendors= $this->app->tagFactory()->searchTable()->select()->where('category', 'LogisticVendor')->execute();
		$vendors=array();
        foreach( $logisticvendors as $logisticvendor) {
            $vendors[]= array( 'id' => $logisticvendor->id, 'value' => $logisticvendor->value);
        }
        echo json_encode(array('vendors'=>$vendors)); 
	}
	public function getPriceSourceTags(){ 
        $pricesourcetags = $this->app->tagFactory()->searchTable()->select()->where('category', 'PriceSource')->execute();
        $pricesource=array();
        foreach( $pricesourcetags as $pricesourcetag) {
            $pricesource[]= array( 'id' => $pricesourcetag->id, 'value' => $pricesourcetag->value);
        }
        echo json_encode(array('pricesources'=>$pricesource));  
	}
	public function getProductCategoryTags(){ 
        $productcategorytags = $this->app->tagFactory()->searchTable()->select()->where('category', 'ProductCategory')->execute();
        $productcategories=array();
        foreach( $productcategorytags as $productcategorytag) {
            $productcategories[]= array( 'id' => $productcategorytag->id, 'value' => $productcategorytag->value);
        }
        echo json_encode(array('productcategories'=>$productcategories));  
	}	
	public function getTradingScheduleTags(){ 
        $tradingscheduletags = $this->app->tagFactory()->searchTable()->select()->where('category', 'TradingSchedule')->execute();
        $tradingschedule=array();
        foreach( $tradingscheduletags as $tradingscheduletag) {
            $tradingschedule[]= array( 'id' => $tradingscheduletag->id, 'name' => $tradingscheduletag->value);
        }
        echo json_encode(array('tradingschedule'=>$tradingschedule));  
	}
	public function getCurrencyTags(){ 
        $currencytags = $this->app->tagFactory()->searchTable()->select()->where('category', 'Currency')->execute();
        $currency=array();
        foreach( $currencytags as $currencytag) {
            $currency[]= array( 'id' => $currencytag->id, 'value' => $currencytag->value);
        }
        echo json_encode(array('currency'=>$currency));  
	}
	public function getProviderGroupTags(){ 
        $priceprovidertags = $this->app->tagFactory()->searchTable()->select()->where('category', 'PriceProvider')->execute();
        $providergroup=array();
        foreach( $priceprovidertags as $priceprovidertag) {
            $providergroup[]= array( 'id' => $priceprovidertag->id, 'value' => $priceprovidertag->value);
        }
        echo json_encode(array('providergroup'=>$providergroup));  
	}
}
