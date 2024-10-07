<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\mygtp;

use Snap\IApiProcessor;
use Snap\api\param\ApiParam;
use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\TLogging;

/**
 * This processor class defines a main factory method to provide customisation on different API versions.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class MyGtpApiProcessor implements IApiProcessor
{
    protected const LOCALE_MAP = [
        MyAccountHolder::LANG_EN => 'en_US.utf8',
        MyAccountHolder::LANG_BM => 'ms_MY.utf8',
        MyAccountHolder::LANG_CN => 'zh_CN.utf8',
    ];

    use TLogging;
    /**
     * Formats exceptions to MyGTP exception codes
     *
     */
    public function formatException($e, $customizeMsg=''){
        $codeMap = [
            // Format - {subsystem}:1{category}:2{number}:2

            // Param
            'Snap\api\exception\ApiProcessorNoActionFound' => 10001,
            'Snap\api\exception\ApiParamRequestNotFound' => 10002,
            'Snap\api\exception\ApiParamEmailInvalid' => 10003,
            'Snap\api\exception\ApiParamPhoneInvalid' => 10004,
            'Snap\api\exception\ApiParamConfirmationInvalid' => 10005,
            'Snap\api\exception\ApiParamRecordNotFound' => 10006,
            'Snap\api\exception\ApiParamDatetimeInvalid' => 10007,
            'Snap\api\exception\ApiParamGrantTypeInvalid' => 10008,
            // Auth
            'Snap\api\exception\ApiInvalidAccessToken' => 10101,
            'Snap\api\exception\CredentialInvalid' => 10102,
            'Snap\api\exception\MyGtpActionNotPermitted' => 10103,
            'Snap\api\exception\IpMaximumRetries' => 10104,
            'Snap\api\exception\RefreshTokenInvalid' => 10105,
            'Snap\api\exception\AccessTokenInvalid' => 10106,
            'Snap\api\exception\LoginMaximumRetries' => 10107,

            // Account Holder
            'Snap\api\exception\InitialInvestmentNotMade' => 20001,
            'Snap\api\exception\MyGtpAccountHolderNoPincode' => 20002,
            'Snap\api\exception\MyGtpAccountClosurePending' => 20003,
            'Snap\api\exception\MyGtpInvalidQuestionnaireAnswer' => 20004,
            'Snap\api\exception\MyGtpAccountHolderWrongPin' => 20005,
            'Snap\api\exception\MyGtpAccountHolderNoAccountNameOrNumber' => 20006,
            'Snap\api\exception\MyGtpAccountHolderNotEnoughBalance' => 20007,
            'Snap\api\exception\EkycAlreadyPassed' => 20008,
            'Snap\api\exception\EkycPendingResult' => 20009,
            'Snap\api\exception\EkycSubmissionInvalid' => 20010,
            'Snap\api\exception\MyGtpAccountHolderMyKadExists' => 20011,
            'Snap\api\exception\MyGtpStatementNotAvailable' => 20012,
            'Snap\api\exception\MyGtpPhoneNumberExists' => 20013,
            'Snap\api\exception\MyGtpPhoneNumberNotExist' => 20014,
            'Snap\api\exception\MyGtpAccountClosureNotAllowed' => 20015,
            'Snap\api\exception\MyGtpAccountHolderPincodeRequired' => 20016,
            'Snap\api\exception\MyGtpProfileUpdateNotAllowed' => 20017,
            'Snap\api\exception\MyGtpPartnerUsernameExists' => 20018,


            // Records
            'Snap\api\exception\MyOccupationCategoryNotFound' => 20101,
            'Snap\api\exception\MyBankNotFound' => 20102,
            'Snap\api\exception\MyCloseReasonInvalid' => 20103,
            'Snap\api\exception\MyPriceAlertNotFound' => 20104,
            'Snap\api\exception\DocumentNotFound' => 20105,
            'Snap\api\exception\DocumentContentEmpty' => 20106,
            'Snap\api\exception\EmailAddressTakenException' => 20107,
            'Snap\api\exception\EmailAddressNotFound' => 20108,
            'Snap\api\exception\ResetTokenInvalid' => 20109,
            'Snap\api\exception\MyGtpAccountHolderNotExist' => 20110,
            'Snap\api\exception\MyGtpVerificationCodeInvalid' => 20111,
            'Snap\api\exception\MyGtpAccountHolderNotActive' => 20112,
            // Partner
            'Snap\api\exception\MyGtpPartnerMismatch' => 30001,
            'Snap\api\exception\MyPartnerApiMissing' => 30002,
            'Snap\api\exception\PartnerBranchCodeInvalid' => 30003,
            'Snap\api\exception\MyGtpPartnerApiInvalid' => 30004,
            // Transaction
            'Snap\api\exception\OrderInvalidAction' => 40001,
            'Snap\api\exception\MyGtpFpxCreateTxError' => 40002,
            'Snap\api\exception\MyGtpPriceValidationNotValid' => 40003,
            'Snap\api\exception\MyGtpProductInvalid' => 40004,
            'Snap\api\exception\MyGtpInvalidAmount' => 40005,
            'Snap\api\exception\OrderPriceDataExpired' => 40006,
            'Snap\api\exception\MyGtpPartnerInsufficientGoldbalance' => 40007,
            'Snap\api\exception\MyGtpTransactionNotExists' => 40008,
            'Snap\api\exception\TradingHourOutOfBounds' => 40009,
            'Snap\api\exception\MyGtpWalletException' => 40011,
            'Snap\api\exception\MyGtpWalletInsufficientBalance' => 40012,
            'Snap\api\exception\MyGtpWalletInvalidSession' => 40013,
            'Snap\api\exception\MyGtpLoanInsufficientBalance' => 40014,

            // Conversion
            'Snap\api\exception\RedemptionError' => 60001,


            'Snap\api\exception\GeneralException' => 50000,
            'Exception' => 50001,
            'Error' => 50002,
        ];

        $exClass = get_class($e);
        if(! $exClass) {
            $exClass = 'Snap\api\exception\GeneralException';
        }

        $errMsg = empty($customizeMsg) ? $e->getMessage() : $customizeMsg;
        $code = $codeMap[$exClass];
        if (! $code) {
            $code = $codeMap["Exception"];
        }

        return [
             'success'       => false,
             'error'         => $code,
             'error_message' => gettext("Error ($code): ") . $errMsg
        ];

    }

    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     *
     * @param  String $version Version number that we would like the params to get
     * @return MyGtpApiProcessor derived class
     */
    static function getInstance($version)
    {
        $className = __CLASS__ . preg_replace('/\./', '_', strtolower($version));
        \Snap\App::getInstance()->log("Instantiating MyGTP processor $className...", SNAP_LOG_ERROR);
        if(class_exists($className)) {
            return new $className;
        }
        return new self;
    }

    /**
     * Main method to process the incoming request.  Implemented class can get relevant
     * information about the action to be taken etc from the apiParam and then call the
     * appropriate manager to execute the main business logics.
     *
     * @param  App                      $app           App Class
     * @param  \Snap\api\param\ApiParam $apiParam      Api parameter object containing the decoded data
     * @param  array                    $decodedData   Decoded and converted data
     * @param  array                    $requestParams Original raw data from the API request
     * @return \Snap\api\param\ApiParam     The response type represented as apiParams.
     */
    public function process($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams)
    {
        throw \Snap\api\exception\ApiProcessorNoActionFound::fromTransaction($this, [
                    'action_type' => $apiParam->getActionType(),
                    'processor_class' => __CLASS__]);
    }

    /**
     * Helper factory method to create and return api param object
     * @param  array  $requestParams        The input parameters to get version information
     * @param  string $responseActionType   Desired output response format
     * @param  string $responseParams       Data to be formatted
     * @return ApiParam                     Created object.
     */
    protected function createOutputApiParam($responseActionType, $requestParams, $responseParams)
    {
        $outputApiParam = \Snap\api\param\MyGtpApiParam::getInstance($requestParams['version']);
        $outputApiParam->setActionType($responseActionType);
        $formattedResponse = $outputApiParam->encode($this->app, $responseActionType, $responseParams, $requestParams);
        return $formattedResponse;
    }

    /**
     * Disallow instantiation of the class through new.
     */
    private function __construct()
    {
    }
}
?>