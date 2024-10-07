<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;
use PhpRbac\Rbac;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class  updatepermissionjob  extends basejob {
	/**
	 * This is the main function that will run for each job.
	 * @param  App    $app    The snap application class
	 * @param  array  $params Parameters that are passed in from commandline with -p option
	 * @return void
	 */
	public function doJob($app, $params = array()) {
		$continue = false;
		foreach( $this->describeOptions() as $anOption => $desc) {
			if( isset($params[$anOption])) {
				$continue = true;
				break;
			}
		}
		if(!$continue) {
			$this->parent->printInfo();
			return;
		}

		echo "============================================\n";
		echo "======\n";
		echo "====== Application permission updater\n";
		echo "======\n";
		echo "============================================\n";
		$countUpdated = 0;
		$permissions = $app->getController()->getAvailableApplicationPermission();
		$rbac = new \PhpRbac\Rbac;
		if(isset($params['sync'])) {
			foreach( $permissions as $aRight => $desc) {
				if(null == $rbac->Permissions->returnId($aRight)) {
					echo "Registering rights $aRight now.\n";
					$rbac->Permissions->addPath($aRight);
					$countUpdated++;
				} //else echo "The permission $aRight is available in the system\n";
			}
			if($countUpdated == 0) echo "All permissions used by system has been registered\nNo action done by this job\n";
			else echo "Updated total of $countUpdated permissions\n";

			//Remove permissions that are no longer in specified in controller.
			$allDecendents = $rbac->Permissions->descendants(1);
			$path = array();
			foreach( $allDecendents as $aPapa) {
				$itemPath = '';	
				$path[$aPapa['Depth']-1] = $aPapa['Title'];
				$idToRemove = $aPapa['ID'];
				for($i=0; $i < $aPapa['Depth'];$i++) {
					$itemPath .= "/" . $path[$i];
				}
				$bFound = false;
				if($itemPath == '/root/developer') continue;
				foreach( $permissions as $aRight => $desc) {
					if(preg_match("#$itemPath#", $aRight)) {
						$bFound = true;
						break;
					}
				}
				if( !$bFound) {
					$idToRemove = $rbac->Permissions->returnId($itemPath);
					$rbac->Permissions->unassignRoles( $idToRemove);
					$rbac->Permissions->remove( $idToRemove, true);
					echo "Remove unreferenced permission $itemPath ($idToRemove)\n";
				}
			}	
		}
		if( isset($params['assignroot'])) {
			$user = $app->userFactory()->getByField('username', $params['assignroot']);
			if( $user->id > 0) {
				$rbac->Users->assign( 1, $user->id);
				echo "Assigned /root to {$params['assignroot']} is {$user->id}\n";
			} else echo "The username {$params['assignroot']} is not found in the system\n";
		}
		if( isset($params['unassignroot'])) {
			$user = $app->userFactory()->getByField('username', $params['assignroot']);
			if( $user->id > 0) {
				$rbac->Users->assign( 1, $user->id);
				echo "Assigned /root to {$params['assignroot']} is {$user->id}\n";
			} else echo "The username {$params['assignroot']} is not found in the system\n";
		}
	}

	/**
	 * This method is used to display options parameter for this job.
	 * @return Array of associative array of parameters.
	 *         E.g.[
	 *         	  'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
	 *         	  'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
	 *         ]
	 *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
	 *                [type] is the expected data type of the parameter or its valid values.
	 *                [default] is the default value for the field.
	 *                [desc] is the description of the parameter and what it does.
	 */
	function describeOptions() {
		return [
			'sync' => array('required' =>  false,  'type' => 'int', 'desc' => 'Synchronise the permissions from controller and DB'),
			'assignroot' => array('required' =>  false,  'type' => 'string', 'desc' => 'Add super-privilege (/root) to specified username'),
			'unassignroot' => array('required' =>  false,  'type' => 'string', 'desc' => 'Remove super-privilege (/root) to specified username')
		];
	}
}
?>