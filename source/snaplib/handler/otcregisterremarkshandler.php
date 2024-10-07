<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use DateTime;
use Snap\App;
use Snap\object\OtcUserActivityLog;
use Snap\object\OtcRegisterRemarks;
use Snap\object\MyLocalizedContent;
use Snap\sqlrecorder;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */

class otcregisterremarkshandler extends CompositeHandler
{
    function __construct(App $app)
    {   
        $this->mapActionToRights('list', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;');
        $this->mapActionToRights('approveregister' ,  '/all/access');
        $this->mapActionToRights('rejectregister' ,  '/all/access');
        $this->mapActionToRights('registerapproval' ,  '/all/access');
        $this->mapActionToRights('checkapprovalstatus' ,  '/all/access');

        $this->app = $app;
        $currentStore = $app->otcregisterremarksStore();
        $this->sqlRecorder = new sqlrecorder();
        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore,1));
        } 
        else {
            $this->addChild(new ext6gridhandler($this,$currentStore,1));
        }
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
		$app = App::getInstance();

		// Start Query
		// Add new permissions for OTC
		if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
			$partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
        }
		else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
        }
		else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
			$partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }
		
		if (isset($params['filter'])){
			// check filter case 
			switch($params['filter']){
				case 'approval':
					// filter for approval view
					$sqlHandle->andWhere('status', OtcRegisterRemarks::PENDING);
					break;
			}
		}

        return array($params, $sqlHandle, $fields);
    }

    function onPreListing($objects, $params, $records) {

		$app = App::getInstance();

		foreach($records as $key => $record) {

            $createuser = $app->userStore()->getById($records[$key]['createdby']);
            $moduser = $app->userStore()->getById($records[$key]['modifiedby']);
            $records[$key]['createdbyname'] = $createuser->username;
            $records[$key]['modifiedbyname'] = $moduser->username;
            
		}
		return $records;
	}

    public function registerapproval($app, $params)
    {
        $userId = $app->getUserSession()->getUserId();
        $user = $app->userStore()->getById($userId);

        if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }

        $finalPartnerId = $user->partnerid != 0 ? $user->partnerid : $partnerId;

        $identityno = $params['ic_no'] ?? 'Foreigner';

        if($params['type'] == OtcRegisterRemarks::TYPE_REGISTER){
            $type = OtcRegisterRemarks::TYPE_REGISTER;
        }else  if($params['type'] == OtcRegisterRemarks::TYPE_BUY){
            $type = OtcRegisterRemarks::TYPE_BUY;
        }else  if($params['type'] == OtcRegisterRemarks::TYPE_SELL){
            $type = OtcRegisterRemarks::TYPE_SELL;
        }else  if($params['type'] == OtcRegisterRemarks::TYPE_CONVERSION){
            $type = OtcRegisterRemarks::TYPE_CONVERSION;
        }

        $this->logDebug("Creating data for to get approval for failed biometric " .strtolower($type). " - ".$identityno);
        $registerremarks = $this->app->otcregisterremarksStore()->create([
            'type' => $type,
            'mykadno' =>   $identityno,
            'remarks' => $params['remarks'],
            'status' =>  OtcRegisterRemarks::PENDING,
        ]);

        $registerremarks = $this->app->otcregisterremarksStore()->save($registerremarks);

        $selectedremarkstoapproved =  $this->app->otcregisterremarksStore()->searchTable()->select()
                                    ->andWhere('mykadno' , $identityno)
                                    ->andWhere('remarks' , $params['remarks'])
                                    ->andWhere('createdby', $userId)
                                    ->andWhere('status', OtcRegisterRemarks::PENDING)
                                    ->orderby('id', 'desc')
                                    ->one();

        $notificationManager = $app->notificationManager();
        $urlparam = urlencode($selectedremarkstoapproved->id);

        // url code
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $currentPageURL = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        // $currentPageURL = $_SERVER['PHP_SELF'];
        $parsedUrl = parse_url($currentPageURL);
        $baseUrl = dirname($parsedUrl['path']);
        // print($localhostURL); exit;

        // $baseUrl = "http://localhost/index.php";
        if ($selectedremarkstoapproved) {
            $data = [
                'title'     => 'Title',
                'body'      => $type . ' Failed to be approved' . $selectedremarkstoapproved->mykadno . $selectedremarkstoapproved->remarks ?? null,
                'url'       => $currentPageURL .'/#approvalview=' . $urlparam,
                'partnerid' => $finalPartnerId,
            ];
        
            $notificationManager->postToNotificationChannel($data);
            $this->log(__METHOD__ . "(): Unable to proceed past notif manager");
            return json_encode(['success' => true, 'isawait' => true, 'id' => $selectedremarkstoapproved->id, 'partnerid' => $finalPartnerId]);
        } else {
            $this->log(__METHOD__ . "(): Registration not approved. No data available for processing.");
            return json_encode(['success' => false, 'message' => 'Registration not approved. No data available for processing.']);
        }
    }

    public function checkapprovalstatus ($app, $params)
    {   
        $Status = OtcRegisterRemarks::PENDING;

		try{
			if($params['id']){
				// search for registration failed record
				$registerremarkstatus = $this->app->otcregisterremarksStore()->searchTable()->select()
                                    ->where('id', $params['id'])
                                    ->one();
				$data = [];

				// if record found, check status
				if($registerremarkstatus){
					if($params['approve'] == 'yes'){
						$isPendingApproval = $Status == $registerremarkstatus->status ? true : false;
					}				
				}

				if($params['approve']){
					if(!$isPendingApproval){
						$data = [
							'id' 			        => $registerremarkstatus->id,
							'mykadno'          	    => $registerremarkstatus->mykadno,
                            'remarks'               => $registerremarkstatus->remarks,
                            'status'                => $registerremarkstatus->status,
						];
					}
				}
			}else{
				throw new \Exception("Please include search parameters");
			}


			echo json_encode(['success' => true, 'ispendingapproval' => $isPendingApproval, 'status' => $registerremarkstatus->status, 'record' => $data]);
		}catch (\Exception $e){
			$this->log("Failed to validate OTC Approval Status for Registration Without Biometric : ". $params['id'], SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
    }

    public function approveregister($app, $params){
		$response = ['success' => false, 'message' => 'initialize'];

		try{
			$approvalselectedlist = $app->otcregisterremarksStore()->searchTable()->select()
                                    ->where('id',$params['id'])
                                    ->andWhere('createdby', $params['user_id'])
                                    ->andWhere('mykadno' , $params['ic_no'])
                                    ->andWhere('remarks' , $params['register_remarks'])
                                    ->one();

			if($approvalselectedlist){
				$approvalselectedlist->status = OtcRegisterRemarks::APPROVED;
                $approvalselectedlist = $this->app->otcregisterremarksStore()->save($approvalselectedlist);
				$response['success'] = true;
				$response['message'] = "OK";
			}
			else{
				Throw new \Exception(print_r($params));
			}
		}
		catch(\Throwable $e){
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		echo json_encode($response);
	}

    public function rejectregister($app, $params){
		$response = ['success' => false, 'message' => 'initialize'];
		try{
			$rejectedselectedlist = $app->otcregisterremarksStore()->searchTable()->select()
                                    ->where('id',$params['id'])
                                    ->andWhere('createdby', $params['user_id'])
                                    ->andWhere('mykadno' , $params['ic_no'])
                                    ->andWhere('remarks' , $params['register_remarks'])
                                    ->one();
                                    
			if($rejectedselectedlist){
                $rejectedselectedlist->status = OtcRegisterRemarks::REJECTED;
                $rejectedselectedlist = $this->app->otcregisterremarksStore()->save($rejectedselectedlist);
				$response['success'] = true;
				$response['message'] = "OK";
			}
			else{
				Throw new \Exception(print_r($params));
			}
		}
		catch(\Throwable $e){
			$response['success'] = false;
			$response['message'] = $e->getMessage();
		}
		echo json_encode($response);
	}
}
