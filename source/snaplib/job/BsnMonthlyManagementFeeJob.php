<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use \Snap\object\Partner;
use \Snap\object\MyAccountHolder;
use \Snap\object\MyMonthlyStorageFee;
use \Snap\object\OtcManagementFee;
use \Snap\object\MyPaymentDetail;
use \Snap\object\OtcOutstandingStorageFeeJob;
use \Snap\object\MyLedger;
use \Snap\api\casa\BaseCasa;
use Snap\util\storagefee\LastTwoMonthBalanceChargeStrategy;

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
class BsnMonthlyManagementFeeJob extends basejob
{
	/**
     * outstading management fee
     * @var array
     */
	protected $outStandingManagementFee = [];
	
	/**
     * @var IStorageFeeChargeStrategy|null $chargeStrategy 
     * The strategy used to calculate the chargeable weight.
     */
	private $chargeStrategy;
	
	/**
	 * Perform the job processing for active partners, calculating and managing monthly fees.
	 *
	 * @param App $app The application instance.
	 * @param array $params An optional array of parameters.
	 *
	 * @return void
	 */	
    public function doJob($app, $params = array())
    {
		$chargedon = new \DateTime('now', $app->getUserTimezone());
		if (isset($params['chargedon']) && 0 < strlen($params['chargedon'])) {
			$chargedon = new \DateTime($params['chargedon'], new \DateTimeZone('UTC'));
			$chargedon->setTimezone($app->getUserTimezone());
		}
		$partners = $app->partnerStore()
					->searchTable()
					->select()
					->where('status', Partner::STATUS_ACTIVE)
					->get();
		if ($partners) {
			foreach ($partners as $partner) {
				$product = $app->productStore()->getByField('code', 'DG-999-9');
				$priceProvider = $app->priceProviderStore()->getForPartnerByProduct($partner, $product);
				$accountHolders = $app->myaccountholderStore()->searchTable()
								->select()
								->where('status', MyAccountHolder::STATUS_ACTIVE)
								->where('partnerid', $partner->id)
								->get();
				if ($accountHolders) {
					foreach ($accountHolders as $accountHolder) {
						$monthlyManagementFee = $this->addMonthlyManagementFee($app, $accountHolder, $partner, $priceProvider, $chargedon);
						if ($monthlyManagementFee) {
							$paymentDetail = $this->addPaymentDetail($app, $monthlyManagementFee, $accountHolder);
							$previousOutStandingAmount = $this->getPreviousOutStandingAmount($app, $monthlyManagementFee->accountholderid);
							if (0 < $previousOutStandingAmount) {
								$paymentDetail->amount += $previousOutStandingAmount;
								$monthlyManagementFee->amount += $previousOutStandingAmount;
							}
							
							$outStandingStorageFeeJob = $this->addOutstandingStorageFeeJob($app, $monthlyManagementFee, $accountHolder);
							
							$this->callCasaApi($app, $accountHolder, $paymentDetail, $outStandingStorageFeeJob, $monthlyManagementFee);
						}
					}
				}
			}
		}
    }

	/**
	 * Adds a monthly management fee to the system.
	 *
	 * @param MyApp $app The application instance.
	 * @param AccountHolder $accountHolder The account holder for whom the fee is added.
	 * @param Partner $partner The partner associated with the fee.
	 * @param PriceProvider $priceProvider The price provider for fee calculations.
	 * @param DateTime $chargedon The date and time when the fee is charged.
	 *
	 * @return bool Returns false if the current balance is less than or equal to 0, indicating the fee addition was unsuccessful.
	 */
	function addMonthlyManagementFee ($app, $accountHolder, $partner, $priceProvider, $chargedon)
	{
		$chargeableGram = $this->getChargeableGram($app, $accountHolder, $partner, $chargedon);
		if (0 >= $chargeableGram) return false;
		
		$amount = $this->calculateMonthlyManagementFee($app, $chargeableGram, $partner);
		if (0 >= $amount) return false;
		$refNo = $app->mygtpstorageManager()->generateRefNo('SF', $app->mymonthlystoragefeeStore());
		
		$lastPriceOfDayDataKey = '{LastPriceOfTheDay}:' . $priceProvider->id;
		$priceStreamCache = $app->getCache($lastPriceOfDayDataKey);
		$lastPriceOfDay = $app->priceStreamStore()->create();
		$lastPriceOfDay->fromCache($priceStreamCache);
		$priceClose = $partner->calculator()->round($lastPriceOfDay->companysellppg);
		$xau = $partner->calculator()->divide($amount, $priceClose);
		$until = clone $chargedon;
		$until->setTimezone($app->getServerTimezone());
			
		$monthlyManagementFee = $app->mymonthlystoragefeeStore()->create([
			'xau' => 0,
			'price' => $priceClose,
			'amount' => $amount,
			'adminfeexau' => '0',
			'storagefeexau' => '0',
			'accountholderid' => $accountHolder->id,
			'refno' => $refNo,
			'accruedmonthlyfee' => $accountHolder->getCurrentGoldBalance($until),
			'pricestreamid' => '0',
			'status' => MyMonthlyStorageFee::STATUS_ACTIVE,
			'chargedon' => $chargedon->format('Y-m-d H:i:s')
		]);

		return $app->mymonthlystoragefeeStore()->save($monthlyManagementFee);
	}
	
	/**
     * Calculates the chargeable grams for the storage fee by using the selected strategy.
     * 
     * @param mixed $app             The application context, providing configuration and stores.
     * @param mixed $accountHolder   The account holder whose balance is used for the calculation.
     * @param mixed $partner         The partner entity involved in the transaction.
     * @param \DateTime $chargedon   The date when the charge is applied.
     * 
     * @return int The calculated chargeable weight in grams.
     */
	private function getChargeableGram ($app, $accountHolder, $partner, $chargedon)
	{
		$this->getStorageFeeChargeStrategy();
		return $this->chargeStrategy->calculateChargeableGram($app, $accountHolder, $partner, $chargedon);
	}
	
	/**
	 * Retrieves the total gold bought by the account holder during the current month.
	 *
	 * @param object $app The application instance that provides access to various services.
	 * @param object $accountHolder The account holder object, which includes an ID and other details.
	 * @param \DateTime $chargedon The DateTime object representing the date on which the charge is being calculated.
	 * 
	 * @return float The total grams of gold bought by the account holder in the current month.
	 */
	function getCurrentMonthBuyGoldBalance ($app, $accountHolder, $chargedon)
	{
		$ledgerStore = $app->myledgerStore();
		$ledgerHdl = $ledgerStore->searchTable(false);
		$p = $ledgerStore->getColumnPrefix();
		$startDateTime = clone $chargedon;
		$startDateTime->modify("- 1 month");
		$startDateTime->setTimezone($app->getServerTimezone());
		$endDateTime = clone $chargedon;
		$endDateTime->setTimezone($app->getServerTimezone());
		
		$ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("SUM({$p}credit) AS sum")])
							   ->where('accountholderid', $accountHolder->id)
							   ->where('transactiondate', '>=', $startDateTime->format('Y-m-d H:i:s'))
							   ->where('transactiondate', '<', $endDateTime->format('Y-m-d H:i:s'))
							   ->where('status', MyLedger::STATUS_ACTIVE);
							   
		return $ledgerHdl->one()['sum'] ?? 0;
	}

	/**
	 * Calculates the monthly management fee based on the chargeable grams of gold and applicable OTC management fees.
	 *
	 * @param object $app The application instance that provides access to various services.
	 * @param float $chargeableGram The chargeable grams of gold for the account holder.
	 * @param object $partner The partner object, which includes a calculator method for performing calculations.
	 * 
	 * @return float The calculated monthly management fee.
	 */
	function calculateMonthlyManagementFee ($app, $chargeableGram, $partner)
	{
		$monthlyManagementFee = 0;
		$otcManagementFees = $app->otcmanagementfeeStore()
							->searchTable()
							->select()
							->where('status', OtcManagementFee::STATUS_ACTIVE)
							->get();

		if ($otcManagementFees) {
			$matchOtcManagementFee = null;
			$now = new \DateTime();
			foreach ($otcManagementFees as $otcManagementFee) {
				$isMatch = true;
				
				if ($otcManagementFee->avgdailygoldbalancegramfrom > 0 && $chargeableGram < $otcManagementFee->avgdailygoldbalancegramfrom) {
					$isMatch = false;
				}
				
				if ($otcManagementFee->avgdailygoldbalancegramto > 0 && $chargeableGram > $otcManagementFee->avgdailygoldbalancegramto) {
					$isMatch = false;
				}
				
				if ($otcManagementFee->starton !== null && $now < $otcManagementFee->starton) {
					$isMatch = false;
				}
				
				if ($otcManagementFee->endon !== null && $now > $otcManagementFee->endon) {
					$isMatch = false;
				}
				
				if ($isMatch) {
					$matchOtcManagementFee = $otcManagementFee;
					break;
				}
			}
			
			if (null !== $matchOtcManagementFee) {
				$monthlyManagementFee = $partner->calculator()->multiply($chargeableGram, $matchOtcManagementFee->feeamount);
			}
		}
		
		return $monthlyManagementFee;
	}

	/**
	 * Adds a payment detail for the provided monthly management fee and account holder information.
	 *
	 * @param App $app The application instance.
	 * @param MyMonthlyStorageFee $monthlyManagementFee The monthly management fee for which the payment detail is created.
	 * @param MyAccountHolder $accountHolder The account holder associated with the payment detail.
	 *
	 * @return MyPaymentDetail The added payment detail instance.
	 */	
	function addPaymentDetail ($app, $monthlyManagementFee, $accountHolder)
	{
		$now = new \DateTime('now', $app->getUserTimezone());
		
		$actualyChargedMonth = new \DateTime($monthlyManagementFee->chargedon->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
		$actualyChargedMonth->modify('- 1 day');
		
		$payment = $app->mypaymentdetailStore()
					->create([
						'amount' => $monthlyManagementFee->amount,
						'customerfee' => 0.00,
						'accountholderid' => $accountHolder->id,
						'paymentrefno' => $app->mygtpstorageManager()->generateRefNo("P", $app->mypaymentdetailStore()),
						'sourcerefno'  => $monthlyManagementFee->refno,
						'status'       => MyPaymentDetail::STATUS_PENDING_PAYMENT,
						'productdesc'  => "Monthly Management Fee - " . $actualyChargedMonth->format('M Y'),
						'transactiondate' => $now,
						'verifiedamount' => 0.00,
						'priceuuid' => 0
					]);

		return $app->mypaymentdetailStore()->save($payment);
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
										'deducttype' => OtcOutstandingStorageFeeJob::DEDUCTTYPE_MONTHLY,
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
	 * @param MyMonthlyStorageFee $monthlyManagementFee The monthly management fee to be flagged.
	 *
	 * @return void
	 */
	function callCasaApi ($app, $accountHolder, $paymentDetail, $outStandingStorageFeeJob, $monthlyManagementFee)
	{
		$className = $app->getConfig()->{'otc.casa.api'};
		$api = BaseCasa::getInstance($className);
		$apiMethod = 'deductManagementFee';
		$actualyChargedMonth = new \DateTime($monthlyManagementFee->chargedon->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
		$actualyChargedMonth->modify('- 1 day');
		$paymentRef = 'GOLD MGT FEE ' . $actualyChargedMonth->format('M Y');
		$apiParams = [$accountHolder, $paymentDetail, $paymentRef];
		$response = call_user_func_array(array($api, $apiMethod), $apiParams);
		
		if ($response['success']) {
			$outStandingStorageFeeJob->status = OtcOutstandingStorageFeeJob::STATUS_ACTIVE;
			$this->flagMonthlyManagmentFee($app, $monthlyManagementFee, MyMonthlyStorageFee::STATUS_PAID);
			$this->flagOutStandingManagementFee($app);
		} else {
			$outStandingStorageFeeJob->status = OtcOutstandingStorageFeeJob::STATUS_FAILED;
			$outStandingStorageFeeJob->code = $response['error_message'];
			$this->flagMonthlyManagmentFee($app, $monthlyManagementFee, MyMonthlyStorageFee::STATUS_FAILED);
		}
		
		$app->otcoutstandingstoragefeejobStore()->save($outStandingStorageFeeJob, ['status']);
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
	 * @param \YourNamespace\AppClass $app The application instance.
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
	
	/**
     * Initializes and sets the storage fee charge strategy.
     * 
     * @return void
     */
	private function getStorageFeeChargeStrategy ()
	{
		$this->chargeStrategy = new LastTwoMonthBalanceChargeStrategy;
	}

    function describeOptions()
    {
        return [];
    }

}
