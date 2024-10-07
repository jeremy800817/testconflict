<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use \Snap\api\casa\BaseCasa;
use \Snap\object\ApiJob;
use \Snap\object\OtcOutstandingStorageFeeJob;
use \Snap\object\MyPaymentDetail;
use \Snap\object\OtcManagementFee;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Chen <chen.teng.siang@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class MyGtpResendOrderJob extends basejob
{
	
	/**
     * api job period in day
     * @var Integer
     */
	private $jobPeriod = 0;
	
    public function doJob($app, $params = array())
    {
		xdebug_break();
		$this->log("[Resend Transaction Process] ########## Start Resend Transaction Process ##########", SNAP_LOG_DEBUG);
		$partner = $app->partnerStore()->getById($params['partnerId']);
		if(!$params['manual']){

			$missingTransaction = $this->getIntegrityData($app, $partner->code);
			$this->log("[Resend Transaction Process] Missing Transaction : ". json_encode($missingTransaction), SNAP_LOG_DEBUG);
		}

		$now = new \DateTime('now', $app->getServerTimezone());
		$now = $now->setTimezone($app->getUserTimezone());
		$datenow = $now->format('Y-m-d');
		$yesterday = $now->modify('-1 day')->format('Y-m-d');
		$dateArr = [
			'datenow' => $datenow,
			'yesterday' => $yesterday,
		];
		
		if($params['manual']){

			if($params['datestart'] && $params['dateend']) {
				
				$goldTrxs = $app->mygoldtransactionStore()->searchView()->select()
					->where('ordpartnerid', $partner->id)
					->andWhere('createdon', '>=', $params['datestart'])
					->andWhere('createdon', '<=', $params['dateend'])
					->execute();

				foreach($goldTrxs as $goldTrx) {
					$this->log("[Resend Transaction Process] Missing Transaction To Be Transferred Manually : ". $goldTrx->refno, SNAP_LOG_DEBUG);
					$app->apiManager()->transferOrderBetweenDb($goldTrx);
				}
			} else {
				$refNoArr = explode(",", $params['refno']);
				foreach($refNoArr as $missingTransactions){
					$goldTrx = $app->mygoldtransactionStore()->searchView()->select()
						->where('refno', $missingTransactions)
						->one();
					
					$this->log("[Resend Transaction Process] Missing Transaction To Be Transferred Manually : ". $goldTrx->refno, SNAP_LOG_DEBUG);
					$app->apiManager()->transferOrderBetweenDb($goldTrx);
				}
			}
		}
		else if($missingTransaction['success']){

			foreach($dateArr as $date){
				$refNoArr = explode(", ", $missingTransaction['data'][$date]['refno']);
				if(preg_match('/^GT.*/i',$refNoArr[0])){
					foreach($refNoArr as $missingTransactions){

						$missingTransactions = explode('_', $missingTransactions);

						$goldTrx = $app->mygoldtransactionStore()->searchView()->select()
							->where('refno', $missingTransactions[0])
							->one();

						if($goldTrx){
							$this->log("[Resend Transaction Process] Missing Transaction To Be Transferred : ". $goldTrx->refno, SNAP_LOG_DEBUG);
							$app->apiManager()->transferOrderBetweenDb($goldTrx);
						}else{
							$this->log("[Resend Transaction Process] Transaction did not exist in database : ". $goldTrx->refno, SNAP_LOG_DEBUG);
						}
					}
				}
			}
		}
		
		$this->log("[Resend Transaction Process] ########## End Resend Transaction Process ##########", SNAP_LOG_DEBUG);
    }

	/*Call Api to get missing transaction*/
	function getIntegrityData($app, $partnerCode){

		/*To test for setcache manually*/

		// $date = date('Ymd');
		// $refNo = array(
		// 	'GT1',
		// 	'GT2',
		// 	'GT3',
		// 	'GT4'
		// );
		// $data[$date]['refno'] = implode(",", $refNo);
		// $data[$date]['total'] = count($refNo);

		// $integrityDataKey = 'transactions_integrity_'. $date;
		// $now = new \DateTime('now', $app->getServerTimezone());
		// $timestamp = $now->getTimestamp();
		// $expireTimestamp = strtotime('tomorrow 10:00:00');
		// $expire = $expireTimestamp - $timestamp;
		// $app->setCache($integrityDataKey, json_encode($data[$date]), $expire);

		// $integrityDataCache = $app->getCache($integrityDataKey, json_encode($data['20231128']), $expire);

		/*End*/
		if(!($app->getConfig()->{'snap.environtment.development'})){
			$apiurl = $app->getConfig()->{'mygtp.api.url.prod'};
		}else{
			$apiurl = $app->getConfig()->{'mygtp.api.url.dev'};
		}

		$curl = curl_init();
		//echo "start api";
		curl_setopt_array($curl, array(
			CURLOPT_URL => $apiurl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>'{
				"version": "1.0my",
				"action": "getintegritydata",
				"partner": "'.$partnerCode.'"
			}',
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json'
			),
		));

		//to skip cert at localhost only
		// curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

		/*To check api error if any*/
		$response = curl_exec($curl);
		if (curl_errno($curl)) {
			$error_msg = curl_error($curl);

			$message = "[Resend Transaction Process] Unable get missing transaction - Response:". $error_msg;
			$this->log($message, SNAP_LOG_DEBUG);
			$this->addErrorLog($message);
		}
		curl_close($curl);

		$data = json_decode($response, true);

		return $data;
	}

    function describeOptions()
    {
        return [];
    }
}
