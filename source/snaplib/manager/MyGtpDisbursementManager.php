<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\api\exception\GeneralException;
use Snap\IObservable;
use Snap\IObservation;
use Snap\IObserver;
use Snap\TObservable;
use Snap\TLogging;
use Snap\api\exception\MyGtpInvalidStartDate;
use Snap\api\exception\MyGtpPartnerSettingsNotInitialized;
use Snap\api\exception\OrderInvalidAction;
use Snap\api\payout\BasePayout;
use Snap\object\MyAccountHolder;
use Snap\object\MyGtpEventConfig;
use Snap\object\Partner;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;
use Snap\object\MyLedger;
use Snap\object\Order;
use Snap\object\Product;
use Snap\api\casa\BaseCasa;

/**
 * This class handles all logic for disbursement
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpDisbursementManager implements IObservable, IObserver
{
    use TLogging;
    use TObservable;

    /** @var Snap\App $app */
    private $app = null;
    private $limit = 5000;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if (($changed instanceof BasePayout || $changed instanceof BaseCasa) && $state->target instanceof MyDisbursement) {
            if ($state->isConfirmAction()) {
                $disbursement = $state->target;
                $this->onDisbursementCompleted($disbursement);
            } elseif ($state->isCancelAction()) {
                $disbursement = $state->target;
                $this->onDisbursementFailed($disbursement);
            } elseif ($state->isRejectAction()) {
                $disbursement = $state->target;
                $this->onDisbursementRejected($disbursement);
            } elseif ($state->isChangerequestAction()) {
                $disbursement = $state->target;
                $this->onDisbursementChange($disbursement);
            }
            // Handle disbursement cancelled
        }
    }

    /**
     * This method is used to update the reference numbers for company buy.  This will also send email internally to relevant parties
     * 
     * 
     * @param  Order $order  The order to be edited
     * @return Order
     */
    
    public function editReferenceNumber($disbursementRefNo, $gatewayRefNo, $bankRefNo, $transactionDate, $orderId)
    {
        // CAUTION booking order will not valid if PRICE_REQUEST_ID is used for CORE
        // price request id is consider as booking ID in aspect
        try {
            
            //$this->app->getDbHandle()->beginTransaction();
            //$counter = 0; 
            $errorMessages = [];
            // SAP_API will update sap_order_no, sap_order_ref, etc..
            for($i = 0; $i < count($disbursementRefNo); $i++ ) {
    
                $disbursement = $this->app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', $disbursementRefNo[$i])->one();
               
                // Check Disbursement Status if it is IN_PROGRESS
                if(MyDisbursement::STATUS_IN_PROGRESS == $disbursement->status){
                    $_now = new \DateTime();
                    $_now = \Snap\common::convertUTCToUserDatetime($_now);
                    $disbursement->modifiedon = $_now;
                    $disbursement->gatewayrefno = $gatewayRefNo[$i];
                    $disbursement->refno = $bankRefNo[$i];
                    $disbursement->requestedon = $transactionDate[$i];
                    $oldStatus[$i] = $disbursement->status;
                    $disbursement->status =  MyDisbursement::STATUS_COMPLETED;
                    //print_r($disbursement);
                    //STATUS_COMPLETED
                    $updateDisbursement = $this->app->mydisbursementStore()->save($disbursement);
                    

                    $goldTransaction = $this->app->mygoldtransactionStore()->searchView()->select()->where('orderid', $orderId[$i])->one();
                    //Check Buy or Sell
                    if(Order::TYPE_COMPANYBUY == $goldTransaction->ordtype){
                        // CompanyBuy = Sell
                        $buyorsell = "Sell";
                    }else if(Order::TYPE_COMPANYSELL == $goldTransaction->ordtype){
                        // CompanySell = Buy
                        $buyorsell = "Buy";
                    }
                   
                    //$observation = new \Snap\IObservation($updateDisbursement, \Snap\IObservation::ACTION_CONFIRM, $oldStatus, [ ]);
                    //$this->notify($observation);
                     // Send push notification / email
                    $accHolder = $this->app->myaccountholderStore()->getById($disbursement->accountholderid);
                    $this->notify(new IObservation($updateDisbursement, IObservation::ACTION_EDIT, $oldStatus[$i], [
                        'event' => MyGtpEventConfig::EVENT_GOLDTRANSACTION_EDIT_REF,
                        'accountholderid'   => $accHolder->id,
                        'buyorsell'         => $buyorsell,
                        'name'              => $accHolder->fullname,
                        'receiver'          => $accHolder->email,
                        'ordno'             => $goldTransaction->orderid,
                        'requestedon'       => $goldTransaction->dbmpdtrequestedon,
                        'price'             => $goldTransaction->ordprice,
                        'xau'               => $goldTransaction->ordxau,
                        'amount'            => $goldTransaction->ordamount,
                        'bankname'          => $goldTransaction->dbmbankname,
                        'accno'             => $goldTransaction->dbmaccountnumber,
                    ]));
                }else{
                  
                    // Track all completed records for error message
                    $goldTransaction = $this->app->mygoldtransactionStore()->searchView()->select()->where('orderid', $orderId[$i])->one();

                    $errorMessages[] = $goldTransaction->ordorderno;
                }
                
                
                
            }

            throw new \Exception('Unable to update records '. implode(", ", $errorMessages).' as they are already completed' );
        
            //$this->app->getDbHandle()->commit();
            //$this->log(__METHOD__."({$order->orderno}) has been completed successfully.", SNAP_LOG_DEBUG);
        } catch(\Exception $e) {
            $this->log("Record is unable to be edited due to disbursement being completed", SNAP_LOG_ERROR);

            throw $e;
        }
        return $updateDisbursement;
    }

    /**
     * Create disbursement object
     * 
     * @param MyGoldTransaction $goldTx 
     * @param Partner           $partner 
     * @param MyAccountHolder   $accHolder
     * @param Order             $order
     * @return MyDisbursement
     */
    public function createDisbursementForGoldTransaction(MyGoldTransaction $goldTx, Partner $partner, MyAccountHolder $accHolder, $order = null, $partnerData = null)
    {
        if (!$order) {
            $order = $goldTx->getOrder();
        }

        // Disbursement only for customer sell orders
        if (Order::TYPE_COMPANYBUY != $order->type) {
            throw OrderInvalidAction::fromTransaction([], [
                'action'    => $order->type,
                'orderno'   => $goldTx->refno
            ]);
        }

        $disbursementAmount = $order->amount;
        $disbursementFee = $order->fee;

        $disbursementStore = $this->app->mydisbursementStore();
        $disbursement = $disbursementStore->create([
            'accountholderid'   => $accHolder->id,
            'bankid'    => $accHolder->bankid,
            'accountname' => $accHolder->accountname,
            'accountnumber' => $accHolder->accountnumber,
            'amount'    => $disbursementAmount,
            'fee'       => $disbursementFee,
            'refno'     => $this->app->mygtpTransactionManager()->generateRefNo("D", $disbursementStore),
            'transactionrefno' => $goldTx->refno,
            'status'    => MyDisbursement::STATUS_IN_PROGRESS
        ]);
		
		if (0 < strlen($partnerData)) {
            $disbursement->token = $partnerData;
        }
		
        $disbursement = $disbursementStore->save($disbursement);
        $this->log("Created disbursement {$disbursement->refno} with amount $disbursementAmount and fee $disbursementFee", SNAP_LOG_DEBUG);

        return $disbursement;
    }

    /**
     * Create disbursement object
     * 
     * @param MyGoldTransaction $goldTx 
     * @param Partner           $partner 
     * @param MyAccountHolder   $accHolder
     * @param string            $walletName
     * @param Order             $order
     * @return MyDisbursement
     */
    public function createWalletDisbursementForGoldTransaction(MyGoldTransaction $goldTx, Partner $partner, MyAccountHolder $accHolder, $walletName, $order = null, $partnerData = null)
    {
        if (!$order) {
            $order = $goldTx->getOrder();
        }

        /** @var Product $product */
        $product = $order->getProduct();

        // Disbursement only for customer sell orders
        if (Order::TYPE_COMPANYBUY != $order->type) {
            throw OrderInvalidAction::fromTransaction([], [
                'action'    => $order->type,
                'orderno'   => $goldTx->refno
            ]);
        }

        $disbursementAmount = $order->amount;
        $disbursementFee = $order->fee;

        $this->log(__METHOD__ . ": Created for wallet {$walletName} account number - {$accHolder->partnercusid}", SNAP_LOG_DEBUG);

        $noteValue = $accHolder->note; // toyyib case becoz dont have acc number
        $accHolder->partnercusid = (!empty($noteValue) ? $accHolder->note : $accHolder->partnercusid);

        $disbursementStore = $this->app->mydisbursementStore();
        $disbursement = $disbursementStore->create([
            'accountholderid'   => $accHolder->id,
            'bankid'            => 0,
            'accountname'       => $walletName,
            'accountnumber'     => $accHolder->partnercusid,
            'amount'            => $disbursementAmount,
            'fee'               => $disbursementFee,
            'productdesc'       => $product->name . " {$order->xau}g",
            'refno'             => $this->app->mygtpTransactionManager()->generateRefNo("D", $disbursementStore),
            'transactionrefno'  => $goldTx->refno,
            'status'            => MyDisbursement::STATUS_IN_PROGRESS
        ]);
        
        if (0 < strlen($partnerData)) {
            $disbursement->token = $partnerData;
        }

        $disbursement = $disbursementStore->save($disbursement);
        $this->log("Created disbursement {$disbursement->refno} with amount $disbursementAmount and fee $disbursementFee", SNAP_LOG_DEBUG);

        return $disbursement;
    }

    /**
     * Admin manually confirm disbursement from BO
     * 
     * @param MyDisbursement $disbursement 
     * @param string $bankTxRef 
     * @param float $verifiedAmount 
     * @return MyDisbursement 
     */
    public function manualConfirmDisbursement($disbursement, $bankTxRef, $verifiedAmount, $doNotify = true)
    {
        $this->log(__METHOD__."(ref={$disbursement->refno}, gatewayref=$bankTxRef, amt=$verifiedAmount)", SNAP_LOG_DEBUG);
        // $disbursement-gatewayrefno> = $bankTxRef;
        $disbursement->bankrefno = $bankTxRef;
        $disbursement->verifiedamount = $verifiedAmount;
        try {
            $this->app->getDBHandle()->beginTransaction();

            $disbursement = $this->app->mydisbursementStore()->save($disbursement);
            $disbursement = $this->onDisbursementCompleted($disbursement, $doNotify);

            $this->app->getDBHandle()->commit();
        } catch (\Exception $e) {
            $this->app->getDBHandle()->rollBack();
            throw $e;
        }
        return $disbursement;
    }

    /**
     * Confirms disbursement
     * @param MyDisbursement $disbursement 
     * @param bool           $doNotify      Send email/push
     * @return MyDisbursement
     */
    public function onDisbursementCompleted($disbursement, $doNotify = true)
    {
        if (MyDisbursement::STATUS_IN_PROGRESS != $disbursement->status) {
            $message = "Disbursement ({$disbursement->refno}) current status ({$disbursement->status}) is not in progress.";
            $this->log($message, SNAP_LOG_ERROR);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        $oldStatus = $disbursement->status ;
        $disbursement->disbursedon = $now;
        $disbursement->status = MyDisbursement::STATUS_COMPLETED;

        // Updated by Cheok on 2021-05-18 to cater for status flow change for sell transactions
        $goldTransaction = $this->app->mygoldtransactionStore()->getByField('refno', $disbursement->transactionrefno);
        $goldTransaction->status = MyGoldTransaction::STATUS_PAID;
        $goldTransaction =  $this->app->mygoldtransactionStore()->save($goldTransaction);
        // End update by Cheok

        $this->log(__METHOD__."(refno={$disbursement->refno})");

        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }

            // Save disbursement
            $disbursement = $this->app->mydisbursementStore()->save($disbursement);

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();

                /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
                $transferDb = $this->app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
                if($transferDb) {
                    $order = $goldTransaction->getOrder();
                    $sendTrans = $this->app->mygtptransactionManager()->sendTransactionBetweenDb($goldTransaction,$order);
                }
                else $this->log("[Transfer DB Process] Transaction with refno ".$goldTransaction->refno." no need to trasnfer to other db because status mygoldtransaction ".$goldTransaction->status." is not PAID", SNAP_LOG_DEBUG);
                /**/
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            $this->log(__METHOD__. "(): ".$e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        }
        
        $accHolder = $this->app->myaccountholderStore()->getById($disbursement->accountholderid);
        $goldTransaction = $this->app->mygoldtransactionStore()->getByField('refno', 'transactionrefno', [], false, 1);
        
        if ($doNotify) {
            // Notify disbursement status updated
            $this->notify(new IObservation($disbursement, IObservation::ACTION_CONFIRM, $oldStatus, [
                'event' => MyGtpEventConfig::EVENT_DISBURSEMENT_CONFIRMED,
                'accountholderid'   => $accHolder->id,
                'buyorsell'         => Order::TYPE_COMPANYBUY == $goldTransaction->ordtype ? "Sell" : "Buy",
                'name'              => $accHolder->fullname,
                'receiver'          => $accHolder->email,
                'ordno'             => $goldTransaction->orderid,
                'requestedon'       => $goldTransaction->dbmpdtrequestedon,
                'price'             => $goldTransaction->ordprice,
                'xau'               => $goldTransaction->ordxau,
                'amount'            => $goldTransaction->ordamount,
                'bankname'          => $goldTransaction->dbmbankname,
                'accno'             => $goldTransaction->dbmaccountnumber,
            ]));
        }

        return $disbursement;
    }

     /**
     * Fails disbursement
     * @param MyDisbursement $disbursement 
     * @param bool           $doNotify      Send email/push
     * @return MyDisbursement
     */
    public function onDisbursementFailed($disbursement, $doNotify = true)
    {
        // Lock to avoid concurrent updating from checkstatus
        $lockKey = '{FailedDisbursement}:' . $disbursement->id;
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);

        $disbursement = $this->app->mydisbursementStore()->getById($disbursement->id);
        if (MyDisbursement::STATUS_IN_PROGRESS != $disbursement->status) {
            $cacher->unlock($lockKey);
            $message = "Disbursement ({$disbursement->refno}) current status ({$disbursement->status}) is not in progress.";
            $this->log($message, SNAP_LOG_ERROR);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }
        
        try {
            $startedTransaction = $this->app->getDBHandle()->inTransaction();
            if (!$startedTransaction) {
                $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
            }
            
            $goldTransaction = $this->app->mygoldtransactionStore()->getByField('refno', $disbursement->transactionrefno);
            $oldGoldTransactionStatus = $goldTransaction->status;
            $goldTransaction->status = MyGoldTransaction::STATUS_FAILED;
            $goldTransaction = $this->app->mygoldtransactionStore()->save($goldTransaction, ['status']);
            
            $order = $goldTransaction->getOrder();
            $order->status = Order::STATUS_EXPIRED;
            $order = $this->app->orderStore()->save($order, ['status']);
            
            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());

            // Save disbursement
            $disbursement->cancelledon = $now;
            $disbursement->status = MyDisbursement::STATUS_CANCELLED;
            $disbursement = $this->app->mydisbursementStore()->save($disbursement, ['status', 'cancelledon']);
            $ledgers = $this->app->myledgerStore()->searchTable()->select()->where('refno', $goldTransaction->refno)->get();
            foreach($ledgers as $ledger) {
                $ledger->status = MyLedger::STATUS_INACTIVE;
                $this->app->myledgerStore()->save($ledger);
            }

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();

                /*SEND TO GTP DB order. Check if need to transfer order to another db*/ 
                $transferDb = $this->app->getConfig()->{'mygtp.db.ordertransfer'}; // transfer if 1
                if($transferDb) $sendTrans = $this->app->mygtptransactionManager()->sendTransactionBetweenDb($goldTransaction,$order);
                else $this->log("[Transfer DB Process] Transaction with refno ".$goldTransaction->refno." no need to trasnfer to other db because status mygoldtransaction ".$goldTransaction->status." is not FAILED", SNAP_LOG_DEBUG);
                /**/
            }            
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }

            $this->log(__METHOD__. "(): ".$e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        } finally {
            $cacher->unlock($lockKey);
        }

        $accHolder = $this->app->myaccountholderStore()->getById($order->buyerid);
        if ($doNotify) {
            $email = $accHolder->email;
        }

        $this->notify(new IObservation($goldTransaction, IObservation::ACTION_CANCEL, $oldGoldTransactionStatus, [
            'event' => MyGtpEventConfig::EVENT_GOLDTRANSACTION_FAIL,
            'accountholderid'   => $accHolder->id,
            'projectBase'       => $this->app->getConfig()->{'projectBase'},
            'name'              => $accHolder->fullname,
            'receiver'          => $email ?? '',
            'ordertype'         => 'SELL',
            'xau'               => number_format($order->xau, 3),
            'amount'            => number_format($order->amount, 2),
            'bookingprice'      => number_format($order->price, 2)
        ]));

        return $disbursement;
    }

    /**
     * Reject disbursement and send email notification
     * @param MyDisbursement $disbursement 
     * @param bool           $doNotify      Send email/push
     * @return MyDisbursement
     */
    public function onDisbursementRejected($disbursement, $doNotify = true)
    {
        if (MyDisbursement::STATUS_IN_PROGRESS != $disbursement->status) {
            $message = "Disbursement ({$disbursement->refno}) current status ({$disbursement->status}) is not in progress.";
            $this->log($message, SNAP_LOG_ERROR);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Save disbursement
        $disbursement->cancelledon = $now;
        $disbursement->status = MyDisbursement::STATUS_CANCELLED;
        $disbursement = $this->app->mydisbursementStore()->save($disbursement);

        $mailer = $this->app->getMailer();
        $emailTo = explode(',', $this->app->getConfig()->{'mygtp.disbursement.emailto'}); //compulsory email
        $projectBase = $this->app->getConfig()->{'projectBase'};    
        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $subject = '##DEMO##';
        }
        
        $emailTo = explode(',', $this->app->getConfig()->{'mygtp.disbursement.emailto'}); //compulsory email
        
        foreach($emailTo as $anEmail){
            $mailer->addAddress($anEmail);
        }

        $accHolder = $this->app->myaccountholderStore()->getById($disbursement->accountholderid);
        $mailer->Subject = $subject . "{$projectBase} - Disbursement Failed after 3 attempts";
        $mailer->Body    = "The disbursement failed for account holder {$accHolder->fullname} ({$accHolder->accountholdercode})} after 3 attempts";

        if($mailer->send()) $this->logDebug(__METHOD__." Success sending email {$subject} to receivers.");
        else $this->logDebug(__METHOD__." Failed sending email {$subject} to receivers.");

        return $disbursement;
    }

    /**
     * Since we have unique constrains on disbursement transaction ref no, we need to modify the ref as we need to resubmit 
     * new payment request again
     *
     * @param  MyDisbursement $disbursement
     * @param  boolean        $doNotify
     * @return MyDisbursement
     */
    public function onDisbursementChange($disbursement, $doNotify = true)
    {
        if (MyDisbursement::STATUS_IN_PROGRESS != $disbursement->status) {
            $message = "Disbursement ({$disbursement->refno}) current status ({$disbursement->status}) is not in progress.";
            $this->log($message, SNAP_LOG_ERROR);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }

        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Save disbursement
        $disbursement->transactionrefno = '_' . $disbursement->transactionrefno . $disbursement->id;
        $this->logDebug(__METHOD__ . '(): Cancelling disbursement and detaching from transaction: (' . $disbursement->transactionrefno . ')');
        
        $disbursement->cancelledon = $now;
        $disbursement->status = MyDisbursement::STATUS_CANCELLED;
        $disbursement = $this->app->mydisbursementStore()->save($disbursement); 

        return $disbursement;
    }

    public function getStateMachine(MyDisbursement $payment)
    {
        $config = [
            'property_path' => 'status',
            'states' => [
                MyDisbursement::STATUS_IN_PROGRESS => ['type' => 'initial', 'properties' => []],
                MyDisbursement::STATUS_COMPLETED   => ['type' => 'final', 'properties' => []],
                MyDisbursement::STATUS_CANCELLED   => ['type' => 'final', 'properties' => []],

            ],
            'transitions' => [                                               
                MyDisbursement::STATUS_COMPLETED => ['from' => [MyDisbursement::STATUS_IN_PROGRESS], 'to' => MyDisbursement::STATUS_COMPLETED],
                MyDisbursement::STATUS_CANCELLED => ['from' => [MyDisbursement::STATUS_IN_PROGRESS], 'to' => MyDisbursement::STATUS_CANCELLED],

            ]
        ];
        return $this->createStateMachine($payment, $config);
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

    public function generateBulkPaymentReport($dateStart, $dateEnd, $conditions,$currentdate){
        $generateText   = new \Snap\api\mbb\AceBulkPayment($this->app,false);

        $partner        = $conditions['partner'];
        $refno          = $conditions['refno'];
        $nameoffile     = $conditions['filename'];
        $genDate        = $conditions['generatedate'];
        $custPartner    = $conditions['customizepartner'];
        $dateGenerate   = date("dmY", strtotime($genDate));
        $useCurrentDate = date("dmY", strtotime($currentdate));
        //if($partner->sapcompanysellcode1 != '') $pCode = $partner->sapcompanysellcode1;
        $batchId        = $dateGenerate.$custPartner;

        /****/
        $dateStart      = new \DateTime($dateStart, $this->app->getUserTimezone()); 
        $dateEnd        = new \DateTime($dateEnd, $this->app->getUserTimezone()); 
        //$dateStart      = \Snap\common::convertUTCToUserDatetime($dateStart);
        $startAt        = new \DateTime($dateStart->format('Y-m-d H:i:s'));
        $saveStartAt    = $startAt;
        $startAt        = \Snap\common::convertUserDatetimeToUTC($startAt);
        $endAt          = new \DateTime($dateEnd->format('Y-m-d H:i:s'));
        $saveEndAt      = $endAt;
        $endAt          = \Snap\common::convertUserDatetimeToUTC($endAt);
        /*****/
        $clientbatchid  = $batchId;
        $aceaccid       = $this->app->getConfig()->{'gtp.bulk.accbank'};
        $aceaccno       = $this->app->getConfig()->{'gtp.bulk.accno'};
        $provprod       = $this->app->getConfig()->{'gtp.bulk.product'};
        $currfix        = $this->app->getConfig()->{'gtp.bulk.curr'};
        $defbank        = $this->app->getConfig()->{'gtp.bulk.intrabank'};
        $valuedate      = $useCurrentDate;
        $additionalDetails = [
            'clientbatchid' => $clientbatchid,
            'aceaccid'      => $aceaccid,
            'aceaccno'      => $aceaccno,
            'valuedate'     => $valuedate,
            'provprod'      => $provprod,
            'currfix'       => $currfix,
            'defbank'       => $defbank,
            'filename'      => $nameoffile
        ];

        // Get Pending Transactions
        if($refno){
            $buyTransactionsC = $this->app->mydisbursementStore()->searchView(false)->select()
                ->where('transactionrefno', 'IN' ,$refno)
                ->execute();
        } else {
            $buyTransactionsC = $this->app->mydisbursementStore()->searchView(false)->select()
                ->where('status', 'IN' ,[MyDisbursement::STATUS_IN_PROGRESS])
                ->andWhere('accpartnerid', $partner->id)
                ->andWhere('bankid','!=',0)
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
                $buyTransactions = $this->app->mydisbursementStore()->searchView()->select()
                ->where('transactionrefno', 'IN' ,$refno)
                ->execute();
            } else {
                $buyTransactions = $this->app->mydisbursementStore()->searchView()->select()
                    ->where('status', 'IN' ,[MyDisbursement::STATUS_IN_PROGRESS])
                    ->andWhere('accpartnerid', $partner->id)
                    ->andWhere('bankid','!=',0)
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    ->execute();
            }

            /*check acc no if valid*/
            foreach($buyTransactions as $aTransaction){
                if(0 == $aTransaction->accountnumber || null == $aTransaction->accountnumber){
                    /*get myaccountholder table*/
                    $accHolder = $this->app->myaccountholderStore()->getById($aTransaction->accountholderid);
                    $bankdetails = $this->app->mybankStore()->getById($accHolder->bankid);

                    /*overwrite*/
                    $aTransaction->accountnumber    = $accHolder->accountnumber;
                    $aTransaction->accountname      = $accHolder->accountname;
                    $aTransaction->bankid           = $accHolder->bankid;
                    $aTransaction->bankswiftcode    = $bankdetails->swiftcode;
                }
            }

            /*getOrderNo*/
            foreach($buyTransactions as $aTransaction){
                $ordTransaction = $this->app->orderStore()->getByField('partnerrefid',$aTransaction->transactionrefno);
                $aTransaction->ordorderno = $ordTransaction->orderno;
            }

            $paymentOutput = $generateText->checkFile($buyTransactions,$currDate,$totalItem,$paging,$i,$additionalDetails);
        }
    }

    public function updateStatusFromMbbResponse($response,$partnerid){
        //example response:
        /*[records] => Array
            (
                [GT202204210900002] => Array
                    (
                        [0] => Array
                            (
                                [0] => 01
                                [1] => IG
                                [2] => Domestic Payments (MY)
                                [3] => 22042022
                                [4] => EGCE2EF34B
                                [5] => GT202204210900002
                                [6] => MYR
                                [7] => 41.81
                                [8] => Y
                                [9] => MYR
                                [10] => 512361171734
                                [11] => 12168020211290
                                [12] => Y
                                [13] => NUR AISYAH FARHANA
                                [14] => 880813085610
                                [15] => 0
                                [16] => 0
                                [17] => 0
                                [18] => BIMBMYKL
                                [19] => 01
                                [20] => Successful
                                [21] => 26042022
                                [22] => 22042022
                                [23] => MYIG220422385253
                                [24] => Successful  22042022

                            )

                        [1] => Array
                            (
                                [0] => 02
                                [1] => PA
                                [2] => EGCE2EF34B
                                [3] => nuraisyah130888@gmail.com
                                [4] => GT202204210900002
                                [5] => 41.81
                                [6] =>

                            )

                    )
            )
        */

        foreach($response['records'] as $key=>$value){
            //$key will be refno.exp:GT202203092100002
            $amount         = $value[0][7];
            $accName        = $value[0][13];
            $accNo          = $value[0][11];
            $bankrefno      = $value[0][23];
            $statustransfer = $value[0][20];

            $transactions = $this->app->mydisbursementStore()->searchView()->select()
                    ->where('transactionrefno', $key)
                    ->one();

            if(count($transactions)>0){
                /*double checking*/
                //check amount
                if(is_array($partnerid)){
                    $partnerlist = implode(', ', $partnerid);
                    if(!in_array($transactions->accpartnerid,$partnerid)){
                        $this->log("Bulk Payment Response - Transaction partner id is ".$transactions->accpartnerid." and list partner id is ".$partnerlist." is not correct.", SNAP_LOG_ERROR);
                        throw new \Exception("Partner for {$key} is incorrect. Please upload at correct module.");
                    }
                } else {
                    if($transactions->accpartnerid != $partnerid){
                        $this->log("Bulk Payment Response - Transaction partner id is ".$transactions->accpartnerid." and partner id is ".$partnerid." is not correct.", SNAP_LOG_ERROR);
                        throw new \Exception("Partner for {$key} is incorrect. Please upload at correct module.");
                    }
                }

                if($amount != $transactions->amount) {
                    $this->log("Bulk Payment Response - Amount in rec-gen is not the same as in DB for {$key}", SNAP_LOG_ERROR);
                    throw new \Exception("Please double check amount - {$key}");
                }
                //check accnum
                if($accNo != $transactions->accountnumber) {
                    $this->log("Bulk Payment Response - Acc number in rec-gen is not the same as in DB for {$key}", SNAP_LOG_ERROR);
                    throw new \Exception("Please double check account number - {$key}");
                }

                if($statustransfer == 'Successful') $updateStatus = $this->manualConfirmDisbursement($transactions, $bankrefno, $transactions->amount);
                else $this->log("Bulk Payment Response - Status from Maybank for {$key} is ".$statustransfer, SNAP_LOG_ERROR);
            } else {
                throw new \Exception("There is no data for {$key}");
            }
        }

        return json_encode($updateStatus);
    }

}