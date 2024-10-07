<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\rebateConfig;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mygoldtransactionHandler extends CompositeHandler {

	function __construct(App $app) {
		parent::__construct('/root/bmmb', 'goldtransaction');      

		$this->mapActionToRights('editReferenceNo', 'list');

		$this->mapActionToRights('list', 'list');

		$this->mapActionToRights('updatePendingRefundStatus', 'list');

		$this->app = $app;

		$myGoldTransactionStore = $app->mygoldtransactionFactory();
		$this->addChild(new ext6gridhandler($this, $myGoldTransactionStore, 1));
	}

	function editReferenceNo($app, $params){

		try{
			//$disbursements = $app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', 'IN', $params['refno'])->execute();
			for ($x = 0; $x < sizeof($params['orderid']); $x++) {
				//print_r($disbursements)
				//($disbursementRefNo, $gatewayRefNo, $bankRefNo, $transactionDate, $orderId)
				$disbursement = $this->app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', $params['refno'][$x])->one();

				$return = $this->app->myGtpDisbursementManager()->manualConfirmDisbursement($disbursement, $params['bankrefno'][$x], $params['verifiedamount'][$x]);
				
				/*
				foreach ($disbursements as $disbursement){
					$return = $this->app->myGtpDisbursementManager()->editReferenceNumber($disbursement, $params['gatewayrefno'], $params['bankrefno'], $params['transactiondate']);
				}
				if ($return){
					return $return;
				}*/
			}
			
			echo json_encode(['success' => true,]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
		}
	}

	function updatePendingRefundStatus($app, $params){
		try{
			$goldTransaction = $app->myGoldTransactionStore()->getById($params['id']);
			$gtManager = $app->myGtpTransactionManager();
			$data = $gtManager->updatePendingRefundStatus($goldTransaction, $params['status']);
			if($data['success'] == true){
				return json_encode(array('success' => true, 'message' => 'Record updated'));
			}
			else{
				return json_encode(array('success' => false, 'message' => $data['message']));
			}
		}
		catch (\Exception $e) {
            return json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
		
	}

}
