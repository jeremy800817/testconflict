<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Exception;
use Psr\Log\InvalidArgumentException;
use Snap\api\exception\ApiParamProductInvaliid;
use Snap\api\exception\CredentialInvalid;
use Snap\api\exception\GeneralException;
use Snap\api\exception\MyGtpAccountHolderMyKadExists;
use Snap\api\exception\MyGtpAccountHolderNoAccountNameOrNumber;
use Snap\api\exception\MyGtpAccountHolderNotActive;
use Snap\api\exception\MyGtpAccountHolderNotEnoughBalance;
use Snap\api\exception\MyGtpAccountHolderNotExist;
use Snap\api\exception\MyGtpAccountHolderWrongPin;
use Snap\api\exception\MyGtpActionNotPermitted;
use Snap\api\exception\MyGtpFpxCreateTxError;
use Snap\api\exception\MyGtpGoldSettlementMethodNotAllowed;
use Snap\api\exception\MyGtpInvalidAmount;
use Snap\api\exception\MyGtpInvalidStartDate;
use Snap\api\exception\MyGtpLoanInsufficientBalance;
use Snap\api\exception\MyGtpPartnerApiInvalid;
use Snap\api\exception\MyGtpPartnerInsufficientGoldBalance;
use Snap\api\exception\MyGtpPartnerSettingsNotInitialized;
use Snap\api\exception\MyGtpPriceValidationNotValid;
use Snap\api\exception\MyGtpProductInvalid;
use Snap\api\exception\MyGtpTransactionExists;
use Snap\api\fpx\BaseFpx;
use Snap\api\payout\BasePayout;
use Snap\api\wallet\BaseWallet;
use Snap\IObservable;
use Snap\IObservation;
use Snap\IObserver;
use Snap\object\MyAccountHolder;
use Snap\object\MyConversion;
use Snap\object\MyFee;
use Snap\object\MyGoldTransaction;
use Snap\object\MyGoldTransfer;
use Snap\object\MyGoldTransferLog;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyKYCSubmission;
use Snap\object\MyLedger;
use Snap\object\MyLoanTransaction;
use Snap\object\MyPartnerApi;
use Snap\object\MyPartnerSetting;
use Snap\object\MyPaymentDetail;
use Snap\object\Order;
use Snap\object\Partner;
use Snap\object\PriceStream;
use Snap\object\PriceValidation;
use Snap\object\Product;
use Snap\object\VaultItem;
use Snap\object\VaultLocation;
use Snap\TObservable;
use Snap\TLogging;
use Snap\api\casa\BaseCasa;

/**
 * This class handles all logic for MyGTP gold transactions
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpTransactionManager implements IObserver, IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;
    private $limit = 5000;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if (($changed instanceof BaseFpx || $changed instanceof BaseWallet || $changed instanceof BaseCasa) && $state->target instanceof MyPaymentDetail &&
            preg_match('/^GT.*/i', $state->target->sourcerefno)) {

            // FPX Gateway callback notification
            $paymentDetailStore = $this->app->mypaymentdetailStore();
            $paymentDetail = $paymentDetailStore->getById($state->target->id);
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());

            $paymentStatus = $state->target->status;
			$ledgerType = ($changed instanceof BaseCasa) ? MyLedger::TYPE_BUY_CASA : MyLedger::TYPE_BUY_FPX;
            if ($state->isConfirmAction() && MyPaymentDetail::STATUS_SUCCESS == $paymentStatus) {
                // Payment passed
                $this->onPaymentSuccess($paymentDetail, $ledgerType);
            } else if ($state->isCancelAction() && MyPaymentDetail::STATUS_CANCELLED == $paymentStatus) {
                // Payment cancelled
                $this->onPaymentFailed($paymentDetail);
            } else if ($state->isRejectAction() && MyPaymentDetail::STATUS_FAILED == $paymentStatus) {
                // Payment rejected
                $this->onPaymentFailed($paymentDetail);
            } else if ($state->isReverseAction() && MyPaymentDetail::STATUS_REFUNDED == $paymentStatus) {
                // Payment refunded
                $this->onPaymentRefunded($paymentDetail);
            } else {
                $this->log(__METHOD__. "(): Unable to handle payment notification. Action = {$state->action}, Payment detail ({$paymentDetail->paymentrefno}) status = {$paymentDetail->status}");
            }
        }
    }


    /**
     * Process gold transaction when success payment received
     * 
     * @param MyPaymentDetail   $payment    The payment object
     * 
     * @return void
     */
    public function onPaymentSuccess($payment, $ledgerType)
    {

        // Expired checking
        $expiry = new \DateTime('now', $this->app->getUserTimezone());
        $expiry->sub(\DateInterval::createFromDateString("5 minutes"));
        
        // If created more than 5 minutes then its has expired
        if ($payment->createdon->format('Y-m-d H:i:s') < $expiry->format('Y-m-d H:i:s')) {
            $this->log("Unable to confirm gold transaction for {$payment->sourcerefno} {$payment->paymentrefno} as it has expired", SNAP_LOG_ERROR);
            $goldTx = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno, [], false, 1);
            $order = $goldTx->getOrder();
            if (MyGoldTransaction::STATUS_CONFIRMED == $goldTx->status || Order::STATUS_CONFIRMED ==  $order->status) {
                $errorMessage = "Unable to confirm gold transaction for {$payment->sourcerefno} {$payment->paymentrefno} as it status confirmed. gold transaction status: {$goldTx->status}, order status : {$order->status}";
                $this->log($errorMessage, SNAP_LOG_ERROR);
                return;
            }
            $goldTx->status = MyGoldTransaction::STATUS_PENDING_REFUND;
            $this->app->mygoldtransactionStore()->save($goldTx);
            $order->status = Order::STATUS_CANCELLED;
            $this->app->orderStore()->save($order);

            /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
            $transferDb = $this->app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
            if($transferDb) $sendTrans = $this->sendTransactionBetweenDb($goldTx,$order,'pendingrefund');
            else $this->log("[Transfer DB Process] Transaction with refno ".$goldTx->refno." no need to trasnfer to other db because status mygoldtransaction ".$goldTx->status." is not PENDING REFUND", SNAP_LOG_DEBUG);
            /**/

            return;
        }
        
        /** @var MyGoldTransaction $goldTx*/
        $goldTx = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno, [], false, 1);
        $accHolder = $this->app->myaccountholderStore()->getById($goldTx->ordbuyerid);

        $this->logDebug(__METHOD__. "(): Payment success for gold transaction {$goldTx->refno}");

        // Confirm the gold transaction
        $goldTx = $this->confirmBookGoldTransaction($goldTx, $ledgerType);

        // Update accountholder initial investment status
        if (MyAccountHolder::INVESTMENT_NONE == $accHolder->investmentmade) {
            $this->logDebug("Updating account holder initial investment status to INVESTMENT_MADE");
            $accHolder->investmentmade = MyAccountHolder::INVESTMENT_MADE;
            $accHolder = $this->app->myaccountholderStore()->save($accHolder);
        }

        $this->logDebug("Finished updating gold transaction and ledger objects for successful payment $payment->paymentrefno");
    }

    /**
     * Process gold transaction when payment expired or rejected
     * 
     * @param MyPaymentDetail $payment 
     * 
     * @return void
     */
    public function onPaymentFailed($payment)
    {
        /** @var MyGoldTransaction $goldTx*/
        $goldTx = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno, [], false, 1);
        $this->logDebug(__METHOD__. "(): Payment failed for gold transaction {$goldTx->refno}");

        try {
            if (MyGoldTransaction::STATUS_PENDING_PAYMENT != $goldTx->status) {
                $this->logDebug(__METHOD__."(): Gold transaction ({$goldTx->refno}) not in pending status");
                return;
            }

            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $order = $this->app->orderStore()->getById($goldTx->orderid);
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());

            $order->status = Order::STATUS_EXPIRED;     // Cancel status reserved for MBB
            $order = $this->app->orderStore()->save($order);

            $goldTx->status = MyGoldTransaction::STATUS_FAILED;
            $goldTx->failedon = $now;
            $goldTx = $this->app->mygoldtransactionStore()->save($goldTx);

            $this->logDebug("Successfully failed gold transaction ({$goldTx->refno}), Order Ref : {$order->orderno}");

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();

                /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
                $transferDb = $this->app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
                if($transferDb) $sendTrans = $this->sendTransactionBetweenDb($goldTx,$order,'failed');
                else $this->log("[Transfer DB Process] Transaction with refno ".$goldTx->refno." no need to trasnfer to other db because status mygoldtransaction ".$goldTx->status." is not FAILED", SNAP_LOG_DEBUG);
                /**/
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Process gold transaction if payment refunded
     * 
     * @param MyPaymentDetail $payment
     * 
     * @return void
     */
    public function onPaymentRefunded($payment)
    {
        $this->logDebug(__METHOD__."() Payment {$payment->paymentrefno} was refunded.");
        return;

        // Refund not in initial planning. Details TBC

        /** @var MyGoldTransaction $goldTx*/
        $goldTx = $this->app->mygoldtransactionStore()->getByField('refno', $payment->sourcerefno, [], false, 1);
        $accHolder = $this->app->myaccountholderStore()->getById($goldTx->ordbuyerid);

        try {
            if (MyGoldTransaction::STATUS_PAID != $goldTx->status) {
                $this->logDebug(__METHOD__."(): Gold transaction ({$goldTx->refno}) not in completed status");
                return;
            }

            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            // TODO : How to handle refunded payments?
            $order = $this->app->orderStore()->getById($goldTx->orderid);
            // $order = $this->app->spotorderManager()->confirmBookOrder($order);

            $oldStatus = $goldTx->status;
            $now = new \DateTime('now', $this->app->getUserTimezone());
            $goldTx->status = MyGoldTransaction::STATUS_REVERSED;
            $goldTx->reversedon = $now;
            $goldTx = $this->app->mygoldtransactionStore()->save($goldTx);

            $ledger = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_REFUND_DG,
                'accountholderid'   => $order->buyerid,
                'partnerid'         => $accHolder->partnerid,
                'debit'             => $order->xau,
                'credit'            => 0.00,
                'refno'             => $goldTx->refno,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledger = $this->app->myledgerStore()->save($ledger);

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Books an order using price validation
     * 
     * @param MyAccountHolder $accountHolder    The account holder
     * @param Partner         $partner          Partner that the accountholder is registered with
     * @param Product         $product          The product involved in this transaction
     * @param string          $priceRef         The price validation
     * @param string          $buyOrSell        Order type COMPANYBUY/COMPANYSELL
     * @param string          $grams            Unit of gold in grams
     * @param string          $settlementMethod Settlement method
     * @param string          $campaignCode     The campaign code entered by user
     * @param string          $partnerData      Data from partner required for payment
     * @param string          $closeAccountTx   If tx is for account close, then skip checks
     * 
     * @return MyGoldTransaction
     */
    public function bookGoldTransaction($accountHolder, $partner, $product, $priceRef, $orderType, 
                              $grams, $settlementMethod, $apiVersion,
                              $pin, $fromAlert = false, $campaignCode = '', $partnerData = '', $closeAccountTx = false)
    {
        $this->logDebug(__METHOD__. "(): Initializing booking for gold transaction");

        if (! $closeAccountTx) {
            $this->validateBookGoldTxRequest($accountHolder, $partner, $product, $orderType,
                                             $priceRef, $grams, $settlementMethod, $pin, $apiVersion);
        }

        // Serial processing of booking & confirmation process due to inventory balance checking
        // For customer buy orders - bmmb
        $isCustomerBuy = $orderType == Order::TYPE_COMPANYSELL;
        if ($isCustomerBuy) {
            $this->checkPartnerBalanceSufficient($partner, $grams);
        }
        // Previously only check CustomerBuy, new common warehouse implementation will check both buy&sell - 01-10-2021 
        if ($partner->sharedgv){
            $this->log(__METHOD__."(): check sharedgv partner balance", SNAP_LOG_DEBUG);
            $checking = $this->app->bankvaultmanager()->checkSharedDgvBalanceToProceed($grams, $orderType);
            if (!$checking){
                $this->log(__METHOD__."(): check sharedgv partner balance return false", SNAP_LOG_DEBUG);
                throw MyGtpPartnerInsufficientGoldBalance::fromTransaction([]);
            }
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        if (!$settings) {
            throw MyGtpPartnerSettingsNotInitialized::fromTransaction($partner);
        }

        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }
		
		$memberType = $accountHolder->getAdditionalData()->category;

        try {
            // Create an order through spotordermanager
            $refid = $this->generateRefNo("GT", $this->app->mygoldtransactionStore());
            
            $order = $this->doBookSpotOrder($partner, $apiVersion, $refid, $orderType,
                                            $product, $priceRef, $grams, $campaignCode, $memberType);
                                            
            if (! $order) {
                throw GeneralException::fromTransaction(null, ['message' =>"Unable to create order. Transaction refno : $refid"]);
            }

            $this->logDebug(__METHOD__. "(): Creating gold transaction object");
            // Add entry to MyGoldTransaction table for account holder
            $calc = $partner->calculator();
            
            $price = $this->getPriceFromReference($priceRef, $orderType);
            $goldTransaction = $this->app->mygoldtransactionStore()
                                    ->create([
                                        'originalamount' => $order->amount,
                                        'settlementmethod' => $settlementMethod,
                                        'salespersoncode' => $accountHolder->referralsalespersoncode,
                                        'refno'          => $refid,
                                        'orderid'        => $order->id,
                                        'campaigncode'   => $campaignCode,
                                        'status'        => MyGoldTransaction::STATUS_PENDING_PAYMENT
                                    ]);
            
            // Modify order object for MYGTP related data
            $order->buyerid = $accountHolder->id;
            $order->isspot  = $fromAlert ? MyGoldTransaction::FROMALERT_YES : MyGoldTransaction::FROMALERT_NO;
            if (MyGoldTransaction::SETTLEMENT_METHOD_FPX == $settlementMethod) {
                // Positive amount
                $order->fee = $calc->add($order->fee, $settings->transactionfee);
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT == $settlementMethod) {
                // Negative amount
                $order->fee = $calc->add($order->fee, $settings->payoutfee);
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $settlementMethod) {
                // No fee
                $order->fee = $settings->walletfee;
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_LOAN == $settlementMethod) {
                $order->fee = 0;

                $amount = number_format(floatval($goldTransaction->originalamount) + floatval($order->fee), 2, '.', '');

                // If not enough balance throw
                if (0 > $accountHolder->loanbalance - $amount) {
                    $this->log(__METHOD__ . "(): Insufficient loan amount balance for account holder {$accountHolder->accountholdercode}", SNAP_LOG_ERROR);
                    throw MyGtpLoanInsufficientBalance::fromTransaction([], [
                        'message' => gettext("Insufficient loan amount balance.")
                    ]);
                }
                    
            }   else if (MyGoldTransaction::SETTLEMENT_METHOD_CASH == $settlementMethod || MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod) {
                // No fee
                $order->fee = 0;
            }

            // $order->amount  = $calc->add($order->amount, $order->fee);

            $goldTransaction = $this->app->mygoldtransactionStore()->save($goldTransaction);
            
            $goldTransaction->priceuuid = $priceRef; //this is to pass uuid price to add into paymentdetail
            $order = $this->app->orderStore()->save($order);

            // When Customer Sell Gold
            if (Order::TYPE_COMPANYBUY == $order->type) {
                
                // update by azam on 2021-09-14 if autosubmitorder is turn on, and  SAP return error, 
                // it will rollback confirmBookGoldTransaction and will not create ledger
                // to overcome this we need to put this here for CompanyBuy
				$ledgerType = (MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod) ? MyLedger::TYPE_SELL_CASA : MyLedger::TYPE_SELL;
                $ledgers = $this->createLedgersForGoldTransaction($goldTransaction, $ledgerType);
                $this->logDebug(__METHOD__. "(): Initializing payment for gold transaction {$goldTransaction->refno}");

                //print_r('test');exit;
                // Process payment using settlement method
                $payment = $this->initializeTransactionPayment($accountHolder, $goldTransaction, $order,
                                                            $partner, $product, $settlementMethod, $partnerData);
                
                // Throw error if payment was not successful
                if (false === $payment) {
                    $this->log(__METHOD__."(): Unable to create payment for gold transaction {$goldTransaction->refno}.", SNAP_LOG_ERROR);
                    throw MyGtpFpxCreateTxError::fromTransaction(null, []);
                }
            }
            
            // Save the gold transaction object
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }

        // When Customer Buy Gold
        if (Order::TYPE_COMPANYSELL == $order->type) {
            $this->logDebug(__METHOD__. "(): Initializing payment for gold transaction {$goldTransaction->refno}");
            // Process payment using settlement method
            $payment = $this->initializeTransactionPayment($accountHolder, $goldTransaction, $order,
                                                        $partner, $product, $settlementMethod, $partnerData);
                                                        
            // Immediately confirm transaction for loan
            if (MyGoldTransaction::SETTLEMENT_METHOD_LOAN == $settlementMethod) {
               $payment = $this->createLoanTransaction($accountHolder, $goldTransaction, $order);
            }

            // Throw error if payment was not successful
            if (false === $payment) {
                $this->log(__METHOD__."(): Unable to create payment for gold transaction {$goldTransaction->refno}.", SNAP_LOG_ERROR);
                throw MyGtpFpxCreateTxError::fromTransaction(null, []);
            }
        }
        $this->logDebug(__METHOD__."(): Finished booking process for gold transaction {$goldTransaction->refno}");
        $gdTx = $this->app->mygoldtransactionStore()->getById($goldTransaction->id);
	    return $gdTx;
    }

 /**
     * Books an order using price validation
     * 
     * @param MyAccountHolder $accountHolder    The account holder
     * @param Partner         $partner          Partner that the accountholder is registered with
     * @param Product         $product          The product involved in this transaction
     * @param string          $priceRef         The price validation
     * @param string          $buyOrSell        Order type COMPANYBUY/COMPANYSELL
     * @param string          $grams            Unit of gold in grams
     * @param string          $settlementMethod Settlement method
     * @param string          $campaignCode     The campaign code entered by user
     * @param string          $partnerData      Data from partner required for payment
     * @param string          $closeAccountTx   If tx is for account close, then skip checks
     * 
     * @return MyGoldTransaction
     */
    public function getCurrentAmount($partner, $uuid, $weight, $orderType)
    {
        $this->logDebug(__METHOD__. "(): Initializing booking for gold transaction");

        $price = $partner->calculator()->round($this->getPriceFromReference($uuid, $orderType));
        $amount = $partner->calculator()->multiply($weight, $price);
        return $amount;
    }
    
    /**
     * Books an order using price validation
     * 
     * @param MyAccountHolder $accountHolder    The account holder
     * @param Partner         $partner          Partner that the accountholder is registered with
     * @param Product         $product          The product involved in this transaction
     * @param string          $priceRef         The price validation
     * @param string          $buyOrSell        Order type COMPANYBUY/COMPANYSELL
     * @param string          $grams            Unit of gold in grams
     * @param string          $settlementMethod Settlement method
     * @param string          $campaignCode     The campaign code entered by user
     * @param string          $partnerData      Data from partner required for payment
     * @param string          $closeAccountTx   If tx is for account close, then skip checks
     * 
     * @return MyGoldTransaction
     */
    public function bookGoldTransactionOtcPartOne($accountHolder, $partner, $product, $priceRef, $orderType, 
                              $grams, $settlementMethod, $apiVersion,
                              $pin, $fromAlert = false, $campaignCode = '', $partnerData = '', $closeAccountTx = false)
    {
        $this->logDebug(__METHOD__. "(): Initializing booking for gold transaction");

        if (! $closeAccountTx) {
            $this->validateBookGoldTxRequest($accountHolder, $partner, $product, $orderType,
                                             $priceRef, $grams, $settlementMethod, $pin, $apiVersion);
        }

        // Serial processing of booking & confirmation process due to inventory balance checking
        // For customer buy orders - bmmb
        $isCustomerBuy = $orderType == Order::TYPE_COMPANYSELL;
        if ($isCustomerBuy) {
            $this->checkPartnerBalanceSufficient($partner, $grams);
        }
        // Previously only check CustomerBuy, new common warehouse implementation will check both buy&sell - 01-10-2021 
        if ($partner->sharedgv){
            $this->log(__METHOD__."(): check sharedgv partner balance", SNAP_LOG_DEBUG);
            $checking = $this->app->bankvaultmanager()->checkSharedDgvBalanceToProceed($grams, $orderType);
            if (!$checking){
                $this->log(__METHOD__."(): check sharedgv partner balance return false", SNAP_LOG_DEBUG);
                throw MyGtpPartnerInsufficientGoldBalance::fromTransaction([]);
            }
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        if (!$settings) {
            throw MyGtpPartnerSettingsNotInitialized::fromTransaction($partner);
        }

        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }
		
		$memberType = $accountHolder->getAdditionalData()->category;

        try {
            // Create an order through spotordermanager
            $refid = $this->generateRefNo("GT", $this->app->mygoldtransactionStore());
            $order = $this->doBookSpotOrder($partner, $apiVersion, $refid, $orderType,
                                            $product, $priceRef, $grams, $campaignCode, $memberType);
            if (! $order) {
                throw GeneralException::fromTransaction(null, ['message' =>"Unable to create order. Transaction refno : $refid"]);
            }

            $this->logDebug(__METHOD__. "(): Creating gold transaction object");
            // Add entry to MyGoldTransaction table for account holder
            $calc = $partner->calculator();
            $price = $this->getPriceFromReference($priceRef, $orderType);
            $goldTransaction = $this->app->mygoldtransactionStore()
                                    ->create([
                                        'originalamount' => $order->amount,
                                        'settlementmethod' => $settlementMethod,
                                        'salespersoncode' => $accountHolder->referralsalespersoncode,
                                        'refno'          => $refid,
                                        'orderid'        => $order->id,
                                        'campaigncode'   => $campaignCode,
                                        'status'        => MyGoldTransaction::STATUS_PENDING_PAYMENT
                                    ]);
            
            // Modify order object for MYGTP related data
            $order->buyerid = $accountHolder->id;
            $order->isspot  = $fromAlert ? MyGoldTransaction::FROMALERT_YES : MyGoldTransaction::FROMALERT_NO;
            if (MyGoldTransaction::SETTLEMENT_METHOD_FPX == $settlementMethod) {
                // Positive amount
                $order->fee = $calc->add($order->fee, $settings->transactionfee);
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT == $settlementMethod) {
                // Negative amount
                $order->fee = $calc->add($order->fee, $settings->payoutfee);
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $settlementMethod) {
                // No fee
                $order->fee = $settings->walletfee;
            } else if (MyGoldTransaction::SETTLEMENT_METHOD_LOAN == $settlementMethod) {
                $order->fee = 0;

                $amount = number_format(floatval($goldTransaction->originalamount) + floatval($order->fee), 2, '.', '');

                // If not enough balance throw
                if (0 > $accountHolder->loanbalance - $amount) {
                    $this->log(__METHOD__ . "(): Insufficient loan amount balance for account holder {$accountHolder->accountholdercode}", SNAP_LOG_ERROR);
                    throw MyGtpLoanInsufficientBalance::fromTransaction([], [
                        'message' => gettext("Insufficient loan amount balance.")
                    ]);
                }
                    
            }   else if (MyGoldTransaction::SETTLEMENT_METHOD_CASH == $settlementMethod || MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod) {
                // No fee
                $order->fee = 0;
            }

            // $order->amount  = $calc->add($order->amount, $order->fee);

            $goldTransaction = $this->app->mygoldtransactionStore()->save($goldTransaction);
            $goldTransaction->priceuuid = $priceRef; //this is to pass uuid price to add into paymentdetail
            $order = $this->app->orderStore()->save($order);

            
            // Save the gold transaction object
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }

        return $goldTransaction;
    }

     /**
     * Books an order using price validation
     * 
     * @param MyAccountHolder $accountHolder    The account holder
     * @param Partner         $partner          Partner that the accountholder is registered with
     * @param Product         $product          The product involved in this transaction
     * @param string          $priceRef         The price validation
     * @param string          $buyOrSell        Order type COMPANYBUY/COMPANYSELL
     * @param string          $grams            Unit of gold in grams
     * @param string          $settlementMethod Settlement method
     * @param string          $campaignCode     The campaign code entered by user
     * @param string          $partnerData      Data from partner required for payment
     * @param string          $closeAccountTx   If tx is for account close, then skip checks
     * 
     * @return MyGoldTransaction
     */
    public function bookGoldTransactionOtcPartTwo($accountHolder, $partner, $product, $goldTransaction, 
                              $order, $settlementMethod,  $partnerData = '')
    {
        $this->logDebug(__METHOD__. "(): Initializing approval booking for gold transaction");

       

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        if (!$settings) {
            throw MyGtpPartnerSettingsNotInitialized::fromTransaction($partner);
        }

        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }

        try {
            
            // When Customer Sell Gold
            if (Order::TYPE_COMPANYBUY == $order->type) {
                
                // update by azam on 2021-09-14 if autosubmitorder is turn on, and  SAP return error, 
                // it will rollback confirmBookGoldTransaction and will not create ledger
                // to overcome this we need to put this here for CompanyBuy
				$ledgerType = (MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod) ? MyLedger::TYPE_SELL_CASA : MyLedger::TYPE_SELL;
                $ledgers = $this->createLedgersForGoldTransaction($goldTransaction, $ledgerType);
                $this->logDebug(__METHOD__. "(): Initializing payment for gold transaction {$goldTransaction->refno}");

                // Process payment using settlement method
                $payment = $this->initializeTransactionPayment($accountHolder, $goldTransaction, $order,
                                                            $partner, $product, $settlementMethod, $partnerData);
                
                // Throw error if payment was not successful
                if (false === $payment) {
                    $this->log(__METHOD__."(): Unable to create payment for gold transaction {$goldTransaction->refno}.", SNAP_LOG_ERROR);
                    throw MyGtpFpxCreateTxError::fromTransaction(null, []);
                }
            }
            
            // Save the gold transaction object
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }

        // When Customer Buy Gold
        if (Order::TYPE_COMPANYSELL == $order->type) {
            $this->logDebug(__METHOD__. "(): Initializing payment for gold transaction {$goldTransaction->refno}");
            // Process payment using settlement method
            $payment = $this->initializeTransactionPayment($accountHolder, $goldTransaction, $order,
                                                        $partner, $product, $settlementMethod, $partnerData);
            
            // Immediately confirm transaction for loan
            if (MyGoldTransaction::SETTLEMENT_METHOD_LOAN == $settlementMethod) {
               $payment = $this->createLoanTransaction($accountHolder, $goldTransaction, $order);
            }

            // Throw error if payment was not successful
            if (false === $payment) {
                $this->log(__METHOD__."(): Unable to create payment for gold transaction {$goldTransaction->refno}.", SNAP_LOG_ERROR);
                throw MyGtpFpxCreateTxError::fromTransaction(null, []);
            }
        }
        $this->logDebug(__METHOD__."(): Finished booking process for gold transaction {$goldTransaction->refno}");
        $gdTx = $this->app->mygoldtransactionStore()->getById($goldTransaction->id);
	    return $gdTx;
    }

    /**
     * Confirms booking of gold transaction, also creates a ledger for gold transaction
     * Custom logic due to SpotOrderManager::confirmBookOrder using latest spot price for confirm price
     * 
     * @param MyGoldTransaction     $goldTx      The gold transaction
     * @param string                $ledgerType  Type of ledger
     * @param ApiManager            $apiManager  ApiManager to be used
     * @param string                $skipSAPSubmission  Manual use for catering duplicate order sap error where 
     *                                                  already confirm on SAP but Pending on GTP
     * @param bool                  $notifyUser  If need to notify user via email or not
     * 
     * @return MyGoldTransaction
     */
    public function confirmBookGoldTransaction($goldTx, $ledgerType, $apiManager = null, $skipSAPSubmission = false, $notifyUser = true)
    {
        // Lock to avoid concurrent updating from redirect / callback / checkstatus
        $lockKey = '{ConfirmGoldTransaction}:' . $goldTx->id;
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);
        $skipconfirmstep = $goldTx->skipconfirm;

        // Refresh goldtx data
        $goldTx = $this->app->mygoldtransactionStore()->getById($goldTx->id);
        $order = $goldTx->getOrder();
        if(!$skipconfirmstep){
            $sm = $this->getGoldTransactionStateMachine($goldTx);
            if (! $sm->can(MyGoldTransaction::STATUS_PAID) || !in_array($order->status, [Order::STATUS_PENDING])) {
                $this->log("confirmBookGoldTransaction({$order->orderno}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'confirmation ']);
            }
        }

        $partner = $this->app->partnerStore()->getById($order->partnerid);
        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $oldGoldTxStatus = $goldTx->status;
            // Updated by Cheok on 2021-05-18 to  cater for status flow change for sell transactions
             if (Order::TYPE_COMPANYBUY != $order->type) {
                $goldTx->status = MyGoldTransaction::STATUS_PAID;
                $goldTx = $this->app->mygoldtransactionStore()->save($goldTx);
                $this->logDebug("Updated gold transaction to paid $goldTx->refno");

                // update by azam on 2021-09-14 if autosubmitorder is turn on, and  SAP return error, 
                // it will rollback confirmBookGoldTransaction and will not create ledger
                // to overcome this we need to put this on bookGoldTransaction for CompanyBuy
                $ledgers = $this->createLedgersForGoldTransaction($goldTx, $ledgerType);

                //$transGold = $goldTx; //only transfer when type is not CompanyBuy. Transferdb to db if true
            }           
            // End update by Cheok

            // Only submit to SAP if autosubmitorder is enabled.
            if ($partner->autosubmitorder || $skipSAPSubmission) {
                $this->confirmGoldTransaction($goldTx, $apiManager, $skipSAPSubmission);
            }

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();

                // update shareDGV / DGV amount
                $this->app->bankvaultManager()->updateSharedDGVPartnerBalance($partner, $order->xau, $order->type);

                /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
                $transferDb = $this->app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
                if($transferDb) $sendTrans = $this->sendTransactionBetweenDb($goldTx,$order);
                else $this->log("[Transfer DB Process] Transaction with refno ".$goldTx->refno." no need to trasnfer to other db because status mygoldtransaction ".$goldTx->status." is not PAID or transaction is not CompanySell", SNAP_LOG_DEBUG);
                /**/
            }

            $cacher->unlock($lockKey);
            $this->log(__METHOD__."({$goldTx->refno}) has been completed successfully.", SNAP_LOG_DEBUG);
        } catch (\Exception $e) {
            $cacher->unlock($lockKey);
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            throw $e;
        }

        // Send push notification / email
        $accHolder = $this->app->myaccountholderStore()->getById($order->buyerid);
        if ($notifyUser) {
            $email = $accHolder->email;
        }

        $amt = $order->amount;
        $amtFormat = number_format($amt,2);
        $orderAmount = $amtFormat." "; //putting the empty space so that the variable is identified as string, not decimal

        $this->notify(new IObservation($goldTx, IObservation::ACTION_CONFIRM, $oldGoldTxStatus, [
            'event' => MyGtpEventConfig::EVENT_GOLDTRANSACTION_CONFIRMED,
            'accountholderid'   => $accHolder->id,
            'projectBase'       => $this->app->getConfig()->{'projectBase'},
            'name'              => $accHolder->fullname,
            'receiver'          => $email ?? '',
            'ordertype'         => Order::TYPE_COMPANYBUY == $order->type ? 'SELL' : "BUY",
            'xau'               => number_format($order->xau, 3),
            'amount'            => $orderAmount,
            'bookingprice'      => number_format($order->price, 2)
        ]));

        return $goldTx;
    }


    /**
     * Confirms gold transaction & its order upon SAP confirm
     * 
     * @param MyGoldTransaction     $goldTx      The gold transaction
     * @param ApiManager            $apiManager  ApiManager to be used
     * @param string                $skipSAPSubmission  Manual use for catering duplicate order sap error where 
     *                                                  already confirm on SAP but Pending on GTP
     * 
     * @return MyGoldTransaction
     */
    public function confirmGoldTransaction($goldTx, $apiManager = null, $skipSAPSubmission = false)
    {
        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            // Ensure order object is latest
            $order = $this->app->orderStore()->getById($goldTx->orderid);

            // $sm = $this->app->spotorderManager()->getStateMachine($order);
            if ( !in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED])) {
                $this->log("confirmGoldTransaction({$order->partnerrefid}):  Unable to proceed to confirm due to status", SNAP_LOG_ERROR);
                throw \Snap\api\exception\OrderInvalidAction::fromTransaction($order, ['action' => 'confirmation ']);
            }
            
            $skipConfirmOrder = Order::STATUS_CONFIRMED == $order->status;
            if (!$skipConfirmOrder) {

                if (! $skipSAPSubmission) {
                    $sapSucceed = $this->submitOrderToSAP($order, $apiManager);
                    if (!$sapSucceed) {
                        $this->log(__METHOD__."():SAP Submission Failed", SNAP_LOG_ERROR);
                        throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'SAP Submission failed: Unable to proceed order.']);
                    }
                }

                $now = new \DateTime();
                $now->setTimezone($this->app->getUserTimezone());

                // Confirm order first
                $order->status = Order::STATUS_CONFIRMED;
                $order->confirmon = $now;
                $order->confirmprice = $order->bookingprice;
                $order->confirmby = defined('SNAPAPP_DBACTION_USERID') ? SNAPAPP_DBACTION_USERID : 0;
                $order->confirmpricestreamid = $order->bookingpricestreamid;
                $order = $this->app->orderStore()->save($order);
                $this->logDebug("Order ({$order->orderno}) is confirmed. Proceeding with updating gold transaction {$goldTx->refno}");
            }

            // Update gold transaction after order
            $oldGoldTxStatus = $goldTx->status;
            //if ($goldTx->status != MyGoldTransaction::STATUS_PAID){
            //    $goldTx->status = MyGoldTransaction::STATUS_CONFIRMED;
            //}
            $goldTx->status = MyGoldTransaction::STATUS_CONFIRMED;
            $goldTx = $this->app->mygoldtransactionStore()->save($goldTx);
            $this->logDebug("Updated gold transaction to confirmed $goldTx->refno");

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            throw $e;
        }

        return $goldTx;
    }

    /**
     * Submits order to SAP
     * @param Order $order 
     * @return bool 
     */
    public function submitOrderToSAP(Order $order, $apiManager = null)
    {
        $this->log(__METHOD__."({$order->orderno}) init sapBookNewOrder START.", SNAP_LOG_DEBUG);
        $apiManager = $apiManager ?? $this->app->apiManager();
        $sap_return = $apiManager->sapBookNewOrder($order);
        $sapSucceed = 'Y' == $sap_return[0]['success'];
        $this->log(__METHOD__."({$order->orderno}) init sapBookNewOrder END.", SNAP_LOG_DEBUG);

        return $sapSucceed;
    }

    /**
     * Returns receipt page data
     * 
     * @param MyPaymentDetail $payment
     * @param array           $params
     */
    public function createReceiptPage($payment)
    {
        // $paymentRef = $payment->paymentrefno;
        // $tags = ['##TX_STATUS##', '##TX_FPX_ID##', '##TX_DATETIME##', '##PRODUCT_DESC##', 
        //          '##TX_AMOUNT##', '##PAYMENT_REF_NO##', '##SELLER_ORDER_NO##'];

        // $replacements = [$payment->getStatusString(), $payment->gatewayrefno, $payment->transactiondate->format('Y-m-d H:i:s'),
        //                  $payment->productdesc, $payment->amount, $payment->paymentrefno, $payment->sourcerefno];
        
        // $html = str_replace($tags, $replacements, file_get_contents(SNAPAPP_DIR.DIRECTORY_SEPARATOR."client".DIRECTORY_SEPARATOR."fpxreceipt.html"));
        $html = "";
        return $html;
    }

    /**
     * Method to do a redirect to the NGINX internal path
     */
    public function subscribeToPriceStream(MyAccountHolder $accHolder, Partner $partner, $productCode = null)
    {
        // Updated by Cheok on 2020-12-23 to allow users to view price stream even though unverified 
        // Check if account holder can perform transactions
        // if (! $accHolder->canDoTransaction()) {
        //     throw MyGtpActionNotPermitted::fromTransaction([]);
        // }       
        // End update by Cheok

        // Digital Gold by default
        if (!$productCode) {
            $productCode = "DG-999-9";
        }

        $product = $this->app->productStore()->getByField('code', $productCode);
        if (!$product) {
            throw GeneralException::fromTransaction([], ['message' => gettext("Product not found")]);
        }

        $priceProvider = $this->app->priceproviderStore()->getForPartnerByProduct($partner, $product);
        $path = $this->app->getConfig()->{'app.pricestream.subscribe.path'};
        if ($path) {
            // Do an internal redirect to the stream path
            $fullPath = $path . $priceProvider->id;
            header("X-Accel-Redirect: $fullPath");
            header('X-Accel-Buffering: no');
        }
    }

    /**
     * Get the total amount for a gold transaction before booking
     *
     * @param  Partner     $partner
     * @param  Product     $product
     * @param  PriceStream $priceObj
     * @param  boolean     $isSell
     * @param  string       $grams
     * @param  string       $settlementMethod
     * @return array
     */
    public function bookGoldTransactionAmountBreakdown(Partner $partner, Product $product, PriceStream $priceObj, $isSell, $grams, $settlementMethod, $campaignCode = '', $memberType = '')
    {
        $calc = $partner->calculator();
        $orderFee = $isSell ? $partner->getRefineryFee($product) : $partner->getPremiumFee($product);
        $price    = $isSell ? $priceObj->companybuyppg : $priceObj->companysellppg;
		$price = $calc->round($price);
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if ($isSell){
            $transactionType = 'CompanyBuy';
        }else{
            $transactionType = 'CompanySell';
        }
        $partnerServices_product = $partner->getServiceForProduct($product);
        $specialDiscountCondition = $partnerServices_product->specialpricetype;
        if ($specialDiscountCondition != \Snap\object\PartnerService::SPECIALTYPE_NONE){
            $amount = $calc->multiply($grams, $price);
            $discountPriceReturn = $this->app->spotorderManager()->specialPriceOffsetDiscount($partner, $product, $transactionType, $price, $amount, $grams);
            $discountPrice = $discountPriceReturn['discountprice'];
        }else{
            $discountPrice = 0;
        }
		
		$fees = ('CompanyBuy' == $transactionType) ? $partner->getRefineryFee($product) : $partner->getPremiumFee($product);
		
		//pricing model discount
		if ($this->app->getStore('otcpricingmodel')) {
			$discountPriceReturn = $this->app->spotorderManager()->otcPricingModelDiscount($priceObj, $campaignCode, $memberType, $grams, $price, $fees, $partner, $transactionType);
			if (!$discountPriceReturn){
				$discountPrice = 0;
			}else{
				$discountPrice = $discountPriceReturn['discountprice'];
			}
		}
        
        $price    = $calc->round($price + ($fees) + ($discountPrice));
        $amount   = $calc->multiply($grams, $price);
        if (!$isSell) {

            if (MyGoldTransaction::SETTLEMENT_METHOD_FPX === $settlementMethod) {
                $transactionFee = $settings->transactionfee;
            } elseif (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $settlementMethod) {
                // Currently 0
                $transactionFee = 0;
            }

            $transactionFee = $calc->add($transactionFee, $orderFee);
            $totalPayment = $calc->add($amount, $transactionFee);
        } else {
            if (MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT === $settlementMethod) {
                $transactionFee = $settings->payoutfee;
            } elseif (MyGoldTransaction::SETTLEMENT_METHOD_WALLET === $settlementMethod) {
                // Currently 0
                $transactionFee = 0;
            }
            $transactionFee = $calc->add($transactionFee, $orderFee);
            $totalPayment = $partner->calculator()->sub($amount, $transactionFee);
        }
        $totalPayment = $partner->calculator()->round($totalPayment);

        $return = [
            'transaction_fee' => $transactionFee,
            'amount'          => $amount,
            'total'           => $totalPayment,
            'discount'        => $discountPrice,
            'specialprice'    => $price
        ];

        return $return;
    }

    /**
     * Creates ledgers for the gold transaction 
     * 
     * @param MyGoldTransaction $goldTx       Gold transaction 
     * @param string            $ledgerType   Customer ledger type
     */
    protected function createLedgersForGoldTransaction($goldTx, $ledgerType)
    {
        $this->logDebug("Creating customer ledger for gold transaction $goldTx->refno");
        $now = new \DateTime();
        /** @var Order @order */
        $order = $this->app->orderStore()->getById($goldTx->orderid);
        $accHolder = $this->app->myaccountholderStore()->getById($order->buyerid);

        if($this->app->getConfig()->{'projectBase'} == 'BSN'){
            $partnerId = $order->partnerid;
        }else{
            $partnerId = $accHolder->partnerid;
        }

        $ledger = $this->app->myledgerStore()->create([
            'type'              => $ledgerType,
            'accountholderid'   => $order->buyerid,
            'partnerid'         => $partnerId,
            'refno'             => $goldTx->refno,
            'typeid'            => $goldTx->id,
            'transactiondate'   => $now->format('Y-m-d H:i:s'),
            'status'            => MyLedger::STATUS_ACTIVE
        ]);
        
        // Set debit/credit depending if it is a buy or sell
        if (Order::TYPE_COMPANYBUY == $order->type) {
            $ledger->debit = $order->xau;
            $ledger->credit = 0.00;
        } else {
            $ledger->credit = $order->xau;
            $ledger->debit = 0.00;
        }

        $ledger = $this->app->myledgerStore()->save($ledger);
        $this->logDebug("Finished saving customer ledger for gold transaction $goldTx->refno");

        $this->logDebug("Creating partner ledger for gold transaction $goldTx->refno");
        $partnerLedger = $this->app->myledgerStore()->create([
            'accountholderid'   => 0,
            'partnerid'         => $accHolder->partnerid,
            'refno'             => $goldTx->refno,
            'typeid'            => $goldTx->id,
            'transactiondate'   => $now->format('Y-m-d H:i:s'),
            'status'            => MyLedger::STATUS_ACTIVE
        ]);
        
        // Set debit/credit depending if it is a buy or sell
        if (Order::TYPE_COMPANYBUY == $order->type) {
            $partnerLedger->type   = MyLedger::TYPE_ACEBUY;
            $partnerLedger->credit = $order->xau;
            $partnerLedger->debit  = 0.00;
        } else {
            $partnerLedger->type   = MyLedger::TYPE_ACESELL;
            $partnerLedger->debit  = $order->xau;
            $partnerLedger->credit = 0.00;
        }

        $partnerLedger = $this->app->myledgerStore()->save($partnerLedger);
        $this->logDebug("Finished saving partner ledger for gold transaction $goldTx->refno");

        return [ $ledger, $partnerLedger ];
    }

    /**
     * Starts the payment process for the gold transaction
     * 
     * @param MyAccountHolder   $accHolder          AccountHolder
     * @param MyGoldTransaction $goldTx             The transaction
     * @param Order             $order              The associated order
     * @param Partner           $partner            The associated partner
     * @param Product           $product            The product
     * @param string            $settlementMethod   The settlement method
     * @param string            $partnerData        The partner data required for payment detail
     * 
     * @return mixed|bool   Return false or throw exception to indicate failure
     */
    protected function initializeTransactionPayment($accHolder, $goldTx, $order, $partner, $product, $settlementMethod, $partnerData = '')
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        switch ($order->type) {
            // Customer sell
            case Order::TYPE_COMPANYBUY:
                if (MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT == $settlementMethod) {
                    $disbursement = $this->app->mygtpDisbursementManager()
                                         ->createDisbursementForGoldTransaction($goldTx, $partner, $accHolder);

                    $payout = BasePayout::getInstance($settings->payoutprovider);
                    $data = $payout->createPayout($accHolder, $disbursement);
                    return $data;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $settlementMethod) {

                    if (!$settings->partnerpaymentprovider) {
                        $this->log(__METHOD__."(): ". "Partner Payment Provider API not found.", SNAP_LOG_ERROR);
                        throw MyGtpPartnerApiInvalid::fromTransaction([], [
                            'message' => gettext("Partner Payment Provider API not found.")
                        ]);
                    }

                    $walletName = basename(str_replace('\\', '/', $settings->partnerpaymentprovider));
                    $className = "\\Snap\\api\\payout\\{$walletName}Payout";
                    $payout = BasePayout::getInstance($className);                    
                    
                    /**
                     * For GoPayz wallet we will do a batch processing instead of processing single payment.
                     * 
                     */
                    if ($payout instanceof \Snap\api\payout\GoPayzPayout) {
                        return true;
                    }

                    $disbursement = $this->app->mygtpDisbursementManager()
                                              ->createWalletDisbursementForGoldTransaction($goldTx, $partner, $accHolder, $walletName, $order, $partnerData);
                    
                    $data = $payout->createPayout($accHolder, $disbursement);
                    return $data;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod && 0 < strlen($this->app->getConfig()->{'otc.casa.api'})) {
                    
                    $disbursement = $this->app->mygtpDisbursementManager()
                                         ->createDisbursementForGoldTransaction($goldTx, $partner, $accHolder, null, $partnerData);
                    
                    $casaApiClassname = $this->app->getConfig()->{'otc.casa.api'};
                    $payout = BaseCasa::getInstance($casaApiClassname);
                    
                    $data = $payout->createPayout($accHolder, $disbursement);
                    
                    return $data;
                }
                
                break;

            // Customer buy
            case Order::TYPE_COMPANYSELL:
                if (MyGoldTransaction::SETTLEMENT_METHOD_FPX == $settlementMethod) {
                    // Customer buy using FPX
                    $payment = $this->createPaymentDetailForTransaction($goldTx, $order, $partner, $product);

                    $fpx = BaseFpx::getInstance($settings->companypaymentprovider);
                    $data = $fpx->initializeTransaction($accHolder, $payment);

                    return $data;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $settlementMethod) {
                    // Customer buy using Wallet
                    $payment = $this->createPaymentDetailForTransaction($goldTx, $order, $partner, $product, $partnerData);

                    $wallet = BaseWallet::getInstance($settings->partnerpaymentprovider);
                    $data = $wallet->initializeTransaction($accHolder, $payment);

                    return $data;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_LOAN == $settlementMethod) {
                    // For Loan Settelement method, loan transaction payment is handled in createLedgersForGoldTransaction()
                    $payment = $this->createPaymentDetailForTransaction($goldTx, $order, $partner, $product);
                    return true;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_CASH == $settlementMethod) {
                    // For Cash Settelement method, payment is handled directly over the counter
                    $payment = $this->createPaymentDetailForTransaction($goldTx, $order, $partner, $product);
                    // Manual function for callback is performed here ( updating status and enter transaction )

                    // Update payment status to paid since payment is made at counter
                    // Process manual callback 
                    $payment = $this->processManualPaymentCallback($goldTx, $payment);
                    return true;
                } elseif (MyGoldTransaction::SETTLEMENT_METHOD_CASA == $settlementMethod && 0 < strlen($this->app->getConfig()->{'otc.casa.api'})) {
                    $payment = $this->createPaymentDetailForTransaction($goldTx, $order, $partner, $product, $partnerData);
                    $casaApiClassname = $this->app->getConfig()->{'otc.casa.api'};
                    
                    $casa = BaseCasa::getInstance($casaApiClassname);
                    $data = $casa->initializeTransaction($accHolder, $payment);

                    return $data;
                }
                break;
        }

        throw GeneralException::fromTransaction(null, [
            'message'   => "Order type not supported for transaction {$goldTx->refno}."
        ]);
    }


    /**
     * Creates a payment detail object for FPX purposes
     * 
     * @param MyGoldTransaction $goldTransaction    The transaction object
     * @param Order             $order              The associated order
     * @param Partner           $partner            The partner for this transaction
     * @param Product           $product            The product
     * @param string            $partnerData        The partner data required for transaction if available
     */
    protected function createPaymentDetailForTransaction($goldTransaction, $order, $partner, $product, $partnerData = '')
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
        $payment = $this->app->mypaymentdetailStore()
                        ->create([
                            'amount' => $goldTransaction->originalamount,
                            'customerfee' => $order->fee, // _PENDING channge use order->fee if has premium.refinery. need use new column as processfee
                            'accountholderid' => $order->buyerid,
                            'paymentrefno' => $this->generateRefNo("P", $this->app->mypaymentdetailStore()),
                            'sourcerefno'  => $goldTransaction->refno,
                            'status'       => MyPaymentDetail::STATUS_PENDING_PAYMENT,
                            'productdesc'  => $product->name . " {$order->xau}g",
                            'transactiondate' => $now,
                            'verifiedamount' => 0.00,
                            'priceuuid' => $goldTransaction->priceuuid
                        ]);

        if (0 < strlen($partnerData)) {
            $payment->token = $partnerData;
        }

        $payment = $this->app->mypaymentdetailStore()->save($payment);
        $payment = $this->app->mypaymentdetailStore()->getById($payment->id);
        return $payment;
    }

    /**
     * Process payment callback for manual transactions ( cash payment )
     * 
     * @param MyGoldTransaction $goldTransaction    The transaction object
     * @param Order             $order              The associated order
     * @param Partner           $partner            The partner for this transaction
     * @param Product           $product            The product
     * @param string            $partnerData        The partner data required for transaction if available
     */
    protected function processManualPaymentCallback($goldTransaction, $payment)
    {
        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$response['sellerOrderNo']})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$response['sellerOrderNo']})");
        }


        $payment->verifiedamount = $goldTransaction->originalamount ?? $payment->verifiedamount;
        // $payment->gatewaystatus = $response['status'];
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['verifiedamount']);
        $initialStatus = $payment->status;
        $action = IObservation::ACTION_NONE;
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());
        
         // Set status of the payment
         $skip = false;
         $state = $this->app->mygtptransactionManager()->getPaymentDetailStateMachine($payment);
        //  if (in_array($m1_status, ['UNSUCCESSFUL', 'FAILED']) && $state->can(MyPaymentDetail::STATUS_FAILED)) {
        //      $action = IObservation::ACTION_REJECT;
        //      $payment->status = MyPaymentDetail::STATUS_FAILED;
        //      $payment->failedon = $now;
             
        //  } else if ("CANCELLED" == $m1_status && $state->can(MyPaymentDetail::STATUS_CANCELLED)) {
        //      $action = IObservation::ACTION_CANCEL;
        //      $payment->status = MyPaymentDetail::STATUS_CANCELLED;
        //      $payment->failedon = $now;
 
        //  } else if (in_array($m1_status, ['CAPTURED', 'APPROVED'])) {
        //      $action = IObservation::ACTION_CONFIRM;
        //      $payment->status = MyPaymentDetail::STATUS_SUCCESS;
        //      $payment->successon = $now;
 
        //  } else if ("ROLLBACK" == $m1_status && $state->can(MyPaymentDetail::STATUS_REFUNDED)) {
        //      $action = IObservation::ACTION_REVERSE;
        //      $payment->status = MyPaymentDetail::STATUS_REFUNDED;
        //      $payment->refundedon = $now;
        //  } else {
        //      $this->log("Payment {$payment->paymentrefno} callback received but no action was taken.", SNAP_LOG_ERROR);
        //      $skip = true;
        //  }
        $action = IObservation::ACTION_CONFIRM;
        $payment->status = MyPaymentDetail::STATUS_SUCCESS;
        $payment->successon = $now;
 
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewaystatus', 'status', 'successon', 'failedon', 'refundedon', 'verifiedamount']);
        $this->notify(new IObservation($payment, $action, $initialStatus, ['response' => $response]));
        return $payment;
    }

    /**
     * Performs order booking using GTP SpotOrderManager
     * 
     * @param Partner $partner 
     * @param string $version 
     * @param string $refid 
     * @param string $orderType 
     * @param Product $product 
     * @param string $priceRef 
     * @param string $grams 
     * @return Order
     * @throws MyGtpPriceValidationNotValid 
     */
    protected function doBookSpotOrder(Partner $partner, $version, $refid, $orderType,
                                     Product $product, $priceRef, $grams, $campaignCode = '', $memberType = '')
    {
        // Round locked in price for GTP
        $lockedInPrice = $partner->calculator()->round($this->getPriceFromReference($priceRef, $orderType));
        
        $order = $this->app
                        ->spotorderManager()
                        ->bookOrder($partner, $version, $refid, $orderType,
                                    $product, $priceRef, null, 'weight', $grams,
                                    $lockedInPrice, null /** unused */, '',
                                    ''/**reference */, null /**timestamp */, true /** use 0 limit = unlimited */, $campaignCode, $memberType);

                                    
        return $order;
    }

    /**
     * Check if product is valid for transaction
     * @param Partner $partner 
     * @param Product $product 
     * @param string $buyOrSell 
     * @return boolean 
     */
    protected function checkGoldTxProductValid($partner, $product, $buyOrSell)
    {
        $cusBuy = Order::TYPE_COMPANYSELL == $buyOrSell;
        $productValid = $cusBuy ? $product->companycansell && $partner->canBuy($product) 
                                : $product->companycanbuy && $partner->canSell($product);

        return $productValid;
    }

    /**
     * Returns price from reference
     * @param string  $priceRef         Price reference UUID
     * @param string  $orderType        Order TYPE
     * 
     * @return string
     */
    protected function getPriceFromReference($priceRef, $orderType)
    {

        $store = (preg_match('/^PV.*/', $priceRef) ? $this->app->priceValidationStore() : $this->app->priceStreamStore());
        $priceObj = $store->getByField('uuid', $priceRef);
        $customerSell = Order::TYPE_COMPANYBUY == $orderType;

        if ($priceObj instanceof PriceStream) {
            $price = $customerSell ? $priceObj->companybuyppg : $priceObj->companysellppg;
        } else if ($priceObj instanceof PriceValidation) {
            $price = $priceObj->price;
        } else {
            $this->log(__METHOD__."(): Unable to find price stream with uuid {$priceRef}", SNAP_LOG_DEBUG);
            throw MyGtpPriceValidationNotValid::fromTransaction(null);
        }

        return $price;
    }

    /**
     * Validates settlement method for gold transaction
     * @param string $buyOrSell         Order::TYPE_COMPANYBUY/SELL
     * @param string $settlementMethod  MyGoldTransaction::SETTLEMENT_METHOD_*
     * 
     * @return boolean
     */
    protected function checkGoldTxSettlementMethod(MyPartnerSetting $settings, $buyOrSell, $settlementMethod)
    {
        // Customer buy settlement methods
        $buySettlementMethods = [
            MyGoldTransaction::SETTLEMENT_METHOD_FPX,
            MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
            MyGoldTransaction::SETTLEMENT_METHOD_CASH,
            MyGoldTransaction::SETTLEMENT_METHOD_CASA,
            MyGoldTransaction::SETTLEMENT_METHOD_LOAN
        ];
        $sellSettlementMethods = [
            MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
            MyGoldTransaction::SETTLEMENT_METHOD_CASA,
            MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
        ];

        $customerBuy = Order::TYPE_COMPANYSELL ==  $buyOrSell;

        $valid = in_array($settlementMethod, $customerBuy ? $buySettlementMethods : $sellSettlementMethods);

        if ($valid) {
            $hasPaymentProvider = true;
            if ($customerBuy && MyGoldTransaction::SETTLEMENT_METHOD_FPX == $settlementMethod && !$settings->companypaymentprovider) {
                $message = "Partner FPX API not found.";
                $hasPaymentProvider = false;
            } elseif ($customerBuy && MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $settlementMethod && !$settings->partnerpaymentprovider) {
                $message = "Partner Payment Provider API not set.";
                $hasPaymentProvider = false;

            } elseif (!$customerBuy && !$settings->payoutprovider) {
                // Customer sell
                $message = "Partner Payout API not found.";
                $hasPaymentProvider = false;
            }

            if (! $hasPaymentProvider) {
                $this->log(__METHOD__.":". $message, SNAP_LOG_ERROR);
                throw MyGtpPartnerApiInvalid::fromTransaction([], [
                    'message' => $message
                ]);
            }
        }

        return $valid;
    }

    /**
     * @param MyAccountHolder $accountHolder
     * @param Partner         $partner
     * @param Product         $product              Product 
     * @param string          $orderType            Order type (COMPANYBUY/COMPANYSELL)
     * @param string          $priceRef             Price reference
     * @param string          $grams                Xau amount of this order
     * @param string          $settlementMethod     Settlement/Payment method for this transaction
     * @param string          $pin                  Pin code of this user
     * @param string          $apiVersion           API version
     * @param bool            $quickMode            Skip certain checks (For use before spot order)
     * @param bool            $quickMode            Skip certain checks (For use before spot order)
     * @param MyGtpAccountManager $accMgr           Account manager instance
     */
    public function validateBookGoldTxRequest($accountHolder, $partner, $product, $orderType, $priceRef, $grams, $settlementMethod, $pin, $apiVersion = null, $accMgr = null, $quickMode = false)
    {
        if ((! $accountHolder->canDoTransaction())) {
            throw MyGtpActionNotPermitted::fromTransaction($accountHolder, ['message' => '']);
        }

        // Check product configuration can buy/sell
        $productValid = $this->checkGoldTxProductValid($partner, $product, $orderType);
        if (!$productValid) {
            throw MyGtpProductInvalid::fromTransaction($product);
        }

        if (!$quickMode) {
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
            $validSettlement = $this->checkGoldTxSettlementMethod($settings, $orderType, $settlementMethod);
            if (!$validSettlement) {
                $this->logDebug(__METHOD__."(): No valid settlement method was given");
                throw GeneralException::fromTransaction(null, ['message' => gettext('No valid settlement method was provided')]);
            }
        }


        $accMgr = $accMgr ?? $this->app->mygtpaccountManager();
        $calc = $partner->calculator(false);

        $settings = $this->app->mypartnersettingStore()
                ->searchTable()
                ->select(['mininitialxau', 'minbalancexau'])
                ->where('partnerid', $partner->id)
                ->one();

        if ($accountHolder->initialInvestmentMade()) {

            if (!$quickMode) {
                // Verify pin etc
                $this->logDebug(__METHOD__."(): Verifying account holder pin");
                if (! $accMgr->verifyAccountHolderPin($accountHolder, $pin)) {
                    $this->logDebug(__METHOD__."(): Account holder pin entered was incorrect");
                    throw MyGtpAccountHolderWrongPin::fromTransaction(null);
                }
            }

        } else {

            if (!$quickMode) {
                // Verify pin etc
                $this->logDebug(__METHOD__."(): Verifying account holder pin");
                if (! $accMgr->verifyAccountHolderPin($accountHolder, $pin)) {
                    $this->logDebug(__METHOD__."(): Account holder pin entered was incorrect");
                    throw MyGtpAccountHolderWrongPin::fromTransaction(null);
                }
            }

            // Calculate minitial investment needed for first transaction            
            if ( 0 > $calc->sub($grams, $settings->mininitialxau)) {
                $this->logDebug(__METHOD__."(): Entered amount of {$grams} grams does not meet initial investment of {$settings->mininitialxau}g");
                throw GeneralException::fromTransaction(null, ['message' => sprintf(gettext("Minimum initial investment of %s grams not met."), number_format($settings->mininitialxau, 3))]);
            }
        }

        $customerSell = Order::TYPE_COMPANYBUY == $orderType;

        $xau = floatval($grams);
        $currentBalance = floatval($accountHolder->getCurrentGoldBalance());
        $availableBalance = $accMgr->getAccountHolderAvailableGoldBalance($accountHolder);
        $this->logDebug(__METHOD__."(): Settlement method {$settlementMethod} is chosen.");

        // Sell transaction validations
        //$skipCustomerSellChecking = false;
        //$skipCustomerSellChecking_partnerid = $this->app->getConfig()->{'mygtp.toyyid.skipcustomersellchecking.partnerid'};
        //if ($skipCustomerSellChecking_partnerid && $skipCustomerSellChecking_partnerid == $partner->id){
        //    $skipCustomerSellChecking = true;
        //}
        if ($customerSell) {
            // Check account name/number set
            if (MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT === $settlementMethod && (0 >= strlen($accountHolder->accountname) || 0 >= strlen($accountHolder->accountnumber))) {
                $this->logDebug(__METHOD__."(): Account holder has not set account name/number.");
                throw MyGtpAccountHolderNoAccountNameOrNumber::fromTransaction([], ['message' => gettext("Account name/number not set")]);
            }

            // Check if account holder has enough balance
            $remainingBalance = floatval($calc->sub($availableBalance, $xau));
            $this->log(__METHOD__."(): Balance: {$currentBalance}g, Balance after deduct storage fees: ${availableBalance}g, Sell Amount: ${xau}g", SNAP_LOG_DEBUG);
            if (0 > $remainingBalance) {
                $this->log(__METHOD__."(): Account holder ID ({$accountHolder->id}) does not have enough gold balance to perform sell transaction of {$xau}g.", SNAP_LOG_ERROR);
                throw MyGtpAccountHolderNotEnoughBalance::fromTransaction(null);
            }

            if (!$quickMode) {
                // Get pending debits (Sells/Conversions)
                $pendingDebits = $accMgr->getPendingPaymentXauDebits($accountHolder);
                $remainingBalance = $calc->sub($remainingBalance, $pendingDebits);
                $this->log(__METHOD__."(): Pending debits: {$pendingDebits}g, Balance after transaction: {$remainingBalance}g", SNAP_LOG_DEBUG);
                if (0 > $remainingBalance) {
                    $this->log(__METHOD__."(): Account holder ID ({$accountHolder->id}) does not have enough gold balance to perform sell transaction of {$xau}g.", SNAP_LOG_ERROR);
                    throw GeneralException::fromTransaction([], ['message' => gettext("Please complete or cancel existing FPX transactions before proceeding.")]);
                }
            }

            // Minimum account balance is 1
            if ($remainingBalance < $settings->minbalancexau) {
                $this->log(__METHOD__."(): Account holder ({$accountHolder->id}) attempting to sell below minimum balance of {$settings->minbalancexau}g", SNAP_LOG_ERROR);
                $this->log("Balance: {$currentBalance}, Balance after deduct storage fees: ${availableBalance}, Sell Amount: ${$xau}g", SNAP_LOG_ERROR);
                throw MyGtpInvalidAmount::fromTransaction([], ['message' => "Minimum balance after sell transaction must be above {$settings->minbalancexau}g."]);
            }
        }
    }

    /**
     * 
     * @param Partner             $partner      The partner
     * @param float               $grams        Xau amount available to sell
     * @param MyGtpPartnerManager $partnerMgr   Partner Manager class
     * 
     * @return void
     * @throws GeneralException
     */
    public function checkPartnerBalanceSufficient($partner, $grams, $partnerMgr = null, $orderType = null)
    {
        /** @var MyGtpPartnerManager*/
        $partnerMgr = $partnerMgr ?? $this->app->mygtppartnerManager();
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        if (!$settings->strictinventoryutilisation) {
            $this->log("Skipping partner balance check for partner {$partner->code}", SNAP_LOG_DEBUG);
            return;
        }
        
        // bmmb | finance institution partner
        $this->logDebug(__METHOD__.": Checking partner balance is sufficient to perform order");
        $balance = $partnerMgr->getPartnerInventoryBalance($partner);
        $pendingSum = $this->app->mygoldtransactionStore()->searchView()->select()
                                    ->where('ordpartnerid', $partner->id)
                                    ->where('ordtype', Order::TYPE_COMPANYSELL)
                                    ->where('status', MyGoldTransaction::STATUS_PENDING_PAYMENT)
                                    ->sum('ordxau');

        $calc = $partner->calculator(false);
        $newBal = $calc->sub(strval($balance), strval($grams + floatval($pendingSum))); 

        $this->log(__METHOD__.": Current Balance: $balance, Order xau: $grams, Pending sum: $pendingSum, Balance after: $newBal", SNAP_LOG_INFO);
        if (0 > $newBal) {
            $this->log(__METHOD__.": Partner does not have enough xau in stock to carry out transaction.", SNAP_LOG_ERROR);
            throw MyGtpPartnerInsufficientGoldBalance::fromTransaction([]);
        }

        $this->log(__METHOD__.": Partner xau balance is sufficient to perform this transaction", SNAP_LOG_INFO);
    }

   

    /**
     * Generates a reference number for an object
     * 
     * @param string      $prefix    The prefix for the reference number
     * @param dbdatastore $store     The object store
     */
    public function generateRefNo($prefix, $store)
    {
        $app = $this->app;
        $cacher = $app->getCacher();

        $now = new \DateTime(date('Y-m-d H:00:00'));
        $now->setTimezone($app->getUserTimezone());
        $cacheKey = "ref:{$prefix}_" . $now->format("ymdH");

        $cacher->waitForLock($cacheKey."_lock", 1, 60, 60);
        $next = $cacher->increment($cacheKey, 1, 5400);    // keep key for 1H30M
        if (1 == $next) {
            // Resume count
            $totalHourCount = $store->searchTable()->select()->where('createdon', '>=', $now->format('Y-m-d H:00:00'))->count();
            if (0 < $totalHourCount) {
                $next = $totalHourCount + 1;
                $cacher->set($cacheKey, $next, 5400);
            } else {
                $this->log(__METHOD__. "(): New reference sequence for ($cacheKey)", SNAP_LOG_INFO);
            }
        }
        $cacher->unlock($cacheKey."_lock");

        $now->setTimezone($app->getUserTimezone());
        $refNo = strtoupper(sprintf("%s%s%05d", $prefix, $now->format('ymdH'), $next));
        $this->log(__METHOD__."(): Generated refno $refNo");

        return $refNo;
    }

    public function getPartnerBalances($partner){
        //$return = $this->app->mygtppartnerManager()->getPartnerInventoryBalance($partner);
        
        // Get Vault Location
        if($this->app->getConfig()->{'projectBase'} == 'BSN'){
            $bmmbxau = 0;  
        }else{
            $vaultLocation = $this->app->vaultlocationStore()->searchView()->select()->where('partnerid', $partner->id)->one();

            // Get all vault amount
            // Vaultlocation based on bmmb
            $items = $this->app->vaultitemStore()->searchView()->select()->where('partnerid', $partner->id)->andWhere('vaultlocationid', $vaultLocation->id)->execute();

            $bmmbxau = $ordxau = 0;

            foreach ($items as $item){
                $bmmbxau = $item->weight + $bmmbxau;
                $bmmbxau = $partner->calculator(false)->round($bmmbxau);
            }
        }
        // Get Pending Transactions
        $balance = $this->app->mygtppartnerManager()->getPartnerInventoryBalance($partner);

        // Get Customer Holding
        $customerHolding = $bmmbxau - $balance;
        $customerHolding = $partner->calculator(false)->round($customerHolding);
       

        // Get Pending Transactions
        $pendingTransactions = $this->app->mygoldtransactionStore()->searchView()->select()
            ->where('status', MyGoldTransaction::STATUS_PENDING_PAYMENT)
            ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
            ->execute();
    
        
        foreach ($pendingTransactions as $pendingTransaction){
            $ordxau += $pendingTransaction->ordxau;
        }

        $bmmbxau = number_format($bmmbxau, 3);
        $customerHolding = number_format($customerHolding, 3);
        $balance = number_format($balance, 3);

        //print_r($pendingTranscations);
        // Query vault amount 
        $return['vaultamount'] = $bmmbxau;
        $return['totalcustomerholding'] = $customerHolding;
        $return['totalbalance'] = $balance;
        $return['pendingtransaction'] = $ordxau;

        return $return;
    }

    /**
     * Transfer gold from one account holder to another
     *
     * @param Partner $partner || $sender
     * @param MyAccountHolder $receiver
     * @param float $price
     * @param float $xau
     * @param float $amount
     * @param string $refNo     
     * @param string $remarks
     * @return MyAccountHolder
     */
    public function transferGoldToAccountHolder($partner, $receiver, $price, $xau, $amount, $refNo, $remarks)
    {
        try {
            $this->logDebug(__METHOD__ . ":() Begin transferring gold {$xau}g from {$partner->code} to {$receiver->accountholdercode}");

            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            // Use amount from input
            // $amount = $partner->calculator()->multiply($xau, $price);
            $transfer = $this->app->mygoldtransferStore()->create([                
                'accountholderid'   => $receiver->id,
                'price'             => $price,
                'xau'               => $xau,
                'amount'            => $amount,
                'remarks'           => $remarks,                
                'status'            => MyGoldTransfer::STATUS_ACTIVE
            ]);
            $transfer = $this->app->mygoldtransferStore()->save($transfer);

            $now = new \DateTime('now', $this->app->getUserTimezone());
            
            $this->logDebug("Creating partner ledger for transfer $refNo");
            $ledger = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_PROMO,
                'typeid'            => $transfer->id,
                'accountholderid'   => 0,
                'partnerid'         => $partner->id,
                'debit'             => $xau,
                'credit'            => 0.00,
                'refno'             => $refNo,
                'remarks'           => 'Special - Transfer Out - '.$remarks,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledger = $this->app->myledgerStore()->save($ledger);
            $this->logDebug("Finished saving partner ledger for transfer $refNo");
            
            $this->logDebug("Creating customer ledger for gold transaction $refNo");
            $ledger = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_PROMO,
                'typeid'            => $transfer->id,
                'accountholderid'   => $receiver->id,
                'partnerid'         => $receiver->partnerid,
                'debit'             => 0.00,
                'credit'            => $xau,
                'refno'             => $refNo,
                'remarks'           => $remarks,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledger = $this->app->myledgerStore()->save($ledger);
            $this->logDebug("Finished saving customer ledger for transfer $refNo");

            if (! $receiver->initialInvestmentMade()) {
                $receiver->investmentmade = true;
                $receiver = $this->app->myaccountholderStore()->save($receiver, ['investmentmade']);

                $this->logDebug(__METHOD__ . ":() Updated {$receiver->accountholdercode} initial investment status to ({$receiver->investmentmade})");
            }

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }

            $this->logDebug(__METHOD__ . ":() Finished transferring gold {$xau}g from {$partner->code} to {$receiver->accountholdercode}");

        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            $this->log(__METHOD__ . ":() Error transferring gold {$xau}g from {$partner->code} to {$receiver->accountholdercode}, {$e->getMessage()}", SNAP_LOG_ERROR);            
            throw $e;
        }
    }

    /**
     * Import gold transfer instruction from file
     *
     * @param Partner         $partner
     * @param string          $filename     
     * @param bool            $skipHeader
     * @param bool            $preCheck     Run without actually crediting the gold transfer
     * @return bool
     */
    public function importGoldTransfer(Partner $partner, $filename, $skipHeader = true, $preCheck = false)
    {
        $this->logDebug(__METHOD__ . ":() Begin processing gold transfer from file {$filename}");

        $fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($filename);
        if ($fileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }elseif ($fileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }else {
            throw new \Exception('File type not supported: '. $fileType);
        }

        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($filename);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        
        $goldTransferLog = $this->app->mygoldtransferlogStore()->create([
            'filename' => $filename,
            'filecontent' => json_encode($sheetData),
            'partnerid' => $partner->id,                
        ]);

        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            $total = 0;
            $errors = [];
            $successRows = [];
            $failedRows = [];
            $headerRows = [];
            $originalFileName = basename($filename);

            foreach ($sheetData as $i => $row){
                if (1 == $i && $skipHeader) {
                    $row['M'] = $preCheck ? 'Pre-Check Status' : 'Import Status';
                    $headerRows[] = $row;
                    continue;
                }
    
                $this->logDebug(__METHOD__ . ': Reading line data ' . json_encode($row));
                
                $accHolderCode = trim($row['C']);
                $nric = trim($row['D']);
                $transferRefNo = trim($row['A']);
                $partnerCampaignSpotOrderRef = trim($row['L']);

                $price = trim($row['G']);
                $xau = trim($row['H']);
                $amount = trim($row['J']);

                
                try {
                
                    if (!$partnerCampaignSpotOrderRef){
                        throw new \Exception("Empty Speical Order Ref in file");
                    }

                    if (0 >= strlen($transferRefNo)) {
                        throw new \Exception("Transfer ref no not found");
                    }

                    if (!$preCheck && (0 >= $price || 0 >= $xau || 0 >= $amount)) {
                        throw new \Exception("Invalid data found for Price ({$price}), Xau ({$xau}), Amount ({$amount})");
                    }
                    
                    $exists = $this->app->myledgerStore()->searchTable()->select()
                        ->where('partnerid', $partner->id)
                        ->where('type', MyLedger::TYPE_PROMO)
                        ->where('refno', $transferRefNo)
                        ->where('status', MyLedger::STATUS_ACTIVE)
                        ->exists();

                    if ($exists) {
                        $this->log(__METHOD__ . "(): Aborting transfer as refno ({$transferRefNo}) already exists for partner ({$partner->code})");
                        throw MyGtpTransactionExists::fromTransaction(null, ['message' => "Transfer refno ({$transferRefNo}) already exists for partner ({$partner->code})"]);
                    }

                    if (0 < strlen($accHolderCode)) {

                        // If AccCode is given, we get acc using code
                        /** @var MyAccountHolder $receiver */
                        $receiver = $this->app->myaccountholderStore()->getByField('accountholdercode', $accHolderCode);
                        
                        if ($receiver->mykadno != $nric) {
                            $this->log(__METHOD__ . "(): Accountholder ({$receiver->accountholdercode}) NRIC ({$nric}) does not matched with the record {$receiver->mykadno}");
                            throw MyGtpAccountHolderNotExist::fromTransaction(null, ['message' => "Accountholder ({$receiver->accountholdercode}) NRIC ({$nric}) does not matched with the record {$receiver->mykadno}"]);
                            
                        }
                    } elseif (0 < strlen($nric)) {
                        // If exists more than 1 same IC we could not determine which one we should credit the gold to, so throw exception
                        if (1 < $this->app->myaccountholderStore()->searchTable()->select()->where('mykadno', $nric)->andWhere('partnerid', $partner->id)->andWhere('status', MyAccountHolder::STATUS_ACTIVE)->count()) {
                            throw MyGtpAccountHolderMyKadExists::fromTransaction(null, ['message' => "Accountholder ({$accHolderCode}) NRIC ({$nric}) record exists more than 1"]);
                        }
                        
                        // If not given acc code then we try to get using nric
                        /** @var MyAccountHolder $receiver */
                        $receiver = $this->app->myaccountholderStore()->searchTable()->select()->where('mykadno', $nric)->andWhere('partnerid', $partner->id)->andWhere('status', MyAccountHolder::STATUS_ACTIVE)->one();

                        // Since acc code not given on input file, we write back acc code to output file
                        $row['C'] = $receiver->accountholdercode;
                    }

                    if (0 === strlen($row['B'])) {
                        $row['B'] = $receiver->fullname;
                    }

                    if (0 === strlen($row['E'])) {
                        $row['E'] = $receiver->phoneno;
                    }

                    if (0 === strlen($row['F'])) {
                        $row['F'] = $receiver->email;
                    }

                    if (! $receiver) {
                        $this->log(__METHOD__ . "(): Accountholder ({$accHolderCode}) NRIC ({$nric}) record could not be found", SNAP_LOG_ERROR);
                        throw MyGtpAccountHolderNotExist::fromTransaction(null, ['message' => "Accountholder ({$accHolderCode}) NRIC ({$nric}) record could not be found"]);
                    }

                    if (! $receiver->isActive()) {
                        $this->log(__METHOD__ . "(): Accountholder ({$accHolderCode}) NRIC ({$nric}) is not active", SNAP_LOG_ERROR);
                        throw MyGtpAccountHolderNotActive::fromTransaction(null, ['message' => "Accountholder ({$accHolderCode}) NRIC ({$nric}) is currently not active"]);
                    }
        
                    if (! $preCheck) {
                        $this->transferGoldToAccountHolder($partner, $receiver, $price, $xau, $amount, $transferRefNo, $partnerCampaignSpotOrderRef);
                    }

                    $total++;
                    $row['M'] = 'Pass';
                    $successRows[] = $row;
                
                } catch (MyGtpTransactionExists $e) {
                    $errors[] = $e->getMessage();
                    $row['M'] = $e->getMessage();
                    $failedRows[] = $row;
                } catch (MyGtpAccountHolderNotExist $e) {
                    $errors[] = $e->getMessage();
                    $row['M'] = $e->getMessage();
                    $failedRows[] = $row;
                } catch (MyGtpAccountHolderNotActive $e) {
                    $errors[] = $e->getMessage();
                    $row['M'] = $e->getMessage();
                    $failedRows[] = $row;
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                    $row['M'] = $e->getMessage();
                    $failedRows[] = $row;
                }
            }

            if (0 < count($errors)) {
                $errStr = implode(PHP_EOL, $errors);
                throw new Exception($errStr);
            }
            
            if (! $preCheck) {
                $goldTransferLog->remarks = 'Total number of transfer: '. $total;
                $goldTransferLog->status = MyGoldTransferLog::STATUS_SUCCESS;
                $this->app->mygoldtransferlogStore()->save($goldTransferLog);
                $outputFileName = 'SUCCESS_' . date('Ymd') . '_' . time() . '_' . $originalFileName;
            } else {
                $outputFileName = 'SUCCESS_PRECHECK_' . date('Ymd') . '_' . time() . '_' . $originalFileName;
            }

            $mergedArray = array_merge($headerRows, $successRows, $failedRows);
            $this->logDebug(__METHOD__ . ":() Finished processing gold transfer from file {$filename}");

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            
            if (! $preCheck) {
                $goldTransferLog->remarks = $e->getMessage();
                $goldTransferLog->status = MyGoldTransferLog::STATUS_FAILED;
                $this->app->mygoldtransferlogStore()->save($goldTransferLog);
                $outputFileName = 'FAILED_' . date('Ymd') . '_' . time() . '_' . $originalFileName;
            } else {
                $outputFileName = 'FAILED_PRECHECK_' . date('Ymd') . '_' . time() . '_' . $originalFileName;
            }

            $mergedArray = array_merge($headerRows, $failedRows, $successRows);

            $this->log(__METHOD__ . ":() Error processing gold transfer from file ({$filename}) - {$e->getMessage()}", SNAP_LOG_ERROR);
            throw $e;
        } finally {
            $outputPath = $this->app->getConfig()->{'mygtp.goldcrediting.outputpath'};            
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();            
            $spreadsheet->getActiveSheet()->fromArray($mergedArray);
            $spreadsheet->getActiveSheet()->getStyle("A1:F" . count($sheetData))->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);

            if ($fileType == 'Xls'){
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xls($spreadsheet);
            }elseif ($fileType == 'Xlsx'){
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            }
            
            $writer->save($outputPath . DIRECTORY_SEPARATOR . $outputFileName);
        }
    }

    /**
     * Create loan transaction
     *
     * @param MyAccountHolder $accHolder
     * @param MyGoldTransaction $goldTx
     * @param MyPaymentDetail $payment
     * 
     * @return mixed
     */
    protected function createLoanTransaction($accHolder, $goldTx, $order)
    {
        try {
            
            $this->logDebug("Creating loan record for gold transaction $goldTx->refno");
            if (!$this->app->getDBHandle()->inTransaction()) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }
            
            
            // Lock for getting account holder loan balance
            $lockKey = '{AccountLoanBalance}:' . $accHolder->id;
            $cacher = $this->app->getCacher();
            $cacher->waitForLock($lockKey, 1, 60, 60);

            // Get latest balance
            $accHolder = $this->app->myaccountholderStore()->getById($accHolder->id);
            $amount = number_format(floatval($goldTx->originalamount) + floatval($order->fee), 2, '.', '');

            // If not enough balance throw
            if (0 > $accHolder->loanbalance - $amount) {
                $this->log(__METHOD__ . "(): Insufficient loan amount balance for account holder {$accHolder->accountholdercode}", SNAP_LOG_ERROR);                

                throw MyGtpLoanInsufficientBalance::fromTransaction([], [
                    'message' => gettext("Insufficient loan amount balance.")
                ]);
            }
            
            // Save new balance
            $accHolder->loanbalance = $accHolder->loanbalance - $amount;
            $accHolder = $this->app->myaccountholderStore()->save($accHolder);
            $loanTx = $this->app->myloantransactionStore()
                                ->create([
                                    'achid' => $order->buyerid,
                                    'transactiontype' => Order::TYPE_COMPANYBUY == $order->type ? MyLoanTransaction::TYPE_CREDIT : MyLoanTransaction::TYPE_DEBIT,
                                    'gtrrefno' => $goldTx->refno,
                                    'transactionamount' => $amount,
                                    'xau' => $order->xau,
                                ]);
            $loanTx = $this->app->myloantransactionStore()->save($loanTx);
                            
            $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $goldTx->refno);
            $payment->status         = MyPaymentDetail::STATUS_SUCCESS;
            $payment->verifiedamount = $loanTx->transactionamount;
            $payment                 = $this->app->mypaymentdetailStore()->save($payment);
            
            $cacher->unlock($lockKey);
            
            $this->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_BUY_FPX);
            
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
            
            $this->logDebug("Finished saving loan record for gold transaction $goldTx->refno");
        } catch (\Exception $e) {
            $cacher->unlock($lockKey);
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            throw $e;
        }
        

        return $loanTx;
    }

    public function getPaymentDetailStateMachine(MyPaymentDetail $payment)
    {
        $config = [
            'property_path' => 'status',
            'states' => [
                MyPaymentDetail::STATUS_PENDING             => ['type' => 'initial', 'properties' => []],
                MyPaymentDetail::STATUS_PENDING_PAYMENT     => ['type' => 'normal', 'properties' => []],
                MyPaymentDetail::STATUS_SUCCESS             => ['type' => 'normal', 'properties' => []],
                MyPaymentDetail::STATUS_REFUNDED            => ['type' => 'final', 'properties' => []],
                MyPaymentDetail::STATUS_FAILED              => ['type' => 'final', 'properties' => []],
                MyPaymentDetail::STATUS_CANCELLED           => ['type' => 'final', 'properties' => []],

            ],
            'transitions' => [
                MyPaymentDetail::STATUS_PENDING_PAYMENT     => ['from' => [MyPaymentDetail::STATUS_PENDING], 'to' => MyPaymentDetail::STATUS_PENDING_PAYMENT],
                MyPaymentDetail::STATUS_SUCCESS             => ['from' => [MyPaymentDetail::STATUS_PENDING_PAYMENT], 'to' => MyPaymentDetail::STATUS_SUCCESS],
                MyPaymentDetail::STATUS_REFUNDED            => ['from' => [MyPaymentDetail::STATUS_SUCCESS], 'to' => MyPaymentDetail::STATUS_REFUNDED],
                MyPaymentDetail::STATUS_FAILED              => ['from' => [MyPaymentDetail::STATUS_PENDING_PAYMENT], 'to' => MyPaymentDetail::STATUS_FAILED],
                MyPaymentDetail::STATUS_CANCELLED           => ['from' => [MyPaymentDetail::STATUS_PENDING_PAYMENT], 'to' => MyPaymentDetail::STATUS_CANCELLED],

            ]
        ];
        return $this->createStateMachine($payment, $config);
    }

    public function getGoldTransactionStateMachine(MyGoldTransaction $gt)
    {
        $config = [
            'property_path' => 'status',
            'states' => [
                MyGoldTransaction::STATUS_PENDING_PAYMENT     => ['type' => 'initial', 'properties' => []],
                MyGoldTransaction::STATUS_REVERSED            => ['type' => 'final', 'properties' => []],
                MyGoldTransaction::STATUS_FAILED              => ['type' => 'final', 'properties' => []],
                MyGoldTransaction::STATUS_PAID                => ['type' => 'normal', 'properties' => []],
                MyGoldTransaction::STATUS_CONFIRMED           => ['type' => 'final', 'properties' => []],
                MyGoldTransaction::STATUS_PENDING_APPROVAL    => ['type' => 'normal', 'properties' => []],
                MyGoldTransaction::STATUS_REJECTED            => ['type' => 'final', 'properties' => []],

                // MyGoldTransaction::STATUS_CONFIRM_FAILED      => ['type' => 'final', 'properties' => []],

            ],
            'transitions' => [
                MyGoldTransaction::STATUS_FAILED                => ['from' => [MyGoldTransaction::STATUS_PENDING_PAYMENT], 'to' => MyGoldTransaction::STATUS_FAILED],
                MyGoldTransaction::STATUS_PAID                  => ['from' => [MyGoldTransaction::STATUS_PENDING_PAYMENT, MyGoldTransaction::STATUS_FAILED, MyGoldTransaction::STATUS_PENDING_APPROVAL], 'to' => MyGoldTransaction::STATUS_PAID],
                MyGoldTransaction::STATUS_REVERSED              => ['from' => [MyGoldTransaction::STATUS_PAID], 'to' => MyGoldTransaction::STATUS_REVERSED],
                MyGoldTransaction::STATUS_CONFIRMED             => ['from' => [MyGoldTransaction::STATUS_PAID], 'to' => MyGoldTransaction::STATUS_CONFIRMED],
                MyGoldTransaction::STATUS_PENDING_PAYMENT      => ['from' => [MyGoldTransaction::STATUS_PENDING_APPROVAL], 'to' => MyGoldTransaction::STATUS_PENDING_PAYMENT],
                MyGoldTransaction::STATUS_REJECTED      => ['from' => [MyGoldTransaction::STATUS_PENDING_APPROVAL], 'to' => MyGoldTransaction::STATUS_REJECTED],
                // MyGoldTransaction::STATUS_CONFIRM_FAILED        => ['from' => [MyGoldTransaction::STATUS_CONFIRMED], 'to' => MyGoldTransaction::STATUS_CONFIRM_FAILED],

            ]
        ];
        return $this->createStateMachine($gt, $config);
    }

    /**
     *
     * @return \Finite\StateMachine\StateMachine
     */
    private function createStateMachine($object, array $config)
    {
        $stateMachine = new \Finite\StateMachine\StateMachine;

        $loader = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($object);
        $stateMachine->initialize();
        return $stateMachine;
    }

    public function updatePendingRefundStatus(MyGoldTransaction $goldTransaction, $status){
        try{
            $whoUpdate = $this->app->getUserSession()->getUser()->id;
            $dt = date('Y-m-d H:i:s');
            $modifiedDate = new \DateTime($dt);
            $modifiedDate->setTimezone($this->app->getUserTimezone());

            $goldTransaction->status = $status;
            $goldTransaction->modifiedon = $modifiedDate;
            $goldTransaction->modifiedby = $whoUpdate;

            $bool = $this->app->myGoldTransactionStore()->save($goldTransaction);

            if($bool){
                $this->log(__METHOD__ . "(): mygoldtransaction->".$goldTransaction->id." status updated to ".$status." by user->id ".$whoUpdate);
                return array('success' => true,'message' => '');
            }
            else{
                $this->log(__METHOD__ . "(): mygoldtransaction->".$goldTransaction->id." status failed to update by user->id ".$whoUpdate);
                return array('success' => false,'message' => 'Failed save data');
            }
        }
        catch(\Exception $e){
            $this->log(__METHOD__ . "(): mygoldtransaction->".$goldTransaction->id." status failed to update by user->id ".$whoUpdate.". Message: ".$e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    public function generateBulkPaymentReport($dateStart, $dateEnd, $conditions){ //previously use as bulk payment. now using the function with same name at mydisbursement manager
        $generateText = new \Snap\api\mbb\AceBulkPayment($this->app,false);


        $currDate       = date("Y-m-d h:i:s", strtotime('now')); //UTC
        /*user timezone*/
        $checkDate          = new \DateTime($currDate, $this->app->getUserTimezone());
        $convertToUserTime  = \Snap\common::convertUTCToUserDatetime($checkDate);
        $userDate           = $convertToUserTime->format('Y-m-d H:i:s');
        /**/

        $partner        = $conditions['partner'];
        $refno          = $conditions['refno'];
        $nameoffile     = $conditions['filename'];
        $dateGenerate   = date("dmYHis", strtotime($userDate));
        if($partner->sapcompanysellcode1 != '') $pCode = $partner->sapcompanysellcode1;
        $batchId        = $dateGenerate.$pCode;

        /****/
        $dateStart      = new \DateTime($dateStart, $this->app->getUserTimezone()); 
        $dateEnd        = new \DateTime($dateEnd, $this->app->getUserTimezone()); 
        //$dateStart      = \Snap\common::convertUTCToUserDatetime($dateStart);
        $startAt        = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
        $saveStartAt    = $startAt;
        $startAt        = \Snap\common::convertUserDatetimeToUTC($startAt);
        $endAt          = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
        $saveEndAt      = $endAt;
        $endAt          = \Snap\common::convertUserDatetimeToUTC($endAt);
        /*****/
        $clientbatchid  = $batchId;
        $aceaccid   = $this->app->getConfig()->{'gtp.bulk.accbank'};
        $aceaccno   = $this->app->getConfig()->{'gtp.bulk.accno'};
        $provprod   = $this->app->getConfig()->{'gtp.bulk.product'};
        $currfix    = $this->app->getConfig()->{'gtp.bulk.curr'};
        $defbank    = $this->app->getConfig()->{'gtp.bulk.intrabank'};
        $valuedate      = $dateGenerate;
        $additionalDetails = [
            'clientbatchid' => $clientbatchid,
            'aceaccid' => $aceaccid,
            'aceaccno' => $aceaccno,
            'valuedate' => $valuedate,
            'provprod' => $provprod,
            'currfix' => $currfix,
            'defbank' => $defbank,
            'filename' => $nameoffile
        ];

        // Get Pending Transactions
        if($refno){
            $buyTransactionsC = $this->app->mygoldtransactionStore()->searchView(false)->select()
                ->where('status', 'IN' ,[MyGoldTransaction::STATUS_PENDING_PAYMENT])
                ->andWhere('ordpartnerid', $partner->id)
                ->andWhere('refno', 'IN' ,$refno)
                ->execute();
        } else {
            $buyTransactionsC = $this->app->mygoldtransactionStore()->searchView(false)->select()
                ->where('status', 'IN' ,[MyGoldTransaction::STATUS_PENDING_PAYMENT])
                ->andWhere('ordpartnerid', $partner->id)
                ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT)
                ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                ->execute();
        }
    
        $totalItem = count($buyTransactionsC);
        if($totalItem > $this->limit) $paging = ceil($totalItem / $this->limit);
        else $paging = 1;
        
        for($i=1;$i<=$paging;$i++){
            $offset = ($i-1) * $this->limit;
            if($refno){
                $buyTransactions = $this->app->mygoldtransactionStore()->searchView()->select()
                ->where('status', 'IN' ,[MyGoldTransaction::STATUS_PENDING_PAYMENT])
                ->andWhere('ordpartnerid', $partner->id)
                ->andWhere('refno', 'IN' ,$refno)
                ->execute();
            } else {
                $buyTransactions = $this->app->mygoldtransactionStore()->searchView()->select()
                    ->where('status', 'IN' ,[MyGoldTransaction::STATUS_PENDING_PAYMENT])
                    ->andWhere('ordpartnerid', $partner->id)
                    ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT)
                    ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    ->execute();
            }
            $paymentOutput = $generateText->checkFile($buyTransactions,$currDate,$totalItem,$paging,$i,$additionalDetails);
        }


    }

    public function sendTransactionBetweenDb($goldTx,$order = null,$specialcondition=null){
        $this->log("[Transfer DB Process] Transaction with refno ".$goldTx->refno." need to trasnfer to other db", SNAP_LOG_DEBUG);

        //need to grab because if using $goldTx, obj did not properly convert to array.
        if ($specialcondition == 'failed' || $specialcondition == 'pendingrefund' ) $goldTx = $this->app->mygoldtransactionStore()->getById($goldTx->id, [], false, 1); 
        
        /*check status first*/
        $myGoldStatus = $goldTx->status;

        if($myGoldStatus == MyGoldTransaction::STATUS_PAID){ //means if mygoldtransaction is PAID
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone()); 
            // Confirm order first
            $order->status = Order::STATUS_CONFIRMED;
            $order->confirmon = $now;
            $updateOrder = $this->app->orderStore()->save($order);
            $this->log("[Transfer DB Process] Success change status order $updateOrder->partnerrefid before transfer to DB", SNAP_LOG_DEBUG);

            // change mygoldtransaction status
            $goldTx->status = MyGoldTransaction::STATUS_CONFIRMED;
            $goldTx->completedon = $now;
            $updateGoldTransaction = $this->app->mygoldtransactionStore()->save($goldTx);

            // get the view of gold tx instead
            $goldTx = $this->app->mygoldtransactionStore()->searchView()->select()
                                    ->where('id', $goldTx->id)
                                    ->one();
        }

        $this->log("########START SENDING ORDER ".$order->partnerrefid." BETWEEN DB##########", SNAP_LOG_DEBUG);
        $transferOrder = $this->app->apiManager()->transferOrderBetweenDb($goldTx,$order); 
        $this->log("########END SENDING ORDER ".$order->partnerrefid." BETWEEN DB##########", SNAP_LOG_DEBUG);
    }

    public function registerGoldFromDb(Partner $partner,$goldtrans,$order,$paymentDisbursetransactions,$ledgerTransactions){
        $this->log("[Transfer DB Process] Start checking if mygoldtransaction already exist or not for ".$order->partnerrefid, SNAP_LOG_DEBUG);
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone()); 
        /*check if mygoldtransactin exist*/
        $goldTrx = $this->app->mygoldtransactionStore()->getByField('refno',$order->partnerrefid); 

        if(!$goldTrx){
            $goldTrx = $this->app->mygoldtransactionStore()
                    ->create([
                        'originalamount'    => $goldtrans['originalamount'],
                        'settlementmethod'  => $goldtrans['settlementmethod'],
                        'salespersoncode'   => $goldtrans['salespersoncode'],
                        'refno'             => $order->partnerrefid,
                        'orderid'           => $order->id,
                        'campaigncode'      => $goldtrans['campaigncode'],
                        'completedon'       => $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['completedon']),
                        'reversedon'        => $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['reversedon']),
                        'failedon'          => $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['failedon']),
                        'status'            => $goldtrans['status']
                    ]);

            $this->log("[Transfer DB Process] Create new mygoldtransaction ".$order->partnerrefid." from other db", SNAP_LOG_DEBUG);
        } else {
            /*update status*/
           $goldTrx->status         = $goldtrans['status'];
           $goldTrx->completedon    = $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['completedon']);
           $goldTrx->reversedon     = $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['reversedon']);
           $goldTrx->failedon       = $this->app->spotorderManager()->formatDateTimeWhenTransfer($goldtrans['failedon']);
            $this->log("[Transfer DB Process] mygoldtransaction ".$order->partnerrefid." from other db already exist. . Change status successfully to ".$ordTrans['status'], SNAP_LOG_DEBUG);
        }
        $saveGold = $this->app->mygoldtransactionStore()->save($goldTrx);

        if($saveGold) {
            /*update paymentdetails for companysell*/
            if('CompanySell' == $order->type){
                $paymentDetailStore = $this->app->mypaymentdetailStore();
                $paymentTrx = $paymentDetailStore->getByField('sourcerefno',$saveGold->refno); 
                if(!$paymentTrx){
                    $paymentTrx = $paymentDetailStore->create([
                                    'amount'            => $paymentDisbursetransactions['amount'],
                                    'customerfee'       => $paymentDisbursetransactions['customerfee'],
                                    'accountholderid'   => 0,
                                    'paymentrefno'      => $paymentDisbursetransactions['paymentrefno'],
                                    'sourcerefno'       => $saveGold->refno,
                                    'status'            => $paymentDisbursetransactions['status'],
                                    'productdesc'       => $paymentDisbursetransactions['productdesc'],
                                    'gatewaystatus'     => $paymentDisbursetransactions['gatewaystatus'],
                                    'transactiondate'   => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['transactiondate'],'transactiondate'),
                                    'requestedon'       => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['requestedon']),
                                    'successon'         => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['successon']),
                                    'failedon'          => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['failedon']),
                                    'refundedon'        => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['refundedon']),
                                    'verifiedamount'    => $paymentDisbursetransactions['verifiedamount'],
                                ]);
                    $this->log("[Transfer DB Process] Create new mypaymentdetails ".$saveGold->refno." from other db", SNAP_LOG_DEBUG);
                } else {
                    $paymentTrx->status         = $paymentDisbursetransactions['status'];
                    $paymentTrx->gatewaystatus  = $paymentDisbursetransactions['gatewaystatus'];
                    $paymentTrx->requestedon    = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['requestedon']);
                    $paymentTrx->successon      = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['successon']);
                    $paymentTrx->failedon       = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['failedon']);
                    $paymentTrx->refundedon     = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['refundedon']);
                    $this->log("[Transfer DB Process] mypaymentdetails ".$saveGold->refno." from other db already exist. . Change status successfully to ".$paymentDisbursetransactions['status'], SNAP_LOG_DEBUG);
                }
                $savePaymentDetails = $paymentDetailStore->save($paymentTrx);
            }
            
            /*update mydisbursement for companybuy*/
            elseif('CompanyBuy' == $order->type){
                $disbursementStore = $this->app->mydisbursementStore();
                $disburseTrx = $disbursementStore->getByField('transactionrefno',$saveGold->refno); 
                if(!$disburseTrx){
                    $disburseTrx = $disbursementStore->create([
                                    'accountholderid'   => 0,
                                    'bankid'            => $paymentDisbursetransactions['bankid'],
                                    'accountname'       => $paymentDisbursetransactions['accountname'],
                                    'accountnumber'     => $paymentDisbursetransactions['accountnumber'],
                                    'amount'            => $paymentDisbursetransactions['amount'],
                                    'fee'               => $paymentDisbursetransactions['fee'],
                                    'refno'             => $paymentDisbursetransactions['refno'],
                                    'transactionrefno'  => $saveGold->refno,
                                    'requestedon'       => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['requestedon']),
                                    'disbursedon'       => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['disbursedon']),
                                    'cancelledon'       => $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['cancelledon']),
                                    'status'            => $paymentDisbursetransactions['status'],
                                ]);
                    $this->log("[Transfer DB Process] Create new mydisbursement ".$saveGold->refno." from other db", SNAP_LOG_DEBUG);
                } else {
                    $disburseTrx->status        = $paymentDisbursetransactions['status'];
                    $disburseTrx->disbursedon   = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['disbursedon']);
                    $disburseTrx->cancelledon   = $this->app->spotorderManager()->formatDateTimeWhenTransfer($paymentDisbursetransactions['cancelledon']);
                    $this->log("[Transfer DB Process] mydisbursement ".$saveGold->refno." from other db already exist. . Change status successfully to ".$paymentDisbursetransactions['status'], SNAP_LOG_DEBUG);
                }
                $saveDisbursement = $disbursementStore->save($disburseTrx);
            }

            if($ledgerTransactions){
                $this->logDebug("[Transfer DB Process] Creating partner ledger for gold transaction $goldTx->refno");
                /*check if ledger exist*/
                $ledgerStore = $this->app->myledgerStore();
                $ledgerTrx = $ledgerStore->getByField('refno',$saveGold->refno); 
                if(!$ledgerTrx){
                    $partnerLedger = $ledgerStore->create([
                        'accountholderid'   => 0,
                        'partnerid'         => $order->partnerid,
                        'refno'             => $saveGold->refno,
                        'typeid'            => $saveGold->id,
                        'transactiondate'   => $this->app->spotorderManager()->formatDateTimeWhenTransfer($ledgerTransactions['transactiondate'],'transactiondate'),
                        'type'              => $ledgerTransactions['type'],
                        'credit'            => $ledgerTransactions['credit'],
                        'debit'             => $ledgerTransactions['debit'],
                        'status'            => $ledgerTransactions['status']
                    ]);

                    $updateLedger = $ledgerStore->save($partnerLedger);
                    $this->logDebug("[Transfer DB Process] Finished saving partner ledger for gold transaction $saveGold->refno");
                } else {
                    $this->logDebug("[Transfer DB Process] Ledger for gold transaction $saveGold->refno already exist");
                }
            }
            $this->log("[Transfer DB Process] mygoldtransaction ".$saveGold->refno." from other db successfully add/update.", SNAP_LOG_DEBUG);
        }
        return $saveGold;
    }

    public function sendTransactionToLedger($goldTransaction,$ledgerType){
        $ledgers = $this->createLedgersForGoldTransaction($goldTransaction, $ledgerType);
        return true;
    }

    public function updateGatewayReference($payment,$walletReference){
        $this->logDebug("Update gateway reference for payment $payment->paymentrefno");

        $payment = $this->app->mypaymentdetailStore()->getByField('paymentrefno', $payment->paymentrefno);

        if (! $payment) {
            $this->log(__METHOD__."(): Unable to find corresponding payment. Gateway ref: ({$payment->paymentrefno})", SNAP_LOG_ERROR);
            throw new \Exception("Unable to find corresponding payment. Gateway ref: ({$payment->paymentrefno})");
        }

        $payment->gatewayrefno = $walletReference;
        $payment = $this->app->mypaymentdetailStore()->save($payment, ['gatewayrefno']);

        $this->logDebug("Success updating gateway reference for payment $payment->paymentrefno");
        return true;
    }

    public function sendPendingRefundTrxToPartner($token, $accountholder, $partner){
        try{
            $cutOffTimeStart = new \DateTime("3 days ago");
            $cutOffTimeStartFormatted = $cutOffTimeStart->format('Y-m-d H:i:s');
            $this->log("Start date: {$cutOffTimeStartFormatted}", SNAP_LOG_DEBUG);
            //echo $cutOffTimeStartFormatted."\n";

            $cutOffTimeEnd = new \DateTime(date('Y-m-d')." 23:59:59");
            $cutOffTimeEndFormatted = $cutOffTimeEnd->format('Y-m-d H:i:s');
            $this->log("End date: {$cutOffTimeEndFormatted}", SNAP_LOG_DEBUG);
            //echo $cutOffTimeEndFormatted."\n";

            $partnersetting = $this->app->mypartnersettingStore()
                                ->searchTable()
                                ->select()
                                ->where('partnerid', '=', $partner->id)
                                ->forwardKey('partnerid')
                                ->get();
            
            $goldtxStore = $this->app->mygoldtransactionStore();
            $pendingRefundGoldTx = $goldtxStore->searchView()->select()
                                    ->where('ordpartnerid', '=', $partner->id)
                                    ->andWhere('createdon', '<=', $cutOffTimeEndFormatted)
                                    ->andWhere('createdon', '>=', $cutOffTimeStartFormatted)
                                    ->andWhere('status', MyGoldTransaction::STATUS_PENDING_REFUND)
                                    ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
                                    ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)
                                    ->andWhere('achmykadno', $accountholder->mykadno)
                                    ->execute();
        
            $ttl = count($pendingRefundGoldTx);
            $this->log("Total record: {$ttl}", SNAP_LOG_DEBUG);
            if ($ttl > 0) {
                $sent = $this->sendTrxToPartner($partnersetting, $pendingRefundGoldTx, $token);
            }
        }
        catch(\Exception $e){
            Throw new \Exception($e->getMessage());
        }
    }

    public function sendTrxToPartner($partnersetting, $pendingRefundGoldTx, $token){
        foreach ($pendingRefundGoldTx as $tx) {
            try {
                
                $refno = $tx->refno;
                $partnerId = $tx->ordpartnerid;
                $this->log("Send {$tx->refno} to partner {$tx->ordpartnerid}", SNAP_LOG_DEBUG);

                $paymentProvider = BaseWallet::getInstance($partnersetting[$partnerId]->partnerpaymentprovider);
                if (!$paymentProvider) {
                    throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                }
                $paymentDetail = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
                if (!$paymentDetail) {
                    throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                }

                $this->app->getDBHandle()->beginTransaction();
                $one = $paymentProvider->sendPendingRefundStatus($paymentDetail, $token);
                $this->app->getDBHandle()->commit();

                
            } catch (\Throwable $e) {
                // echo $e->getMessage();
                $this->log("ERROR sendTrxToPartner: ".$e->getMessage());
                if ($this->app->getDBHandle()->inTransaction()) {
                    $this->app->getDbHandle()->rollback();
                }

                Throw new \Exception($e->getMessage());
            }
        }
    }

    public function sendFailedTrxToPartner($token, $accountholder, $partner){
        try{
            $cutOffTimeStart = new \DateTime("5 days ago");
            $cutOffTimeStartFormatted = $cutOffTimeStart->format('Y-m-d H:i:s');
            $this->log("Start date: {$cutOffTimeStartFormatted}", SNAP_LOG_DEBUG);
            //echo $cutOffTimeStartFormatted."\n";

            $cutOffTimeEnd = new \DateTime(date('Y-m-d')." 23:59:59");
            $cutOffTimeEndFormatted = $cutOffTimeEnd->format('Y-m-d H:i:s');
            $this->log("End date: {$cutOffTimeEndFormatted}", SNAP_LOG_DEBUG);
            //echo $cutOffTimeEndFormatted."\n";

            $partnersetting = $this->app->mypartnersettingStore()
                                ->searchTable()
                                ->select()
                                ->where('partnerid', '=', $partner->id)
                                ->forwardKey('partnerid')
                                ->get();
            
            $goldtxStore = $this->app->mygoldtransactionStore();
            $failedGoldTx = $goldtxStore->searchView()->select()
                                    ->where('ordpartnerid', '=', $partner->id)
                                    ->andWhere('createdon', '<=', $cutOffTimeEndFormatted)
                                    ->andWhere('createdon', '>=', $cutOffTimeStartFormatted)
                                    ->andWhere('status', MyGoldTransaction::STATUS_FAILED)
                                    ->andWhere('ordtype', Order::TYPE_COMPANYSELL)
                                    ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)
                                    ->andWhere('achmykadno', $accountholder->mykadno)
                                    ->execute();
        
            $ttl = count($failedGoldTx);
            $this->log("Total record: {$ttl}", SNAP_LOG_DEBUG);
            if ($ttl > 0) {
                $sent = $this->processFailedTrxToPartner($partnersetting, $failedGoldTx, $token);
            }
        }
        catch(\Exception $e){
            Throw new \Exception($e->getMessage());
        }
    }

    public function processFailedTrxToPartner($partnersetting, $failedGoldTx, $token){
        $i = 0;
        foreach ($failedGoldTx as $tx) {
            try {
                $i += 1;
                $this->log("iterate: {$i}", SNAP_LOG_DEBUG);
                $refno = $tx->refno;
                $partnerId = $tx->ordpartnerid;
                $this->log("Send {$tx->refno} to partner {$tx->ordpartnerid}", SNAP_LOG_DEBUG);

                $paymentProvider = BaseWallet::getInstance($partnersetting[$partnerId]->partnerpaymentprovider);
                if (!$paymentProvider) {
                    throw new \Exception(__METHOD__ . "(): No partner payment provider found for partner {$partnerId}");
                }
                $paymentDetail = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
                if (!$paymentDetail) {
                    throw new \Exception(__METHOD__ . "(): No payment found for {$refno}");
                }

                $this->app->getDBHandle()->beginTransaction();
                $one = $paymentProvider->processFailedPaymentStatus($paymentDetail, $token);
                $this->app->getDBHandle()->commit();

                
            } catch (\Throwable $e) {
                // echo $e->getMessage();
                $this->log("ERROR sendTrxToPartner: ".$e->getMessage());
                if ($this->app->getDBHandle()->inTransaction()) {
                    $this->app->getDbHandle()->rollback();
                }

                Throw new \Exception($e->getMessage());
            }
        }
    }
    
    public function registerPaymentFromDb ($partner, $myConversion, $arrMyPaymentDetail)
    {
        $count = $this->app->mypaymentdetailStore()
                ->searchTable()
                ->select(['id'])
                ->where('sourcerefno', $myConversion->refno)
                ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyPaymentDetail, ['partnerrefno' => $myConversion->refno, 'code' => $partner->code, 'action' => 'payment']);
        }

        $arrMyPaymentDetail['sourcerefno'] = $myConversion->refno;
        $myPaymentDetail = $this->app->mypaymentdetailStore()->create($arrMyPaymentDetail);
        $myPaymentDetailSaved = $this->app->mypaymentdetailStore()->save($myPaymentDetail);
        
        return $myPaymentDetailSaved;
    }

    public function registerPaymentFromSvr_ ($partner, $myConversion, $arrMyPaymentDetail)	
    {	
        $count = $this->app->mypaymentdetailStore()	
                ->searchTable()	
                ->select(['id'])	
                ->where('sourcerefno', $myConversion->refno)	
                ->count();	
        if($count) {	
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyPaymentDetail, ['partnerrefno' => $myConversion->refno, 'code' => $partner->code, 'action' => 'payment']);	
        }	
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );	
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));	
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');	
        $arrMyPaymentDetail['sourcerefno'] = $myConversion->refno;	
        //$arrMyPaymentDetail['createdon'] = $triggerOn;	
        //$arrMyPaymentDetail['modifiedon'] = $triggerOn;	
        $myPaymentDetail = $this->app->mypaymentdetailStore()->create($arrMyPaymentDetail);	
        $myPaymentDetailSaved = $this->app->mypaymentdetailStore()->save($myPaymentDetail);	
        	
        return $myPaymentDetailSaved;	
    }
    
    public function registerLedgerFromDb ($partner, $myPaymentDetail, $myConversion, $arrMyLedger)
    {
        $count = $this->app->myledgerStore()
                ->searchTable()
                ->select(['id'])
                ->where('refno', $myPaymentDetail->sourcerefno)
                ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyLedger, ['partnerrefno' => $myPaymentDetail->sourcerefno, 'code' => $partner->code, 'action' => 'payment']);
        }

        $arrMyLedger['typeid'] = $myConversion->id;
        $arrMyLedger['partnerid'] = $partner->id;
        $arrMyLedger['refno'] = $myPaymentDetail->sourcerefno;
        $myLedger = $this->app->myledgerStore()->create($arrMyLedger);
        $myLedgerSaved = $this->app->myledgerStore()->save($myLedger);
        
        return $myLedgerSaved;
    }

    public function registerLedgerFromSvr_ ($partner, $myPaymentDetail, $myConversion, $arrMyLedger)	
    {	
        $count = $this->app->myledgerStore()	
                ->searchTable()	
                ->select(['id'])	
                ->where('refno', $myPaymentDetail->sourcerefno)	
                ->count();	
        if($count) {	
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyLedger, ['partnerrefno' => $myPaymentDetail->sourcerefno, 'code' => $partner->code, 'action' => 'payment']);	
        }	
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );	
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));	
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');	
        $arrMyLedger['typeid'] = $myConversion->id;	
        $arrMyLedger['partnerid'] = $partner->id;	
        $arrMyLedger['refno'] = $myPaymentDetail->sourcerefno;	
        //$arrMyLedger['createdon'] = $triggerOn;	
        //$arrMyLedger['modifiedon'] = $triggerOn;	
        $myLedger = $this->app->myledgerStore()->create($arrMyLedger);	
        $myLedgerSaved = $this->app->myledgerStore()->save($myLedger);	
        	
        return $myLedgerSaved;	
    }

    public function registerPaymentFromSvr ($partner, $myConversion, $arrMyPaymentDetail,$partnerName)
    {
        $sourcerefno = $arrMyPaymentDetail['sourcerefno']."_".$partnerName;
        $count = $this->app->mypaymentdetailStore()
                ->searchTable()
                ->select(['id'])
                ->where('sourcerefno', $sourcerefno)
                ->count();
        if($count) {
            // $myPaymentDetailSaved = $this->app->mypaymentdetailStore()->searchTable()->select()->where('sourcerefno',$sourcerefno)->one();
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyPaymentDetail, ['partnerrefno' => $sourcerefno, 'code' => $partner->code, 'action' => 'payment']);
        }
        // else{
        //     $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        //     $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        //     $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
        //     $arrMyPaymentDetail['sourcerefno'] = $sourcerefno;
        //     $arrMyPaymentDetail['createdon'] = $triggerOn;
        //     $arrMyPaymentDetail['modifiedon'] = $triggerOn;
        //     $myPaymentDetail = $this->app->mypaymentdetailStore()->create($arrMyPaymentDetail);
        //     $myPaymentDetailSaved = $this->app->mypaymentdetailStore()->save($myPaymentDetail);
        // }
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
        $arrMyPaymentDetail['sourcerefno'] = $sourcerefno;
        $arrMyPaymentDetail['createdon'] = $triggerOn;
        $arrMyPaymentDetail['modifiedon'] = $triggerOn;
        $myPaymentDetail = $this->app->mypaymentdetailStore()->create($arrMyPaymentDetail);
        $myPaymentDetailSaved = $this->app->mypaymentdetailStore()->save($myPaymentDetail);
        return $myPaymentDetailSaved;
    }

    public function registerLedgerFromSvr ($partner, $myPaymentDetail, $myConversion, $arrMyLedger)
    {
        $count = $this->app->myledgerStore()
                ->searchTable()
                ->select(['id'])
                ->where('refno', $myConversion->refno)
                ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyLedger, ['partnerrefno' => $myPaymentDetail->sourcerefno, 'code' => $partner->code, 'action' => 'payment']);
        }
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
        $arrMyLedger['typeid'] = $myConversion->id;
        $arrMyLedger['partnerid'] = $partner->id;
        $arrMyLedger['refno'] = $myConversion->refno;
        $arrMyLedger['createdon'] = $triggerOn;
        $arrMyLedger['modifiedon'] = $triggerOn;
        $myLedger = $this->app->myledgerStore()->create($arrMyLedger);
        $myLedgerSaved = $this->app->myledgerStore()->save($myLedger);
        
        return $myLedgerSaved;
    }
}
