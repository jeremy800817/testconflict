<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use \Snap\object\MyMonthlyStorageFee;
use \Snap\object\OtcOutstandingStorageFeeJob;
use \Snap\api\casa\BaseCasa;
use \Snap\object\OtcManagementFee;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class BsnMonthlyManagementFeeReAttemptJob extends basejob
{
	/**
     * outstading management fee
     * @var array
     */
	protected $outStandingManagementFee = [];
	
	/**
     * api deduct attempt times
     * @var Integer
     */
	private $attempt = 4;
	
	/**
     * api deduct type
     * @var constant
     */
	private $deductType = OtcOutstandingStorageFeeJob::DEDUCTTYPE_CRONJOB;

	/**
	 * Perform job processing for active monthly management fees, updating outstanding storage fee jobs.
	 *
	 * @param App $app The application instance.
	 * @param array $params An optional array of parameters.
	 *
	 * @return void
	 */	
    public function doJob($app, $params = array())
    {
		$response = array(
            'success' => false,
            'data' => '',
            'error_code' => '',
            'error_message' => '',
        );
		
		$this->getActiveManagementFee($app, $params);
		
		$query = $app->mymonthlystoragefeeStore()->searchTable()
				->select(['accountholderid', 'amount', 'chargedon', 'xau'])
				->addFieldMax('msf_refno', 'msf_refno')
				->where('status', MyMonthlyStorageFee::STATUS_FAILED)
				->groupBy('accountholderid');
				
		if ($params['accountholderid']) {
			$query->where('accountholderid', $params['accountholderid']);
			$this->deductType = OtcOutstandingStorageFeeJob::DEDUCTTYPE_MANUAL;
		}

		$monthlyManagementFees = $query->get();
		
		if ($monthlyManagementFees) {
			foreach ($monthlyManagementFees as $monthlyManagementFee) {
				$paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $monthlyManagementFee->refno);
				$previousOutStandingAmount = $this->getPreviousOutStandingAmount($app, $monthlyManagementFee->accountholderid);
				if (0 < $previousOutStandingAmount) {
					$paymentDetail->amount = $previousOutStandingAmount;
					$monthlyManagementFee->amount = $previousOutStandingAmount;
				}
				$accountHolder = $app->myaccountholderStore()->getById($monthlyManagementFee->accountholderid);
				$outStandingStorageFeeJob = $this->addOutstandingStorageFeeJob($app, $monthlyManagementFee, $accountHolder);
				
				$response = $this->callCasaApi($app, $accountHolder, $paymentDetail, $outStandingStorageFeeJob);
			}
		}
		
		return $response;
    }
	
	/**
	 * Adds an outstanding storage fee job based on the provided monthly management fee and account holder information.
	 *
	 * @param App $app The application instance.
	 * @param MyMonthlyStorageFee $monthlyManagementFee The monthly management fee for which the outstanding storage fee job is created.
	 * @param MyAccountHolder $accountHolder The account holder associated with the outstanding storage fee job.
	 *
	 * @return OtcOutstandingStorageFeeJob The added outstanding storage fee job instance.
	 */	
	function addOutstandingStorageFeeJob ($app, $monthlyManagementFee, $accountHolder)
	{
		$actualyChargedMonth = new \DateTime($monthlyManagementFee->chargedon->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
		$actualyChargedMonth->modify('- 1 day');
		
		$outstandingStorageFeeJob = $app->otcoutstandingstoragefeejobStore()
									->create([
										'accountholderid' => $accountHolder->id,
										'refno' => $monthlyManagementFee->refno,
										'code' => '',
										'desc' => 'Monthly Management Fee - ' . $actualyChargedMonth->format('M Y'),
										'amount' => $monthlyManagementFee->amount,
										'xau' => $monthlyManagementFee->xau,
										'deducttype' => $this->deductType,
										'status' => OtcOutstandingStorageFeeJob::STATUS_INACTIVE
									]);

		return $app->otcoutstandingstoragefeejobStore()->save($outstandingStorageFeeJob);
	}
	
	/**
	 * Calls the Casa API to initialize a transaction, updates the status of the outstanding storage fee job, and flags the monthly management fee.
	 *
	 * @param App $app The application instance.
	 * @param MyAccountHolder $accountHolder The account holder associated with the transaction.
	 * @param MyPaymentDetail $paymentDetail The payment detail for the transaction.
	 * @param OtcOutstandingStorageFeeJob $outStandingStorageFeeJob The outstanding storage fee job to be updated.
	 *
	 * @return void
	 */
	function callCasaApi ($app, $accountHolder, $paymentDetail, $outStandingStorageFeeJob)
	{
		$className = $app->getConfig()->{'otc.casa.api'};
		$api = BaseCasa::getInstance($className);
		$apiMethod = 'deductManagementFee';
		$actualyChargedMonth = new \DateTime('now', new \DateTimeZone('UTC'));
		$actualyChargedMonth->modify('- 1 month');
		$paymentRef = 'GOLD MGT FEE ' . $actualyChargedMonth->format('M Y');
		$apiParams = [$accountHolder, $paymentDetail, $paymentRef];
		$response = call_user_func_array(array($api, $apiMethod), $apiParams);
		
		if ($response['success']) {
			$outStandingStorageFeeJob->status = OtcOutstandingStorageFeeJob::STATUS_ACTIVE;
			$this->flagOutStandingManagementFee($app);
		} else {
			$outStandingStorageFeeJob->status = OtcOutstandingStorageFeeJob::STATUS_FAILED;
			$outStandingStorageFeeJob->code = $response['error_message'];
		}
		
		$app->otcoutstandingstoragefeejobStore()->save($outStandingStorageFeeJob, ['status']);
		
		return $response;
	}
	
	/**
	 * Flags the monthly management fee with the specified status and saves it to the storage.
	 *
	 * @param App $app The application instance.
	 * @param MonthlyManagementFee $monthlyManagementFee The monthly management fee to flag.
	 * @param string $status The status to set for the monthly management fee.
	 * 
	 * @return void
	 */
	function flagMonthlyManagmentFee ($app, $monthlyManagementFee, $status)
	{
		$monthlyManagementFee->status = $status;
		$app->mymonthlystoragefeeStore()->save($monthlyManagementFee, ['status']);
	}
	
	/**
	 * Flag outstanding management fees as paid.
	 *
	 * @param $app The application instance.
	 * @return void
	 */
	function flagOutStandingManagementFee ($app)
	{
		if ($this->outStandingManagementFee) {
			foreach ($this->outStandingManagementFee as $id) {
				$outStandingManagementFee = $app->mymonthlystoragefeeStore()->getById($id);
				$outStandingManagementFee->status = MyMonthlyStorageFee::STATUS_PAID;
				$app->mymonthlystoragefeeStore()->save($outStandingManagementFee, ['status']);
			}
		}
	}
	
	/**
	 * Retrieves the management fee period based on specified parameters.
	 *
	 * This method queries the OTC management fee store to fetch the active management fee and
	 * sets the period and attempt properties of the current instance accordingly.
	 *
	 * @param $app    The application instance.
	 * @param array                 $params Additional parameters for filtering (if any).
	 *
	 * @return void
	 */
	private function getActiveManagementFee ($app, $params)
	{
		$managementFees = $app->otcmanagementfeeStore()->searchTable()
						->select()
						->where('status', OtcManagementFee::STATUS_ACTIVE)
						->limit(1)
						->execute();
		
		if ($managementFees) {
			if ($managementFees[0]->attempt) {
				$this->attempt = $managementFees[0]->attempt;
			}
		}
	}
	
	/**
	 * Get the total Casa deduction attempts for the specified reference number.
	 *
	 * @param App $app The application instance.
	 * @param string $refNo The reference number for which to get the Casa deduction attempts.
	 * 
	 * @return int The total number of Casa deduction attempts.
	 */
	private function getCasaDeductAttempt ($app, $refNo)
	{
		$totalAttempt = $app->otcoutstandingstoragefeejobStore()
						->searchTable()
						->select()
						->where('refno', $refNo)
						->whereIn('deducttype', [OtcOutstandingStorageFeeJob::DEDUCTTYPE_MONTHLY, OtcOutstandingStorageFeeJob::DEDUCTTYPE_CRONJOB])
						->count();
						
		return $totalAttempt;
	}
	
	/**
	 * Get the total outstanding amount from failed monthly storage fees for a given account holder.
	 *
	 * @param $app The application instance.
	 * @param int $accountHolderId The ID of the account holder.
	 * @return float The total outstanding amount from failed monthly storage fees.
	 */
	private function getPreviousOutStandingAmount ($app, $accountHolderId)
	{
		$previousOutStandingAmount = 0;
		
		$failedManagementFees = $app->mymonthlystoragefeeStore()
								->searchTable()
								->select()
								->where('accountholderid', $accountHolderId)
								->where('status', MyMonthlyStorageFee::STATUS_FAILED)
								->get();
		if ($failedManagementFees) {
			foreach ($failedManagementFees as $failedManagementFee) {
				$previousOutStandingAmount += $failedManagementFee->amount;
				array_push($this->outStandingManagementFee, $failedManagementFee->id);
			}
		}
		
		return $previousOutStandingAmount;
	}

    function describeOptions()
    {
        return [];
    }
}
