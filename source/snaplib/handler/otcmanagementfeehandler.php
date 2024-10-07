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
use \Snap\object\OtcManagementFee;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Dianah(dianah@silverstream.my)
 * @version 1.0
 */
class otcmanagementfeeHandler extends CompositeHandler {
	function __construct(App $app) {
		
		$this->app = $app;
		$projectBase = strtolower($app->getConfig()->{'projectBase'});
		$this->mapActionToRights('list', '/root/'.$projectBase.'/managementfee/list');
		$this->mapActionToRights('add', '/root/'.$projectBase.'/managementfee/add');
		$this->mapActionToRights('edit', '/root/'.$projectBase.'/managementfee/edit');
		$this->mapActionToRights('delete', '/root/'.$projectBase.'/managementfee/delete');
		
		$this->mapActionToRights('approveManagementFee', '/all/access');
		$this->mapActionToRights('rejectManagementFee', '/all/access');
		$this->mapActionToRights('getParentRecord', '/all/access');
		
		$currentStore = $app->otcmanagementfeeStore();
		$this->currentStore = $currentStore; 

        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore));
        } else {
            $this->addChild(new ext6gridhandler($this, $currentStore));
        }
	}
	
	function getParentRecord ($app, $params)
	{
		$response = array(
			'success' => false,
			'message' => ''
		);
		
		if ($params['parentid']) {
			$managemenetFee = $app->otcmanagementfeeStore()->getById($params['parentid']);
			if ($managemenetFee) {
				$response['success'] = true;
				$response['record'] = $managemenetFee->toArray();
			}
		}
		
		echo json_encode($response);
	}
	
	function approveManagementFee ($app, $params)
	{
		$response = array(
			'success' => false,
			'message' => ''
		);
		
		try{
			$managemenetFee = $app->otcmanagementfeeStore()->getById($params['id']);
			if (!$managemenetFee) throw new \Exception("Management Fee object not found");
			
			$checker = $app->userStore()->searchTable()->select()->where('id',$app->getUsersession()->getUserId())->one();
			$now = new \DateTime('now', $app->getUserTimezone());
			if ($managemenetFee->remarks) $managemenetFee->remarks .= '||';
			
			if (OtcManagementFee::ACTION_ADD == $params['requestaction']) {
				$managemenetFee->status = OtcManagementFee::STATUS_ACTIVE;
				$managemenetFee->checker = $checker->username;
				$managemenetFee->remarks .= 'Remarks: '.$params['remarks'].'; Approval Code:'.$params['approvalcode'];
				
				$managemenetFee->actionon = $now;
				$app->otcmanagementfeeStore()->save($managemenetFee, ['status', 'checker', 'remarks', 'actionon']);
				
				$response['success'] = true;
				$response['message'] = "Add Management Fee Approved";
			}
			
			if (OtcManagementFee::ACTION_EDIT == $params['requestaction']) {
				$oldManagementFee = $app->otcmanagementfeeStore()->getById($params['parentid']);
				if ($oldManagementFee) {
					$oldManagementFee->status = OtcManagementFee::STATUS_INACTIVE;
					$updated = $app->otcmanagementfeeStore()->save($oldManagementFee, ['status']);
					
					if ($updated) {
						$managemenetFee->status = OtcManagementFee::STATUS_ACTIVE;
						$managemenetFee->checker = $checker->username;
						$managemenetFee->remarks .= 'Remarks: '.$params['remarks'].'; Approval Code:'.$params['approvalcode'];
						$managemenetFee->actionon = $now;
						$app->otcmanagementfeeStore()->save($managemenetFee, ['status', 'checker', 'remarks', 'actionon']);
						
						$response['success'] = true;
						$response['message'] = "Edit Management Fee Approved";
					}
				}
			}
			
			if (OtcManagementFee::ACTION_DELETE == $params['requestaction']) {
				$managemenetFee->status = OtcManagementFee::STATUS_INACTIVE;
				$managemenetFee->checker = $checker->username;
				$managemenetFee->remarks .= 'Remarks: '.$params['remarks'].'; Approval Code:'.$params['approvalcode'];
				$managemenetFee->actionon = $now;
				$app->otcmanagementfeeStore()->save($managemenetFee, ['status', 'checker', 'remarks', 'actionon']);
				
				$response['success'] = true;
				$response['message'] = "Delete Management Fee Approved";
			}
		} catch(\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}

		echo json_encode($response);
	}
	
	function rejectManagementFee ($app, $params)
	{
		$response = array(
			'success' => false,
			'message' => ''
		);
		
		try{
			$managemenetFee = $app->otcmanagementfeeStore()->getById($params['id']);
			if (!$managemenetFee) throw new \Exception("Management Fee object not found");
			
			$checker = $app->userStore()->searchTable()->select()->where('id',$app->getUsersession()->getUserId())->one();
			$now = new \DateTime('now', $app->getUserTimezone());
			
			$managemenetFee->status = OtcManagementFee::STATUS_REJECT_APPROVAL;
			$managemenetFee->checker = $checker->username;
			$managemenetFee->remarks = 'Remarks: '.$params['remarks'].'; Approval Code:'.$params['approvalcode'];
			$managemenetFee->actionon = $now;
			
				
			if (OtcManagementFee::ACTION_ADD == $params['requestaction']) {
				$response['success'] = true;
				$response['message'] = "Add Management Fee Rejected";
			}
			
			if (OtcManagementFee::ACTION_EDIT == $params['requestaction']) {
				$response['success'] = true;
				$response['message'] = "Edit Management Fee Rejected";
			}
			
			if (OtcManagementFee::ACTION_DELETE == $params['requestaction']) {
				$response['success'] = true;
				$response['message'] = "Delete Management Fee Rejected";
				
				$managemenetFee->status = OtcManagementFee::STATUS_ACTIVE;
				$managemenetFee->requestaction = OtcManagementFee::ACTION_ADD;
			}
			
			$app->otcmanagementfeeStore()->save($managemenetFee);
		} catch(\Exception $e) {
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		
		echo json_encode($response);
	}
	
	function onPreQueryListing($params, $sqlHandle, $fields)
    {
		if ($params['filter']) {
			if ('approval' == $params['filter']) {
				$sqlHandle->where(function ($q) {
					$q->where('status', OtcManagementFee::STATUS_PENDING_APPROVAL);
					$q->orWhere(function ($r) {
						$r->where('status', OtcManagementFee::STATUS_ACTIVE);
						$r->andWhere('requestaction', OtcManagementFee::ACTION_DELETE);
					});
				});
			}
		} else {
			$sqlHandle->where(function ($q) {
				$q->whereIn('status',[OtcManagementFee::STATUS_ACTIVE, OtcManagementFee::STATUS_PENDING_APPROVAL]);           
			});
		}
		
		return array($params, $sqlHandle, $fields);
    }
	
	function doAction( $app, $action, $params)
	{
		if ($params['starton']) $params['starton'] .= ' 00:00:00';
		if ($params['endon']) $params['endon'] .= ' 23:59:59';

		if ('edit' == $action && OtcManagementFee::STATUS_ACTIVE == $params['status']) {
			$params['parentid'] = $params['id'];
			$params['id'] = '';
			$params['status'] = OtcManagementFee::STATUS_PENDING_APPROVAL;
			$params['requestaction'] = OtcManagementFee::ACTION_EDIT;
		}
		
		if ('delete' == $action) {
			$action = 'edit';
			$params = array(
				'id' => $params['ids'],
				'requestaction' => OtcManagementFee::ACTION_DELETE
			);
		}
        
        return parent::doAction($app, $action, $params);
    }
}
