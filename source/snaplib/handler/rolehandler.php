<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\App;
Use PhpRbac\Rbac;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Wayne <wayne@silverstream.my>
 * @version 1.0
 */
class roleHandler extends CompositeHandler {
	private $parentID = 1;
	private $permissionTreePath = array();
	private $permissionTreePathText = array();
	private $removePermission = array();

	function __construct(App $app) {
		parent::__construct('/root/system', 'role');

		$this->mapActionToRights('list', 'list');
		$this->mapActionToRights('detail', 'list');
		$this->mapActionToRights('add', 'add');
		$this->mapActionToRights('edit', 'edit');
		$this->mapActionToRights('delete', 'delete');
		$this->mapActionToRights('getrolepermissions', 'list');

		$this->removePermission[] = "/root/developer";
		$this->removePermission[] = "/root/developer/tag";
		$this->removePermission[] = "/root/developer/event";
	}

	function doAction( $app, $action, $params) {
		$rbac = new Rbac();
        
        $this->doOtcUserActivityLog($app, $action, $params);
        
		if ($action == 'list') {
            if ($params['filter']) {
                $filters = $this->getNameValues($params['filter']);
            }
        
			$roles = array();
			$allRoles = $rbac->Roles->descendants(1);
			foreach ($allRoles as $roleTitle => $roleData) {
                $bFiltderMatch = true;
				if(preg_match('/developer/i', $roleTitle)) {
					continue;
				}
                if ($filters['title'] && !preg_match('/'.$filters['title'].'/i', $roleTitle)) {
                    $bFiltderMatch = false;
                }
                if ($filters['description'] && !preg_match('/'.$filters['description'].'/i', $roleData['Description'])) {
                    $bFiltderMatch = false;
                }
                if (!$bFiltderMatch) {
                    continue;
                }
				$permissionPath = array();
				$permissions = $rbac->Roles->permissions($roleData['ID']);
				foreach ($permissions as $permissionID) {
					$permissionPath[] = $rbac->Permissions->getPath($permissionID);
				}
				$this->log("permissionPath [{$roleTitle}]: ".implode('||', $permissionPath));
				$oneRole = array();
				$oneRole['id'] = $roleData['ID'];
				$oneRole['title'] = $roleTitle;
				$oneRole['description'] = $roleData['Description'];
				$oneRole['permissions'] = implode('||', $permissionPath);
				$roles[] = $oneRole;
			}

			$dataArray = array();
			$dataArray['sort'] = $params['sort'];
			$dataArray['direction'] = $params['dir'];
			$dataArray['pageSize'] = intval($params['limit']);
			$dataArray['startIndex'] = $params['start'] = intval($params['start']);
			$dataArray['query'] = json_encode($params);
			$dataArray['totalRecords'] = count($roles);
			$dataArray['records'] =	$roles;
			$dataArray['recordsReturned'] =	count($roles);
			echo json_encode($dataArray);
			return;
		} elseif ($action == 'add') {
			$bSuccess = false;
			$errMsg = '';
			$fieldErr = 'title';

			if (!isset($params['title']) || strlen($params['title']) <= 0) {
				$errMsg = 'No Role Title';
			} elseif (!isset($params['permissions']) || strlen($params['permissions']) <= 0) {
				$fieldErr = '';
				$errMsg = 'No Role Permissions';
			} else {
				$permissions = $this->parsePermissionsFromStr($params['permissions']);

				$existRoleID = $rbac->Roles->titleId($params['title']);
				if ($existRoleID > 0) $errMsg = 'Duplicate Role Title';
				else {
					$addedRoleID = $rbac->Roles->add($params['title'], $params['description'], $this->parentID);
					if ($addedRoleID > 0) {
						foreach ($permissions as $permissionPath) {
							$rbac->Roles->assign($addedRoleID, $permissionPath);
						}
						$bSuccess = true;
					} else $errMsg = 'Error in Saving';
				}
			}

			$response = array(
				'success' => $bSuccess,
				'id' => $addedRoleID,
				'field' => $fieldErr,
				'errmsg' => $errMsg
			);
			echo json_encode($response);
            
            if ($app->getOtcUserActivityLog()) {
                $activity = 'Add record with id: ' . $addedRoleID;
                $app->auditManager()->saveOtcUserActivityLog($params['hdl'], $action, $activity);
            }
            
			return;
		} elseif ($action == 'edit') {
			$bSuccess = false;
			$errMsg = '';
			$fieldErr = 'title';

			if (!isset($params['id']) || intval($params['id']) <= 0) {
				$errMsg = 'Invalid Role';
			} elseif (!isset($params['title']) || strlen($params['title']) <= 0) {
				$errMsg = 'No Role Title';
			} elseif (!isset($params['permissions']) || strlen($params['permissions']) <= 0) {
				$fieldErr = '';
				$errMsg = 'No Role Permissions';
			} else {
				$permissions = $this->parsePermissionsFromStr($params['permissions']);

				$existRoleID = $rbac->Roles->returnId($params['title']);
				if ($existRoleID == null || $existRoleID == $params['id']) {
					$existRoleTitle = trim(strtolower($rbac->Roles->getTitle($params['id'])));
					$existRoleDesc = trim(strtolower($rbac->Roles->getDescription($params['id'])));
					$editTitle = trim(strtolower($params['title']));
					$editDesc = trim(strtolower($params['description']));
					if ($existRoleTitle == $editTitle && $existRoleDesc == $editDesc) $updatedRole = true;
					else $updatedRole = $rbac->Roles->edit($params['id'], $params['title'], $params['description']);
					if ($updatedRole) {
						$currentPermissions = $rbac->Roles->unassignPermissions($params['id']);
						foreach ($permissions as $permissionPath) {
							$rbac->Roles->assign($params['id'], $permissionPath);
						}
						$bSuccess = true;
					} else $errMsg = 'Error in Saving';
				} else $errMsg = 'Duplicate Role Title';
			}

			$response = array(
				'success' => $bSuccess,
				'id' => $params['id'],
				'field' => $fieldErr,
				'errmsg' => $errMsg
			);
			echo json_encode($response);
			return;
		} elseif ($action == 'delete') {
			$deleteRole = array();
			$allUsers = $app->userFactory()->searchTable()->select()->execute();
			$roles = explode(',', $params['ids']);
			foreach ($roles as $roleID) {
				$countUser = 0;
				foreach ($allUsers as $oneUser) {
					if ($rbac->Users->hasRole($roleID, $oneUser->id)) $countUser++;
				}
				if ($countUser <= 0) $deleteRole[] = $roleID;
			}

			$bSuccess = false;
			if (count($deleteRole) > 0) {
				foreach ($deleteRole as $roleID) {
					if ($rbac->Roles->remove($roleID)) $bSuccess = true;
				}
			}
			if ($bSuccess) $errMsg = '';
			else $errMsg = 'Users are assigned to the role. Please unassign the users before delete the role.';

			$response = array(
				'success' => $bSuccess,
				'errmsg' => $errMsg
			);
			echo json_encode($response);
			return;
		} elseif ($action == 'getrolepermissions') {
			$permissions = $app->getAvailableApplicationPermission();
			if (count($this->removePermission) > 0) {
				$tmpPermissions = array();
				foreach ($permissions as $key => $value) {
					if (in_array($key, $this->removePermission)) continue;
					$tmpPermissions[$key] = $value;
				}
				$permissions = $tmpPermissions;
			}
			ksort($permissions);

			$this->permissionTreePathText = $permissions;
			$this->permissionTreePath = array();
			foreach ($permissions as $path => $text) {
				$this->permissionTreePath[$path] = $path;
			}
			$permissionTree = $this->explodeTree($this->permissionTreePath, '/', true);
			$permissionTreeData = $this->plotPermissionTree($permissionTree);
			echo json_encode($permissionTreeData);
			return;
		}
		return parent::doAction($app, $action, $params);
	}

	/**
	 * Explode any single-dimensional array into a full blown tree structure,
	 * based on the delimiters found in it's keys.
	 *
	 * @param array   $singleDArray 	The array to be exploded into tree structure
	 * @param string  $delimiter 		The delimiter within the keys
	 * @param boolean $baseVal 			Set true if want to maintain the higher nodes (parents) value
	 * @return array 					The return array consist the tree structure
	 */
	function explodeTree($singleDArray, $delimiter = '_', $baseVal = false) {
		if (!is_array($singleDArray)) return false;
		$splitRE = '/'.preg_quote($delimiter, '/').'/';
		$returnArr = array();
		foreach ($singleDArray as $key => $val) {
			// Get parent parts and the current leaf
			$parts	= preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
			$leafPart = array_pop($parts);

			// Build parent structure
			// Might be slow for really deep and large structures
			$parentArr = &$returnArr;
			foreach ($parts as $part) {
				if (!isset($parentArr[$part])) {
					$parentArr[$part] = array();
				} elseif (!is_array($parentArr[$part])) {
					if ($baseVal) {
						$parentArr[$part] = array('__base_val' => $parentArr[$part]);
					} else {
						$parentArr[$part] = array();
					}
				}
				$parentArr = &$parentArr[$part];
			}

			// Add the final part to the structure
			if (empty($parentArr[$leafPart])) {
				$parentArr[$leafPart] = $val;
			} elseif ($baseVal && is_array($parentArr[$leafPart])) {
				$parentArr[$leafPart]['__base_val'] = $val;
			}
		}
		return $returnArr;
	}

	/**
	 * Plot the tree structure array into the permission tree store data
	 *
	 * @param array   $treeArray 	The array to be plotted into tree structure data
	 * @return array 				The return array is the data structure for the permission tree store
	 */
	function plotPermissionTree($treeArray) {
		$records = array();
		foreach ($treeArray as $k => $v) {
			// skip the baseval thingy. Not a real node.
			if ($k == "__base_val") continue;
			// determine the real value of this node.
			$baseVal = (isset($v["__base_val"]) ? $v["__base_val"] : 'all');
			$showVal = (is_array($v) ? $baseVal : $v);
			// getting the nodes
			$record = array();
			$record['text'] = (isset($this->permissionTreePathText[$showVal]) ? $this->permissionTreePathText[$showVal] : ucwords(strtolower($showVal)));
			$record['checked'] = false;
			if (is_array($v)) {
				// this is what makes it recursive, rerun for childs
				$children = $this->plotPermissionTree($v);
			}
			if (is_array($v)) {
				// parents and children
				$record['permission'] = (($showVal == 'all') ? '/'.$k : $showVal);
				$record['cls'] = 'folder';
				$record['expanded'] = true;
				$record['children'] = $children;
			} else {
				// this is a leaf node. no children
				$record['permission'] = (($showVal == 'all') ? '/'.$k : $showVal);
				$record['leaf'] = true;
			}
			$records[] = $record;				
		}
		return $records;
	}

	/**
	 * Parse the permissions into a consolidated permission array
	 *
	 * @param string   $permissionStr 	The permission in string form
	 * @return array 					The return array is the permission path
	 */
	function parsePermissionsFromStr($permissionStr) {
		$permissions = explode('||', $permissionStr);

		$jsonPermission = [];
		foreach ($permissions as $permissionPath) {
			$currentPermLevel = &$jsonPermission;
			foreach (explode('/', $permissionPath) as $aPath) {
				if ($aPath == '') continue;
				if (isset($currentPermLevel[$aPath]) && 1 == $currentPermLevel[$aPath]) {
					$currentPermLevel = &$currentPermLevel[$aPath];
					break;
				} elseif (!isset($currentPermLevel[$aPath])) $currentPermLevel[$aPath] = array();
				$currentPermLevel = &$currentPermLevel[$aPath];
			}
			$currentPermLevel = 1;
		}

		$tmpPermissions = $this->parsePermissionFromArray($jsonPermission);
		$permissions = explode('||', $tmpPermissions);
		return $permissions;
	}

	/**
	 * Parse the permissions into array
	 *
	 * @param array 	$permissions 	The permission in array
	 * @param string 	$prevPath 		The prev path of the permission
	 * @return array 					The return array is the permission path
	 */
	function parsePermissionFromArray($permissions, $prevPath = '') {
		$permissionPath = '';
		foreach ($permissions as $k => $v) {
			if (is_array($v)) {
				$passPath = $prevPath.'/'.$k;
				$permissionPath .= $this->parsePermissionFromArray($v, $passPath);
			} else $permissionPath .= $prevPath.'/'.$k.'||';
		}
		return $permissionPath;
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	function canHandleAction( $app, $action) {
		return true;
	}

	function getRights($action) {
		if ('list' == $action) {
			return "/root/system/role/list;/root/system/user/add;/root/system/user/edit;";
		}
		return '/root/system/role';
	}
    
    public function doOtcUserActivityLog ($app, $action, $params) {
        if ($app->getOtcUserActivityLog()) {
            $otcExt6GridHandler = new otcext6gridhandler($this, '');
            $otcUserActivityLog = ('true' == $params['otcuseractivitylog']) ? true : false;
            if ('list' == $action && $otcUserActivityLog) {
                $otcExt6GridHandler->doViewLog($app, $action, $params);
            }
            if ('edit' == $action) {
                $otcExt6GridHandler->doEditLog($app, $action, $params);
            }
        }
    }
    
    public function getNameValues($filters) {
		$data = array();
        $filters = json_decode($filters);
		if (count($filters) > 0) {
			foreach ($filters as $filter) {
				if (strlen($filter->value) == 0 && !is_array($filter->value)) continue;
				$filterValue = $filter->value;

				$operator = '=';
				if ($filter->operator == '>') $operator = '>';
				elseif ($filter->operator == '<') $operator = '<';
				elseif ($filter->operator == '>=') $operator = '>=';
				elseif ($filter->operator == '<=') $operator = '<=';
				elseif ($filter->operator == 'eq') $operator = '=';
				elseif ($filter->operator == '!=') $operator = '<>';
				elseif ($filter->operator == 'like') {
					$operator = 'like';
					$filterValue = $filterValue;
				}
                $data[$filter->property] = $filterValue;
			}
		}
        return $data;
	}

}
