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
 * This handler manages different JS application states setting such as grid etc
 * supports for updates and deletes
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
class appstatehandler extends basehandler {
	
	/**
	 * default implementation that assumes the every action key will have a corresponding permission
	 *
	 * @param  String  $action  Action requested by user
	 * @return String   The permission string representing the permissions to check for
	 */
	function getRights($action) {
		return "/all/access";
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	function canHandleAction( $app, $action) {
		switch($action) {
			case 'update':
			case 'delete':
			case 'deleteTraderOrder':
				return true;
			default:
				return false;
		}
		return false;
	}

	/**
	 * This method adds in additional handler to form a composite handler chain that is able to
	 * perform certain types of actions.
	 * 
	 * @param IHandler $child The handler that would be added into.
	 */
	function addChild(\Snap\IHandler $child) {
		return false;
	}

	/**
	/**
	 * This is the main method that will be used to handle any requests from the handler
	 *
	 * @param  App 	  $app    The application instance
	 * @param  String $action The action (string) to operate on
	 * @param  Array  $params Query parameters parsed in
	 * @return None
	 */
	function doAction( $app, $action, $params) {
		$this->log('Into the doAction() method now of the app state !!!' . $action, SNAP_LOG_ERROR);
		$appstateStore = $app->appstateFactory();
		if('update' == $action) {
			$arrState = $appstateStore->searchTable()->select()
							->where('userid', $app->getUsersession()->getUserId())
							->andWhere('key', $params['key'])
							->execute();
			if(count($arrState)) $stateObj = $arrState[0];
			else $stateObj = $appstateStore->create(['key' => $params['key'], 'userid' => $app->getUsersession()->getUserId()]);
			$stateObj->value = $params['value'];
			$appstateStore->save($stateObj);
			return json_encode(array('success' => true));
		} else if('delete' == $action) {
			$arrState = $appstateStore->searchTable()->select()
							->where('userid', $app->getUsersession()->getUserId())
							->andWhere('key', $params['key'])
							->execute();
			if(count($arrState)) $appstateStore->delete($arrState[0]);	
			return json_encode(array('success' => true));		
		} else if('deleteTraderOrder' == $action) {
			$arrState = $appstateStore->searchTable()->select()
							->where('userid', $app->getUsersession()->getUserId())
							->andWhere(function ($q) {
								$q->where('key', 'like', 'snap.view.trader.TraderOrders%');
								$q->orWhere('key', 'like', 'trader_orders_dashboard%');
							})
							->execute();
			if(count($arrState)) {
				foreach ($arrState as $aState) {
					$appstateStore->delete($aState);
				}
			}	
			return json_encode(array('success' => true));		
		}
		return '';
	}
}
