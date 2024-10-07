<?php

// table involded trasnfergold, retainedgold, ledger, trasnsaction

// CAUTION: THIS IS NOT SAME AS MYGOLDTRANSFER
// THIS IS XAU TRANSFER BETWEEN ACCOUNTHOLDER | NEW USER
// `MYGOLDTRANSFER` TABLE IS USE FOR REWARD, CHAMPAIGN, ETC
// WRONG NAMING PREVIOUSLY

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\TLogging;
use Snap\TObservable;
use Snap\IObservable;
use Snap\object\MyTransferGold;
use Snap\object\MyPledge;
use Snap\object\MyLedger;
use Snap\object\Partner;

use Snap\api\exception\MyGtpPartnerMismatch;
use Snap\api\exception\MyGtpAccountHolderNotEnoughBalance;

/**
 * This class handles account holder transfer management
 *
 */
class MyGtpTransferGoldManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Process gold transaction when success payment received
     * 
     * @param   string   $type   type of transfer, value: xau, myr <> defualt: xau, myr *pending
     * 
     * @return bool
     */
    public function transfer($fromAccountHolder, $toAccountHolder, $type = 'xau', $transferXauAmount, $referencePrice, $message){
        
        try {
            $this->logDebug(__METHOD__ . ":() Begin transferring gold {$xau}g from {$fromAccountHolder->accountholdercode} to {$toAccountHolder->accountholdercode}");
            
            $lockKey = '{TRANSFERGOLD}:' . $fromAccountHolder->accountholdercode;
            $cacher = $this->app->getCacher();
            $cacher->waitForLock($lockKey, 1, 60, 60);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $partner = $this->app->partnerStore()->getById($fromAccountHolder->partnerid);
            $partnerReceiver = $this->app->partnerStore()->getById($toAccountHolder->partnerid);
            if($this->app->getConfig()->{'gtp.transfergold.skippartnermatchcheck'} == '1'){
                // skip partner match check
            }
            else{
                if ($partner->id != $partnerReceiver->id){
                    throw MyGtpPartnerMismatch::fromTransaction([], ['message' => "Sender's partner {$partner->name} does not match with receiver."]);
                }
            }
            // if ($partner->id != $partnerReceiver->id){
            //     throw MyGtpPartnerMismatch::fromTransaction([], ['message' => "Sender's partner {$partner->name} does not match with receiver."]);
            // }

            $xau = $transferXauAmount;

            // check sender balander to send
            $this->checkAvailableTransferAmount($partner, $fromAccountHolder, $xau);


            // reference purposes ONLY
            $price = $referencePrice;
            $amount = $partner->calculator()->multiply($transferXauAmount, $referencePrice);
            // reference purposes ONLY
            
            // if ($transferType == MyTransferGold::TYPE_TRANSFER_OUT){
            //     $ledgerType = MyLedger::TYPE_TRANSFER_OUT;
            // }else if ($transferType == MyTransferGold::TYPE_TRANSFER_IN){
            //     $ledgerType = MyLedger::TYPE_TRANSFER_IN;
            // }else{
            //     throw error;
            // }

	    $userId = $this->app->getUserSession()->getUserId();
        $user =  $this->app->userStore()->getById($userId);

	    $refNo = $this->app->mygtptransactionmanager()->generateRefNo("TR", $this->app->mytransfergoldStore());
            $this->logDebug("Creating User Transfer Gold data - $refNo");
            $transfer = $this->app->mytransfergoldStore()->create([  
                'partnerid'             => $user->partnerid,
                'accountholderid'       => $fromAccountHolder->id,
                'type'                  => MyTransferGold::TYPE_TRANSFER,
                'fromaccountholderid'   => $fromAccountHolder->id,
                'toaccountholderid'     => $toAccountHolder->id,
                'refno'                 => $refNo,
                'price'                 => $price,
                'xau'                   => $xau,
                'amount'                => $amount,             
                'message'               => $message,                
                'notify'                => 0,                
		'status'                => MyTransferGold::STATUS_ACTIVE,
		'createdby'             => $userId,
            ]);
            $transfer = $this->app->mytransfergoldStore()->save($transfer);

            $now = new \DateTime('now', $this->app->getUserTimezone());
            
            $this->logDebug("Creating data for User who perform TRANSFER - $refNo");
            $ledger = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_TRANSFER,
                'typeid'            => $transfer->id,
                'accountholderid'   => $fromAccountHolder->id,
                'partnerid'         => $partner->id,
                'debit'             => $xau,
                'credit'            => 0.00,
                'refno'             => $refNo,
                'remarks'           => 'Transfer GOLD: '.$xau.'(g) to - '.$toAccountHolder->fullname,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledger = $this->app->myledgerStore()->save($ledger);

            $this->logDebug("Creating data for User who perform RECEIVE - $refNo");
            $ledgerReceiver = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_TRANSFER,
                'typeid'            => $transfer->id,
                'accountholderid'   => $toAccountHolder->id,
                'partnerid'         => $partnerReceiver->id,
                'debit'             => 0.00,
                'credit'            => $xau,
                'refno'             => $refNo,
                'remarks'           => 'Receive GOLD: '.$xau.'(g) from '.$fromAccountHolder->fullname,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledgerReceiver = $this->app->myledgerStore()->save($ledgerReceiver);

            $this->logDebug("Finished saving USER(s) ledger for transfer $refNo");

            // instant email service
            $transferTime = $now->format('Y-m-d H:i:s');

            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
                $this->log("Order $refid from merchant {$partner->code} SQL committed ", SNAP_LOG_DEBUG);
            }
            $cacher->unlock($lockKey);
			
			if($this->app->getConfig()->{'otc.job.diffserver'} == '1'){
				
			}
			else{
				$this->instantNotifyUserTransfer($fromAccountHolder, $toAccountHolder, $xau, $refNo, $transferTime, $partner);
			}
			// $this->instantNotifyUserTransfer($fromAccountHolder, $toAccountHolder, $xau, $refNo, $transferTime, $partner);

            return $transfer;

        } catch(\Exception $e) {
            $cacher->unlock($lockKey);
            if(! $alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;  //rethrow the data
        }
    }

    public function transferApprovalPartOne($fromAccountHolder, $toAccountHolder, $type = 'xau', $transferXauAmount, $referencePrice, $message){
        try{
            $this->logDebug(__METHOD__ . ":() Begin transferring gold {$xau}g from {$fromAccountHolder->accountholdercode} to {$toAccountHolder->accountholdercode}");
            
            $lockKey = '{TRANSFERGOLD}:' . $fromAccountHolder->accountholdercode;
            $cacher = $this->app->getCacher();
            $cacher->waitForLock($lockKey, 1, 60, 60);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $partner = $this->app->partnerStore()->getById($fromAccountHolder->partnerid);
            $partnerReceiver = $this->app->partnerStore()->getById($toAccountHolder->partnerid);
            if($this->app->getConfig()->{'gtp.transfergold.skippartnermatchcheck'} == '1'){
                // skip partner match check
            }
            else{
                if ($partner->id != $partnerReceiver->id){
                    throw MyGtpPartnerMismatch::fromTransaction([], ['message' => "Sender's partner {$partner->name} does not match with receiver."]);
                }
            }

            $xau = $transferXauAmount;

            $this->checkAvailableTransferAmount($partner, $fromAccountHolder, $xau);

            // reference purposes ONLY
            $price = $referencePrice;
            $amount = $partner->calculator()->multiply($transferXauAmount, $referencePrice);

            $userId = $this->app->getUserSession()->getUserId();
            $user =  $this->app->userStore()->getById($userId);
            
            $refNo = $this->app->mygtptransactionmanager()->generateRefNo("TR", $this->app->mytransfergoldStore());
            $this->logDebug("Creating User Transfer Gold data - $refNo");
            $transfer = $this->app->mytransfergoldStore()->create([  
                'partnerid'             => $user->partnerid,
                'accountholderid'       => $fromAccountHolder->id,
                'type'                  => MyTransferGold::TYPE_TRANSFER,
                'fromaccountholderid'   => $fromAccountHolder->id,
                'toaccountholderid'     => $toAccountHolder->id,
                'refno'                 => $refNo,
                'price'                 => $price,
                'xau'                   => $xau,
                'amount'                => $amount,             
                'message'               => $message,                
                'notify'                => 0,                
                'status'                => MyTransferGold::STATUS_REQUIREAPPROVAL
            ]);
            $transfer = $this->app->mytransfergoldStore()->save($transfer);

            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
                // $this->log("Order $refid from merchant {$partner->code} SQL committed ", SNAP_LOG_DEBUG);
            }
            $cacher->unlock($lockKey);

            return $transfer;
        }
        catch(\Throwable $e){
            $cacher->unlock($lockKey);
            if(! $alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;  //rethrow the data
        }
    }

    public function transferApprovalPartTwo(MyTransferGold $mytransfergold, $remarks = "", $approvalcode = ""){
        try{
            $fromAccountHolder = $this->app->myaccountholderStore()->getById($mytransfergold->fromaccountholderid);
            $toAccountHolder = $this->app->myaccountholderStore()->getById($mytransfergold->toaccountholderid);

            $lockKey = '{TRANSFERGOLD}:' . $fromAccountHolder->accountholdercode;
            $cacher = $this->app->getCacher();
            $cacher->waitForLock($lockKey, 1, 60, 60);
            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $checker = $this->app->userStore()->searchTable()->select()->where('id',$this->app->getUsersession()->getUserId())->one();

            $mytransfergold->status = 1;
            $mytransfergold->actionon = new \DateTime('now');
            $mytransfergold->remarks = 'Remarks: '.$remarks.'; Approval Code:'.$approvalcode;
            $mytransfergold->checker = $checker->username;
            $mytransfergold = $this->app->mytransfergoldStore()->save($mytransfergold);

            $now = new \DateTime('now', $this->app->getUserTimezone());
            
            $this->logDebug("Creating data for User who perform TRANSFER - $mytransfergold->refno");
            $ledger = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_TRANSFER,
                'typeid'            => $mytransfergold->id,
                'accountholderid'   => $mytransfergold->fromaccountholderid,
                'partnerid'         => $fromAccountHolder->partnerid,
                'debit'             => $mytransfergold->xau,
                'credit'            => 0.00,
                'refno'             => $mytransfergold->refno,
                'remarks'           => 'Transfer GOLD: '.$mytransfergold->xau.'(g) to - '.$toAccountHolder->accountholdercode,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);

            $ledger = $this->app->myledgerStore()->save($ledger);
            $this->logDebug("Creating data for User who perform RECEIVE - $mytransfergold->refno");
            $ledgerReceiver = $this->app->myledgerStore()->create([
                'type'              => MyLedger::TYPE_TRANSFER,
                'typeid'            => $mytransfergold->id,
                'accountholderid'   => $mytransfergold->toaccountholderid,
                'partnerid'         => $toAccountHolder->partnerid,
                'debit'             => 0.00,
                'credit'            => $mytransfergold->xau,
                'refno'             => $mytransfergold->refno,
                'remarks'           => 'Receive GOLD: '.$xau.'(g) from '.$fromAccountHolder->accountholdercode,
                'transactiondate'   => \Snap\Common::convertUserDatetimeToUTC($now)->format('Y-m-d H:i:s'),
                'status'            => MyLedger::STATUS_ACTIVE
            ]);
            $ledgerReceiver = $this->app->myledgerStore()->save($ledgerReceiver);
            $this->logDebug("Finished saving USER(s) ledger for transfer $mytransfergold->refno");
            // instant email service
            $transferTime = $now->format('Y-m-d H:i:s');
            if(! $alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
                // $this->log("Order $refid from merchant {$partner->code} SQL committed ", SNAP_LOG_DEBUG);
            }
            $cacher->unlock($lockKey);
            // $this->instantNotifyUserTransfer($fromAccountHolder, $toAccountHolder, $xau, $refNo, $transferTime, $partner);
            return $mytransfergold;
        }
        catch(\Throwable $e){
            $cacher->unlock($lockKey);
            if(! $alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }
            throw $e;  //rethrow the data
        } 
    }

    private function instantNotifyUserTransfer($fromAccountHolder, $toAccountHolder, $xau, $refNo, $transferTime, $partner){
        $now = new \DateTime('now', $this->app->getUserTimezone());
        
        //SENDER
        $fromAccountEmailSubject = $partner->projectbase. ' - You perform a gold transfer of '.$xau.' (g)';
        $fromAccountEmailContent = '
            TRANSFER '.$xau.' (g) to '.$toAccountHolder->fullname.'
        ';
        $this->instantNotify($fromAccountHolder->email, $fromAccountEmailSubject, $fromAccountEmailContent, $partner);
        $insertEventLog = $this->app->eventLogStore()->create([
            "triggerid"  => 0,
            "groupid"    => 0,
            "objectid"   => 0,
            "reference"  => 'custom_instant',
            "subject"    => $fromAccountEmailSubject,
            "log"        => $fromAccountEmailContent,
            "sendto"     => $fromAccountHolder->email,
            "sendon"     => $now,
            "status"     => 1,
        ]);
        $this->app->eventLogStore()->save($insertEventLog);

        //RECEIVER
        $toAccountEmailSubject = $partner->projectbase. ' - You receive a gold transfer of '.$xau.' (g)';
        $toAccountEmailContent = '
            RECEIVE '.$xau.' (g) from '.$fromAccountHolder->fullname.'
        ';
        $this->instantNotify($toAccountHolder->email, $toAccountEmailSubject, $toAccountEmailContent, $partner);
        $insertEventLog = $this->app->eventLogStore()->create([
            "triggerid"  => 0,
            "groupid"    => 0,
            "objectid"   => 0,
            "reference"  => 'custom_instant',
            "subject"    => $toAccountEmailSubject,
            "log"        => $toAccountEmailContent,
            "sendto"     => $toAccountHolder->email,
            "sendon"     => $now,
            "status"     => 1,
        ]);
        $this->app->eventLogStore()->save($insertEventLog);

    }

    private function instantNotify($receiverEmail, $subject, $content, $partner){
        $mailer = $this->app->getMailer();
        $mailer->SMTPDebug = 0; // return debug code off
        
        $receiverArr = [$receiverEmail];

        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            $mailer->addAddress($receiver);
        }
        $mailer->isHtml(true);
        
        $email = $partner->senderemail;
        $name = $partner->sendername;
        $mailer->setFrom($email, $name);
        $mailer->Subject = $subject;
        $mailer->Body    = $content;

        $mailer->send();
    }

    public function transferFrom(){

    }

    private function checkAvailableTransferAmount($partner, $fromAccountHolder, $transaferXauAmount){
        $availableBalance = 0;

        $calc = $partner->calculator(false);
        $currentBalance = floatval($fromAccountHolder->getCurrentGoldBalance());
        $availableBalance = $this->app->mygtpaccountManager()->getAccountHolderAvailableGoldBalance($fromAccountHolder);
        $remainingBalance = floatval($calc->sub($availableBalance, $transaferXauAmount));
        $this->log(__METHOD__."(): Balance: {$currentBalance}g, Balance after deduct storage fees: ${availableBalance}g, Transfer Amount: ${transaferXauAmount}g", SNAP_LOG_DEBUG);
        if (0 > $remainingBalance) {
            $this->log(__METHOD__."(): Account holder ID ({$fromAccountHolder->id}) does not have enough gold balance to perform transfer transaction of {$transaferXauAmount}g.", SNAP_LOG_ERROR);
            throw MyGtpAccountHolderNotEnoughBalance::fromTransaction(null);
        }else{
            return true;
        }
    }

    private function checkAccountHolderStatus($accountHolder){

    }

    private function conversionXau($now = true){

    }


    private function insertLedger($accountHolder, $transferType, $transferGold){
        $this->logDebug("Creating customer ledger for gold transfer $goldTx->refno");

        $now = new \DateTime();
        $accountHolder;

        // if ($transferType == MyTransferGold::TYPE_TRANSFER_OUT){
        //     $ledgerType = MyLedger::TYPE_TRANSFER_OUT;
        // }else if ($transferType == MyTransferGold::TYPE_TRANSFER_IN){
        //     $ledgerType = MyLedger::TYPE_TRANSFER_IN;
        // }else{
        //     throw error;
        // }

        $ledgerType = MyLedger::TYPE_TRANSFER;

        $ledger = $this->app->myledgerStore()->create([
            'type'              => $ledgerType,
            'accountholderid'   => $accountHolder->id,
            'partnerid'         => $accountHolder->partnerid,
            'typeid'            => $transferGold->id,
            'refno'             => $transferGold->refno,
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

    
}
