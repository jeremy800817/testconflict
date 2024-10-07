<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\mygtp;

use Exception;
use Snap\api\exception\ApiException;
use Snap\api\exception\ApiParamGrantTypeInvalid;
use Snap\manager\MyGtpDocumentationManager;
use Snap\object\MyDocumentation;
use Snap\api\exception\GeneralException;
use Snap\api\exception\MyGtpPartnerMismatch;
use Snap\api\exception\MyGtpPartnerSettingsNotInitialized;
use Snap\api\exception\MyGtpPriceValidationNotValid;
use Snap\api\exception\MyGtpProductInvalid;
use Snap\api\exception\MyGtpStatementNotAvailable;
use Snap\api\exception\MyGtpTransactionNotExists;
use Snap\api\exception\MyGtpVerificationCodeInvalid;
use Snap\api\exception\MyGtpAccountHolderWrongPin;
use Snap\api\exception\MyGtpAccountHolderNotExist;
use Snap\manager\MyGtpTransactionManager;
use Snap\object\Logistic;
use Snap\object\MyAccountHolder;
use Snap\object\MyCloseReason;
use Snap\object\MyConversion;
use Snap\object\MyGoldTransaction;
use Snap\object\MyHistoricalPrice;
use Snap\object\MyLedger;
use Snap\object\MyMonthlyStorageFee;
use Snap\object\MyPriceAlert;
use Snap\object\Order;
use Snap\object\Redemption;
use Snap\object\VaultItem;
use Snap\object\VaultLocation;
use Snap\object\Partner;
use Snap\sqlrecorder;
use Spipu\Html2Pdf\Html2Pdf;

class MyGtpApiProcessor1_0my extends MyGtpApiProcessor {
    /**
     * Main method to process the incoming request.  Implemented class can get relevant
     * information about the action to be taken etc from the apiParam and then call the
     * appropriate manager to execute the main business logics.
     *
     * @param  App                      $app           App Class
     * @param  \Snap\api\param\ApiParam $apiParam      Api parameter object containing the decoded data
     * @param  array                    $decodedData   Decoded and converted data
     * @param  array                    $requestParams Original raw data from the API request
     * @return \Snap\api\param\ApiParam The response type represented as apiParams.
     */
    public function process($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams) {
        // TODO: Actions need to be reviewed

        // Check partner matches accountholder
        if (isset($decodedData['accountholder']) && $decodedData['partner']->id != $decodedData['accountholder']->partnerid) {
            throw MyGtpPartnerMismatch::fromTransaction([], ['message' => "Partner {$requestParams['merchant_id']} does not match the logged in user."]);
        }
        
        $settings = $app->mypartnersettingStore()->getByField('partnerid', $decodedData['partner']->id);
        if (!$settings) {
            throw MyGtpPartnerSettingsNotInitialized::fromTransaction([], ['code' => $requestParams['merchant_id']]);
        }

        // Get user's language settings if logged in
        if (isset($decodedData['accountholder'])) {
            $accHolder = $decodedData['accountholder'];
            $locale = self::LOCALE_MAP[$accHolder->preferredlang] ?? self::LOCALE_MAP[MyAccountHolder::LANG_EN];
            $domain = $app->getConfig()->{'projectBase'};

            setlocale(LC_MESSAGES, $locale);
            bind_textdomain_codeset($domain, 'UTF-8');
            bindtextdomain($domain, SNAPLIB_DIR . DIRECTORY_SEPARATOR . "resource/languages");
            textdomain($domain);
        }

        // Process action
        switch ($apiParam->getActionType()) {
            case 'verify_account':
                return $this->doVerifyAccount($app, $apiParam, $decodedData, $requestParams);
            // case 'verify_phone':
            //     return $this->doVerifyPhone($app, $apiParam, $decodedData, $requestParams);
            case 'resend_verification_email':
                return $this->doResendVerificationEmail($app, $apiParam, $decodedData, $requestParams);
            case 'resend_verification_phone':
                return $this->doResendVerificationPhone($app, $apiParam, $decodedData, $requestParams);
            case 'announcement_list':
                return $this->doAnnouncementList($app, $apiParam, $decodedData, $requestParams);
            case 'announcement':
                return $this->doAnnouncement($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'aqad':
                return $this->doAqad($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'app_config':
                return $this->doAppConfig($app, $apiParam, $decodedData, $requestParams);
            case 'legal_document':
                return $this->doLegalDocument($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'gold_prices':
                return $this->doGoldPrices($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'statement':
                return $this->doStatement($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'login_accno':
            case 'login_partner':
            case 'login_phone':
            case 'login':
                return $this->doLogin($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'refresh_token':
                return $this->doRefreshToken($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'register':
                return $this->doRegister($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'session_summary':
                return $this->doSessionSummary($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'forgot_password':
            case 'forgot_password_phone':
                return $this->doForgotPassword($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'reset_password':
            case 'reset_password_phone':
                return $this->doResetPassword($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'close_account':
                return $this->doCloseAccount($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'close_account_status':
                return $this->doCloseAccountStatus($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'forgot_pin':
                return $this->doForgotPin($app, $apiParam, $decodedData, $requestParams);
            case 'reset_pin':
                return $this->doResetPin($app, $apiParam, $decodedData, $requestParams);
            case 'pin_update':
                return $this->doPinUpdate($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'password_update':
                return $this->doPasswordUpdate($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'profile_update':
                return $this->doProfileUpdate($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'bank_account_update':
                return $this->doBankAccountUpdate($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'language_update':
                return $this->doLanguageUpdate($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'ekyc_onboarding':
                return $this->doEkycOnboarding($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'logout':
                return $this->doLogout($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'spot_acesell':
            case 'spot_acebuy':
                return $this->doSpotOrder($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'spot_status':
                return $this->doSpotStatus($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'spot_transaction_history':
                return $this->doSpotTransactionHistory($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'conversion_fee':
                return $this->doConversionFee($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'conversion':
                return $this->doConversion($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'conversion_history':
                return $this->doConversionHistory($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'price_alerts':
                return $this->doPriceAlerts($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'new_price_alert':
                return $this->doNewPriceAlert($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'delete_price_alert':
                return $this->doDeletePriceAlert($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'disbursement':
                return $this->doDisbursement($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'disbursement_history':
                return $this->doDisbursementHistory($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'pricestream':
                return $this->doPricestream($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'goldbar_list':
                return $this->doGoldBarList($app, $apiParam, $decodedData, $requestParams);

            // Development APIs
            case 'trigger_email':
                return $this->doTriggerEmail($app, $apiParam, $decodedData, $requestParams);
            case 'trigger_push':
                return $this->doTriggerPush($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'delete_account':
                return $this->doDeleteAccount($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'transferbetweendb':
                if ($requestParams['transactions']['redemption']) return $this->doTransferConversion($app, $apiParam, $decodedData, $requestParams);
                else return $this->doTransferOrder($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'friendinvitation':
                return $this->doFriendInvitation($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'goldtransfer':
                return $this->doGoldTransfer($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'checkcontact':
                return $this->doCheckContact($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'gold_transfer_history':
                return $this->doGoldTransferHistory($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'verify_pin':
                return $this->doVerifyPin($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'checking_partnercustid':
                return $this->doCheckingpPartnerCusId($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'uploadimg':
                return $this->doUploadImg($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'get_nric_img':
                return $this->getNricImg($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'updatemykadnumber':
                return $this->doUpdateCustomerMykad($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'updatepaymentgatewaywallet':
                return $this->doUpdatePaymentGateway($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'search_partner':
                return $this->doSearchPartner($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'pendingrefundredonewallet':
                return $this->doPendingRefundRedoneWallet($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'failedredonewallet':
                return $this->doFailedRedoneWallet($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'vault_store_inventory':
                return $this->VaultDataPartner($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'pricestream_notoken':
                return $this->doPricestreamNoToken($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'transferbetweensvr':
                return $this->doTransferConversionServer($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'getlogisticrecord':
                return $this->doGetLogisticRecord($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'getredemptionitems':
                return $this->doGetRedemptionItems($app, $apiParam, $decodedData, $requestParams);
                break;
            case 'get_account_no':
                return $this->doGetAccountNo($app, $apiParam, $decodedData, $requestParams);
                break;
        }

        return parent::process($app, $apiParam, $decodedData, $requestParams);
    }

    protected function doVerifyAccount($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $projectbase = $app->getConfig()->{'projectBase'};
        $destination = [];
        try {
            $success = $accountManager->verifyAccountHolderEmail(
                $decodedData['partner'],
                $decodedData['email'],
                $decodedData['code']
            );

            if (!$success) {
                throw MyGtpVerificationCodeInvalid::fromTransaction(null, ['message' => 'Not Success.']);
            }
           

            $responseParams = '<img class="img-fluid" style="width: 750px;height: 303px;" src="/src/resources/images/'.$projectbase.'/welcome.png" alt="'.$projectbase.' EKYC Approved"><br><br>'."Your e-mail address is verified successfully.";
        } catch (ApiException $e) {
            $responseParams = '<img class="img-fluid" style="width: 750px;height: 303px;" src="/src/resources/images/'.$projectbase.'/welcome.png" alt="'.$projectbase.' EKYC Approved"><br><br>'.$e->getMessage() . " Please re-send the verification e-mail from within the mobile app.";
            $destination['response_code'] = 403;
        }
        

        $sender = MyGtpApiSender::getInstance('Html', null);
        $sender->response($app, $responseParams, $destination);

        return $responseParams;
    }

    protected function doVerifyPhone($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $success = $accountManager->verifyAccountHolderPhone(
            $decodedData['accountholder'],
            $decodedData['phone_number'],
            $decodedData['code']
        );

        $responseParams = [
            'success'   => true,
            'data'      => []
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doResendVerificationEmail($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $success = $accountManager->sendEmailVerification($decodedData['partner'], $decodedData['email']);

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => gettext('Email sent successfully')
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doResendVerificationPhone($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $success = $accountManager->sendPhoneVerification($decodedData['partner'], $decodedData['phone_number']);

        $responseParams = [
            'success' => true,
            'data' => []
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doAnnouncementList($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAnnouncementManager */
        $announcementManager = $app->mygtpannouncementManager();
        if ($requestParams['merchant_id']){
            $partner = $app->partnerStore()->getByField('code', $requestParams['merchant_id']);
        }else{
            $partner = false;
        }
        $announcements = $announcementManager->getCoreActiveAnnouncements($partner);

        $data = [];
        foreach ($announcements as $announcement) {
            $attachments = $announcement->getAttachments();
            foreach ($attachments as $attachment) {
                $param = [
                    'version' => $requestParams['version'],
                    'merchant_id' => $requestParams['merchant_id'],
                    'action'  => 'announcement',
                    'pid' => intval($announcement->id), // Format follow announcementhandler
                    'aid' => intval($attachment->id)
                ];
                $data[] = [
                    //       https                                 ://      host.com              /mygtp.php                 ?    query
                    'url' => $_SERVER['HTTP_X_FORWARDED_PROTO'] . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?' . http_build_query($param)
                ];
            }
        }
        $response = [
            'success' => true,
            'data'    => $data
        ];

        $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doAnnouncement($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAnnouncementManager */
        $announcementManager = $app->mygtpannouncementManager();
        $attachment = $announcementManager->getAnnouncementAttachment($decodedData['pid'], $decodedData['aid']);

        $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Attachment', null);
        $response = $sender->response($app, $attachment);
        return $response;
    }

    protected function doAqad($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $isSell    = false != preg_match('/acebuy/i', $decodedData['type']);
        $orderType = $isSell ? Order::TYPE_COMPANYBUY : Order::TYPE_COMPANYSELL;
        $partner   = $decodedData['partner'];
        $product   = $decodedData['product'];
        $priceRef  = $requestParams['uuid'];
        $grams     = $partner->calculator(false)->round($requestParams['weight']);
        
        $priceObj = $app->pricestreamStore()->getByField('uuid', $priceRef);
        if (! $priceObj) {
            $this->log(__METHOD__."(): Unable to find price stream with uuid {$priceRef}", SNAP_LOG_DEBUG);
            throw MyGtpPriceValidationNotValid::fromTransaction(null);
        }
        
        $ppg          = $isSell ? $priceObj->companybuyppg : $priceObj->companysellppg;
        $ppg          = $partner->calculator()->round($ppg);
        
        /** @var MyGtpTransactionManager $txMgr */
        $txMgr = $app->mygtptransactionManager();

        $settlementMethodMap = [
            'fpx' => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
            'bank_account' => MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
            'wallet' => MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
            'loan' => MyGoldTransaction::SETTLEMENT_METHOD_LOAN,
        ];

        if (! isset($decodedData['settlement_method'])) {
            $settlementMethod = MyGoldTransaction::SETTLEMENT_METHOD_FPX;
            if ($isSell) {
                $settlementMethod = MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT;
            }
        } else {
            $settlementMethod = $settlementMethodMap[$decodedData['settlement_method']];
        }

		$accountHolder = $decodedData['accountholder'];
        $txMgr->validateBookGoldTxRequest($accountHolder, $partner, $product, $orderType, $priceRef, $grams,$settlementMethod,null,null,null,true);
		
		$memberType = $accountHolder->getAdditionalData()->category;
		
        $breakdownArr = $app->mygtptransactionManager()->bookGoldTransactionAmountBreakdown($partner, $product, $priceObj, $isSell, $grams, $settlementMethod, $decodedData['campaign_code'], $memberType);

        $spPrice = (!empty($breakdownArr['discount'])||$breakdownArr['discount'] != null) ? $breakdownArr['specialprice'] : null;

        $response = [
            'success' => true,
            'data' => [
                'weight' => floatval(number_format($grams, 3, '.', '')),
                'price'  => floatval(number_format($ppg, 2, '.', '')),
                'amount' => floatval(number_format($breakdownArr['amount'], 2, '.', '')),
                'transaction_fee' => floatval(number_format($breakdownArr['transaction_fee'], 2, '.', '')),
                'total_transaction_amount' => floatval(number_format($breakdownArr['total'], 2, '.', '')),
                'special_price' => ($spPrice) ? floatval(number_format($spPrice, 2, '.', '')) : null,
            ]
        ];

        $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doAppConfig($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $partnerMgr = $app->mygtppartnerManager();

        $config = $partnerMgr->getPartnerConfig($decodedData['partner']);
        $response = [
            'success' => true,
            'data' => [
                'app_config' => $config
            ]
        ];

        $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doLegalDocument($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Pdf', null);

        try {
            /** @var MyGtpDocumentationManager */
            $documentationMgr = $app->mygtpdocumentationManager();
            $documentContent = $documentationMgr->getDocumentForLanguage(strtoupper($decodedData['type']), strtoupper($decodedData['lang']), $decodedData['partner'] );

            $sender->response($app, file_get_contents($documentContent->filecontent));
            return $documentContent->filename;
        } catch (\Snap\api\exception\ApiException $e) {
            $errorMessage = 'Unable to process request: ' . $e->getMessage();
            $this->log($errorMessage, SNAP_LOG_ERROR);

            $sender->response($app, $e->getMessage(), ['response_code' => 404]);
            return $errorMessage;
        }

    }

    protected function doGoldPrices($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $provider = $app->priceProviderStore()->getForPartnerByProduct($decodedData['partner'], $decodedData['product']);

        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('priceproviderid', $provider->id);
        $conditionRecorder->where('priceon', '>=', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_from'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('priceon', '<', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_to'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('status', MyHistoricalPrice::STATUS_ACTIVE);

        $listings = $this->getListing($app->myhistoricalpriceStore(), $decodedData['page_number'], $decodedData['page_size'], true, ['open', 'close', 'high', 'low', 'priceproviderid', 'priceon'], ['priceon' => 'DESC'], 0, $conditionRecorder);

        $responseParams = [
            'success' => true,
            'data' => $this->formatHistoricalGoldPriceList($app, $listings['data']),
            'paging' => $listings['paging'],
        ];


        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doLogin($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $authMgr = $app->mygtpauthManager();
        $accountMgr = $app->MyGtpAccountManager();

        // Get access token
        switch ($requestParams['grant_type']) {
            case 'password':
                if ('login_phone' == $apiParam->getActionType()) {
                    $accessToken = $authMgr->loginPasswordGrantPhone($requestParams['phone_number'], $requestParams['password'], $decodedData['partner'], $decodedData);
                } elseif ('login_partner' == $apiParam->getActionType()) {
                    $accessToken = $authMgr->loginPasswordGrantPartner($requestParams['partner_customer_id'], $requestParams['password'], $decodedData['partner'], $decodedData);
                } elseif ('login_accno' == $apiParam->getActionType()) {
                    $accountMgr->doCheckResetPassword($requestParams['account_no']);
                    $accessToken = $authMgr->loginPasswordGrantAccNo($requestParams['account_no'].'_accountnumber', $requestParams['password'], $decodedData['partner'], $decodedData);
                } else {
                    $accessToken = $authMgr->loginPasswordGrant($requestParams['email'], $requestParams['password'], $decodedData['partner'], $decodedData);
                }
                break;

            default:
                throw ApiParamGrantTypeInvalid::fromTransaction(null, ['type' => $requestParams['grant_type']]);
        }

        // Register push token
        if (strlen($requestParams['push_token'])) {
            $app->mygtptokenManager()->registerPushToken($decodedData['accountholder'], $requestParams['push_token']);
        }

        // Get session summary
        $sessionSummary = $this->doSessionSummary($app, $apiParam, $decodedData, $requestParams, false)['data'];

        $token = [
            'token' => $accessToken
        ];
        $data = array_merge($token, $sessionSummary ?? []);

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, ['success' => true, 'data' => $data]);
        return $data;
    }

    public function doRefreshToken($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $authMgr = $app->mygtpauthManager();
        $accessToken = $authMgr->getNewAccessToken($requestParams['refresh_token'], $decodedData['partner']);
        $data = array_merge([
            'token' => $accessToken
        ]);

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, ['success' => true, 'data' => $data]);
        return $data;
    }

    public function doStatement($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $sender = MyGtpApiSender::getInstance('Pdf', null);

        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpStatementManager */
        $reportingManager = $app->mygtpStatementManager();
        $accHolder = $decodedData['accountholder'];        
        $dateStart = new \DateTime($decodedData['date_from']->format('Y-m-d H:i:s'), $app->getUserTimezone());
        $dateEnd   = new \DateTime($decodedData['date_to']->format('Y-m-d H:i:s'), $app->getUserTimezone());
        $dateNow   = new \DateTime('now', $app->getUserTimezone());
        $untilDate = clone $dateStart;
        $untilDate->modify("-1 second");

        // if ($accHolder->createdon > $dateEnd || $dateStart > $dateNow || $dateEnd > $dateNow) {
        //     throw \Snap\api\exception\MyGtpStatementNotAvailable::fromTransaction([], [
        //         'start' => $dateStart->format('Y-m-d H:i:s'), 
        //         'end' => $dateEnd->format('Y-m-d H:i:s')]
        //     );
        // }

        $openingBalance = $reportingManager->getStatementOpeningBalance($accHolder, $untilDate);
        $records        = $reportingManager->getStatementRecords($accHolder, $dateStart, $dateEnd);

        array_unshift($records,$openingBalance);
        $records = $reportingManager->formatStatementRecords($records);
        $titleDate = 'Transaction Listing For Individual Customers as At ' . $dateEnd->format('d F Y');
        $html = $reportingManager->getStatementAsHtml($accHolder, $titleDate, $records);
        $responseParams = $reportingManager->exportHtmlAsPdfString($html);
        $sender->response($app, $responseParams, ['attachment' => true, 'filename' => "Statement.pdf"]);

        return [];
    }

    protected function doRegister($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $sender = MyGtpApiSender::getInstance('Json', null);

        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $accHolder = $accountManager->register(
            $decodedData['partner'],
            strtoupper($decodedData['full_name']),
            $decodedData['mykad_number'],
            $decodedData['phone_number'],
            $decodedData['occupation_category'],
            $decodedData['occupation_subcategory'],
            $decodedData['email'],
            $decodedData['password'],
            strtoupper($decodedData['preferred_lang']),
            $decodedData['referral_branch_code'],
            $decodedData['nok_full_name'],
            $decodedData['nok_mykad_number'],
            $decodedData['nok_phone'],
            $decodedData['nok_email'],
            $decodedData['nok_address'],
            $decodedData['nok_relationship'],
            $decodedData['phone_verification_code'],
            $decodedData['referral_salesperson_code'],
            false,
            $decodedData['partner_customer_id'],
            $decodedData['partner_data']
        );

        $responseParams = [
            'success' => true,
            'data'    => [
                'message' => gettext('Account registration success')
            ]
        ];

        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doSessionSummary($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams, $sendResponse = true)
    {
        $partnerMgr = $app->mygtppartnerManager();
        $data = [];

        if (!isset($requestParams['type']) || "app_config" != $requestParams['type']) {
            $accHolder = $decodedData['accountholder'];
            $userSummary = $app->mygtpaccountManager()->getAccountHolderSummary($accHolder);
            $ekycStatus = $app->mygtpaccountManager()->getAccountHolderEkycProgress($accHolder);
            $data['user_summary'] = $userSummary;
            $data['ekyc'] = $ekycStatus;

        }

        if (!isset($requestParams['type']) || "user" != $requestParams['type']) {
            $config = $partnerMgr->getPartnerConfig($decodedData['partner']);
            $data['app_config'] = $config;
        }

        $response = [
            'success' => true,
            'data' => $data
        ];

        if ($sendResponse) {
            $sender = \Snap\api\mygtp\MyGtpApiSender::getInstance('Json', null);
            $sender->response($app, $response);
        }

        return $response;

    }

    protected function doForgotPassword($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {

        $version = strtolower($requestParams['version']);
        $isPhone = false != strstr($apiParam->getActionType(), "phone");

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        if ($isPhone) {
            $accountManager->forgotPasswordPhone(
                $decodedData['partner'],
                $decodedData['phone_number']
            );
        } else {
            $accountManager->forgotPassword(
                $decodedData['partner'],
                $decodedData['email']
            );
        }

        $successMessage = $isPhone ? gettext('Password reset code sent to the phone number.')
                                   : gettext('Password reset code sent to the email address.');

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => $successMessage
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doResetPassword($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $isPhone = false != strstr($apiParam->getActionType(), "phone");

        if ($isPhone) {
            $accountManager->resetPasswordPhone(
                $decodedData['partner'],
                $decodedData['phone_number'],
                $decodedData['password'],
                $decodedData['code']
            );
        } else {
            $accountManager->resetPassword(
                $decodedData['partner'],
                $decodedData['email'],
                $decodedData['password'],
                $decodedData['code']
            );
        }



        $responseParams = [
            'success' => true,
            'data' => [
                'message' => gettext('Password has been reset successfully.')
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }


    protected function doForgotPin($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $accountManager->forgotPin(
            $decodedData['partner'],
            $decodedData['accountholder']
        );

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => 'Pin reset code sent to the email address.'
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doResetPin($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $accountManager->resetPin(
            $decodedData['partner'],
            $decodedData['accountholder'],
            $decodedData['new_pin'],
            $decodedData['code']
        );

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => gettext('Pin has been reset successfully.')
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doCloseAccount($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $accountManager->closeAccount($decodedData['partner'], $decodedData['accountholder'], $decodedData['close_reason'], $decodedData['version'], $decodedData['pin']);

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => 'Account closure request successfully submitted'
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doCloseAccountStatus($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $pendingRequest = $accountManager->getCloseAccountStatus($decodedData['accountholder'], $decodedData['close_reason']);

        $data = null;

        if ($pendingRequest) {

            $data = [
                'status' => $pendingRequest->getStatusString(),
                'requested_on' => $pendingRequest->requestedon->format('Y-m-d H:i:s'),
                'remarks' => $pendingRequest->remarks ?? '',
                'close_reason_id' => $pendingRequest->reasonid,
            ];
        }

        $responseParams = [
            'success' => true,
            'data' => $data
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doPinUpdate($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->editPincode($decodedData['accountholder'], $decodedData['new_pin'], $decodedData['current_pin']);
        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully updated pin')
            ]
        ];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doPasswordUpdate($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->editPassword($decodedData['accountholder'], $decodedData['new_password'], $decodedData['current_password']);
        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully updated password')
            ]
        ];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doProfileUpdate($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->editProfile(
            $decodedData['accountholder'],
            $decodedData['address_line_1'],
            $decodedData['address_line_2'],
            $decodedData['postcode'],
            $decodedData['city'],
            $decodedData['state'],
            $decodedData['nok_full_name'],
            $decodedData['nok_mykad_number'],
            $decodedData['nok_phone'],
            $decodedData['nok_email'],
            $decodedData['nok_address'],
            $decodedData['nok_relationship'],
            $decodedData['occupation_category'],
            $decodedData['occupation_subcategory'],
            $decodedData['referral_salesperson_code'],
            $decodedData['referral_branch_code'],
            $decodedData['pin']
        );

        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully updated profile')
            ]
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doBankAccountUpdate($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->editBankAccount($decodedData['accountholder'], $decodedData['bank'], $decodedData['bank_acc_name'], $decodedData['bank_acc_number'], $decodedData['pin']);
        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully updated bank account information')
            ]
        ];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doLanguageUpdate($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->changeLanguage($decodedData['accountholder'], strtoupper($decodedData['preferred_lang']));

        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully changed the language')
            ]
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doEkycOnboarding($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();
        $accountManager->processEKycVerification($decodedData['accountholder'], $decodedData['partner'], $decodedData);

        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully uploaded images')
            ]
        ];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doLogout($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /** @var \Snap\manager\MyGtpAccountManager */
        $app->mygtpaccountManager()->logoutAccountHolder($decodedData['accountholder'], $requestParams['access_token']);

        $response = [
            'success' => true,
            'data'    => [
                'message'   => gettext('Successfully logged out')
            ]
        ];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doSpotOrder($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        //$decodedData['accountholder'] = $app->myaccountHolderStore()->getById(735); //for testing
        $aceSell = false != preg_match('/acesell/i', $apiParam->getActionType());
        $orderType = $aceSell ? Order::TYPE_COMPANYSELL : Order::TYPE_COMPANYBUY;

        /*get partner id & get list of partnerids that can skip confirmation. Wallet purpose*/
        $checkPartnerId = $decodedData['partner']->id;
        $getskipartnerids = $app->getConfig()->{'mygtp.partnerids.skipconfirmation'};
        $skipartnerids = explode(',', $getskipartnerids);
        /*end get partner id & get list of partnerids that can skip confirmation. Wallet purpose*/

        $settlementMethodMap = [
            'fpx' => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
            'bank_account' => MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT,
            'wallet' => MyGoldTransaction::SETTLEMENT_METHOD_WALLET,
            'loan' => MyGoldTransaction::SETTLEMENT_METHOD_LOAN
        ];

        $settlementMethod = $settlementMethodMap[$decodedData['settlement_method']];

        $decodedData['accountholder']->note = $decodedData['note']; // 20220406 - add new parameter note under accountholder. Toyyib wallet
        $decodedData['accountholder']->accesstoken = $requestParams['access_token']; //grab accesstoken
        // Create an order in the system
        $goldTx = $app->mygtptransactionManager()
                      ->bookGoldTransaction($decodedData['accountholder'], $decodedData['partner'], $decodedData['product'],
                                  $decodedData['uuid'], $orderType, $decodedData['weight'], $settlementMethod,
                                  $decodedData['version'], $decodedData['pin'], $decodedData['from_alert'], $decodedData['campaign_code'], $decodedData['partner_data']);

        if ( Order::TYPE_COMPANYSELL == $orderType && $goldTx) {
            // Spot Buy Transaction
            if (in_array($settlementMethod, [MyGoldTransaction::SETTLEMENT_METHOD_FPX, MyGoldTransaction::SETTLEMENT_METHOD_WALLET])) {
                $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $goldTx->refno);
                $location = $payment->location;

                if (! $location) {
                    $this->log(__METHOD__."(): Unable to obtain paywall location from payment detail");
                    throw GeneralException::fromTransaction(null, [
                        'message'   => 'Unable to retrieve payment wall location'
                    ]);
                }

                $order = $goldTx->getOrder();
                $data = [
                    'location'        => $location,
                    'refno'           => $payment->paymentrefno,
                    'transactionid'   => $goldTx->refno,
                    'amount'          => floatval(number_format($goldTx->originalamount, 2, '.', '')),
                    'transaction_fee' => floatval(number_format($order->fee, 2, '.', '')),
                    'total_transaction_amount'    => floatval(number_format($order->amount, 2, '.', ''))
                ];
            }
        } else {
            $disbursement = $app->mydisbursementStore()->getByField('transactionrefno', $goldTx->refno);

            /*TOYYIB wallet situation where GTP straight send to their wallet & receive status transaction*/
            //this is because transaction straight change to STATUS_PAID.It give error when trigger MyGtpTransactionManager::confirmBookGoldTransaction
            if(in_array($checkPartnerId,$skipartnerids)) {
                $this->log("[Skip confirmation for wallet] Partner id ".$checkPartnerId." wallet can skip confirmation.", SNAP_LOG_DEBUG);
                $goldTx->skipconfirm = 1;
            }
            
            // Spot Sell Transaction
            // Just confirm the sell transaction.
            $goldTx = $app->mygtptransactionManager()->confirmBookGoldTransaction($goldTx, MyLedger::TYPE_SELL);
            $data = [                
                'refno'           => $disbursement->refno,
                'transactionid'   => $goldTx->refno
            ];            

            if (MyGoldTransaction::SETTLEMENT_METHOD_WALLET == $goldTx->settlementmethod) {
                $data['location'] = $disbursement->location;
            }            
        }

        $response = ['success' => true];
        $response['data'] = $data ?? [];
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doSpotStatus($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        if (isset($decodedData['uuid'])) {
            $pricestream = $app->pricestreamStore()->getByField('uuid', $decodedData['uuid']);
            $order = $app->orderStore()->searchTable()->select()
                        ->where('buyerid', $decodedData['accountholder']->id)
                        ->andWhere('pricestreamid', $pricestream->id)
                        ->one();

            
            if (! $order) {
                throw MyGtpPriceValidationNotValid::fromTransaction(null);
            }

            $goldTx = $app->mygoldtransactionStore()->searchView(true, 2)->select()->where('orderid', $order->id)->one();

        } elseif (isset($decodedData['transactionid'])) {
            $goldTx = $app->mygoldtransactionStore()
                          ->searchView(true, 2)
                          ->select()
                          ->where('refno', $decodedData['transactionid'])
                          ->where('ordbuyerid', $decodedData['accountholder']->id)
                          ->one();
        }

        if ($goldTx) {
            $response = [
                'success' => true,
                'data' => current($this->formatSpotTransactionHistory($app, [$goldTx]))
            ];
        } else {
            throw MyGtpPriceValidationNotValid::fromTransaction(null);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doSpotTransactionHistory($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('ordbuyerid', $decodedData['accountholder']->id);
        $conditionRecorder->where('ordpartnerid', $decodedData['partner']->id);

        switch ($decodedData['type']) {
            // case 'spot_acesell':
            case 'buysellpromo':
                $conditionRecorder->where('ordtype', 'in', [Order::TYPE_COMPANYBUY, Order::TYPE_COMPANYSELL, MyLedger::TYPE_PROMO]);
                break;
            case 'buy':
                $conditionRecorder->where('ordtype', Order::TYPE_COMPANYSELL);
                break;
            // case 'spot_acebuy' :
            case 'sell' :
                $conditionRecorder->where('ordtype', Order::TYPE_COMPANYBUY);
                break;
            case 'adminstoragefee': 
                $conditionRecorder->where('ordtype', MyMonthlyStorageFee::TYPE_ADMIN_AND_STORAGE_FEE);
                break;
            case 'promo': 
                $conditionRecorder->where('ordtype', MyLedger::TYPE_PROMO);
                break;
            default:
                $conditionRecorder->where('ordtype', 'in', [Order::TYPE_COMPANYBUY, Order::TYPE_COMPANYSELL, MyMonthlyStorageFee::TYPE_ADMIN_AND_STORAGE_FEE, MyLedger::TYPE_PROMO]);
        }

        $conditionRecorder->where('createdon', '>=', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_from'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('createdon', '<', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_to'])->format('Y-m-d H:i:s'));

        $listings = $this->getListing($app->mygoldtransactionStore(), $decodedData['page_number'], $decodedData['page_size'], false, [], ['createdon' => 'DESC'], 2, $conditionRecorder);

        $response = [
            'success' => true,
            'data' => $this->formatSpotTransactionHistory($app, $listings['data']),
            'paging' => $listings['paging'],
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doConversionFee($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $product = $decodedData['product'];
        if ("DG-999-9" == $product->code) {
            $this->log(__METHOD__ . "(): Unable to perform conversion for product (Digital Gold)");
            throw MyGtpProductInvalid::fromTransaction([], ['code' => $product->code]);
        }

        $settlementMethodMap = [
            'fpx' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX,            
            'wallet' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET,
        ];

        if (! isset($decodedData['payment_mode'])) {
            $paymentMode = MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX;        
        } else {
            $paymentMode = $settlementMethodMap[$decodedData['payment_mode']];
        }

        /*additional data to add to pass to manager*/
        $decodedData['accountholder']->additionaldata = array(
            "methodofreceive" => $decodedData['methodofreceive'],
            "collectedbranchcode" => $decodedData['collectedbranchcode'],
        );

        $generatedData = $app->mygtpconversionManager()
                             ->getPreBookConversionAmount($decodedData['accountholder'],$decodedData['partner'], $product, $decodedData['quantity'], $paymentMode);
        

        $data = [
            // 'conversion_fee' => floatval($generatedData['totalRedemptionFee']),
            // 'insurance_fee'  => floatval($generatedData['totalInsuranceFee']),
            // 'courier_fee'    => floatval($generatedData['totalCourierCharges']),
            // 'transaction_fee'        => floatval($generatedData['totalFpxFee'])
            'conversion_fee' => floatval($generatedData['totalRedemptionFee'])
                              + floatval($generatedData['totalInsuranceFee'])
                              + floatval($generatedData['totalCourierCharges']),
            'transaction_fee'=> floatval($generatedData['totalPaymentFee']),
            'payment_mode' => $decodedData['payment_mode'] ?? 'fpx',
        ];
        $data['total_fee'] = $data['conversion_fee'] + $data['transaction_fee'];

        $response = [
            'success' => true,
            'data' => $data,
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doConversion($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $product = $decodedData['product'];
        if ("DG-999-9" == $product->code) {
            $this->log(__METHOD__ . "(): Unable to perform conversion for product (Digital Gold)");
            throw MyGtpProductInvalid::fromTransaction([], ['code' => $product->code]);
        }

        $settlementMethodMap = [
            'fpx' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX,            
            'wallet' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET,
            'casa' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_CASA
        ];

        if (! isset($decodedData['payment_mode'])) {
            $paymentMode = MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX;        
        } else {
            $paymentMode = $settlementMethodMap[$decodedData['payment_mode']];
        }

        $decodedData['accountholder']->note = $decodedData['note']; // 20220406 - add new parameter note under accountholder. Toyyib wallet
        $decodedData['accountholder']->accesstoken = $requestParams['access_token']; //grab accesstoken

        /*additional data to add to pass to manager*/
        $decodedData['accountholder']->additionaldata = array(
            "methodofreceive" => $decodedData['methodofreceive'],
            "collectedbranchcode" => $decodedData['collectedbranchcode'],
        );
        $conversions = $app->mygtpconversionManager()
                          ->doConversion($decodedData['accountholder'], $decodedData['partner'], $product, $decodedData['quantity'], $requestParams['version'], $paymentMode, $decodedData['campaign_code'], $decodedData['partner_data']);
        // Get payment wall location
        $matches = [];
        preg_match('/^CV\d+/', $conversions[0]->refno, $matches);
        $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $matches[0]);

        if(!empty($decodedData['note'])){ //Toyyib wallet case because when Toyyib return success, it means already paid. add by DK-20220420
            //overwrite conversion obj for Toyyib
            $refno = $conversions[0]->refno;
            $conversions[0] = $app->myconversionStore()->getByField('refno', $refno);
        }

        $data = [
            'location'  => $payment->location
        ];

        // Format response
        $conversionData = $this->formatConversionHistory($app, $conversions);
        foreach ($conversionData as &$cvData) {
            unset($cvData['items'][0]['serialnumber'], $cvData['logistics_log']);
        }
        unset ($cvData);
        foreach ($conversionData as $cvData) {
            $data['conversions'][] = $cvData;
        }
        
        $response = [
            'success' => true,
            'data' => $data,
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doConversionHistory($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('accountholderid', $decodedData['accountholder']->id);
        $conditionRecorder->where('rdmpartnerid', $decodedData['partner']->id);
        $conditionRecorder->where('createdon', '>=', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_from'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('createdon', '<', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_to'])->format('Y-m-d H:i:s'));
        $listings = $this->getListing($app->myconversionStore(), $decodedData['page_number'], $decodedData['page_size'], false, [], ['id' => 'DESC'], 1, $conditionRecorder);

        $response = [
            'success' => true,
            'data' => $this->formatConversionHistory($app, $listings['data']),
            'paging' => $listings['paging'],
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doPriceAlerts($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('accountholderid', $decodedData['accountholder']->id);
        $conditionRecorder->where('status', MyPriceAlert::STATUS_ACTIVE);
        $conditionRecorder->where('createdon', '>=', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_from'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('createdon', '<', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_to'])->format('Y-m-d H:i:s'));

        $listings = $this->getListing($app->mypricealertStore(), $decodedData['page_number'], $decodedData['page_size'], false, [], ['id' => 'DESC'], 0, $conditionRecorder);

        $responseParams = [
            'success' => true,
            'data' => $this->formatPriceAlertList($app, $decodedData['accountholder'], $listings['data']),
            'paging' => $listings['paging'],
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doNewPriceAlert($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {

        $version = strtolower($requestParams['version']);
        $type = strtolower($decodedData['type']) == 'buy' ? MyPriceAlert::TYPE_BUY : MyPriceAlert::TYPE_SELL;

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = $app->mygtpPriceAlertManager();

        $priceAlert = $priceAlertManager->addNewPriceAlert(
            $decodedData['accountholder'],
            $decodedData['price'],
            $type,
            $decodedData['remark']
        );

        $responseParams = [
            'success' => true,
            'data' => current($this->formatPriceAlertList($app, $decodedData['accountholder'], [$priceAlert])),
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doDeletePriceAlert($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {

        $version = strtolower($requestParams['version']);

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = $app->mygtpPriceAlertManager();

        $priceAlerts = $priceAlertManager->deletePriceAlert(
            $decodedData['accountholder'],
            $decodedData['price_alert']
        );

        $responseParams = [
            'success' => true,
            'data' => [
                'message' => 'Price alert successfully deleted.'
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doDisbursement($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        // TODO
    }

    protected function doDisbursementHistory($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('accountholderid', $decodedData['accountholder']->id);
        $conditionRecorder->where('createdon', '>=', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_from'])->format('Y-m-d H:i:s'));
        $conditionRecorder->where('createdon', '<', \Snap\Common::convertUserDatetimeToUTC($decodedData['date_to'])->format('Y-m-d H:i:s'));

        $listings = $this->getListing($app->mydisbursementStore(), $decodedData['page_number'], $decodedData['page_size'], false, [], ['id' => 'DESC'], 0, $conditionRecorder);

        $responseParams = [
            'success' => true,
            'data' => $this->formatdisbursementHistory($app, $listings['data']),
            'paging' => $listings['paging'],
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $responseParams);
        return $responseParams;
    }

    protected function doPricestream($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        try {
            $app->mygtptransactionManager()->subscribeToPriceStream($decodedData['accountholder'], $decodedData['partner']);

            $response = [
                'success'   => true
            ];

            // // No response needed if websocket connection opened
            // $sender = MyGtpApiSender::getInstance('Json', null);
            // $sender->response($app, $response);
            return $response;
        } catch (\Exception $e) {
            http_response_code(403);
            $ip = \Snap\Common::getRemoteIP();
            $this->log("[$ip](pricestream): ". $e->getMessage(), SNAP_LOG_INFO);
        }
        return ['success' => false];
    }

    protected function doGoldBarList($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $partner = $decodedData['partner'];
        $partnerVault = $app->vaultlocationStore()->searchTable()->select()
                            ->where('partnerid', $partner->id)
                            ->andWhere('type', VaultLocation::TYPE_END)
                            ->andWhere('status', VaultLocation::STATUS_ACTIVE)
                            ->one();

        if (!$partnerVault) {
            throw GeneralException::fromTransaction([], ['message' => gettext("Unable to get partner vault")]);
        }
        $condition = new sqlrecorder();
        $condition->where('vaultlocationid', $partnerVault->id)
                  ->andWhere('status', 'in', [VaultItem::STATUS_ACTIVE, VaultItem::STATUS_TRANSFERRING, VaultItem::STATUS_PENDING_ALLOCATION]);

        $listing = $this->getListing($app->vaultitemStore(), 1, 0, false, [], [], 0, $condition);

        $resourceDir = SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'resource' ;
        $template = file_get_contents($resourceDir . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'barlist.html');
        $template = str_replace('##IMAGE##', $resourceDir . DIRECTORY_SEPARATOR . 'image' . DIRECTORY_SEPARATOR .'header.jpg' ,$template);
        $template = str_replace('##DATE##', (new \DateTime('now', $app->getUserTimezone()))->format('l - M j, Y') ,$template);
        
        // Split into 10 per page
        $fp = array_splice($listing['data'],0,10);
        $goldBarArr = array_chunk($listing['data'], 15);
        array_unshift($goldBarArr, $fp);
        preg_match('/((?<=##FIRSTPAGE##)[\s\S]*?(?=##ENDFIRSTPAGE##))/', $template, $firstPageMatches);
        preg_match('/((?<=##ROW##)[\s\S]*?(?=##ENDROW##))/', $firstPageMatches[0], $matchRows);

        $replacements = '';
        $goldBars = array_shift($goldBarArr);
        foreach ($goldBars as $index => $bar) {
            $class = $index % 2 == true ? '' : 'even-row';
            $replacements .= str_replace(['##SERIALNO##', '##EVEN##'], [$bar->serialno, $class], $matchRows[0]);
        }
        $replacements = preg_replace('/##ROW##[\s\S]*##ENDROW##/', $replacements, $firstPageMatches[0]);

        $subsequentReplacements = '';
        preg_match('/((?<=##SUBSEQUENTPAGE##)[\s\S]*?(?=##ENDSUBSEQUENTPAGE##))/', $template, $subsequentPageMatches);
        foreach ($goldBarArr as $goldBars) {
            preg_match('/((?<=##ROW##)[\s\S]*?(?=##ENDROW##))/', $subsequentPageMatches[0], $matchRows);
            $subsequentReplacement = '';
            foreach ($goldBars as $index => $bar) {
                $class = $index % 2 == true ? '' : 'even-row';

                $subsequentReplacement .= str_replace(['##SERIALNO##', '##EVEN##'], [$bar->serialno, $class], $matchRows[0]);
            }
            $subsequentReplacements .= preg_replace('/##ROW##[\s\S]*##ENDROW##/', $subsequentReplacement, $subsequentPageMatches[0]);
        }

        $template = preg_replace('/##SUBSEQUENTPAGE##[\s\S]*##ENDSUBSEQUENTPAGE##/', $subsequentReplacements, $template);
        $template = preg_replace('/##FIRSTPAGE##[\s\S]*##ENDFIRSTPAGE##/', $replacements, $template);


        $html2pdf = new Html2Pdf('P', 'A4', 'en', true, 'UTF-8', array(0,0,0,0));
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($template);

        // For $dest = S, $name is ignored, as we are returning string
        $content = $html2pdf->output('export.pdf', 'S');

        $sender = MyGtpApiSender::getInstance('Pdf', null);
        $sender->response($app, $content, ['attachment' => true, 'filename' => "GoldBarList.pdf"]);
        return ['success' => true];
    }

    protected function doDeleteAccount($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $accHolder = $app->mygtpaccountManager()->disableAccountHolder($decodedData['accountholder']);

        $responseParams = ['success' => true, 'data' => []];
        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doTriggerPush($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $pushMgr = $app->mygtppushnotificationManager();        

        try {
            $pushMgr->doTriggerPushForEvent($requestParams['type'], $decodedData['accountholder'], $requestParams['id']);
        } catch (\Exception $e) {
            throw GeneralException::fromTransaction(null, [
                'message'   => $e->getMessage()
            ]);
        }
        
        
        $responseParams = ['success' => true, 'data' => []];
        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doTriggerEmail($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        switch ($decodedData['type']) {
            case 'EKYC_PASS':
                $result = new \stdClass();
                $result->result = 'P';
                $accHolder = $app->myaccountHolderStore()->getById($decodedData['id']);
                $accHolder = $app->mygtpaccountManager()->processKycResult($accHolder, $result);
                $app->mygtpscreeningManager()->screenAccountHolder($accHolder, false, true);
                break;
            case 'EKYC_FAIL':
                $result = new \stdClass();
                $result->result = 'F';
                $accHolder = $app->myaccountHolderStore()->getById($decodedData['id']);
                $accHolder = $app->mygtpaccountManager()->processKycResult($accHolder, $result);
                break;
            default:
                throw GeneralException::fromTransaction(null, ['message' => "Invalid type"]);
                break;
        }
        
        $responseParams = ['success' => true, 'data' => []];
        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    /**
     * Return formatted data for spot transaction history
     *
     * @return array
     */
    protected function formatSpotTransactionHistory($app, $transactions)
    {
        $data = [];
        foreach ($transactions as $tx)
        {
            $getOrder = $app->orderStore()->getByField('id', $tx->orderid);
            $txData = [];
            $txData['transactionid'] = $tx->refno;
            $txData['weight'] = floatval(number_format($tx->ordxau, 3,'.', ''));
            $txData['price'] = floatval(number_format($tx->ordprice, 2,'.', '')+($getOrder->discountprice));
            $txData['status'] = $tx->getStatusString();
            $txData['status_code'] = intval($tx->status);
            $txData['date'] = $tx->createdon->format('Y-m-d H:i:s');
            $txData['transaction_fee'] = floatval($tx->ordfee);

            if ($tx->ordtype == Order::TYPE_COMPANYBUY) {
                $type = "sell";
            } elseif ($tx->ordtype == Order::TYPE_COMPANYSELL) {
                $type = "buy";
            } elseif ($tx->ordtype == MyMonthlyStorageFee::TYPE_ADMIN_AND_STORAGE_FEE) {
                $type = 'adminstoragefee';
                $txData['transaction_fee'] = 0.00;
            } elseif ($tx->ordtype == MyLedger::TYPE_PROMO) {
                $type = 'promo';
                $txData['transaction_fee'] = 0.00;
            } else {
                $type = strtolower($tx->ordtype);
                $txData['transaction_fee'] = 0.00;
            }

            $txData['amount'] = floatval(number_format($tx->originalamount, 2,'.', ''));
            $txData['total_transaction_amount'] = floatval(number_format($tx->ordamount, 2, '.', ''));
            $txData['type'] = $type;
            $txData['settlement_method'] = strtolower($tx->settlementmethod);
            $data[] = $txData;
        }

        return $data;
    }

    /**
     * Returns formatted array of items in redemption
     * 
     * @param Redemption $redemption       Redemption
     * @param MyConversion $conversion     Conversion
     * 
     * @return array
     */
    protected function formatConversionItems($app, $redemption, $conversion)
    {
        // Temporary following MiGA format
        // Sample response after Redemptionmanager processing
        // [{"sapreturnid": 2645, "code": "GS-999-9-1g", "serialnumber": "IGR200142", "weight":"1.00000", "sapreverseno":"1901"}]
        $items = json_decode($redemption->items, true);
        $data = [];
        foreach ($items as $item) {
            // If redemption confirmed with SAP
            if (isset($item['sapreturnid'])) {
                $tmp = [
                    'serialnumber' => $item['serialnumber'],
                    'quantity'     => $item['quantity'] ?? 1
                ];

                $product = $app->productStore()->getByField('sapitemcode', $item['code']);
                $tmp['name'] = $product->name;
            } else {
                $tmp = [
                    'serialnumber' => $item['serialno'],
                    'name'         => $item['name'],
                    'quantity'     => $item['quantity']
                ];
            }

            // Dont return serial number if not paid
            if (MyConversion::STATUS_PAYMENT_PAID != $conversion->status) {
                $tmp['serialnumber'] = '';
            }

            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * Return formatted data for conversion history
     *
     * @param  array $conversions
     *
     * @return array
     */
    protected function formatConversionHistory($app, $conversions)
    {
        $convMgr = $app->mygtpconversionManager();
        $conversionData = [];
        foreach ($conversions as $conversion)
        {
            $redemption = $app->redemptionStore()->getById($conversion->redemptionid);
            $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $conversion->refno);
            $logistic = $app->logisticStore()->searchView()->select()
                            ->where('type', Logistic::TYPE_REDEMPTION)
                            ->andWhere('typeid', $redemption->id)
                            ->one();
            $data = [];

            $data['refno'] = $conversion->refno;
            $data['paymentrefno'] = $paymentDetail->paymentrefno;
            $data['conversion_fee'] = floatval($redemption->redemptionfee) 
                                    + floatval($redemption->specialdeliveryfee)
                                    + floatval($redemption->handlingfee)
                                    + floatval($redemption->insurancefee);
            $data['transaction_fee']= is_null($paymentDetail) ? 0.00 : floatval($paymentDetail->customerfee + $paymentDetail->gatewayfee);
            $data['total_fee']= $data['conversion_fee'] + $data['transaction_fee'];
            // $data['courier_fee'] = floatval($redemption->specialdeliveryfee) + floatval($redemption->handlingfee);
            // $data['insurance_fee'] = floatval($redemption->insurancefee);
            $data['created_on'] = $conversion->createdon->format('Y-m-d H:i:s');
            $data['status'] = $conversion->getStatusText();
            $data['status_code'] = intval($conversion->status);
            $data['payment_mode'] = strtolower($conversion->logisticfeepaymentmode);

            // Get the items for this conversion
            $data['items'] = $this->formatConversionItems($app, $redemption, $conversion);

            $data['logistic_status'] = gettext("Pending");
            $data['logistic_status_code'] = Logistic::STATUS_PENDING;
            $data['logistics_log'] = [];

            // Get total quantity of items
            $data['total_items'] = 0;
            foreach ($data['items'] as $item) {
                $data['total_items'] += $item['quantity'];
            }

            // Total weight of the redemption
            $data['total_weight'] = floatval($redemption->totalweight);

            if ($logistic) {
                // Sets logistic status
                $data['logistic_status']        = $logistic->getStatusText();
                $data['logistic_status_code']   = intval($logistic->status);
                $data['logistic_trackingnum']   = $logistic->awbno;
                $data['logistic_vendorname']    = $logistic->vendorname;

                // Get the logistic log for this conversion
                $logisticlogs = $convMgr->getRedemptionLogisticLog($app, $conversion->redemptionid);
                foreach ($logisticlogs as $log) {
                    $log->timeon->setTimezone($app->getUserTimezone());
                    $data['logistics_log'][] = [
                        'status'      => $log->readablestatus,
                        'status_code' => intval($log->value),
                        'date'        =>  $log->timeon->format('Y-m-d H:i:s')
                    ];
                }
            }



            $conversionData[] = $data;
        }

        return $conversionData;
    }

    /**
     * Return the formatted price alert data
     *
     * @return array
     */
    protected function formatPriceAlertList($app, $accountHolder, $priceAlerts)
    {

        $settings = $app->mypartnersettingStore()->getByField('partnerid', $accountHolder->partnerid);

        $duration = $settings->pricealertvaliddays;

        $priceAlertData = [];

        foreach ($priceAlerts as $priceAlert) {

            $createdon = $priceAlert->createdon;
            $expiry    = clone $createdon;
            $expiry->add(\DateInterval::createFromDateString("{$duration} days"));

            $lastTriggered = null != $priceAlert->lasttriggeredon ?
                                $priceAlert->lasttriggeredon->format('Y-m-d H:i:s') :
                                '';

            $priceAlertData[] = [
                'id' => intval($priceAlert->id),
                'price' => floatval($priceAlert->amount),
                'type' => $priceAlert->type == MyPriceAlert::TYPE_BUY ? 'Buy' : 'Sell',
                'type_code' => $priceAlert->type,
                'date' => $createdon->format('Y-m-d H:i:s'),
                'last_triggered' => $lastTriggered ?? '',
                'expiry' => $expiry->format('Y-m-d H:i:s'),
                'remarks' => $priceAlert->remarks ?? ''
            ];
        }

        return $priceAlertData;
    }

    /**
     * Return formatted data for disbursement history
     *
     * @return array
     */
    protected function formatdisbursementHistory($app, $disbursements)
    {
        $data = [];

        foreach ($disbursements as $dis) {
            $data[] = [
                'id'         => intval($dis->id),
                'amount'     => floatval($dis->amount),
                'status'     => $dis->getStatusString(),
                'created_on' => $dis->requestedon->format('Y-m-d H:i:s')
            ];
        }

        return $data;
    }

    /**
     * Return formatted data for gold prices
     *
     * @return array
     */
    public function formatHistoricalGoldPriceList($app, $prices)
    {
        $data = [];

        foreach ($prices as $price) {
            $data[] = [
                'date'       => $price->priceon->format('Y-m-d'),
                'opn_sell'   => floatval($price->open),
                'high_sell'  => floatval($price->high),
                'min_sell'   => floatval($price->low),
                'close_sell' => floatval($price->close)
            ];
        }

        return $data;
    }


    /**
     * Get the listing of records from the store together with paging metadata
     *
     * @param \Snap\dbdatastore $store
     * @param int               $page
     * @param int               $limit
     * @param boolean           $distinct
     * @param array             $fields
     * @param array             $orderby
     * @param int               $viewIndex
     * @param sqlRecorder       $conditionRecorder
     * @return array
     */
    protected function getListing($store, int $page, int $limit, $distinct = false, array $fields = [], array $orderby = [], $viewIndex = 0, $conditionRecorder = null)
    {
        $pagingArray = [];
        $dataArray   = [];

        if ($limit == 0) $limit	= 9999;
        $pagingArray['page_size'] = $limit;
        $pagingArray['page_number'] = $page;

        $offset = ($page - 1) * $limit;

        $mainRecorder = new sqlrecorder();

        if ($distinct) $mainRecorder->distinct();
        if ($conditionRecorder instanceof sqlRecorder && $conditionRecorder->hasRecording()) $mainRecorder->where( $conditionRecorder );

        $currentStoreHandler  = $store->searchView(false, $viewIndex);
        if ($distinct && !empty($fields)) {
            $prefixedFields = array_map(function ($field) use ($store) {
                return $store->getColumnPrefix() . $field;
            }, $fields);
            $currentStoreHandler  = $currentStoreHandler->select([$currentStoreHandler->raw('COUNT(DISTINCT '.implode(', ', $prefixedFields).') AS cnt')]);
        } else {
            $currentStoreHandler  = $currentStoreHandler->select([$currentStoreHandler->raw('COUNT(*) AS cnt')]);
        }

        $currentStoreHandler  = $mainRecorder->replayTo($currentStoreHandler);
        $totalRecords         = $currentStoreHandler->execute();
        $pagingArray['total'] = intval($totalRecords[0]['cnt']);

        // get the records for the listing purpose
        $currentStoreHandler = $store->searchView(true, $viewIndex);
        $currentStoreHandler = $currentStoreHandler->select($fields);
        $currentStoreHandler = $mainRecorder->replayTo($currentStoreHandler);
        if (count($orderby) > 0) $currentStoreHandler->orderBy($orderby);
        $currentStoreHandler->limit($offset, $limit);

        $dataArray = $currentStoreHandler->execute() ?? [];

        return [
            'data' => $dataArray,
            'paging' => $pagingArray,
        ];
    }

    protected function doTransferOrder($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $this->log("[Transfer DB Process] START TO ADD/EDIT DATA AT GTP DB", SNAP_LOG_DEBUG);
        $convertTrns                    = $requestParams['transactions'];
        $goldtransactions               = $convertTrns['goldtransaction'];
        $ordertransactions              = $convertTrns['ordertransaction'];
        $paymentDisbursetransactions    = $convertTrns['paydisbursetransaction'];
        $ledgerTransactions             = $convertTrns['ledgertransaction'];
        $extname                        = $requestParams['partnername'];
        $storageAdmTransactions         = $convertTrns['storageadmintrx'];

        if(isset($convertTrns['store'])) $store = $convertTrns['store'];

        if($store == 'mymonthlystoragefee'){
            $storageManager = $app->mygtpstorageManager();
            $storageAdmTransfer = $storageManager->createStorageAdmTrxFromDb($decodedData['partner'],$storageAdmTransactions,$extname);

            if($storageAdmTransfer){
                $responseParams = [
                    'success' => true,
                    'data'    => [
                        'message' => gettext("Monthly storage transaction for ".$decodedData['partner']->code." success add")
                    ]
                ];
            }
            $this->log("[Transfer DB Process] Successfully transfer mymonthlystorage for ".$decodedData['partner']->code." to other db.", SNAP_LOG_DEBUG);
        } else {
            $orderManager = $app->spotorderManager();
            $goldTransManager = $app->mygtptransactionManager();
            
            $orderTransfer = $orderManager->registerOrdFromDb($decodedData['partner'],$ordertransactions,$extname);

            if($orderTransfer) $goldTransManager->registerGoldFromDb($decodedData['partner'],$goldtransactions,$orderTransfer,$paymentDisbursetransactions,$ledgerTransactions);

            if($orderTransfer && $goldTransManager){
                $responseParams = [
                    'success' => true,
                    'data'    => [
                        'message' => gettext("Order ".$ordertransactions['orderno']." and mygoldtransaction ".$goldtransactions['refno']." success add")
                    ]
                ];
            }
            
            $this->log("[Transfer DB Process] Successfully transfer Order ".$ordertransactions['orderno']." and mygoldtransaction ".$goldtransactions['refno']." to other db.", SNAP_LOG_DEBUG);
        }

        return $responseParams;        
    }
    
    protected function doTransferConversion ($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $partner = $decodedData['partner'];
        $partnerName = $requestParams['transactions']['partnername'];
        $arrRedemption = $requestParams['transactions']['redemption'];
        $arrMyConversion = $requestParams['transactions']['myconversion'];
        $arrMyPaymentDetail = $requestParams['transactions']['mypaymentdetail'];
        $arrMyLedger = $requestParams['transactions']['myledger'];
        
        $responseParams = array(
            'success' => false,
            'data' => array(
                'message' => 'Register conversion from db failed.'
            )
        );
        
        $alreadyInTransaction = $app->getDbHandle()->inTransaction();
        
        if(! $alreadyInTransaction) {
            $app->getDbHandle()->beginTransaction();
        }
        
        try{
            $redemptionSaved = $app->redemptionManager()->registerRedemptionFromDb($partner, $arrRedemption, $partnerName);
            $myConversionSaved = $app->mygtpconversionManager()->registerConversionFromDb($partner, $redemptionSaved, $arrMyConversion);
            $myPaymentDetailSaved = $app->mygtptransactionManager()->registerPaymentFromDb($partner, $myConversionSaved, $arrMyPaymentDetail);
            $myLedgerSaved = $app->mygtptransactionManager()->registerLedgerFromDb($partner, $myPaymentDetailSaved, $myConversionSaved, $arrMyLedger);

            if(! $alreadyInTransaction) {
                $app->getDbHandle()->commit();
                
                $responseParams['success'] = true;
                $responseParams['data']['message'] = 'Register conversion from db successed.';
                $this->log(__function__ . ", Transfer conversion between Db successfully.", SNAP_LOG_DEBUG);
            }
        }catch(\Exception $e){
            if(! $alreadyInTransaction && $app->getDBHandle()->inTransaction()) {
                $app->getDbHandle()->rollback();
                $this->log(__function__ . ", Transfer conversion between Db rollback, error: " . $e->getMessage(), SNAP_LOG_ERROR);
            }
            
            throw $e;
        }
        
        return $responseParams;
    }

    protected function doFriendInvitation($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $goldInvite = $app->mygoldinvitetransferManager()->processFriendInvitation($decodedData['partner'],$decodedData['accountholder'],$decodedData['friendfullname'],$decodedData['friendemail'],$decodedData['friendcontactno'],$decodedData['referralcode'],$decodedData['message']);

        if ($goldInvite) {
            $response = [
                'success' => true,
                'data' => [
                    'friendname' => $goldInvite->receivername,
                    'friendemail' => $goldInvite->receiveremail,
                    'friendcontact' => $goldInvite->contact,
                ]
            ];
        } else {
            throw GeneralException::fromTransaction([], ['message' => gettext("There is error to invite friend")]);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doGoldTransfer($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $receiver_accountcode   = $decodedData['receiver_accountcode'];
        $receiver_phone         = $decodedData['receiver_phone_number'];
        $receiver_email         = $decodedData['receiver_email'];

        /*check pin*/
        $myaccmanager = $app->mygtpaccountManager();

        // Verify the pincode
        if (!$myaccmanager->verifyAccountHolderPin($decodedData['accountholder'], $decodedData['pin'])) {
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        }

        $receiver = $app->myaccountholderStore()->searchTable()
                        ->select()
                        ->where('accountholdercode', $receiver_accountcode)
                        ->andWhere('email', $receiver_email)
                        ->andWhere('phoneno', $receiver_phone)
                        ->one();

        if($receiver){
            $goldTransfer = $app->mygtptransfergoldManager()->transfer($decodedData['accountholder'], $receiver, 'xau', $decodedData['weight'], $decodedData['price'], $decodedData['message']);
        } else {
            throw GeneralException::fromTransaction([], ['message' => gettext("Accountholdercode ".$receiver_accountcode." and accountholder contact number ".$receiver_phone." and accountholder email ".$receiver_email." of receiver did not match or did not exist.")]);
        }

        if ($goldTransfer) {
            $response = [
                'success' => true,
                'data' => [
                    'receiver_name'         => $receiver->fullname,
                    'receiver_email'        => $receiver->email,
                    'receiver_phonenumber'  => $receiver->phoneno,
                    'receiver_accountcode'  => $decodedData['receiver_accountcode'],
                    'received_xau'          => $goldTransfer->xau,
                    'received_price'        => $goldTransfer->price,
                    'received_amount'       => $goldTransfer->amount,
                ]
            ];
        } else {
            throw GeneralException::fromTransaction([], ['message' => gettext("There is error to transfer gold to other account holder")]);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doCheckContact($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $getAccCheckContact = $app->mygtpaccountManager()->getAccountHolderCheckContact($decodedData['partner'],$decodedData['phone_number']);

        if ($getAccCheckContact) {
            $response = [
                'success' => true,
                'data' => [
                    'fullname'      => $getAccCheckContact->fullname,
                    'email'         => $getAccCheckContact->email,
                    'accountcode'   => $getAccCheckContact->accountholdercode,
                    'mykad_number'  => $getAccCheckContact->mykadno,
                    'phone_number'  => $getAccCheckContact->phoneno,
                ]
            ];
        } else {
            throw GeneralException::fromTransaction([], ['message' => gettext("There is error chen checking contact.")]);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }
    protected function doGoldTransferHistory($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        /*own transfer*/
        $conditionRecorder = new sqlRecorder();
        $conditionRecorder->where('accountholderid', $decodedData['accountholder']->id);
        $listings = $this->getListing($app->mytransfergoldStore(), 1, 0, false, [], ['createdon' => 'DESC'], 2, $conditionRecorder);

        /*receive gold transfer*/
        $conditionRecorderR = new sqlRecorder();
        $conditionRecorderR->where('toaccountholderid', $decodedData['accountholder']->id);
        $listingsR = $this->getListing($app->mytransfergoldStore(), 1, 0, false, [], ['createdon' => 'DESC'], 2, $conditionRecorderR);

        $data = [];
        foreach($listings['data'] as $aGoldTransferHistory){
            $getAccDetailsReceiver  = $app->myaccountHolderStore()->getById($aGoldTransferHistory->toaccountholderid);
            //$getAccDetailsSender    = $app->myaccountHolderStore()->getById($aGoldTransferHistory->fromaccountholderid);
            $getOwnAccDetails       = $app->myaccountHolderStore()->getById($aGoldTransferHistory->accountholderid);
            $txData = [];
            $txData['sendto']               = $getAccDetailsReceiver->fullname;
            $txData['sendtoaccholdercode']  = $getAccDetailsReceiver->accountholdercode;
            $txData['sendtophoneno']        = $getAccDetailsReceiver->phoneno;
            $txData['sendxau']              = $aGoldTransferHistory->xau;
            $txData['sendprice']            = $aGoldTransferHistory->price;
            $txData['sendtype']             = $aGoldTransferHistory->type;
            $txData['sendon']               = $aGoldTransferHistory->createdon->format('Y-m-d H:i:s');
            $txData['sendmessage']          = $aGoldTransferHistory->message;
            $txData['status']               = $aGoldTransferHistory->getStatusString();
            $data['assender'][] = $txData;
        }

        foreach($listingsR['data'] as $aGoldTransferHistory){
            //$getAccDetailsReceiver  = $app->myaccountHolderStore()->getById($aGoldTransferHistory->toaccountholderid);
            $getAccDetailsSender    = $app->myaccountHolderStore()->getById($aGoldTransferHistory->fromaccountholderid);
            $getOwnAccDetails       = $app->myaccountHolderStore()->getById($aGoldTransferHistory->accountholderid);
            $txData = [];
            $txData['receivefrom']               = $getAccDetailsSender->fullname;
            $txData['receivefromaccholdercode']  = $getAccDetailsSender->accountholdercode;
            $txData['receivefromphoneno']        = $getAccDetailsSender->phoneno;
            $txData['receivexau']                = $aGoldTransferHistory->xau;
            $txData['receiveprice']              = $aGoldTransferHistory->price;
            $txData['receivetype']               = $aGoldTransferHistory->type;
            $txData['receiveon']                 = $aGoldTransferHistory->createdon->format('Y-m-d H:i:s');
            $txData['receivemessage']            = $aGoldTransferHistory->message;
            $txData['status']                    = $aGoldTransferHistory->getStatusString();
            $data['asreceiver'][] = $txData;
        }

        $response = [
            'success' => true,
            'data' => $data
        ];

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doVerifyPin($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        //$accHolder  = $decodedData['accountholder'];
        $icNum      = $decodedData['mykad_number'];
        $pinInput   = $decodedData['pin'];

        //get accholder obj
        $accHolder = $app->myaccountholderStore()->searchTable()
                        ->select()
                        ->where('mykadno', $icNum)
                        ->andWhere('partnerid', $decodedData['partner']->id)
                        ->andWhere('status', 1)
                        ->one();
        if (!$accHolder) {
            $this->logDebug(__METHOD__."(): Account holder not exist");
            throw MyGtpAccountHolderNotExist::fromTransaction(null, ['message' => 'No account for Mykad Number '.$icNum.' under partner '.$decodedData['partner']->code]);
        }

        $accMgr         = $app->mygtpaccountManager();
        $checkVerifyPin = $accMgr->verifyAccountHolderPin($accHolder, $pinInput);

        if (!$checkVerifyPin) {
            $this->logDebug(__METHOD__."(): Account holder pin entered was incorrect");
            throw MyGtpAccountHolderWrongPin::fromTransaction(null);
        } else {
            $this->log(__METHOD__."(): Account holder pin entered was correct", SNAP_LOG_DEBUG);
            $response = [
                'success' => true,
                'data' => []
            ];
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

     protected function doCheckingpPartnerCusId($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $partnercusid = $decodedData['partner_customerid'];
        $partner      = $decodedData['partner'];

        //get accholder obj
        $accHolder = $app->myaccountholderStore()->searchTable()
                        ->select()
                        ->where('partnercusid', $partnercusid)
                        ->andWhere('partnerid', $decodedData['partner']->id)
                        ->andWhere('status', 1)
                        ->one();
        if (!$accHolder) {
            $this->logDebug(__METHOD__."(): Account holder not exist");
            throw MyGtpAccountHolderNotExist::fromTransaction(null, ['message' => 'No account for partner customer id '.$partnercusid.' under partner '.$partner->code]);
        } else {
            $this->log(__METHOD__."(): Account holder exist", SNAP_LOG_DEBUG);
            $response = [
                'success' => true,
                'data' => [
                    'contactno'      => $accHolder->phoneno
                ]
            ];
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doUploadImg($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $accHolder      = $decodedData['accountholder']; //acc obj
        $imgFrontName   = $decodedData['imagename'];
        $imgFront       = $decodedData['image'];
        $imgBackName    = $decodedData['imagebackname'];
        $imgBack        = $decodedData['imageback'];

        $imgManager = $app->mygtpaccountManager()->saveImageUpload($accHolder,$imgFrontName,$imgFront,$imgBackName,$imgBack);

        if ($imgManager) {
            $response = [
                'success' => true,
                'data' => [
                    'name'          => $imgManager->name,
                    'nric'          => $imgManager->ach_mykadno,
                    'image'         => $imgManager->image,
                    'imagename'     => $imgManager->imagename,
                    'imageback'     => $imgManager->imageback,
                    'imagebackname' => $imgManager->imagebackname,
                ]
            ];
        } else {
            $message = "doUploadImg::There is error when uploading image for ".$accHolder->mykadno." with partnerid ".$accHolder->partnerid.".";
            $this->log($message, SNAP_LOG_INFO);
            throw GeneralException::fromTransaction([], ['message' => gettext($message)]);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function getNricImg($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $accHolder      = $decodedData['accountholder']; //acc obj
        // $imgFrontName   = $decodedData['imagename'];
        // $imgFront       = $decodedData['image'];
        // $imgBackName    = $decodedData['imagebackname'];
        // $imgBack        = $decodedData['imageback'];

        $imgManager = $app->mygtpaccountManager()->getImageUpload($accHolder);

        if ($imgManager) {
            // generate from base 64 images 
            $front = '';
            $back = '';
            if(trim($imgManager->image) != ''){
                $front = 'data:jpg;base64,'.$imgManager->image;
            }

            if(trim($imgManager->imageback) != ''){
                $back = 'data:jpg;base64,'.$imgManager->imageback;
            }

            $response = [
                'success' => true,
                'data' => [
                    'name'          => $imgManager->name,
                    'nric'          => $imgManager->ach_mykadno,
                    'image'         => $front,
                    'imagename'     => $imgManager->imagename,
                    'imageback'     => $back,
                    'imagebackname' => $imgManager->imagebackname,
                ]
            ];
        } else {
            $message = "getNricImg::There is error when loading images for ".$accHolder->mykadno." with partnerid ".$accHolder->partnerid.".";
            $this->log($message, SNAP_LOG_INFO);
            throw GeneralException::fromTransaction([], ['message' => gettext($message)]);
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }
    
    protected function doUpdateCustomerMykad($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $accHolder      = $decodedData['accountholder'];
        $partner        = $decodedData['partner'];
        $mykadnumber    = $decodedData['mykad_number'];

        //get accholder obj
        $accHolder = $app->myaccountholderStore()->searchTable()
                        ->select()
                        ->where('id', $accHolder->id)
                        ->andWhere('partnerid', $partner->id)
                        ->andWhere('status', 1)
                        ->one();
        if (!$accHolder) {
            $this->logDebug(__METHOD__."(): Account holder not exist");
            throw MyGtpAccountHolderNotExist::fromTransaction(null, ['message' => 'No account for partner customer id '.$partnercusid.' under partner '.$partner->code]);
        } else {
            $updateMyKad = $app->mygtpaccountManager()->updateMyKadNumber($accHolder,$mykadnumber);
            if($updateMyKad){
                $this->log(__METHOD__."(): Account holder exist", SNAP_LOG_DEBUG);
                $response = [
                    'success' => true,
                    'data' => [
                        'accholdername'     => $updateMyKad->fullname,
                        'mykad_number'      => $updateMyKad->mykadno,
                    ]
                ];
            }
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doUpdatePaymentGateway($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $this->log("Start doUpdatePaymentGateway-----");

        $walletData        = $decodedData['details']['data'];
        $walletReference   = $walletData['gatewayReference'];
        $paymentrefno      = $decodedData['paymentrefno'];

        $payment = $app->mypaymentdetailStore()->getByField('paymentrefno', $paymentrefno);

        $updateGateway = $app->mygtptransactionManager()->updateGatewayReference($payment,$walletReference);

        if($updateGateway){
            $response = [
                'success' => true,
                'gatewayreference' => $walletReference,
                'paymentreference' => $paymentrefno
            ];
        }
        
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doSearchPartner($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $this->log("Start doSearchPartner-----");

        $email      = $decodedData['email'];
        $partner    = $decodedData['partner'];

        /** @var \Snap\manager\MyGtpAccountManager */
        $accountManager = $app->mygtpaccountManager();

        $accHolder = $accountManager->searchpartner($partner,$email);

        $responseParams = [
            'success' => true,
            'data'    => [
                'email' => $email,
                'partnercode' => $accHolder
            ]
        ];

        $sender = MyGtpApiSender::getInstance('Json', null);
        $sender->response($app, $responseParams);

        return $responseParams;
    }

    protected function doPendingRefundRedoneWallet($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $response = ['success' => false];
        try{
            $accHolder      = $decodedData['accountholder'];
            $partner        = $decodedData['partner'];
            $redoneToken = $decodedData['redonetoken'];

            $trxManager = $app->mygtptransactionManager()->sendPendingRefundTrxToPartner($redoneToken, $accHolder, $partner);

            $response['success'] = true;
            $response['data'] = ['message' => 'Pending Refund Transaction has been updated'];
        }
        catch(\Exception $e){
            $response['error_message'] = $e->getMessage();
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }

    protected function doFailedRedoneWallet($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $response = ['success' => false];
        try{
            $accHolder      = $decodedData['accountholder'];
            $partner        = $decodedData['partner'];
            $redoneToken = $decodedData['redonetoken'];

            $trxManager = $app->mygtptransactionManager()->sendFailedTrxToPartner($redoneToken, $accHolder, $partner);

            $response['success'] = true;
            $response['data'] = ['message' => 'Failed Transaction has been updated'];
        }
        catch(\Exception $e){
            $response['error_message'] = $e->getMessage();
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        return $response;
    }
    
    protected function VaultDataPartner($app, $apiParam, $decodedData, $requestParams)
    {
        $partner = $decodedData['partner'];
    
        $bankvaultpartner = $app->bankvaultManager();
        $vaultdatapartner = $bankvaultpartner->getVaultData($partner);

        $mygtptransaction = $app->mygtptransactionManager();
        $returnsumvault = $mygtptransaction->getPartnerBalances($partner);
        



        // Access the returned values
        $partner_Gold = [];
        foreach ($vaultdatapartner['partner_Gold'] as $index => $goldItem ) {
            $return = [
                'id' => $goldItem->id,
                'partnerid' => $goldItem->partnerid,
                'vaultlocationid' => $goldItem->vaultlocationid,
                'productid' => $goldItem->productid,
                'weight' => $goldItem->weight,
                'brand' => $goldItem->brand,
                'serialno' => $goldItem->serialno,
                'allocated' => $goldItem->allocated,
                'allocatedon' => $goldItem->allocatedon,
                'utilised' => $goldItem->utilised,
                'movetovaultlocationid' => $goldItem->movetovaultlocationid,
                'moverequestedon' => $goldItem->moverequestedon,
                'movecompletedon' => $goldItem->movecompletedon,
                'returnedon' => $goldItem->returnedon,
                'newvaultlocationid' => $goldItem->newvaultlocationid,
                'deliveryordernumber' => $goldItem->deliveryordernumber,
                'sharedgv' => $goldItem->sharedgv,
                'createdon' => $goldItem->createdon,
                'createdby' => $goldItem->createdby,
                'modifiedon' => $goldItem->modifiedon,
                'modifiedby' => $goldItem->modifiedby,
                'status' => $goldItem->status,
                'vaultlocationname' => $goldItem->vaultlocationname,
                'vaultlocationtype' => $goldItem->vaultlocationtype,
                'vaultlocationdefault' => $goldItem->vaultlocationdefault,
                'movetolocationpartnerid' => $goldItem->movetolocationpartnerid,
                'movetovaultlocationname' => $goldItem->movetovaultlocationname,
                'newvaultlocationname' => $goldItem->newvaultlocationname,
                'partnername' => $goldItem->partnername,
                'partnercode' => $goldItem->partnercode,
                'productname' => $goldItem->productname,
                'productcode' => $goldItem->productcode,
                'createdbyname' => $goldItem->createdbyname,
                'modifiedbyname' => $goldItem->modifiedbyname
            ];
            $partner_Gold[] = $return;
        }

        $vaultLocationStart = [];
        $return = [
            'id' => $vaultdatapartner['vaultLocationStart']->id,
            'partnerid' => $vaultdatapartner['vaultLocationStart']->partnerid,
            'name' => $vaultdatapartner['vaultLocationStart']->name,
            'type' => $vaultdatapartner['vaultLocationStart']->type,
            'minimumlevel' => $vaultdatapartner['vaultLocationStart']->minimumlevel,
            'reorderlevel' => $vaultdatapartner['vaultLocationStart']->reorderlevel,
            'defaultlocation' => $vaultdatapartner['vaultLocationStart']->defaultlocation,
            'createdon' => $vaultdatapartner['vaultLocationStart']->createdon,
            'createdby' => $vaultdatapartner['vaultLocationStart']->createdby,
            'modifiedon' => $vaultdatapartner['vaultLocationStart']->modifiedon,
            'modifiedby' => $vaultdatapartner['vaultLocationStart']->modifiedby,
            'status' => $vaultdatapartner['vaultLocationStart']->status,
            'partnername' => $vaultdatapartner['vaultLocationStart']->partnername,
            'partnercode' => $vaultdatapartner['vaultLocationStart']->partnercode,
            'createdbyname' => $vaultdatapartner['vaultLocationStart']->createdbyname,
            'modifiedbyname' => $vaultdatapartner['vaultLocationStart']->modifiedbyname
        ];
        $vaultLocationStart[] = $return;

        $vaultLocationEnd = [];
        $return = [
            'id' => $vaultdatapartner['vaultLocationEnd']->id,
            'partnerid' => $vaultdatapartner['vaultLocationEnd']->partnerid,
            'name' => $vaultdatapartner['vaultLocationEnd']->name,
            'type' => $vaultdatapartner['vaultLocationEnd']->type,
            'minimumlevel' => $vaultdatapartner['vaultLocationEnd']->minimumlevel,
            'reorderlevel' => $vaultdatapartner['vaultLocationEnd']->reorderlevel,
            'defaultlocation' => $vaultdatapartner['vaultLocationEnd']->defaultlocation,
            'createdon' => $vaultdatapartner['vaultLocationEnd']->createdon,
            'createdby' => $vaultdatapartner['vaultLocationEnd']->createdby,
            'modifiedon' => $vaultdatapartner['vaultLocationEnd']->modifiedon,
            'modifiedby' => $vaultdatapartner['vaultLocationEnd']->modifiedby,
            'status' => $vaultdatapartner['vaultLocationEnd']->status,
            'partnername' => $vaultdatapartner['vaultLocationEnd']->partnername,
            'partnercode' => $vaultdatapartner['vaultLocationEnd']->partnercode,
            'createdbyname' => $vaultdatapartner['vaultLocationEnd']->createdbyname,
            'modifiedbyname' => $vaultdatapartner['vaultLocationEnd']->modifiedbyname
        ];
        $vaultLocationEnd[] = $return;

        $vaultLocationEnd_2 = [];
        $return = [
            'id' => $vaultdatapartner['vaultLocationEnd2']->id,
            'partnerid' => $vaultdatapartner['vaultLocationEnd2']->partnerid,
            'name' => $vaultdatapartner['vaultLocationEnd2']->name,
            'type' => $vaultdatapartner['vaultLocationEnd2']->type,
            'minimumlevel' => $vaultdatapartner['vaultLocationEnd2']->minimumlevel,
            'reorderlevel' => $vaultdatapartner['vaultLocationEnd2']->reorderlevel,
            'defaultlocation' => $vaultdatapartner['vaultLocationEnd2']->defaultlocation,
            'createdon' => $vaultdatapartner['vaultLocationEnd2']->createdon,
            'createdby' => $vaultdatapartner['vaultLocationEnd2']->createdby,
            'modifiedon' => $vaultdatapartner['vaultLocationEnd2']->modifiedon,
            'modifiedby' => $vaultdatapartner['vaultLocationEnd2']->modifiedby,
            'status' => $vaultdatapartner['vaultLocationEnd2']->status,
            'partnername' => $vaultdatapartner['vaultLocationEnd2']->partnername,
            'partnercode' => $vaultdatapartner['vaultLocationEnd2']->partnercode,
            'createdbyname' => $vaultdatapartner['vaultLocationEnd2']->createdbyname,
            'modifiedbyname' => $vaultdatapartner['vaultLocationEnd2']->modifiedbyname
        ];
        $vaultLocationEnd_2[] = $return;


        $items = [];
        foreach ($vaultdatapartner['items'] as $item) {
            $return = [
                'id' => $item->id,
                'partnerid' => $item->partnerid,
                'vaultlocationid' => $item->vaultlocationid,
                'productid' => $item->productid,
                'weight' => $item->weight,
                'brand' => $item->brand,
                'serialno' => $item->serialno,
                'allocated' => $item->allocated,
                'allocatedon' => $item->allocatedon,
                'utilised' => $item->utilised,
                'movetovaultlocationid' => $item->movetovaultlocationid,
                'moverequestedon' => $item->moverequestedon,
                'movecompletedon' => $item->movecompletedon,
                'returnedon' => $item->returnedon,
                'newvaultlocationid' => $item->newvaultlocationid,
                'deliveryordernumber' => $item->deliveryordernumber,
                'sharedgv' => $item->sharedgv,
                'createdon' => $item->createdon,
                'createdby' => $item->createdby,
                'modifiedon' => $item->modifiedon,
                'modifiedby' => $item->modifiedby,
                'status' => $item->status,
                'vaultlocationname' => $item->vaultlocationname,
                'vaultlocationtype' => $item->vaultlocationtype,
                'vaultlocationdefault' => $item->vaultlocationdefault,
                'movetolocationpartnerid' => $item->movetolocationpartnerid,
                'movetovaultlocationname' => $item->movetovaultlocationname,
                'newvaultlocationname' => $item->newvaultlocationname,
                'partnername' => $item->partnername,
                'partnercode' => $item->partnercode,
                'productname' => $item->productname,
                'productcode' => $item->productcode,
                'createdbyname' => $item->createdbyname,
                'modifiedbyname' => $item->modifiedbyname
            ];
            $items[] = $return;
        }

        $items_2 = [];
        foreach ($vaultdatapartner['items2'] as $items2) {
            $return = [
                'id' => $items2->id,
                'partnerid' => $items2->partnerid,
                'vaultlocationid' => $items2->vaultlocationid,
                'productid' => $items2->productid,
                'weight' => $items2->weight,
                'brand' => $items2->brand,
                'serialno' => $items2->serialno,
                'allocated' => $items2->allocated,
                'allocatedon' => $items2->allocatedon,
                'utilised' => $items2->utilised,
                'movetovaultlocationid' => $items2->movetovaultlocationid,
                'moverequestedon' => $items2->moverequestedon,
                'movecompletedon' => $items2->movecompletedon,
                'returnedon' => $items2->returnedon,
                'newvaultlocationid' => $items2->newvaultlocationid,
                'deliveryordernumber' => $items2->deliveryordernumber,
                'sharedgv' => $items2->sharedgv,
                'createdon' => $items2->createdon,
                'createdby' => $items2->createdby,
                'modifiedon' => $items2->modifiedon,
                'modifiedby' => $items2->modifiedby,
                'status' => $items2->status,
                'vaultlocationname' => $items2->vaultlocationname,
                'vaultlocationtype' => $items2->vaultlocationtype,
                'vaultlocationdefault' => $items2->vaultlocationdefault,
                'movetolocationpartnerid' => $items2->movetolocationpartnerid,
                'movetovaultlocationname' => $items2->movetovaultlocationname,
                'newvaultlocationname' => $items2->newvaultlocationname,
                'partnername' => $items2->partnername,
                'partnercode' => $items2->partnercode,
                'productname' => $items2->productname,
                'productcode' => $items2->productcode,
                'createdbyname' => $items2->createdbyname,
                'modifiedbyname' => $items2->modifiedbyname
            ];
            $items_2[] = $return;
        }

        $items_3 = [];
        foreach ($vaultdatapartner['items3'] as $items3) {
            $return = [
                'id' => $items3->id,
                'partnerid' => $items3->partnerid,
                'vaultlocationid' => $items3->vaultlocationid,
                'productid' => $items3->productid,
                'weight' => $items3->weight,
                'brand' => $items3->brand,
                'serialno' => $items3->serialno,
                'allocated' => $items3->allocated,
                'allocatedon' => $items3->allocatedon,
                'utilised' => $items3->utilised,
                'movetovaultlocationid' => $items3->movetovaultlocationid,
                'moverequestedon' => $items3->moverequestedon,
                'movecompletedon' => $items3->movecompletedon,
                'returnedon' => $items3->returnedon,
                'newvaultlocationid' => $items3->newvaultlocationid,
                'deliveryordernumber' => $items3->deliveryordernumber,
                'sharedgv' => $items3->sharedgv,
                'createdon' => $items3->createdon,
                'createdby' => $items3->createdby,
                'modifiedon' => $items3->modifiedon,
                'modifiedby' => $items3->modifiedby,
                'status' => $items3->status,
                'vaultlocationname' => $items3->vaultlocationname,
                'vaultlocationtype' => $items3->vaultlocationtype,
                'vaultlocationdefault' => $items3->vaultlocationdefault,
                'movetolocationpartnerid' => $items3->movetolocationpartnerid,
                'movetovaultlocationname' => $items3->movetovaultlocationname,
                'newvaultlocationname' => $items3->newvaultlocationname,
                'partnername' => $items3->partnername,
                'partnercode' => $items3->partnercode,
                'productname' => $items3->productname,
                'productcode' => $items3->productcode,
                'createdbyname' => $items3->createdbyname,
                'modifiedbyname' => $items3->modifiedbyname
            ];
            $items_3[] = $return;
        }

        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, [
            'partner_Gold' => $partner_Gold,
            'vaultLocationEnd' => $vaultLocationEnd,
            'vaultLocationEnd2' => $vaultLocationEnd_2,
            'vaultLocationStart' => $vaultLocationStart,
            'items' => $items,
            'items2' => $items_2,
            'items3' => $items_3,
            'vaultamount' => $returnsumvault['vaultamount'],
            'totalcustomerholding' => $returnsumvault['totalcustomerholding'],
            'totalbalance' => $returnsumvault['totalbalance'],
            'pendingtransaction' => $returnsumvault['pendingtransaction'],
        ]);
        
        return [
            'partner_Gold' => $partner_Gold,
            'vaultLocationEnd' => $vaultLocationEnd,
            'vaultLocationEnd_2' => $vaultLocationEnd_2,
            'vaultLocationStart' => $vaultLocationStart,
            'items' => $items,
            'items2' => $items_2,
            'items3' => $items_3,
            'vaultamount' => $returnsumvault['vaultamount'],
            'totalcustomerholding' => $returnsumvault['totalcustomerholding'],
            'totalbalance' => $returnsumvault['totalbalance'],
            'pendingtransaction' => $returnsumvault['pendingtransaction'],
        ];
    }

    protected function doPricestreamNoToken($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        try {
            $acc = New MyAccountHolder($app->myaccountholderStore());
            $app->mygtptransactionManager()->subscribeToPriceStream($acc, $decodedData['partner']);
            $response = [
                'success'   => true
            ];
            // // No response needed if websocket connection opened
            // $sender = MyGtpApiSender::getInstance('Json', null);
            // $sender->response($app, $response);
            return $response;
        } catch (\Exception $e) {
            http_response_code(403);
            $ip = \Snap\Common::getRemoteIP();
            $this->log("[$ip](pricestream): ". $e->getMessage(), SNAP_LOG_INFO);
        }
        return ['success' => false];
    }

    protected function doTransferConversionServer ($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $partner = $decodedData['partner'];
        $partnerName = $requestParams['partnername'];
        $arrRedemption = $requestParams['transactions']['redemption'];
        $arrMyConversion = $requestParams['transactions']['conversion'];
        $arrMyPaymentDetail = $requestParams['transactions']['paymentdetail'];
        $arrMyLedger = $requestParams['transactions']['ledger'];
        
        $responseParams = array(
            'success' => false,
            'data' => array(
                'message' => 'Register conversion from db failed.'
            )
        );
        
        $alreadyInTransaction = $app->getDbHandle()->inTransaction();
        
        if(! $alreadyInTransaction) {
            $app->getDbHandle()->beginTransaction();
        }
        
        try{
            // first, save data
            if(isset($arrRedemption['id'])){
                unset($arrRedemption['id']);
            }
            if(isset($arrMyConversion['id'])){
                unset($arrMyConversion['id']);
            }
            if(isset($arrMyPaymentDetail['id'])){
                unset($arrMyPaymentDetail['id']);
            }
            if(isset($arrMyLedger['id'])){
                unset($arrMyLedger['id']);
            }
            $redemptionSaved = $app->redemptionManager()->registerRedemptionFromSvr($partner, $arrRedemption, $partnerName);
            $myConversionSaved = $app->mygtpconversionManager()->registerConversionFromSvr($partner, $redemptionSaved, $arrMyConversion);
            $sourcerefno = $arrMyPaymentDetail['sourcerefno']."_".$partnerName;
            $this->log(__function__ . ", sourcerefno: ".$sourcerefno, SNAP_LOG_DEBUG);
            $payment = $app->mypaymentdetailStore()->searchTable()->select()->where('sourcerefno', $sourcerefno)->execute();
            $this->log(__function__ . ", count payment: ".count($payment), SNAP_LOG_DEBUG);
            if(count($payment) == 0){
                $myPaymentDetailSaved = $app->mygtptransactionManager()->registerPaymentFromSvr($partner, $myConversionSaved, $arrMyPaymentDetail,$partnerName);
            }
            $myLedgerSaved = $app->mygtptransactionManager()->registerLedgerFromSvr($partner, $myPaymentDetailSaved, $myConversionSaved, $arrMyLedger);
            
            //then, send to sap here
            $branchId = null;
            $deliveryInfos = new \stdClass;
            $deliveryInfos->contactname1     = $redemptionSaved->deliverycontactname1;
            $deliveryInfos->contact_mobile1  = $redemptionSaved->deliverycontactno1;
            $deliveryInfos->address1         = $redemptionSaved->deliveryaddress1;
            $deliveryInfos->address2         = $redemptionSaved->deliveryaddress2;
            $deliveryInfos->city             = $redemptionSaved->deliverycity;
            $deliveryInfos->postcode         = $redemptionSaved->deliverypostcode;
            $deliveryInfos->state            = $redemptionSaved->deliverystate;
            $deliveryInfos->country          = $redemptionSaved->deliverycountry;
            //$this->log(__function__ . ", deliveryInfo: ".print_r($deliveryInfo), SNAP_LOG_DEBUG);
            $scheduleInfo = [];
            $submitSAP = true;
            $rdm = $app->redemptionManager()->confirmRedemption($redemptionSaved, $partner, $branchId, $deliveryInfos, $scheduleInfo, $submitSAP);
            if($rdm){
                $cvn = $app->mygtpconversionManager()->jobSendConversionFeeToSAP($partner, $myConversionSaved);
            }
            if(! $alreadyInTransaction) {
                $app->getDbHandle()->commit();
            }
            $responseParams['success'] = true;
            $responseParams['data']['redemptionItems'] = $rdm->items;
            $responseParams['data']['message'] = 'Register conversion from db successed.';
            
            $this->log(__function__ . ", Transfer conversion between Db successfully.", SNAP_LOG_DEBUG);
        }catch(\Throwable $e){
            if(! $alreadyInTransaction && $app->getDBHandle()->inTransaction()) {
                $app->getDbHandle()->rollback();
                $this->log(__function__ . ", Transfer conversion between Db rollback, error: " . $e->getMessage(), SNAP_LOG_ERROR);
            }
            
            throw $e;
        }
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $responseParams);
        // return $response;
        
        return $responseParams;
    }

    protected function doGetLogisticRecord($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $response = ['success' => false];
        try{
            $redemption = $app->redemptionStore()->searchTable()->select()->where('redemptionno', $requestParams['redemptionno'])->one();
            if(!$redemption){
                Throw new \Exception('GTP Redemption not found');
            }
            $logistic = $app->logisticStore()->searchTable()->select()->where('typeid', $redemption->id)->execute();
            if(count($logistic) > 0){
                $logisticlog = $app->logisticlogStore()->searchTable()->select()->where('logisticid', $logistic[0]->id)->execute();
            }
            else{
                $logistic = [];
                $logisticlog = [];
            }
            $logisticdata = [];
            if(count($logistic) > 0){
                foreach($logistic as $record){
                    $logisticdata = $record->toArray();
                }
            }
            $logisticlogdata = [];
            if(count($logisticlog) > 0){
                foreach($logisticlog as $record){
                    array_push($logisticlogdata,$record->toArray());
                }
            }
            $response['success'] = true;
            $response['data'] = [
                'logistic' => $logisticdata,
                'logisticlog' => $logisticlogdata
            ];
        }
        catch(\Throwable $e){
            $response['success'] = false;
            $response['error_message'] = $e->getMessage();
        }
        
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        
        return $response;
    }

    protected function doGetRedemptionItems($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams){
        $response = ['success' => false];
        try{
            if($requestParams['redemptionno'] == '') Throw new \Exception("Redemptionno not supplied");
            $redemption = $app->redemptionStore()->searchTable()->select()->where('redemptionno', $requestParams['redemptionno'])->one();
            if(!$redemption){
                Throw new \Exception('Redemption record not found');
            }
            $items = $redemption->items;
            $check = json_decode($items, true);
            if(!isset($check[0]['sapreturnid'])){
                Throw new \Exception("No redemption items found");
            }
            $response['success'] = true;
            $response['data'] = [
                'redemptionItems' => $items
            ];
        }
        catch(\Throwable $e){
            $response['success'] = false;
            $response['error_message'] = $e->getMessage();
        }
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, $response);
        
        return $response;
    }

    protected function doGetAccountNo($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        $accountManager = $app->MyGtpAccountManager();
        $accHolderDetails = $accountManager->getAccountHolderDetails($requestParams['mykadno']);
        $sender = MyGtpApiSender::getInstance("Json", null);
        $sender->response($app, ['success' => true, 'data' => $accHolderDetails]);
        return $accHolderDetails;
    }
}
