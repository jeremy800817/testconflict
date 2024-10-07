<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\param;

use Snap\App;

/**
 * This class implements GTP api protocol as specified for Maybank project (v1.0m)
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class MyGtpApiParam1_0my extends MyGtpApiParam
{
    /**
     * Definitions of all the actions that can be done by the API
     */
    protected function __construct()
    {
        parent::__construct();

        /*transferorderdb*/
        $this->registerParameter('transferbetweendb', 'version', 'required;string|max=5', '','');
        $this->registerParameter('transferbetweendb', 'action', 'required', '','');
        $this->registerParameter('transferbetweendb', 'partner', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('transferbetweendb', 'transactions', 'required', '','');

        // Verify account params should be built as HTTP query params and sent in email during registration
        $this->registerParameter('verify_account', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('verify_account', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('verify_account', 'email', 'required;email', '', '');
        $this->registerParameter('verify_account', 'code', 'required', '', '');

        // $this->registerParameter('verify_phone', 'version', 'required;string|max=5', '', '');
        // $this->registerParameter('verify_phone', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        // $this->registerParameter('verify_phone', 'phone_number', 'required;phone|mobile-my', '', '');
        // $this->registerParameter('verify_phone', 'code', 'required;numeric;string|min=6', '', '');

        $this->registerParameter('resend_verification_phone', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('resend_verification_phone', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('resend_verification_phone', 'phone_number', 'required;phone|mobile-my', '', '');

        $this->registerParameter('resend_verification_email', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('resend_verification_email', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('resend_verification_email', 'email', 'required;email', '', '');

        $this->registerParameter('announcement_list', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('announcement_list', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        $this->registerParameter('announcement', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('announcement', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('announcement', 'aid', 'required;numeric', '', '');
        $this->registerParameter('announcement', 'pid', 'required;numeric', '', '');

        $this->registerParameter('aqad', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('aqad', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('aqad', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('aqad', 'uuid', 'required;string|max=20', '', '');
        $this->registerParameter('aqad', 'weight', 'required;numeric|<=100000', '', '');
        $this->registerParameter('aqad', 'product', 'required;productCode', 'toProduct', '');
        $this->registerParameter('aqad', 'type', 'contains|spot_acesell|spot_acebuy', '', '');
        $this->registerParameter('aqad', 'settlement_method', 'optionalcontains|wallet|fpx|bank_account|loan', '', '');


        $this->registerParameter('legal_document', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('legal_document', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('legal_document', 'action', 'contains|legal_document', '', '');
        $this->registerParameter('legal_document', 'type', 'contains|pdpa|tnc|faq|disclaimer|pds', '', '');
        $this->registerParameter('legal_document', 'lang', 'contains|en|ms|zh', '', '');

        $this->registerParameter('app_config', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('app_config', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        $this->registerParameter('gold_prices', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('gold_prices', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('gold_prices', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('gold_prices', 'action', 'contains|gold_prices', '', '');
        $this->registerParameter('gold_prices', 'product', 'required;productCode', 'toProduct', '');
        $this->registerParameter('gold_prices', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('gold_prices', 'date_to', 'required;datetime', 'toDateTime', '');
        $this->registerParameter('gold_prices', 'page_number', 'required;numeric', '', '');
        $this->registerParameter('gold_prices', 'page_size', 'required;numeric', '', '');

        $this->registerParameter('statement', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('statement', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('statement', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('statement', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('statement', 'date_to', 'required;datetime', 'toDateTime', '');

        $this->registerParameter('login', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('login', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('login', 'grant_type', 'required', '', '');
        $this->registerParameter('login', 'email', 'required;email', '', '');
        $this->registerParameter('login', 'password', 'required;string|min=6', '', '');
        $this->registerParameter('login', 'push_token', '', '', '');

        $this->registerParameter('login_phone', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('login_phone', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('login_phone', 'grant_type', 'required', '', '');
        $this->registerParameter('login_phone', 'phone_number', 'required;phone|mobile-my', '', '');
        $this->registerParameter('login_phone', 'password', 'required;string|min=6', '', '');
        $this->registerParameter('login_phone', 'push_token', '', '', '');

        $this->registerParameter('login_partner', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('login_partner', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('login_partner', 'grant_type', 'required', '', '');
        $this->registerParameter('login_partner', 'partner_customer_id', 'required', '', '');
        $this->registerParameter('login_partner', 'password', 'required;string|min=6', '', '');
        $this->registerParameter('login_partner', 'push_token', '', '', '');

        $this->registerParameter('login_accno', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('login_accno', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('login_accno', 'grant_type', 'required', '', '');
        $this->registerParameter('login_accno', 'account_no', 'required', '', '');
        $this->registerParameter('login_accno', 'password', 'required;string|min=6', '', '');
        $this->registerParameter('login_accno', 'push_token', '', '', '');

        $this->registerParameter('refresh_token', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('refresh_token', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('refresh_token', 'refresh_token', 'required', '', '');

        $this->registerParameter('register', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('register', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('register', 'email', 'required;email', '', '');
        $this->registerParameter('register', 'full_name', 'required;fullname', '', '');
        $this->registerParameter('register', 'mykad_number', 'required', '', '');
        $this->registerParameter('register', 'phone_number', 'required;phone|mobile-my', '', '');
        $this->registerParameter('register', 'phone_verification_code', 'required;numeric;string|min=6', '', '');
        $this->registerParameter('register', 'occupation_category_id', 'required;numeric', 'toOccupationCategory', '');
        $this->registerParameter('register', 'occupation_subcategory_id', '', 'toOccupationSubCategory', '');
        $this->registerParameter('register', 'preferred_lang', '', '', '');
        $this->registerParameter('register', 'referral_branch_code', '', '', '');
        $this->registerParameter('register', 'referral_salesperson_code', '', '', '');
        $this->registerParameter('register', 'password', 'required;confirm;string|min=6;', '', '');
        $this->registerParameter('register', 'confirm_password', 'required;', '', '');
        $this->registerParameter('register', 'nok_full_name', '', '', '');
        // $this->registerParameter('register', 'nok_mykad_number', 'required;string|min=12|max=12;numeric', '', '');
        $this->registerParameter('register', 'nok_phone', 'phone|mobile-my', '', '');
        $this->registerParameter('register', 'nok_email', 'email', '', '');
        $this->registerParameter('register', 'nok_address', 'string', '', '');
        $this->registerParameter('register', 'nok_relationship', 'string', '', '');
        $this->registerParameter('register', 'partner_customer_id', '', '', '');
        $this->registerParameter('register', 'partner_data', '', '', '');

        $this->registerParameter('session_summary', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('session_summary', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('session_summary', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('session_summary', 'type', '', '', '');

        $this->registerParameter('forgot_password_phone', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('forgot_password_phone', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('forgot_password_phone', 'phone_number', 'required;phone|mobile-my', '', '');

        $this->registerParameter('reset_password_phone', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('reset_password_phone', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('reset_password_phone', 'phone_number', 'required;phone|mobile-my', '', '');
        $this->registerParameter('reset_password_phone', 'password', 'required;confirm;string|min=6;', '', '');
        $this->registerParameter('reset_password_phone', 'confirm_password', 'required', '', '');
        $this->registerParameter('reset_password_phone', 'code', 'required;numeric;string|min=6', '', '');

        $this->registerParameter('forgot_password', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('forgot_password', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('forgot_password', 'email', 'required;email', '', '');

        $this->registerParameter('reset_password', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('reset_password', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('reset_password', 'email', 'required;email', '', '');
        $this->registerParameter('reset_password', 'password', 'required;confirm;string|min=6;', '', '');
        $this->registerParameter('reset_password', 'confirm_password', 'required', '', '');
        $this->registerParameter('reset_password', 'code', 'required;numeric;string|min=6', '', '');

        $this->registerParameter('forgot_pin', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('forgot_pin', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('forgot_pin', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        $this->registerParameter('reset_pin', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('reset_pin', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('reset_pin', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('reset_pin', 'new_pin', 'required;numeric;string|min=6;numeric;confirm', '', '');
        $this->registerParameter('reset_pin', 'confirm_new_pin', 'required;numeric;string|min=6;numeric', '', '');
        $this->registerParameter('reset_pin', 'code', 'required;numeric;string|min=6', '', '');

        $this->registerParameter('close_account', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('close_account', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('close_account', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('close_account', 'close_reason_id', 'required;numeric', 'toCloseReason', '');
        $this->registerParameter('close_account', 'pin', 'required;string|min=6;numeric', '', '');

        $this->registerParameter('close_account_status', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('close_account_status', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('close_account_status', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        $this->registerParameter('pin_update', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('pin_update', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('pin_update', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('pin_update', 'current_pin', '', '', '');
        $this->registerParameter('pin_update', 'new_pin', 'required;numeric;string|min=6;numeric;confirm', '', '');
        $this->registerParameter('pin_update', 'confirm_new_pin', 'required;numeric;string|min=6;numeric', '', '');

        $this->registerParameter('password_update', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('password_update', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('password_update', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('password_update', 'current_password', 'required;string|min=6', '', '');
        $this->registerParameter('password_update', 'new_password', 'required;string|min=6;confirm', '', '');
        $this->registerParameter('password_update', 'confirm_new_password', 'required;', '', '');

        $this->registerParameter('profile_update', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('profile_update', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('profile_update', 'nok_full_name', '', '', '');
        // $this->registerParameter('profile_update', 'nok_mykad_number', 'required;string|min=12|max=12;numeric', '', '');
        $this->registerParameter('profile_update', 'nok_phone', 'phone|mobile-my', '', '');
        $this->registerParameter('profile_update', 'nok_email', 'email', '', '');
        $this->registerParameter('profile_update', 'nok_address', 'string', '', '');
        $this->registerParameter('profile_update', 'nok_relationship', 'string', '', '');
        $this->registerParameter('profile_update', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('profile_update', 'address_line_1', 'required', '', '');
        $this->registerParameter('profile_update', 'address_line_2', '', '', '');
        $this->registerParameter('profile_update', 'postcode', 'required;string|min=5;numeric', '', '');
        $this->registerParameter('profile_update', 'city', 'required', '', '');
        $this->registerParameter('profile_update', 'state', 'required', '', '');
        $this->registerParameter('profile_update', 'occupation_category_id', 'required;numeric', 'toOccupationCategory', '');
        $this->registerParameter('profile_update', 'occupation_subcategory_id', '', 'toOccupationSubCategory', '');
        $this->registerParameter('profile_update', 'referral_salesperson_code', '', '', '');
        $this->registerParameter('profile_update', 'referral_branch_code', '', '', '');
        $this->registerParameter('profile_update', 'pin', 'required;string|min=6;numeric', '', '');

        $this->registerParameter('bank_account_update', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('bank_account_update', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('bank_account_update', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('bank_account_update', 'bank_id', 'required;numeric', 'toBank', '');
        $this->registerParameter('bank_account_update', 'bank_acc_name', 'required;fullname', '', '');
        $this->registerParameter('bank_account_update', 'bank_acc_number', 'required;string|min=5;numeric', '', '');
        $this->registerParameter('bank_account_update', 'pin', 'required;string|min=6;numeric', '', '');

        $this->registerParameter('language_update', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('language_update', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('language_update', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('language_update', 'preferred_lang', 'required', '', '');

        // TODO
        // Image validator for uploaded file
        $this->registerParameter('ekyc_onboarding', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('ekyc_onboarding', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('ekyc_onboarding', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('ekyc_onboarding', 'mykad_front_b64', '', '', '');
        $this->registerParameter('ekyc_onboarding', 'mykad_back_b64', '', '', '');
        $this->registerParameter('ekyc_onboarding', 'face_image_b64', '', '', '');
        $this->registerParameter('ekyc_onboarding', 'is_pep', 'required', 'toBoolean', '');
        $this->registerParameter('ekyc_onboarding', 'questionnaire', 'requiredWhen|is_pep|true', '', '');

        $this->registerParameter('logout', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('logout', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('logout', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        // $this->registerParameter('logout', 'action', 'contains|logout', '', '');

        foreach (['spot_acebuy', 'spot_acesell'] as $apiAction) {
            $this->registerParameter($apiAction, 'access_token', 'bearer', 'tokenToAccountHolder', '');
            $this->registerParameter($apiAction, 'version', 'required;string|max=5', '', '');
            $this->registerParameter($apiAction, 'merchant_id', 'required;partnerCode', 'toPartner', '');
            $this->registerParameter($apiAction, 'product', 'required;productCode', 'toProduct', '');
            $settlementMethod = ('spot_acebuy' === $apiAction) ? 'bank_account' : 'fpx';
            $this->registerParameter($apiAction, 'settlement_method', 'contains|wallet|loan|' . $settlementMethod, '', '');
            // $this->registerParameter($apiAction, 'order_type', 'contains|weight|amount', '', '');
            // $this->registerParameter($apiAction, 'amount', 'required;numeric|<=10000000000', '', '');
            $this->registerParameter($apiAction, 'weight', 'required;numeric|<=100000', '', '');
            $this->registerParameter($apiAction, 'uuid', 'string|max=20', '', '');
            $this->registerParameter($apiAction, 'from_alert', '', 'toBoolean', '');
            $this->registerParameter($apiAction, 'pin', 'required;string|min=4;numeric', '', '');
            $this->registerParameter($apiAction, 'campaign_code', '', '', '');
            $this->registerParameter($apiAction, 'partner_data', '', '', '');
            $this->registerParameter($apiAction, 'note', '', '', '');
        }

        $this->registerParameter('spot_status', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('spot_status', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('spot_status', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('spot_status', 'action', 'contains|spot_status', '', '');
        $this->registerParameter('spot_status', 'uuid', 'requiredWhen|transactionid|null', '', '');
        $this->registerParameter('spot_status', 'transactionid', 'requiredWhen|uuid|null', '', '');

        $this->registerParameter('spot_transaction_history', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('spot_transaction_history', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('spot_transaction_history', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('spot_transaction_history', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('spot_transaction_history', 'date_to', 'required;datetime', 'toDateTime', '');
        $this->registerParameter('spot_transaction_history', 'page_number', 'required;numeric', '', '');
        $this->registerParameter('spot_transaction_history', 'page_size', 'required;numeric', '', '');
        $this->registerParameter('spot_transaction_history', 'type', 'optionalcontains|buy|sell|adminstoragefee|promo|buysellpromo', '', '');

        $this->registerParameter('conversion_fee', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('conversion_fee', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('conversion_fee', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('conversion_fee', 'product', 'required;productCode', 'toProduct', '');
        $this->registerParameter('conversion_fee', 'quantity', 'required;numeric|>=0;numeric|<10000', '', '');
        $this->registerParameter('conversion_fee', 'payment_mode', 'optionalcontains|wallet|fpx', '', '');
        $this->registerParameter('conversion_fee', 'methodofreceive', '', '', '');
        $this->registerParameter('conversion_fee', 'collectedbranchcode', '', '', '');

        $this->registerParameter('conversion', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('conversion', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('conversion', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('conversion', 'product', 'required;productCode', 'toProduct', '');
        $this->registerParameter('conversion', 'quantity', 'required;numeric|>=0;numeric|<10000', '', '');
        $this->registerParameter('conversion', 'payment_mode', 'optionalcontains|wallet|fpx', '', '');
        $this->registerParameter('conversion', 'campaign_code', '', '', '');
        $this->registerParameter('conversion', 'pin', 'required;string|min=4;numeric', '', '');
        $this->registerParameter('conversion', 'partner_data', '', '', '');
        $this->registerParameter('conversion', 'note', '', '', '');
        $this->registerParameter('conversion', 'methodofreceive', '', '', '');
        $this->registerParameter('conversion', 'collectedbranchcode', '', '', '');

        $this->registerParameter('conversion_history', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('conversion_history', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('conversion_history', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('conversion_history', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('conversion_history', 'date_to', 'required;datetime', 'toDateTime', '');
        $this->registerParameter('conversion_history', 'page_number', 'required;numeric', '', '');
        $this->registerParameter('conversion_history', 'page_size', 'required;numeric', '', '');

        $this->registerParameter('price_alerts', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('price_alerts', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('price_alerts', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('price_alerts', 'action', 'contains|price_alerts', '', '');
        $this->registerParameter('price_alerts', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('price_alerts', 'date_to', 'required;datetime', 'toDateTime', '');
        $this->registerParameter('price_alerts', 'page_number', 'required;numeric', '', '');
        $this->registerParameter('price_alerts', 'page_size', 'required;numeric', '', '');

        $this->registerParameter('new_price_alert', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('new_price_alert', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('new_price_alert', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('new_price_alert', 'price', 'required;numeric|>=0', '', '');
        $this->registerParameter('new_price_alert', 'type', 'contains|buy|sell', '', '');
        $this->registerParameter('new_price_alert', 'remark', '', '', '');

        $this->registerParameter('delete_price_alert', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('delete_price_alert', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('delete_price_alert', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('delete_price_alert', 'id', 'required;numeric', 'toPriceAlert', '');


        // TODO : Phase 2
        $this->registerParameter('disbursement', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('disbursement', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('disbursement', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('disbursement', 'action', 'contains|disbursement', '', '');
        $this->registerParameter('disbursement', 'amount', 'required;numeric', '', '');
        $this->registerParameter('disbursement', 'pin', 'required;string|min=4;numeric', '', '');

        // TODO : Phase 2
        $this->registerParameter('disbursement_history', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('disbursement_history', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('disbursement_history', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('disbursement_history', 'date_from', 'required;datetime;datetimeCompare|<|date_to', 'toDateTime', '');
        $this->registerParameter('disbursement_history', 'date_to', 'required;datetime', 'toDateTime', '');
        $this->registerParameter('disbursement_history', 'page_number', 'required;numeric', '', '');
        $this->registerParameter('disbursement_history', 'page_size', 'required;numeric', '', '');

        $this->registerParameter('pricestream', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('pricestream', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('pricestream', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        $this->registerParameter('gold_transfer_history', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('gold_transfer_history', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('gold_transfer_history', 'merchant_id', 'required;partnerCode', 'toPartner', '');

        // $this->registerParameter('goldbar_list', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        // $this->registerParameter('goldbar_list', 'version', 'required;string|max=5', '', '');
        // $this->registerParameter('goldbar_list', 'merchant_id', 'required;partnerCode', 'toPartner', 'constant|BMMB@UAT');

        $this->registerParameter('friendinvitation', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('friendinvitation', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('friendinvitation', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('friendinvitation', 'action', 'contains|friendinvitation', '', '');
        $this->registerParameter('friendinvitation', 'friendfullname', 'required;fullname', '', '');
        $this->registerParameter('friendinvitation', 'friendemail', 'required;email', '', '');
        $this->registerParameter('friendinvitation', 'friendcontactno', 'required;phone|mobile-my', '', '');
        $this->registerParameter('friendinvitation', 'referralcode', '', '', '');
        $this->registerParameter('friendinvitation', 'message', '', '', '');

        $this->registerParameter('goldtransfer', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('goldtransfer', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('goldtransfer', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('goldtransfer', 'action', 'contains|goldtransfer', '', '');
        $this->registerParameter('goldtransfer', 'receiver_phone_number', 'required;phone|mobile-my', '', '');
        $this->registerParameter('goldtransfer', 'receiver_email', 'required;email', '', '');
        $this->registerParameter('goldtransfer', 'weight', 'required;numeric|<=100000', '', '');
        $this->registerParameter('goldtransfer', 'price', 'required;numeric|>=0', '', '');
        $this->registerParameter('goldtransfer', 'amount', 'required;numeric|>=0', '', '');
        $this->registerParameter('goldtransfer', 'receiver_accountcode', 'required', '', '');
        $this->registerParameter('goldtransfer', 'pin', 'required;string|min=4;numeric', '', '');
        $this->registerParameter('goldtransfer', 'message', '', '', '');

        $this->registerParameter('checkcontact', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('checkcontact', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('checkcontact', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('checkcontact', 'action', 'contains|checkcontact', '', '');
        $this->registerParameter('checkcontact', 'phone_number', 'required;phone|mobile-my', '', '');

        // Verify pin is correct or not
        //$this->registerParameter('verify_pin', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('verify_pin', 'mykad_number', 'required', '', '');
        $this->registerParameter('verify_pin', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('verify_pin', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('verify_pin', 'action', 'contains|verify_pin', '', '');
        $this->registerParameter('verify_pin', 'pin', 'required;string|min=6;numeric', '', '');

        // Checking partner customer id
        $this->registerParameter('checking_partnercustid', 'partner_customerid', 'required', '', '');
        $this->registerParameter('checking_partnercustid', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('checking_partnercustid', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('checking_partnercustid', 'action', 'contains|checking_partnercustid', '', '');

        $this->registerParameter('uploadimg', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('uploadimg', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('uploadimg', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('uploadimg', 'action', 'contains|uploadimg', '', '');
        $this->registerParameter('uploadimg', 'image', 'required', '', '');
        $this->registerParameter('uploadimg', 'imagename', 'required', '', '');
        $this->registerParameter('uploadimg', 'imageback', 'required', '', '');
        $this->registerParameter('uploadimg', 'imagebackname', 'required', '', '');

        $this->registerParameter('get_nric_img', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('get_nric_img', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('get_nric_img', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('get_nric_img', 'action', 'contains|get_nric_img', '', '');

        $this->registerParameter('updatemykadnumber', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('updatemykadnumber', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('updatemykadnumber', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('updatemykadnumber', 'action', 'contains|updatemykadnumber', '', '');
        $this->registerParameter('updatemykadnumber', 'mykad_number', 'required', '', '');

        /*for wavpay wallet currently*/
        $this->registerParameter('updatepaymentgatewaywallet', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('updatepaymentgatewaywallet', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('updatepaymentgatewaywallet', 'action', 'contains|updatepaymentgatewaywallet', '', '');
        $this->registerParameter('updatepaymentgatewaywallet', 'paymentrefno', '', '', '');
        $this->registerParameter('updatepaymentgatewaywallet', 'details', '', '', '');


        $this->registerParameter('search_partner', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('search_partner', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('search_partner', 'action', 'contains|search_partner', '', '');
        $this->registerParameter('search_partner', 'email', 'required;email', '', '');

        $this->registerParameter('pendingrefundredonewallet', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('pendingrefundredonewallet', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('pendingrefundredonewallet', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('pendingrefundredonewallet', 'action', 'contains|pendingrefundredonewallet', '', '');
        $this->registerParameter('pendingrefundredonewallet', 'redonetoken', 'required', '', '');

        $this->registerParameter('failedredonewallet', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('failedredonewallet', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('failedredonewallet', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('failedredonewallet', 'action', 'contains|failedredonewallet', '', '');
        $this->registerParameter('failedredonewallet', 'redonetoken', 'required', '', '');

        $this->registerParameter('vault_store_inventory', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('vault_store_inventory', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('vault_store_inventory', 'action', 'contains|vault_store_inventory', '', '');

        $this->registerParameter('pricestream_notoken', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('pricestream_notoken', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('pricestream_notoken', 'action', 'contains|pricestream_notoken', '', '');

        /*transferordersvr*/
        $this->registerParameter('transferbetweensvr', 'version', 'required;string|max=5', '','');
        $this->registerParameter('transferbetweensvr', 'action', 'required', '','');
        $this->registerParameter('transferbetweensvr', 'partner', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('transferbetweensvr', 'transactions', 'required', '','');

        $this->registerParameter('getlogisticrecord', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('getlogisticrecord', 'action', 'contains|getlogisticrecord', '', '');
        
        $this->registerParameter('getredemptionitems', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('getredemptionitems', 'action', 'contains|getredemptionitems', '', '');
        $this->registerParameter('getredemptionitems', 'redemptionno', 'required', '', '');

        $this->registerParameter('get_account_no', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('get_account_no', 'mykadno', 'required', '', '');

        if (App::getInstance()->getConfig()->{'development'}) {
            $this->registerDevelopmentApis();
        }
    }

    protected function registerDevelopmentApis()
    {
        $this->registerParameter('trigger_push', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('trigger_push', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('trigger_push', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('trigger_push', 'type', 'required', '', '');
        $this->registerParameter('trigger_push', 'id', 'required;numeric', '', '');

        $this->registerParameter('trigger_email', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('trigger_email', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('trigger_email', 'merchant_id', 'required;partnerCode', 'toPartner', '');
        $this->registerParameter('trigger_email', 'type', 'required', '', '');
        $this->registerParameter('trigger_email', 'id', 'required;numeric', '', '');

        $this->registerParameter('delete_account', 'access_token', 'bearer', 'tokenToAccountHolder', '');
        $this->registerParameter('delete_account', 'version', 'required;string|max=5', '', '');
        $this->registerParameter('delete_account', 'merchant_id', 'required;partnerCode', 'toPartner', '');
    }
}
