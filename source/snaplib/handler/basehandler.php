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

abstract class BaseHandler implements \Snap\IHandler {
	Use TLogging;

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
	 * @param map    $actionRightsMap      An action name - rights (subrights) array for implementing rights permission
	 */
	function __construct($parentPermissionPath = '/root/system', $moduleName = null, $actionRightsMap = array()) {
		if(null == $moduleName) {
			preg_match('/(.*)handler$/', strtolower(get_class($this)), $matches);
			$moduleName = $matches[1];
		}
		//$this->permissionPath = $parentPermissionPath . '/' . $moduleName;
		$this->actionRightsMap = $actionRightsMap;		
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

	/**
	 * default implementation that assumes the every action key will have a corresponding permission
	 *
	 * @param  String  $action  Action requested by user
	 * @return String   The permission string representing the permissions to check for
	 */
	function getRights($action) {
	 	if(preg_match('/(^\/)|(\/$)/', $this->actionRightsMap[$action])) return $this->actionRightsMap[$action];
		else if(isset($this->actionRightsMap[$action])) return "{$this->permissionPath}/" . $this->actionRightsMap[$action];
		else return "{$this->permissionPath}/{$action}";
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	function canHandleAction( $app, $action) {
		if(isset($this->actionRightsMap[$action])) return true;
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
	abstract function doAction( $app, $action, $params);
}
