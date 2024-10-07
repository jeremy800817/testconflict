<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\object;

use Snap\InputException;

/**
 *
 * This class encapsulates the settings table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                         ID 
 * @property        string              $sapdgcode                  SAP dg code
 * @property        string              $sapmintedwhs               SAP minted whs code
 * @property        float               $mininitialxau              Min initial investment xau
 * @property        float               $minbalancexau              Min balance
 * @property        float               $mindisbursement            Min amount for disbursement
 * @property        boolean             $verifyachemail             Email verification
 * @property        boolean             $verifyachphone             Phone number verfication
 * @property        boolean             $verifyachpin               Pin verfication
 * @property        boolean             $achcancloseaccount         Allow account closure for account holder
 * @property        boolean             $skipekyc                   Skip ekyc for account holder
 * @property        boolean             $skipamla                   Skip amla for account holder
 * @property        boolean             $amlablacklistimmediately   Blacklist account holder when match found
 * @property        string              $ekycprovider               EKYC provider 
 * @property        string              $partnerpaymentprovider     Partner payment provider
 * @property        string              $companypaymentprovider     Ace payment provider
 * @property        string              $payoutprovider             Payout provider
 * @property        float               $transactionfee             Fee for transaction
 * @property        float               $walletfee                  Fee for wallet
 * @property        float               $payoutfee                  Fee for payout
 * @property        float               $courierfee                 Courier fee
 * @property        float               $storagefeeperannum         The percentage of storage fee per annum 
 * @property        float               $adminfeeperannum           The percentage of admin fee per annum 
 * @property        float               $minstoragecharge           The minimum storage fee in MYR
 * @property        float               $minstoragefeethreshold     The minimum xau amount for storage fee 
 * @property        float               $maxxauperdelivery          Maximum xau allowable per delivery 
 * @property        float               $maxpcsperdelivery          Maximum pieces allowable per delivery
 * @property        float               $dgpartnersellcommission    Partner sell commission per gram in MYR
 * @property        float               $dgpartnerbuycommission     Partner buy commission per gram in MYR
 * @property        float               $dgpeakpartnersellcommission Partner peak hour sell commission per gram in MYR
 * @property        float               $dgpeakpartnerbuycommission  Partner peak hour buy commission per gram in MYR
 * @property        DateTime            $dgpeakhourfrom             The peak hour start on daily
 * @property        DateTime            $dgpeakhourto               The peak hour end on daily
 * @property        float               $dgacesellcommission        Company sell commission per gram in MYR
 * @property        float               $dgacebuycommission         Company sell commission per gram in MYR
 * @property        float               $dgaffiliatesellcommission  Affiliate commission per gram in MYR
 * @property        float               $dgaffiliatebuycommission   Affiliate commission per gram in MYR
 * @property        int                 $pricealertvaliddays        Number of days the price alert valid
 * @property        boolean             $strictinventoryutilisation Strict inventory utilisation
 * @property        int                 $accesstokenlifetime         The lifetime of access token in minute
 * @property        int                 $refreshtokenlifetime        The lifetime of refresh token in minute
 * @property        int                 $enablepushnotification     To allow ( On/ Off ) setting for subscribed partners
 * @property        int                 $uniquenric                 If NRIC must be unique
 * @property        int                 $partnerid                  Partner ID
 * @property        int                 $status                     The status of this mapping
 * @property        DateTime            $createdon                  Time this record is created
 * @property        DateTime            $modifiedon                 Time this record is last modified
 * @property        int                 $createdby                  User ID
 * @property        int                 $modifiedby                 User ID
 *
 * @author  Azam
 * @version 1.0
 * @created 2021/04/16
 */
class MyPartnerSetting extends SnapObject
{
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
            'sapdgcode' => null,
            'sapmintedwhs' => null,
            'sapkilobarwhs' => null,
            'mininitialxau' => null,
            'minbalancexau' => null,
            'mindisbursement' => null,
            'verifyachemail' => null,
            'verifyachphone' => null,
            'verifyachpin' => null,
            'achcancloseaccount' => null,
            'skipekyc' => null,
            'skipamla' => null,
            'amlablacklistimmediately' => null,
            'ekycprovider' => null,
            'partnerpaymentprovider' => null,
            'companypaymentprovider' => null,
            'payoutprovider' => null,
            'transactionfee' => null,
            'walletfee' => null,
            'payoutfee' => null,
            'courierfee' => null,
            'storagefeeperannum' => null,
            'adminfeeperannum' => null,
            'minstoragecharge' => null,
            'minstoragefeethreshold' => null,
            'maxxauperdelivery' => null,
            'maxpcsperdelivery' => null,
            'dgpartnersellcommission' => null,
            'dgpartnerbuycommission' => null,
            'dgpeakpartnersellcommission' => null,
            'dgpeakpartnerbuycommission' => null,
            'dgpeakhourfrom' => null,
            'dgpeakhourto' => null,
            'dgacesellcommission' => null,
            'dgacebuycommission' => null,
            'dgaffiliatesellcommission' => null,
            'dgaffiliatebuycommission' => null,
            'pricealertvaliddays' => null,
            'strictinventoryutilisation' => null,
            'accesstokenlifetime' => null,
            'refreshtokenlifetime' => null,
            'enablepushnotification' => null,
            'uniquenric' => null,
            'sapitemcoderedeemfees' => null,
            'sapitemcodeannualfees' => null,
            'sapitemcodestoragefees' => null,
            'sapitemcodetransactionfees' => null,
            'partnerid' => null,
            'status' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
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
        $this->validateRequiredField($this->members['sapdgcode'], 'sapdgcode');
        $this->validateRequiredField($this->members['sapmintedwhs'], 'sapmintedwhs');
        $this->validateRequiredField($this->members['mininitialxau'], 'mininitialxau');
        $this->validateRequiredField($this->members['minbalancexau'], 'minbalancexau');
        $this->validateRequiredField($this->members['mindisbursement'], 'mindisbursement');
        $this->validateRequiredField($this->members['verifyachemail'], 'verifyachemail');
        $this->validateRequiredField($this->members['verifyachphone'], 'verifyachphone');
        $this->validateRequiredField($this->members['verifyachpin'], 'verifyachpin');
        $this->validateRequiredField($this->members['achcancloseaccount'], 'achcancloseaccount');
        $this->validateRequiredField($this->members['skipekyc'], 'skipekyc');
        $this->validateRequiredField($this->members['skipamla'], 'skipamla');
        $this->validateRequiredField($this->members['amlablacklistimmediately'], 'amlablacklistimmediately');
        // $this->validateRequiredField($this->members['ekycprovider'], 'ekycprovider');
        // $this->validateRequiredField($this->members['partnerpaymentprovider'], 'partnerpaymentprovider');
        // $this->validateRequiredField($this->members['companypaymentprovider'], 'companypaymentprovider');
        // $this->validateRequiredField($this->members['payoutprovider'], 'payoutprovider');
        $this->validateRequiredField($this->members['transactionfee'], 'transactionfee');
        $this->validateRequiredField($this->members['walletfee'], 'walletfee');
        $this->validateRequiredField($this->members['payoutfee'], 'payoutfee');
        $this->validateRequiredField($this->members['courierfee'], 'courierfee');
        $this->validateRequiredField($this->members['storagefeeperannum'], 'storagefeeperannum');
        $this->validateRequiredField($this->members['adminfeeperannum'], 'adminfeeperannum');
        $this->validateRequiredField($this->members['minstoragecharge'], 'minstoragecharge');
        $this->validateRequiredField($this->members['minstoragefeethreshold'], 'minstoragefeethreshold');
        $this->validateRequiredField($this->members['maxxauperdelivery'], 'maxxauperdelivery');
        $this->validateRequiredField($this->members['maxpcsperdelivery'], 'maxpcsperdelivery');
        // if (100.0 < $this->members['maxxauperdelivery']) {
        //     throw new \Snap\InputException("Maximum XAU per delivery cannot be below 100", InputException::FIELD_ERROR, 'maxxauperdelivery');
        // }
        $this->validateRequiredField($this->members['dgpartnersellcommission'], 'dgpartnersellcommission');
        $this->validateRequiredField($this->members['dgpartnerbuycommission'], 'dgpartnerbuycommission');
        $this->validateRequiredField($this->members['dgpeakpartnersellcommission'], 'dgpeakpartnersellcommission');
        $this->validateRequiredField($this->members['dgpeakpartnerbuycommission'], 'dgpeakpartnerbuycommission');
        $this->validateRequiredField($this->members['dgpeakhourfrom'], 'dgpeakhourfrom');
        $this->validateRequiredField($this->members['dgpeakhourto'], 'dgpeakhourto');
        // $this->validateRequiredField($this->members['dgacesellcommission'], 'dgacesellcommission');
        // $this->validateRequiredField($this->members['dgacebuycommission'], 'dgacebuycommission');
        $this->validateRequiredField($this->members['pricealertvaliddays'], 'pricealertvaliddays');
        $this->validateRequiredField($this->members['strictinventoryutilisation'], 'strictinventoryutilisation');
        $this->validateRequiredField($this->members['partnerid'], 'partnerid');
        // $this->validateRequiredField($this->members['accesstokenlifetime'], 'accesstokenlifetime');
        // $this->validateRequiredField($this->members['refreshtokenlifetime'], 'refreshtokenlifetime');
        $this->validateRequiredField($this->members['enablepushnotification'], 'enablepushnotification');
        $this->validateRequiredField($this->members['uniquenric'], 'uniquenric');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }
}
