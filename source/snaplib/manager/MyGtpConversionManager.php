<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\api\exception\GeneralException;
use Snap\api\exception\MyGtpAccountHolderNotEnoughBalance;
use Snap\api\exception\MyGtpActionNotPermitted;
use Snap\api\exception\MyGtpFpxCreateTxError;
use Snap\api\exception\MyGtpInvalidAmount;
use Snap\api\exception\MyGtpInvalidStartDate;
use Snap\api\exception\MyGtpPartnerApiInvalid;
use Snap\api\exception\MyGtpProductInvalid;
use Snap\api\exception\RedemptionError;
use Snap\api\fpx\BaseFpx;
use Snap\api\wallet\BaseWallet;
use Snap\api\casa\BaseCasa;
use Snap\IObservable;
use Snap\IObservation;
use Snap\IObserver;
use Snap\object\Logistic;
use Snap\object\MyAccountHolder;
use Snap\object\MyConversion;
use Snap\object\MyFee;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyLedger;
use Snap\object\MyPartnerApi;
use Snap\object\MyPartnerSetting;
use Snap\object\MyPaymentDetail;
use Snap\object\Partner;
use Snap\object\Product;
use Snap\object\Redemption;
use Snap\TObservable;
use Snap\TLogging;

/**
 * This class handles all logic for MyGTP specific conversion(GTP core Redemption)
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpConversionManager implements IObservable, IObserver
{
    use TLogging;
    use TObservable;

    /** @var Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if (($changed instanceof BaseFpx || $changed instanceof BaseWallet || $changed instanceof BaseCasa) && $state->target instanceof MyPaymentDetail
            && preg_match('/^CV.*/i', $state->target->sourcerefno)) {
                $conversions = $this->app->myconversionStore()->searchTable()->select()
                                    ->where('refno', 'like', $state->target->sourcerefno ."%")
                                    ->execute();

                $paymentStatus = $state->target->status;
                if ($state->isConfirmAction() && MyPaymentDetail::STATUS_SUCCESS == $paymentStatus) {
                    $this->doConfirmConversion($conversions);
                } else if ($state->isCancelAction() && MyPaymentDetail::STATUS_CANCELLED == $paymentStatus) {
                    $this->doCancelConversion($conversions);
                } else if ($state->isRejectAction() && MyPaymentDetail::STATUS_FAILED == $paymentStatus) {
                    $this->doCancelConversion($conversions);
                }
            }
    }

    public function getRedemptionLogisticLog($app, $redemptionid)
    {
        $logistic = $app->logisticStore()->searchTable()->select()
                            ->where('type', Logistic::TYPE_REDEMPTION)
                            ->andWhere('typeid', $redemptionid)
                            ->one();

        if (!$logistic) {
            $this->logDebug("No logistic entry found for redemption $redemptionid");
            return [];
        }
        
        // Get the public logs
        $logisticlogs = $app->logisticlogStore()->searchTable()->select()
                            ->where('logisticid', $logistic->id)
                            ->andWhere('type', 'Public')
                            ->orderBy('id', 'DESC')
                            ->execute();

        return $logisticlogs;
        
    }

    /**
     * Get the total amount for a conversion before booking
     *
     * @param  MyAccountHolder $accHolder
     * @param  Partner         $partner
     * @param  Product         $product
     * @param  int             $qty
     * @param  string          $paymentMode
     * @return array
     */
    public function getPreBookConversionAmount(MyAccountHolder $accHolder, Partner $partner, Product $product, $qty, $paymentMode)
    {
        $this->validateConversionRequest($accHolder, $partner, $product, $qty, $paymentMode);

        // Separate product into multiple deliveries
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $maxXau = $settings->maxxauperdelivery ?? 100;
        $maxQty = (int) $settings->maxpcsperdelivery ?? 30;
        $maxProductAmt = floor($maxXau / $product->weight);     // Get maximum amount of products that can be delivered in 1 delivery
        for ($thisDeliveryAmt = 0; 0 < $qty; $qty -= $thisDeliveryAmt) {
            $thisDeliveryAmt = min($qty, $maxProductAmt, $maxQty);
            $deliveries[] = $thisDeliveryAmt;
        }

        $totalFees = 0;
        $return = [];
        foreach ($deliveries as $deliveryQty) {
            $data = $this->generateRedemptionData($accHolder, $partner, $product, $deliveryQty, __FUNCTION__);
            $return['totalRedemptionFee'] += $data['redemptionFee'];
            $return['totalInsuranceFee'] += $data['insuranceFee'];
            $return['totalCourierCharges'] += ($data['handlingFee'] + $data['deliveryFee']);

            $fees = $data['redemptionFee'] + $data['insuranceFee'] + $data['handlingFee'] + $data['deliveryFee'];

	        $return['totalHandlingFee'] += $data['handlingFee'];
            $return['totalDeliveryFee'] += $data['deliveryFee'];

            $totalFees += $fees;
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX === $paymentMode) {
            $paymentFee = $settings->transactionfee;
        } elseif (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET === $paymentMode) {
            $paymentFee = 0;
        } else { 
            $paymentFee = 0;
        }

        $return['totalPaymentFee'] = $paymentFee;

        return $return;
    }

    /**
     * Creates a conversion request
     * @param   MyAccountHolder     $accHolder          Account Holder
     * @param   Partner             $partner            Partner associated with account holder
     * @param   Product             $toProduct          Physical product to be redeemed
     * @param   int                 $qty                Quantity of product to be redeem
     * @param   string              $apiVersion         Api version to be recorded in RedemptionManager
     * @param   string              $apiVersion         Api version to be recorded in RedemptionManager
     * @param   string              $paymentMode        Payment mode
     * @param   string              $campaignCode       Campaign code entered by the user
     * @param   string              $partnerData        Partner data if available
     * @param   RedemptionManager   $redemptionMgr      RedemptionManager instance, used for mocking in test
     * 
     * @return MyConversion[]
     */
    public function doConversion(MyAccountHolder $accHolder, Partner $partner, Product $toProduct, $qty, $apiVersion, $paymentMode, $campaignCode = '', $partnerData = '', $redemptionMgr = null)
    {
        $this->logDebug(__METHOD__."({$accHolder->id}, {$partner->code}, {$toProduct->code}, {$qty}, {$apiVersion})");

        $this->validateConversionRequest($accHolder, $partner, $toProduct, $qty, $paymentMode);
        // Check if conversion should be split 
        $totalWeight = floatval($toProduct->weight * $qty);
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $maxXau = $settings->maxxauperdelivery ?? 100;
        $maxPcs = $settings->maxpcsperdelivery ?? 30;
        $maxProductAmt = floor($maxXau / $toProduct->weight);     // Get maximum amount of products that can be delivered in 1 delivery
        $deliveries = [];

        // Separate product into multiple deliveries
        for ($thisDeliveryAmt = 0, $_qty = $qty; 0 < $_qty; $_qty -= $thisDeliveryAmt) {
            $thisDeliveryAmt = min($_qty, $maxProductAmt, $maxPcs);
            $deliveries[] = $thisDeliveryAmt;
        }

        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }

        try {
            $deliveryCount = count($deliveries);
            $orgConversionRefNo =  $this->app->mygtptransactionManager()->generateRefNo("CV", $this->app->myconversionStore());
            $totalFees = 0;
            $conversions = [];
            $appendChar = 'A';
            foreach ($deliveries as $deliveryQty) {
                $conversionRefNo = 1 < $deliveryCount   ? $orgConversionRefNo . $appendChar++
                                                        : $orgConversionRefNo;

                // Generate required data for RedemptionManager
                $data = $this->generateRedemptionData($accHolder, $partner, $toProduct, $deliveryQty, $conversionRefNo);

                // Redemption object first 
                $redemptionMgr = $redemptionMgr ?? $this->app->redemptionManager();
                $redemption = $redemptionMgr
                              ->createRedemption($partner, $apiVersion, $data['refNo'], $data['branchId'],
                                               $data['redemptionType'], $data['totalWeight'], $qty, $data['itemArray'],
                                               $data['deliveryInfo'], null/** scheduleInfo */, $data['remarks'], null /** timestamp */, null /** priceStream */,
                                               $data['redemptionFee'], $data['insuranceFee'], $data['handlingFee'] + $data['deliveryFee'],$data['pickupbranch']);

                // Confirm redemption through RedemptionManager first
                //$redemption = $redemptionMgr->confirmRedemption($redemption, $partner, $data['branchId'], $data['deliveryInfo']);
                /*
                    2023-06-20: 
                    for otc/mbsb projects, dont request serial no first.
                    after conversion finish, send data to gtp. only then in gtp request for serial no through confirmRedemption
                */
                // if($this->app->getConfig()->{'projectBase'} == 'BSN' || $this->app->getConfig()->{'projectBase'} == 'ALRAJHI' || $this->app->getConfig()->{'projectBase'} == 'MBSB'){
                if($this->app->getConfig()->{'project.conversion.diffsvr'} == '1'){
                    $redemption = $redemptionMgr->confirmRedemption($redemption, $partner, $data['branchId'], $data['deliveryInfo'],[],false);
                }
                else{
                    $redemption = $redemptionMgr->confirmRedemption($redemption, $partner, $data['branchId'], $data['deliveryInfo']);
                }

                if (Redemption::STATUS_CONFIRMED != $redemption->status) {
                    throw RedemptionError::fromTransaction($redemption, ['message' => gettext("Unable to create conversion, please contact customer service.")]);
                }

               
                // Create conversion object
                $conversion = $this->app->myconversionStore()->create([
                    'accountholderid'   => $accHolder->id,
                    'redemptionid'      => $redemption->id,
                    'refno'             => $data['refNo'],
                    'productid'         => $toProduct->id,
                    'commissionfee'     => $data['commissionFee'],
                    'premiumfee'        => $data['premiumFee'],
                    'handlingfee'       => $data['handlingFee'],
                    'courierfee'        => $data['deliveryFee'],
                    'campaigncode'      => $campaignCode,
                    'status'            => MyConversion::STATUS_PAYMENT_PENDING,
                    'logisticfeepaymentmode'    => $paymentMode
                ]);

                $conversion = $this->app->myconversionStore()->save($conversion);
                $conversions[] = $conversion;

                $this->log(__METHOD__ . "(): $orgConversionRefNo ({$redemption->redemptionno}) is created.");
                $fees = $data['redemptionFee'] + $data['insuranceFee'] + $data['handlingFee'] + $data['deliveryFee'];
                $totalFees += $fees;
            }

            // Create FPX Transaction
            if (0 < $totalFees) {
                $paymentFee = $this->getPaymentFee($settings, $paymentMode);;
                $payment = $this->createPaymentForConversion($accHolder, $orgConversionRefNo, $totalFees, $paymentFee, $paymentMode, $partnerData);
                if (!$payment) {
                    $this->log(__METHOD__."(): Unable to create payment for gold transaction {$data['refNo']}.", SNAP_LOG_ERROR);
                    throw MyGtpFpxCreateTxError::fromTransaction(null, []);
                }
            }

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {

            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }

        $this->notify(new IObservation($conversions, IObservation::ACTION_NEW, 0, [
            'event'     => MyGtpEventConfig::EVENT_CONVERSION_CREATE,
            'projectbase' => $this->app->getConfig()->{'projectBase'},
            'accountholderid' => $accHolder->id,
            'name'      => $accHolder->firstname,
            'receiver'  => $accHolder->email,
            // 'bccemail'  => $this->app->getConfig()->{'gtp.conversion.bcc.receiver'}, // `email@email.com, email2@email.com`
        ]));
        return $conversions;
    }

    /**
     * Confirms the conversion after payment is made
     * 
     * @param MyConversion[] $conversion 
     * @param RedemptionManager $redemptionManager 
     * @return MyConversion[]
     */
    public function doConfirmConversion($conversions, $redemptionManager = null)
    {
        if (!is_array($conversions)) {
            $conversions = [$conversions];
        }

        $this->waitForConfirmConversionLock($conversions[0]);

        foreach ($conversions as $idx => $conversion) {
            // Refresh conversion
            $conversions[$idx] = $this->app->myconversionStore()->getById($conversion->id);
            if (! $this->getConversionStateMachine($conversion)->can(MyConversion::STATUS_PAYMENT_PAID)) {
                $message = "Unable to confirm conversion {$conversion->refno}";
                $this->log( __METHOD__."(): ".$message, SNAP_LOG_ERROR);
                throw \Snap\api\exception\RedemptionError::fromTransaction(null, ['message' => $message]);
            }
        }

        try {
            $totalQty = 0;
            $totalFees = 0;
            $totalXau = 0;
            foreach ($conversions as $conversion) {
                $startedTransaction = $this->app->getDBHandle()->inTransaction();
                if (!$startedTransaction) {
                    $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
                }

                // Get required data
                $accHolder = $this->app->myaccountholderStore()->getById($conversion->accountholderid);
                $redemption = $conversion->getRedemption();
                $product = $this->app->productStore()->getById($conversion->productid);
                $partner = $accHolder->getPartner();
                $this->log("Confirming conversion {$redemption->partnerrefno} ({$conversion->id})", SNAP_LOG_INFO);

                // Update conversion object
                $conversion->status = MyConversion::STATUS_PAYMENT_PAID;
                $conversion = $this->app->myconversionStore()->save($conversion);

                // Create ledgers
                $ledgers = $this->createLedgersForConversion($accHolder, $conversion, $redemption);
                $totalQty += $redemption->totalquantity;
                $totalFees +=  $redemption->redemptionfee + $redemption->insurancefee + $redemption->handlingfee + $redemption->specialdeliveryfee;
                $totalXau += $redemption->totalweight;
            }

            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
            $this->releaseConfirmConversionLock($conversions[0]);
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollback();
            }
            throw $e;
        }
        $refno = $conversion->getRefNo(true);
        $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
        $totalFees += $payment->customerfee + $payment->gatewayfee;

        $this->notify(new IObservation($conversions[0], IObservation::ACTION_CONFIRM, 0, [
            'event'     => MyGtpEventConfig::EVENT_CONVERSION_CONFIRMED,
            'projectbase' => $this->app->getConfig()->{'projectBase'},
            'accountholderid' => $accHolder->id,
            'productname'     => $product->name,
            'refno'           => $refno,
            'qty'             => $totalQty,
            'fees'            => $totalFees,
            'xau'             => $totalXau,
            'name'      => $accHolder->fullname,
            'receiver'  => $accHolder->email,
            // 'bccemail'  => $this->app->getConfig()->{'gtp.conversion.bcc.receiver'}, // `email@email.com, email2@email.com`
        ]));

        foreach ($conversions as $conversion) {
            //$this->sendConversionFeeToSAP($partner, $conversion);
            /* 
                2023-06-20:
                For otc/MBSB projects, dont send conversion fee to sap first.
                transfer data to gtp first only then send data to sap
            */
            if($this->app->getConfig()->{'project.conversion.diffsvr'} == '1'){
                $this->app->apiManager()->transferConversionBetweenSvr($conversion, count($conversions));
            }
            else{
                $this->sendConversionFeeToSAP($partner, $conversion);
            }

            // if OTC/MBSB projects, is a transaction from server to server. use different function
            if ($this->app->getConfig()->{'mygtp.db.conversiontransfer'}) {
                $this->app->apiManager()->transferConversionBetweenDb($conversion); 
            }
        }

        if ($partner->id == $this->app->getConfig()->{'gtp.nubex.partner.id'}){
            // inform nusagold/nubex user
            $_VIEW_conversion = $this->app->myconversionStore()->searchView()->select()->where('id', $conversions[0]->id)->one();
            
            $accholderemailinfo = [
                'accountholderid' => $accHolder->id,
                'productname'     => $product->name,
                'refno'           => $refno,
                'qty'             => $totalQty,
                'fees'            => $totalFees,
                'xau'             => $totalXau,
                'name'      => $accHolder->fullname,
                'receiver'  => $accHolder->email,
            ];
            $senderemail = $partner->senderemail;
            $sendername = $partner->sendername;
            $send = $this->sendNubexEmail($accholderemailinfo, $conversions[0], $_VIEW_conversion->deliveryaddress, $_VIEW_conversion->deliverycontactname1, $_VIEW_conversion->deliverycontactno1, $_VIEW_conversion->rdmitems, $senderemail, $sendername);
            if ($send){

            }else{
                
            }
        }

        return $conversions;
    }

    public function sendNubexEmail($accholderemailinfo, $conversion, $deliveryaddress, $deliverycontactname, $deliverycontactno, $items, $senderemail, $sendername){
        // $accholderemailinfo = [
        //     'accountholderid' => $accHolder->id,
        //     'productname'     => $product->name,
        //     'refno'           => $refno,
        //     'qty'             => $totalQty,
        //     'fees'            => $totalFees,
        //     'xau'             => $totalXau,
        //     'name'      => $accHolder->fullname,
        //     'receiver'  => $accHolder->email,
        // ];
        $projectBase = $this->app->getConfig()->{'projectBase'};
        $projectbase = "NUSAGOLD";
        $receiver = 'nusagold@ace2u.com, mzairis@nubex.my, faizal@nubex.my';
   
        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $receiver = 'test@silverstream.my';
        }
        
        
        $subject = 'NUSAGOLD Conversion Request.';
        $body = '
        <img class="img-fluid" style="width: 750px;height: 303px;" src="https://gtp2.ace2u.com/src/resources/images/'.$projectbase.'/conversion.png" alt="'.$projectbase.' EKYC Approved"><br><br>Your e-mail address is verified successfully
        Dear En Faizal,
        <br>
        <p>Please be informed that there is a conversion request made by an account holder for your kind action. Below are the details.</p>
        <br>
        Date requested: '.$conversion->createdon->format('Y-m-d h:i:s').'<br>
        Reference Number: '.$accholderemailinfo['refno'].'<br>
        Product Name: '.$accholderemailinfo['productname'].'<br>
        Quantity: '.$accholderemailinfo['qty'].'<br>
        <br><br>
        Item(s): 
        ';

        foreach (json_decode($items) as $x => $item){
            $y= $x+1;
            $body .= 'no.'.$y.' - '.$item->code.' - '.$item->serialnumber.'<br>';
        }
        $body .= '<br><br>
        Name: '.$deliverycontactname.'<br>
        Address: '.$deliveryaddress.'<br>
        Contact No: '.$deliverycontactno.'<br>
        Email address: '.$accholderemailinfo['receiver'].'<br>
        <br><br>
        Respectively ACE will arrange to send to you the above product type and quantity accordingly.<br>
        <br>
        Thanks <br>
        <br>
        <b>ACE Operation</b>
        ';

        $mailer = $this->app->getMailer();
        $receiverArr = explode(',', $receiver);
     
        foreach ($receiverArr as $receiver) {
            $receiver = trim($receiver, ' ');
            $mailer->addAddress($receiver);
        }
        $mailer->isHtml(true);
        $email = $senderemail;
        $name = $sendername;
        if (0 < strlen($email) && 0 < strlen($name)) {
            $mailer->setFrom($email, $name);
        }
        
        $mailer->Subject = $subject;
        $mailer->Body    = $body;

        $mailer->send();
    }

    /**
     * Cancels a conversion
     * 
     * @param MyConversion[] $conversions
     * @param RedemptionManager|null $redemptionManager 
     * @return void 
     */
    public function doCancelConversion($conversions, $redemptionManager = null)
    {
        if (!is_array($conversions)) {
            $conversions = [$conversions];
        }
        
        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }

        try {
            foreach ($conversions as $conversion) {
                $state = $this->getConversionStateMachine($conversion);
                if (! $state->can(MyConversion::STATUS_PAYMENT_CANCELLED)) {
                    $message = "Unable to cancel conversion {$conversion->refno}";
                    $this->log( __METHOD__."(): ".$message, SNAP_LOG_ERROR);
                    throw \Snap\api\exception\RedemptionError::fromTransaction(null, ['message' => $message]);           
                }

                $accHolder = $this->app->myaccountholderStore()->getById($conversion->accountholderid);
                $partner = $accHolder->getPartner();
                $redemptionManager = $redemptionManager ?? $this->app->redemptionManager();
                $redemptionManager->reverseRedemption($partner, $conversion->refno);

                $conversion->status = MyConversion::STATUS_PAYMENT_CANCELLED;
                $this->app->myconversionStore()->save($conversion);
            }
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollback();
            }
            throw $e;
        }
    }

    /**
     * Cancels a conversion
     * 
     * @param MyConversion[] $conversions
     * @param RedemptionManager|null $redemptionManager 
     * @return void 
     */
    public function doExpireConversion($conversions, $redemptionManager = null)
    {
        if (!is_array($conversions)) {
            $conversions = [$conversions];
        }
        
        $startedTransaction = $this->app->getDBHandle()->inTransaction();
        if (!$startedTransaction) {
            $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
        }

        try {
            foreach ($conversions as $conversion) {
                $state = $this->getConversionStateMachine($conversion);
                if (! $state->can(MyConversion::STATUS_EXPIRED)) {
                    $message = "Unable to expire conversion {$conversion->refno}";
                    $this->log( __METHOD__."(): ".$message, SNAP_LOG_ERROR);
                    throw \Snap\api\exception\RedemptionError::fromTransaction(null, ['message' => $message]);           
                }

                // Refresh conversion
                $conversion = $this->app->myconversionStore()->getById($conversion->id);

                $accHolder = $this->app->myaccountholderStore()->getById($conversion->accountholderid);
                $partner = $accHolder->getPartner();
                $redemptionManager = $redemptionManager ?? $this->app->redemptionManager();
                $redemptionManager->reverseRedemption($partner, $conversion->refno);

                $conversion->status = MyConversion::STATUS_EXPIRED;
                $this->app->myconversionStore()->save($conversion);
            }
            if ($ownsTransaction) {
                $this->app->getDBHandle()->commit();
            }
        } catch (\Exception $e) {
            if ($ownsTransaction) {
                $this->app->getDBHandle()->rollback();
            }
            throw $e;
        }
    }

    /**
     * Return paymentdetail from conversions
     * @param MyConversion[] $conversions 
     * @return MyPaymentDetail|null
     */
    public function getPaymentFromSplittedConversions($conversions)
    {
        $firstRef = "";
        foreach ($conversions as $conversion) {
            $matches = [];
            $isSplit = preg_match('/^CV\d+$/', $conversion->refno, $matches);
            $origRefNo = $matches[0];
            if (! strlen($firstRef)) {
                $firstRef = $origRefNo;
                continue;
            }

            if ($firstRef !== $origRefNo) {
                throw GeneralException::fromTransaction("Unable to get payment detail from different conversions");
            }
        }

        $paymentDetail = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $firstRef);
        return $paymentDetail;
    }

    /**
     * Creates the payment detail
     * @param MyAccountHolder   $accHolder          Account holder
     * @param string            $refno              Reference number of the conversion
     * @param float             $conversionFees     Conversion Fees (Courier + Insurance + Comissions etc)
     * @param float             $transactionFee     FPX/Wallet transaction fee
     * @param string            $paymentMode        Payment mode
     * @param string            $parterData         Partner data used for payment if available
     */
    protected function createPaymentForConversion(MyAccountHolder $accHolder, $refno, $conversionFees, $transactionFee, $paymentMode, $partnerData = '')
    {

        // Find the FPX API
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);

        if (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX === $paymentMode) {
            if (!$settings->companypaymentprovider) {
                $message = "Partner FPX API not found.";
                $this->log(__METHOD__."(): ". $message, SNAP_LOG_ERROR);
                throw MyGtpPartnerApiInvalid::fromTransaction([], [
                    'message' => $message
                ]);
            }

            $paymentProvider = BaseFpx::getInstance($settings->companypaymentprovider);
        } elseif (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET == $paymentMode) {

            if (!$settings->partnerpaymentprovider) {
                $message = "Partner Payment Provider API not found.";
                $this->log(__METHOD__."(): ". $message, SNAP_LOG_ERROR);
                throw MyGtpPartnerApiInvalid::fromTransaction([], [
                    'message' => $message
                ]);
            }
            
            $className = $settings->partnerpaymentprovider;
            if(class_exists($className . 'Conversion')) { 
                $className = $className . 'Conversion';
            }

            $paymentProvider = BaseWallet::getInstance($className);
            
        } elseif (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_CASA == $paymentMode) {

            if (0 == strlen($this->app->getConfig()->{'otc.casa.api'})) {
                $message = "OTC CASA API not found.";
                $this->log(__METHOD__."(): ". $message, SNAP_LOG_ERROR);
                throw MyGtpPartnerApiInvalid::fromTransaction([], [
                    'message' => $message
                ]);
            }
            
            $className = $this->app->getConfig()->{'otc.casa.api'};
            $paymentProvider = BaseCasa::getInstance($className);

        } else {
            $message = "Conversion Payment Mode not found.";
            $this->log(__METHOD__.":". $message, SNAP_LOG_ERROR);
            throw MyGtpPartnerApiInvalid::fromTransaction([], [
                'message' => $message
            ]);
        }

        // Create payment details
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $payment = $this->app->mypaymentdetailStore()->create([
            'accountholderid' => $accHolder->id,
            'amount'        => $conversionFees,
            'customerfee'   => $transactionFee,
            'paymentrefno'  => $this->app->mygtptransactionManager()->generateRefNo("P", $this->app->mypaymentdetailStore()),
            'sourcerefno'   => $refno,
            'status'        => MyPaymentDetail::STATUS_PENDING_PAYMENT,
            'productdesc'   => "Conversion Fees",
            "transactiondate" => $now
        ]);

        if (0 < strlen($partnerData)) {
            $payment->token = $partnerData;
        }

        $payment = $this->app->mypaymentdetailStore()->save($payment);

        // Create the payment in FPX / Wallet
        $data = $paymentProvider->initializeTransaction($accHolder, $payment);


        return $payment;
    }

    /**
     * Create relevant ledgers for conversion and related fees
     * 
     * @return MyLedger[]
     */
    protected function createLedgersForConversion(MyAccountHolder $accHolder, MyConversion $conversion, Redemption $redemption)
    {
        $this->log("Creating ledgers for conversion {$conversion->refno}.", SNAP_LOG_INFO);
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $ledgers = [];

        // User ledger
        $ledger = $this->app->myledgerStore()->create([
            'type'              => MyLedger::TYPE_CONVERSION,
            'typeid'            => $conversion->id,
            'accountholderid'   => $accHolder->id,
            'partnerid'         => $accHolder->partnerid,
            'debit'             => $redemption->totalweight,
            'credit'            => 0,
            'refno'             => $conversion->refno,
            'remarks'           => '',
            'status'            => MyLedger::STATUS_ACTIVE,
            'transactiondate'   => $now
        ]);
        $ledger = $this->app->myledgerStore()->save($ledger);
        $ledgers[] = $ledger;

        // Partner ledger
        $ledger = $this->app->myledgerStore()->create([
            'type'              => MyLedger::TYPE_ACEREDEEM,
            'typeid'            => $conversion->id,
            'accountholderid'   => 0,
            'partnerid'         => $accHolder->partnerid,
            'debit'             => 0,
            'credit'            => $redemption->totalweight,
            'refno'             => $conversion->refno,
            'remarks'           => '',
            'status'            => MyLedger::STATUS_ACTIVE,
            'transactiondate'   => $now
        ]);
        $ledger = $this->app->myledgerStore()->save($ledger);
        $ledgers[] = $ledger;

        return $ledgers;
    }

    /**
     * Get related fees for conversion
     * 
     * @param Partner $partner      The partner/merchant
     * @param Product $product      The product to convert to
     * @param int     $qty          The quantity of product
     * @return array
     */
    public function getFeesForConversion(Partner $partner, Product $product, $qty, $additionalData = null)
    {
        $calc = $partner->calculator();

        // Ace + Merchant Premium/Commission
        $premium = $calc->multiply($partner->getRedemptionPremiumFee($product), strval($qty));
        $commissions = $calc->multiply($partner->getRedemptionCommissionFee($product), strval($qty));
        $redemptionFee = $premium + $commissions;
        // $redemptionFee = $this->calculateApplicableFees($partner, $product->weight * $qty, MyFee::TYPE_CONVERSION_FEE);

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        $insuranceFee = $calc->multiply($partner->getRedemptionInsuranceFee($product), strval($qty));
        $handlingFee = $calc->multiply($partner->getRedemptionHandlingFee($product), strval($qty));

        // Delivery Fee (Courier Fee)
        //check if it have methodofreceive
        if(isset($additionalData['methodofreceive']) && strtolower($additionalData['methodofreceive']) == 'pickup') $deliveryFee = 0.0000;
        else $deliveryFee = $settings->courierfee;

        return [
            'redemptionFee' => $redemptionFee,
            'insuranceFee'  => $insuranceFee,
            'handlingFee'   => $handlingFee,
            'deliveryFee'   => $deliveryFee,
            'commissionFee' => $commissions,
            'premiumFee'    => $premium
        ];
    }

    /**
     * Generate required data for redemption
     * 
     * @return array
     */
    protected function generateRedemptionData(MyAccountHolder $accHolder, Partner $partner, Product $product, $qty, $refno = null)
    {
        $this->log("Generating Redemption data for conversion requested ({$product->code}) x {$qty} for partner {$partner->code}", SNAP_LOG_DEBUG);
        $branch = $partner->getBranch($accHolder->referralbranchcode);
        $refNo = $refno ?? $this->app->mygtptransactionManager()->generateRefNo("CV", $this->app->myconversionStore());
        //$accHolder->additionaldata contain methodofreceive & collectedbranchcode
        $additionalData = $accHolder->additionaldata;

        $achadditionaldata = $this->app->achadditionaldataStore()->searchTable()->select()
        ->where('accountholderid', $accHolder->id)
        ->one();

        $partnerName = $this->app->getConfig()->{'projectBase'};

        $pickupbranch = null; //default null
        if(strtolower($additionalData['methodofreceive']) == 'pickup'){
            $pickupbranch = $additionalData['collectedbranchcode'];
            $branchToPickup = $partner->getBranch($pickupbranch);
            $addressObj = new \stdClass;
            $addressObj->line1      = $branchToPickup->address;
            $addressObj->line2      = '';
            $addressObj->city       = $branchToPickup->city;
            $addressObj->postcode   = $branchToPickup->postcode;
            $addressObj->state      = '';
        } else {
            if($partnerName == 'BSN'){
                $addressObj = $achadditionaldata;
            }else{
                $addressObj = $accHolder->getAddress();
            }
            
        }
        

        // Item array => RedemptionManager::doRedemption()
        $itemArray = [[
            'serialno'     => '',
            'name'         => $product->name,
            'denomination' => floatval($product->weight),
            'quantity'     => intval($qty)
        ]];

        // Delivery Info => RedemptionManager::onDeliveryRedeem()
        $deliveryInfo = new \stdClass;
        $deliveryInfo->contactname1     = $accHolder->getFullName();
        $deliveryInfo->contact_mobile1  = $accHolder->phoneno;

        if($partnerName == 'BSN'){
            $deliveryInfo->address1         = $addressObj->mailingaddress1;
            $deliveryInfo->address2         = $addressObj->mailingaddress2;
            $deliveryInfo->city             = $addressObj->mailingtown;
            $deliveryInfo->postcode         = $addressObj->mailingpostcode;
            $deliveryInfo->state            = $addressObj->mailingstate;

        }else{
            $deliveryInfo->address1         = $addressObj->line1;
            $deliveryInfo->address2         = $addressObj->line2;
            $deliveryInfo->city             = $addressObj->city;
            $deliveryInfo->postcode         = $addressObj->postcode;
            $deliveryInfo->state            = $addressObj->state;
        }
        $deliveryInfo->country          = "Malaysia";
        $totalWeight                    = $product->weight * $qty;

        // Special Delivery if more than 500g. Refer to RedemptionManager::onDeliveryRedeem
        $redemptionType = 500 < $totalWeight ? Redemption::TYPE_SPECIALDELIVERY : Redemption::TYPE_DELIVERY;
            
        // Fees
        $feeArray = $this->getFeesForConversion($partner, $product, $qty,$additionalData);

        $data = [
            'refNo' => $refNo,
            'itemArray'     => $itemArray,
            'deliveryInfo'  => $deliveryInfo,
            'totalWeight'   => $totalWeight,
            'branchId'      => $branch->id,
            'redemptionType'=> $redemptionType,
            'remarks'       => "Conversion {$refNo} - {$deliveryInfo->contactname1} ({$totalWeight}g)",
            'pickupbranch'  => $pickupbranch
        ];
        return array_merge($data, $feeArray);
    }


    protected function validateConversionRequest(MyAccountHolder $accHolder, Partner $partner, Product $product, $qty, $paymentMode, $accMgr = null)
    {
        $accMgr = $accMgr ?? $this->app->mygtpaccountManager();
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        if (!$accHolder->canDoTransaction()) {
            $this->logDebug(__METHOD__. "Accountholder ID {$accHolder->id} not allowed to perform conversion.");
            throw MyGtpActionNotPermitted::fromTransaction([], ['message' => ""]);
        }

        $hasPaymentProvider = true;
        if (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX === $paymentMode && !$settings->companypaymentprovider) {
            $message = "Partner FPX API not set.";
            $hasPaymentProvider = false;
        } elseif (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET == $paymentMode && !$settings->partnerpaymentprovider) {
            $message = "Partner payment provider API not set.";
            $hasPaymentProvider = false;
        } elseif (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_CASA == $paymentMode && 0 == strlen($this->app->getConfig()->{'otc.casa.api'})) {
            $message = "OTC CASA API not set.";
            $hasPaymentProvider = false;
        }

        if (! $hasPaymentProvider) {
            $this->log(__METHOD__.":". $message, SNAP_LOG_ERROR);
            throw MyGtpPartnerApiInvalid::fromTransaction([], [
                'message' => $message
            ]);
        }

        // Maximum delivery weight is 100
        // $maxXau = $settings->maxxauperdelivery ?? 100;
        // if ($maxXau < $product->weight) {
        //     throw MyGtpProductInvalid::fromTransaction($product);
        // }

        // // Maximum delivery pieces is 30
        // $maxPcs = $settings->maxpcsperdelivery ?? 30;
        // if ($maxPcs < $qty) {
        //     throw MyGtpProductInvalid::fromTransaction($product);
        // }

        // Check if given product is deliverable or not
        if (! $product->deliverable || ! $product->weight || !$partner->canRedeem($product)) {
            throw MyGtpProductInvalid::fromTransaction($product);
        }

        // Only allow whole numbers & larger than 0
        if (0 >= $qty || floor($qty) != floatval($qty)) {
            throw MyGtpInvalidAmount::fromTransaction([], ['message' => "Invalid quantity ({$qty}) given."]);
        }

        // Check if account holder has address
        if (!$accHolder->getAddress()) {
            throw MyGtpActionNotPermitted::fromTransaction([], ['message' => "No address added."]);
        }

        // Check if account holder has enough balance for conversion
        $calc        = $partner->calculator(false);
        $availGold = $accMgr->getAccountHolderAvailableGoldBalance($accHolder);
        $pendingDebits = $accMgr->getPendingPaymentXauDebits($accHolder);

        $totalConversionWeight = $calc->multiply($product->weight, strval($qty));
        $balance     = $calc->sub(strval($availGold), strval($totalConversionWeight));
        $this->log(__METHOD__."():Available balance: {$availGold}g, Total conversion weight: {$totalConversionWeight}g, Balance after transaction: {$balance}", SNAP_LOG_DEBUG);
        if (0 > $balance) {
            $this->log(__METHOD__."(): Account holder ID ({$accHolder->id}) does not have enough gold balance to perform conversion of {$totalConversionWeight}g.", SNAP_LOG_ERROR);
            throw MyGtpAccountHolderNotEnoughBalance::fromTransaction([]);
        }

        $balance     = $calc->sub($balance, $pendingDebits);
        $this->log(__METHOD__."(): Pending debits: {$pendingDebits}g, Balance after transaction: {$balance}", SNAP_LOG_DEBUG);
        if (0 > $balance) {
            $this->log(__METHOD__."(): Account holder ID ({$accHolder->id}) does not have enough gold balance to perform conversion of {$totalConversionWeight}g.", SNAP_LOG_ERROR);
            throw GeneralException::fromTransaction([], ['message' => gettext("Please complete or cancel existing FPX transactions before proceeding.")]);
        }

        $this->log(__METHOD__."(): Min Account Balance: {$settings->minbalancexau}g, Balance after transaction: {$balance}", SNAP_LOG_DEBUG);
        if ($balance < $settings->minbalancexau) {
            $this->log(__METHOD__."(): Account holder ID ({$accHolder->id}) does not have enough gold balance to perform conversion of {$totalConversionWeight}g.", SNAP_LOG_ERROR);
            throw MyGtpInvalidAmount::fromTransaction([], ['message' => "Minimum balance after conversion must be above {$settings->minbalancexau}g."]);
        }

    }

    /**
     * Get the fee for payment mode
     *
     * @param MyPartnerSetting $settings
     * @param string $paymentMode
     * @return float
     */
    public function getPaymentFee(MyPartnerSetting $settings, $paymentMode)
    {
        if (MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX === $paymentMode) {
            return $settings->transactionfee;
        }

        // Currently wallet no fee
        return 0;
    }

     /**
     * Updates the conversion status manually for partners without external couriers ( Eg: NUBEX )
     * Status Change from confirmed -> completed
     * @param MyConversion[] $conversion 
     * @param RedemptionManager $redemptionManager 
     * @return MyConversion[]
     */
    public function updateManualConversionStatus($conversions)
    {
        // Get 
        $this->log("into MyConversion::updateManualConversionStatus({$conversions}) method", SNAP_LOG_DEBUG);

        try {
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());

            $totalQty = 0;
            $totalFees = 0;
            $totalXau = 0;
            foreach ($conversions as $conversion) {
               
                // Get required data
                $accHolder = $this->app->myaccountholderStore()->getById($conversion->accountholderid);
                $redemption = $conversion->getRedemption();
                $product = $this->app->productStore()->getById($conversion->productid);
                $partner = $accHolder->getPartner();
                $this->log("Confirming conversion {$redemption->partnerrefno} ({$conversion->id})", SNAP_LOG_INFO);

                $redemption->status = Redemption::STATUS_COMPLETED;
                $redemption = $this->app->redemptionStore()->save($redemption);
                // Update conversion object
                // $conversion->status = MyConversion::STATUS_PAYMENT_PAID;
                // $conversion = $this->app->myconversionStore()->save($conversion);

                // Create ledgers
                // $ledgers = $this->createLedgersForConversion($accHolder, $conversion, $redemption);
                // $totalQty += $redemption->totalquantity;
                // $totalFees +=  $redemption->redemptionfee + $redemption->insurancefee + $redemption->handlingfee + $redemption->specialdeliveryfee;
                // $totalXau += $redemption->totalweight;
            }

           
        } catch (\Exception $e) {
            throw $e;
        }
        // $refno = $conversion->getRefNo(true);
        // $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
        // $totalFees += $payment->customerfee + $payment->gatewayfee;

        // $this->notify(new IObservation($conversions[0], IObservation::ACTION_CONFIRM, 0, [
        //     'event'     => MyGtpEventConfig::EVENT_CONVERSION_CONFIRMED,
        //     'projectbase' => $this->app->getConfig()->{'projectBase'},
        //     'accountholderid' => $accHolder->id,
        //     'productname'     => $product->name,
        //     'refno'           => $refno,
        //     'qty'             => $totalQty,
        //     'fees'            => $totalFees,
        //     'xau'             => $totalXau,
        //     'name'      => $accHolder->fullname,
        //     'receiver'  => $accHolder->email
        // ]));

        return $conversions;
    }

    
     /**
     * Sends Email to Relevant partners to notifuy on completed conversions
     * @param MyConversion[] $conversion 
     * @param string $email
     * @return MyConversion[]
     */
    public function sendEmailNotificationToPartner($conversions, $email = null)
    {
        // Get 
        $this->log("into MyConversion::updateManualConversionStatus({$conversions}) method", SNAP_LOG_DEBUG);

        try {
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $totalQty = 0;
            $totalFees = 0;
            $totalXau = 0;
            foreach ($conversions as $conversion) {
        
                // Get required data
                $accHolder = $this->app->myaccountholderStore()->getById($conversion->accountholderid);
                $redemption = $conversion->getRedemption();

                // Only allow notificaiton if status is confirmed
                if (Redemption::STATUS_COMPLETED != $redemption->status) {
                    throw RedemptionError::fromTransaction($redemption, ['message' => gettext("Unable to create conversion, please contact customer service.")]);
                }
                $product = $this->app->productStore()->getById($conversion->productid);
                $partner = $accHolder->getPartner();
                
                $totalQty += $redemption->totalquantity;
                $totalFees +=  $redemption->redemptionfee + $redemption->insurancefee + $redemption->handlingfee + $redemption->specialdeliveryfee;
                $totalXau += $redemption->totalweight;
                $refno = $conversion->getRefNo(true);
                $this->log("Sending Email to {$partner} {$redemption->partnerrefno} ({$conversion->id})", SNAP_LOG_INFO);
                
                // Send to nubex
            
                // inform nusagold/nubex user
                // $_VIEW_conversion = $this->app->myconversionStore()->searchView()->select()->where('id', $conversions[0]->id)->one();
                
                $accholderemailinfo = [
                    'accountholderid' => $accHolder->id,
                    'productname'     => $product->name,
                    'refno'           => $refno,
                    'qty'             => $totalQty,
                    'fees'            => $totalFees,
                    'xau'             => $totalXau,
                    'name'      => $accHolder->fullname,
                    'receiver'  => $accHolder->email,
                ];
                $senderemail = $partner->senderemail;
                $sendername = $partner->sendername;
                $send = $this->sendNubexEmail($accholderemailinfo, $conversions[0], $conversion->rdmdeliveryaddress, $conversion->rdmdeliverycontactname1, $conversion->rdmdeliverycontactno1, $conversion->rdmitems, $senderemail, $sendername);
                if ($send){
    
                }else{
                    
                }
            
                // $this->notify(new IObservation($conversions[0], IObservation::ACTION_OTHER, 0, [
                //     'event'     => MyGtpEventConfig::EVENT_CONVERSION_COMPLETED,
                //     'projectbase' => $this->app->getConfig()->{'projectBase'},
                //     'accountholderid' => $accHolder->id,
                //     'productname'     => $product->name,
                //     'refno'           => $refno,
                //     'qty'             => $totalQty,
                //     'fees'            => $totalFees,
                //     'xau'             => $totalXau,
                //     'name'      => $accHolder->fullname,
                //     'receiver'  => $email,
                //     'customeremail'  => $accHolder->email
                // ]));
            }

           
        } catch (\Exception $e) {
            throw $e;
        }
        // $refno = $conversion->getRefNo(true);
        // $payment = $this->app->mypaymentdetailStore()->getByField('sourcerefno', $refno);
        // $totalFees += $payment->customerfee + $payment->gatewayfee;



        return $conversions;
    }

    public function jobSendConversionFeeToSAP($partner, $conversion){
        return $this->sendConversionFeeToSAP($partner, $conversion);
    }

    /**
     * Undocumented function
     *
     * @param Partner $partner
     * @param MyConversion $conversion
     * @return bool
     */
    protected function sendConversionFeeToSAP(Partner $partner, MyConversion $conversion)
    {

        // Get view table
        $conversion = $this->app->myconversionStore()->searchView()->select()->where('id', $conversion->id)->one();
        $apiManager = $this->app->apiManager();
        $version    = $requestParams['version'] = '1.0my';
        $requestParams['total_amount'] = $conversion->rdmredemptionfee + $conversion->rdminsurancefee + $conversion->rdmhandlingfee;

        try {
            $this->log(__METHOD__ . "({$conversion->getRefNo()}) init sapFeeCharge START.", SNAP_LOG_DEBUG);
            $sap_return = $apiManager->sapFeeCharge($partner, $version, $conversion, $conversion->rdmtotalweight, 'conversion_fee', $requestParams);
            $this->log(__METHOD__ . "({$conversion->getRefNo()}) init sapFeeCharge END.", SNAP_LOG_DEBUG);
    
            $sapSucceed = 'Y' == $sap_return[0]['success'];
    
            if (!$sapSucceed) {
                $message = "SAP return fail for conversion fee ({$conversion->getRefNo()})";
                $this->log(__METHOD__ . "(): {$message}", SNAP_LOG_ERROR);
                throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'SAP conversion fee submission failed.']);
            }
    
            return true;
            
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Fail on sending conversion fee to SAP, " . $e->getMessage(), SNAP_LOG_ERROR);
        }

        return false;
    }

    protected function getConversionStateMachine(MyConversion $conversion)
    {
        $config = [
            'property_path' => 'status',
            'states' => [
                MyConversion::STATUS_PAYMENT_PENDING        => ['type' => 'initial', 'properties' => []],
                MyConversion::STATUS_PAYMENT_PAID           => ['type' => 'final', 'properties' => []],
                MyConversion::STATUS_PAYMENT_CANCELLED      => ['type' => 'final', 'properties' => []],
                MyConversion::STATUS_EXPIRED                => ['type' => 'final', 'properties' => []],

            ],
            'transitions' => [
                MyConversion::STATUS_PAYMENT_PAID           => ['from' => [MyConversion::STATUS_PAYMENT_PENDING], 'to' => MyConversion::STATUS_PAYMENT_PAID],
                MyConversion::STATUS_PAYMENT_CANCELLED      => ['from' => [MyConversion::STATUS_PAYMENT_PENDING], 'to' => MyConversion::STATUS_PAYMENT_CANCELLED],
                MyConversion::STATUS_EXPIRED                => ['from' => [MyConversion::STATUS_PAYMENT_PENDING], 'to' => MyConversion::STATUS_EXPIRED],

            ]
        ];
        return $this->createStateMachine($conversion, $config);
    }

    /**
     *
     * @return \Finite\StateMachine\StateMachine
     */
    protected function createStateMachine($object, array $config)
    {
        $stateMachine = new \Finite\StateMachine\StateMachine;

        $loader = new \Finite\Loader\ArrayLoader($config);
        $loader->load($stateMachine);
        $stateMachine->setStateAccessor(new \Finite\State\Accessor\PropertyPathStateAccessor($config['property_path']));
        $stateMachine->setObject($object);
        $stateMachine->initialize();
        return $stateMachine;
    }

    /**
     * Create a lock for confirming conversions by partner
     * 
     * @param MyConversion|null $conversion
     * @return void 
     */
    protected function waitForConfirmConversionLock($conversion)
    {
        if (!$conversion) {
            $projectBase = $this->app->getConfig()->{'projectBase'};
            $lockKey = '{ConfirmConversion}:' . $projectBase;
        } else {
            $redemption = $conversion->getRedemption();
            $partner = $this->app->partnerStore()->getById($redemption->partnerid);
            $lockKey = '{ConfirmConversion}:' . $partner->code;
        }
        $cacher = $this->app->getCacher();
        $cacher->waitForLock($lockKey, 1, 60, 60);
    }

    protected function releaseConfirmConversionLock($conversion)
    {
        if (!$conversion) {
            $projectBase = $this->app->getConfig()->{'projectBase'};
            $lockKey = '{ConfirmConversion}:' . $projectBase;
        } else {
            $redemption = $conversion->getRedemption();
            $partner = $this->app->partnerStore()->getById($redemption->partnerid);
            $lockKey = '{ConfirmConversion}:' . $partner->code;
        }
        $cacher = $this->app->getCacher();
        $cacher->unlock($lockKey);
    }
    
    public function registerConversionFromDb ($partner, $redemption, $arrMyConversion)
    {
        $count = $this->app->myconversionStore()
                    ->searchTable()
                    ->select(['id'])
                    ->where('refno', $redemption->partnerrefno)
                    ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyConversion, ['partnerrefno' => $redemption->partnerrefno, 'code' => $partner->code, 'action' => 'conversion']);
        }

        $arrMyConversion['refno'] = $redemption->partnerrefno;
        $arrMyConversion['redemptionid'] = $redemption->id;
        $myConversion = $this->app->myconversionStore()->create($arrMyConversion);
        $myConversionSaved = $this->app->myconversionStore()->save($myConversion);
        
        return $myConversionSaved;
    }

    public function registerConversionFromSvr ($partner, $redemption, $arrMyConversion)
    {
        $count = $this->app->myconversionStore()
                    ->searchTable()
                    ->select(['id'])
                    ->where('refno', $redemption->partnerrefno)
                    ->count();
        if($count) {
            throw \Snap\api\exception\RefDuplicatedException::fromTransaction($arrMyConversion, ['partnerrefno' => $redemption->partnerrefno, 'code' => $partner->code, 'action' => 'conversion']);
        }
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
        $arrMyConversion['refno'] = $redemption->partnerrefno;
        $arrMyConversion['redemptionid'] = $redemption->id;
        $arrMyConversion['createdon'] = $triggerOn;
        $arrMyConversion['modifiedon'] = $triggerOn;
        $myConversion = $this->app->myconversionStore()->create($arrMyConversion);
        $myConversionSaved = $this->app->myconversionStore()->save($myConversion);
        
        return $myConversionSaved;
    }

}
