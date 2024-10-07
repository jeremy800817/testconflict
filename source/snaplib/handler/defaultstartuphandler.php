<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

//Permission System
use PhpRbac\Rbac;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
class defaultstartuphandler extends basehandler {
    function getRights($action) {
        return '/all/access';
    }

    function doAction( $app, $action, $params) {
        $jsonPermission = $jsonStates = [];
        //return file_get_contents("client/maindev.html");
        /*if(defined('production_environment')) $tmpData = file_get_contents(SNAPAPP_DIR . '/src/build/production/snap/index.html');
        else*/ $tmpData = file_get_contents(SNAPAPP_DIR . '/index.html');
        
        $locale = 'en_US';//en_US, zh_CN
        $localeFilePath = SNAPAPP_DIR . '/src/locale/' . $locale . '.json';
        if (isset($params['locale']) && 0 < strlen($params['locale'])) {
			$filePath = SNAPAPP_DIR . '/src/locale/' . $params['locale'] . '.json';
			if (file_exists($filePath)) {
				$localeFilePath = $filePath;
				$locale = $params['locale'];
				$_SESSION["locale"] = $params['locale'];
			}
		} else if (isset($_SESSION['locale']) && 0 < strlen($_SESSION['locale'])) {
			$filePath = SNAPAPP_DIR . '/src/locale/' . $_SESSION['locale'] . '.json';
			if (file_exists($filePath)) {
				$localeFilePath = $filePath;
				$locale = $_SESSION['locale'];
			}
		}
        $translations = file_get_contents($localeFilePath);

        $userId = $app->getUserSession()->getUserid();
        if($userId) {
            
            $permissionCollection = array();
            $rbac = new Rbac();
            $allRoles = $rbac->Users->allRoles($userId);
            foreach($allRoles as $aRole){
                $permissions = $rbac->Roles->permissions($aRole['ID']);
                foreach($permissions as $aPermID) {
                    if(isset($permissionCollection[$aPermID])) continue;
                    //Added by Devon on 2018/11/26 to fix path issue when pointing directly to a /root 
                    $permPath = $rbac->Permissions->getPath($aPermID);
                    if('root/root' == $permPath) $permPath = '/root';
                    //End add 2018/11/26
                    $permissionCollection[$aPermID] = $permPath;
                }
            }
            //Next we will need to break the path down for each permission and make them into arrays....
            foreach($permissionCollection as $permissionPath) {
                $currentPermLevel = &$jsonPermission;
                // $previousPermLevel = null;
                foreach( explode('/', $permissionPath) as $aPath) {
                    if('' == $aPath) continue;
                    if(isset($currentPermLevel[$aPath]) && 1 == $currentPermLevel[$aPath]) {
                        $currentPermLevel = &$currentPermLevel[$aPath];
                        break;
                        //skip, got overriding perm already.
                    } else if( !isset($currentPermLevel[$aPath])) $currentPermLevel[$aPath] = array();
                    $currentPermLevel = &$currentPermLevel[$aPath];
                }
                $currentPermLevel= 1;
            }
            //Finally get the states data for user.
            $states = $app->appstateFactory()->searchTable()->select()
                            ->where('userid', $userId)
                            ->execute();
            if(count($states)) {
                foreach($states as $aState) $jsonStates[] = $aState->toCache();
            }
            
        }
        $partnerid = $app->getUserSession()->getUser()->partnerid;
        if ($partnerid != 0){
            $partner = $app->partnerStore()->getById($partnerid);
            $redirect = $partner->corepartner ? 'orderdashboardview' : 0;
        }else{
            $redirect = 0;
        }

        $usrobj = $app->userStore()->searchTable()->select()->where('id',$app->getUserSession()->getUserId())->one();
        $partnername = '';
        if($usrobj){
            $partnername = $usrobj->id;
            if($usrobj->partnerid != null || $usrobj->partnerid != ''){
                $parobj = $app->partnerStore()->searchTable()->select()->where('id', $usrobj->partnerid)->one();
                if($parobj){
                    $partnername = $parobj->name;
                }
            }
        }

        $displayname = $app->getUserSession()->getUsername();
        if($partnername != ''){
            $displayname = $partnername." - ".$app->getUserSession()->getUsername();
        }
            
        return preg_replace(
            array(
                '/##USERINFO##/', '/##LOCALE##/', '/##TRANSLATIONS##/', '/##PROJECTBASE##/', '/##CHECKER##/', '/##USERACTIVITYLOG##/'
            ),
            array(
                json_encode(
                    array(
                        'a' => $app->getUserSession()->getUsername(), 
                        'b' => $jsonPermission,
                        'c' => $jsonStates,
                        'd' => $app->getUserSession()->getUserId(),
                        'e' => $app->getUserSession()->getUserType(),
                        'f' => $this->getEnvVariables($app),
                        'g' => $redirect,
                        'h' => $displayname,
                        'i' => $partnername,
                        'j' => $this->canEditUserPartner($app),
                        'k' => $userId,
                        'l' => $partnerid,
                    )
                ),
                $locale,
                $translations,
                $app->getConfig()->{'projectBase'},
                $app->getOtcChecker(),
                $app->getOtcUserActivityLog()
            ), $tmpData);            
    
                    // Unused e
                    //'e' => $app->getUserSession()->getUser()->branchid)), $tmpData);
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

    function getEnvVariables($app){
        // EXTJS access -> snap.app.info..

        // production 
        $r = ["env" => 'prod'];
        // development
        // Overwrite all if true
        if (preg_match('/(1|on|yes)/i', $app->getConfig()->development)){
            $r = ["env" => 'dev'];
            // $r = ["env" => 'dev', "version" => '1.0.0DEMO'];
            // eg. $r ["files" => '/path/'];
            // eg. $r ["variable" => 'var'];
        }
        return $r;
    }
	
	function canEditUserPartner ($app)
	{
		if (preg_match('/(1|on|yes)/i', $app->getConfig()->{'otc.edit.user.partner'})) {
            return true;
        }
        
        return false;
	}

}
