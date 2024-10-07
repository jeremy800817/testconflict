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
class bsncontroller extends otccontroller
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

				$bsnStores = [
					'otcpricingmodel'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
						$dbHandle, 'otcpricingmodel', null, 'prm', '\Snap\object\OtcPricingModel', array(),  array(), true
					]],
					'otcmanagementfee' => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
						$dbHandle, 'otcmanagementfee', null, 'mgf', '\Snap\object\OtcManagementFee', array(),  array(), true
					]],
					'otcoutstandingstoragefeejob' => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
						$dbHandle, 'otcoutstandingstoragefeejob', null, 'osf', '\Snap\object\OtcOutstandingStorageFeeJob', array('vw_otcoutstandingstoragefeejob'),  array(), true
					]],
                    'userswitchbranchlog'  => ['class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
						$dbHandle, 'userswitchbranchlog', null, 'usb', '\Snap\object\Userswitchbranchlog', array('vw_userswitchbranchlog'),  array(), true
					]]
				];
				
				//We can now register the store that can be used directly with $app->xxxxStore() methods (*if* publicAccessible = true).
                foreach ($bsnStores as $storeKey => $storeProps) {
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

                        // "/root/bsn" => gettext("BSN Modules"),
                        "/root/bsn" => gettext("BSN Modules"),
                        // "/root/go" => gettext("GO Modules"),
                        // "/root/bsn" => gettext("BSN Modules"),

                        
            // BSN Permissions
			"/root/bsn/search" => gettext("Search Module for BSN"),
            "/root/bsn/search/buy" => gettext("Allow Buy Module for BSN"),
            "/root/bsn/search/sell" => gettext("Allow Sell Module for BSN"),
            "/root/bsn/search/profile" => gettext("Allow View Profile Module for BSN"),
            "/root/bsn/search/redemption" => gettext("Allow Redemption Module for BSN"),
            "/root/bsn/search/goldtransaction" => gettext("Allow View Transaction Module for BSN"),
			
            "/root/bsn/redemption" => gettext("Conversion management for BSN"),
            "/root/bsn/redemption/list" => gettext("View conversion"),
            "/root/bsn/redemption/add" => gettext("Add conversion"),
            "/root/bsn/redemption/edit" => gettext("Edit conversion"),
            "/root/bsn/redemption/export" => gettext("Export conversion"),

            "/root/bsn/logistic" => gettext("Logistics management for BSN"),
            "/root/bsn/logistic/list" => gettext("View logistics information"),
            "/root/bsn/logistic/add" => gettext("Add logistics"),
            "/root/bsn/logistic/edit" => gettext("Edit logistics"),
            "/root/bsn/logistic/delete" => gettext("Delete logistics"),
            "/root/bsn/logistic/complete" => gettext("Complete logistics"),

            "/root/bsn/disbursement" => gettext("Disbursement management for BSN"),
            "/root/bsn/disbursement/list" => gettext("View disbursement"),
            "/root/bsn/disbursement/add" => gettext("Add disbursement"),
            "/root/bsn/disbursement/edit" => gettext("Edit disbursement"),

            "/root/bsn/profile" => gettext("Account holder profile management for BSN"),
            "/root/bsn/profile/list" => gettext("View user profile"),
            "/root/bsn/profile/add" => gettext("Add user profile"),
            "/root/bsn/profile/edit" => gettext("Edit user profile"),
            "/root/bsn/profile/suspend" => gettext("Suspend account holder"),
            "/root/bsn/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/bsn/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/bsn/profile/updateMember" => gettext("Update account holder member list"),
            "/root/bsn/profile/activate" => gettext("Activate account holder"),

            "/root/bsn/approval" => gettext("PEP approval management for BSN"),
            "/root/bsn/approval/list" => gettext("View PEP"),
            "/root/bsn/approval/approve" => gettext("Approve or reject PEP"),

            "/root/bsn/fpx" => gettext("FPX management for BSN"),
            "/root/bsn/fpx/list" => gettext("View FPX"),

            "/root/bsn/ekyc" => gettext("eKYC management for BSN"),
            "/root/bsn/ekyc/list" => gettext("View eKYC"),

            "/root/bsn/goldtransaction" => gettext("Gold transaction management for BSN"),
            "/root/bsn/goldtransaction/list" => gettext("View gold transaction"),
            "/root/bsn/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/bsn/storagefee" => gettext("Storage Fee management for BSN"),
            "/root/bsn/storagefee/list" => gettext("View storage fee"),
            "/root/bsn/fee" => gettext("Fee management"),
            "/root/bsn/fee/list" => gettext("View fee management"),
            "/root/bsn/fee/add" => gettext("Add fee"),
            "/root/bsn/fee/edit" => gettext("Edit fee"),


            "/root/bsn/vault" => gettext("Inventory management for BSN"),
            "/root/bsn/vault/list" => gettext("View inventory"),
            "/root/bsn/vault/add" => gettext("Add item to inventory"),
            "/root/bsn/vault/edit" => gettext("Edit item in inventory"),
            "/root/bsn/vault/return" => gettext("Return item"),
            "/root/bsn/vault/transfer" => gettext("Transfer item"),

            // "/root/bsn/vault/request" => gettext("Request item"), // maker
            // "/root/bsn/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/bsn/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/bsn/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/bsn/report" => gettext("Reporting management for BSN"),
            "/root/bsn/report/commission" => gettext("Commission Reporting"),
            "/root/bsn/report/commission/list" => gettext("View Commission Reporting"),
            "/root/bsn/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/bsn/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/bsn/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/bsn/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/bsn/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/bsn/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),

            "/root/bsn/yearlystatement" => gettext("Yearly Statement for BSN"),
	    "/root/bsn/yearlystatement/reprintyearlystatement" => gettext("Print Yearly Statement BSN"),


            "/root/bsn/accountclosure" => gettext("Account closure management for BSN"),
            "/root/bsn/accountclosure/list" => gettext("View account closure"),
            "/root/bsn/accountclosure/close" => gettext("Close account"),

            "/root/bsn/pricealert" => gettext("Price alert management for BSN"),
            "/root/bsn/pricealert/list" => gettext("View price alert"),

            "/root/bsn/sale" => gettext("Spot Order Special for BSN"),

            "/root/bsn/transfergold" => gettext("Transfer Gold for BSN"),
            "/root/bsn/transfergold/list" => gettext("View Transfer Gold"),
            "/root/bsn/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/bsn/promo" => gettext("Promo for BSN"),
            "/root/bsn/promo/list" => gettext("View Promo"),
            "/root/bsn/promo/export" => gettext("Export Promo"),

            // New Permission for OTC
            // 1) Add approval modules
            "/root/bsn/goldtransaction/approval" => gettext("Approval for BSN"),
            "/root/bsn/goldtransaction/buy" => gettext("Allow Buy Module for BSN"),
            "/root/bsn/goldtransaction/sell" => gettext("Allow Sell Module for BSN"),
        //     "/root/bsn/approval/list" => gettext("View Approval"),
        //     "/root/bsn/approval/export" => gettext("Export Approval"),

            // 2) Add Analytics modules
            "/root/bsn/analytics" => gettext("Analytics for BSN"),
            "/root/bsn/register" => gettext("Registration for BSN"),

            // dahsboard for analytics
            "/root/bsn/dashboard" => gettext("Analytics Dashboard for BSN"),
            "/root/bsn/dashboard/totalaccholder" => gettext("Analytics(Total Account Holder) for BSN"),
            "/root/bsn/dashboard/totalbuygold" => gettext("Analytics(Total Customer Sell Gold) for BSN"),
            "/root/bsn/dashboard/totalsellgold" => gettext("Analytics(Total Customer Buy Gold) for BSN"),
            "/root/bsn/dashboard/vault" => gettext("Analytics(Vault) for BSN"),
            "/root/bsn/dashboard/totalcustomerholding" => gettext("Analytics(Total Customer Holding) for BSN"),
            "/root/bsn/dashboard/balance" => gettext("Analytics(Gold Balance) for BSN"),
			
			"/root/bsn/managementfee" => gettext("Management fee"),
			"/root/bsn/managementfee/list" => gettext("View management fee"),
			"/root/bsn/managementfee/add" => gettext("Add management fee"),
			"/root/bsn/managementfee/edit" => gettext("Edit management fee"),
			"/root/bsn/managementfee/delete" => gettext("Delete management fee"),
            "/root/bsn/managementfee/manualdeduction" => gettext("Management Fee Manual Deduction"),

			"/root/bsn/managementfeereport" => gettext("Management Fee Report"),
			"/root/bsn/managementfeereport/list" => gettext("View Management Fee Report"),
			"/root/bsn/managementfeereport/deductionsuccess" => gettext("View Management Fee Deduction Success"),
			"/root/bsn/managementfeereport/deductionfailed" => gettext("View Management Fee  Deduction Failed"),

            //userswitchbranchlog
            "/root/bsn/userswitchbranchlog" => gettext("User Switch Branch"),
            "/root/bsn/userswitchbranchlog/list" => gettext("User Switch Branch Log"),

			// End BSN Permissions
			
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
