<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\InputException;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                     ID of the account holder
 * @property        int                 $partnerid              The partnerid for this account holder
 * @property        string              $email                  Email address of the account holder
 * @property        string              $phoneno                Phone number of the account holder
 * @property        string              $password               The hashed + salted password of the account holder
 * @property        string              $oldpassword            The old hashed + salted password of the account holder
 * @property        enum                $preferredlang          Preferred language selected by the account holder
 * @property        string              $fullname               The fullname of the account holder
 * @property        string              $accountholdercode      The customer code of the account holder
 * @property        string              $mykadno                MyKAD number of the account holder
 * @property        string              $occupation             Occupation of the account holder
 * @property        int                 $occupationcategoryid   Occupation category of the account holder
 * @property        int                 $occupationsubcategoryid   Occupation subcategory of the account holder
 * @property        string              $referralbranchcode     Referral branch code of the account holder
 * @property        string              $referralsalespersoncode     Referral salesperson code of the account holder
 * @property        string              $pincode                Hashed pincode of the account holder
 * @property        string              $sapacebuycode          The accountholder acebuycode from SAP
 * @property        string              $sapacesellcode         The accountholder acesellcode from SAP
 * @property        int                 $bankid                 The id of bank selected by the account holder
 * @property        string              $accountname        The account holder name for the selected bank
 * @property        string              $accountnumber      The account number for the selected bank
 * @property        string              $accounttype      The account type for the selected bank
 * @property        string              $nokfullname            The full name of next of kin
 * @property        string              $nokmykadno             The mykad number of next of kin
 * @property        string              $nokphoneno             Next of kin phone number
 * @property        string              $nokemail               Next of kin email
 * @property        string              $nokaddress             Next of kin address
 * @property        string              $nokrelationship        Next of kin relationship
 * @property        int                 $investmentmade         The status of initial investment of the account holder
 * @property        int                 $ispep                  Either the person is pep or not
 * @property        string              $pepdeclaration         The questionnaire declaration
 * @property        int                 $kycstatus              The status of ekyc of the account holder
 * @property        int                 $amlastatus             The status of AMLA of the account holder
 * @property        int                 $emailtriggeredon       Email triggered timestamp
 * @property        int                 $emailverifiedon        Email verified timestamp
 * @property        int                 $phoneverifiedon        Phone number verified timestamp
 * @property        string              $partnercusid           Partner customer id
 * @property        string              $partnercusdata         Partner customer data
 * @property        string              $statusremarks          The admin remarks for this user status change (Blacklist/Suspended)
 * @property        int                 $status                 The status of the account holder
 * @property        DateTime            $passwordmodified       The time the password was last modified
 * @property        DateTime            $lastnotifiedon         The time when the last idle account notification was sent
 * @property        DateTime            $lastloginon            The time when user last logged in
 * @property        DateTime            $lastloginip            The IP address when user last logged in
 * @property        DateTime            $verifiedon             The time the account was verified
 * @property        DateTime            $createdon              Time this record is created
 * @property        DateTime            $modifiedon             Time this record is last modified
 * @property        int                 $createdby              User ID
 * @property        int                 $modifiedby             UserID
 * @property        float               $loantotal              Loan Total
 * @property        float               $loanbalance            Loan Balance
 * @property        DateTime            $loanapprovedate        Loan Approve Datetime
 * @property        string              $loanapproveby          Loan Approve person name
 * @property        string              $loanstatus             Loan Status
 * @property        string              $loanreference          Loan reference number
 * @property        int                 $passwordset            The status of password if it has been reset or not (for BSN)
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyAccountHolder extends SnapObject
{

    const LANG_EN = "EN";
    const LANG_BM = "MS";
    const LANG_CN = "ZH";

    const KYC_INCOMPLETE = 0;
    const KYC_PASSED    = 1;
    const KYC_PENDING   = 2;
    const KYC_FAILED      = 7;

    const STATUS_SUSPENDED   = 2;
    const STATUS_BLACKLISTED = 4;
    const STATUS_CLOSED = 5;
    const STATUS_FORCECLOSED = 6;

    const INVESTMENT_NONE = 0;
    const INVESTMENT_MADE = 1;

    const PHONE_UNVERIFIED  = 0;
    const PHONE_VERIFIED    = 1;

    const AMLA_PENDING = 0;
    const AMLA_PASSED  = 1;
    const AMLA_FAILED  = 2;

    const PEP_PENDING  = 0;
    const PEP_PASSED  = 1;
    const PEP_FAILED  = 2;

    const PEP_FLAG = 1;

    const LOAN_PENDING  = 0;
    const LOAN_APPROVED  = 1;
    const LOAN_SETTLED  = 2;

    
    const PARTNERPARENT_MASTER  = 0;
    const PARTNERPARENT_LOAN  = 1;
    const PARTNERPARENT_AFFILIATE_MEMBER  = 2;
    const PARTNERPARENT_PUBLIC  = 3;
    const PARTNERPARENT_AFFILIATE_PUBLIC  = 4;
    
	#bsn reserve from 1 to 20
    const TYPE_SENDIRI = 1;
    const TYPE_BERSAMA = 2;
    const TYPE_ORGANIS = 3;
    const TYPE_AMANAH = 4;
    const TYPE_UNKNOWN = 5;
    const TYPE_CASHLES = 6;
    const TYPE_CASHLNE = 7;
	
	#bsn account balance type
	const TYPE_GIRO = 29;
	const TYPE_GIROI = 41;
	
	#alrahji reserve from 21 to 40
	const TYPE_COMPANY = 21;
    const TYPE_COHEADING = 22;
    const TYPE_SOLEPROPRIETORSHIP = 23;
    const TYPE_INDIVIDUAL = 24;

    #bsn password reset
    const PASSWORD_SET_YES = 1;
    const PASSWORD_SET_NO = 0;

    /**
     * This method will initialise the array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's constructor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
            'partnerid' => null,
            'email'     => null,
            'phoneno'   => null,
            'password'  => null,
            'oldpassword' => null,
            'preferredlang' => null,
            'fullname' => null,
            'campaigncode' => null,
            'accountholdercode' => null,
            'mykadno' => null,
            //'occupation' => null,
            'occupationcategoryid' => null,
            'occupationsubcategoryid' => null,
            'referralbranchcode' => null,
            'referralsalespersoncode' => null,
            'referralintroducercode' => null,
            'pincode' => null,
            'sapacebuycode' => null,
            'sapacesellcode' => null,
            'bankid' => null,
            'accountname' => null,
            'accountnumber' => null,
            'accounttype' => null,
            'nokfullname' => null,
            'nokmykadno' => null,
            'nokphoneno' => null,
            'nokemail'   => null,
            'nokaddress' => null,
            'nokrelationship' => null,
            'investmentmade' => null,
            'ispep' => null,
            'pepdeclaration' => null,
            'pepstatus' => null,
            'kycstatus' => null,
            'amlastatus' => null,
            'emailtriggeredon' => null,
            'emailverifiedon' => null,
            'phoneverifiedon' => null,
            'statusremarks' => null,
            'status' => null,
            'passwordmodified'  => null,
            'lastnotifiedon'   => null,
            'lastloginon'   => null,
            'lastloginip'   => null,
            'verifiedon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
            'partnercusid' => null,
            'partnercusdata' => null,
            'type' => null,
            'loantotal' => null,
            'loanbalance' => null,
            'loanapprovedate' => null,
            'loanapproveby' => null,
            'loanstatus' => null,
            'loanreference' => null,
            'note' => null,  //dont have to add to table.use for wallet purpose.
            'accesstoken' => null,  //dont have to add to table.use for wallet purpose.
            'iskycmanualapproved' => null,
            'additionaldata' => null, //dont have to add to table. use to send extra info
            'passwordset' => null
        );

        $this->viewMembers = array(
            'addressline1' => null,
            'addressline2' => null,
            'addresspostcode' => null,
            'addresscity' => null,
            'addressstate' => null,
            'bankname' => null,
            'xaubalance' => null,
            'amountbalance' => null,
            'bankcode' => null,
            'bankname' => null,
            'occupationcategory' => null,
            'occupationsubcategory' => null,
            'kycremarks' => null,
            'amlasourcetype' => null,
            'partnercode' => null,
            'partnername' => null,
            'partnerparent' => null,
            'typename' => null,
            'referralbranchname' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
        );
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        // $this->validateRequiredField($this->members['preferredlang'], 'preferredlang');
        $this->validateRequiredField($this->members['fullname'], 'fullname');
        $this->validateRequiredField($this->members['mykadno'], 'mykadno');
        //$this->validateRequiredField($this->members['occupation'], 'occupation');
        $this->validateRequiredField($this->members['occupationcategoryid'], 'occupationcategoryid');
        $this->validateRequiredField($this->members['accountholdercode'], 'accountholdercode');

        $existingHolder = $this->getStore()->getByField('accountholdercode', $this->members['accountholdercode'], ['id']);
        if ($existingHolder && $existingHolder->id != $this->members['id']) {
            throw new InputException("Duplicate account code not allowed.", InputException::FIELD_ERROR);
        }

        return true;
    }

    public function initialInvestmentMade()
    {
        return self::INVESTMENT_MADE == $this->members['investmentmade'];
    }

    public function ekycPassed()
    {
        return self::KYC_PASSED == $this->members['kycstatus'];
    }

    public function ekycPending()
    {
        return self::KYC_PENDING == $this->members['kycstatus'];
    }

    public function ekycIncomplete()
    {
        return self::KYC_INCOMPLETE == $this->members['kycstatus'];
    }

    public function ekycFailed()
    {
        return self::KYC_FAILED == $this->members['kycstatus'];
    }

    public function amlaPending()
    {
        return self::AMLA_PENDING == $this->members['amlastatus'];
    }

    public function amlaPassed()
    {
        return self::AMLA_PASSED == $this->members['amlastatus'];
    }

    public function amlaFailed()
    {
        return self::AMLA_FAILED == $this->members['amlastatus'];
    }

    public function isPep()
    {
        return 1 == $this->members['ispep'];
    }

    public function isKYCManualApproved()
    {
        return 1 == $this->members['iskycmanualapproved'];
    }

    public function pepPending()
    {
        return self::PEP_PENDING == $this->members['pepstatus'];
    }

    public function pepPassed()
    {
        return self::PEP_PASSED == $this->members['pepstatus'];
    }

    public function pepFailed()
    {
        return self::PEP_FAILED == $this->members['pepstatus'];
    }



    public function isVerified()
    {
        return !empty($this->members['emailverifiedon']) && !empty($this->members['phoneverifiedon']);
    }

    /**
     * Checks if the account holder has passed onboarding (EKYC & AMLA)
     */
    public function passRegulatoryChecks()
    {
        if ($this->isPep()) {
            return $this->ekycPassed() && $this->amlaPassed() && $this->pepPassed();
        }

        return $this->ekycPassed() && $this->amlaPassed();
    }

    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_ACTIVE:
                return gettext("Active");
                break;
            case self::STATUS_INACTIVE:
                return gettext("Inactive");
                break;
            case self::STATUS_SUSPENDED:
                return gettext("Suspended");
                break;
            case self::STATUS_BLACKLISTED:
                return gettext("Blacklisted");
                break;
            case self::STATUS_CLOSED:
                return gettext("Closed");
                break;
            default:
                return "";
                break;
        }
    }

    public function getEkycStatusString()
    {
        if ($this->ekycIncomplete()) {
            return gettext("Incomplete");
        }

        if ($this->ekycPending()) {
            return gettext("Pending");
        }

        if ($this->ekycFailed()) {
            return gettext("Failed");
        }

        if ($this->amlaPending()) {
            return gettext("AMLA Pending");
        }

        if ($this->amlaFailed()) {
            return gettext("AMLA Failed");
        }

        if ($this->isPep() && $this->pepPending()) {
            return gettext("PEP Pending");
        }

        if ($this->isPep() && $this->pepFailed()) {
            return gettext("PEP Failed");
        }

        if ($this->ekycPassed()) {
            return gettext("Passed");
        }

        // Should not return this status. Statuses should be handled by above.
        return gettext("Failed");
    }

    public function getAmlaStatusString()
    {
        switch ($this->members['amlastatus']) {
            case 0:
                return gettext('Pending');
                break;
            case 1:
                return gettext('Passed');
                break;
            case 2:
                return gettext('Failed');
                break;
            default:
                return gettext('Pending');
                break;
        }
    }

    /**
     * Checks if the account holder can proceed with transaction
     * Conditions:
     * 1. AccountHolder not suspended/blacklisted
     * 2. AccountHolder must pass EKYC & AMLA
     * 3. AccountHolder must have verified email & phone number
     */
    public function canDoTransaction()
    {
        $status = self::STATUS_ACTIVE == $this->members['status'];
        $passChecks = $this->passRegulatoryChecks();
        $isVerified = $this->isVerified();
        return $status && $passChecks && $isVerified;
    }

    /**
     * Checks if the account holder has set a bank account for
     * disbursement / conversion
     *
     */
    public function hasBankAccount()
    {
        return 0 < $this->members['bankid'] &&
            0 < strlen($this->members['accountname']) &&
            0 < strlen($this->members['accountnumber']);
    }

    /**
     * Get the partner related to this account holder
     *
     * @return \Snap\object\Partner|null
     */
    public function getPartner()
    {
        $result = null;

        if (0 < $this->members['partnerid']) {
            $result = $this->getStore()
                ->getRelatedStore('partner')
                ->getById($this->members['partnerid']);
        }

        return $result;
    }

    /**
     * Retrieves the push tokens for this account holder
     *
     * @return array
     */
    public function getDevicePushTokens()
    {
        $now = new \DateTime();
        if (0 < $this->members['id']) {
            $tokens = $this->getStore()->getRelatedStore('mytoken')->searchTable()->select()
                ->where('accountholderid', $this->members['id'])
                ->andWhere('type', MyToken::TYPE_PUSH)
                ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                ->andWhere('status', MyToken::STATUS_ACTIVE)
                ->execute();
            return $tokens;
        }

        return [];
    }

    /**
     * Determine whether account holder has set up pin code
     *
     * @return boolean
     */
    public function hasPincode()
    {
        return 0 < strlen($this->members['pincode']);
    }
	
	public function getAdditionalData ()
	{
		return $this->getStore()
            ->getRelatedStore('additionaldata')
            ->searchTable()
            ->select()
            ->where('accountholderid', $this->members['id'])
            ->one();
	}

    /**
     * Get the stored address for this account holder
     *
     * @return MyAddress
     */
    public function getAddress()
    {
        return $this->getStore()
            ->getRelatedStore('myaddress')
            ->searchTable()
            ->select()
            ->where('accountholderid', $this->members['id'])
            ->one();
    }

    /**
     * Add address record for the account holder
     *
     * @param  string $line1
     * @param  string $line2
     * @param  string $city
     * @param  string $postcode
     * @param  string $state
     * @return MyAddress
     */
    public function addAddress($line1, $line2, $city, $postcode, $state, $mailingLine1 = null, $mailingLine2 = null, $mailingCity = null, $mailingPostcode = null, $mailingState = null)
    {
        $address = $this->getStore()->getRelatedStore('myaddress')->create([
            'line1'           => $line1,
            'line2'           => $line2,
            'city'            => $city,
            'postcode'        => $postcode,
            'state'           => $state,
            'mailingline1' => $mailingLine1,
            'mailingline2' => $mailingLine2,
            'mailingcity' => $mailingCity,
            'mailingpostcode' => $mailingPostcode,
            'mailingstate' => $mailingState,
            'accountholderid' => $this->members['id'],
            'status'          => MyAddress::STATUS_ACTIVE,
        ]);

        return $this->getStore()->getRelatedStore('myaddress')->save($address);
    }

    /**
     * Update address record for the account holder
     *
     * @param  string $line1
     * @param  string $line2
     * @param  string $city
     * @param  string $postcode
     * @param  string $state
     * @return MyAddress
     */
    public function updateAddress(MyAddress $address, $line1, $line2, $city, $postcode, $state, $mailingLine1 = null, $mailingLine2 = null, $mailingCity = null, $mailingPostcode = null, $mailingState = null)
    {
        $address->line1    = $line1;
        $address->line2    = $line2;
        $address->city     = $city;
        $address->postcode = $postcode;
        $address->state    = $state;
        $address->mailingline1 = $mailingLine1;
        $address->mailingline2 = $mailingLine2;
        $address->mailingcity = $mailingCity;
        $address->mailingpostcode = $mailingPostcode;
        $address->mailingstate = $mailingState;

        return $this->getStore()->getRelatedStore('myaddress')->save($address);
    }

    /**
     * Get the date of birth of the account holder from the mykadno
     *
     * @param string $format The data format for the date of birth
     * @return string
     */
    public function getDateOfBirth($format = 'Y-m-d')
    {
        $date = \DateTime::createFromFormat('Ymd', $this->getYearOfBirth() . substr($this->members['mykadno'], 2, 4));
        return $date->format($format);
    }

    /**
     * Get the year of birth of the account holder from the mykadno
     *
     * @param string $format The data format for the year of birth
     * @return string
     */
    public function getYearOfBirth()
    {
        $date = \DateTime::createFromFormat('ymd', substr($this->members['mykadno'], 0, 6));
        $year = $date->format('Y');

        if ($year > date('Y')) {
            $year = $year - 100;
        }

        return $year;
    }

    /**
     * Get the PEP declaration of this submission
     *
     * @return array|null
     */
    public function getPepDeclaration()
    {
        if (0 < strlen($this->members['pepdeclaration'])) {
            return json_decode($this->members['pepdeclaration']);
        }

        return null;
    }

    /**
     * Retrieves the current gold balance
     * 
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCurrentGoldBalance($until = null)
    {
        if (0 < $this->members['id']) {
            $ledgerStore = $this->getStore()->getRelatedStore('ledger');
            $ledgerHdl = $ledgerStore->searchTable(false);
            $p = $ledgerStore->getColumnPrefix();
            
            $ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("SUM({$p}credit) - SUM({$p}debit) AS sum")])
                                   ->where('accountholderid', $this->members['id'])
                                   ->where('status', MyLedger::STATUS_ACTIVE);
                                    // ->where('type', 'in',  [
                                    //         MyLedger::TYPE_BUY_FPX,
                                    //         MyLedger::TYPE_SELL,
                                    //         MyLedger::TYPE_STORAGE_FEE,
                                    //         MyLedger::TYPE_CONVERSION,
                                    //         MyLedger::TYPE_CONVERSION_FEE
                                    //     ]) // All gold transaction ledger types

            if ($until instanceof \DateTime) {
                $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
            }

            $balance = $ledgerHdl->one()['sum'];

            return floatval($balance);
        }

        return 0.00;
    }

     /**
     * Retrieves the buy sell gold balance
     * 
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getBuySellGoldBalance($until = null)
    {
        if (0 < $this->members['id']) {
            $ledgerStore = $this->getStore()->getRelatedStore('ledger');
            $ledgerHdl = $ledgerStore->searchTable(false);
            $p = $ledgerStore->getColumnPrefix();
            
            $ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("SUM({$p}credit) - SUM({$p}debit) AS sum")])
                                   ->where('accountholderid', $this->members['id'])
                                   ->andWhere('type', 'in',  [MyLedger::TYPE_BUY_FPX,MyLedger::TYPE_SELL]);
                                    // ->where('type', 'in',  [
                                    //         MyLedger::TYPE_BUY_FPX,
                                    //         MyLedger::TYPE_SELL,
                                    //         MyLedger::TYPE_STORAGE_FEE,
                                    //         MyLedger::TYPE_CONVERSION,
                                    //         MyLedger::TYPE_CONVERSION_FEE
                                    //     ]) // All gold transaction ledger types

            if ($until instanceof \DateTime) {
                $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
            }

            $balance = $ledgerHdl->one()['sum'];

            return floatval($balance);
        }

        return 0.00;
    }

    /**
     * Retrieves the customer sell price. currentprice * total_xau
     * By DK 20210908
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    // public function getCurrentCustomerGoldValue($pricestream,$goldbalance,$until = null)
    // {
    //     if (0 < $this->members['id']) {
    //         $latestPriceBuy = $pricestream->companybuyppg;
    //         $latestPriceSell = $pricestream->companysellppg;
    //         /*$ledgerStore = $this->getStore()->getRelatedStore('ledger');
    //         $ledgerHdl = $ledgerStore->searchView(false);
    //         $p = $ledgerStore->getColumnPrefix();

    //         $ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("SUM({$p}credit) AS sumxau")])
    //                                ->where('accountholderid', $this->members['id'])
    //                                ->andWhere('type', MyLedger::TYPE_BUY_FPX);
    //                                 // ->where('type', 'in',  [
    //                                 //         MyLedger::TYPE_BUY_FPX,
    //                                 //         MyLedger::TYPE_SELL,
    //                                 //         MyLedger::TYPE_STORAGE_FEE,
    //                                 //         MyLedger::TYPE_CONVERSION,
    //                                 //         MyLedger::TYPE_CONVERSION_FEE
    //                                 //     ]) // All gold transaction ledger types

    //         if ($until instanceof \DateTime) {
    //             $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
    //         }

    //         $purchasexau    = $ledgerHdl->one()['sumxau'];*/

    //         $calculateGoldValue = $latestPriceBuy*$goldbalance;
    //         $finalCalculation = number_format($calculateGoldValue,2, '.', '');

    //         return floatval($finalCalculation);
    //     }

    //     return 0.00;
    // }

    /**
     * Get total amount of profit
     * By DK 20210920
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    // public function getTotalOfCustomerProfit($avgCustPurchase,$until = null)
    // {
    //     if (0 < $this->members['id']) {
    //         $ledgerStore = $this->getStore()->getRelatedStore('ledger');
    //         $ledgerHdl = $ledgerStore->searchView(false);
    //         $p = $ledgerStore->getColumnPrefix();

    //         $ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("COALESCE(SUM({$p}amountout),0) AS sumsellamt,COALESCE(SUM({$p}amountin),0) AS sumbuyamt,COALESCE(SUM({$p}amountout-($avgCustPurchase*{$p}debit)),0) AS sumprofit")])
    //                                ->where('accountholderid', $this->members['id'])
    //                                ->andWhere('type', 'in',  [MyLedger::TYPE_BUY_FPX,MyLedger::TYPE_SELL]);

    //         if ($until instanceof \DateTime) {
    //             $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
    //         }

    //         $sumsellamt    = $ledgerHdl->one()['sumsellamt'];
    //         $sumbuyamt    = $ledgerHdl->one()['sumbuyamt'];
    //         $sumprofit    = $ledgerHdl->one()['sumprofit'];

    //         $updateProfitdecimal = number_format($profitCustomer,2, '.', '');

    //         $getTotalCostGold = $sumbuyamt - $sumsellamt + $sumprofit;
    //         $finalCalculation = number_format($getTotalCostGold,2, '.', '');

    //         return floatval($finalCalculation);
    //     }
    //     return 0.00;
    // }

    /**
     * Get avg cost price
     * By DK 20210920
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    // public function getAvgCostPrice($getTotalCostGoldBalance,$goldbalance,$until = null)
    // {
    //     if (0 < $this->members['id']) {
    //         $updateGoldBalanceWithDecimal = number_format($goldbalance,3, '.', '');
    //         /*check value should not be 0*/
    //         if($goldbalance != 0) $avgcostprice = $getTotalCostGoldBalance / $goldbalance;
    //         else $avgcostprice = 0;

    //         $finalCalculation = number_format($avgcostprice,2, '.', '');

    //         return floatval($finalCalculation);
    //     }
    //     return 0.00;
    // }

    /**
     * Current price - average cost price / average cost price
     * By DK 20210921
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    // public function getDiffWCurrPrice($avgCostprice,$pricestream,$until = null)
    // {
    //     if (0 < $this->members['id']) {
    //         $latestPriceBuy = $pricestream->companybuyppg;
    //         $latestPriceSell = $pricestream->companysellppg;
    //         /*calculate*/
    //         if($avgCostprice != 0 ) $calculate = ((($latestPriceBuy - $avgCostprice) / $avgCostprice))*100;
    //         else $calculate = 0;

    //         $finalCalculation['wDecimal'] = floatval(number_format($calculate,2, '.', ''));
    //         $finalCalculation['woDecimal'] =floatval(number_format($calculate,6, '.', ''));
            
    //         return $finalCalculation;
    //     }

    //     return 0.00;
    // }

    /**
     * Retrieves the customer total purchase
     * By DK 20210908
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCustTotalPurchase($until = null)
    {
        if (0 < $this->members['id']) {
            $ledgerStore = $this->getStore()->getRelatedStore('ledger');
            $ledgerHdl = $ledgerStore->searchView(false);
            $p = $ledgerStore->getColumnPrefix();

            $ledgerHdl = $ledgerHdl->select([$ledgerHdl->raw("SUM({$p}amountin) AS sumamount")])
                                   ->where('accountholderid', $this->members['id']);
                                    // ->where('type', 'in',  [
                                    //         MyLedger::TYPE_BUY_FPX,
                                    //         MyLedger::TYPE_SELL,
                                    //         MyLedger::TYPE_STORAGE_FEE,
                                    //         MyLedger::TYPE_CONVERSION,
                                    //         MyLedger::TYPE_CONVERSION_FEE
                                    //     ]) // All gold transaction ledger types

            if ($until instanceof \DateTime) {
                $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
            }
            $purchaseamount = $ledgerHdl->one()['sumamount'];

            $finalCalculation = number_format($purchaseamount,2, '.', '');

            return floatval($finalCalculation);
        }

        return 0.00;
    }

    /**
     * Current price - average purchase / average purchase
     * By DK 20210908
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCurrentCustomerSellPricePercentage($avgCustPurchase,$pricestream,$until = null)
    {
        if (0 < $this->members['id']) {
            $latestPriceBuy = $pricestream->companybuyppg;
            $latestPriceSell = $pricestream->companysellppg;
            /*calculate*/
            if($avgCustPurchase != 0 ) $calculate = (($latestPriceBuy - $avgCustPurchase) / $avgCustPurchase) / 100;
            else $calculate = 0;

            $finalCalculation = number_format($calculate,2, '.', '');
            
            return floatval($finalCalculation);
        }

        return 0.00;
    }

    /**
     * Retrieves the average customer purchase price
     * By DK 20210908
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCurrentAvgPurchase($until = null)
    {
        if (0 < $this->members['id']) {
            $ledgerStore = $this->getStore()->getRelatedStore('ledger');
            $ledgerHdl = $ledgerStore->searchView(false);
            $p = $ledgerStore->getColumnPrefix();

            $ledgerHdl = $ledgerHdl->select()
                                   ->where('accountholderid', $this->members['id']);

            if ($until instanceof \DateTime) {
                $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));  
            }
            $ledgerHdl->where('status', MyLedger::STATUS_ACTIVE);
            $queryAll = $ledgerHdl->execute();

            if(count($queryAll) > 0){
                foreach($queryAll as $aQuery){
                    if($aQuery[$p.'debit'] == 0 && $aQuery[$p.'credit'] == 0){ //ignore amountin or amountout when credit and debit both are 0;
                        $purchasexau += 0;
                        $purchaseamount += 0;
                    } else {
                        $purchasexau += $aQuery[$p.'credit'];
                        $purchaseamount += $aQuery[$p.'amountin'];
                    }
                }
            } else {
                $purchasexau = 0;
                $purchaseamount = 0;
            }

            $calculateAvg = $purchaseamount/$purchasexau;

            /*check if value is_nan*/
            $checkValue = (is_nan($calculateAvg)) ? 0.00 : $calculateAvg;
            return floatval($checkValue);
        }

        return 0.00;
    }

    /**
     * Retrieves the customer sell price. currentprice * total_xau
     * By DK 20210908
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCurrentCustomerGoldValue($pricestream,$goldbalance,$until = null)
    {
        if (0 < $this->members['id']) {
            $latestPriceBuy = $pricestream->companybuyppg;
            $latestPriceSell = $pricestream->companysellppg;

            $calculateGoldValue = $latestPriceBuy*$goldbalance;
            return floatval($calculateGoldValue);
        }

        return 0.00;
    }

    /**
     * Retrieves the gold currentprice
     * By DK 20220105
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getCurrentGoldPrice($pricestream,$until = null)
    {
        if (0 < $this->members['id']) {
            $latestPriceBuy = $pricestream->companybuyppg;
            $latestPriceSell = $pricestream->companysellppg;

            return floatval($latestPriceBuy);
        }

        return 0.00;
    }

    /**
     * Get total amount of profit
     * By DK 20210920
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getTotalOfCustomerProfit($avgCustPurchase,$until = null)
    {
        if (0 < $this->members['id']) {
            $ledgerStore = $this->getStore()->getRelatedStore('ledger');
            $ledgerHdl = $ledgerStore->searchView(false);
            $p = $ledgerStore->getColumnPrefix();

            $ledgerHdl = $ledgerHdl->select()
                                   ->where('accountholderid', $this->members['id']);

            if ($until instanceof \DateTime) {
                $ledgerHdl = $ledgerHdl->where('transactiondate', '<=', $until->format('Y-m-d H:i:s'));
                                
            }
            $ledgerHdl->where('status', MyLedger::STATUS_ACTIVE);
            $queryAll = $ledgerHdl->execute();

            if(count($queryAll) > 0){
                foreach($queryAll as $aQuery){
                    if($aQuery[$p.'debit'] == 0 && $aQuery[$p.'credit'] == 0){ //ignore amountin or amountout when credit and debit both are 0;
                        $sumsellamt += 0;
                        $sumbuyamt += 0;
                    } else {
                        $sumsellamt += $aQuery[$p.'amountout'];
                        $sumbuyamt += $aQuery[$p.'amountin'];

                        /*calculate profit*/
                        $timesAvgXau = $avgCustPurchase*$aQuery[$p.'debit'];
                        $calculateAll = $aQuery[$p.'amountout'] - $timesAvgXau;
                        $sumprofit += $calculateAll;
                    }
                }
            } else {
                $sumsellamt = 0;
                $sumbuyamt = 0;
                $sumprofit = 0;
            }

            $getTotalCostGold = $sumbuyamt - $sumsellamt + $sumprofit;
            return floatval($getTotalCostGold);
        }
        return 0.00;
    }

    /**
     * Get avg cost price
     * By DK 20210920
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getAvgCostPrice($getTotalCostGoldBalance,$goldbalance,$until = null)
    {
        if (0 < $this->members['id']) {
            $updateGoldBalanceWithDecimal = number_format($goldbalance,3, '.', '');
            /*check value should not be 0*/
            if($goldbalance != 0) $avgcostprice = $getTotalCostGoldBalance / $goldbalance;
            else $avgcostprice = 0;

            return floatval($avgcostprice);
        }
        return 0.00;
    }

    /**
     * Current price - average cost price / average cost price
     * By DK 20210921
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getDiffWCurrPrice($avgCostprice,$pricestream,$until = null)
    {
        if (0 < $this->members['id']) {
            $latestPriceBuy = $pricestream->companybuyppg;
            $latestPriceSell = $pricestream->companysellppg;
            /*calculate*/
            if($avgCostprice != 0 ) $calculate = ((($latestPriceBuy - $avgCostprice) / $avgCostprice))*100;
            else $calculate = 0;
            
            return floatval($calculate);
        }

        return 0.00;
    }

    /**
     * Current price - average cost price
     * By DK 20220105
     * @param \DateTime $until DateTime in user timezone 
     *
     * @return float
     */
    public function getDiffCurrentPrice($avgCostprice,$pricestream,$until = null)
    {
        if (0 < $this->members['id']) {
            $latestPriceBuy = $pricestream->companybuyppg;
            $latestPriceSell = $pricestream->companysellppg;
            /*calculate*/
            if($avgCostprice != 0 ) $calculate = $latestPriceBuy - $avgCostprice;
            else $calculate = 0;
            
            return floatval($calculate);
        }

        return 0.00;
    }

    /**
     * Retrive full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->members['fullname'];
    }

    /**
     * Retrieve occupation category
     * 
     * @return MyOccupationCategory|null
     */
    public function getOccupationCategory()
    {
        if ($this->members['occupationcategoryid']) {
            $obj = $this->getStore()->getRelatedStore('myoccupationcategory')->getById($this->members['occupationcategoryid']);
            $obj->language = $this->members['preferredlang'];

            return $obj;
        }

        return null;
    }

    /**
     * 
     * @return MyOccupationSubCategory|null
     */
    public function getOccupationSubCategory()
    {
        if ($this->members['occupationsubcategoryid']) {
            $obj = $this->getStore()->getRelatedStore('myoccupationsubcategory')->getById($this->members['occupationsubcategoryid']);
            $obj->language = $this->members['preferredlang'];

            return $obj;
        }

        return null;
    }

    /**
     * Check if account is active
     *
     * @return boolean
     */
    public function isActive()
    {
        return self::STATUS_ACTIVE == $this->members['status'];
    }

    /**
     * Check if account is suspended
     *
     * @return boolean
     */
    public function isSuspended()
    {
        return self::STATUS_SUSPENDED == $this->members['status'];
    }

    /**
     * Check if account is blacklisted
     *
     * @return boolean
     */
    public function isBlacklisted()
    {
        return self::STATUS_BLACKLISTED == $this->members['status'];
    }

    /**
     * Check if account is closed
     *
     * @return boolean
     */
    public function isClosed()
    {
        return self::STATUS_CLOSED == $this->members['status'];
    }
    
    /**
     * Get the string representation of the account type.
     *
     * @return string The string representation of the account type.
     */
    public function getAccountTypeString()
    {
        switch ($this->members['accounttype']) {
            case self::TYPE_SENDIRI:
                return gettext("SENDIRI");
                break;
            case self::TYPE_BERSAMA:
                return gettext("BERSAMA");
                break;
            case self::TYPE_ORGANIS:
                return gettext("ORGANIS");
                break;
            case self::TYPE_AMANAH:
                return gettext("AMANAH");
                break;
            case self::TYPE_UNKNOWN:
                return gettext("UNKNOWN");
                break;
            case self::TYPE_CASHLES:
                return gettext("CASHLES");
                break;
            case self::TYPE_CASHLNE:
                return gettext("CASHLNE");
                break;
			case self::TYPE_COMPANY:
                return gettext("COMPANY");
                break;
            case self::TYPE_COHEADING:
                return gettext("COHEADING");
                break;
            case self::TYPE_SOLEPROPRIETORSHIP:
                return gettext("SOLEPROPRIETORSHIP");
                break;
            case self::TYPE_INDIVIDUAL:
                return gettext("INDIVIDUAL");
                break;	
            default:
                return "";
                break;
        }
    }
}
