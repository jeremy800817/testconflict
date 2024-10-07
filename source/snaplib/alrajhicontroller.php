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
class alrajhicontroller extends otccontroller
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
                $alrajhiStores = [
                        'user' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                                $dbHandle, 'user', null, 'usr', '\Snap\object\User', array('vw_user'), array('usradditionaldata' => 'usradditionaldataLazyStore'), true]],
                ];
                
                //We can now register the store that can be used directly with $app->xxxxStore() methods (*if* publicAccessible = true).
                foreach ($alrajhiStores as $storeKey => $storeProps) {
                     $this->registerStore($storeKey, $storeProps, is_array($storeProps) ? $storeProps['publicAccessible'] : true);
                }

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

                        // "/root/alrajhi" => gettext("ALRAJHI Modules"),
                        "/root/alrajhi" => gettext("ALRAJHI Modules"),
                        // "/root/go" => gettext("GO Modules"),
                        // "/root/alrajhi" => gettext("ALRAJHI Modules"),

                        
            // ALRAJHI Permissions

            // New permission for search
            "/root/alrajhi/search" => gettext("Search Module for ALRAJHI"),
            "/root/alrajhi/search/buy" => gettext("Allow Buy Module for ALRAJHI"),
            "/root/alrajhi/search/sell" => gettext("Allow Sell Module for ALRAJHI"),
            "/root/alrajhi/search/profile" => gettext("Allow View Profile Module for ALRAJHI"),
            "/root/alrajhi/search/redemption" => gettext("Allow Redemption Module for ALRAJHI"),
            "/root/alrajhi/search/goldtransaction" => gettext("Allow View Transaction Module for ALRAJHI"),

            "/root/alrajhi/redemption" => gettext("Conversion management for ALRAJHI"),
            "/root/alrajhi/redemption/list" => gettext("View conversion"),
            "/root/alrajhi/redemption/add" => gettext("Add conversion"),
            "/root/alrajhi/redemption/edit" => gettext("Edit conversion"),
            "/root/alrajhi/redemption/export" => gettext("Export conversion"),

            "/root/alrajhi/logistic" => gettext("Logistics management for ALRAJHI"),
            "/root/alrajhi/logistic/list" => gettext("View logistics information"),
            "/root/alrajhi/logistic/add" => gettext("Add logistics"),
            "/root/alrajhi/logistic/edit" => gettext("Edit logistics"),
            "/root/alrajhi/logistic/delete" => gettext("Delete logistics"),
            "/root/alrajhi/logistic/complete" => gettext("Complete logistics"),

            "/root/alrajhi/disbursement" => gettext("Disbursement management for ALRAJHI"),
            "/root/alrajhi/disbursement/list" => gettext("View disbursement"),
            "/root/alrajhi/disbursement/add" => gettext("Add disbursement"),
            "/root/alrajhi/disbursement/edit" => gettext("Edit disbursement"),

            "/root/alrajhi/profile" => gettext("Account holder profile management for ALRAJHI"),
            "/root/alrajhi/profile/list" => gettext("View user profile"),
            "/root/alrajhi/profile/add" => gettext("Add user profile"),
            "/root/alrajhi/profile/edit" => gettext("Edit user profile"),
            "/root/alrajhi/profile/suspend" => gettext("Suspend account holder"),
            "/root/alrajhi/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/alrajhi/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/alrajhi/profile/updateMember" => gettext("Update account holder member list"),
            "/root/alrajhi/profile/activate" => gettext("Activate account holder"),

            "/root/alrajhi/approval" => gettext("PEP approval management for ALRAJHI"),
            "/root/alrajhi/approval/list" => gettext("View PEP"),
            "/root/alrajhi/approval/approve" => gettext("Approve or reject PEP"),

            "/root/alrajhi/fpx" => gettext("FPX management for ALRAJHI"),
            "/root/alrajhi/fpx/list" => gettext("View FPX"),

            "/root/alrajhi/ekyc" => gettext("eKYC management for ALRAJHI"),
            "/root/alrajhi/ekyc/list" => gettext("View eKYC"),

            "/root/alrajhi/goldtransaction" => gettext("Gold transaction management for ALRAJHI"),
            "/root/alrajhi/goldtransaction/list" => gettext("View gold transaction"),
            "/root/alrajhi/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/alrajhi/storagefee" => gettext("Storage Fee management for ALRAJHI"),
            "/root/alrajhi/storagefee/list" => gettext("View storage fee"),
            "/root/alrajhi/fee" => gettext("Fee management"),
            "/root/alrajhi/fee/list" => gettext("View fee management"),
            "/root/alrajhi/fee/add" => gettext("Add fee"),
            "/root/alrajhi/fee/edit" => gettext("Edit fee"),


            "/root/alrajhi/vault" => gettext("Inventory management for ALRAJHI"),
            "/root/alrajhi/vault/list" => gettext("View inventory"),
            "/root/alrajhi/vault/add" => gettext("Add item to inventory"),
            "/root/alrajhi/vault/edit" => gettext("Edit item in inventory"),
            "/root/alrajhi/vault/return" => gettext("Return item"),
            "/root/alrajhi/vault/transfer" => gettext("Transfer item"),

            // "/root/alrajhi/vault/request" => gettext("Request item"), // maker
            // "/root/alrajhi/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/alrajhi/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/alrajhi/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/alrajhi/report" => gettext("Reporting management for ALRAJHI"),
            "/root/alrajhi/report/commission" => gettext("Commission Reporting"),
            "/root/alrajhi/report/commission/list" => gettext("View Commission Reporting"),
            "/root/alrajhi/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/alrajhi/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/alrajhi/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/alrajhi/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/alrajhi/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/alrajhi/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/alrajhi/accountclosure" => gettext("Account closure management for ALRAJHI"),
            "/root/alrajhi/accountclosure/list" => gettext("View account closure"),
            "/root/alrajhi/accountclosure/close" => gettext("Close account"),

            "/root/alrajhi/pricealert" => gettext("Price alert management for ALRAJHI"),
            "/root/alrajhi/pricealert/list" => gettext("View price alert"),

            "/root/alrajhi/sale" => gettext("Spot Order Special for ALRAJHI"),

            "/root/alrajhi/transfergold" => gettext("Transfer Gold for ALRAJHI"),
            "/root/alrajhi/transfergold/list" => gettext("View Transfer Gold"),
            "/root/alrajhi/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/alrajhi/promo" => gettext("Promo for ALRAJHI"),
            "/root/alrajhi/promo/list" => gettext("View Promo"),
            "/root/alrajhi/promo/export" => gettext("Export Promo"),

            // New Permission for OTC
            // 1) Add approval modules
            "/root/alrajhi/goldtransaction/export" => gettext("Export gold transaction statement"),
           
        //     "/root/alrajhi/approval/list" => gettext("View Approval"),
        //     "/root/alrajhi/approval/export" => gettext("Export Approval"),

            // 2) Add Analytics modules
            "/root/alrajhi/analytics" => gettext("Analytics for ALRAJHI"),
            "/root/alrajhi/register" => gettext("Registration for ALRAJHI"),
            "/root/alrajhi/analytics/totalaccholder" => gettext("Analytics(Total Account Holder) for ALRAJHI"),
            "/root/alrajhi/analytics/totalbuygold" => gettext("Analytics(Total Buy Gold) for ALRAJHI"),
            "/root/alrajhi/analytics/totalsellgold" => gettext("Analytics(Total Sell Gold) for ALRAJHI"),
            "/root/alrajhi/analytics/vault" => gettext("Analytics(Vault) for ALRAJHI"),
            "/root/alrajhi/analytics/totalcustomerholding" => gettext("Analytics(Total Customer Holding) for ALRAJHI"),
            "/root/alrajhi/analytics/balance" => gettext("Analytics(Gold Balance) for ALRAJHI"),
            // End ALRAJHI Permissions


                // new
                "/root/system"  => gettext("System  setup related modules"),
                "/root/system/partner" => gettext("Partner management"),
                "/root/system/partner/list" => gettext("View partner information"),
                "/root/system/partner/add" => gettext("Add partner"),
                "/root/system/partner/edit" => gettext("Edit partner"),
                "/root/system/partner/delete" => gettext("Delete partner"),
                "/root/system/product" => gettext("Product management"),
                "/root/system/product/list" => gettext("View product information"),
                "/root/system/product/add" => gettext("Add product"),
                "/root/system/product/edit" => gettext("Edit product"),
                "/root/system/product/delete" => gettext("Delete product"),
                "/root/system/priceprovider" => gettext("Price source management"),
                "/root/system/priceprovider/list" => gettext("View price source"),
                "/root/system/priceprovider/add" => gettext("Add price source"),
                "/root/system/priceprovider/edit" => gettext("Edit price source"),
                "/root/system/priceprovider/delete" => gettext("Delete asset type"),
                "/root/system/tradingschedule" => gettext("Trading schedule management"),
                "/root/system/tradingschedule/list" => gettext("View trading schedule"),
                "/root/system/tradingschedule/add" => gettext("Add trading schedule"),
                "/root/system/tradingschedule/edit" => gettext("Edit trading schedule"),
                "/root/system/tradingschedule/delete" => gettext("Delete inventory type"),

                "/root/system/trader" => gettext("Trader management"),
                "/root/system/trader/list" => gettext("View trader management"),
                "/root/system/trader/add" => gettext("Add trader management"),
                "/root/system/trader/edit" => gettext("Edit trader management"),
                "/root/system/trader/cancel" => gettext("Cancel trader management"),
                "/root/system/trader/confirm" => gettext("Confirm trader management"),
                "/root/system/trader/ktplist" => gettext("View trader for KTP"),

                "/root/system/priceadjuster" => gettext("Priceadjuster management"),
                "/root/system/priceadjuster/list" => gettext("View priceadjuster management"),
                "/root/system/priceadjuster/add" => gettext("Add priceadjuster management"),
                "/root/system/priceadjuster/edit" => gettext("Edit priceadjuster management"),
                "/root/system/priceadjuster/cancel" => gettext("Cancel priceadjuster management"),
                "/root/system/priceadjuster/confirm" => gettext("Confirm priceadjuster management"),

                "/root/system/announcement" => gettext("Announcement management"),
                "/root/system/announcement/list" => gettext("View announcement management"),
                "/root/system/announcement/add" => gettext("Add announcement management"),
                "/root/system/announcement/edit" => gettext("Edit announcement management"),
                "/root/system/announcement/cancel" => gettext("Cancel announcement management"),
                "/root/system/announcement/confirm" => gettext("Confirm announcement management"),
                "/root/system/announcement/delete" => gettext("Delete announcement"),
                "/root/system/apilog" => gettext("Api Logs management"),
                "/root/system/apilog/list" => gettext("View Api logs"),
                "/root/system/event/event_message" => gettext("Event Message"),
                "/root/system/event/event_message/list" => gettext("View Event Message"),
                "/root/system/event/event_message/add" => gettext("Add Event Message"),
                "/root/system/event/event_message/edit" => gettext("Edit Event Message"),
                "/root/system/event/event_message/delete" => gettext("Delete Event Message"),
                "/root/system/event/event_subscription" => gettext("Event Subscription"),
                "/root/system/event/event_subscription/list" => gettext("View Event Subscription"),
                "/root/system/event/event_subscription/add" => gettext("Add Event Subscription"),
                "/root/system/event/event_subscription/edit" => gettext("Edit Event Subscription"),
                "/root/system/event/event_subscription/delete" => gettext("Delete Event Subscription"),
                "/root/system/event/event_log" => gettext("Event Log"),
                "/root/system/event/event_log/list" => gettext("View Event Log"),
                "/root/system/audit" => gettext("Audit Trail for GTP"),
                "/root/system/audit/list" => gettext("View Audit Trail for GTP"),
                
                "/root/system/pricestream" => gettext("Price stream management"),
                "/root/system/pricestream/list" => gettext("View price streams"),
                "/root/system/pricestream/add" => gettext("Add price streams"),
                "/root/system/pricevalidation" => gettext("Price validation management"),
                "/root/system/pricevalidation/list" => gettext("View price validations"),
                "/root/system/pricevalidation/add" => gettext("Add price validations"),
                "/root/system/salescommission" => gettext("Sales commission management"),
                "/root/system/salescommission/list" => gettext("View sales commission"),
                "/root/system/salescommission/add" => gettext("Add sales commission"),
                "/root/system/salescommission/edit" => gettext("Edit sales commission"),
                "/root/system/salescommission/delete" => gettext("Delete sales commission"),
                // "/root/mbb/apfund" => gettext("MBB AP Fund pool management"),
                // "/root/mbb/apfund/list" => gettext("View MBB AP Fund pool management"),

                "/root/system/redemption" => gettext("Redemption management"),
                "/root/system/redemption/list" => gettext("View redemption"),
                "/root/system/redemption/add" => gettext("Add redemption"),
                "/root/system/redemption/edit" => gettext("Edit redemption"),
                "/root/system/replenishment" => gettext("Replenishment management"),
                "/root/system/replenishment/list" => gettext("View replenishment"),
                "/root/system/replenishment/add" => gettext("Add replenishment"),
                "/root/system/replenishment/edit" => gettext("Edit replenishment"),

                "/root/system/mintedbar" => gettext("Minted Warehouse"),
                "/root/system/mintedbar/list" => gettext("View Minted Warehouse"),

                "/root/system/documentation" => gettext("Documentation management"),
                "/root/system/documentation/list" => gettext("View documentation"),
                "/root/system/documentation/add" => gettext("Add documentation"),
                "/root/system/documentation/edit" => gettext("Edit documentation"),
                // End New additions
                
                "/root/system/pushnotification" => gettext("Push notification management"),
                "/root/system/pushnotification/list" => gettext("View push notification management"),
                "/root/system/pushnotification/add" => gettext("Add push notification management"),
                "/root/system/pushnotification/edit" => gettext("Edit push notification management"),
                //"/root/bmmb/pushnotification/cancel" => gettext("Cancel push notification"),
                //"/root/bmmb/pushnotification/confirm" => gettext("Confirm push notification"),
                "/root/system/pushnotification/delete" => gettext("Delete push notification"),

                "/root/system/amla" => gettext("AMLA management"),
                "/root/system/amla/list" => gettext("View amla"),
                "/root/system/amla/import" => gettext("Permission to import amla"),


                // "/root/common/vault" => gettext("Inventory management"),
                // "/root/common/vault/list" => gettext("View inventory"),
                // "/root/common/vault/add" => gettext("Add item to inventory"),
                // "/root/common/vault/edit" => gettext("Edit item in inventory"),
                // "/root/common/vault/return" => gettext("Return item"),
                // "/root/common/vault/transfer" => gettext("Transfer item"),
                // "/root/common/vault/approve" => gettext("Approve item"),
                // "/root/common/vault/download" => gettext("Download item"),
                // "/root/common/vault/print" => gettext("Print item"),


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
