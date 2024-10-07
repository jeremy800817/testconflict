<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\IObservable;
use Snap\api\exception\MyGtpAccountHolderNoPincode;
use Snap\api\exception\MyGtpVerificationCodeInvalid;
use Snap\object\MyAccountHolder;
use Snap\object\MyGoldInviteTransfer;
use Snap\object\Partner;
use Snap\object\MyToken;
use Snap\TObservable;
use Snap\TLogging;

/**
 * This class contains methods related to the authentication process
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpGoldInviteTransferManager implements IObservable
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
     * invite friend using phone number and email
     *
     * @param obj $partner                  Partner obj
     * @param obj $accholder                Password provided by application
     * @param string  $friendname           Friend name
     * @param string  $friendemail          Friend email
     * @param string  $friendcontactno      Friend contact number
     * @param string  $referralcode         Referral code
     * @param string  $message              Message
     * @return array
     */
    public function processFriendInvitation($partner,$accholder,$friendname,$friendemail,$friendcontactno,$referralcode,$message)
    {
        //GET PARTNER
        $partnerid  = $partner->id;

        //GET ACCHOLDER 
        //$accholder  = $this->app->myaccountHolderStore()->getById(744); //for testing
        $accid      = $accholder->id;
        $acccode    = $accholder->accountholdercode;

        $friendInvitation = $this->app->mygoldinvitetransferStore()
                                    ->create([
                                        'partnerid'      => $partnerid,
                                        'senderid'       => $accid,
                                        'receivername'   => $friendname,
                                        'receiveremail'  => $friendemail,
                                        'sendercode'     => $referralcode,
                                        'contact'        => $friendcontactno,
                                        'message'        => $message,
                                        'type'           => MyGoldInviteTransfer::TYPE_INVITE,
                                        'status'         => MyGoldInviteTransfer::STATUS_ACTIVE
                                    ]);

        $inviteFriend = $this->app->mygoldinvitetransferStore()->save($friendInvitation);

        return $inviteFriend;
    }

    /**
     * Transfer gold to friends
     *
     * @param obj $partner                  Partner obj
     * @param obj $accholder                Password provided by application
     * @param string  $friendname           Friend name
     * @param string  $friendemail          Friend email
     * @param string  $friendcontactno      Friend contact number
     * @param string  $referralcode         Referral code
     * @param string  $message              Message
     * @return array
     */
    public function processGoldTransfer($partner,$accountholder,$receivercontactno,$receiveremail,$weight,$price,$amount,$receiveracccode,$pin,$message)
    {
        /*check pin*/
        $myaccmanager = $this->app->mygtpaccountManager();

        // Verify the pincode
        if (!$myaccmanager->verifyAccountHolderPin($accountholder, $pin)) {
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        }

        //GET PARTNER
        $partnerid  = $partner->id;

        //GET ACCHOLDER OF RECEIVERS
        $accholderReceiver = $this->app->myaccountholderStore()
                    ->searchTable()
                    ->select()
                    ->where('partnerid', $partnerid)
                    ->andWhere('phoneno', $receivercontactno)
                    ->andWhere('email', $receiveremail)
                    ->andWhere('accountholdercode', $receiveracccode)
                    ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                    ->one();

        // If account holder is not found
        if (!$accholderReceiver) {
            throw MyGtpVerificationCodeInvalid::fromTransaction(null, ['message' => 'Invalid Account Holder.']);
        }

        $accid              = $accountholder->id; //sender accholder
        $acccode            = $accountholder->accountholdercode; //sender accholder

        $friendInvitation = $this->app->mygoldinvitetransferStore()
                                    ->create([
                                        'partnerid'      => $partnerid,
                                        'senderid'       => $accid,
                                        'receiverid'     => $accholderReceiver->id,
                                        'receivername'   => $accholderReceiver->fullname,
                                        'receiveremail'  => $accholderReceiver->email,
                                        'receiveremail'  => $accholderReceiver->email,
                                        'xau'            => $weight,
                                        'price'          => $price,
                                        'amount'         => $amount,
                                        'contact'        => $accholderReceiver->phoneno,
                                        'message'        => $message,
                                        'type'           => MyGoldInviteTransfer::TYPE_TRANSFER,
                                        'status'         => MyGoldInviteTransfer::STATUS_ACTIVE
                                    ]);

        $inviteFriend = $this->app->mygoldinvitetransferStore()->save($friendInvitation);

        return $inviteFriend;
    }
    
}
