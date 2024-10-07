<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use ClanCats\Hydrahon\Query\Expression;
use DateInterval;
use Exception;
use Snap\api\exception\AccountClosureNotAllowed;
use Snap\api\exception\AccountClosurePending;
use Snap\api\exception\MyGtpAccountClosureNotAllowed;
use Snap\api\exception\MyGtpAccountClosurePending;
use Snap\api\exception\CredentialInvalid;
use Snap\api\exception\EkycAlreadyPassed;
use Snap\api\exception\EkycPendingResult;
use Snap\api\exception\EkycSubmissionInvalid;
use Snap\api\exception\EmailAddressNotFound;
use Snap\IObservable;
use Snap\object\MyAccountHolder;
use Snap\TObservable;
use Snap\TLogging;
use Snap\api\exception\EmailAddressTakenException;
use Snap\api\exception\GeneralException;
use Snap\api\exception\InitialInvestmentNotMade;
use Snap\api\exception\IpMaximumRetries;
use Snap\api\exception\MyGtpAccountHolderMyKadExists;
use Snap\api\exception\MyGtpAccountHolderNoAccountNameOrNumber;
use Snap\api\exception\MyGtpAccountHolderNoPincode;
use Snap\api\exception\MyGtpAccountHolderNotExist;
use Snap\api\exception\MyGtpAccountHolderPincodeRequired;
use Snap\api\exception\MyGtpAccountHolderWrongPin;
use Snap\api\exception\MyGtpActionNotPermitted;
use Snap\api\exception\MyGtpInvalidQuestionnaireAnswer;
use Snap\api\exception\MyGtpPhoneNumberExists;
use Snap\api\exception\MyGtpPhoneNumberNotExist;
use Snap\api\exception\MyGtpProfileUpdateNotAllowed;
use Snap\api\exception\MyGtpVerificationCodeInvalid;
use Snap\api\exception\MyPartnerApiMissing;
use Snap\api\exception\PartnerBranchCodeInvalid;
use Snap\api\exception\MyGtpOccupationCategoryNotMatchSub;
use Snap\api\exception\MyGtpOccupationCategoryWithIllegalSub;
use Snap\api\exception\MyGtpPartnerUsernameExists;
use Snap\InputException;
use Snap\IObservation;
use Snap\object\MyPartnerApi;
use Snap\object\Partner;
use Snap\api\exception\ResetTokenInvalid;
use Snap\Common;
use Snap\IObserver;
use Snap\object\MyAccountClosure;
use Snap\object\MyBank;
use Snap\object\MyAddress;
use Snap\object\MyCloseReason;
use Snap\object\MyConversion;
use Snap\object\MyDailyStorageFee;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;
use Snap\object\MyGtpEventConfig;
use Snap\object\MyOccupationCategory;
use Snap\object\MyOccupationSubCategory;
use Snap\object\MyKYCOperatorLogs;
use Snap\object\MyKYCImage;
use Snap\object\MyKYCResult;
use Snap\object\MyKYCSubmission;
use Snap\object\MyLedger;
use Snap\object\MyLoanTransaction;
use Snap\object\MyMemberUpload;
use Snap\object\MyPartnerSetting;
use Snap\object\MyTransferGold;
use Snap\object\MyToken;
use Snap\object\Order;
use Snap\object\PriceStream;
use Snap\object\Product;
use Snap\api\casa\BaseCasa;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Spipu\Html2Pdf\Html2Pdf;
/**
 * This class handles account holder management
 *
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 07-Oct-2020
 */
class MyGtpAccountManager implements IObservable, IObserver
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        if ($changed instanceof MyGtpDisbursementManager && $state->target instanceof MyDisbursement) {

            $transactionRefNo = $state->target->transactionrefno;
            $disbursementStatus = $state->target->status;

            if ($state->isConfirmAction() && MyDisbursement::STATUS_COMPLETED == $disbursementStatus) {

                // Check if disbursement was for account closure
                $accountClosure = $this->app->myaccountclosureStore()->getByField('transactionrefno', $transactionRefNo);
                if ($accountClosure) {
                    $this->onApproveAccountClosure($accountClosure, MyGtpEventConfig::EVENT_CLOSE_ACCOUNT);
                }
            }
        }
    }

    public function getsendernamesenderemail($object){
        $projectBase = $this->app->getConfig()->{'projectBase'};
        $sendername = $this->app->getConfig()->{'snap.mailer.sendername'};
        $senderemail = $this->app->getConfig()->{'snap.mailer.senderemail'};
        $projectemail = 'noreply@ace2u.com'; //default
        if ($object->partnerid){
            $partner = $this->app->PartnerStore()->getById($object->partnerid);
            $projectBase = $partner->projectbase;
            $sendername = $partner->sendername;
            $senderemail = $partner->senderemail;
            $projectemail = $partner->projectemail;
        }
        return [
            'projectBase' => $projectBase,
            'sendername' => $sendername,
            'senderemail' => $senderemail,
            'projectemail' => $projectemail,
        ];
    }

    /**
     * Returns account summary for the account holder
     * @param AccountHolder $account    The target account holder
     *
     * @return array
     */
    public function getAccountHolderSummary(MyAccountHolder $accHolder)
    {
        $data = [];

        $address = $this->app->myaddressStore()->getByField('accountholderid', $accHolder->id);
        $partner = $accHolder->getPartner();
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        /*get 1st price of the day after 8:30*/
        $product  = $this->app->productStore()->getByField('code', 'DG-999-9');
        $provider = $this->app->priceproviderStore()->getForPartnerByProduct($partner, $product);
        $priceMgr = $this->app->priceManager();
        $firstDayPriceStream = $priceMgr->getFirstDaySpotPrice($provider);
        /**/

        $avgCustPurchase            = $accHolder->getCurrentAvgPurchase();
        $getTotalCostGoldBalance    = $accHolder->getTotalOfCustomerProfit($avgCustPurchase);
        $avgCostprice               = $accHolder->getAvgCostPrice($getTotalCostGoldBalance,$accHolder->getCurrentGoldBalance());
        $currGoldVal                = $accHolder->getCurrentCustomerGoldValue($firstDayPriceStream,$accHolder->getCurrentGoldBalance());
        $diffCurrPricePer           = $accHolder->getDiffWCurrPrice($avgCostprice,$firstDayPriceStream);
        $currentpricegold           = $accHolder->getCurrentGoldPrice($firstDayPriceStream);//get gold price @8:30am
        $diifcurrentprice           = $accHolder->getDiffCurrentPrice($avgCostprice,$firstDayPriceStream);//diff price in rm

        /* Get transfergold fields that have not been notified */
        $receivedGifts = $this->app->mytransfergoldStore()->searchTable()->select()
            ->where('toaccountholderid', $accHolder->id)
            ->andWhere('isnotifyrecipient', '<=', 0)
            ->execute();
            
        $data['gold_balance']               = $accHolder->getCurrentGoldBalance();
        $data['avgbuyprice']                = floatval(number_format($avgCustPurchase,2, '.', ''));
        $data['currentgoldvalue']           = floatval(number_format($currGoldVal,2, '.', ''));
        $data['totalcostgoldbalance']       = floatval(number_format($getTotalCostGoldBalance,2, '.', ''));
        $data['avgcostprice']               = floatval(number_format($avgCostprice,2, '.', ''));
        $data['diffcurrentpriceprcetage']   = floatval(number_format($diffCurrPricePer,2, '.', ''));
        $data['currentpricegold']           = floatval(number_format($currentpricegold,2, '.', ''));
        $data['diifcurrentprice']           = floatval(number_format($diifcurrentprice,2, '.', ''));
        $data['firstpriceofdayid']          = $firstDayPriceStream->id;

        $data['storage_fees'] = floatval($partner->calculator(false)->round($this->getAccountHolderUnchargedStorageFees($accHolder)));
        $data['available_balance'] = floatval($partner->calculator(false)->sub($partner->calculator(false)->sub($data['gold_balance'], $data['storage_fees']), $settings->minbalancexau));
        $data['initial_investment_status'] = $accHolder->initialInvestmentMade();
        $data['user_status']  = $accHolder->getStatusString();
        $data['user_status_code']  = intval($accHolder->status);
        if ($settings->verifyachemail) {
            $data['email_verified']  = null != $accHolder->emailverifiedon;
        }
        if ($settings->verifyachphone) {
            $data['phone_number_verified']  = null != $accHolder->phoneverifiedon;
        }
        $data['profile']['email'] = $accHolder->email;
        $data['profile']['full_name'] = $accHolder->fullname;
        $data['profile']['accountcode'] = $accHolder->accountholdercode;
        $data['profile']['mykad_number'] = $accHolder->mykadno;
        $data['profile']['phone_number'] = $accHolder->phoneno;
        $data['profile']['preferred_lang'] = $accHolder->preferredlang;
        $data['profile']['occupation_category_id'] = intval($accHolder->occupationcategoryid);
        $data['profile']['occupation_subcategory_id'] = intval($accHolder->occupationsubcategoryid);
        $data['profile']['referral_branch_code'] = $accHolder->referralbranchcode;
        $data['profile']['referral_salesperson_code'] = $accHolder->referralsalespersoncode;

        if ($address) {
            $data['profile']['address_line_1'] = $address->line1;
            $data['profile']['address_line_2'] = $address->line2;
            $data['profile']['postcode'] = $address->postcode;
            $data['profile']['city'] = $address->city;
            $data['profile']['state'] = $address->state;
        } else {
            $data['profile']['address_line_1'] = null;
            $data['profile']['address_line_2'] = null;
            $data['profile']['postcode'] = null;
            $data['profile']['city'] = null;
            $data['profile']['state'] = null;
        }

        if ($accHolder->bankid) {
            $data['profile']['bank_id'] = intval($accHolder->bankid);
            $data['profile']['bank_acc_name'] = $accHolder->accountname;
            $data['profile']['bank_acc_number'] = $accHolder->accountnumber;
        } else {
            $data['profile']['bank_id'] = null;
            $data['profile']['bank_acc_name'] = null;
            $data['profile']['bank_acc_number'] = null;
        }

        $data['profile']['nok_full_name'] = $accHolder->nokfullname;
        // $data['profile']['nok_mykad_number'] = $accHolder->nokmykadno;
        $data['profile']['nok_phone'] = $accHolder->nokphoneno;
        $data['profile']['nok_email'] = $accHolder->nokemail;
        $data['profile']['nok_address'] = $accHolder->nokaddress;
        $data['profile']['nok_relationship'] = $accHolder->nokrelationship;

        $data['profile']['partner_customer_id']   = 0 < strlen($accHolder->partnercusid) ? $accHolder->partnercusid : null;
        $data['profile']['partner_customer_data'] = 0 < strlen($accHolder->partnercusdata) ? $accHolder->partnercusdata : null;

        /* Compile records if there are gifts */
        if($receivedGifts){
            /* Set and toggle notification for the gift */
            foreach ($receivedGifts as $gift) {
                $data['received_gifts'][$gift->id]['xau'] = $gift->xau;
                $data['received_gifts'][$gift->id]['price'] = $gift->price;
                $data['received_gifts'][$gift->id]['amount'] = $gift->amount;
                $data['received_gifts'][$gift->id]['createdon'] = $gift->createdon;
                $data['received_gifts'][$gift->id]['message'] = $gift->message;

                // Get sender name
                $fromAccountHolder = $this->app->myaccountholderStore()->getById($gift->fromaccountholderid);
                $data['received_gifts'][$gift->id]['sendername'] = $fromAccountHolder->fullname;
                $data['received_gifts'][$gift->id]['refno'] = $gift->refno;
                // $gift->status = MyToken::STATUS_INACTIVE;
                // Update status active
                $gift->isnotifyrecipient = MyTransferGold::STATUS_ACTIVE;
                $this->app->mytransfergoldStore()->save($gift);
            }
        }
        
        $data['pin_set'] = 0 < strlen($accHolder->pincode);
        return $data;
    }

    public function getAccountHolderEkycProgress(MyAccountHolder $accHolder)
    {
        $data = [];
        $data['status'] = $accHolder->getEkycStatusString();
        $data['status_code'] = intval($accHolder->kycstatus);

        $latestSubmission = $this->app->mykycsubmissionStore()->searchTable()->select()
            ->where('accountholderid', $accHolder->id)
            ->orderBy('id', 'DESC')
            ->one();

        if ($latestSubmission) {
            $data['mykad_front_submitted'] = 0 < strlen($latestSubmission->mykadfrontimageid);
            $data['mykad_back_submitted'] = 0 < strlen($latestSubmission->mykadbackimageid);
            $data['face_image_submitted'] = 0 < strlen($latestSubmission->faceimageid);
        } else {
            $data['mykad_front_submitted'] = false;
            $data['mykad_back_submitted'] = false;
            $data['face_image_submitted'] = false;
        }

        return $data;
    }

    public function getAccountHolderUnchargedStorageFees(MyAccountHolder $accHolder)
    {
        $app = $this->app;
        $now = new \DateTime('now');
        $now->setTimezone($app->getUserTimezone());
        $monthStart = new \DateTime($now->format('Y-m-01 00:00:00'), $app->getUserTimezone());
        $monthEnd = clone $monthStart;
        $monthEnd->add(DateInterval::createFromDateString("1 month"));

        $monthStart->setTimezone($app->getServerTimezone());
        $monthEnd->setTimezone($app->getServerTimezone());

        $dailyStorage = $this->app->mydailystoragefeeStore()->searchTable()->select()
            ->where('accountholderid', $accHolder->id)
            ->andWhere('calculatedon', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->andWhere('calculatedon', '<', $monthEnd->format('Y-m-d H:i:s'))
            ->andWhere('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->sum('xau');

        $monthyStorage = $this->app->mymonthlystoragefeeStore()->searchTable()->select()
            ->where('accountholderid', $accHolder->id)
            ->andWhere('chargedon', '>=', $monthStart->format('Y-m-d H:i:s'))
            ->andWhere('chargedon', '<', $monthEnd->format('Y-m-d H:i:s'))
            ->andWhere('status', MyDailyStorageFee::STATUS_ACTIVE)
            ->exists();

        // Account holder already charged
        if ($monthyStorage) {
            return 0;
        }

        return $dailyStorage ?? 0;
    }

    /****** e-KYC *****/

    public function processEKycVerification(MyAccountHolder $accHolder, Partner $partner, $submissionData = [])
    {
        // Updated by Cheok on 2020-12-21 for allow EKYC onboarding before initial transaction 

        // Check if user done initial investment
        // if (!$accHolder->initialInvestmentMade()) {
        //     throw InitialInvestmentNotMade::fromTransaction([]);
        // }       
        // End update by Cheok


        // Check if user already done e-kyc or is still waiting for results
        if ($accHolder->ekycPassed()) {
            throw EkycAlreadyPassed::fromTransaction([]);
        } else if ($accHolder->ekycPending()) {
            throw EkycPendingResult::fromTransaction([]);
        }

        try {
            if (!$this->app->getDBHandle()->inTransaction()) {
                $this->app->getDBHandle()->beginTransaction();
                $startedTransaction = true;
            }

            // Reset account holder's EKYC status
            $accHolder->kycstatus = MyAccountHolder::KYC_INCOMPLETE;

            $accHolder->ispep = $submissionData['is_pep'] ? 1 : 0;
            if ($accHolder->ispep) {              
                $this->validatePepQuestionnaire($submissionData['questionnaire']);                
                $accHolder->pepdeclaration = json_encode($submissionData['questionnaire']);
            } else {
                $accHolder->pepdeclaration = null;
            }
            
            $accHolder = $this->app->myaccountholderStore()->save($accHolder);

            // Create or update a submission record
            $provider = $this->getPartnerEkycProvider($partner);
            $submission = $provider->createSubmission($this->app, $accHolder, $submissionData);

            if (!$submission) {
                $this->logDebug("Did not receive a submission record from " . get_class($provider) . " provider");
                throw EkycSubmissionInvalid::fromTransaction([]);
            }

            if ($provider->canSubmitToProvider($submission)) {
                $accHolder->kycstatus = MyAccountHolder::KYC_PENDING;
                $accHolder = $this->app->myaccountholderStore()->save($accHolder, ['kycstatus']);
            }
            // Submission of MyEkycSubmission object done through EkycSubmissionJob

            if ($startedTransaction) {
                $this->app->getDBHandle()->commit();
            }

            return;
        } catch (\Exception $e) {
            if ($startedTransaction) {
                $this->app->getDBHandle()->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Method to submit the submission to the e-KYC provider
     */
    public function processEkycPendingSubmission(MyKYCSubmission $submission)
    {
        $stateMachine = $this->getKycSubmissionStateMachine($submission);
        if (!$stateMachine->can(MyKYCSubmission::STATUS_PENDING_RESULT)) {
            // Skip if this submission is not pending for submission.
            $this->logDebug("EKYC Submission id {$submission->id} not pending for submission.");
            return;
        }

        $accHolder = $this->app->myaccountholderStore()->getById($submission->accountholderid);
        $provider = $this->getPartnerEkycProvider($accHolder->partnerid);

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);
                            
        try {

            // Get the result from EKYC provider
            $submission = $provider->submitSubmission($this->app, $submission);
            if (!$submission || MyKYCSubmission::STATUS_PENDING_RESULT != $submission->status) {
                $message = "Submission {$submission->id} was unable to be submitted.";
                $this->log(__CLASS__ . ": " . $message, SNAP_LOG_INFO);
                throw GeneralException::fromTransaction([], ['message' => $message]);
            }

            // Since we are already saving the status in submitSubmission
            // If result was successfully submitted mark as pending result.
            // if (MyKYCSubmission::STATUS_PENDING_RESULT != $submission->status) {
            //     $submission->status = MyKYCSubmission::STATUS_PENDING_RESULT;
            //     $submission->submittedon = new \DateTime();
            //     $submission = $this->app->mykycsubmissionStore()->save($submission);
            // }

        } catch (\Snap\api\exception\ProviderApiError $e) {
            $partnerinfo = $this->getsendernamesenderemail($accHolder);
            // Error  related to api call or credentials
            $observation = new \Snap\IObservation(
                $submission,
                \Snap\IObservation::ACTION_OTHER,
                MyKYCSubmission::STATUS_PENDING_SUBMISSION,
                [
                    'errormessage' => $e->getMessage(),
                    'event' => MyGtpEventConfig::EVENT_EKYC_PROVIDER_ERROR,
                    'projectbase' => $partnerinfo['projectBase'],
                    'provider' => basename(str_replace('\\', '/', get_class($provider))),
                    'receiver' => $this->app->getConfig()->{'mygtp.admin.email'},
                    'sendername'            => $partnerinfo['sendername'],
                    'senderemail'           => $partnerinfo['senderemail'],
                ]
            );

            $this->notify($observation);
        } catch (\Exception $e) {
            $partnerinfo = $this->getsendernamesenderemail($accHolder);
            // Reject as submitted data failed verification
            $accHolder = $this->app->myaccountholderStore()->getById($submission->accountholderid);
            $accHolder->kycstatus = MyAccountHolder::KYC_FAILED;
            $accHolder = $this->app->myaccountholderStore()->save($accHolder, ['kycstatus']);

            // Init receivers
            // Send to ace
            // $receiverEmail = $partnerinfo['projectemail'];
            $receiverEmailOperator = 'nurul.nadirah@ace2u.com';
            $receiverEmailOperator .= ',' . 'ezzah.nabila@ace2u.com';
            // Bcc Emails
            $bccEmail = 'rebeccaho@silverstream.my';
            $bccEmail .= ',' . 'nazri@silverstream.my';

            $receiverEmailUser = $accHolder->email;
            // Send to accholder
            // $receiverEmail .= ',' . $accHolder->email;
            $observation_1 = new \Snap\IObservation(
                $submission,
                \Snap\IObservation::ACTION_REJECT,
                MyKYCSubmission::STATUS_PENDING_SUBMISSION,
                [
                    'event' => MyGtpEventConfig::EVENT_EKYC_VERIFICATION_FAILED,
                    'projectbase' => $partnerinfo['projectBase'],
                    'accountholderid'   => $accHolder->id,
                    'name' => $accHolder->fullname,
                    'receiver' => $receiverEmailUser,
                    'reason' => $submission->remarks,
                    'sendername'            => $partnerinfo['sendername'],
                    'senderemail'           => $partnerinfo['senderemail'],
                ]
            );
            $observation_2 = new \Snap\IObservation(
                $submission,
                \Snap\IObservation::ACTION_OPERATORREJECT,
                MyKYCSubmission::STATUS_PENDING_SUBMISSION,
                [
                    'event' => MyGtpEventConfig::EVENT_EKYC_VERIFICATION_FAILED,
                    'projectbase' => $partnerinfo['projectBase'],
                    'accountholderid'   => $accHolder->id,
                    'name' => $accHolder->fullname,
                    'nric' => $accHolder->mykadno,
                    'email' => $accHolder->email,
                    'contactno' => $accHolder->phoneno,
                    'accountcode' => $accHolder->accountholdercode,
                    'receiver' => $receiverEmailOperator,
                    'bccemail' => $bccEmail,
                    'reason' => $submission->remarks,
                    'sendername'            => $partnerinfo['sendername'],
                    'senderemail'           => $partnerinfo['senderemail'],
                ]
            );

            $this->notify($observation_1);
            $this->notify($observation_2);
            return;
        }
    }

    /**
     * Method to process pending submission
     */
    public function processEkycPendingResult(MyKYCSubmission $submission)
    {
        $stateMachine = $this->getKycSubmissionStateMachine($submission);
        if (!$stateMachine->can(MyKYCSubmission::STATUS_COMPLETE)) {
            // Skip if this submission is not pending for result.
            $this->logDebug("EKYC Submission id {$submission->id} not pending for result.");
            return;
        }

        $accHolder = $this->app->myaccountholderStore()->getById($submission->accountholderid);
        $provider = $this->getPartnerEkycProvider($accHolder->partnerid);

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);
        
        try {

            // Get the result from EKYC provider
            $result = $provider->getResult($this->app, $submission);
            if (!$result) {
                $message = "No results were able to be obtained for submission {$submission->id}.";
                $this->log($message, SNAP_LOG_INFO);
                throw GeneralException::fromTransaction([], ['message' => $message]);
            }

            // Process the result (Set accountholder status etc)
            $this->processKycResult($accHolder, $result);

            // If result was successfully processed, mark submission as complete
            if (MyKYCSubmission::STATUS_COMPLETE != $submission->status) {
                $submission->status = MyKYCSubmission::STATUS_COMPLETE;
                $submission = $this->app->mykycsubmissionStore()->save($submission, ['status']);
            }

            // AMLA Check next
            $accHolder = $this->app->myaccountHolderStore()->getById($accHolder->id);
            if ($accHolder->ekycPassed() && $accHolder->amlaPending()) {

                $immediatelyBlacklist = filter_var($settings->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN);

                // Send event to process AMLA
                $this->app->startCLIJob("AmlaCheckJob.php", ['accountholderid' => $accHolder->id, 'immediatelyblacklist' => $immediatelyBlacklist]);
                // $this->notify(new IObservation($accHolder, IObservation::ACTION_OTHER, $oldStatus, ['event' => MyGtpEventConfig::EVENT_AMLA_CHECK]));
            }
        } catch (\Snap\api\exception\ProviderApiError $e) {
            $partnerinfo = $this->getsendernamesenderemail($accHolder);
            $this->log(__METHOD__ . ": Error on processing result. Message: " . $e->getMessage(), SNAP_LOG_ERROR);

            // Email admin
            $observation = new \Snap\IObservation(
                $submission,
                \Snap\IObservation::ACTION_OTHER,
                MyKYCSubmission::STATUS_PENDING_RESULT,
                [
                    'errormessage' => $e->getMessage(),
                    'event' => MyGtpEventConfig::EVENT_EKYC_PROVIDER_ERROR,
                    'projectbase' => $partnerinfo['projectBase'],
                    'provider' => basename(str_replace('\\', '/', get_class($provider))),
                    'receiver' => $this->app->getConfig()->{'mygtp.admin.email'},
                    'reason' => $submission->remarks,
                    'sendername'            => $sendername,
                    'senderemail'           => $senderemail,
                ]
            );

            $this->notify($observation);

            return;
        } catch (\Exception $e) {

            $this->log(__METHOD__ . ": Error on processing result. Message: " . $e->getMessage(), SNAP_LOG_ERROR);
            return;
        }
    }

    /**
     * Method to process the submission results
     * @param MyAccountHolder $accountHolder    The account holder
     * @param mixed           $result     The result returned by the e-kyc provider
     * @return MyAccountHolder
     */
    public function processKycResult($accountHolder, $result)
    {
        $accountHolder = $this->app->myaccountHolderStore()->getById($accountHolder->id);
        switch ($result->result) {
            case MyKYCResult::RESULT_PASSED:
                $accountHolder->kycstatus = MyAccountHolder::KYC_PASSED;

                // For passed we first check for amla only then we notify
                break;
            case MyKycResult::RESULT_CAUTIOUS:
            case MyKycResult::RESULT_FAILED:
                $accountHolder->kycstatus = MyAccountHolder::KYC_FAILED;

                // Init receivers
                $partnerinfo = $this->getsendernamesenderemail($accountHolder);
                // Send to ace
                $receiverEmail = $partnerinfo['projectemail'];
                // Send to accholder
                // $receiverEmail .= ',' . $accountHolder->email;
                    
                $observation = new \Snap\IObservation(
                    $accountHolder,
                    \Snap\IObservation::ACTION_REJECT,
                    MyAccountHolder::KYC_INCOMPLETE,
                    [
                        'event' => MyGtpEventConfig::EVENT_EKYC_RESULT_FAILED,
                        'projectbase' => $this->app->getConfig()->{'projectBase'},
                        'accountholderid'   => $accountHolder->id,
                        'name' => $accountHolder->fullname,
                        'receiver' => $receiverEmail
                    ]
                );

                break;
            default:
                $accountHolder->kycstatus = MyAccountHolder::KYC_INCOMPLETE;

                $observation = new \Snap\IObservation(
                    $accountHolder,
                    \Snap\IObservation::ACTION_REJECT,
                    MyAccountHolder::KYC_INCOMPLETE,
                    [
                        'event' => MyGtpEventConfig::EVENT_EKYC_RESULT_FAILED,
                        'projectbase' => $partnerinfo['projectBase'],
                        'accountholderid'   => $accountHolder->id,
                        'name' => $accountHolder->fullname,
                        'receiver' => $accountHolder->email,
                        'sendername'            => $sendername,
                        'senderemail'           => $senderemail,
                    ]
                );

                break;
        }

        $accountHolder = $this->app->myaccountHolderStore()->save($accountHolder, ['kycstatus']);
        if ($observation) {
            $this->notify($observation);
        }
        return $accountHolder;
    }

    public function getKycReminderList($partnerid){
        if (!$partnerid){
            return false;
        }
        $list = $this->app->myaccountholderStore()->searchTable()->select()
            ->where('kycstatus', 'IN', [MyAccountHolder::KYC_FAILED, MyAccountHolder::KYC_INCOMPLETE])
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->andWhere('partnerid', $partnerid)
            ->execute();
        return $list;
    }

    /**
     * Method to process the submission results
     * @param MyAccountHolder $accountHolder    The account holder
     * @param mixed           $result     The result returned by the e-kyc provider
     * @return MyAccountHolder
     */
    public function processKycReminder($accountHolder, $partner)
    {
        
        // if ($accountHolder->kycstatus != MyAccountHolder::KYC_INCOMPLETE || $accountHolder->kycstatus != MyAccountHolder::KYC_FAILED){
        //     return false;
        // }


        // $observation = new \Snap\IObservation(
        //     $accountHolder,
        //     \Snap\IObservation::ACTION_REJECT,
        //     MyAccountHolder::KYC_INCOMPLETE,
        //     [
        //         'event' => MyGtpEventConfig::EVENT_EKYC_RESULT_FAILED,
        //         'projectbase' => $partnerinfo['projectBase'],
        //         'accountholderid'   => $accountHolder->id,
        //         'name' => $accountHolder->fullname,
        //         'receiver' => $accountHolder->email,
        //         'sendername'            => $sendername,
        //         'senderemail'           => $senderemail,
        //     ]
        // );

        // if ($observation) {
        //     $this->notify($observation);
        // }

        $receiver = $accountHolder->email;
        $getSenderEmail = $partner->senderemail;
        $getSenderName = $partner->sendername;

        $mailer = $this->app->getMailer();
     
        $mailer->addAddress($receiver);

        $mailer->isHtml(true);
        $email = $getSenderEmail;
        $name = $getSenderName;
        if (0 < strlen($email) && 0 < strlen($name)) {
            $mailer->setFrom($email, $name);
        }
        
        $mailer->Subject = 'EasiGold e-KYC Reminder';
        $mailer->Body    = $this->getKycReminderEmail($accountHolder);

        if ($mailer->send()){
            $keyreminder = $this->app->mykycreminderStore()->create([
                'senton' => new \Datetime(),
                'accountholderid' => $accountHolder->id,
            ]);
            $this->app->mykycreminderStore()->save($keyreminder);
        }

        return true;
    }

    public function getKycReminderEmail($data){
        $html = '

        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <style>body,table,td{font-family:Helvetica,Arial,sans-serif!important}.ExternalClass{width:100%}.ExternalClass,.ExternalClass div,.ExternalClass font,.ExternalClass p,.ExternalClass span,.ExternalClass td{line-height:150%}a{text-decoration:none}*{color:inherit}#MessageViewBody a,a[x-apple-data-detectors],u+#body a{color:inherit;text-decoration:none;font-size:inherit;font-family:inherit;font-weight:inherit;line-height:inherit}img{-ms-interpolation-mode:bicubic}table:not([class^=s-]){font-family:Helvetica,Arial,sans-serif;mso-table-lspace:0;mso-table-rspace:0;border-spacing:0;border-collapse:collapse}table:not([class^=s-]) td{border-spacing:0;border-collapse:collapse}@media screen and (max-width:600px){.w-full,.w-full>tbody>tr>td{width:100%!important}[class*=s-lg-]>tbody>tr>td{font-size:0!important;line-height:0!important;height:0!important}.s-5>tbody>tr>td{font-size:20px!important;line-height:20px!important;height:20px!important}}</style>

            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="outline:0;width:100%;min-width:100%;height:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;font-family:Helvetica,Arial,sans-serif;line-height:24px;font-weight:400;font-size:16px;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box;color:#000;margin:0;padding:0;border:0" class="bg-light body" bgcolor="#f7fafc" valign="top"><tbody><tr><td align="left" style="line-height:24px;font-size:16px;margin:0" bgcolor="#f7fafc" valign="top"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="container"><tbody><tr><td align="center" style="line-height:24px;font-size:16px;margin:0;padding:0 16px"><!--[if (gte mso 9)|(IE)]><table align=center role=presentation><tr><td width=600><![endif]--><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;max-width:600px;margin:0 auto" align="center"><tbody><tr><td align="left" style="line-height:24px;font-size:16px;margin:0"><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="s-5 w-full" width="100%"><tbody><tr><td align="left" style="line-height:20px;font-size:20px;width:100%;height:20px;margin:0" height="20" width="100%"></td></tr></tbody></table><table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-radius:6px;border-collapse:separate!important;width:100%;overflow:hidden;box-shadow:rgba(50,50,93,.25) 0 6px 12px -2px,rgba(0,0,0,.3) 0 3px 7px -3px;border:1px solid #e2e8f0" class="card" bgcolor="#ffffff"><tbody><tr><td align="left" style="line-height:24px;font-size:16px;width:100%;margin:0" bgcolor="#ffffff">
                <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="card-body">
                    <tbody><tr>
                        <td align="left" style="line-height:24px;font-size:16px;width:100%;margin:0;padding:20px">
                            <img alt="KYC Failed" class="img-fluid" src="https://gtp2.ace2u.com/src/resources/images/EasiGold/notification_kyc.png" style="height:auto;line-height:100%;outline:0;text-decoration:none;display:block;max-width:100%;width:100%;border:0 none" width="100%"><br>
                            <h3 align="left" style="padding-top:0;padding-bottom:0;font-weight:500;vertical-align:baseline;font-size:20px;line-height:33.6px;margin:0">Dear '.$data->fullname.',</h3><br>

                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">We thank you for signing up for our EasiGold digital account. We noticed that you have yet to complete your e-KYC submission, which is required before you are able to perform your first trade. This procedure is regulated by Bank Negara Malaysia.</p><br>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">This serves as a friendly reminder for you to complete your submission, if you are not familiar with e-KYC submission.</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Should you need any clarification or assistance with regards to e-KYC submission please do not hesitate to reach us at helpdesk@easigold2u.com or +6016 607 7686</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Please ignore this if you have fully submitted your e-KYC details.</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Thank you. </p>
                            <br><br>

                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Kami mengucapkan terima kasih kerana mendaftar untuk akaun digital EasiGold kami. Kami mendapati anda masih belum melengkapkan penyerahan e-KYC anda, yang diperlukan sebelum anda dapat melakukan perdagangan pertama anda. Prosedur ini dikawal oleh Bank Negara Malaysia.</p><br>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Email ini sebagai peringatan mesra untuk anda melengkapkan penyerahan anda, jika anda tidak biasa dengan penyerahan e-KYC.</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Sekiranya anda memerlukan sebarang penjelasan atau bantuan berkaitan penyerahan e-KYC, sila jangan teragak-agak untuk menghubungi kami di helpdesk@easigold2u.com atau +6016 607 7686</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Sila abaikan, jika anda telah menyerahkan sepenuhnya butiran e-KYC anda.</p>
                            <br>
                            <p align="left" style="line-height:24px;font-size:16px;width:100%;margin:0">Terima kasih. </p>

                        </td>
                    </tr>
                </tbody></table>
            </td></tr></tbody></table>
            <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%" class="s-5 w-full" width="100%"><tbody><tr><td align="left" style="line-height:20px;font-size:20px;width:100%;height:20px;margin:0" height="20" width="100%"></td></tr></tbody></table>
            <div style="align-content:center;font-family:Arial,Helvetica,sans-serif;color:#756f6f7c;font-size:11px" align="center" class="footer"><span>Copyright &copy; '.date("Y").' EasiGold, All Rights Reserved</span><br><span>You are receiving this email because you have opted in at our website</span></div></td></tr></tbody></table><!--[if (gte mso 9)|(IE)]><![endif]--></td></tr></tbody></table></td></tr></tbody></table>


        ';

        return $html;
    }

    /**
     * Returns an instance of eKYC provider for a partner
     *
     * @param Partner|string           $partner
     *
     * @return IEkycProvider
     */
    public function getPartnerEkycProvider($partner)
    {
        if (!$partner instanceof Partner) {
            $partner = $this->app->partnerStore()->getById($partner);
        }

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if (!$settings->ekycprovider) {
            throw MyPartnerApiMissing::fromTransaction([], ['type' => MyPartnerApi::TYPE_EKYC, 'code' => $partner->code]);
        }

        $provider = new $settings->ekycprovider;
        return $provider;
    }

    /**
     * Register account with the given data
     *
     * @param   Partner $partner
     * @param   string  $fullName
     * @param   string  $myKadNo
     * @param   string  $phoneNo
     * @param   MyOccupationCategory     $occupationCategory
     * @param   MyOccupationSubCategory  $occupationSubCategory
     * @param   string  $email
     * @param   string  $password
     * @param   string  $preferredLang
     * @param   string  $referralBranchCode
     * @param   string  $nokFullName
     * @param   string|null  $nokMyKadNo
     * @param   string  $nokPhoneNo,
     * @param   string  $nokEmail,
     * @param   string  $nokAddress,
     * @param   string  $nokRelationship
     * @param   string|null $phoneVerificationCode
     * @param   string|null $referralSalespersonCode
     * @param   bool $skipVerification
     * @param   string $partnerCusId
     * @param   string $partnerData
     * @return  MyAccountHolder|null
     */
    public function register(
        Partner $partner,
        string  $fullName,
        string  $myKadNo,
        string  $phoneNo,
        MyOccupationCategory  $occupationCategory,
        MyOccupationSubCategory  $occupationSubCategory = null,
        string  $email,
        string  $password,
        ?string  $preferredLang,
        ?string  $referralBranchCode,
        string  $nokFullName,
        $nokMyKadNo = '',
        $nokPhoneNo,
        $nokEmail,
        $nokAddress,
        $nokRelationship,
        $phoneVerificationCode = '',
        $referralSalespersonCode = '',
        bool $skipVerification = false,
        $partnerCusId = '',
        $partnerData = ''
    ): ?MyAccountHolder {

        // 2022-09-14- redone skip phone, email, ic checking partnerid 3975223
        $redone_partnerid = $this->app->getConfig()->{'gtp.red.partner.id'}; 
        $bsn_partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'}; 
        $usePartnerGroupId = false;
        $listOfPartners = [];
        if (!empty($redone_partnerid)) {
            array_push($listOfPartners, $redone_partnerid);
        }
        if (!empty($bsn_partnerid)) {
            array_push($listOfPartners, $bsn_partnerid);
            // grab bsn partners
            if ($partner->group == $bsn_partnerid){
                #do unique check
                $usePartnerGroupId = true;
            }
        }

        // if (!$redone_partnerid || intval($redone_partnerid) <= 0){
        //     $redone_partnerid = 0;
        // }
        // if (!$bsn_partnerid || intval($bsn_partnerid) <= 0){
        //     $bsn_partnerid = 0;
        // }

      

        $app = $this->app;
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        // Query main occupation in sub category 
        $checkSub = $app->myoccupationsubcategoryStore()->searchTable()->select()->where('occupationcategoryid', $occupationCategory->id)->count();

        // If checksub has return = it has sub category
        // If checksub has no return = it has no sub category

        // Check whether input is correct (follows the correct designation)
        // If main occupation has subcategory
        if ($checkSub) {
            //$inputSubCategory = $app->myoccupationsubcategoryStore()->getById($occupationSubCategory->id);

            // check if subcategory's main occupation matches with input
            if($occupationSubCategory->occupationcategoryid != $occupationCategory->id){
                // if not match with input
                if(empty($occupationSubCategory)){
                    // If empty sub passed in
                    throw MyGtpOccupationCategoryWithIllegalSub::fromTransaction([], [
                        'message' => gettext("Sub category field is empty")
                    ]);
                }else {
                    // If sub passed in but is incorrect
                    throw MyGtpOccupationCategoryWithIllegalSub::fromTransaction([], [
                        'message' => gettext("Main category does not match with the main category of sub occupation.")
                    ]);
                }
            }
        }else{
            // If main occupation has no subcategory
            // Sub category should be empty
            if(!empty($occupationSubCategory)){
                throw MyGtpOccupationCategoryWithIllegalSub::fromTransaction([], [
                    'message' => gettext("This main category selected does not contain sub category.")
                ]);
            }

        }
        
        // Check if branch code is valid
        if (0 < strlen($referralBranchCode) && !$partner->getBranch($referralBranchCode)) {
            throw PartnerBranchCodeInvalid::fromTransaction([], ['code' =>  $referralBranchCode]);
        }

        // Check if language is valid if only provided
        if ($preferredLang && !in_array($preferredLang, [MyAccountHolder::LANG_EN, MyAccountHolder::LANG_BM, MyAccountHolder::LANG_CN])) {
            throw GeneralException::fromTransaction([], ['message' => 'Invalid language selected']);
        }
        $accHolderStore = $app->myaccountHolderStore();

        $emailTaken = $accHolderStore
            ->searchTable()
            ->select(['id'])
            ->where('partnerid', $partner->id)
            ->andWhere('email', $email)
            ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
            ->exists();

        // temp solution
        if (!$bsn_partnerid){
            if ($emailTaken && $partner->id != $redone_partnerid) {
                throw EmailAddressTakenException::fromTransaction($partner, ['email' => $email]);
            }
        }
        
        // if(!$usePartnerGroupId){
        //     if ($emailTaken && (!empty($listOfPartners) && !in_array($partner->id, $listOfPartners))) {
        //         throw EmailAddressTakenException::fromTransaction($partner, ['email' => $email]);
        //     }
        // }else{
        //     if ($emailTaken && (!empty($listOfPartners) && !in_array(11547, $listOfPartners))) {
        //         throw EmailAddressTakenException::fromTransaction($partner, ['email' => $email]);
        //     }
        // }
       
        
        // If not true, allow same IC for multiple account holder
        if ($settings->uniquenric) {
            $mykadExists = $accHolderStore->searchTable()->select()
                              ->where('mykadno', $myKadNo)
                              ->andWhere('partnerid', $partner->id)
                              ->andWhere('kycstatus', MyAccountHolder::KYC_PASSED)
                              ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                              ->exists();
            if ($mykadExists && $partner->id != $redone_partnerid) {
                throw MyGtpAccountHolderMyKadExists::fromTransaction([], ['mykadno' => $myKadNo]);
            }
            // if(!$usePartnerGroupId){
            //     if ($mykadExists && (!empty($listOfPartners) && !in_array($partner->id, $listOfPartners))) {
            //         throw MyGtpAccountHolderMyKadExists::fromTransaction([], ['mykadno' => $myKadNo]);
            //     }
            // }else{
            //     if ($mykadExists && (!empty($listOfPartners) && !in_array($partner->group, $listOfPartners))) {
            //         throw MyGtpAccountHolderMyKadExists::fromTransaction([], ['mykadno' => $myKadNo]);
            //     }
            // }
          
        }

        // Check if phone number already in use
        $existingAccountHolder = $this->app->myaccountholderStore()->searchTable()->select()
                                      ->where('phoneno', $phoneNo)
                                      ->whereNotNull('phoneverifiedon')
                                      ->andWhere('partnerid', $partner->id)
                                      ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                                    //   ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                                      ->exists();

        if (!$bsn_partnerid){
            if ($existingAccountHolder && $partner->id != $redone_partnerid) {
                throw MyGtpPhoneNumberExists::fromTransaction([], ['message' => "Phone number is already in use"]);
            }
        }
        // if(!$usePartnerGroupId){
        //     if ($existingAccountHolder && (!empty($listOfPartners) && !in_array($partner->id, $listOfPartners))) {
        //         throw MyGtpPhoneNumberExists::fromTransaction([], ['message' => "Phone number is already in use"]);
        //     }            
        // }else{
        //     if ($existingAccountHolder && (!empty($listOfPartners) && !in_array($partner->group, $listOfPartners))) {
        //         throw MyGtpPhoneNumberExists::fromTransaction([], ['message' => "Phone number is already in use"]);
        //     }
        // }
      

        $now = new \DateTime('now');
        $phoneTokenValid = false;
        if ($settings->verifyachphone && !$skipVerification) {
            $phoneToken =   $this->app->mytokenStore()->searchTable()->select()
                                 ->where('type', MyToken::TYPE_VERIFICATION_PHONE)
                                 ->andWhere('token', $phoneVerificationCode)
                                 ->andWhere('status', MyToken::STATUS_ACTIVE)
                                 ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                                 ->one();
            if (!$phoneToken) {
                throw GeneralException::fromTransaction([], ['message' => 'Phone verification code is invalid']);
            }

            // Check token's phone number is same as phone number given
            if ($phoneToken->remarks != $phoneNo) {
                throw GeneralException::fromTransaction([], ['message' => "Phone number given ($phoneNo) is different from the phone number used to receive verification code"]);
            }

            $phoneToken->status = MyToken::STATUS_INACTIVE;
            $phoneToken->expireon = $now;
            $phoneToken = $this->app->mytokenStore()->save($phoneToken);
            $phoneTokenValid = true;
        }

        // Generates the hashed password
        $hashedPw = $this->generateHashedPassword($password);

        do {
            $accountCode = $this->generateRandomAccountCode();
            $accountCodeExists = $app->myaccountholderStore()->searchTable()->select()
                                     ->where('accountholdercode', $accountCode)->exists();
        } while ($accountCodeExists);

        $account = $app->myaccountholderStore()->create([
            'partnerid' => $partner->id,
            'accountholdercode' => $accountCode,
            'fullname'  => $fullName,
            'mykadno' => $myKadNo,
            'phoneno' => $phoneNo,
            'occupationcategoryid' => $occupationCategory->id ?? 0,
            'occupationsubcategoryid' => (!empty($occupationSubCategory)) ? $occupationSubCategory->id : 0,
            'email' => $email,
            'password' => $hashedPw,
            'oldpassword' => $hashedPw,
            'preferredlang' => $preferredLang ?? MyAccountHolder::LANG_EN,
            'referralbranchcode' => $referralBranchCode,
            'referralsalespersoncode' => $referralSalespersonCode,
            'nokfullname' => $nokFullName ?? '',
            'nokmykadno' => $nokMyKadNo ?? '',
            'nokphoneno' => $nokPhoneNo ?? '',
            'nokemail'   => $nokEmail ?? '',
            'nokaddress' => $nokAddress ?? '',
            'nokrelationship' => $nokRelationship ?? '',
        ]);

        $now->setTimezone($this->app->getUserTimezone());
        if (! $settings->verifyachemail || $skipVerification) {
            $account->emailverifiedon = $now;
        }

        if (! $settings->verifyachphone || $phoneTokenValid || $skipVerification) {
            $account->phoneverifiedon = $now;
        }

        if ($settings->skipekyc) {
            $account->kycstatus = MyAccountHolder::KYC_PASSED;
        }

        if ($settings->skipamla) {
            $account->amlastatus = MyAccountHolder::AMLA_PASSED;
        }

        $account->status = ($account->emailverifiedon && $account->phoneverifiedon) ? MyAccountHolder::STATUS_ACTIVE : MyAccountHolder::STATUS_INACTIVE;

        // Optional
        if (0 < strlen($partnerCusId)) {
            $existingAccountHolder = $this->app->myaccountholderStore()->searchTable()->select()
                                      ->where('partnercusid', $partnerCusId)                                      
                                      ->andWhere('partnerid', $partner->id)
                                      ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                                      ->exists();

            //if ($existingAccountHolder) {
            //    throw MyGtpPartnerUsernameExists::fromTransaction([], ['message' => "Partner customer id is already in use"]);
            //}
            
            $account->partnercusid = $partnerCusId;
        }

        // Optional
        if (0 < strlen($partnerData)) {
            $account->partnerdata = $partnerData;
        }

        $account = $app->myaccountholderStore()->save($account);

    
        /*
        * Do email check and send here
        */
        // For the case with Subcategory
        // Check if user occupation is politically exposed
        // If yes, send email
        if(!empty($occupationSubCategory)){
            if(1 == $occupationSubCategory->politicallyexposed){
                $this->notify(new IObservation($account, IObservation::ACTION_OTHER, $account->status, [
                    //'event' => MyGtpEventConfig::EVENT_GOLDTRANSACTION_EDIT_REF,
                    'accountholdercode'     => $account->accountholdercode,
                    'mykadno'               => $account->mykadno,
                    'name'                  => $account->fullname,
                    'receiver'              => $account->email,
                    'occupation'            => $occupationCategory->category,
                    'suboccupation'         => $occupationSubCategory->code,
                ]));
            }
        }else{
            // If there are no subcategory present, check if the occupationcategory is PEP
            if(1 == $occupationCategory->politicallyexposed){
                $this->notify(new IObservation($account, IObservation::ACTION_OTHER, $account->status, [
                    //'event' => MyGtpEventConfig::EVENT_GOLDTRANSACTION_EDIT_REF,
                    'accountholdercode'     => $account->accountholdercode,
                    'mykadno'               => $account->mykadno,
                    'name'                  => $account->fullname,
                    'receiver'              => $account->email,
                    'occupation'            => $occupationCategory->category,
                    'suboccupation'         => '-',
                ]));
            }
        }

        if (!$account->emailverifiedon) {
            $this->sendEmailVerification($partner, $account->email);
            // Save timestamp before sending email
            $account->emailtriggeredon = $now;
            $account = $this->app->myaccountholderStore()->save($account);
        }

        return $account;
    }

    /**
     * Triggers job to send email for verification purpose
     * 
     * @param Partner $partner 
     * @param mixed $email 
     * @return MyToken 
     * @throws GeneralException 
     */
    public function sendEmailVerification(Partner $partner, $email)
    {
        // Check account exists and is not verified
        $account = $this->app->myaccountholderStore()->searchTable()->select()
                        ->where('email', $email)
                        ->andWhere('partnerid', $partner->id)
                        ->whereNull('emailverifiedon')
                        ->andWhere('status', MyAccountHolder::STATUS_INACTIVE)
                        ->one();

        if (!$account) {
            throw GeneralException::fromTransaction(null, [
                'message'   => "Invalid email address given."
            ]);
        }

        // Invalidate existing tokens
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $existingTokens = $this->app->mytokenStore()->searchTable()->select()
                    ->andWhere('type', MyToken::TYPE_VERIFICATION)
                    ->andWhere('accountholderid', $account->id)
                    ->andWhere('status', MyToken::STATUS_ACTIVE)
                    ->execute();
        foreach ($existingTokens as $existing) {
            $existing->status = MyToken::STATUS_INACTIVE;
            $this->app->mytokenStore()->save($existing);
        }

        // Generate token and send email
        $token = $this->app->mygtptokenManager()->generateAccountVerificationToken($account);
        $observation = new IObservation($account, IObservation::ACTION_VERIFY, 0, [
            'event' => MyGtpEventConfig::EVENT_ACCOUNT_VERIFICATION,
            'projectbase' => $this->app->getConfig()->{'projectBase'},
            'name' => $account->fullname,
            'receiver' => $account->email,
            'partnercode' => $partner->code,
            'code' => $token->token,
            'expiry' => $token->expireon,
            'email' => urlencode($account->email)
        ]);
        $this->notify($observation);

        return $token;
    }

    /**
     * Sends phone verification
     * 
     * @param Partner $partner
     * @param string $phoneNo 
     * @return MyToken 
     * @throws GeneralException 
     */
    public function sendPhoneVerification(Partner $partner, string $phoneNo)
    {
        if (!$this->ipCanSendSms()) {
            throw IpMaximumRetries::fromTransaction([], [
                'seconds'   => $this->getIpSmsTimeToExpire()
            ]);
        }

        // Check if phone number already in use
        $existingAccountHolder = $this->app->myaccountholderStore()->searchTable()->select()
                                      ->where('phoneno', $phoneNo)
                                      ->whereNotNull('phoneverifiedon')
                                      ->andWhere('partnerid', $partner->id)
                                      ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
                                    //   ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                                      ->one();

        if ($existingAccountHolder) {
            throw MyGtpPhoneNumberExists::fromTransaction([], ['message' => "Phone number is already in use"]);
        }

        // Invalidate existing tokens
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $existingTokens = $this->app->mytokenStore()->searchTable()->select()
                    ->andWhere('type', MyToken::TYPE_VERIFICATION_PHONE)
                    ->andWhere('status', MyToken::STATUS_ACTIVE)
                    // ->andWhere('accountholderid', $accHolder->id)
                    ->andWhere('remarks', $phoneNo)
                    ->execute();
        foreach ($existingTokens as $existing) {
            $existing->status = MyToken::STATUS_INACTIVE;
            $this->app->mytokenStore()->save($existing);
        }

        // Generate token and send sms
        $token = $this->app->mygtptokenManager()->generatePhoneVerificationToken($phoneNo);
        $observation = new IObservation($this->app->myaccountholderStore()->create(), IObservation::ACTION_VERIFY, 0, [
            'event' => MyGtpEventConfig::EVENT_PHONE_VERIFICATION,
            'projectbase' => $this->app->getConfig()->{'projectBase'},
            'receiver' => $phoneNo,
            'code' => $token->token,
            'expiry' => $token->expireon,
        ]);
        $this->notify($observation);

        $this->incrementIpSentSms();
        return $token;
    }

    /**
     * This method check if account holder exists and generate a password reset token for the account holder
     *
     * @param  Partner $partner
     * @param  string  $email
     * @return string
     */
    public function forgotPassword(Partner $partner, string $email)
    {
        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('email', $email)
            // ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        // If account holder is not found
        if (!$accountHolder) {
            throw EmailAddressNotFound::fromTransaction([], ['email' => $email]);
        }

        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenManager = $this->app->mygtptokenManager();


        // Generate the unique token for account holder
        $token = $tokenManager->generatePasswordResetToken($accountHolder);

        // Send email
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_CHANGEREQUEST,
            $accountHolder->status,
            [
                'event' => MyGtpEventConfig::EVENT_FORGOT_PASSWORD,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'name' => $accountHolder->fullname,
                'expiry' => $token->expireon,
                'code' => $token->token,
                'receiver' => $accountHolder->email
            ]
        );

        $this->notify($observation);

        return $token->token;
    }

    /**
     * Forgot password - send code through phone
     * @param Partner $partner 
     * @param string $phoneNo 
     * @return string
     * @throws MyGtpPhoneNumberNotExist 
     */
    public function forgotPasswordPhone(Partner $partner, string $phoneNo)
    {
        if (!$this->ipCanSendSms()) {
            throw IpMaximumRetries::fromTransaction([], [
                'seconds'   => $this->getIpSmsTimeToExpire()
            ]);
        }

        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('phoneno', $phoneNo)
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        // If account holder is not found
        if (!$accountHolder) {
            throw MyGtpPhoneNumberNotExist::fromTransaction([], ['phone_number' => $phoneNo]);
        }

        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenManager = $this->app->mygtptokenManager();

        // Generate the unique token for account holder
        $token = $tokenManager->generatePasswordResetToken($accountHolder);

        // Send email
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_CHANGEREQUEST,
            $accountHolder->status,
            [
                'event' => MyGtpEventConfig::EVENT_FORGOT_PASSWORD_PHONE,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'name' => $accountHolder->fullname,
                'expiry' => $token->expireon,
                'code' => $token->token,
                'receiver' => $accountHolder->phoneno
            ]
        );

        $this->notify($observation);

        $this->incrementIpSentSms();
        return $token->token;
    }

    /**
     * Reset the password for the account holder
     *
     * @param  Partner $partner
     * @param  string  $email
     * @param  string  $newPassword
     * @param  string  $code
     * @return void
     */
    public function resetPassword(Partner $partner, string $email, string $newPassword, string $code)
    {
        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('email', $email)
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        // If account holder is not found
        if (!$accountHolder) {
            throw EmailAddressNotFound::fromTransaction([], ['email' => $email]);
        }

        $this->resetPassword_common($partner, $accountHolder, $newPassword, $code);
    }

    /**
     * Reset the password for the account holder using phone number
     *
     * @param  Partner $partner
     * @param  string  $phoneNo
     * @param  string  $newPassword
     * @param  string  $code
     * @return void
     */
    public function resetPasswordPhone(Partner $partner, string $phoneNo, string $newPassword, string $code)
    {
        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('phoneno', $phoneNo)
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        // If account holder is not found
        if (!$accountHolder) {
            throw MyGtpPhoneNumberNotExist::fromTransaction([], ['phone_number' => $phoneNo]);
        }

        $this->resetPassword_common($partner, $accountHolder, $newPassword, $code);
    }


    /**
     * Common logic for reset password
     * 
     * @param Partner $partner 
     * @param MyAccountHolder $accountHolder 
     * @param string $newPassword 
     * @param string $code 
     * @return void 
     * @throws ResetTokenInvalid 
     */
    protected function resetPassword_common(Partner $partner, MyAccountHolder $accountHolder, string $newPassword, string $code)
    {
        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenManager = $this->app->mygtptokenManager();

        if (!$tokenManager->verifyTokenValidity($accountHolder, $code, MyToken::TYPE_PASSWORD_RESET)) {
            throw ResetTokenInvalid::fromTransaction($partner, []);
        }

        // Invalidate token so it can't be reused
        $tokenManager->invalidatePasswordResetToken($accountHolder, $code);

        $this->savePassword($accountHolder, $newPassword);

        $this->app->myaccountholderStore()->save($accountHolder);

        // Send email
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_ASSIGN,
            $accountHolder->status,
            [
                'event' => MyGtpEventConfig::EVENT_RESET_PASSWORD,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'name' => $accountHolder->fullname,
                'passwordmodifiedon' => $accountHolder->passwordmodified,
                'receiver' => $accountHolder->email
            ]
        );

        $this->notify($observation);
    }

    /**
     * This method check if account holder exists and generate a password reset token for the account holder
     *
     * @param  Partner $partner
     * @param  MyAccountHolder  $accountHolder
     * @return string
     */
    public function forgotPin(Partner $partner, MyAccountHolder $accountHolder)
    {
        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenManager = $this->app->mygtptokenManager();

        // Generate the unique token for account holder
        $token = $tokenManager->generatePinResetToken($accountHolder);

        // Send email
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_CHANGEREQUEST,
            $accountHolder->status,
            [
                'event' => MyGtpEventConfig::EVENT_FORGOT_PIN,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'name' => $accountHolder->fullname,
                'expiry' => $token->expireon,
                'code' => $token->token,
                'receiver' => $accountHolder->email
            ]
        );

        $this->notify($observation);

        return $token->token;
    }

    /**
     * Reset the password for the account holder
     *
     * @param  Partner $partner
     * @param  MyAccountHolder  $accountHolder
     * @param  string  $newPin
     * @param  string  $code
     * @return void
     */
    public function resetPin(Partner $partner, MyAccountHolder $accountHolder, string $newPin, string $code)
    {
        /** @var \Snap\manager\MyGtpTokenManager */
        $tokenManager = $this->app->mygtptokenManager();

        if (!$tokenManager->verifyTokenValidity($accountHolder, $code, MyToken::TYPE_PIN_RESET)) {
            throw ResetTokenInvalid::fromTransaction($partner, []);
        }

        // Invalidate token so it can't be reused
        $tokenManager->invalidateToken($accountHolder, $code, MyToken::TYPE_PIN_RESET);

        $this->savePincode($accountHolder, $newPin);

        // Send email
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_ASSIGN,
            $accountHolder->status,
            [
                'event' => MyGtpEventConfig::EVENT_RESET_PIN,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'name' => $accountHolder->fullname,
                'pinmodifiedon' => (new \DateTime('now', $this->app->getUserTimezone()))->format("Y-m-d H:i:s"),
                'receiver' => $accountHolder->email
            ]
        );

        $this->notify($observation);
    }

    public function editPassword(MyAccountHolder $accountHolder, $newPassword, $oldPassword)
    {
        // Verify the password
        if (!password_verify($oldPassword, $accountHolder->password)) {
            throw CredentialInvalid::fromTransaction(null);
        }

        $this->savePassword($accountHolder, $newPassword);
    }

    /**
     * Edit the account holder  pincode 
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string          $oldPincode
     * @param  string          $newPincode
     * @return void
     */
    public function editPincode(MyAccountHolder $accountHolder, string $newPincode, string $oldPincode = null)
    {

        // If account holder already setup a pincode
        if ($accountHolder->hasPincode()) {

            // Verify the pincode
            if (null === $oldPincode || !$this->verifyPincode($oldPincode, $accountHolder->pincode)) {
                throw MyGtpAccountHolderWrongPin::fromTransaction(null);
            }
        }

        $this->savePincode($accountHolder, $newPincode);
    }

    /**
     * Verify an account holder email
     * 
     * @param Partner $partner 
     * @param string $email 
     * @param string $verificationCode 
     * 
     * @return MyAccountHolder
     */
    public function verifyAccountHolderEmail(Partner $partner, string $email, string $verificationCode)
    {
        $accHolder = $this->app->myaccountHolderStore()->searchTable()->select()
                          ->where('email', $email)
                          ->andWhere('partnerid', $partner->id)
                          ->andWhere('status', MyAccountHolder::STATUS_INACTIVE)
                          ->one();
        if (! $accHolder) {
            // throw MyGtpAccountHolderNotExist::fromTransaction($accHolder);
            throw MyGtpVerificationCodeInvalid::fromTransaction(null, ['message' => 'Invalid Account Holder.']);
        }

        $now = new \DateTime();
        $token = $this->app->mytokenStore()->searchTable()->select()
                    ->where('token', $verificationCode)
                    ->andWhere('type', MyToken::TYPE_VERIFICATION)
                    ->andWhere('accountholderid', $accHolder->id)
                    ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                    ->andWhere('status', MyToken::STATUS_ACTIVE)
                    ->one();
        
        if (! $token) {
            throw MyGtpVerificationCodeInvalid::fromTransaction(null, ['message' => 'Invalid Token.']);
        }

        // Set status active and invalidate token
        if ($accHolder->phoneverifiedon) {
            $accHolder->status = MyAccountHolder::STATUS_ACTIVE;
        }
        $now->setTimezone($this->app->getUserTimezone());
        $accHolder->emailverifiedon = $now;
        $accHolder = $this->app->myaccountHolderStore()->save($accHolder);
        $this->app->mygtpTokenManager()->invalidateVerificationToken($accHolder, $token->token, MyToken::TYPE_VERIFICATION);

        return $accHolder;
    }

    /**
     * Verify an account holder phone after registration
     * Not used anymore, phone verification now done during registration
     * 
     * @param MyAccountHolder $accountHolder 
     * @param string $phoneNo 
     * @param string $verificationCode 
     * @return MyAccountHolder
     * @throws MyGtpVerificationCodeInvalid 
     */
    public function verifyAccountHolderPhone(MyAccountHolder $accountHolder, string $phoneNo, string $verificationCode)
    {
        if ($accountHolder->phoneverifiedon) {
            throw MyGtpActionNotPermitted::fromTransaction([], ['message' => "Phone number is already verified"]);
        }

        $now = new \DateTime();
        $token = $this->app->mytokenStore()->searchTable()->select()
                    ->where('accountholderid', $accountHolder->id)
                    ->andWhere('type', MyToken::TYPE_VERIFICATION_PHONE)
                    ->andWhere('expireon', '>', $now->format('Y-m-d H:i:s'))
                    ->andWhere('status', MyToken::STATUS_ACTIVE)
                    ->one();
        
        /**
         * Verification code is invalid if
         * 1. Active phone verification token not found
         * 2. Verification code does not match
         * 3. Phone number given does not match
         * 4. Current account holder id does not match with token
         */
        if (! $token ||
            $verificationCode != $token->token ||
            $phoneNo != $token->remarks ||
            $accountHolder->id != $token->accountholderid) {
            throw MyGtpVerificationCodeInvalid::fromTransaction([]);
        }

        // Set status active and invalidate token
        if ($accountHolder->emailverifiedon) {
            $accountHolder->status = MyAccountHolder::STATUS_ACTIVE;
        }

        // Set phone verified and use the phone number as
        $now->setTimezone($this->app->getUserTimezone());
        $accountHolder->phoneno = $phoneNo;
        $accountHolder->phoneverifiedon = $now;
        $accountHolder = $this->app->myaccountHolderStore()->save($accountHolder);
        $this->app->mygtpTokenManager()->invalidateVerificationToken($accountHolder, $token->token, MyToken::TYPE_VERIFICATION_PHONE);

        return $accountHolder;
    }

    /**
     * Verifies the account holder's current pin
     * @param  MyAccountHolder $accHolder
     * @param  string          $pinToVerify
     * 
     * @return bool
     */
    public function verifyAccountHolderPin($accHolder, $pinToVerify = null)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);
        
        if (! $settings->verifyachpin) {
            return true;
        }

        if (!$accHolder->hasPincode()) {
            throw MyGtpAccountHolderNoPincode::fromTransaction(null);
        }

        if (is_null($pinToVerify)) {
            throw MyGtpAccountHolderPincodeRequired::fromTransaction(null);
        }

        return $this->verifyPincode($pinToVerify, $accHolder->pincode);
    }

    /**
     * Verify if the given pincode matched with the stored pincode
     *
     * @param  string $pincode
     * @param  string $storedPincode
     * @param  bool   $useHash
     * @return bool
     */
    public function verifyPincode($pincode, $storedPincode, $useHash = true)
    {
        return $useHash ? $storedPincode === $this->hashPincode($pincode) : $storedPincode === $pincode;
    }

    /**
     * Edit the account holder bank account information
     *
     * @param  MyAccountHolder $accountHolder
     * @param  MyBank $bank
     * @param  string $bankAccName
     * @param  string $bankAccNumber
     * @param  string $pincode
     * @return MyAccountHolder
     */
    public function editBankAccount(
        MyAccountHolder $accountHolder,
        MyBank $bank,
        string $bankAccName,
        string $bankAccNumber,
        string $pincode = null
    ) {
        // Verify the pincode
        if (!$this->verifyAccountHolderPin($accountHolder, $pincode)) {
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        }

        $accountHolder->bankid = $bank->id;
        $accountHolder->accountnumber = $bankAccNumber;
        $accountHolder->accountname = $bankAccName;

        $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);
        return $accountHolder;
    }

     /**
      * Edit the account holder profile

      * @param MyAccountHolder $accountHolder 
      * @param string $line1 
      * @param string $line2 
      * @param string $postcode 
      * @param string $city 
      * @param string $state 
      * @param string $nokFullName 
      * @param string $nokMyKadNo 
      * @param string $nokPhoneNo 
      * @param string $nokEmail 
      * @param string $nokAddress 
      * @param string $nokRelationship 
      * @param MyOccupationCategory $occupationCategory 
      * @param MyOccupationSubCategory|null $occupationSubCategory 
      * @param string $salespersonCode 
      * @param string $referralBranchCode 
      * @param string|null $pincode 

      * @return MyAccountHolder 
      * @throws MyGtpOccupationCategoryWithIllegalSub 
      */
    public function editProfile(
        MyAccountHolder $accountHolder,
        string $line1,
        string $line2,
        string $postcode,
        string $city,
        string $state,
        string $nokFullName,
        $nokMyKadNo = '',
        $nokPhoneNo,
        $nokEmail,
        $nokAddress,
        $nokRelationship,
        MyOccupationCategory $occupationCategory,
        MyOccupationSubCategory $occupationSubCategory = null,
        $salespersonCode = '',
        $referralBranchCode = '',
        string $pincode = null,
		$mailingLine1 = null, 
		$mailingLine2 = null, 
		$mailingCity = null, 
		$mailingPostcode = null, 
		$mailingState = null
    ) {
        
        // Query main occupation in sub category 
        $checkSub = $this->app->myoccupationsubcategoryStore()->searchTable()->select()->where('occupationcategoryid', $occupationCategory->id)->count();

        // If checksub has return = it has sub category
        // If checksub has no return = it has no sub category

        // Check whether input is correct (follows the correct designation)
        // If main occupation has subcategory
        if ($checkSub) {
            //$inputSubCategory = $app->myoccupationsubcategoryStore()->getById($occupationSubCategory->id);

            // check if subcategory's main occupation matches with input
            if($occupationSubCategory->occupationcategoryid != $occupationCategory->id){
                // if not match with input
                throw MyGtpOccupationCategoryWithIllegalSub::fromTransaction([], [
                    'message' => gettext("Main category does not match with the main category of sub occupation.")
                ]);
            }
        }else{
            // If main occupation has no subcategory
            // Sub category should be empty
            if(!empty($occupationSubCategory)){
                throw MyGtpOccupationCategoryWithIllegalSub::fromTransaction([], [
                    'message' => gettext("This main category selected does not contain sub category.")
                ]);
            }

        }

        // Verify the pincode
        if (!$this->verifyAccountHolderPin($accountHolder, $pincode)) {
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        }

        // If initially null, assume during registration it was not provided 
        if (0 == strlen($accountHolder->referralsalespersoncode) && $salespersonCode) {
            throw MyGtpProfileUpdateNotAllowed::fromTransaction(null, ['message' => gettext('Not allowed to update sales person code.')]);
        }

        // Check if branch code is valid
        if (0 < strlen($referralBranchCode) && !$accountHolder->getPartner()->getBranch($referralBranchCode)) {
            throw PartnerBranchCodeInvalid::fromTransaction([], ['code' =>  $referralBranchCode]);
        }

        // Get exisiting address
        /** @var MyAddress */
        $address = $accountHolder->getAddress();

        // Update address for account holder
        if ($address) {
            $accountHolder->updateAddress($address, $line1, $line2, $city, $postcode, $state, $mailingLine1, $mailingLine2, $mailingCity, $mailingPostcode, $mailingState);
        } else {
            $accountHolder->addAddress($line1, $line2, $city, $postcode, $state, $mailingLine1, $mailingLine2, $mailingCity, $mailingPostcode, $mailingState);
        }

        // Update next of kin of account holder
        $accountHolder->nokfullname = $nokFullName;
        $accountHolder->nokmykadno = $nokMyKadNo ?? '';
        $accountHolder->nokphoneno = $nokPhoneNo;
        $accountHolder->nokemail = $nokEmail;
        $accountHolder->nokaddress = $nokAddress;
        $accountHolder->nokrelationship = $nokRelationship;
        $accountHolder->referralsalespersoncode = $salespersonCode;
        $accountHolder->referralbranchcode = $referralBranchCode;

        $accountHolder->occupationcategoryid = $occupationCategory->id;
        $accountHolder->occupationsubcategoryid = (!empty($occupationSubCategory)) ? $occupationSubCategory->id : 0;
        //$accountHolder->occupation = $occupation;

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Change the language of the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string          $language
     * @return MyAccountHolder
     */
    public function changeLanguage(MyAccountHolder $accountHolder, string $language)
    {
        if (!in_array($language, [MyAccountHolder::LANG_EN, MyAccountHolder::LANG_BM, MyAccountHolder::LANG_CN])) {
            throw GeneralException::fromTransaction([], ['message' => 'Invalid language selected']);
        }

        if ($accountHolder->preferredlang !== $language) {
            $accountHolder->preferredlang = $language;
            $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);
        }

        return $accountHolder;
    }

    /**
     * Request account closure for the given account holder using the reason selected 
     *
     * @param  Partner $partner
     * @param  MyAccountHolder $accountHolder
     * @param  MyCloseReason $reason
     * @param  string $version
     * @param  string $pincode
     * @param  MyGtpTransactionManager $transactionMgr
     * @param  MyGtpStorageManager $storageMgr
     * @return MyAccountClosure
     */
    public function closeAccount(
        Partner $partner, 
        MyAccountHolder $accountHolder,
        MyCloseReason $reason = null,
        string $version,
        string $pincode = null,
        bool $skipPin = false,
        string $remarks = '',
        bool $isDormant = false,
        MyGtpTransactionManager $transactionMgr = null,
        MyGtpStorageManager $storageMgr = null
    ) {

        $this->validateCloseAccountRequest($partner, $accountHolder, $pincode, $skipPin, $isDormant);

        try {                   

            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }

            $now = new \DateTime();
            $now->setTimezone($this->app->getUserTimezone());
            $accountClosure = $this->app->myaccountclosureStore()->create([
                'reasonid' => $reason ? $reason->id : 0,
                'accountholderid' => $accountHolder->id,
                'status' => MyAccountClosure::STATUS_PENDING,
                'remarks' => $remarks,
                'requestedon' => $now
            ]);
            $accountClosure = $this->app->myaccountclosureStore()->save($accountClosure);

            // Suspend the account holder
            $accountHolder->status = MyAccountHolder::STATUS_SUSPENDED;
            $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);
            
            $this->log(__METHOD__ . "() Account closure requested successfully for account holder {$accountHolder->id}", SNAP_LOG_DEBUG);            
            $notifyUser = filter_var($this->app->getConfig()->{'mygtp.accountclosure.notifyuser'}, FILTER_VALIDATE_BOOLEAN);            
            if ($notifyUser) {                
                $email = $accountHolder->email;
            }

            $partnerinfo = $this->getsendernamesenderemail($accountHolder);
            $this->notify(new IObservation($accountClosure, IObservation::ACTION_NEW, MyAccountClosure::STATUS_PENDING, [
                'event'           => MyGtpEventConfig::EVENT_CLOSE_ACCOUNT,
                'accountholderid' => $accountHolder->id,
                'projectBase'     => $partnerinfo['projectBase'],
                'name'            => $accountHolder->fullname,
                'email'           => $accountHolder->email,
                'receiver'        => $email ?? '',
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]));

            $balance = $accountHolder->getCurrentGoldBalance();
            $inititialInvestmentMade = $accountHolder->initialInvestmentMade();

            // If there is balance then we submit a CompanyBuy order on behalf of user
            if ( 0 < $balance && $inititialInvestmentMade) {

                $product  = $this->app->productStore()->getByField('code', 'DG-999-9');
                if($this->app->getConfig()->{'otc.job.diffserver'} == '1'){
                    $partner = $this->app->partnerStore()->searchTable()->select()->where('id', $accountHolder->partnerid)->one();
                    $provider = $this->app->priceproviderStore()->getForPartnerByProduct($partner, $product);
                }
                else{
                    $provider = $this->app->priceproviderStore()->getForPartnerByProduct($partner, $product);
                }

                /** @var PriceManager $priceMgr */
                $priceMgr = $this->app->priceManager();
                $priceStream = $priceMgr->getLatestSpotPrice($provider);

                $accountClosure = $this->onProcessAccountClosure($partner, $accountHolder, $accountClosure, $product, $priceStream, $version, $transactionMgr, $storageMgr);

                if (0 >= strlen($accountClosure->transactionrefno)) {
                    // If no sell transaction was made, then no admin intervention
                    $accountClosure = $this->onApproveAccountClosure($accountClosure, MyGtpEventConfig::EVENT_CLOSE_ACCOUNT);
                }
                
            } else {
                $event = $isDormant ? MyGtpEventConfig::EVENT_CLOSE_DORMANT_ACCOUNT : MyGtpEventConfig::EVENT_CLOSE_ACCOUNT;
                $accountClosure = $this->onApproveAccountClosure($accountClosure, $event);
            }

            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }

        } catch (\Exception $e) {

            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to request account closure for account holder {$accountHolder->id}. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }

        return $accountClosure;
    }

    /**
     * Check if can close account
     *
     * @param Partner         $partner
     * @param MyAccountHolder $accountHolder
     * @param string          $pincode
     * @param bool            $skipPin
     * @param bool            $isDormant
     * @return bool
     */
    public function validateCloseAccountRequest($partner, $accountHolder, $pincode = null, $skipPin = false, $isDormant = false)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

        if ($accountHolder->initialInvestmentMade() && !$accountHolder->hasBankAccount() && !$isDormant) {
            throw MyGtpAccountHolderNoAccountNameOrNumber::fromTransaction([], ['message' => gettext("Account name/number not set")]);
        }

        $pendingAccountClosure = $this->app->myaccountclosureStore()
            ->searchTable()
            ->select(['id'])
            ->where('accountholderid', $accountHolder->id)
            ->andWhere('status', MyAccountClosure::STATUS_PENDING)
            ->exists();

        if ($pendingAccountClosure) {
            throw MyGtpAccountClosurePending::fromTransaction([], []);
        }

        if (!$settings->achcancloseaccount) {
            throw MyGtpAccountClosureNotAllowed::fromTransaction([], []);
        }

        // Verify the pincode
        if (! $skipPin && $settings->verifyachpin && !$this->verifyAccountHolderPin($accountHolder, $pincode)) {
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        }

        if (!$accountHolder->isActive()) {
            throw MyGtpActionNotPermitted::fromTransaction([], ['message' => gettext('Account is not active')]);
        }

        return true;
    }

    /**
     * Process the account closure, 
     * submit sell orders and
     * charge monthly storage fee
     *
     * @param  Partner $partner
     * @param  MyAccountHolder $accountHolder
     * @param  MyAccountClosure $accountClosure
     * @param  Product $product
     * @param  PriceStream $priceStream
     * @param  string $apiVersion
     * @param  MyGtpTransactionManager $transactionMgr
     * @param  MyGtpStorageManager $storageMgr
     * @return MyAccountClosure
     */
    protected function onProcessAccountClosure(
        $partner, 
        $accountHolder, 
        $accountClosure, 
        $product, 
        $priceStream, 
        $apiVersion,
        $transactionMgr = null,
        $storageMgr = null
    ) {
        try {

            $alreadyInTransaction = $this->app->getDbHandle()->inTransaction();
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->beginTransaction();
            }        
            
            $sm = $this->getAccountClosureStateMachine($accountClosure);

            if (! $sm->can(MyAccountClosure::STATUS_IN_PROGRESS)) {
                $this->log(__METHOD__ . ":  Unable to proceed account closure due to status", SNAP_LOG_ERROR);
                throw \Snap\api\exception\MyGtpAccountClosureInvalidAction::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid status")]);
            }

            $now = new \DateTime('now', $this->app->getServerTimezone());

            $balanceBeforeDeduction = $accountHolder->getCurrentGoldBalance();

            /** @var MyGtpStorageManager $storageMgr */
            $storageMgr = $storageMgr ?? $this->app->mygtpstorageManager();
            $storageMgr->calculateDailyStorageFee($partner, $accountHolder, $now);
            $msFees = $storageMgr->chargeMonthlyFee($partner, $accountHolder, $priceStream, $now);

            $dailyFeeCount = $storageMgr->totalCountDaiyFees($accountHolder, $now);

            /** @var MyGtpTransactionManager $transactionMgr */
            $transactionMgr = $transactionMgr ?? $this->app->mygtptransactionManager();
            $balanceAfterDeduction = $accountHolder->getCurrentGoldBalance();
            
            $bank = $this->app->mybankStore()->getById($accountHolder->bankid);

            $notifyAce = filter_var($this->app->getConfig()->{'mygtp.accountclosure.notifyace'}, FILTER_VALIDATE_BOOLEAN);
            $notifyUser = filter_var($this->app->getConfig()->{'mygtp.accountclosure.notifyuser'}, FILTER_VALIDATE_BOOLEAN);

            if (0 < $balanceAfterDeduction) {
                // Create an order in the system
                $goldTx = $transactionMgr->bookGoldTransaction(
                    $accountHolder, 
                    $partner, 
                    $product,
                    $priceStream->uuid, 
                    Order::TYPE_COMPANYBUY,
                    floatval($balanceAfterDeduction),
                    MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
                    $apiVersion,
                    null,
                    false,
                    '',
                    '',
                    true
                );
                $goldTx = $transactionMgr->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_SELL, null, false, $notifyUser);
                $order = $goldTx->getOrder();
            }

            
            $accountClosure->transactionrefno = $goldTx ? $goldTx->refno : null;
            $accountClosure->status = MyAccountClosure::STATUS_IN_PROGRESS;
            $accountClosure = $this->app->myaccountclosureStore()->save($accountClosure);

            $partnerinfo = $this->getsendernamesenderemail($accountHolder);
            $email = '';
            if ($notifyAce) {
                $email = $partnerinfo['projectemail'];
            }

            if ($notifyUser) {
                if ($notifyAce) {
                    $email .= ',' . $accountHolder->email;
                }
                else{
                    $email = $accountHolder->email;
                }
                
            }
            $this->notify(new IObservation($accountClosure, IObservation::ACTION_VERIFY, MyAccountClosure::STATUS_PENDING, [
                'event'                 => MyGtpEventConfig::EVENT_CLOSE_ACCOUNT,
                'accountholderid'       => $accountHolder->id,
                'projectBase'           => $partnerinfo['projectBase'],
                'accountholdercode'     => $accountHolder->accountholdercode,
                'nric'                  => $accountHolder->mykadno,
                'name'                  => $accountHolder->fullname,
                'email'                 => $accountHolder->email,
                'goldtransactionrefno'  => $accountClosure->transactionrefno ? $accountClosure->transactionrefno : 'N/A',
                'feerefno'              => $msFees[$accountHolder->id]->refno ? $msFees[$accountHolder->id]->refno : 'N/A',
                'feededucted'           => number_format($msFees[$accountHolder->id]->xau,3),
                'feecount'              => $dailyFeeCount,
                'balancebefore'         => number_format($balanceBeforeDeduction, 3),
                'balanceafter'          => number_format($balanceAfterDeduction, 3),
                'sellingamount'         => $order ? number_format($order->amount,2) : '0.00',
                'sellingprice'          => $order ? number_format($order->price,2) : '0.00',
                'payoutfee'             => $order ? number_format($order->fee,2) : '0.00',
                'bankname'              => $bank->name,
                'bankaccname'           => $accountHolder->accountname,
                'bankaccnumber'         => $accountHolder->accountnumber,
                'receiver'              => $email ?? '',
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]));

            $this->log(__METHOD__ . "() Account closed successfully for account holder {$accountHolder->id}", SNAP_LOG_DEBUG);
            
            if (!$alreadyInTransaction) {
                $this->app->getDbHandle()->commit();
            }
        } catch (\Exception $e) {
            if (!$alreadyInTransaction && $this->app->getDBHandle()->inTransaction()) {
                $this->app->getDbHandle()->rollback();
            }

            $this->log(__METHOD__ . "() Error while trying to process account closure for account holder {$accountHolder->id}. " . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }

        return $accountClosure;
    }

    /**
     * Approve the account closure 
     *
     * @param MyAccountClosure $accountClosure
     * @return MyAccountClosure
     */
    protected function onApproveAccountClosure(MyAccountClosure $accountClosure, $event)
    {
        $sm = $this->getAccountClosureStateMachine($accountClosure);
        if (! $sm->can(MyAccountClosure::STATUS_APPROVED)) {
            $this->log(__METHOD__ . ":  Unable to proceed account closure due to status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\MyGtpAccountClosureInvalidAction::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid status")]);
        }

        $closeDate = new \DateTime('now');
        $closeDate->setTimezone($this->app->getUserTimezone());

        $accountClosure->status   = MyAccountClosure::STATUS_APPROVED;
        $accountClosure->closedon = $closeDate;
        $accountClosure = $this->app->myaccountclosureStore()->save($accountClosure);

        $accountHolder = $this->app->myaccountholderStore()->getById($accountClosure->accountholderid);
        $accountHolder->status = MyAccountHolder::STATUS_CLOSED;
        $this->app->myaccountholderStore()->save($accountHolder);

        $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        $email = '';
        $notifyAce = filter_var($this->app->getConfig()->{'mygtp.accountclosure.notifyace'}, FILTER_VALIDATE_BOOLEAN);
        if ($notifyAce) {
            $email = $partnerinfo['projectemail'];
        }

        $notifyUser = filter_var($this->app->getConfig()->{'mygtp.accountclosure.notifyuser'}, FILTER_VALIDATE_BOOLEAN);            
        if ($notifyUser) {
            if ($notifyAce) {
                $email .= ',' . $accountHolder->email;
            }
            else{
                $email = $accountHolder->email;
            }
            
        }

        $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
        $this->notify(new IObservation($accountClosure, IObservation::ACTION_CONFIRM, MyAccountClosure::STATUS_PENDING, [
            'event'           => $event,
            'accountholderid' => $accountHolder->id,
            'projectBase'     => $partnerinfo['projectBase'],
            'name'            => $accountHolder->fullname,
            'email'           => $accountHolder->email,
            'receiver'        => $email ?? '',
            'cutoff'          => $lastTransactionsCutoff,
            'sendername'            => $partnerinfo['sendername'],
            'senderemail'           => $partnerinfo['senderemail'],
        ]));

        return $accountClosure;
    }

    /**
     * Check account closing request status for the given account holder 
     *
     * @param  MyAccountHolder  $accountHolder
     * @param  MyCloseReason    $reason
     * @param  string           $pincode     
     * @return MyAccountClosure
     */
    public function getCloseAccountStatus(MyAccountHolder $accountHolder)
    {
        $pendingAccountClosure = $this->app->myaccountclosureStore()
            ->searchTable()
            ->select([])
            ->where('accountholderid', $accountHolder->id)
            ->orderBy('id', 'DESC')
            ->one();

        
        return $pendingAccountClosure;
    }

    /**
     * Get dormant account holders for the given partner based on last login cutoff date
     *
     * @param Partner            $partner
     * @param \DateTime          $lastLoginCutoffDate
     * @return MyAccountHolder[]
     */
    public function getDormantAccountHolders(Partner $partner, $lastLoginCutoffDate, $notifiedRangeStart, $notifiedRangeEnd)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        
        $accHolders = $this->app->myaccountHolderStore()
                                ->searchTable()
                                ->select()
                                ->where(function ($query) use ($lastLoginCutoffDate, $notifiedRangeStart, $notifiedRangeEnd) {
                                    $query->where('lastloginon', '<=', $lastLoginCutoffDate->format('Y-m-d H:i:s'));
                                    $query->orWhere(function ($q) use ( $notifiedRangeStart, $notifiedRangeEnd) {
                                        $q->where('lastnotifiedon', '>=', $notifiedRangeStart->format('Y-m-d H:i:s'));
                                        $q->orWhere('lastnotifiedon', '<=', $notifiedRangeEnd->format('Y-m-d H:i:s'));
                                    });
                                })
                                ->where('partnerid', $partner->id)
                                ->where('status', MyAccountHolder::STATUS_ACTIVE)
                                ->execute();

        $dormantAccHolders = [];

        foreach ($accHolders as $accHolder) {
            if ($settings->minbalancexau > $accHolder->getCurrentGoldBalance()) {
                $dormantAccHolders[] = $accHolder;
            }
        }

        return $dormantAccHolders;
    }

    /**
     * Notify account holder to activate their account
     *
     * @param  MyAccountHolder $accHolder
     * @return void
     */
    protected function notifyDormantAccount($accHolder)
    {
        $now = new \DateTime('now');
        $now->setTimezone($this->app->getUserTimezone());   
        $accHolder->lastnotifiedon = $now;
        $accHolder = $this->app->myaccountHolderStore()->save($accHolder);
        $projectBase = $this->app->getConfig()->{'projectBase'};

        $partnerinfo = $this->getsendernamesenderemail($accHolder);
        $email = '';
        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv) {
            $email = $partnerinfo['projectemail'];
        }

        $email .= ',' . $accHolder->email;

        $closeAccountCutoff = $this->app->getConfig()->{'mygtp.accountclosure.closeaccountcutoff'} ?? '36 months';
        $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accHolder->partnerid);

        $lastTransaction = $this->app->myledgerStore()
                ->searchTable()
                ->select(['transactiondate'])
                ->where('accountholderid', $accHolder->id)
                ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])                                
                ->orderBy('id', 'DESC')
                ->one();        

        if (! $lastTransaction) {
            $date = Common::convertUserDatetimeToUTC($accHolder->createdon);
            $notifyStartDate = new \DateTime($date->format('Y-m-d H:i:s'));
            $notifyEndDate   = new \DateTime($date->format('Y-m-d H:i:s'));
        } else {
            $notifyStartDate = new \DateTime($lastTransaction->transactiondate->format('Y-m-d H:i:s'));
            $notifyEndDate   = new \DateTime($lastTransaction->transactiondate->format('Y-m-d H:i:s'));
        }

        $notifyStartDate->add(\DateInterval::createFromDateString($lastTransactionsCutoff));
        $notifyStartDate->setTimezone($this->app->getUserTimezone());
        $notifyEndDate->add(\DateInterval::createFromDateString($closeAccountCutoff));
        // Eg. 1 month / 1 day
        $notifyEndDate->sub(\DateInterval::createFromDateString('1 ' . preg_replace('/[0-9]+/', '', $closeAccountCutoff)));
        $notifyEndDate->setTimezone($this->app->getUserTimezone());

        $this->notify(new IObservation($accHolder, IObservation::ACTION_FREEZE, MyAccountHolder::STATUS_ACTIVE, [
            'event' => MyGtpEventConfig::EVENT_REMIND_DORMANT_ACCOUNT,
            'accountholderid'   => $accHolder->id,
            'projectBase'       => $projectBase,
            'name'              => $accHolder->fullname,
            'email'             => $accHolder->email,
            'receiver'          => $email,
            'cutoff'            => $lastTransactionsCutoff,
            'startdate'         => $notifyStartDate->format('d/m/Y'),
            'enddate'           => $notifyEndDate->format('d/m/Y'),
            'minxau'            => number_format($settings->minbalancexau, 3),
            'sendername'            => $partnerinfo['sendername'],
            'senderemail'           => $partnerinfo['senderemail'],
        ]));

        return $accHolder;
    }

    /**
     * Process dormant account holders
     *
     * @param Partner           $partner             Account holder patner to process
     * @param MyAccountHolder[] $dormantAccHolders   Dormant account holders to process
     * @param MyCloseReason     $reason              Reason
     * @param string            $apiVersion          The last login cut off used to determine account as dormant
     * @return void
     */
    protected function processDormantAccountHolders(Partner $partner, $dormantAccHolders, $reason, $apiVersion, $transactionMgr = null, $storageMgr = null)
    {
        $closeAccountCutoff = $this->app->getConfig()->{'mygtp.accountclosure.closeaccountcutoff'} ?? '36 months';
        $dateLastTransaction = new \DateTime($closeAccountCutoff . ' ago');

        // Find last transactions in 12 months period
        $lastTransactions = $this->app->myledgerStore()
                ->searchTable()
                ->select(['accountholderid'])
                ->whereIn('accountholderid', array_keys($dormantAccHolders))
                ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])                
                ->where('transactiondate','>', $dateLastTransaction->format('Y-m-d H:i:s'))
                ->where('status', MyLedger::STATUS_ACTIVE)
                ->groupBy('accountholderid')
                ->forwardKey('accountholderid')
                ->get();
        
        $accHolders = $this->app->myaccountholderStore()
                ->searchTable()
                ->select(['id'])
                ->whereIn('id', array_keys($dormantAccHolders))
                ->where('createdon','>', $dateLastTransaction->format('Y-m-d H:i:s'))
                ->forwardKey('id')
                ->get();

        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        $systemAccountId = $this->app->getConfig()->{'mygtp.accountclosure.systemaccountid'};
        $emailPattern = $this->app->getConfig()->{'mygtp.accountclosure.testemailpattern'} ?? '@silverstream.my';

        // $maxNotify = $this->app->getConfig()->{'mygtp.accountclosure.maxnotify'};
        foreach ($dormantAccHolders as $accHolder) {
            

            if ($systemAccountId == $accHolder->id) {
                $this->log(__METHOD__ . "(): Skipping system account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);
                continue;
            }

            // Test for silverstream email only
            if ($developmentEnv && !preg_match($emailPattern, $accHolder->email)) {
                continue;
            }

            $this->log(__METHOD__ . "(): Processing dormant account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);

            try {
                // If account last transaction <= 12 months
                if (! isset($lastTransactions[$accHolder->id]) && ! isset($accHolders[$accHolder->id])) {
                    $this->log(__METHOD__ . "(): Proceed to notify close dormant account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);
                    $this->closeAccount($partner, $accHolder, $reason, $apiVersion, null, true, 'Dormant', true, $transactionMgr, $storageMgr);
                } else {
                    $this->log(__METHOD__ . "(): Notifying dormant account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);
                    $this->notifyDormantAccount($accHolder);
                }
            
            } catch (\Exception $e) {
                $this->log(__METHOD__ . "(): Error processing dormant account holder ({$accHolder->accountholdercode}), {$e->getMessage()}", SNAP_LOG_ERROR);
            }
        }        
    }

    public function checkDormantAccount(Partner $partner, $apiVersion, $transactionMgr = null, $storageMgr = null)
    {
        $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
        $lastNotifiedCutoff     = $this->app->getConfig()->{'mygtp.accountclosure.lastnotifiedcutoff'} ?? '1 month';

        $dateStart           = new \DateTime('now');
        $dateLastTransaction = new \DateTime($lastTransactionsCutoff . ' ago');
        $dateLastNotified    = new \DateTime($lastNotifiedCutoff . ' ago');
        
        // To cater for notification that are later than cutoff
        $dateLastNotified->modify('+1 hour');

        $limit = 1000;

        if($app->getConfig()->{'otc.job.diffserver'} == '1'){
            $arr_partner = $this->app->partnerStore()->searchTable()->select()->where('group', $partner->id)->execute();
            $ids = [];
            foreach($arr_partner as $record){
                array_push($ids, $record->id);
            }

            $count = $this->app->myaccountholderStore()
            ->searchView()
            ->select()
            ->where('partnerid', "IN", $ids)
            ->where(function ($q) use ($dateLastTransaction) {
                $q->where(function ($r) {
                    $r->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);
                    $r->where(function ($j) {
                        $j->where('xaubalance', '<=', 0);
                        $j->orWhereNull('xaubalance');
                    });
                });
                $q->orWhere(function ($r) use ($dateLastTransaction) {
                    $r->where('investmentmade', MyAccountHolder::INVESTMENT_NONE);
                    $r->where('createdon', '<=', $dateLastTransaction->format('Y-m-d H:i:s'));
                });
            })
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where(function ($q) use ($dateLastNotified) {
                $q->where('lastnotifiedon', '<=', $dateLastNotified->format('Y-m-d H:i:s'));
                $q->orWhereNull('lastnotifiedon');
            })
            ->count();
        }
        else{
            $count = $this->app->myaccountholderStore()
            ->searchView()
            ->select()
            ->where('partnerid', $partner->id)
            ->where(function ($q) use ($dateLastTransaction) {
                $q->where(function ($r) {
                    $r->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);
                    $r->where(function ($j) {
                        $j->where('xaubalance', '<=', 0);
                        $j->orWhereNull('xaubalance');
                    });
                });
                $q->orWhere(function ($r) use ($dateLastTransaction) {
                    $r->where('investmentmade', MyAccountHolder::INVESTMENT_NONE);
                    $r->where('createdon', '<=', $dateLastTransaction->format('Y-m-d H:i:s'));
                });
            })
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where(function ($q) use ($dateLastNotified) {
                $q->where('lastnotifiedon', '<=', $dateLastNotified->format('Y-m-d H:i:s'));
                $q->orWhereNull('lastnotifiedon');
            })
            ->count();
        }

        $totalPages = ceil($count / $limit);

        // Chunk query
        for($page = 0; $page < $totalPages; $page++) {
            if($app->getConfig()->{'otc.job.diffserver'} == '1'){
                $arr_partner = $this->app->partnerStore()->searchTable()->select()->where('group', $partner->id)->execute();
                $ids = [];
                foreach($arr_partner as $record){
                    array_push($ids, $record->id);
                }
                
                $accHolders = $this->app->myaccountholderStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid', "IN", $ids)
                    ->where(function ($q) use ($dateLastTransaction) {
                        $q->where(function ($r) {
                            $r->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);
                            $r->where(function ($j) {
                                $j->where('xaubalance', '<=', 0);
                                $j->orWhereNull('xaubalance');
                            });
                        });
                        $q->orWhere(function ($r) use ($dateLastTransaction) {
                            $r->where('investmentmade', MyAccountHolder::INVESTMENT_NONE);
                            $r->where('createdon', '<=', $dateLastTransaction->format('Y-m-d H:i:s'));
                        });
                    })
                    ->where('status', MyAccountHolder::STATUS_ACTIVE)
                    ->where(function ($q) use ($dateLastNotified) {
                        $q->where('lastnotifiedon', '<=', $dateLastNotified->format('Y-m-d H:i:s'));
                        $q->orWhereNull('lastnotifiedon');
                    })
                    ->page($page, $limit)
                    ->forwardKey('id')
                    ->get();            

                $withTransactionIds = $this->app->myledgerStore()
                    ->searchTable()
                    ->select(['accountholderid'])
                    ->whereIn('accountholderid', array_keys($accHolders))
                    ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])
                    ->where('transactiondate','<=', $dateStart->format('Y-m-d H:i:s'))
                    ->where('transactiondate','>', $dateLastTransaction->format('Y-m-d H:i:s'))
                    ->where('status', MyLedger::STATUS_ACTIVE)
                    ->groupBy('accountholderid')
                    ->forwardKey('accountholderid')
                    ->get();

                // Return all ids which doesnot have transactions
                $dormantAccHolders = array_diff_key($accHolders, $withTransactionIds);
                $this->processDormantAccountHolders($partner, $dormantAccHolders, null, $apiVersion, $transactionMgr, $storageMgr);
            }
            else{
                $accHolders = $this->app->myaccountholderStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid', $partner->id)
                    ->where(function ($q) use ($dateLastTransaction) {
                        $q->where(function ($r) {
                            $r->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);
                            $r->where(function ($j) {
                                $j->where('xaubalance', '<=', 0);
                                $j->orWhereNull('xaubalance');
                            });
                        });
                        $q->orWhere(function ($r) use ($dateLastTransaction) {
                            $r->where('investmentmade', MyAccountHolder::INVESTMENT_NONE);
                            $r->where('createdon', '<=', $dateLastTransaction->format('Y-m-d H:i:s'));
                        });
                    })
                    ->where('status', MyAccountHolder::STATUS_ACTIVE)
                    ->where(function ($q) use ($dateLastNotified) {
                        $q->where('lastnotifiedon', '<=', $dateLastNotified->format('Y-m-d H:i:s'));
                        $q->orWhereNull('lastnotifiedon');
                    })
                    ->page($page, $limit)
                    ->forwardKey('id')
                    ->get();            

                $withTransactionIds = $this->app->myledgerStore()
                    ->searchTable()
                    ->select(['accountholderid'])
                    ->whereIn('accountholderid', array_keys($accHolders))
                    ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])
                    ->where('transactiondate','<=', $dateStart->format('Y-m-d H:i:s'))
                    ->where('transactiondate','>', $dateLastTransaction->format('Y-m-d H:i:s'))
                    ->where('status', MyLedger::STATUS_ACTIVE)
                    ->groupBy('accountholderid')
                    ->forwardKey('accountholderid')
                    ->get();

                // Return all ids which doesnot have transactions
                $dormantAccHolders = array_diff_key($accHolders, $withTransactionIds);
                $this->processDormantAccountHolders($partner, $dormantAccHolders, null, $apiVersion, $transactionMgr, $storageMgr);
            }
            
        }
    }

    /**
     * Logs out an account holder
     * 
     * @param MyAccountHolder $accHolder        The account holder
     * @param string          $tokenStr         The token string
     * 
     * @return void
     */
    public function logoutAccountHolder(MyAccountHolder $accHolder, $tokenStr)
    {
        $success = $this->app->mygtptokenManager()->invalidateToken($accHolder, $tokenStr, MyToken::TYPE_ACCESS);
        if (!$success) {
            throw GeneralException::fromTransaction([], ['message' => 'Unable to log out']);
        }
    }

    /**
     * **FOR DEVELOPMENT ENV** 
     * Disables/deletes an account holder from being used.
     * Changes email, NRIC to invalid numbers to prevent usage
     */
    public function disableAccountHolder(MyAccountHolder $accHolder)
    {
        $isDev = filter_var($this->app->getConfig()->{'development'}, FILTER_VALIDATE_BOOLEAN);
        $accountHolderStore = $this->app->myaccountholderStore();

        if (!$isDev) {
            throw \Snap\api\exception\GeneralException::fromTransaction("Not in development environment");
        }

        // Invalidate all tokens for the account holder
        $tokens = $this->app->mytokenStore()->searchTable()->select()
                        ->where('accountholderid', $accHolder->id)
                        ->andWhere('status', MyToken::STATUS_ACTIVE)
                        ->execute();

        $now = new \DateTime('now');
        foreach ($tokens as $token) {
            $token->expireon = $now->format('Y-m-d H:i:s');
            $token->status = MyToken::STATUS_INACTIVE;
            $this->app->mytokenStore()->save($token);
        }

        $accHolder->mykadno .= "-DISABLED";
        $accHolder->email   .= "-DISABLED";
        $accHolder->password.= "-DISABLED";
        $accHolder->phoneno .= "-DISABLED";
        $accHolder->status   = MyAccountHolder::STATUS_CLOSED;
        $accHolder->statusremarks = "Account deleted";
        $accHolder = $accountHolderStore->save($accHolder);
        return $accHolder;
    }

    /**
     * Send notification to the account holder about the incomplete profile
     *
     * @return void
     */
    public function remindIncompleteProfile(MyAccountHolder $accHolder)
    {
        $this->log(__METHOD__ . "(): Reminding account holder ({$accHolder->id}) for incomplete profile", SNAP_LOG_DEBUG);

        $this->notify(new IObservation($accHolder, IObservation::ACTION_VERIFY, MyAccountHolder::STATUS_ACTIVE, [
            'event' => MyGtpEventConfig::EVENT_REMIND_INCOMPLETE_PROFILE,
            'accountholderid'   => $accHolder->id,
            'projectBase'       => $this->app->getConfig()->{'projectBase'},
            'name'              => $accHolder->fullname,
            'email'             => $accHolder->email
        ]));
    }

    /**
     * Get list of account holders with incomplete profile for partner
     * 
     * @param Partner $partner
     *
     * @return array
     */
    public function getIncompleteProfileAccountHolders(Partner $partner)
    {
        $this->log(__METHOD__ . "(): Getting account holders with incomplete profile", SNAP_LOG_DEBUG);

        return $accHolderWithoutAddress = $this->app->myaccountholderStore()
            ->searchView()
            ->select()
            ->where('status', MyAccountHolder::STATUS_ACTIVE)
            ->where('partnerid', $partner->id)
            ->where(function ($q) {
                $q->whereNull('bankcode');
                $q->orWhereNull('addressline1');
            })            
            ->where(function ($q) {
                $q->whereNull('bankid');
                $q->orWhereNull('accountname');
                $q->orWhereNull('accountnumber');
                $q->orWhereNull('addressline1');
            })
            ->execute();
    }
    
    /**
     * Get the PDF file for the pep person using Person Id, person id can be retrieved using getPepMatches()
     *
     * @param  Partner $partner
     * @param  int $personId
     * @return string
     */
    public function getPepPdfForPerson(Partner $partner, $personId)
    {
        $provider = $this->getPartnerEkycProvider($partner);
        $mypepperson = $provider->getPepPdf($personId);

        return $mypepperson->file;
    }

    /**
     * Get the person pep record using Person Id, person id can be retrieved using getPepMatches()
     *
     * @param  Partner $partner
     * @param  int $personId
     * @return array
     */
    public function getPepJsonForPerson(Partner $partner, $personId)
    {
        $provider = $this->getPartnerEkycProvider($partner);
        $mypepperson = $provider->getPepJson($personId);

        return json_decode($mypepperson->file, true);
    }

    /**
     * Get the list of PEP matches for the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  Partner $partner
     * @return array
     */
    public function getPepMatches(Partner $partner, MyAccountHolder $accountHolder, $params = [])
    {
        $app = $this->app;
        $provider = $this->getPartnerEkycProvider($partner);
        $myPepSearchResult = $provider->searchForPepRecords($app, $accountHolder, $params);

        return json_decode($myPepSearchResult->response, true);
    }

    /**
     * Approve account holder PEP status
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function approveAccountHolder(MyAccountHolder $accountHolder, $remarks = null)
    {
        // $initialPepStatus = $accountHolder->pepstatus;
        $accountHolder->pepstatus = MyAccountHolder::PEP_PASSED;
        $accountHolder->statusremarks = $remarks;
        $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_APPROVE,
            0,
            [
                'event' => MyGtpEventConfig::EVENT_PEP_PASSED,
                'projectbase' => $partnerinfo['projectBase'],
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'accountholderid' => $accountHolder->id,
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]
        );

        $this->notify($observation);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

     /**
     * Approve account holder EKYC status
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function approveAccountHolderEKYC(MyAccountHolder $accountHolder, $remarks = null)
    {

        
        $approverUserId = $this->app->getUserSession()->getUser()->id;

        // Get Accountholder 
        if ($accountHolder->kycstatus != MyAccountHolder::KYC_FAILED) {
            $this->log(__METHOD__ . ":  Unable to proceed action due to account holder has KYC previously", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to account holder has made previous KYC records")]);
        }

        // $initialPepStatus = $accountHolder->pepstatus;
        $accountHolder->kycstatus = MyAccountHolder::KYC_PASSED;
        $accountHolder->statusremarks = $remarks;
        //Save active status for kyc manual approve
        $accountHolder->iskycmanualapproved = MyAccountHolder::STATUS_ACTIVE;
        $partnerinfo = $this->getsendernamesenderemail($accountHolder);

        // Generate Logs for account holder ekyc manual update
        // Create mew entry

        // Set approve date ( Time is not saved )
        $approveDate = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
        $approveDate->setTimezone($this->app->getUserTimezone());

        // Do amla check 
        // AMLA Check next
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $accountHolder->partnerid);

        if ($accountHolder->ekycPassed() && $accountHolder->amlaPending()) {

            $immediatelyBlacklist = filter_var($settings->amlablacklistimmediately, FILTER_VALIDATE_BOOLEAN);

            // Send event to process AMLA
            $this->app->startCLIJob("AmlaCheckJob.php", ['accountholderid' => $accountHolder->id, 'immediatelyblacklist' => $immediatelyBlacklist]);
            // $this->notify(new IObservation($accHolder, IObservation::ACTION_OTHER, $oldStatus, ['event' => MyGtpEventConfig::EVENT_AMLA_CHECK]));
        }
        
        // Init receivers
        // Send to ace
        // $receiverEmail = $partnerinfo['projectemail'];
        // Send to accholder
        // $receiverEmail .= ',' . $accountHolder->email;
    
        $kycOperatorLogs = $this->app->mykycoperatorlogsStore()->create([
            'type'   => MyKYCOperatorLogs::TYPE_APPROVE,
            'accountholderid'         => $accountHolder->id,
            'remarks'                => $remarks,
            'approvedby'             => $approverUserId,
            'approvedon'           => $approveDate,
            'status'               => MyKYCOperatorLogs::STATUS_ACTIVE,
            // 'xau'               => $now->format('Y-m-d H:i:s'),
            // 'status'            => MyLedger::STATUS_ACTIVE
        ]);

        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_APPROVE,
            MyAccountHolder::KYC_FAILED,
            [
                'event' => MyGtpEventConfig::EVENT_EKYC_PASSED,
                'projectbase' => $partnerinfo['projectBase'],
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'accountholderid' => $accountHolder->id,
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]
        );

        $this->notify($observation);

        $kycOperatorLogs = $this->app->mykycoperatorlogsStore()->save($kycOperatorLogs);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Reject account holder PEP status
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function rejectAccountHolder(MyAccountHolder $accountHolder, $remarks)
    {        
        // $initialPepStatus = $accountHolder->pepstatus;
        $accountHolder->pepstatus = MyAccountHolder::PEP_FAILED;
        $accountHolder->statusremarks = $remarks;
        $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_REJECT,
            0,
            [
                'projectbase' => $partnerinfo['projectBase'],
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'accountholderid' => $accountHolder->id,
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]
        );

        $this->notify($observation);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Blacklist account holder and set AMLA as failed
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function blacklistAccountHolder(MyAccountHolder $accountHolder, $remarks = null)
    {
        $sm = $this->getAccountHolderStateMachine($accountHolder);

        if (! $sm->can(MyAccountHolder::STATUS_BLACKLISTED)) {
            $this->log(__METHOD__ . ":  Unable to proceed action due to account holder status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid account holder status")]);
        }

        $accountHolder->amlastatus = MyAccountHolder::AMLA_FAILED;
        $accountHolder->status     = MyAccountHolder::STATUS_BLACKLISTED;
        $accountHolder->verifiedon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
        $accountHolder->statusremarks = $remarks;
        $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_OPERATORREJECT,
            0,
            [
                'projectbase' => $partnerinfo['projectBase'],
                'accountholdercode' => $accountHolder->accountholdercode,
                'mykadno' => $accountHolder->mykadno,
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]
        );

        $this->notify($observation);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Unblacklist account holder and set AMLA as passed
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function unBlacklistAccountHolder(MyAccountHolder $accountHolder, $remarks = null)
    {        
        $sm = $this->getAccountHolderStateMachine($accountHolder);

        if (! $sm->can(MyAccountHolder::STATUS_ACTIVE)) {
            $this->log(__METHOD__ . ":  Unable to proceed action due to account holder status", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid account holder status")]);
        }

        $accountHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
        $accountHolder->status     = MyAccountHolder::STATUS_ACTIVE;
        $accountHolder->verifiedon = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
        $accountHolder->statusremarks = $remarks;
        $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        $observation = new \Snap\IObservation(
            $accountHolder,
            \Snap\IObservation::ACTION_OPERATORAPPROVE,
            0,
            [
                'projectbase' => $partnerinfo['projectBase'],
                'accountholdercode' => $accountHolder->accountholdercode,
                'mykadno' => $accountHolder->mykadno,
                'name' => $accountHolder->fullname,
                'receiver' => $accountHolder->email,
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
            ]
        );

        $this->notify($observation);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Activate account holder and set dormant status to false
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function activateDormantAccountHolder(MyAccountHolder $accountHolder, $remarks = null)
    {
        // $sm = $this->getAccountHolderStateMachine($accountHolder);

        // if (! $sm->can(MyAccountHolder::STATUS_ACTIVE)) {
        //     $this->log(__METHOD__ . ":  Unable to proceed action due to account holder status", SNAP_LOG_ERROR);
        //     throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid account holder status")]);
        // }

        // $accountHolder->dormant = MyAccountHolder::STATUS_INACTIVE;
        $accountHolder->status = MyAccountHolder::STATUS_ACTIVE;
        $accountHolder->statusremarks = $remarks;

        $accountClosure = $this->app->myaccountclosureStore()->getByField('accountholderid', $accountHolder->id);
        if ($accountClosure) {
            $accountClosure->status = MyAccountClosure::STATUS_REACTIVATED;
            $accountClosure = $this->app->myaccountclosureStore()->save($accountClosure);
        }

        // $partnerinfo = $this->getsendernamesenderemail($accountHolder);
        // $observation = new \Snap\IObservation(
        //     $accountHolder,
        //     \Snap\IObservation::ACTION_PRINT,
        //     0,
        //     [
        //         'projectbase' => $partnerinfo['projectBase'],
        //         'accountholdercode' => $accountHolder->accountholdercode,
        //         'mykadno' => $accountHolder->mykadno,
        //         'name' => $accountHolder->fullname,
        //         'receiver' => $accountHolder->email,
        //         'sendername'            => $partnerinfo['sendername'],
        //         'senderemail'           => $partnerinfo['senderemail'],
        //     ]
        // );

        // $this->notify($observation);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Get all pending xau debits that are pending payment
     * 
     * @param MyAccountHolder $accHolder 
     * @return float
     */
    public function getPendingPaymentXauDebits(MyAccountHolder $accHolder)
    {
        $total = 0;

        // Get pending conversions
        $pendingConversionSum = $this->app->myconversionStore()->searchView()->select()
                                ->where('accountholderid', $accHolder->id)
                                ->andWhere('status', MyConversion::STATUS_PAYMENT_PENDING)
                                ->sum('rdmtotalweight');
        $total += $pendingConversionSum;

        // Others 

        return $total;
    }

    public function getUnsuccessfullRegistrationReport(Partner $partner = null, \DateTime $dateStart = null, \DateTime $dateEnd = null)
    {
        $conditions = function ($q) use ($partner, $dateStart, $dateEnd) {
            
            if ($partner) {
                $q->where('partnerid', $partner->id);
            }

            if ($dateStart) {
                $q->where('createdon', '>=', $dateStart->format('Y-m-d H:i:s'));
            } 
    
            if ($dateEnd) {
                $q->where('createdon', '<=', $dateEnd->format('Y-m-d H:i:s'));
            }

            $q->where(function ($r) {
                $r->where('kycstatus', MyAccountHolder::KYC_FAILED);
                $r->orWhere('amlastatus', MyAccountHolder::AMLA_FAILED);
                $r->orWhere('pepstatus', MyAccountHolder::PEP_FAILED);
            });
        };

        $store = $this->app->myaccountholderStore();
        $prefix = $store->getColumnPrefix();

        $kyc = new Expression("CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed'" .
            "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'" .
            "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'" .
            "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'" .
            "ELSE 'Pending' END as `{$prefix}kycstatus`");
        $kyc->original = 'kycstatus';

        $amla = new Expression("CASE WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_FAILED . " THEN 'Failed'" .
            "WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PASSED . " THEN 'Passed'" .
            "WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PENDING . " THEN 'Pending'" .
            "ELSE 'Pending' END as `{$prefix}amlastatus`");
        $amla->original = 'amlastatus';
        

        $pep = new Expression("CASE WHEN `{$prefix}ispep` <> " . MyAccountHolder::PEP_FLAG . " THEN 'N/A'" .
            "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'" .
            "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'" .
            "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed'" .
            "ELSE 'Pending' END as `{$prefix}pepstatus`");
        $pep->original = 'pepstatus';

        $header = [
            (object) ['text' => 'ID', 'index' => 'id'],
            (object) ['text' => 'Full Name', 'index' => 'fullname'],
            (object) ['text' => 'NRIC', 'index' => 'mykadno'],
            (object) ['text' => 'Account Code', 'index' => 'accountholdercode'],
            (object) ['text' => 'Email', 'index' => 'email'],
            (object) ['text' => 'Phone', 'index' => 'phoneno'],
            (object) ['text' => 'Occupation Category', 'index' => 'occupationcategory'],
            (object) ['text' => 'Occupation Subcategory', 'index' => 'occupationsubcategory'],
            (object) ['text' => 'Address Line 1', 'index' => 'addressline1'],
            (object) ['text' => 'Address Line 2', 'index' => 'addressline2'],
            (object) ['text' => 'Address City', 'index' => 'addresscity'],
            (object) ['text' => 'Address Postcode', 'index' => 'addresspostcode'],
            (object) ['text' => 'Address State', 'index' => 'addressstate'],
            (object) ['text' => 'KYC', 'index' => $kyc],
            (object) ['text' => 'KYC Remarks', 'index' => 'kycremarks'],
            (object) ['text' => 'AMLA', 'index' => $amla],
            (object) ['text' => 'Source', 'index' => 'amlasourcetype'],
            (object) ['text' => 'PEP', 'index' => $pep],
            (object) ['text' => 'Status Remarks', 'index' => 'statusremarks'],
        ];


        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        return $reportingManager->generateMyGtpReport($store, $header, null, null, $conditions, null, 2, false, true);
    }

    public function sendEmail($subject, $bodyEmail, $attachment, $attachmentName = null, $recipient)
    {
        $mailer = $this->app->getMailer();

        $mailer->addAddress($recipient);
        $mailer->addAttachment($attachment, $attachmentName);

        $mailer->Subject = $subject;
        $mailer->Body    = $bodyEmail;
        $mailer->send();
    }

    /**
     * Update the account holder partner related data
     *
     * @param MyAccountHolder $accHolder
     * @param string $partnerCusId
     * @param string $partnerCusType
     * @param string $partnerCampaignCode
     * @return void
     */
    public function updatePartnerCustomerData($accHolder, $partnerCusId, $partnerData)
    {
        $accHolder->partnercusid = $partnerCusId ?? null;
        $accHolder->partnerdata = 0 < strlen($partnerData) ? $partnerData : null;

        return $this->app->myaccountholderStore()->save($accHolder);
    }

    /*********************
     *                   *
     * Utility Functions *
     *                   *
     *********************/

    /**
     * Gets available balance for account holder
     * 
     * @param MyAccountHolder $accHolder
     * 
     * @return string
     */
    public function getAccountHolderAvailableGoldBalance(MyAccountHolder $accHolder)
    {
        $currentBal = strval($accHolder->getCurrentGoldBalance());
        $fees = strval($this->getAccountHolderUnchargedStorageFees($accHolder));

        $calc = $accHolder->getPartner()->calculator(false);
        return $calc->sub($currentBal, $fees);
    }

    /**
     * Custom algorithm to generate unique peppered passwords.
     * Salt is automatically generated using password_hash() with BCRYPT option
     *
     * @param string $pepperDerive        A string to derive the pepper from
     * @param string $password          The password to be hashed with salt
     *
     * @return string       The salted password to be hashed
     */
    public function generatePepperedPassword($pepperDerive, $password)
    {
        $key = $this->app->getAppName();
        $pepper = substr(hash_hmac("sha256", $pepperDerive, $key), 0, 5);
        return $pepper + $password;
    }


    /**
     * Generate hashed password using password_hash with BCRYPT option.
     * Salt is determined by PHP and is included in the returned password.
     *
     * @param string $password      The password to be hashed
     *
     * @return string|false|null
     */
    public function generateHashedPassword($password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost'  => 12   // default is 10
        ]);
    }

    /**
     *
     * @return \Finite\StateMachine\StateMachine
     */
    public function getKycSubmissionStateMachine(MyKYCSubmission $submission)
    {
        $config       = [
            'property_path' => 'status',
            'states' => [
                MyKYCSubmission::STATUS_INCOMPLETE          => ['type' => 'initial', 'properties' => []],
                MyKYCSubmission::STATUS_PENDING_SUBMISSION  => ['type' => 'normal',  'properties' => []],
                MyKYCSubmission::STATUS_PENDING_RESULT      => ['type' => 'normal',  'properties' => []],
                MyKYCSubmission::STATUS_INACTIVE            => ['type' => 'final',   'properties' => []],
                MyKYCSubmission::STATUS_COMPLETE            => ['type' => 'final',   'properties' => []],
            ],
            'transitions' => [
                MyKYCSubmission::STATUS_INCOMPLETE          => ['from' => [MyKYCSubmission::STATUS_INACTIVE, MyKYCSubmission::STATUS_INCOMPLETE], 'to' => MyKYCSubmission::STATUS_INCOMPLETE],
                MyKYCSubmission::STATUS_PENDING_SUBMISSION  => ['from' => [MyKYCSubmission::STATUS_INCOMPLETE, MyKYCSubmission::STATUS_PENDING_SUBMISSION], 'to' => MyKYCSubmission::STATUS_PENDING_SUBMISSION],
                MyKYCSubmission::STATUS_PENDING_RESULT      => ['from' => [MyKYCSubmission::STATUS_PENDING_SUBMISSION], 'to' => MyKYCSubmission::STATUS_PENDING_RESULT],
                MyKYCSubmission::STATUS_INACTIVE            => ['from' => [MyKYCSubmission::STATUS_INCOMPLETE, MyKYCSubmission::STATUS_PENDING_SUBMISSION, MyKYCSubmission::STATUS_PENDING_RESULT], 'to' => MyKYCSubmission::STATUS_INACTIVE],
                MyKYCSubmission::STATUS_COMPLETE            => ['from' => [MyKYCSubmission::STATUS_PENDING_RESULT], 'to' => MyKYCSubmission::STATUS_COMPLETE],
            ]
        ];

        return $this->createStateMachine($submission, $config);
    }

    /**
     *
     * @return \Finite\StateMachine\StateMachine
     */
    public function getAccountClosureStateMachine(MyAccountClosure $accountClosure)
    {
        $config       = [
            'property_path' => 'status',
            'states' => [
                MyAccountClosure::STATUS_PENDING     => ['type' => 'initial', 'properties' => []],
                MyAccountClosure::STATUS_IN_PROGRESS => ['type' => 'normal',  'properties' => []],
                MyAccountClosure::STATUS_REJECTED    => ['type' => 'final',   'properties' => []],
                MyAccountClosure::STATUS_APPROVED    => ['type' => 'final',   'properties' => []],
                MyAccountClosure::STATUS_REACTIVATED => ['type' => 'final',   'properties' => []],
            ],
            'transitions' => [
                MyAccountClosure::STATUS_IN_PROGRESS => ['from' => [MyAccountClosure::STATUS_PENDING], 'to' => MyAccountClosure::STATUS_IN_PROGRESS],
                MyAccountClosure::STATUS_APPROVED    => ['from' => [MyAccountClosure::STATUS_IN_PROGRESS, MyAccountClosure::STATUS_PENDING], 'to' => MyAccountClosure::STATUS_APPROVED],
                MyAccountClosure::STATUS_REJECTED    => ['from' => [MyAccountClosure::STATUS_IN_PROGRESS, MyAccountClosure::STATUS_PENDING], 'to' => MyAccountClosure::STATUS_REJECTED],
                MyAccountClosure::STATUS_REACTIVATED => ['from' => [MyAccountClosure::STATUS_APPROVED, MyAccountClosure::STATUS_REJECTED], 'to' => MyAccountClosure::STATUS_REACTIVATED],
            ]
        ];

        return $this->createStateMachine($accountClosure, $config);
    }

    /**
     *
     * @return \Finite\StateMachine\StateMachine
     */
    public function getAccountHolderStateMachine(MyAccountHolder $accHolder)
    {
        $config       = [
            'property_path' => 'status',
            'states' => [
                MyAccountHolder::STATUS_INACTIVE    => ['type' => 'initial', 'properties' => []],
                MyAccountHolder::STATUS_ACTIVE      => ['type' => 'normal', 'properties' => []],
                MyAccountHolder::STATUS_SUSPENDED   => ['type' => 'normal',  'properties' => []],
                MyAccountHolder::STATUS_BLACKLISTED => ['type' => 'normal',  'properties' => []],
                MyAccountHolder::STATUS_CLOSED      => ['type' => 'final',   'properties' => []],
            ],
            'transitions' => [
                MyAccountHolder::STATUS_ACTIVE      => ['from' => [MyAccountHolder::STATUS_INACTIVE, MyAccountHolder::STATUS_SUSPENDED, MyAccountHolder::STATUS_BLACKLISTED], 'to' => MyAccountHolder::STATUS_ACTIVE],
                MyAccountHolder::STATUS_SUSPENDED   => ['from' => [MyAccountHolder::STATUS_INACTIVE, MyAccountHolder::STATUS_ACTIVE], 'to' => MyAccountHolder::STATUS_SUSPENDED],
                MyAccountHolder::STATUS_BLACKLISTED => ['from' => [MyAccountHolder::STATUS_INACTIVE, MyAccountHolder::STATUS_ACTIVE], 'to' => MyAccountHolder::STATUS_BLACKLISTED],
                MyAccountHolder::STATUS_CLOSED      => ['from' => [MyAccountHolder::STATUS_INACTIVE, MyAccountHolder::STATUS_ACTIVE], 'to' => MyAccountHolder::STATUS_CLOSED],
            ]
        ];

        return $this->createStateMachine($accHolder, $config);
    }

    /**
     * Returns a random string
     * 
     * @return string
     */
    protected function generateRandomAccountCode($prefix = "EG", $length = 8)
    {
        $random = bin2hex(random_bytes(floor($length/2)));
        $accountCode = $prefix . strtoupper($random);
        return $accountCode;
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

    /**
     * Save the new password for the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $newPassword
     * @return MyAccountHolder
     */
    private function savePassword(MyAccountHolder $accountHolder, $newPassword)
    {
        $now = new \DateTime();
        $now->setTimezone($this->app->getUserTimezone());

        // Save previous password hash
        $accountHolder->oldpassword = $accountHolder->password;

        // Save new password hash
        $accountHolder->password = $this->generateHashedPassword($newPassword);
        $accountHolder->passwordmodified = $now;

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Save the pincode either hashed or plainnumber
     *
     * @param  MyAccountHolder $account
     * @param  string          $pincode
     * @param  bool            $useHash
     * @return MyAccountHolder
     */
    private function savePincode(MyAccountHolder $accountHolder, string $newPincode, bool $useHash = true)
    {
        $accountHolder->pincode = $useHash ? $this->hashPincode($newPincode) : $newPincode;

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * This method return the hashed pincode number
     *
     * @param  string $pincode
     * @return string
     */
    private function hashPincode(string $pincode)
    {
        // If we are using the same hash algorithm as used for password
        // return $this->generateHashedPassword($pincode);
        return hash('sha256', $pincode);
    }

    /**
     * Validate the questionnaire array 
     *
     * @param  array $questionnaire
     * @return void
     */
    protected function validatePepQuestionnaire($questionnaire)
    {
        if (is_null($questionnaire)) {
            $this->log(__METHOD__ . "(): Missing questionnaire from param. Questionnaire is required", SNAP_LOG_ERROR);
            throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Questionnaire is required']);
        }

        if (!isset($questionnaire['metadata']) || !isset($questionnaire['questions']) || !is_array($questionnaire['questions'])) {
            $this->log(__METHOD__ . "(): Missing metadata or questions for questionnaire", SNAP_LOG_ERROR);
            throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing metadata or questions']);
        }

        $metadata = $questionnaire['metadata'];
        
        if (!isset($metadata['language']) || !in_array($metadata['language'], [MyAccountHolder::LANG_EN, MyAccountHolder::LANG_BM])) {
            $this->log(__METHOD__ . "(): Missing or invalid language provided for questionnaire", SNAP_LOG_ERROR);
            throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing or invalid language provided']);
        }

        
        if (!isset($metadata['version'])) {
            $this->log(__METHOD__ . "(): Missing version number for questionnaire", SNAP_LOG_ERROR);
            throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing version number']);
        }

        $schema = $this->getQuestionnaireSchema();        
        $supportedVersions = array_keys($schema);
        $version = $metadata['version'];

        if (!in_array($version, $supportedVersions)) {
            $this->log(__METHOD__ . "(): Invalid version number for questionnaire", SNAP_LOG_ERROR);
            throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Invalid version number, supported version are: (' . implode(', ', $supportedVersions) . ')']);
        }

        $questions = $questionnaire['questions'];        

        foreach ($questions as $question) {

            if (!isset($question['id']) || !is_numeric($question['id']) || !isset($question['answers']) || !is_array($question['answers'])) {
                $this->log(__METHOD__ . "(): Missing or invalid question id or answers", SNAP_LOG_ERROR);
                throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing or invalid question id or answers']);
            }

            $questionId = $question['id'];
            $answers = $question['answers'];

            
            $answersCount = count($answers);
            if ($answersCount < $schema[$version][$questionId]['min'] || $answersCount > $schema[$version][$questionId]['max']) {
                throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing or invalid count of answers were provided for question ' . $questionId]);
            }

            foreach ($answers as $answer) {
                if (!isset($answer['value']) || 0 === strlen($answer['value'])) {
                    throw MyGtpInvalidQuestionnaireAnswer::fromTransaction([], ['extra_message' => 'Missing property or value from answer for question ' . $questionId]);
                }                
            }
        }
    }

    /**
     * Get the questionnaire schema
     *
     * @return array
     */
    protected function getQuestionnaireSchema()
    {
        $this->log(__METHOD__ . "(): Getting questionnaire schema", SNAP_LOG_DEBUG);

        // [ Version => [ QuestionId => [...], ... ] ]
        $schema = [
            5 => [
                1 => [                    
                    'min' => 1,
                    'max' => 1,
                ],
                2 => [                    
                    'min' => 0,
                    'max' => 5,
                ],
                3 => [                    
                    'min' => 0,
                    'max' => 10,
                ],
                4 => [                    
                    'min' => 1,
                    'max' => 1,
                ],
                5 => [                    
                    'min' => 0,
                    'max' => 9,
                ],
            ]
        ];
        
        return $schema;
    }

    private function ipCanSendSms($skip = false)
    {
        if ($skip) return true;

        $ip = \Snap\Common::getRemoteIP();
        if (0 < strlen($ip) && "localhost" != $ip) {
            $key = "{smsretry}:".$ip;
            $exist = $this->app->getCache($key);
            return null == $exist;
        }

        return true;
    }

    private function getIpSmsTimeToExpire()
    {
        $ip = \Snap\Common::getRemoteIP();
        if (0 != strlen($ip)) {
            $key = "{smsretry}:$ip";
            $timeleft = $this->app->getCacher()->getEngine()->ttl($key);

            return $timeleft;
        }
        return 0;
    }

    /**
     * Increments the number of times this IP requested for sms
     */
    private function incrementIpSentSms()
    {
        $ip = \Snap\Common::getRemoteIP();
        if (0 != strlen($ip)) {
            $key = "{smsretry}:$ip";
            // Increment last failed amount and keep for 30 seconds
            $this->app->getCacher()->increment($key, 1, 30);
        }
    }

    /**
     * Increments the number of times close notification sent
     */
    private function incrementCloseNotify($accHolder)
    {
        $this->log(__METHOD__ . "(): Incrementing close notify for account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);
        $key = "{closenotify}:{$accHolder->id}";
        // Increment last notification and keep for 25 hours
        $num = $this->app->getCacher()->increment($key, 1, 25 * 60 * 60);
        $this->log(__METHOD__ . "(): Total close notify is {$num} for account holder ({$accHolder->accountholdercode})", SNAP_LOG_DEBUG);
        return $num;
    }

    /**
     * Update account holder loan through approval 
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function updateAccountHolderLoan(MyAccountHolder $accountHolder, Partner $partner, $loanTotal, $loanReference, $approveDate, $remarks = null)
    {        
        // $sm = $this->getAccountHolderStateMachine($accountHolder);

        // if (! $sm->can(MyAccountHolder::LOAN_APPROVED)) {
        //     $this->log(__METHOD__ . ":  Unable to proceed action due to account holder status", SNAP_LOG_ERROR);
        //     throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to invalid account holder status")]);
        // }
        
        // Get Approver ID
        $approverUserId = $this->app->getUserSession()->getUser()->id;

        // Get Accountholder 
        if ($accountHolder->loanstatus == MyAccountHolder::LOAN_APPROVED || $accountHolder->loanstatus == MyAccountHolder::LOAN_SETTLED ) {
            $this->log(__METHOD__ . ":  Unable to proceed action due to account holder has made loan previously", SNAP_LOG_ERROR);
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to account holder has made loans")]);
        }

        // Update loan status
        // $accountHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
        // $accountHolder->status     = MyAccountHolder::STATUS_ACTIVE;
        $accountHolder->loantotal     = $loanTotal;
        $accountHolder->loanbalance     = $loanTotal;
        $accountHolder->loanreference     = $loanReference;

        // Set approve date ( Time is not saved )
        $approveDate = new \DateTime($approveDate);
        $approveDate->setTimezone($this->app->getUserTimezone());
        $accountHolder->loanapprovedate = $approveDate;
        $accountHolder->loanapproveby     = $approverUserId;
        $accountHolder->loanstatus     = MyAccountHolder::LOAN_APPROVED;
        // $accountHolder->statusremarks = $remarks;

        // Update loan transaction
        // Default loan approval type set to credit
        // Create MyLoanTransaction
        $loantransaction = $this->app->myloantransactionStore()->create([
            'achid'         => $accountHolder->id,
            'transactiontype'   => MyLoanTransaction::TYPE_CREDIT,
            'gtrrefno'         => '',
            'transactionamount' => $accountHolder->loantotal,
            'xau'              => 0,
            // 'xau'               => $now->format('Y-m-d H:i:s'),
            // 'status'            => MyLedger::STATUS_ACTIVE
        ]);


        // $accountHolder->loanapprovedate = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));

        // $observation = new \Snap\IObservation(
        //     $accountHolder,
        //     \Snap\IObservation::ACTION_OPERATORAPPROVE,
        //     0,
        //     [
        //         'projectbase' => $this->app->getConfig()->{'projectBase'},
        //         'accountholdercode' => $accountHolder->accountholdercode,
        //         'mykadno' => $accountHolder->mykadno,
        //         'name' => $accountHolder->fullname,
        //         'receiver' => $accountHolder->email
        //     ]
        // );

        // $this->notify($observation);

        $loantransaction = $this->app->myloantransactionStore()->save($loantransaction);

        return $this->app->myaccountholderStore()->save($accountHolder);
    }

    /**
     * Update account holder loan through FTP 
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function readImportAccountHolderLoan($file, $partnerId ,$preview = false, $debug = true){
        // $version = '1.0';
        // $params['version'] = $version;
        // $params['code'] = $partner->sapcompanybuycode1; // customer id *required
        // $params['item'] = '';
        // $_RETURN_sap_rate_cards = $this->app->apiManager()->sapGetRateCard($version, $params);
        // $data = file_get_contents('C:\laragon\www\gtp\source\snaplib\manager\data.json');
        // // echo $data;
        // // print_r(json_decode($data, true));exit;
        // $data = json_decode($data, true);
        


        // read excel file, item reference number, get from gtp_db and use its value
        // if have varients(xau_weight) between the excel file and gtp_db, just remark on gtp_db but still push to sap

        // NOTE! -> change all old buyback purity weight into details json_encode()
        // NOTE! -> change all old buyback before 30-april all if branchid column == null search branch code, and replace branchid, currently all wrong inserted.

        $cacher = $this->app->getCacher();


        // $partnerObj = $this->app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
        
        // $partners = array();
        // foreach ($partnerObj as $partner){
        // 	// array_push($partnerId,$partner->id);
        //     array_push($partners, $partner->id);
        // }
        // $pos1 = $this->app->getConfig()->{'gtp.pos1.partner.id'};
        // $pos2 = $this->app->getConfig()->{'gtp.pos2.partner.id'};
        // $pos3 = $this->app->getConfig()->{'gtp.pos3.partner.id'};
        // $pos4 = $this->app->getConfig()->{'gtp.pos4.partner.id'};

        // $partners = [$pos1, $pos2, $pos3, $pos4];

        // process readable array - START
        // $reader = new \PhpOffice\PhpSpreadsheet\Reader();
        // $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']);
        // print_r($inputFileType);exit;
        // $this->app->dd($inputFileType);
        if ($inputFileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        if ($inputFileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if (!$reader){
            throw error;
        }
        $spreadsheet = $reader->load($file['tmp_name']);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $partner = $this->app->PartnerStore()->searchTable()->select()->where('id', $partnerId)->one();
        
        $items_array = [];
        
        // $this->app->dd($sheetData);
        
        // fix memory leak on branches from cache
        // $branches = $this->getPartnersBranches($partners);
        $buyback = false;
        $tender = false;

        // Init counter
        $counter = 0;
        $excel_file_section = null;
        foreach ($sheetData as $x => $row){
            if ($row["A"] >= 1){
                if ($row["A"] == 1){
                    if ($excel_file_section == null){
                        $excel_file_section = 'loan';
                    }else{
                        $excel_file_section = 'empty';
                    }
                }

                if ($excel_file_section == 'loan'){
                    $item_array['index'] = $row["A"];
                    $item_array['full_name'] = $row["B"];
                    $item_array['nric'] = $row["C"]; // buybackno
                    $item_array['loan_approve_amount'] = $row["D"];
                    $item_array['ref_no'] = $row["E"];
                    $item_array['loan_approve_date'] = $row["F"];
                    $a= trim($item_array['nric']);
                    // Check if NRC has account
                    // $accountHolder = $app->myaccountholderStore()->getById($params['id']);
                    
                    $accountHolder = $this->app->myaccountholderStore()->searchTable()->select()->where('mykadno', $item_array['nric'])
                    ->andWhere('partnerid', $partnerId)
                    // Check loanstatus doesnt read null, loan status check will be done separately
                    // ->andWhere('loanstatus', '<=', 1)
                    ->orderBy('id', 'desc')->one();

                    if($accountHolder){
                        // If true, check for loanstatus
                        // Perform loan status check
                        // If loanstatus is declared and not null or 0
                        $loanstatus = $accountHolder->loanstatus;
                        if(isset($loanstatus) && $loanstatus > 0){
                            // Account already had loan approved before
                            // Reject!
                            // $item_array['chk_status'] = 0;
                            $item_array['chk_status'] = 2;
                        }else{
                            // Account has not made loan before 
                            $item_array['chk_status'] = 1;
                        }
                        $item_array['account'] = $accountHolder;
                    }else{
                        // if false, perform false
                        $item_array['chk_status'] = 0;
                    }

                    // $buyback = $this->app->buybackStore()->searchTable()->select()->where('buybackno', trim($item_array['referenceNo']))->andWhere('status', 'not in', [2,6])->orderBy('id', 'desc')->one();
                    // var_dump($item_array['referenceNo']);exit;
                    // $item_array['branchid'] = $buyback->branchid;
                    // $item_array['partnerid'] = $buyback->partnerid;

                    // $item_array['buybackid'] = $buyback->id;

                    // $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($buyback->branchid);
                    // $item_array['posbranchcode'] = $getbranchCode->code;
                    // $item_array['type'] = 'loan';
                }
                // if ($excel_file_section == 'tender'){
                //     $item_array['index'] = $row["A"];
                //     $item_array['purchase_no'] = $row["C"];
                //     $item_array['referenceNo'] = $row["C"]; // tender item reference number
                //     $item_array['customer_weight'] = '-';
                //     $item_array['vendor_weight'] = $row["L"];
                //     $item_array['branch_code'] = $row["B"];
                
                //     $draft_item = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['referenceNo']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','desc')->one();
                //     $grnorder = $this->app->goodsreceivenoteorderStore()->getById($draft_item->goodreceivednoteorderid);
                //     $buyback = $this->app->buybackStore()->getById($grnorder->buybackid);
                //     $item_array['branchid'] = $draft_item->branchid;
                //     $item_array['partnerid'] = $buyback->partnerid;
                    
                //     $item_array['buybackid'] = $buyback->id;
                    
                //     $getbranchCode = $this->app->partnerStore()->getRelatedStore('branches')->getById($draft_item->branchid);
                //     $item_array['posbranchcode'] = $getbranchCode->code;
                //     $item_array['type'] = 'tender';
                // }
                
                // // $draft_items = $this->app->goodsreceivenotedraftStore()->searchTable()->select()->where('referenceno', trim($item_array['purchase_no']))->andWhere('status', 'NOT IN', [2,6])->orderby('id','asc')->execute();
                // $item_array['item_details'] = [];
                // $item_array['item_draftid'] = [];
                // if (!$draft_items){
                //     // if has no inactive draft item (not yet submit to SAP)
                //     continue;
                // }
                // // $dupicate = false;
                // // $dupicate_compare = 0;
                // foreach($draft_items as $draft_item_single){
                //     // if ($dupicate_compare != $draft_item_single->goodreceivednoteorderid){
                //     //     $dupicate = true;
                //     //     continue;
                //     // }
                //     // $dupicate_compare = $draft_item_single->goodreceivednoteorderid;

                //     $item_array['item_details'][] = json_decode($draft_item_single->details);
                //     $item_array['item_draftid'][] = $draft_item_single->id;
                // } 
                

                // if (empty($item_array['branchid'])){
                //     continue;
                // }
                // $branch = $this->app->partnerStore()->getRelatedStore('branches')->getById($item_array['branchid']);
                // $item_array['branchname'] = $branch->name;
                
                $counter++;
                $items_array[$counter][] = $item_array;
            }
        }
        // print_r($items_array);exit;
        // process readable array - END

        // Save to DB
        foreach ($items_array as $item){
            $save = $item;

            // Get Uploader ID
            $uploaderUserId = $this->app->getUserSession()->getUser()->id;

            // Get Accountholder 
            // if ($accountHolder->loanstatus == MyAccountHolder::LOAN_APPROVED || $accountHolder->loanstatus == MyAccountHolder::LOAN_SETTLED ) {
            //     $this->log(__METHOD__ . ":  Unable to proceed action due to account holder has made loan previously", SNAP_LOG_ERROR);
            //     throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => gettext("Unable to perform requested action due to account holder has made loans")]);
            // }
       
            // Check status,
            // If 0 = Reject
            // If 1 = Update
            if ($item[0]['chk_status'] == 1) {

                $accountHolder = $item[0]['account'];
                // Update loan status
                // $accountHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
                // $accountHolder->status     = MyAccountHolder::STATUS_ACTIVE;
                $accountHolder->loantotal     = $item[0]['loan_approve_amount'];
                $accountHolder->loanbalance     = $item[0]['loan_approve_amount'];
                $accountHolder->loanreference     = $item[0]['ref_no'];

                // Set approve date ( Time is not saved )
                // Massage string by removing whitespaces
                $strdate = preg_replace('/\s+/', '', $item[0]['loan_approve_date']);

                // Convert to date from string
                $approveDate = str_replace('/', '-', $strdate);
                $approveDate = strtotime($approveDate);
                $approveDate = date('Y-m-d H:i:s', $approveDate);
                $approveDate = new \DateTime($approveDate);
                $approveDate->setTimezone($this->app->getUserTimezone());

                $accountHolder->loanapprovedate =   $approveDate;
                $accountHolder->loanapproveby     = $uploaderUserId;
                $accountHolder->loanstatus     = MyAccountHolder::LOAN_APPROVED;
                // $accountHolder->statusremarks = $remarks;

                // Update loan transaction
                // Default loan approval type set to credit
                // Create MyLoanTransaction
                $loantransaction = $this->app->myloantransactionStore()->create([
                    'achid'         => $accountHolder->id,
                    'transactiontype'   => MyLoanTransaction::TYPE_CREDIT,
                    'gtrrefno'         => '',
                    'transactionamount' => $accountHolder->loantotal,
                    'xau'              => 0,
                    // 'xau'               => $now->format('Y-m-d H:i:s'),
                    // 'status'            => MyLedger::STATUS_ACTIVE
                ]);

                $loantransaction = $this->app->myloantransactionStore()->save($loantransaction);
                $this->app->myaccountholderStore()->save($accountHolder);
            }


            // return $this->app->myaccountholderStore()->save($accountHolder);
        // foreach ($items_array as $branchcode => $branch){
            // foreach ($branch as $type => $buybacks){
            //     $ratecard = [];
            //     $comments = '';
            //     $po = [];
            //     $ratecard = [];
            //     $total_grn_weight = 0;
            //     foreach($buybacks as $buybackid => $buyback){
            //         // $buyback_data = $this->app->buybackStore()->getById(3);
            //         $buyback_data = $this->app->buybackStore()->getById($buybackid);

            //         $version = '1.0';
            //         $type = 'purchase';
            //         $refOrKey = $buyback_data->buybackno;
            //         $isRef = true;
            //         $sap_po = $this->app->apiManager()->sapGetPostedOrders($version, $type, $refOrKey, $isRef);
            //         // print_r($sap_po);exit;
            //         if (!$sap_po){
            //             continue;
            //         }
            //         $docEntry = $sap_po['hdr'][0]['docEntry'];
            //         $docNum = $sap_po['hdr'][0]['docNum'];
                    
            //         $po[] = [
            //             "docEntry" => $docEntry,
            //             "docNum" => $docNum,
            //             "u_GTPREFNO" => $buyback_data->buybackno
            //         ];
            //         $total_gtpxauweight = 0;
            //         $item_total_weight = 0;
            //         // $this->app->dd($buyback);

            //         $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
            //         $params['version'] = '1.0';
            //         $params['item'] = '';
            //         $params['code'] = $partner->sapcompanybuycode1;
            //         $ratecard_sap = $this->app->apiManager()->sapGetRateCard($version, $params);
            //         $reformed_ratecard = [];
            //         foreach($ratecard_sap['ratecard'] as $rate){
            //             $reformed_ratecard[$rate['u_itemcode']] = $rate['u_purity'];
            //         }
            //         // $this->app->dd($reformed_ratecard);
                    
            //         foreach ($buyback as $index => $draftItem){
            //             // $total_gtpxauweight += $draftItem['gtpxauweight'];
            //             $details = $draftItem['item_details'];
            //             $draftids[] = $draftItem['item_draftid']; // update draft id status to submitted;
            //             // $this->app->dd($buyback);p
            //             foreach ($details as $item_detail){
            //                 if (is_array($item_detail)){
            //                     // different format from bb and tender mode _CAUTION 
            //                     // CHANGE TENDER FORMAT IF CAN and revert this is_array condition
            //                     foreach ($item_detail as $x_item_details){
            //                         $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($x_item_details->purity);
            //                         if (!$sap_rate_itemcode){
            //                             $sap_rate_itemcode = $x_item_details->purity;
            //                         }
            //                         $purity_value = $reformed_ratecard[$sap_rate_itemcode];
            //                         $ratecard[] = [
            //                             "u_itemcode" => $sap_rate_itemcode,
            //                             "u_xauweight" => 0,
            //                             "u_purity" => $purity_value,
            //                             "u_inputweight" => $x_item_details->weight,
            //                         ];
        
            //                         $gtpxauweight = (floatval($purity_value) / 100) * floatval($x_item_details->weight);
            //                         $total_gtpxauweight += $gtpxauweight;
            //                         $item_total_weight += floatval($x_item_details->weight);
            //                         $total_grn_weight += $gtpxauweight;
            //                     }
            //                 }else{
            //                     $sap_rate_itemcode = $this->formatBuybackPOSPurityToSAPCode($item_detail->purity);
            //                     if (!$sap_rate_itemcode){
            //                         $sap_rate_itemcode = $item_detail->purity;
            //                     }
            //                     $purity_value = $reformed_ratecard[$sap_rate_itemcode];
            //                     $ratecard[] = [
            //                         "u_itemcode" => $sap_rate_itemcode,
            //                         "u_xauweight" => 0,
            //                         "u_purity" => $purity_value,
            //                         "u_inputweight" => $item_detail->weight,
            //                     ];

            //                     $gtpxauweight = (floatval($purity_value) / 100) * floatval($item_detail->weight);
            //                     $total_gtpxauweight += $gtpxauweight;
            //                     $item_total_weight += floatval($item_detail->weight);
            //                     $total_grn_weight += $gtpxauweight;
            //                 }
                            
            //             }
                        
            //             // if (floatval($total_grn_weight) != floatval($draftItem['vendor_weight'])){
            //             //     $variants = (floatval($total_grn_weight) - floatval($draftItem['vendor_weight']));
            //             //     $comments .= $buyback->referenceno.'>vrts:'.$variants.';';
            //             //     // $comments .= $buyback['referenceNo'].'>vrts:'.$variants.';';
            //             // }
            //             // $ratecard[] = [
            //                 // "u_itemcode" => value.data.u_itemcode,
            //                 // "u_purity" => value.data.u_purity,
            //                 // "u_inputweight" => value.data.gtp_inputweight,
            //                 // "u_xauweight" => value.data.gtp_xauweight
            //             // ];
            //         }
            //         $total_grn_weight = $total_grn_weight;
            //         // $ratecard[] = $ratecard_item;
            //         $partner = $this->app->partnerStore()->getById($buyback_data->partnerid);
            //         $customer = $partner->sapcompanybuycode1;

            //         $summary = [
            //             "total_expected_xau" => 0,
            //             "total_gross_weight" => 0,
            //             "total_xau_collected" => $total_grn_weight,
            //             "vatsum" => 0,
            //         ];

            //         // $draftids = $draft_item_ids;
            //         // summary = {
            //         //     "total_expected_xau": total_expected,
            //         //     "total_gross_weight": total_gross_weight,
            //         //     "total_xau_collected": total_xau_collected,
            //         //     "vatsum": vatsum,
            //         // }

            //         // $branch = $this->app->partnerStore()->getRelatedStore('branches')->getByField('code', $branchcode);
                    

            //         // $data = json_encode($data);
            //         // post to  SAP_GRN;
            //         // $return = $this->sendSAPGrnPOS($data);
            //         // if (!$return){

            //         // }
            //     }
            //     // print_r($ratecard);exit;


            //     // consolidate RATECARD START
            //     $new_rate = [];
            //     foreach ($ratecard as $rcard){
            //         $new_rate[$rcard['u_itemcode']]['u_purity'] = $rcard['u_purity'];
            //         $new_rate[$rcard['u_itemcode']]['u_inputweight'] += $rcard['u_inputweight'];
            //     }
            //     $final_rate = [];
            //     foreach ($new_rate as $key => $nrate){
            //         $final_rate[] = [
            //             "u_itemcode" => $key,
            //             "u_xauweight" => 0,
            //             "u_purity" => $nrate['u_purity'],
            //             "u_inputweight" => $nrate['u_inputweight'],
            //         ];
            //     }
            //     $ratecard = $final_rate;
            //     // consolidate RATECARD END

            //     $branchname = $this->app->partnerStore()->getRelatedStore('branches')->getbyField('code',$branchcode);
            //     $data = [
            //         'po' => $po,
            //         'ratecard' => $ratecard,
            //         'customer' => $customer,
            //         'comments' => 'POS:'.$branchcode.'POSNAME:'.$branchname->name.'<>V-'.$comments,
            //         'summary' => $summary,
            //     ];
            //     $data = json_encode($data);
            //     // print_r($data);exit;
            //     // post to  SAP_GRN;

            //     if ($debug == false){
            //         $return = $this->sendSAPGrnPOS($data);
            //         if (!$return){

            //         }
            //         // if ($return){
            //         //     foreach ($draftids as $draft_id){
            //         //         $draft = $this->app->goodsreceivenotedraftStore()->getById($draft_id);
            //         //         $draft->status = 2;
            //         //         $this->app->goodsreceivenotedraftStore()->save($draft);
            //         //     }
            //         // }
            //     }else{
            //         echo "<pre>";
            //         print_r($data);
            //         echo "</pre>";
            //     }

            //     // $this->app->dd($data, false);
            // }

        }

   

        // To return excel status
        $return = $items_array;
        $this->exportImportAccountHolderLoan($spreadsheet, $items_array ,$partner->code);
        return $return;

    }

    public function exportImportAccountHolderLoan($spreadsheet, $items_array, $partnercode){
        try {
            // $headerText = [];
            // $headerSQL = [];
            // foreach ($header as $headerColumn){
            //     array_push($headerText, $headerColumn->text);
            //     array_push($headerSQL, $headerColumn->index);
            // }

            // $query = $currentStore->searchView()->select($headerSQL);

            // if ($conditions){
            //     $query->where($conditions);
            // }

            // $queryData = $query->execute();

            // if ($resultCallback) {
            //     $queryData = $resultCallback($queryData);
            // }

            // $headerString = $this->createHeader($headerText);
            // $contentString = $this->createContent($headerSQL, $queryData);
            // $excelpages = $headerString.$contentString;

            // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            // $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // load formating decimal START
            // $columns = [];
            
            // $totalcolumn = count($headerText);
            // $checker = $totalcolumn + 1;

            // foreach ($header as $x => $headerColumn){
            //     if ($headerColumn->decimal){
            //         $column = $x; // 0 = A, 1 = B;
            //         $column_decimal = $headerColumn->decimal;
            //         array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
            //     }
            // }
            $rows = [];
            $totalrow = $counter;
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
                'index' => 3,
            ];

            $spreadsheet->getActiveSheet()->getStyle("A1:Z1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $alphabet = range('G', 'Z');

            $column_alphabet = 'G';
            $column_alphabet_append = 'H';
            $column_alphabet_append_2 = 'I';
            // Set Header for status
            $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['index'],'Status');
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$rows['index'],'Status Remarks');
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            foreach ($items_array as $item){
                // Find current row
                $recordrow = $item[0]['index'] + 3;
            
                // $column_alphabet = $alphabet[$column['column']];
                

                $style = $column_alphabet.$recordrow;
                // $style = $column_alphabet.$rows['total'];
                $range = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['end'];
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                if ($item[0]['chk_status'] == 1){
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Success');
                }else if($item[0]['chk_status'] == 2){
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Failed');
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$recordrow,'Previously Approved');
                    
                    $account = $item[0]['account'];
                    // Send email here to ACE
                    $observation = new IObservation($account, IObservation::ACTION_APPROVE, 1, [
                        'event' => MyGtpEventConfig::EVENT_ACCOUNTHOLDER_LOAN_FAILED,
                        'projectbase' => $this->app->getConfig()->{'projectBase'},
                        'name' => $item[0]['full_name'],
                        'receiver' => $this->app->getConfig()->{'mygtp.admin.email'},
                        'partnercode' => $partnercode,
                        'code' => $item[0]['nric'],
                        'approveamount' => $item[0]['loan_approve_amount'],
                        'approvedate' => $item[0]['loan_approve_date'],
                        'email' => urlencode($item[0]['account']->email)
                    ]);
                    // $observation = new \Snap\IObservation($item[0]['account'], \Snap\IObservation::ACTION_APPROVE, 1, [
                    //     'serialno'        => $body,
                    //     'approvedby'     => $this->app->getUserSession()->getUser()->name,
                    // ]);
                    $this->notify($observation);
                    
                    // $this->notify(new IObservation($item[0]['account'], IObservation::ACTION_APPROVE, 0, [
                    //     'event' => MyGtpEventConfig::EVENT_ACCOUNTHOLDER_LOAN_FAILED,
                    //     // 'accountholderid'   => $accHolder->id,
                    //     // 'buyorsell'         => $buyorsell,
                    //     // 'name'              => $accHolder->fullname,
                    //     // 'receiver'          => $accHolder->email,
                    //     // 'ordno'             => $goldTransaction->orderid,
                    //     // 'requestedon'       => $goldTransaction->dbmpdtrequestedon,
                    //     // 'price'             => $goldTransaction->ordprice,
                    //     // 'xau'               => $goldTransaction->ordxau,
                    //     // 'amount'            => $goldTransaction->ordamount,
                    //     // 'bankname'          => $goldTransaction->dbmbankname,
                    //     // 'accno'             => $goldTransaction->dbmaccountnumber,
                    // ]));

                }else{
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Failed');
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$recordrow,'NRIC Not Matched');

                }
            
            }
            // foreach ($columns as $column){
            //     $column_alphabet = $alphabet[$column['column']];
            //     $style = $column_alphabet.$rows['total'];
            //     $range = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['end'];
            //     $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
            //     $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
            //     $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['total'],'=SUM('.$range.')');
            // }

            // foreach ($columns as $column){
            //     $column_alphabet = $alphabet[$column['column']];
            //     $style = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['total'];
            //     if ($column['decimal'] == 2){
            //         $decimal_format = '0.00';
            //     }
            //     if ($column['decimal'] == 3){
            //         $decimal_format = '0.000';
            //     }
            //     $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format); 
            // }
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv){
                $environtmentFileName = '_DEMO_';
            }else{
                $environtmentFileName = '_';
            }
            $partnername = $partner->name;
            $filename = 'ACE'.$environtmentFileName.$partnername.'UPLOAD_RESULTS_EXPORT_'.$datenow.'.xlsx';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            $writer->save("php://output");
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to get data for Export", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }// Append and product excel output
    }

     /**
     * Update account holder loan through FTP 
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $remarks
     * @return MyAccountHolder
     */
    public function readImportAccountHolderMember($file, $partner ,$preview = false, $debug = true){
        
        // $version = '1.0';
        // $params['version'] = $version;
        // $params['code'] = $partner->sapcompanybuycode1; // customer id *required
        // $params['item'] = '';
        // $_RETURN_sap_rate_cards = $this->app->apiManager()->sapGetRateCard($version, $params);
        // $data = file_get_contents('C:\laragon\www\gtp\source\snaplib\manager\data.json');
        // // echo $data;
        // // print_r(json_decode($data, true));exit;
        // $data = json_decode($data, true);
        


        // read excel file, item reference number, get from gtp_db and use its value
        // if have varients(xau_weight) between the excel file and gtp_db, just remark on gtp_db but still push to sap

        // NOTE! -> change all old buyback purity weight into details json_encode()
        // NOTE! -> change all old buyback before 30-april all if branchid column == null search branch code, and replace branchid, currently all wrong inserted.

        $cacher = $this->app->getCacher();

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file['tmp_name']);
        // print_r($inputFileType);exit;
        // $this->app->dd($inputFileType);
        if ($inputFileType == 'Xls'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        }
        if ($inputFileType == 'Xlsx'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        }
        if ($inputFileType == 'Csv'){
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        }
        if (!$reader){
            throw error;
        }
        
        $spreadsheet = $reader->load($file['tmp_name']);
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // $partner = $this->app->PartnerStore()->searchTable()->select()->where('id', $partnerId)->one();
        
        $items_array = [];
        
        // $this->app->dd($sheetData);
        
        // fix memory leak on branches from cache
        // $branches = $this->getPartnersBranches($partners);
        $buyback = false;
        $tender = false;

        // Init counter
        $counter = 0;
        $excel_file_section = null;
        
        foreach ($sheetData as $x => $row){
            if ($row["A"] >= 1){
                if ($row["A"] == 1){
                    if ($excel_file_section == null){
                        $excel_file_section = 'loan';
                    }else{
                        $excel_file_section = 'empty';
                    }
                }

                if ($excel_file_section == 'loan'){
                    $item_array['index'] = $row["A"];
                    $item_array['memberuniqueid'] = $row["B"];
                    $item_array['full_name'] = $row["C"];
                    $item_array['nric'] = $row["D"]; // buybackno
                    $item_array['contact'] = $row["E"];
                    $item_array['email'] = $row["F"];
                    $item_array['address_line1'] = $row["G"];
                    $item_array['address_line2'] = $row["H"];
                    $item_array['postcode'] = $row["I"];
                    $item_array['state'] = $row["J"];

                    
                    // Check if NRC has account
                    // $accountHolder = $app->myaccountholderStore()->getById($params['id']);
                   
                    $memberupload = $this->app->mymemberuploadStore()->searchTable()->select()->where('ic', $item_array['nric'])
                    ->andWhere('partnerid', $partner->id)
                    // Check if there is NRIC record in memberupload table
                    // ->andWhere('memberstatus', '<=', 1)
                    ->orderBy('id', 'desc')->one();
                    if($memberupload){
                        // If true, check for memberstatus
                        // Perform member status check
                        // If memberstatus is declared and not null or 0
                        $memberstatus = $memberupload->status;
                        if(isset($memberstatus) && $memberstatus > 0){
                            // Member has been mapped before
                            // Reject!
                            // $item_array['chk_status'] = 0;
                            $item_array['chk_status'] = 2;
                        }else{
                            // Member exist but status unmapped
                            $item_array['chk_status'] = 0;
                        }
                        $item_array['member'] = $memberupload;
                    }else{
                        // if no record, then save said record
                        $item_array['chk_status'] = 1;
                    }
                    // $item_array['chk_status'] = 1;

                }
                
                $counter++;
                $items_array[$counter][] = $item_array;

                //limit the total record or it will memory limit exhausted
                //updated: removed because it seems if using csv file would not get the memory limit exhausted error (concluded after done few trial)
                // if($counter > 1100){exit;}
            }
        }

        // Save to DB
        foreach ($items_array as $item){
            $save = $item;

            // Get Uploader ID
            $uploaderUserId = $this->app->getUserSession()->getUser()->id;

            // Check status,
            // If 0 = Reject
            // If 1 = Update
            if ($item[0]['chk_status'] == 1) {

                // $memberupload = $item[0]['member'];
                // // Update loan status
                // // $accountHolder->amlastatus = MyAccountHolder::AMLA_PASSED;
                // // $accountHolder->status     = MyAccountHolder::STATUS_ACTIVE;
                // $memberupload->loantotal     = $item[0]['loan_approve_amount'];
                // $memberupload->loanbalance     = $item[0]['loan_approve_amount'];
                // $memberupload->loanreference     = $item[0]['ref_no'];

                // // Set approve date ( Time is not saved )
                // // Massage string by removing whitespaces
                // $strdate = preg_replace('/\s+/', '', $item[0]['loan_approve_date']);

                // // Convert to date from string
                // $approveDate = str_replace('/', '-', $strdate);
                // $approveDate = strtotime($approveDate);
                // $approveDate = date('Y-m-d H:i:s', $approveDate);
                // $approveDate = new \DateTime($approveDate);
                // $approveDate->setTimezone($this->app->getUserTimezone());

                // $memberupload->loanapprovedate =   $approveDate;
                // $memberupload->loanapproveby     = $uploaderUserId;
                // $memberupload->loanstatus     = MyAccountHolder::LOAN_APPROVED;
                // $memberupload->statusremarks = $remarks;

                // Update loan transaction
                // Default loan approval type set to credit
                // Create MyLoanTransaction
                $memberupload = $this->app->mymemberuploadStore()->create([
                    'partnerid'     => $partner->id,
                    'memberuniqueid'=> $item[0]['memberuniqueid'],
                    'name'          => $item[0]['full_name'],
                    'ic'            => $item[0]['nric'],
                    'contact'       => $item[0]['contact'],
                    'email'         => $item[0]['email'],
                    'address_line1' => $item[0]['address_line1'],
                    'address_line2' => $item[0]['address_line2'],
                    'postcode'      => $item[0]['postcode'],
                    'state'         => $item[0]['state'],
                    'status'        => MyMemberUpload::STATUS_UNMAPPED,
                    // 'xau'               => $now->format('Y-m-d H:i:s'),
                    // 'status'            => MyLedger::STATUS_ACTIVE
                ]);

                $memberupload = $this->app->mymemberuploadStore()->save($memberupload);
                // $this->app->myaccountholderStore()->save($memberupload);
            }

        }

   

        // To return excel status
        $return = $items_array;
        $this->exportImportAccountHolderMember($spreadsheet, $items_array ,$partner->code);
        return $return;

    }

    public function exportImportAccountHolderMember($spreadsheet, $items_array, $partnercode){
        try {
            // $headerText = [];
            // $headerSQL = [];
            // foreach ($header as $headerColumn){
            //     array_push($headerText, $headerColumn->text);
            //     array_push($headerSQL, $headerColumn->index);
            // }

            // $query = $currentStore->searchView()->select($headerSQL);

            // if ($conditions){
            //     $query->where($conditions);
            // }

            // $queryData = $query->execute();

            // if ($resultCallback) {
            //     $queryData = $resultCallback($queryData);
            // }

            // $headerString = $this->createHeader($headerText);
            // $contentString = $this->createContent($headerSQL, $queryData);
            // $excelpages = $headerString.$contentString;

            // $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            // $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // load formating decimal START
            // $columns = [];
            
            // $totalcolumn = count($headerText);
            // $checker = $totalcolumn + 1;

            // foreach ($header as $x => $headerColumn){
            //     if ($headerColumn->decimal){
            //         $column = $x; // 0 = A, 1 = B;
            //         $column_decimal = $headerColumn->decimal;
            //         array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
            //     }
            // }
            $rows = [];
            // $totalrow = $counter;
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
                'index' => 1,
            ];

            $spreadsheet->getActiveSheet()->getStyle("A1:Z1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $alphabet = range('K', 'Z');

            $column_alphabet = 'K';
            $column_alphabet_append = 'L';
            $column_alphabet_append_2 = 'M';
            // Set Header for status
            $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['index'],'Status');
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet.$rows['index'])->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$rows['index'],'Status Remarks');
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // $spreadsheet->getActiveSheet()->getStyle($column_alphabet_append_2.$rows['index'])->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            foreach ($items_array as $item){
                // Find current row
                $recordrow = $item[0]['index'] + 1;
            
                // $column_alphabet = $alphabet[$column['column']];
                

                $style = $column_alphabet.$recordrow;
                // $style = $column_alphabet.$rows['total'];
                $range = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['end'];
                // $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                // $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                // $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getRight()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                if ($item[0]['chk_status'] == 1){
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Success');
                }else if($item[0]['chk_status'] == 2){
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Failed');
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$recordrow,'NRIC has already been mapped');
                }else{
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$recordrow,'Failed');
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet_append.$recordrow,'NRIC already in use');

                    // // Send email here to ACE
                    // $observation = new IObservation($account, IObservation::ACTION_VERIFY, 0, [
                    //     'event' => MyGtpEventConfig::EVENT_ACCOUNT_VERIFICATION,
                    //     'projectbase' => $this->app->getConfig()->{'projectBase'},
                    //     'name' => $item[0]['full_name'],
                    //     'receiver' => $this->app->getConfig()->{'mygtp.admin.email'},
                    //     'partnercode' => $partnercode,
                    //     'code' => $item[0]['nric'],
                    //     'approveamount' => $item[0]['loan_approve_amount'],
                    //     'approvedate' => $item[0]['loan_approve_date'],
                    //     'email' => urlencode($account->email)
                    // ]);
                    // $this->notify($observation);
                }
            
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv){
                $environtmentFileName = '_DEMO_';
            }else{
                $environtmentFileName = '_';
            }
            $partnername = $partner->name;
            $filename = 'ACE'.$environtmentFileName.$partnername.'UPLOAD_RESULTS_EXPORT_'.$datenow.'.xlsx';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$filename.'"');
            $writer->save("php://output");
        } catch(\Exception $e) {
            $this->log(__METHOD__."Error to get data for Export", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }// Append and product excel output
    }

    public function getAccountHolderCheckContact(Partner $partner, string $phoneNo)
    {
        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('partnerid', $partner->id)
            ->andWhere('phoneno', $phoneNo)
            ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
            ->one();

        // If account holder is not found
        if (!$accountHolder) {
            throw MyGtpPhoneNumberNotExist::fromTransaction([], ['phone_number' => $phoneNo]);
        }

        return $accountHolder;
    }

    public function saveImageUpload($accHolder,$imgFrontName,$imgFront,$imgBackName,$imgBack)
    {
        $now         = new \DateTime('now');
        $getImgStore = $this->app->mykycimageStore()->searchTable()->select()
                    ->andWhere('partnerid', $accHolder->partnerid)
                    ->andWhere('ach_mykadno', $accHolder->mykadno)
                    ->execute();
        if(count($getImgStore) > 0){
            foreach ($getImgStore as $getImg) {
                $getImg->imagename     = $imgFrontName;
                $getImg->image         = $imgFront;
                $getImg->imageback     = $imgBack;
                $getImg->imagebackname = $imgBackName;
                $getImg->uploaded      = $now->format('Y-m-d H:i:s');

                $imgStore = $this->app->mykycimageStore()->save($getImg);
            }
        } else {
            $createImgStore = $this->app->mykycimageStore()->create([
                'name'          => $accHolder->fullname,
                'ach_mykadno'   => $accHolder->mykadno,
                'partnerid'     => $accHolder->partnerid,
                'imagename'     => $imgFrontName,
                'image'         => $imgFront,
                'imageback'     => $imgBack,
                'imagebackname' => $imgBackName,
                'uploaded'      => $now->format('Y-m-d H:i:s')
            ]);
            $imgStore = $this->app->mykycimageStore()->save($createImgStore);
        }

        if(!$imgStore) {
            $message = "saveImageUpload::There is error when uploading image for ".$accHolder->mykadno." with partnerid ".$accHolder->partnerid.".";
            $this->log($message, SNAP_LOG_INFO);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }

        return $imgStore;
    }

    public function getImageUpload($accHolder)
    {
        $now         = new \DateTime('now');
        $getImgStore = $this->app->mykycimageStore()->searchTable()->select()
                    ->andWhere('partnerid', $accHolder->partnerid)
                    ->andWhere('ach_mykadno', $accHolder->mykadno)
                    ->execute();
        if(count($getImgStore) > 0){
            foreach ($getImgStore as $getImg) {

                $imgStore = $getImg;
            }
        } else {
           // Do nothing
        }

        if(!$imgStore) {
            $message = "getImageUpload::There is error when loading images for ".$accHolder->mykadno." with partnerid ".$accHolder->partnerid.".";
            $this->log($message, SNAP_LOG_INFO);
            throw GeneralException::fromTransaction([], ['message' => $message]);
        }

        return $imgStore;
    }
    
    public function updateMyKadNumber(MyAccountHolder $accholder,$mykadnumber){
        //save icnumber
        $accholder->mykadno = $mykadnumber;
        $accholder->loanstatus = 1; //indicator that accholder already update their ic. added 20220928
        $accholder = $this->app->myaccountholderStore()->save($accholder, ['mykadno','loanstatus']);

        return $accholder;
    }

    /**
     * Operation to print Account Holder Register
     * 
     * @param  AccountHolder    $accountholderid        Account Holder Id of registered user 
     */
    public function printRegister($accountholderid, $generatedpassword = null, $projectbase = null,  $generatedpin = null, $haveWebsite = false, $haveNextOfKin = true)
    {
        try {
            
            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                // $myaccountholder = $this->app->myaccountholderStore()->getById($accountholderid);
                $myaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                    ->where('id', $accountholderid)
                    ->one();
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;
                $myaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                    ->where('id', $accountholderid)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$myaccountholder){
                return false;
            }

            // if(Order::TYPE_COMPANYBUY == $order->type){
            //     $finalAcePriceTitle = "ACE - Buy Order";
            //     $finalAcePriceLabel = "Ace Buy Final Price (RM/g)";
            //     $orderFeeLabel = "Refining Fee";
            // }else if (Order::TYPE_COMPANYSELL == $order->type){
            //     $finalAcePriceTitle = "ACE - Sell Order";
            //     $finalAcePriceLabel = "Ace Sell Final Price (RM/g)";
            //     $orderFeeLabel = "Premium Fee";
            // }else{
            //     $finalAcePriceTitle = "-";
            //     $finalAcePriceLabel = "-";
            //     $orderFeeLabel = "-";
            // }
        
            $finalAcePrice = number_format(($myaccountholder->price + ($myaccountholder->fee)),3);
            $weight = number_format($myaccountholder->xaubalance,3);
            $totalEstValue = number_format($myaccountholder->amountbalance,3);
            // $orderFee = number_format($myaccountholder->fee,3);

            // Get customer name
            $customerId = $myaccountholder->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;

            // $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($myaccountholder->salespersonid && $myaccountholder->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($order->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            // get partner
            $partner = $this->app->partnerStore()->getById($myaccountholder->partnerid);
            //background-color:#009ABB;color: #fff;
            /*    <td style="width: 50%;">
                    <div style="font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Personal Information' . '</div>
                    <p><span>Account Holder Code. 			</span>: '. $myaccountholder->accountholdercode . '</p>
                    <p><span>Full Name 					    </span>: '. $myaccountholder->fullname . '</p>
                    <p><span>Email 					        </span>: '. $myaccountholder->email . '</p>
                    <p><span>Identity No (NRIC) 			</span>: '. $myaccountholder->mykadno . '</p>
                    <p><span>Mobile     					</span>: '. $myaccountholder->phoneno . '</p>
                    <p><span>Address					    </span>: '. $myaccountholder->addressline1. ' ' . $myaccountholder->addressline2 . '</p>
                    <p><span>City						    </span>: '. $myaccountholder->addresscity . '</p>
                    <p><span>Postcode						</span>: '. $myaccountholder->addresspostcode . '</p>
                    <p><span>State					    	</span>: '. $myaccountholder->addressstate . '</p>     
                    </td>
            */
            $base_pdf_1 = '
            <style type="text/css">
            span {font-weight: bold;}
            </style>

            <h3>Registration Details</h3>
            <br>
            <table style="width: 100%;" >
                <tr>
                    <td style="width: 50%;">
                    <div style="color: #009ABB;font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Personal Information' . '</div>
                    <p><span>Account Holder Code. 			</span>: '. $myaccountholder->accountholdercode . '</p>
                    <p><span>Full Name 					    </span>: '. $myaccountholder->fullname . '</p>
                    <p><span>Email 					        </span>: '. $myaccountholder->email . '</p>
                    <p><span>Identity No (NRIC) 			</span>: '. $myaccountholder->mykadno . '</p>
                    <p><span>Mobile     					</span>: '. $myaccountholder->phoneno . '</p>
                    <p><span>Address					    </span>: '. $myaccountholder->addressline1. ' ' . $myaccountholder->addressline2 . '</p>
                    <p><span>City						    </span>: '. $myaccountholder->addresscity . '</p>
                    <p><span>Postcode						</span>: '. $myaccountholder->addresspostcode . '</p>
                    <p><span>State					    	</span>: '. $myaccountholder->addressstate . '</p>     
 
            ';

            $generated_password = $generatedpassword ? 
            '<br>
            <div style="color: #009ABB;font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Account Information' . '</div>
            <p><span>Generated Password 		</span>: '.  $this->encrypt_decrypt($generatedpassword, 'decrypt') . '</p>
            ' : '';

            $generated_pin = $generatedpin ? 
            '<p><span>Generated Pin 		</span>: '.  $this->encrypt_decrypt($generatedpin, 'decrypt') . '</p>
            ' : '';

            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv){
                $projectUrl = $this->app->getConfig()->{'iframe.uat'} ? $this->app->getConfig()->{'iframe.uat'} : '';
                
            }else{
                $projectUrl = $this->app->getConfig()->{'iframe.prd'} ? $this->app->getConfig()->{'iframe.prd'} : '';
            }


            $account_partner = $partner ? 
            '<p><span>Account Registered At 		</span>: '.  $partner->name . '</p>
            ' : '';
            $base_pdf_2a = '
                    </td>
                    <td style="width: 50%;">
                    <div style="color: #009ABB;font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Bank Account Information' . '</div>
                    <p><span>Bank Account 			        </span>: '. $myaccountholder->accountname . '</p>
                    <p><span>Bank Account Number		    </span>: '. $myaccountholder->accountnumber . '</p>
                    <p><span>Occupation Category 		    </span>: '. $myaccountholder->occupationcategory . '</p>

            ';

            if($haveNextOfKin){
                $base_pdf_2b = '                    
                        <br>
                        <div style="color: #009ABB;font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Next of Kin Information' . '</div>
                        <p><span>Full Name 					    </span>: '. $myaccountholder->nokfullname . '</p>
                        <p><span>Email 					        </span>: '. $myaccountholder->nokemail . '</p>
                        <p><span>Identity No (NRIC) 			</span>: '. $myaccountholder->nokmykadno . '</p>
                        <p><span>Mobile     					</span>: '. $myaccountholder->nokphoneno . '</p>
                        <p><span>Address					    </span>: '. $myaccountholder->nokaddress . '</p>
                        <p><span>Relationship   				</span>: '. $myaccountholder->nokrelationship . '</p>
                        <p><span>Date Registered		 	    </span>: '. $myaccountholder->createdon->format('Y-m-d H:i:s') . '</p>
                        <br>
                        </td>
                    </tr>
                </table>';
    
            }else{
                $base_pdf_2b = '                    
                        <br>
                        <div style="color: #009ABB;font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Account Information' . '</div>
                        <p><span>Salesman Code					</span>: '. $myaccountholder->referralsalespersoncode . '</p>
                        <p><span>Introducer Code 			    </span>: '. $myaccountholder->referralintroducercode . '</p>
                        <p><span>Date Registered		 	    </span>: '. $myaccountholder->createdon->format('Y-m-d H:i:s') . '</p>
                        <p><span></span></p>
                        <p><span></span></p>
                        <p><span></span></p>
                        <p><span></span></p>
                        <p><span></span></p>
                        <br>
                        </td>
                    </tr>
                </table>';
    
            }
           

            $base_suboccupation = $myaccountholder->occupationsubcategory ? '<p><span>Occupation Sub Category 		</span>: '. $myaccountholder->occupationsubcategory . '</p>' : '';
            
            // to add difference in url based on env
            // if env development, show uat
            if($haveWebsite){
                $qr_code = $projectbase ? '<img style="width: 20%;" src="src/resources/images/'.strtoupper($projectbase).'/link_qr.png">' : '';
                $redirect_url = !empty($projectUrl) ? 
                '<p><span>Link for Login 		</span>: '.  $projectUrl . '</p>
                ' : '';
    
            }else{
                $qr_code = '';
                $redirect_url = '';
    
            }
           
            
            $returnPdf = $base_pdf_1. $generated_password. $generated_pin. $account_partner.$redirect_url. $base_pdf_2a. $base_suboccupation .  $base_pdf_2b . $qr_code;
            // <span><p>'. $orderFeeLabel . '          : '. $orderFee  . '</p></span>
            
			


        
        } catch (\Exception $e) {
           
            throw $e;
        }
        return $returnPdf;
    }

    public function printRegisterOTC($accountholderid, $generatedpassword = null, $projectbase = null,  $generatedpin = null, $haveWebsite = false, $haveNextOfKin = true)
    {
        try {

            $casa = BaseCasa::getInstance($this->app->getConfig()->{'otc.casa.api'});

            if ($this->app->getUsersession()->getUser()->type != 'Customer'){
                // $myaccountholder = $this->app->myaccountholderStore()->getById($accountholderid);
                $myaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                    ->where('id', $accountholderid)
                    ->one();
            }else{
                $partnerId = $this->app->getUsersession()->getUser()->partnerid;
                $myaccountholder = $this->app->myaccountholderStore()->searchView()->select()
                    ->where('id', $accountholderid)
                    ->andWhere('partnerid', $partnerId)
                    ->one();
            }
            if (!$myaccountholder){
                return false;
            }else{
                $additionalData = $this->app->achadditionaldataStore()->searchTable()->select()
                    ->where('accountholderid', $accountholderid)
                    ->one();
            }

            
            

            // Get customer name
            $customerId = $myaccountholder->partnerid;
            $userobj = $this->app->partnerStore()->getById($customerId);
            $customername = $userobj->name;

            // $product = $this->app->productStore()->getById($order->productid);

            // Get salesperson name
            if ($myaccountholder->salespersonid && $myaccountholder->salespersonid != 0){
                $salesperson = $this->app->userStore()->getById($myaccountholder->salespersonid);
                $salespersonname = $salesperson->name;
            }else{
                $salespersonname = '-';
            }

            // get partner
            $partner = $this->app->partnerStore()->getById($myaccountholder->partnerid);
            //background-color:#009ABB;color: #fff;
            /*    <td style="width: 50%;">
                    <div style="font-weight:bold;font-size:16px;margin-bottom: 8px">'. 'Personal Information' . '</div>
                    <p><span>Account Holder Code. 			</span>: '. $myaccountholder->accountholdercode . '</p>
                    <p><span>Full Name 					    </span>: '. $myaccountholder->fullname . '</p>
                    <p><span>Email 					        </span>: '. $myaccountholder->email . '</p>
                    <p><span>Identity No (NRIC) 			</span>: '. $myaccountholder->mykadno . '</p>
                    <p><span>Mobile     					</span>: '. $myaccountholder->phoneno . '</p>
                    <p><span>Address					    </span>: '. $myaccountholder->addressline1. ' ' . $myaccountholder->addressline2 . '</p>
                    <p><span>City						    </span>: '. $myaccountholder->addresscity . '</p>
                    <p><span>Postcode						</span>: '. $myaccountholder->addresspostcode . '</p>
                    <p><span>State					    	</span>: '. $myaccountholder->addressstate . '</p>     
                    </td>
            */

            if($myaccountholder->accounttype){
                if($myaccountholder->accounttype == 22){
                    $toReturn[]= array( 
                        'searchFlag' =>  1, // aka searchflag
                        'keyword' => $myaccountholder->partnercusid,
                    );
                    $jointAccount = $casa->getCustomerInfo($toReturn);
                }
                $accounttypestr = $myaccountholder->getAccountTypeString();
            }

            $userId = $this->app->getUserSession()->getUserId();
        	$user = $this->app->userStore()->getById($userId);
        	$teller = $user->username;

            $data = [
                'date'                   => $myaccountholder->createdon->format('Y-m-d H:i:s'),
                'partner_name'           => $customername,
                'parcustid'              => $myaccountholder->partnercusid,
                'mykad_no'               => $myaccountholder->mykadno,
                'full_name'              => $myaccountholder->fullname,
                'account_no'             => $myaccountholder->accountnumber,
                'account_type_str'       => $accounttypestr,
                'accountholder_code'     => $myaccountholder->accountholdercode,

                'title'                  => $additionalData->title,
                'nationality'            => $additionalData->nationality,
                'dateofbirth'            => $additionalData->dateofbirth,
                'bumiputera'             => $additionalData->bumiputera,
                'religion'               => $additionalData->religion,
                'gender'                 => $additionalData->gender,
                'maritalstatus'          => $additionalData->maritalstatus,
                'race'                   => $additionalData->race,
                'mailingaddress1'        => $additionalData->mailingaddress1,
                'mailingpostcode'        => $additionalData->mailingpostcode,
                'mailingtown'            => $additionalData->mailingtown,
                'homephoneno'            => $additionalData->homephoneno,
                'occupation'             => $additionalData->occupation,
                'employername'           => $additionalData->employername,
                'officeaddress1'         => $additionalData->officeaddress1,
                'officepostcode'         => $additionalData->officetown,
                'officestate'            => $additionalData->officestate,
                'dateofincorporation'    => $additionalData->dateofincorporation,
                'placeofincorporation'   => $additionalData->officepostcode,
                'businessdesc'           => $additionalData->businessdesc,
                'phonenoincorporation'   => $additionalData->phonenoincorporation,
                'jointtypeofid'          => $additionalData->jointtypeofid,
                'jointtitle'             => $additionalData->jointtitle,
                'jointnationality'       => $additionalData->jointnationality,
                'jointdateofbirth'       => $additionalData->jointdateofbirth,
                'jointbumiputera'        => $additionalData->jointbumiputera,
                'jointreligion'          => $additionalData->jointreligion,
                'jointgender'            => $additionalData->jointgender,
                'jointmaritalstatus'     => $additionalData->jointmaritalstatus,
                'jointrace'              => $additionalData->jointrace,
                
                'address'                => $myaccountholder->addressline1.' '.$myaccountholder->addressline2,
                'postcode'               => $myaccountholder->addresspostcode,
                'city'   	 	         => $myaccountholder->addresscity,
                'phoneno'                => $myaccountholder->phoneno,
                'email'                  => $myaccountholder->email,
                'occupation_category'    => $myaccountholder->occupationcategory,
                'nok_fullname'           => $myaccountholder->nokfullname,
                'nok_mykad'              => $myaccountholder->nokmykadno,
                'join_nok_phoneno'       => $myaccountholder->nokphoneno,
                'relationship'           => $myaccountholder->nokrelationship,
                'teller'                 => $teller,
                'jointdetails'           => $jointAccount['data']['joinaccount']
            ];
            

            return($data);

        } catch (\Exception $e) {
           
            throw $e;
        }
    }

    /**
     * Triggers job to send email for otc registration purpose
     * 
     * @param MyAccountHolder $account 
     * @param mixed $email 
     * @return MyToken 
     * @throws GeneralException 
     */
    public function sendEmailRegistration(MyAccountHolder $account_table, $pdf = null, $password = null, $partnercode = null, $pin = null)
    {
        $partnerinfo = $this->getsendernamesenderemail($account_table);
        $account = $this->app->myaccountholderStore()->searchView()->select()
        ->where('id', $account_table->id)
        ->one();
        $partner = $this->app->partnerStore()->getById($account->partnerid);
        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv){
            $projectUrl = $this->app->getConfig()->{'iframe.uat'} ? $this->app->getConfig()->{'iframe.uat'} : '';
            $qr_code = $partnercode ? '<img style="width: 20%;" src="src/resources/images/'.strtoupper($partnercode).'/link_qr_uat.png">' : '';
        }else{
            $projectUrl = $this->app->getConfig()->{'iframe.prd'} ? $this->app->getConfig()->{'iframe.prd'} : '';
            $qr_code = $partnercode ? '<img style="width: 20%;" src="src/resources/images/'.strtoupper($partnercode).'/link_qr.png">' : '';
        }

        if($pdf == null){
            // Generate email only
            $observation = new IObservation($account, IObservation::ACTION_VERIFY, 0, [
                'event' => MyGtpEventConfig::EVENT_REGISTRATION_INFORMATION,
                'projectbase' => $this->app->getConfig()->{'projectBase'},
                'sendername'            => $partnerinfo['sendername'],
                'senderemail'           => $partnerinfo['senderemail'],
                'accountholdercode'     => $account->accountholdercode,
                'mykadno'               => $account->mykadno,
                'name'                  => $account->fullname,
                'receiver'              => $account->email,
                'phoneno'               => $account->phoneno,
                'address'               => $account->addressline1,
                'city'                  => $account->addresscity,
                'postcode'              => $account->addresspostcode,
                'state'                 => $account->addressstate,
                'password'              => $password ? $password : '-',
                'pin'                   => $pin ? $pin : '-',
                'projecturl'            => $projectUrl,
                'partner'               => $partner->name,
                'bankname'              => $account->accountname,
                'bankaccountno'         => $account->accountnumber,
                'occupationcategory'    => $account->occupationcategory,
                'occupationsubcategory' => ($account->occupationsubcategory) ? $account->occupationsubcategory : '-',

                'nokname'               => $account->nokfullname,
                'nokemail'              => $account->nokemail,
                'nokmykadno'            => $account->nokmykadno,
                'nokpostcode'           => $account->nokphoneno,
                'nokaddress'            => $account->nokaddress,
                'nokrelationship'       => $account->nokrelationship,
                'dateregistered'        => $account->createdon->format('Y-m-d H:i:s'),

                'qrcode'                => $qr_code,

                'bccemail' => 'ang@silverstream.my'
            ]);
        }else{

                // pdf output
                // Generate email with pdf attachment
                $observation = new IObservation($account, IObservation::ACTION_VERIFY, 0, [
                    'event' => MyGtpEventConfig::EVENT_REGISTRATION_INFORMATION,
                    'projectbase' => $this->app->getConfig()->{'projectBase'},
                    'sendername'            => $partnerinfo['sendername'],
                    'senderemail'           => $partnerinfo['senderemail'],
                    // 'name' => $account->fullname,
                    // 'receiver' => $account->email,
                    // 'partnercode' => $partner->code,
                    // 'code' => $token->token,
                    // 'expiry' => $token->expireon,
                    // 'email' => urlencode($account->email),
                    'accountholdercode'     => $account->accountholdercode,
                    'mykadno'               => $account->mykadno,
                    'name'                  => $account->fullname,
                    'receiver'              => $account->email,
                    'phoneno'               => $account->phoneno,
                    'address'               => $account->addressline1,
                    'city'                  => $account->addresscity,
                    'postcode'              => $account->addresspostcode,
                    'state'                 => $account->addressstate,
                    'password'              => $password ? $password : '-',
                    'pin'                   => $pin ? $pin : '-',
                    'projecturl'            => $projectUrl,
                    'partner'               => $partner->name,
                    'bankname'              => $account->accountname,
                    'bankaccountno'         => $account->accountnumber,
                    'occupationcategory'    => $account->occupationcategory,
                    'occupationsubcategory' => ($account->occupationsubcategory) ? $account->occupationsubcategory : '-',

                    'nokname'               => $account->nokfullname,
                    'nokemail'              => $account->nokemail,
                    'nokmykadno'            => $account->nokmykadno,
                    'nokpostcode'           => $account->nokphoneno,
                    'nokaddress'            => $account->nokaddress,
                    'nokrelationship'       => $account->nokrelationship,
                    'dateregistered'        => $account->createdon->format('Y-m-d H:i:s'),

                    'qrcode'                => $qr_code,
                    'attachment'            => $pdf,
                    'bccemail' => 'ang@silverstream.my'
                    // 'occupation'            => $occupationCategory->category,
                    // 'suboccupation'         => '-',
                ]);
        }
      
        $this->notify($observation);

        return true;
    }
        
    /*
    * Encrypt Decrypt password for account
    *
    */
    public function encrypt_decrypt($string, $action = 'encrypt')
    {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'AA74CDCC2BBRT935136HH7B63C27'; // user define private key
        $secret_iv = '5fgf5HJ5g27'; // user define secret key
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
        if ($action == 'encrypt') {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }

    /*
    * Search & return partner code from email
    *
    */
    public function searchpartner($partner,$email)
    {
        $account = $this->app->myaccountholderStore()->searchTable()->select()
                        ->where('email', $email)
                        ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                        ->execute();

        if (!$account) {
            throw GeneralException::fromTransaction(null, [
                'message'   => "Invalid email address given."
            ]);
        } else {
            foreach($account as $anAcc){
                $partnerid      = $anAcc->partnerid;
                $partner        = $this->app->partnerStore()->getById($partnerid);
                $partnerCode[]  = $partner->code;
            }
        }

        return $partnerCode;
    }
    
    /**
     * Update MyAccountHolder data using Casa API.
     *
     * @param array $params Parameters to filter MyAccountHolder objects.
     * @return string Error message if Casa API is not found or no MyAccountHolder found, null otherwise.
     */
    public function updateMyAccountHolderByCasaApi(array $params = []): ?string
    {
        $casaApiClassname = $this->app->getConfig()->{'otc.casa.api'};

        // Check if OTC CASA API is available.
        if (empty($casaApiClassname)) {
            return 'OTC CASA API not found.';
        }
        
        $myaccountholderStore = $this->app->myaccountholderStore();
        $myaddressStore = $this->app->myaddressStore();
        $achadditionaldataStore = $this->app->achadditionaldataStore();
        $query = $myaccountholderStore->searchTable()->select();
        
        if (!empty($params)) {
            foreach ($params as $column => $value) {
                $query->where($column, '=', $value);
            }
        } else {
            $query->where('status', '=', MyAccountHolder::STATUS_ACTIVE);
        }
        
        $myAccountHolders = $query->execute();
        
        if (empty($myAccountHolders)) {
            return 'Account holder is empty.';
        }

        foreach ($myAccountHolders as $myAccountHolder) {
            $casaApi = BaseCasa::getInstance($casaApiClassname);
            
            $methodName = 'updateMyAccountHolder';
            if(!method_exists($casaApi, $methodName)) return 'updateMyAccountHolder is not found.';
            
            $updateColumns = call_user_func_array([$casaApi, $methodName], [$myAccountHolder]);
            if (!$updateColumns) continue;;
            if ($updateColumns['curl_error']) return $updateColumns['curl_error'];
            
            $procedureName = 'update_myaccountholder';
            if ($myaccountholderStore->isStoreProcedureExist($procedureName)) {
                $myaccountholderStore->callStoreProcedure($procedureName, $myAccountHolder->id, $updateColumns['myaccountholder']);
            } else {
                $noEmptyValueArray = array_filter($updateColumns['myaccountholder'], function($value) {
                    return !is_null($value) && $value !== ''; 
                });
				$fields = $myAccountHolder->getFields();
				foreach ($noEmptyValueArray as $key => $value) {
					if (in_array($key, $fields)) {
						$myAccountHolder->{$key} = $value;
					}
				}
                $myaccountholderStore->save($myAccountHolder);
            }
            
            $procedureName = 'update_myaddress';
            if ($myaddressStore->isStoreProcedureExist($procedureName)) {
                $myaddressStore->callStoreProcedure($procedureName, $myAccountHolder->id, $updateColumns['myaddress']);
            } else {
                $address = $myAccountHolder->getAddress();
				$line1 = (isset($updateColumns['myaddress']['line1']) && 0 < strlen($updateColumns['myaddress']['line1'])) ? $updateColumns['myaddress']['line1'] : $address->line1;
				$line2 = (isset($updateColumns['myaddress']['line2']) && 0 < strlen($updateColumns['myaddress']['line2'])) ? $updateColumns['myaddress']['line2'] : $address->line2;
				$city = (isset($updateColumns['myaddress']['city']) && 0 < strlen($updateColumns['myaddress']['city'])) ? $updateColumns['myaddress']['city'] : $address->city;
				$postcode = (isset($updateColumns['myaddress']['postcode']) && 0 < strlen($updateColumns['myaddress']['postcode'])) ? $updateColumns['myaddress']['postcode'] : $address->postcode;
				$state = (isset($updateColumns['myaddress']['state']) && 0 < strlen($updateColumns['myaddress']['state'])) ? $updateColumns['myaddress']['state'] : $address->state;
				$mailingline1 = (isset($updateColumns['myaddress']['mailingline1']) && 0 < strlen($updateColumns['myaddress']['mailingline1'])) ? $updateColumns['myaddress']['mailingline1'] : $address->mailingline1;
				$mailingline2 = (isset($updateColumns['myaddress']['mailingline2']) && 0 < strlen($updateColumns['myaddress']['mailingline2'])) ? $updateColumns['myaddress']['mailingline2'] : $address->mailingline2;
				$mailingcity = (isset($updateColumns['myaddress']['mailingcity']) && 0 < strlen($updateColumns['myaddress']['mailingcity'])) ? $updateColumns['myaddress']['mailingcity'] : $address->mailingcity;
				$mailingpostcode = (isset($updateColumns['myaddress']['mailingpostcode']) && 0 < strlen($updateColumns['myaddress']['mailingpostcode'])) ? $updateColumns['myaddress']['mailingpostcode'] : $address->mailingpostcode;
				$mailingstate = (isset($updateColumns['myaddress']['mailingstate']) && 0 < strlen($updateColumns['myaddress']['mailingstate'])) ? $updateColumns['myaddress']['mailingstate'] : $address->mailingstate;
				
                $myAccountHolder->updateAddress(
					$address, $line1, $line2, $city, $postcode, $state, $mailingline1, $mailingline2, $mailingcity, $mailingpostcode, $mailingstate
				);
            }
			
			$procedureName = 'update_achadditionaldata';
            if ($achadditionaldataStore->isStoreProcedureExist($procedureName)) {
                $achadditionaldataStore->callStoreProcedure($procedureName, $myAccountHolder->id, $updateColumns['achadditionaldata']);
            } else {
                $noEmptyValueArray = array_filter($updateColumns['achadditionaldata'], function($value) {
                    return !is_null($value) && $value !== ''; 
                });
				$achadditionaldata = $achadditionaldataStore->getByField('accountholderid',$myAccountHolder->id);
                $fields = $achadditionaldata->getFields();
				foreach ($noEmptyValueArray as $key => $value) {
					if (in_array($key, $fields)) {
						$achadditionaldata->{$key} = $value;
					}
				}
                $achadditionaldataStore->save($achadditionaldata);
            }
        }
        
        return 'Update account holder successfully.';
    }

    /**
     * Return the account holder details for selecting which account to login (BSN)
     *
     * @param  string  $mykadno   mykadno of account holder
     * @return array   $data      account holder details
     */
    public function getAccountHolderDetails(string $mykadno)
    {
        $data = [];

        $accountHolders = $this->app->myaccountholderStore()
            ->searchView()
            ->select()
            ->where('mykadno', $mykadno)
            ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
            ->execute();

        if (!$accountHolders) {
            throw GeneralException::fromTransaction([], ['message' => 'Account holder does not exist.']);
        }

        foreach ($accountHolders as $accountHolder) {
            if ($accountHolder->xaubalance == null || $accountHolder->xaubalance == '') {
                $xaubalance = 0;
            } else $xaubalance = $accountHolder->xaubalance;
            
            $data[] = [
                'merchant_id' => $accountHolder->partnercode,
                'accountnumber' => $accountHolder->accountnumber,
                'accountholdercode' => $accountHolder->accountholdercode,
                'xaubalance' => $xaubalance
            ];
        }

        return $data;
    }

    /**
     * Save the accountholdercode as the new password for the account holder (BSN)
     *
     * @param  string    $accountNo     accountnumber of accountholder
     */
    public function doCheckResetPassword(string $accountNo)
    {
        $accountHolder = $this->app->myaccountholderStore()
            ->searchTable()
            ->select()
            ->where('accountnumber', $accountNo)
            ->andWhere('status', '!=', MyAccountHolder::STATUS_CLOSED)
            ->one();
        
        if (!$accountHolder) {
            throw GeneralException::fromTransaction([], ['message' => 'Account holder does not exist.']);
        }

        if ($accountHolder->passwordset != MyAccountHolder::PASSWORD_SET_YES) {
            $this->savePassword($accountHolder, $accountHolder->accountholdercode);
            
            $accountHolder->passwordset = MyAccountHolder::PASSWORD_SET_YES;
            $this->app->myaccountholderStore()->save($accountHolder);
        }
    }
}
