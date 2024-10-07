<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2023
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;
use Snap\store\dbdatastore as DbDatastore;
use Snap\App;
use \Snap\object\OtcManagementFee;
/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *

 * @version 1.0feedata
 */
class managementfeehandler extends CompositeHandler
{
    function __construct(App $app)
    {
		$projectBase = strtolower($app->getConfig()->{'projectBase'});
        $this->mapActionToRights('list', '/root/'.$projectBase.'/managementfee/list');  
        $this->mapActionToRights('delete', '/root/'.$projectBase.'/managementfee/delete');    
        $this->mapActionToRights('add', '/root/'.$projectBase.'/managementfee/add');       
        $this->mapActionToRights('edit', '/root/'.$projectBase.'/managementfee/edit'); 
		$this->mapActionToRights('addPeriod', '/root/'.$projectBase.'/managementfee/add');
		$this->mapActionToRights('addAttempt', '/root/'.$projectBase.'/managementfee/add');
		$this->mapActionToRights('addJobPeriod', '/root/'.$projectBase.'/managementfee/add');
        $this->currentStore = $app->otcmanagementfeeStore();      
        $this->addChild(new ext6gridhandler($this, $this->currentStore));
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {

      $sqlHandle->where(function ($q) {
        $q->where('status',\Snap\object\Order::STATUS_ACTIVE);           
      }); 
      return array($params, $sqlHandle, $fields);
    }
    
	function onPrepareSorting($order) {
     $order = \Snap\object\OtcManagementFee::TYPE_SORT;  
	 return $order;
	}
	
	/**
	 * Add a Periodic Management Fee.
	 *
	 * @param object $app - The application object.
	 * @param array $params - An array of parameters.
	 * @return string - JSON-encoded response.
	 */
	public function addPeriod ($app, $params)
	{
		$response = array(
			'success' => true,
			'id' => '',
			'field' => '',
			'errmsg' => ''
		);
		
		$managementFeeCount = $this->currentStore->searchTable()->select()->where('status', OtcManagementFee::STATUS_ACTIVE)->count();
		
		if (0 == $managementFeeCount) {
			$response = array(
				'success' => false,
				'errmsg' => 'Please add Management Fee first before you can set Periodic Management Fee'
			);
		} else {
			$dbHandle = $app->getDBHandle();
			$prefix = $this->currentStore->getColumnPrefix();
			$condition = sprintf("{$prefix}status = %d", OtcManagementFee::STATUS_ACTIVE);
			$fields = sprintf("{$prefix}period = '%d'", $params['period']);
			$tableName = $this->currentStore->getTableName();
			$result = $dbHandle->query("UPDATE {$tableName} SET {$fields} WHERE {$condition}");
			if (1 <= $result->rowCount()) {
				$response = array(
					'success' => true,
				);
			} else {
				$response = array(
					'success' => false,
					'errmsg' => 'Failed to add Periodic Management Fee'
				);
			}
		}
		
		return json_encode($response);
	}
	
	/**
	 * Add an Attempt to collect the Management Fee.
	 *
	 * @param object $app - The application object.
	 * @param array $params - An array of parameters.
	 * @return string - JSON-encoded response.
	 */
	public function addAttempt ($app, $params)
	{
		$response = array(
			'success' => true,
			'id' => '',
			'field' => '',
			'errmsg' => ''
		);
		
		$managementFeeCount = $this->currentStore->searchTable()->select()->where('status', OtcManagementFee::STATUS_ACTIVE)->count();
		
		if (0 == $managementFeeCount) {
			$response = array(
				'success' => false,
				'errmsg' => 'Please add Management Fee first before you can set Attempt to collect the Management Fee'
			);
		} else {
			$dbHandle = $app->getDBHandle();
			$prefix = $this->currentStore->getColumnPrefix();
			$condition = sprintf("{$prefix}status = %d", OtcManagementFee::STATUS_ACTIVE);
			$fields = sprintf("{$prefix}attempt = '%d'", $params['attempt']);
			$tableName = $this->currentStore->getTableName();
			$result = $dbHandle->query("UPDATE {$tableName} SET {$fields} WHERE {$condition}");
			if (1 <= $result->rowCount()) {
				$response = array(
					'success' => true,
				);
			} else {
				$response = array(
					'success' => false,
					'errmsg' => 'Failed to add Attempt to collect the Management Fee'
				);
			}
		}
		
		return json_encode($response);
	}
	
	/**
	 * Add a Cronjob to collect the management fee.
	 *
	 * @param object $app - The application object.
	 * @param array $params - An array of parameters.
	 * @return string - JSON-encoded response.
	 */
	public function addJobPeriod ($app, $params)
	{
		$response = array(
			'success' => true,
			'id' => '',
			'field' => '',
			'errmsg' => ''
		);
		
		$managementFeeCount = $this->currentStore->searchTable()->select()->where('status', OtcManagementFee::STATUS_ACTIVE)->count();
		
		if (0 == $managementFeeCount) {
			$response = array(
				'success' => false,
				'errmsg' => 'Please add Management Fee first before you can set Cronjob to collect the management fee'
			);
		} else {
			$dbHandle = $app->getDBHandle();
			$prefix = $this->currentStore->getColumnPrefix();
			$condition = sprintf("{$prefix}status = %d", OtcManagementFee::STATUS_ACTIVE);
			$fields = sprintf("{$prefix}jobperiod = '%d'", $params['jobperiod']);
			$tableName = $this->currentStore->getTableName();
			$result = $dbHandle->query("UPDATE {$tableName} SET {$fields} WHERE {$condition}");
			if (1 <= $result->rowCount()) {
				$response = array(
					'success' => true,
				);
			} else {
				$response = array(
					'success' => false,
					'errmsg' => 'Failed to add Cronjob to collect the management fee'
				);
			}
		}
		
		return json_encode($response);
	}
}
