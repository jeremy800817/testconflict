<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\App;
use Snap\object\User;
Use \PhpRbac\Rbac;
use Snap\object\Userswitchbranchlog;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Weng (wayne@silverstream.my)
 * @version 1.0
 */
class userHandler extends CompositeHandler {
	function __construct(App $app) {
		//parent::__construct('/root/system', 'user');

		$this->mapActionToRights("getSalesPersons", "/root/system/user/list;/root/mbb/replenishment;/root/system/replenishment;/root/mbb/redemption;/root/mbb/buyback;/root/gtp/logistic;/root/mbb/logistic;");
		$this->app = $app;
        $this->mapActionToRights('list', '/root/system/user/list');
        $this->mapActionToRights('detail', '/root/system/user/list');
        $this->mapActionToRights('add', '/root/system/user/add');
        $this->mapActionToRights('edit', '/root/system/user/edit');
        $this->mapActionToRights('delete', '/root/system/user/suspend');
        $this->mapActionToRights('getuserrole', '/root/system/user/list');
        $this->mapActionToRights('changepassword', '/all/access');
        $this->mapActionToRights('getuserprofile', '/root/system/user/list');

        $this->mapActionToRights('getUserAdditionalData', '/all/access');
        $this->mapActionToRights('exportExcel', '/all/access');
        
		$currentStore = $app->userFactory();
        $this->currentStore = $currentStore;
        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore, 1));
        } else {
            $this->addChild(new ext6gridhandler($this, $currentStore, 1));
        }
	}

    function onPreQueryListing($params, $sqlHandle, $fields){
        $sqlHandle->andWhere('status','!=', User::STATUS_DELETED);
        return array($params, $sqlHandle, $fields);
    }

	function onPreListing($objects, $params, $records) {

		$app = App::getInstance();
		
   
        foreach($records as $key => $record) {
            // $createdOn = new DateTime($record['createdon']);            
            // Display the current $record variable

            $rbac = new Rbac();

            $roles = $rbac->Users->allRoles($record['id']);
            foreach($roles as $index => $aRole) {
                $records[$key]['role'] = $records[$key]['role'].' '.($index+1).'. '.$aRole['Title'];
            }

            if ($app->userStore()->getRelatedStore('usradditionaldata')){
                $additionalData = $this->app->userStore()->getRelatedStore('usradditionaldata')->searchTable()->select()->where('userid', $records[$key]['id'])->one();
                $records[$key]['staffid'] = $additionalData->staffid;
            }             
        }
			
		

		return $records;
	}

	public function getSalesPersons(){    	
        $salespersons = $this->app->userStore()->searchTable()->select()->where('usr_type', 'Sale')->execute();
        $users=array();
        foreach($salespersons as $salesperson) {        
            $users[]= array( 'id' => $salesperson->id, 'name' => $salesperson->name);
        }        
		echo json_encode(array('salesmen'=>$users));    
	}      
    function doAction( $app, $action, $params) {
        $rbac = new Rbac();
        if ($action == 'add' || $action == 'edit') {
            try {
                $tmpRecord = $app->userFactory()->create();
                if ($action == 'add') {
                    $tmpRecord->isUsernameValid($params['username']);
                    $tmpRecord->isUsernameExists($params['username']);
                }else{
                    if(isset($params['partnerid'])){
                        $user = $app->userStore()->getById($params['id']);
                        if($params['partnerid'] != $user->partnerid){
                            $this->log("User switch account from " . $user->partnerid . ' to ' . $params['partnerid'], SNAP_LOG_INFO);
                            $userswitchbranchlog = $app->userswitchbranchlogStore()->create([
                                'userid' => $user->id,
                                'frompartnerid' => $user->partnerid,
                                'topartnerid' => $params['partnerid'],
                                'status' => Userswitchbranchlog::STATUS_INACTIVE
                            ]);
                            $userswitchbranchlog = $app->userswitchbranchlogStore()->save($userswitchbranchlog);

                            $params['userswitchbranchlogid'] = $userswitchbranchlog->id;
                        }
                    }
                }
                if (isset($params['userpassword']) && strlen($params['userpassword']) > 0) $tmpRecord->isPasswordValid($params['userpassword']);
            } catch(\Snap\InputException $e) {
                $fieldErr = 'username';
                if ($e->getErrorField() == 'password_user') $fieldErr = 'userpassword';
                $this->log("Error in validating login info. Error is " . $e->getMessage(), SNAP_LOG_INFO);
                echo json_encode(['success' => false, 'errmsg' => $e->getMessage(), 'field' => $fieldErr]);
                return;
            }

            if (strlen($params['userpassword']) > 0) {
                $now = new \DateTime("now", $app->getUserTimezone()); 
                $now = $now->format("Y-m-d H:i:s");
                $params['passwordmodified'] = $now;
                $params['password'] = array('func' => "SHA2('".$params['userpassword']."', 224)");
            }

            if ($action == 'add'){
                $params['status'] = $tmpRecord::STATUS_ACTIVE;
            }
            
        } elseif ($action == 'delete') {
            $params['statusdel'] = true;
        } elseif ($action == 'changepassword') {
            $tmpRecord = $app->userFactory()->create();  
            //$oldPasswordValid = $tmpRecord->validatePassword($params['oldpassword'], $params['id']);
            $oldPasswordValid = $tmpRecord->validateUser($params['user_name'],$params['oldpassword'],null);            
            if ($oldPasswordValid) {
                try {
                    if (isset($params['userpassword']) && strlen($params['userpassword']) > 0) $tmpRecord->isPasswordValid($params['userpassword']);
                } catch(\Snap\InputException $e) {
                    $fieldErr = 'userpassword';
                    $this->log("Error in validating change password info [ new password ]. Error is " . $e->getMessage(), SNAP_LOG_INFO);
                    echo json_encode(['success' => false, 'errmsg' => $e->getMessage(), 'field' => $fieldErr]);
                    return;
                }

                $now = new \DateTime("now", $app->getUserTimezone()); 
                $now = $now->format("Y-m-d H:i:s");
                $params['passwordmodified'] = $now;
                $params['password'] = array('func' => "SHA2('".$params['userpassword']."', 224)");
                $params['oldpassword'] = array('func' => "SHA2('".$params['oldpassword']."', 224)");
                $params['bypassrole'] = true;
                $params['username']=$params['user_name'];
                $params['id']=$this->app->getUserSession()->getUserId();
                $action = 'edit';
            } else {
                $errMsg = 'Unable to validate current password';
                $this->log("Error in validating change password info [ old password ]. Error is " . $errMsg, SNAP_LOG_INFO);
                echo json_encode(['success' => false, 'errmsg' => $errMsg, 'field' => 'oldpassword']);
                return;
            }
        } elseif ($action == 'getuserrole') {
            $roleData = array();
            $roles = $rbac->Users->allRoles($params['id']);
            foreach($roles as $aRole) {
                $roleData[] = ['id' => $aRole['ID'], 'title' => $aRole['Title'], 'description' => '-', 'permissions' => '-'];
            }            
            // $object = $app->userFactory()->getById($params['id']);
            // $roles = explode(';', $object->role);
            // foreach ($roles as $roleID) {
            //     $roleTitle = $rbac->Roles->getTitle($roleID);
            //     $roleData[] = ['id' => $roleID, 'title' => $roleTitle, 'description' => '-', 'permissions' => '-'];
            // }
            echo json_encode(['success' => true, 'roledata' => $roleData]);
            return;
        } elseif ($action == 'getuserprofile') {
            $object = $app->userFactory()->getById($params['id'], array(), false, 1);
            $record = $object->toArray();

            $roles = explode(';', $record['role']);
            foreach ($roles as $roleID) {
                $roleTitle = $rbac->Roles->getTitle($roleID);
                $roleData[] = ['role' => $roleTitle];
            }

            $record['expire'] = $record['expire']->format('Y/m/d');
            unset($record['password']);

            echo json_encode(['success' => true, 'userdata' => $record, 'roledata' => $roleData]);
            return;
        }
        return parent::doAction($app, $action, $params);
    }

    function onPostAddEditCallback($record, $params) {

        if(isset($params['userswitchbranchlogid'])){
            $userswitchbranchlog = $this->app->userswitchbranchlogStore()->getById($params['userswitchbranchlogid']);

            if($userswitchbranchlog){
                $userswitchbranchlog->status = Userswitchbranchlog::STATUS_ACTIVE;
                $userswitchbranchlog = $this->app->userswitchbranchlogStore()->save($userswitchbranchlog);
            }
        }

        if (isset($params['bypassrole']) && $params['bypassrole']) return null;
        $rbac = new Rbac();
        $allRoles = $rbac->Users->allRoles($record->id);
        foreach ($allRoles as $oneRole) {
            $rbac->Users->unassign($oneRole['ID'], $record->id);
        }
        $roles = explode('||', $params['selectedroles']);
        foreach ($roles as $roleID) {
            $rbac->Users->assign($roleID, $record->id);
        }

        if($this->app->userStore()->getRelatedStore('usradditionaldata')){
            $additionalData = $this->app->userStore()->getRelatedStore('usradditionaldata')->searchTable()->select()->where('userid', $params['id'])->one();
            if (empty($additionalData)){
                $record->addAdditionalData(
                    $params['staffid']
                );
            }else{
                $record->updateAdditionalData(
                    $additionalData, $params['staffid']
                );
            }
        }
    }

    public function getUserAdditionalData($app, $params){    	
        $additionalData = $app->usradditionaldataStore()->searchTable(false)->select()->where('userid', $params['id'])->orderBy("id", "DESC")->execute();
        echo json_encode(['success' => true, 'additionaldata' => $additionalData]);
	} 

    function exportExcel($app, $params){
		
        $modulename = 'USER_LIST';
        $header = json_decode($params["header"]);
        // $dateRange = json_decode($params["daterange"]);

        if("on" == $this->app->getConfig()->{'otc.user.activity.log'}){
            $params['otcuseractivitylog'] = true;
        }

        ob_start();
        $this->doAction($app, 'list', $params);
        $data = ob_get_contents();
        ob_end_clean();

        $list = json_decode($data);

        $filteredRecords = [];
        // change status based on object status
        foreach ($list->records as $record) {

            $recordDate = strtotime($record->createdon); 
            // check status with constant
            // if ($recordDate >= strtotime($dateRange->startDate) && $recordDate <= strtotime($dateRange->endDate)) {
                switch ($record->status) {
                    case User::STATUS_ACTIVE:
                        $record->status = gettext("Active");
                        break;
                    case User::STATUS_INACTIVE:
                        $record->status = gettext("Inactive");
                        break;
                    case User::STATUS_SUSPENDED:
                        $record->status = gettext("Suspend");
                        break;
                    case User::STATUS_DISABLED_RESIGN:
                        $record->status = gettext("Reversed");
                        break;
                    case User::STATUS_DISABLED_RELIEF:
                        $record->status = gettext("Disabled For Revoke");
                        break;
                    case User::STATUS_DISABLED_RESIGN:
                        $record->status = gettext("Disabled For Resign");
                        break;
                    default:
                        return "";
                }

                $filteredRecords[] = $record;
            // }
        }

        $this->app->reportingManager()->generateTransactionReportWithList($filteredRecords, $header, '', '', $modulename, '', '', null, '', null, $this->currentStore);
    }
}
