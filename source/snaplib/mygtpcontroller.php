<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */

namespace Snap;


/**
 * Controller for mygtp
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  payment.base
 */
class mygtpcontroller extends gtpcontroller
{
        /**
         * This method will be called everytime the appplication framework starts up to allow proper initialisation of the
         * controller.  The initialisation can further take in the application context or mode as defined.
         *
         * @param  int $contextOrMode  The currently running mode of the application
         * @return void
         */
        public function initialiseController($app, $contextOrMode)
        {
                //prepare the parent's default datastore
                parent::initialiseController($app, $contextOrMode);

                $cacheId = $app->getConfig()->isKeyExists('cacheid') ? $app->getConfig()->cacheid : '0';
                $dbHandle = $app->getDbHandle();
                $cacheHandle = $app->getCacher();

                //Initialise all the required store objects
                $gtpStores = [
                        // 'tag' => [ 'class' => '\Snap\store\tagstore', 'publicAccessible' => true, 'parameters' => [
                        //         $cacheHandle, $dbHandle, 'mytag', null, 'tag', '\Snap\object\MyTag', array('vw_tag'), array(), true]],
                        'myaccountholder'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myaccountholder', null, 'ach', '\Snap\object\MyAccountHolder', array('vw_myaccountholder', 'vw_myaccountholdersignup'),  array('partner' => 'partnerLazyStore', 'mytoken' => 'mytokenLazyStore', 'myaddress' => 'myaddressLazyStore', 'ledger' => 'myledgerLazyStore', 'myoccupationcategory' => 'myoccupationcategoryLazyStore', 'myoccupationsubcategory' => 'myoccupationsubcategoryLazyStore', 'additionaldata' => 'achadditionaldataLazyStore'), true
                        ]],
                        'myaccountclosure'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myaccountclosure', null, 'acs', '\Snap\object\MyAccountClosure', array('vw_myaccountclosure'),  array(), true
                        ]],
                        'myaccountdetaillog'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myaccountdetaillog', null, 'adl', '\Snap\object\MyAccountDetailLog', array(),  array(), true
                        ]],
                        'myaddress'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myaddress', null, 'add', '\Snap\object\MyAddress', array(),  array(), true
                        ]],
                        'myamlascanlog'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myamlascanlog', null, 'asl', '\Snap\object\MyAmlaScanLog', array('vw_myamlascanlog'),  array(), true
                        ]],
                        'myannouncement'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myannouncement', null, 'ann', '\Snap\object\MyAnnouncement', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        'myannouncementtheme'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myannouncementtheme', null, 'ant', '\Snap\object\MyAnnouncementTheme', array(),  array(), true
                        ]],
                        'mybank'  => ['class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $cacheHandle, $dbHandle, 'mybank', null, 'bnk', '\Snap\object\MyBank', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        'myclosereason'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myclosereason', null, 'crn', '\Snap\object\MyCloseReason', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        'myconversion'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myconversion', null, 'cvn', '\Snap\object\MyConversion', array('vw_myconversion'),  array('redemption'=> 'redemptionLazyStore', 'mypaymentdetail' => 'mypaymentdetailLazyStore'), true
                        ]],
                        'mydailystoragefee'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mydailystoragefee', null, 'dsf', '\Snap\object\MyDailyStorageFee', array('vw_mydailystoragefee'),  array(), true
                        ]],
                        'mydisbursement'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mydisbursement', null, 'dbm', '\Snap\object\MyDisbursement', array('vw_mydisbursement'),  array(), true
                        ]],
                        'mydocumentation'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mydocumentation', null, 'doc', '\Snap\object\MyDocumentation', array('vw_mydocumentation', 'vw_mydocumentationcontent'),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        // 'myfee'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                        //         $dbHandle, 'myfee', null, 'fee', '\Snap\object\MyFee', array(),  array(), true
                        // ]],
                        'mygoldtransfer'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mygoldtransfer', null, 'gtf', '\Snap\object\MyGoldTransfer', array(),  array(), true
                        ]],
                        'mygoldtransferlog'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mygoldtransferlog', null, 'gtl', '\Snap\object\MyGoldTransferLog', array(),  array(), true
                        ]],
                        'mygoldtransaction'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mygoldtransaction', null, 'gtr', '\Snap\object\MyGoldTransaction', array('vw_mygoldtransaction', 'vw_mygoldtransactionhistory'),  array('mypaymentdetail'=> 'mypaymentdetailLazyStore', 'mydisbursement' => 'mydisbursementLazyStore', 'order' => 'orderLazyStore'), true
                        ]],
                        'myhistoricalprice'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myhistoricalprice', null, 'hpr', '\Snap\object\MyHistoricalPrice', array(),  array(), true
                        ]],
                        'myimage'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => false, 'parameters' => [
                                $dbHandle, 'myimage', null, 'img', '\Snap\object\MyImage', array(),  array(), true
                        ]],
                        'mykycoperatorlogs'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mykycoperatorlogs', null, 'kyl', '\Snap\object\MyKYCOperatorLogs', array(),  array(), true
                        ]],
                        'mykycresult'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mykycresult', null, 'kyr', '\Snap\object\MyKYCResult', array(),  array(), true
                        ]],
                        'mykycsubmission'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mykycsubmission', null, 'kys', '\Snap\object\MyKYCSubmission', array(),  array('result' => 'mykycresultLazyStore', 'image' => 'myimageLazyStore'), true
                        ]],
                        'mykycreminder'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mykycreminder', null, 'kcr', '\Snap\object\MyKYCReminder', array(),  array(), true
                        ]],
                        'myledger'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myledger', null, 'led', '\Snap\object\MyLedger', array('vw_myledger'),  array(), true
                        ]],
                        'myloantransaction'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myloantransaction', null, 'mlh', '\Snap\object\MyLoanTransaction', array(),  array(), true
                        ]],
                        'mylocalizedcontent'  => ['class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => false, 'parameters' => [
                                $cacheHandle, $dbHandle, 'mylocalizedcontent', null, 'loc', '\Snap\object\MyLocalizedContent', array(),  array(), true
                        ]],
                        // 'mylogisticfeemapping'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                        //         $dbHandle, 'mylogisticfeemapping', null, 'loc', '\Snap\object\MyLogisticFeeMapping', array(),  array(), true
                        // ]],
                        'mymemberupload'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mymemberupload', null, 'mem', '\Snap\object\MyMemberUpload', array(),  array(), true
                        ]],
                        'mymonthlystoragefee'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mymonthlystoragefee', null, 'msf', '\Snap\object\MyMonthlyStorageFee', array('vw_mymonthlystoragefee'),  array(), true
                        ]],
                        'myoccupationcategory'  => ['class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $cacheHandle, $dbHandle, 'myoccupationcategory', null, 'occ', '\Snap\object\MyOccupationCategory', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        'myoccupationsubcategory'  => ['class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $cacheHandle, $dbHandle, 'myoccupationsubcategory', null, 'osc', '\Snap\object\MyOccupationSubCategory', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        // 'mypartnerapi'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                        //         $dbHandle, 'mypartnerapi', null, 'pap', '\Snap\object\MyPartnerApi', array(),  array(), true
                        // ]],
                        'mypartnersetting'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypartnersetting', null, 'psg', '\Snap\object\MyPartnerSetting', array(),  array(), true
                        ]],
                        'mypartnersapsetting'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypartnersapsetting', null, 'pss', '\Snap\object\MyPartnerSapSetting', array(),  array(), true
                        ]],
                        'mypartnersapsettingcode'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypartnersapsettingcode', null, 'psc', '\Snap\object\MyPartnerSapSettingCode', array(),  array(), true
                        ]],
                        'mypaymentdetail'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypaymentdetail', null, 'pdt', '\Snap\object\MyPaymentDetail', array(),  array(), true
                        ]],
                        'mypepperson'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypepperson', null, 'pep', '\Snap\object\MyPepPerson', array(),  array(), true
                        ]],
                        'mypepsearchresult'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypepsearchresult', null, 'pes', '\Snap\object\MyPepSearchResult', array(),  array(), true
                        ]],
                        'mypricealert'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mypricealert', null, 'pal', '\Snap\object\MyPriceAlert', array('vw_mypricealert'),  array(), true
                        ]],
                        'mypushnotification'  => ['class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $cacheHandle, $dbHandle, 'mypushnotification', null, 'pnt', '\Snap\object\MyPushNotification', array(),  array('mylocalizedcontent' => 'mylocalizedcontentLazyStore'), true
                        ]],
                        'myscreeninglist'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myscreeninglist', null, 'scl', '\Snap\object\MyScreeningList', array(),  array(), true
                        ]],
                        'myscreeninglistimportlog' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                                $dbHandle, 'myscreeninglistimportlog', null, 'sci', '\Snap\object\MyScreeningListImportLog', array('vw_myscreeninglistimportlog'), array(), true
                        ]],
                        'myscreeningmatchlog'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'myscreeningmatchlog', null, 'scm', '\Snap\object\MyScreeningMatchLog', array('vw_myscreeningmatchlog'),  array(), true
                        ]],
                        'mytoken'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mytoken', null, 'tok', '\Snap\object\MyToken', array(),  array(), true
                        ]],
                        'mytransfergold'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mytransfergold', null, 'gtb', '\Snap\object\MyTransferGold', array('vw_mytransfergold'),  array(), true
                        ]],
                        'mykycimage'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                                $dbHandle, 'mykycimage', null, '', '\Snap\object\MyKYCImage', array(),  array(), true
                        ]],
                ];

                //We can now register the store that can be used directly with $app->xxxxStore() methods (*if* publicAccessible = true).
                foreach ($gtpStores as $storeKey => $storeProps) {
                        $this->registerStore($storeKey, $storeProps, is_array($storeProps) ? $storeProps['publicAccessible'] : true);
                }

                //Manager configurations -IMPORTANT:  Register all observable targets first, then only the observers
                $this->registerManager('mygtpauth', '\Snap\manager\MyGtpAuthManager', array());
                // $this->registerManager('mygtpfee', '\Snap\manager\MyGtpFeeManager', array());
                $this->registerManager('mygtptoken', '\Snap\manager\MyGtpTokenManager', array());
                $this->registerManager('mygtptransaction', '\Snap\manager\MyGtpTransactionManager', array());
                $this->registerManager('mygtpconversion', '\Snap\manager\MyGtpConversionManager', array('logistic'));
                $this->registerManager('mygtppartner', '\Snap\manager\MyGtpPartnerManager', array('bankvault'));
                $this->registerManager('mygtpscreening', '\Snap\manager\MyGtpScreeningManager', array());
                $this->registerManager('mygtpannouncement', '\Snap\manager\MyGtpAnnouncementManager', array());
                $this->registerManager('mygtphistoricalprice', '\Snap\manager\MyGtpHistoricalPriceManager', array());
                $this->registerManager('mygtpdisbursement', '\Snap\manager\MyGtpDisbursementManager', array());
                $this->registerManager('mygtpaccount', '\Snap\manager\MyGtpAccountManager', array('mygtpdisbursement'));
                $this->registerManager('mygtpdocumentation', '\Snap\manager\MyGtpDocumentationManager', array());
                $this->registerManager('mygtpstatement', '\Snap\manager\MyGtpStatementManager', array());
                $this->registerManager('mygtppricealert', '\Snap\manager\MyGtpPriceAlertManager', array('price'));
                $this->registerManager('mygtpstorage', '\Snap\manager\MyGtpStorageManager');
                $this->registerManager('mygtppushnotification', '\Snap\manager\MyGtpPushNotificationManager', array('mygtpaccount', 'mygtpscreening', 'mygtppricealert'));
                $this->registerManager('mygtptransfergold', '\Snap\manager\MyGtpTransferGoldManager');
                $this->registerManager('notification', '\Snap\manager\notificationManager', array('mygtpaccount', 'mygtppushnotification', 'mygtpscreening', 'mygtptransaction', 'mygtpconversion', 'mygtpdisbursement', 'mygtppricealert'));
                $this->registerManager('mygoldinvitetransfer', '\Snap\manager\MyGtpGoldInviteTransferManager');
                // $this->registerManager('spotorder', '\Snap\manager\SpotOrderManager', array());
                // $this->registerManager('futureorder', '\Snap\manager\FutureOrderManager', array('price', 'spotorder'));
                // $this->registerManager('price', '\Snap\manager\PriceManager', array('spotorder'));
                // $this->registerManager('api', '\Snap\manager\ApiManager', array('price'));
                // $this->registerManager('mbbanp', '\Snap\manager\MbbAnPManager', array('spotorder'));
                // $this->registerManager('logistic', '\Snap\manager\LogisticManager', array());
                // $this->registerManager('redemption', '\Snap\manager\RedemptionManager', array('logistic'));
                // $this->registerManager('replenishment', '\Snap\manager\ReplenishmentManager', array('logistic'));
                // $this->registerManager('buyback', '\Snap\manager\BuybackManager', array(''));
                // $this->registerManager('bankvault', '\Snap\manager\BankVaultManager', array());
                // $this->registerManager('partner', '\Snap\manager\partnermanager', array());
                // $this->registerManager('queue', '\Snap\manager\QueueManager', array());
                // $this->registerManager('ftpprocessor', '\Snap\manager\FtpProcessorManager', array());
                // $this->registerManager('eventtest', '\Snap\manager\EventTestManager', array());
                // $this->registerManager('sms', '\Snap\manager\SmsManager', array());
                // $this->registerManager('workflow', '\Snap\manager\workflowmanager', array('settlement', 'partner', 'account', 'member', 'fundin'));
                // $this->registerManager('notification', '\Snap\manager\notificationManager',
                //                         array('spotorder', 'futureorder', 'price', 'api', 'redemption', 'bankvault', 'partner', 'mbbanp', 'logistic', 'eventtest'));

                return true;  //continue with the application
        }

        /**
         * This method should be implemented and return an array with the key representing the permission
         * key (can be multiple level by specifying a path E.g.  /root/system/user/add, /root/system/user/edit).
         * The value of the array shall be a description of the permission.
         *
         * This method can be used to initialise the rbac tables with the initial permissions.  It will also
         * be used by the user-role handler to allow for configuration of the application permission.
         */
        public function getAvailableApplicationPermission()
        {
                return array_merge(parent::getAvailableApplicationPermission(), [

                        "/root/bmmb" => gettext("BMMB Modules"),
                        "/root/go" => gettext("GO Modules"),
                        "/root/one" => gettext("ONECENT Modules"),
                        "/root/onecall" => gettext("ONECALL Modules"),
                        "/root/air" => gettext("AIRGOLD Modules"),
                        "/root/mcash" => gettext("MCASH Modules"),
                        "/root/ktp" => gettext("KTP Modules"),

                        "/root/system/pushnotification" => gettext("Push notification management"),
                        "/root/system/pushnotification/list" => gettext("View push notification management"),
                        "/root/system/pushnotification/add" => gettext("Add push notification management"),
                        "/root/system/pushnotification/edit" => gettext("Edit push notification management"),
                        //"/root/bmmb/pushnotification/cancel" => gettext("Cancel push notification"),
                        //"/root/bmmb/pushnotification/confirm" => gettext("Confirm push notification"),
                        "/root/system/pushnotification/delete" => gettext("Delete push notification"),

                        "/root/bmmb/redemption" => gettext("Conversion management"),
                        "/root/bmmb/redemption/list" => gettext("View conversion"),
                        "/root/bmmb/redemption/add" => gettext("Add conversion"),
                        "/root/bmmb/redemption/edit" => gettext("Edit conversion"),
                        "/root/bmmb/redemption/export" => gettext("Export conversion"),

                        "/root/bmmb/logistic" => gettext("Logistics management for BMMB"),
                        "/root/bmmb/logistic/list" => gettext("View logistics information for BMMB"),
                        "/root/bmmb/logistic/add" => gettext("Add logistics for BMMB"),
                        "/root/bmmb/logistic/edit" => gettext("Edit logistics for BMMB"),
                        "/root/bmmb/logistic/delete" => gettext("Delete logistics for BMMB"),
                        "/root/bmmb/logistic/complete" => gettext("Complete logistics for BMMB"),

                        "/root/bmmb/disbursement" => gettext("Disbursement management"),
                        "/root/bmmb/disbursement/list" => gettext("View disbursement"),
                        "/root/bmmb/disbursement/add" => gettext("Add disbursement"),
                        "/root/bmmb/disbursement/edit" => gettext("Edit disbursement"),

                        "/root/system/documentation" => gettext("Documentation management"),
                        "/root/system/documentation/list" => gettext("View documentation"),
                        "/root/system/documentation/add" => gettext("Add documentation"),
                        "/root/system/documentation/edit" => gettext("Edit documentation"),

                        "/root/bmmb/profile" => gettext("Account holder profile management"),
                        "/root/bmmb/profile/list" => gettext("View user profile"),
                        "/root/bmmb/profile/add" => gettext("Add user profile"),
                        "/root/bmmb/profile/edit" => gettext("Edit user profile"),
                        "/root/bmmb/profile/suspend" => gettext("Suspend account holder"),
                        "/root/bmmb/profile/unsuspend" => gettext("Unsuspend account holder"),

                        "/root/bmmb/approval" => gettext("PEP approval management"),
                        "/root/bmmb/approval/list" => gettext("View PEP"),
                        "/root/bmmb/approval/approve" => gettext("Approve or reject PEP"),

                        "/root/bmmb/fpx" => gettext("FPX management"),
                        "/root/bmmb/fpx/list" => gettext("View FPX"),

                        "/root/bmmb/ekyc" => gettext("eKYC management"),
                        "/root/bmmb/ekyc/list" => gettext("View eKYC"),

                        "/root/system/amla" => gettext("AMLA management"),
                        "/root/system/amla/list" => gettext("View amla"),
                        "/root/system/amla/import" => gettext("Permission to import amla"),

                        "/root/bmmb/goldtransaction" => gettext("Gold transaction management"),
                        "/root/bmmb/goldtransaction/list" => gettext("View gold transaction"),
                        "/root/bmmb/goldtransaction/export" => gettext("Export gold transaction statement"),

                        "/root/bmmb/storagefee" => gettext("Storage Fee management"),
                        "/root/bmmb/storagefee/list" => gettext("View storage fee"),
                        "/root/bmmb/fee" => gettext("Fee management"),
                        "/root/bmmb/fee/list" => gettext("View fee management"),
                        "/root/bmmb/fee/add" => gettext("Add fee"),
                        "/root/bmmb/fee/edit" => gettext("Edit fee"),


                        "/root/common/vault" => gettext("Inventory management"),
                        "/root/common/vault/list" => gettext("View inventory"),
                        "/root/common/vault/add" => gettext("Add item to inventory"),
                        "/root/common/vault/edit" => gettext("Edit item in inventory"),
                        "/root/common/vault/return" => gettext("Return item"),
                        "/root/common/vault/transfer" => gettext("Transfer item"),
                        "/root/common/vault/approve" => gettext("Approve item"),
                        "/root/common/vault/download" => gettext("Download item"),
                        "/root/common/vault/print" => gettext("Print item"),

                        "/root/bmmb/vault" => gettext("Inventory management"),
                        "/root/bmmb/vault/list" => gettext("View inventory"),
                        "/root/bmmb/vault/add" => gettext("Add item to inventory"),
                        "/root/bmmb/vault/edit" => gettext("Edit item in inventory"),
                        "/root/bmmb/vault/return" => gettext("Return item"),
                        "/root/bmmb/vault/transfer" => gettext("Transfer item"),
                        "/root/bmmb/vault/approve" => gettext("Approve item"),

                        "/root/bmmb/vault/request" => gettext("Request item"), // maker
                        "/root/bmmb/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
                        "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
                        "/root/bmmb/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

                        
                        "/root/bmmb/report" => gettext("Reporting management"),
                        "/root/bmmb/report/commission" => gettext("Commission Reporting"),
                        "/root/bmmb/report/commission/list" => gettext("View Commission Reporting"),
                        "/root/bmmb/report/storagefee" => gettext("Storage Fee Reporting"),
                        "/root/bmmb/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                        "/root/bmmb/report/adminfee" => gettext("Admin Fee Reporting"),
                        "/root/bmmb/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                        "/root/bmmb/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                        "/root/bmmb/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),

                        "/root/bmmb/accountclosure" => gettext("Account closure management"),
                        "/root/bmmb/accountclosure/list" => gettext("View account closure"),
                        "/root/bmmb/accountclosure/close" => gettext("Close an account"),

                        "/root/bmmb/pricealert" => gettext("Price alert management"),
                        "/root/bmmb/pricealert/list" => gettext("View price alert"),

                        "/root/bmmb/mintedbar" => gettext("Minted warehouse management for BMMB"),
                        "/root/bmmb/mintedbar/list" => gettext("View minted warehouse"),

                        // GO permissions
                        "/root/go/redemption" => gettext("Conversion management"),
                        "/root/go/redemption/list" => gettext("View conversion"),
                        "/root/go/redemption/add" => gettext("Add conversion"),
                        "/root/go/redemption/edit" => gettext("Edit conversion"),
                        "/root/go/redemption/export" => gettext("Export conversion"),

                        "/root/go/logistic" => gettext("Logistics management for GO"),
                        "/root/go/logistic/list" => gettext("View logistics information for GO"),
                        "/root/go/logistic/add" => gettext("Add logistics for GO"),
                        "/root/go/logistic/edit" => gettext("Edit logistics for GO"),
                        "/root/go/logistic/delete" => gettext("Delete logistics for GO"),
                        "/root/go/logistic/complete" => gettext("Complete logistics for GO"),

                        "/root/go/disbursement" => gettext("Disbursement management"),
                        "/root/go/disbursement/list" => gettext("View disbursement"),
                        "/root/go/disbursement/add" => gettext("Add disbursement"),
                        "/root/go/disbursement/edit" => gettext("Edit disbursement"),

                        "/root/go/profile" => gettext("Account holder profile management"),
                        "/root/go/profile/list" => gettext("View user profile"),
                        "/root/go/profile/add" => gettext("Add user profile"),
                        "/root/go/profile/edit" => gettext("Edit user profile"),
                        "/root/go/profile/suspend" => gettext("Suspend account holder"),
                        "/root/go/profile/unsuspend" => gettext("Unsuspend account holder"),

                        "/root/go/approval" => gettext("PEP approval management"),
                        "/root/go/approval/list" => gettext("View PEP"),
                        "/root/go/approval/approve" => gettext("Approve or reject PEP"),

                        "/root/go/fpx" => gettext("FPX management"),
                        "/root/go/fpx/list" => gettext("View FPX"),

                        "/root/go/ekyc" => gettext("eKYC management"),
                        "/root/go/ekyc/list" => gettext("View eKYC"),

                        "/root/go/goldtransaction" => gettext("Gold transaction management"),
                        "/root/go/goldtransaction/list" => gettext("View gold transaction"),
                        "/root/go/goldtransaction/export" => gettext("Export gold transaction statement"),

                        "/root/go/storagefee" => gettext("Storage Fee management"),
                        "/root/go/storagefee/list" => gettext("View storage fee"),
                        "/root/go/fee" => gettext("Fee management"),
                        "/root/go/fee/list" => gettext("View fee management"),
                        "/root/go/fee/add" => gettext("Add fee"),
                        "/root/go/fee/edit" => gettext("Edit fee"),


                        "/root/go/vault" => gettext("Inventory management"),
                        "/root/go/vault/list" => gettext("View inventory"),
                        "/root/go/vault/add" => gettext("Add item to inventory"),
                        "/root/go/vault/edit" => gettext("Edit item in inventory"),
                        "/root/go/vault/return" => gettext("Return item"),
                        "/root/go/vault/transfer" => gettext("Transfer item"),
                        "/root/go/vault/approve" => gettext("Approve item"),

                        
                        "/root/go/report" => gettext("Reporting management"),
                        "/root/go/report/commission" => gettext("Commission Reporting"),
                        "/root/go/report/commission/list" => gettext("View Commission Reporting"),
                        "/root/go/report/storagefee" => gettext("Storage Fee Reporting"),
                        "/root/go/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                        "/root/go/report/adminfee" => gettext("Admin Fee Reporting"),
                        "/root/go/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                        "/root/go/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                        "/root/go/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),

                        "/root/go/accountclosure" => gettext("Account closure management"),
                        "/root/go/accountclosure/list" => gettext("View account closure"),
                        "/root/go/accountclosure/close" => gettext("Close an account"),

                        "/root/go/pricealert" => gettext("Price alert management"),
                        "/root/go/pricealert/list" => gettext("View price alert"),

                        "/root/go/sale" => gettext("Spot Order Special for GOPAYZ"),
                        // End GO permissions

                        // One Permissions
                        "/root/one/redemption" => gettext("Conversion management for ONECENT"),
                        "/root/one/redemption/list" => gettext("View conversion"),
                        "/root/one/redemption/add" => gettext("Add conversion"),
                        "/root/one/redemption/edit" => gettext("Edit conversion"),
                        "/root/one/redemption/export" => gettext("Export conversion"),

                        "/root/one/logistic" => gettext("Logistics management for ONECENT"),
                        "/root/one/logistic/list" => gettext("View logistics information"),
                        "/root/one/logistic/add" => gettext("Add logistics"),
                        "/root/one/logistic/edit" => gettext("Edit logistics"),
                        "/root/one/logistic/delete" => gettext("Delete logistics"),
                        "/root/one/logistic/complete" => gettext("Complete logistics"),

                        "/root/one/disbursement" => gettext("Disbursement management for ONECENT"),
                        "/root/one/disbursement/list" => gettext("View disbursement"),
                        "/root/one/disbursement/add" => gettext("Add disbursement"),
                        "/root/one/disbursement/edit" => gettext("Edit disbursement"),

                        "/root/one/profile" => gettext("Account holder profile management for ONECENT"),
                        "/root/one/profile/list" => gettext("View user profile"),
                        "/root/one/profile/add" => gettext("Add user profile"),
                        "/root/one/profile/edit" => gettext("Edit user profile"),
                        "/root/one/profile/suspend" => gettext("Suspend account holder"),
                        "/root/one/profile/unsuspend" => gettext("Unsuspend account holder"),

                        "/root/one/approval" => gettext("PEP approval management for ONECENT"),
                        "/root/one/approval/list" => gettext("View PEP"),
                        "/root/one/approval/approve" => gettext("Approve or reject PEP"),

                        "/root/one/fpx" => gettext("FPX management for ONECENT"),
                        "/root/one/fpx/list" => gettext("View FPX"),

                        "/root/one/ekyc" => gettext("eKYC management for ONECENT"),
                        "/root/one/ekyc/list" => gettext("View eKYC"),

                        "/root/one/goldtransaction" => gettext("Gold transaction management for ONECENT"),
                        "/root/one/goldtransaction/list" => gettext("View gold transaction"),
                        "/root/one/goldtransaction/export" => gettext("Export gold transaction statement"),

                        "/root/one/storagefee" => gettext("Storage Fee management for ONECENT"),
                        "/root/one/storagefee/list" => gettext("View storage fee"),
                        "/root/one/fee" => gettext("Fee management"),
                        "/root/one/fee/list" => gettext("View fee management"),
                        "/root/one/fee/add" => gettext("Add fee"),
                        "/root/one/fee/edit" => gettext("Edit fee"),


                        "/root/one/vault" => gettext("Inventory management for ONECENT"),
                        "/root/one/vault/list" => gettext("View inventory"),
                        "/root/one/vault/add" => gettext("Add item to inventory"),
                        "/root/one/vault/edit" => gettext("Edit item in inventory"),
                        "/root/one/vault/return" => gettext("Return item"),
                        "/root/one/vault/transfer" => gettext("Transfer item"),
                        "/root/one/vault/approve" => gettext("Approve item"),

                        "/root/one/report" => gettext("Reporting management for ONECENT"),
                        "/root/one/report/commission" => gettext("Commission Reporting"),
                        "/root/one/report/commission/list" => gettext("View Commission Reporting"),
                        "/root/one/report/storagefee" => gettext("Storage Fee Reporting"),
                        "/root/one/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                        "/root/one/report/adminfee" => gettext("Admin Fee Reporting"),
                        "/root/one/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                        "/root/one/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                        "/root/one/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),

                        "/root/one/accountclosure" => gettext("Account closure management for ONECENT"),
                        "/root/one/accountclosure/list" => gettext("View account closure"),
                        "/root/one/accountclosure/close" => gettext("Close an account"),

                        "/root/one/pricealert" => gettext("Price alert management for ONECENT"),
                        "/root/one/pricealert/list" => gettext("View price alert"),

                        "/root/one/sale" => gettext("Spot Order Special for ONECENT"),
                        // End One Permissions

                         // OneCall Permissions
                         "/root/onecall/redemption" => gettext("Conversion management for ONECALL"),
                         "/root/onecall/redemption/list" => gettext("View conversion"),
                         "/root/onecall/redemption/add" => gettext("Add conversion"),
                         "/root/onecall/redemption/edit" => gettext("Edit conversion"),
                         "/root/onecall/redemption/export" => gettext("Export conversion"),
 
                         "/root/onecall/logistic" => gettext("Logistics management for ONECALL"),
                         "/root/onecall/logistic/list" => gettext("View logistics information"),
                         "/root/onecall/logistic/add" => gettext("Add logistics"),
                         "/root/onecall/logistic/edit" => gettext("Edit logistics"),
                         "/root/onecall/logistic/delete" => gettext("Delete logistics"),
                         "/root/onecall/logistic/complete" => gettext("Complete logistics"),
 
                         "/root/onecall/disbursement" => gettext("Disbursement management for ONECALL"),
                         "/root/onecall/disbursement/list" => gettext("View disbursement"),
                         "/root/onecall/disbursement/add" => gettext("Add disbursement"),
                         "/root/onecall/disbursement/edit" => gettext("Edit disbursement"),
 
                         "/root/onecall/profile" => gettext("Account holder profile management for ONECALL"),
                         "/root/onecall/profile/list" => gettext("View user profile"),
                         "/root/onecall/profile/add" => gettext("Add user profile"),
                         "/root/onecall/profile/edit" => gettext("Edit user profile"),
                         "/root/onecall/profile/suspend" => gettext("Suspend account holder"),
                         "/root/onecall/profile/unsuspend" => gettext("Unsuspend account holder"),
 
                         "/root/onecall/approval" => gettext("PEP approval management for ONECALL"),
                         "/root/onecall/approval/list" => gettext("View PEP"),
                         "/root/onecall/approval/approve" => gettext("Approve or reject PEP"),
 
                         "/root/onecall/fpx" => gettext("FPX management for ONECALL"),
                         "/root/onecall/fpx/list" => gettext("View FPX"),
 
                         "/root/onecall/ekyc" => gettext("eKYC management for ONECALL"),
                         "/root/onecall/ekyc/list" => gettext("View eKYC"),
 
                         "/root/onecall/goldtransaction" => gettext("Gold transaction management for ONECALL"),
                         "/root/onecall/goldtransaction/list" => gettext("View gold transaction"),
                         "/root/onecall/goldtransaction/export" => gettext("Export gold transaction statement"),
 
                         "/root/onecall/storagefee" => gettext("Storage Fee management for ONECALL"),
                         "/root/onecall/storagefee/list" => gettext("View storage fee"),
                         "/root/onecall/fee" => gettext("Fee management"),
                         "/root/onecall/fee/list" => gettext("View fee management"),
                         "/root/onecall/fee/add" => gettext("Add fee"),
                         "/root/onecall/fee/edit" => gettext("Edit fee"),
 
 
                         "/root/onecall/vault" => gettext("Inventory management for ONECALL"),
                         "/root/onecall/vault/list" => gettext("View inventory"),
                         "/root/onecall/vault/add" => gettext("Add item to inventory"),
                         "/root/onecall/vault/edit" => gettext("Edit item in inventory"),
                         "/root/onecall/vault/return" => gettext("Return item"),
                         "/root/onecall/vault/transfer" => gettext("Transfer item"),
                         "/root/onecall/vault/approve" => gettext("Approve item"),
 
                         
                         "/root/onecall/report" => gettext("Reporting management for ONECALL"),
                         "/root/onecall/report/commission" => gettext("Commission Reporting"),
                         "/root/onecall/report/commission/list" => gettext("View Commission Reporting"),
                         "/root/onecall/report/storagefee" => gettext("Storage Fee Reporting"),
                         "/root/onecall/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                         "/root/onecall/report/adminfee" => gettext("Admin Fee Reporting"),
                         "/root/onecall/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                         "/root/onecall/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                         "/root/onecall/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),

 
                         "/root/onecall/accountclosure" => gettext("Account closure management for ONECALL"),
                         "/root/onecall/accountclosure/list" => gettext("View account closure"),
 
                         "/root/onecall/pricealert" => gettext("Price alert management for ONECALL"),
                         "/root/onecall/pricealert/list" => gettext("View price alert"),

                         "/root/onecall/sale" => gettext("Spot Order Special for ONECALL"),
 
                         // End OneCall Permissions

                          // AirGold Permissions
                          "/root/air/redemption" => gettext("Conversion management for AIRGOLD"),
                          "/root/air/redemption/list" => gettext("View conversion"),
                          "/root/air/redemption/add" => gettext("Add conversion"),
                          "/root/air/redemption/edit" => gettext("Edit conversion"),
                          "/root/air/redemption/export" => gettext("Export conversion"),
  
                          "/root/air/logistic" => gettext("Logistics management for AIRGOLD"),
                          "/root/air/logistic/list" => gettext("View logistics information"),
                          "/root/air/logistic/add" => gettext("Add logistics"),
                          "/root/air/logistic/edit" => gettext("Edit logistics"),
                          "/root/air/logistic/delete" => gettext("Delete logistics"),
                          "/root/air/logistic/complete" => gettext("Complete logistics"),
  
                          "/root/air/disbursement" => gettext("Disbursement management for AIRGOLD"),
                          "/root/air/disbursement/list" => gettext("View disbursement"),
                          "/root/air/disbursement/add" => gettext("Add disbursement"),
                          "/root/air/disbursement/edit" => gettext("Edit disbursement"),
  
                          "/root/air/profile" => gettext("Account holder profile management for AIRGOLD"),
                          "/root/air/profile/list" => gettext("View user profile"),
                          "/root/air/profile/add" => gettext("Add user profile"),
                          "/root/air/profile/edit" => gettext("Edit user profile"),
                          "/root/air/profile/suspend" => gettext("Suspend account holder"),
                          "/root/air/profile/unsuspend" => gettext("Unsuspend account holder"),
  
                          "/root/air/approval" => gettext("PEP approval management for AIRGOLD"),
                          "/root/air/approval/list" => gettext("View PEP"),
                          "/root/air/approval/approve" => gettext("Approve or reject PEP"),
  
                          "/root/air/fpx" => gettext("FPX management for AIRGOLD"),
                          "/root/air/fpx/list" => gettext("View FPX"),
  
                          "/root/air/ekyc" => gettext("eKYC management for AIRGOLD"),
                          "/root/air/ekyc/list" => gettext("View eKYC"),
  
                          "/root/air/goldtransaction" => gettext("Gold transaction management for AIRGOLD"),
                          "/root/air/goldtransaction/list" => gettext("View gold transaction"),
                          "/root/air/goldtransaction/export" => gettext("Export gold transaction statement"),
  
                          "/root/air/storagefee" => gettext("Storage Fee management for AIRGOLD"),
                          "/root/air/storagefee/list" => gettext("View storage fee"),
                          "/root/air/fee" => gettext("Fee management"),
                          "/root/air/fee/list" => gettext("View fee management"),
                          "/root/air/fee/add" => gettext("Add fee"),
                          "/root/air/fee/edit" => gettext("Edit fee"),
  
  
                          "/root/air/vault" => gettext("Inventory management for AIRGOLD"),
                          "/root/air/vault/list" => gettext("View inventory"),
                          "/root/air/vault/add" => gettext("Add item to inventory"),
                          "/root/air/vault/edit" => gettext("Edit item in inventory"),
                          "/root/air/vault/return" => gettext("Return item"),
                          "/root/air/vault/transfer" => gettext("Transfer item"),
                          "/root/air/vault/approve" => gettext("Approve item"),
  
                          "/root/air/report" => gettext("Reporting management for AIRGOLD"),
                          "/root/air/report/commission" => gettext("Commission Reporting"),
                          "/root/air/report/commission/list" => gettext("View Commission Reporting"),
                          "/root/air/report/storagefee" => gettext("Storage Fee Reporting"),
                          "/root/air/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                          "/root/air/report/adminfee" => gettext("Admin Fee Reporting"),
                          "/root/air/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                          "/root/air/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                          "/root/air/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
  
                          "/root/air/accountclosure" => gettext("Account closure management for AIRGOLD"),
                          "/root/air/accountclosure/list" => gettext("View account closure"),
  
                          "/root/air/pricealert" => gettext("Price alert management for AIRGOLD"),
                          "/root/air/pricealert/list" => gettext("View price alert"),

                          "/root/air/sale" => gettext("Spot Order Special for AIRGOLD"),
                          // End AirGold Permissions
                        

                           // MCASH Permissions
                           "/root/mcash/redemption" => gettext("Conversion management for MCASH"),
                           "/root/mcash/redemption/list" => gettext("View conversion"),
                           "/root/mcash/redemption/add" => gettext("Add conversion"),
                           "/root/mcash/redemption/edit" => gettext("Edit conversion"),
                           "/root/mcash/redemption/export" => gettext("Export conversion"),
   
                           "/root/mcash/logistic" => gettext("Logistics management for MCASH"),
                           "/root/mcash/logistic/list" => gettext("View logistics information"),
                           "/root/mcash/logistic/add" => gettext("Add logistics"),
                           "/root/mcash/logistic/edit" => gettext("Edit logistics"),
                           "/root/mcash/logistic/delete" => gettext("Delete logistics"),
                           "/root/mcash/logistic/complete" => gettext("Complete logistics"),
   
                           "/root/mcash/disbursement" => gettext("Disbursement management for MCASH"),
                           "/root/mcash/disbursement/list" => gettext("View disbursement"),
                           "/root/mcash/disbursement/add" => gettext("Add disbursement"),
                           "/root/mcash/disbursement/edit" => gettext("Edit disbursement"),
   
                           "/root/mcash/profile" => gettext("Account holder profile management for MCASH"),
                           "/root/mcash/profile/list" => gettext("View user profile"),
                           "/root/mcash/profile/add" => gettext("Add user profile"),
                           "/root/mcash/profile/edit" => gettext("Edit user profile"),
                           "/root/mcash/profile/suspend" => gettext("Suspend account holder"),
                           "/root/mcash/profile/unsuspend" => gettext("Unsuspend account holder"),
   
                           "/root/mcash/approval" => gettext("PEP approval management for MCASH"),
                           "/root/mcash/approval/list" => gettext("View PEP"),
                           "/root/mcash/approval/approve" => gettext("Approve or reject PEP"),
   
                           "/root/mcash/fpx" => gettext("FPX management for MCASH"),
                           "/root/mcash/fpx/list" => gettext("View FPX"),
   
                           "/root/mcash/ekyc" => gettext("eKYC management for MCASH"),
                           "/root/mcash/ekyc/list" => gettext("View eKYC"),
   
                           "/root/mcash/goldtransaction" => gettext("Gold transaction management for MCASH"),
                           "/root/mcash/goldtransaction/list" => gettext("View gold transaction"),
                           "/root/mcash/goldtransaction/export" => gettext("Export gold transaction statement"),
   
                           "/root/mcash/storagefee" => gettext("Storage Fee management for MCASH"),
                           "/root/mcash/storagefee/list" => gettext("View storage fee"),
                           "/root/mcash/fee" => gettext("Fee management"),
                           "/root/amcashir/fee/list" => gettext("View fee management"),
                           "/root/mcash/fee/add" => gettext("Add fee"),
                           "/root/mcash/fee/edit" => gettext("Edit fee"),
   
   
                           "/root/mcash/vault" => gettext("Inventory management for MCASH"),
                           "/root/mcash/vault/list" => gettext("View inventory"),
                           "/root/mcash/vault/add" => gettext("Add item to inventory"),
                           "/root/mcash/vault/edit" => gettext("Edit item in inventory"),
                           "/root/mcash/vault/return" => gettext("Return item"),
                           "/root/mcash/vault/transfer" => gettext("Transfer item"),
                           "/root/mcash/vault/approve" => gettext("Approve item"),
   
                           "/root/mcash/report" => gettext("Reporting management for MCASH"),
                           "/root/mcash/report/commission" => gettext("Commission Reporting"),
                           "/root/mcash/report/commission/list" => gettext("View Commission Reporting"),
                           "/root/mcash/report/storagefee" => gettext("Storage Fee Reporting"),
                           "/root/mcash/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                           "/root/mcash/report/adminfee" => gettext("Admin Fee Reporting"),
                           "/root/mcash/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                           "/root/mcash/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                           "/root/mcash/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
  
   
                           "/root/mcash/accountclosure" => gettext("Account closure management for MCASH"),
                           "/root/mcash/accountclosure/list" => gettext("View account closure"),
   
                           "/root/mcash/pricealert" => gettext("Price alert management for MCASH"),
                           "/root/mcash/pricealert/list" => gettext("View price alert"),
 
                           "/root/mcash/sale" => gettext("Spot Order Special for MCASH"),
                           // End MCASH Permissions

                           // KTP Permissions
                           "/root/ktp/redemption" => gettext("Conversion management for KTP"),
                           "/root/ktp/redemption/list" => gettext("View conversion"),
                           "/root/ktp/redemption/add" => gettext("Add conversion"),
                           "/root/ktp/redemption/edit" => gettext("Edit conversion"),
                           "/root/ktp/redemption/export" => gettext("Export conversion"),
   
                           "/root/ktp/logistic" => gettext("Logistics management for KTP"),
                           "/root/ktp/logistic/list" => gettext("View logistics information"),
                           "/root/ktp/logistic/add" => gettext("Add logistics"),
                           "/root/ktp/logistic/edit" => gettext("Edit logistics"),
                           "/root/ktp/logistic/delete" => gettext("Delete logistics"),
                           "/root/ktp/logistic/complete" => gettext("Complete logistics"),
   
                           "/root/ktp/disbursement" => gettext("Disbursement management for KTP"),
                           "/root/ktp/disbursement/list" => gettext("View disbursement"),
                           "/root/ktp/disbursement/add" => gettext("Add disbursement"),
                           "/root/ktp/disbursement/edit" => gettext("Edit disbursement"),
   
                           "/root/ktp/profile" => gettext("Account holder profile management for KTP"),
                           "/root/ktp/profile/list" => gettext("View user profile"),
                           "/root/ktp/profile/add" => gettext("Add user profile"),
                           "/root/ktp/profile/edit" => gettext("Edit user profile"),
                           "/root/ktp/profile/suspend" => gettext("Suspend account holder"),
                           "/root/ktp/profile/unsuspend" => gettext("Unsuspend account holder"),
   
                           "/root/ktp/approval" => gettext("PEP approval management for KTP"),
                           "/root/ktp/approval/list" => gettext("View PEP"),
                           "/root/ktp/approval/approve" => gettext("Approve or reject PEP"),
   
                           "/root/ktp/fpx" => gettext("FPX management for KTP"),
                           "/root/ktp/fpx/list" => gettext("View FPX"),
   
                           "/root/ktp/ekyc" => gettext("eKYC management for KTP"),
                           "/root/ktp/ekyc/list" => gettext("View eKYC"),
   
                           "/root/ktp/goldtransaction" => gettext("Gold transaction management for KTP"),
                           "/root/ktp/goldtransaction/list" => gettext("View gold transaction"),
                           "/root/ktp/goldtransaction/export" => gettext("Export gold transaction statement"),
   
                           "/root/ktp/storagefee" => gettext("Storage Fee management for KTP"),
                           "/root/ktp/storagefee/list" => gettext("View storage fee"),
                           "/root/ktp/fee" => gettext("Fee management"),
                           "/root/ktp/fee/list" => gettext("View fee management"),
                           "/root/ktp/fee/add" => gettext("Add fee"),
                           "/root/ktp/fee/edit" => gettext("Edit fee"),
   
   
                           "/root/ktp/vault" => gettext("Inventory management for KTP"),
                           "/root/ktp/vault/list" => gettext("View inventory"),
                           "/root/ktp/vault/add" => gettext("Add item to inventory"),
                           "/root/ktp/vault/edit" => gettext("Edit item in inventory"),
                           "/root/ktp/vault/return" => gettext("Return item"),
                           "/root/ktp/vault/transfer" => gettext("Transfer item"),
                           "/root/ktp/vault/approve" => gettext("Approve item"),
   
                           "/root/ktp/report" => gettext("Reporting management for KTP"),
                           "/root/ktp/report/commission" => gettext("Commission Reporting"),
                           "/root/ktpv/report/commission/list" => gettext("View Commission Reporting"),
                           "/root/ktp/report/storagefee" => gettext("Storage Fee Reporting"),
                           "/root/ktp/report/storagefee/list" => gettext("View Storage Fee Reporting"),
                           "/root/ktp/report/adminfee" => gettext("Admin Fee Reporting"),
                           "/root/ktp/report/adminfee/list" => gettext("View Admin Fee Reporting"),
                           "/root/ktpv/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
                           "/root/ktp/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
  
   
                           "/root/ktp/accountclosure" => gettext("Account closure management for KTP"),
                           "/root/ktp/accountclosure/list" => gettext("View account closure"),
   
                           "/root/ktp/pricealert" => gettext("Price alert management for KTP"),
                           "/root/ktp/pricealert/list" => gettext("View price alert"),
 
                           "/root/ktp/sale" => gettext("Spot Order Special for KTP"),
                           // End MCASH Permissions

                        "/root/system/amlascanlog/list" => gettext("Amla scan log"),


                        "/root/system/myannouncement" => gettext("Announcement management"),
                        "/root/system/myannouncement/list" => gettext("View announcement management"),
                        "/root/system/myannouncement/approve" => gettext("Approve an announcement"),
                        "/root/system/myannouncement/add" => gettext("Add an announcement"),
                        "/root/system/myannouncement/edit" => gettext("Edit an announcement"),
                        "/root/system/myannouncement/disable" => gettext("Disable an announcement"),
                        "/root/system/myannouncement/delete" => gettext("Delete an announcement"),
                        "/root/system/myannouncementtheme" => gettext("Announcement theme management"),
                        "/root/system/myannouncementtheme/list" => gettext("View announcement theme management"),
                        "/root/system/myannouncementtheme/add" => gettext("Add an announcement theme"),
                        "/root/system/myannouncementtheme/edit" => gettext("Edit an announcement theme"),
                        "/root/system/myannouncementtheme/delete" => gettext("Delete an announcement theme"),

                ]);
        }

        protected function registerManager($managerKey, $managerClass, $observableTargets = array())
        {
                // Updated by Cheok on 2020-10-23 to allow re-registering of notificationmanager
                if (isset($this->managers[strtolower($managerKey)])) {
                        $manager = $this->managers[strtolower($managerKey)];
                        $manager['classname'] = $managerClass;
                        $manager['observableTargets'] = array_merge($manager['observableTargets'], $observableTargets);
                        $this->managers[strtolower($managerKey)] = $manager;
                } else {
                        $this->managers[strtolower($managerKey)] = array('classname' => $managerClass, 'observableTargets' => $observableTargets);
                }
                // End update by Cheok

                foreach ($observableTargets as $aManagerKey) {
                        if (!preg_match('/(store|factory)$/i', $aManagerKey)) {
                                $this->observerableArray[$aManagerKey][] = $managerKey;
                        } else {
                                //We have to start up this class immediately because we have to link it to the data store
                                $store = call_user_func_array(array($this, $aManagerKey), null);
                                if ($store && $store instanceof \Snap\IObservable) {
                                        $theManager = $this->getManager($managerKey);
                                        $store->attach($theManager);
                                }
                        }
                }
        }
}
