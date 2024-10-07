<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\TLogging;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */

class CompositeHandler implements \Snap\IHandler {
	Use TLogging;

	/**
	 * Stores a list of handler's that are used by this composite handler
	 * @var array
	 */
	private $children = array();

	/**
	 * The parent permission path
	 * @var string
	 */
	protected $permissionPath;

	/**
	 * A mapping of the actions to the sub-rights to check against.
	 */
	protected $actionRightsMap = array();

	/**
	 * Constructor - to define the permission to be used.
	 * @param string $parentPermissionPath A permission path that represents its parent
	 * @param string $moduleName           The module name to get the permission
	 */
	function __construct($parentPermissionPath = '/root/system', $moduleName = null, $actionRightsMap = array()) {
		if (null == $moduleName) {
			preg_match('/(.*)handler$/', strtolower(get_class($this)), $matches);
			$moduleName = $matches[1];
		}
		if('/' == $parentPermissionPath[strlen($parentPermissionPath)-1]) {
			$parentPermissionPath = substr($parentPermissionPath, 0, strlen($parentPermissionPath)-1);
		}
		$this->permissionPath = $parentPermissionPath . '/' . $moduleName;
		$this->actionRightsMap = $actionRightsMap;
	}

	/**
	 * default implementation that assumes the every action key will have a corresponding permission
	 *
	 * @param  String  $action  Action requested by user
	 * @return String   The permission string representing the permissions to check for
	 */
	function getRights($action) {
		$rights = $this->actionRightsMap[$action];
		if(! is_string($rights)) {
		 	foreach ($this->children as $aChild) {
		 		if($aChild->canHandleAction( \Snap\App::getInstance(), $action)) {
		 			$rights = $aChild->getRights($action);
					break;
		 		}
		 	}
		}
	 	if(preg_match('/(^\/)|(\/$)/', $rights)) {
	 		return $rights;
		} else {
			return "{$this->permissionPath}/" . $rights;
		}
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	 function canHandleAction( $app, $action) {
	 	$canHandleAction = false;
	 	if(isset($this->actionRightsMap[$action])) $canHandleAction = true;
	 	else {
		 	foreach ($this->children as $aChild) {
		 		if ($aChild->canHandleAction($app, $action)) {
		 			$canHandleAction = true;
		 		}
		 	}
	 	}
	 	return $canHandleAction;
	 }

	/**
	 * This method adds in additional handler to form a composite handler chain that is able to
	 * perform certain types of actions.
	 * 
	 * @param IHandler $child The handler that would be added into.
	 */
	function addChild(\Snap\IHandler $child) {
		$this->children[] = $child;
	}

	/**
	 * This is the main method that will be used to handle any requests from the handler
	 *
	 * @param  App 	  $app    The application instance
	 * @param  String $action The action (string) to operate on
	 * @param  Array  $params Query parameters parsed in
	 * @return None
	 */
	function doAction( $app, $action, $params) {
		foreach ($this->children as $aChild) {
			if ($aChild->canHandleAction($app, $action)) {
				if (method_exists($aChild, $action)) {
					return call_user_method_array($action, $aChild, array($app, $params));
				} else return $aChild->doAction($app, $action, $params);
			}
		}
	}

	/**
	 * This method is used to register an action with its associated rights so that we are 
	 * able to invoke the proper actions for this handler.
	 */
	protected function mapActionToRights( $action, $subRights) {
		$subRights = substr($subRights,0,1)=='/' ? $subRights : ($this->permissionPath . '/'.$subRights);
		if( isset($this->actionRightsMap[$action])) {
			$this->actionRightsMap[$action] = $this->actionRightsMap[$action] . ';'. $subRights;
		} else $this->actionRightsMap[$action] = $subRights;
	}
}
