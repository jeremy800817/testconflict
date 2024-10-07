<?php
/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2018
*/
Namespace Snap;

Use Snap\store\dbdatastore;
Use Snap\store\redisarraydbdatastore;
Use Snap\redisarraycacher;

/**
* This controller class implements the basic framework entities binding and facilities.  Inherit from this
* class to extends its functionality and then add a key in the config file setting to indicate the new
* controller class to use.
*
* @author Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  payment.base
*/
class gtpcontroller extends Controller
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
        if(!$cacheHandle instanceof \Snap\redisarraycacher) {
            throw new Exception("GtpController::initialiseController() unable to continue because it requires Redis cacher to run");
        }

        //Initialise all the required store objects
        $gtpStores = [
            'tag' => [ 'class' => '\Snap\store\tagstore', 'publicAccessible' => true, 'parameters' => [
                    $cacheHandle, $dbHandle, 'tag', null, 'tag', '\Snap\object\Tag', array('vw_tag'), array(), true]],
            //'taglink' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                    //$dbHandle, 'taglink', null, 'tlk', '\Snap\object\TagLink', array('vw_taglink'), array('tag' => 'tagStore'), true]],
            'partner' => [ 'class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                    $cacheHandle, $dbHandle, 'partner', null, 'par', '\Snap\object\Partner', array('vw_partner'),
                    array( 'services' => 'partnerserviceLazyStore', 'branches' => 'partnerbranchmapLazyStore'), true]],
            'partnerservice' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => false, 'parameters' => [
                    $dbHandle, 'partnerservice', null, 'pas', '\Snap\object\PartnerService', array('vw_partnerservice'), array('product'=> 'productLazyStore'), true]],
            'partnerbranchmap' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => false,  'parameters' => [
                        $dbHandle, 'partnerbranchmap', null, 'pbm', '\Snap\object\PartnerBranchMap', array(), array(), true]],
            'product' => [ 'class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true, 'parameters' => [
                    $cacheHandle, $dbHandle, 'product', null, 'pdt', '\Snap\object\Product', array(), array(), true]],
            'apilog' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                    $dbHandle, 'apilogs', null, 'api', '\Snap\object\ApiLogs', array('vw_apilogs'), array(), true]],
            'ftplog' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                    $dbHandle, 'ftplogs', null, 'ftp', '\Snap\object\FtpLogs', array(), array(), true]],
            'exportlog' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true, 'parameters' => [
                    $dbHandle, 'exportlogs', null, 'exg', '\Snap\object\ExportLogs', array(), array(), true]],
            'mbbapfund' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'mbbapfund', null, 'apf', '\Snap\object\MbbApFund', array('vw_mbbapfund'), array(), true]],
            'apigoldrequest' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'apiGoldRequest', null, 'agr', '\Snap\object\ApiGoldRequest', array(), array(), true]],
            'appstate' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'app_state', null, 'stt', '\Snap\object\AppState', array(), array(), true]],
            'attachment' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'attachment', null, 'att', '\Snap\object\Attachment', array(), array(), true]],
            'announcement' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'announcement', null, 'ann', '\Snap\object\Announcement', array(),  array('attachment' => 'attachmentStore'), true]],
            'calendar' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'calendar', null, 'cal', '\Snap\object\Calendar', array(), array(), true]],
            'documents' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'documents', null, 'doc', '\Snap\object\Documents', array(), array(), true]],
            'buyback' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'buyback', null, 'byb', '\Snap\object\Buyback', array('vw_buyback'), array('partner' => 'partnerLazyStore', 'product' => 'productLazyStore'), true]],
            'buybacklogistic' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'buybacklogistic', null, 'byl', '\Snap\object\BuybackLogistic', array(), array(), true]],
            'goodsreceivenotedraft' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'goodreceivenotedraft', null, 'grd', '\Snap\object\GoodsReceivedNoteDraft', array(), array(), true]],
            'goodsreceivenote' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'goodsreceivenote', null, 'grn', '\Snap\object\GoodsReceiveNote', array('vw_goodsreceivenote'), array(), true]],
            'goodsreceivenoteorder' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'goodsreceivenoteorder', null, 'gro', '\Snap\object\GoodsReceiveNoteOrder', array(), array(), true]],
            'iprestriction' => [ 'class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $cacheHandle, $dbHandle, 'iprestriction', null, 'ipr', '\Snap\object\IPRestriction', array(), array(), true]],
            'logistic' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'logistic', null, 'lgs', '\Snap\object\Logistic', array('vw_logistic'), array(), true]],
            'logisticlog' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'logisticlog', null, 'lgl', '\Snap\object\LogisticLog', array(), array(), true]],
            'order' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'order', null, 'ord', '\Snap\object\Order', array('vw_order'), array('partner' => 'partnerLazyStore', 'product' => 'productLazyStore'), true]],
            'orderqueue' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'orderqueue', null, 'orq', '\Snap\object\OrderQueue', array('vw_orderqueue'), array('partner' => 'partnerLazyStore', 'product' => 'productLazyStore'), true]],
            'ordercancel' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'orderqueue', null, 'orq', '\Snap\object\OrderCancel', array('vw_order_queue'), array(), true]],
            'priceprovider' => [ 'class' => '\Snap\store\PriceProviderStore', 'publicAccessible' => true,  'parameters' => [
                        $cacheHandle, $dbHandle, 'priceprovider', null, 'prp', '\Snap\object\PriceProvider', array('vw_priceprovider'), array(), true]],
            'pricestream' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'pricestream', null, 'pst', '\Snap\object\PriceStream', array('vw_pricestream'), array(), true]],
            'pricevalidation' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'pricevalidation', null, 'pva', '\Snap\object\PriceValidation', array('vw_pricevalidation'), array('partner' => 'partnerLazyStore'), true]],
            'priceadjuster' => [ 'class' => '\Snap\store\priceAdjusterStore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'priceadjuster', null, 'paj', '\Snap\object\PriceAdjuster', array('vw_priceadjuster'), array(), true]],
            'redemption' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'redemption', null, 'rdm', '\Snap\object\Redemption', array('vw_redemption'), array(), true]],
            'replenishment' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'replenishment', null, 'rpm', '\Snap\object\Replenishment', array('vw_replenishment'), array(), true]],
            'replenishmentlogistic' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'replenishmentlogistic', null, 'rpl', '\Snap\object\ReplenishmentLogistic', array(), array(), true]],
            'smsoutbox' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'smsoutbox', null, 'sms', '\Snap\object\SMSOutBox', array(), array(), true]],
            'salescommission' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'salescommission', null, 'com', '\Snap\object\SalesCommission', array(), array(), true]],
            'taglink' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'taglink', null, 'tlk', '\Snap\object\TagLink', array(), array(), true]],
            'tradingschedule' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'tradingschedule', null, 'tds', '\Snap\object\TradingSchedule', array('vw_tradingschedule'), array('tag' => 'tagStore'), true]],
            'user' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'user', null, 'usr', '\Snap\object\User', array('vw_user'), array(), true]],
            'userlog' => [ 'class' => '\Snap\store\userlogstore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'userlog', null, 'usl', '\Snap\object\UserLog', array(), array(), true]],
            'vaultitem' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'vaultitem', null, 'sti', '\Snap\object\VaultItem', array('vw_vaultitem'), array('vaultlocation' => 'vaultlocationLazyStore'), true]],
            'vaultitemtrans' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'vaultitemtrans', null, 'vit', '\Snap\object\VaultItemTrans', array('vw_vaultitemtrans'), array('vaultlocation' => 'vaultlocationLazyStore', 'vaultitemtransitem' => 'vaultitemtransitemLazyStore'), true]],
            'vaultitemtransitem' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'vaultitemtransitem', null, 'vti', '\Snap\object\VaultItemTransItem', array('vw_vaultitemtransitem'), array(), true]],
            'vaultlocation' => [ 'class' => '\Snap\store\redisarraydbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $cacheHandle, $dbHandle, 'vaultlocation', null, 'stl', '\Snap\object\VaultLocation', array('vw_vaultlocation'), array(), true]],
            'achadditionaldata' => [ 'class' => '\Snap\store\dbdatastore', 'publicAccessible' => true,  'parameters' => [
                        $dbHandle, 'achadditionaldata', null, 'aad', '\Snap\object\AchAdditionalData', array(),  array(), true]],
        ];

        //We can now register the store that can be used directly with $app->xxxxStore() methods (*if* publicAccessible = true).
        foreach ($gtpStores as $storeKey => $storeProps) {
            $this->registerStore($storeKey, $storeProps, is_array($storeProps)? $storeProps['publicAccessible'] : true);
        }

        //Manager configurations -IMPORTANT:  Register all observable targets first, then only the observers
        // $this->registerManager('schedule', '\Snap\manager\schedulemanager', array());
        $this->registerManager('spotorder', '\Snap\manager\SpotOrderManager', array());
        $this->registerManager('futureorder', '\Snap\manager\FutureOrderManager', array('price', 'spotorder'));
        $this->registerManager('price', '\Snap\manager\PriceManager', array('spotorder'));
        $this->registerManager('api', '\Snap\manager\ApiManager', array('price'));
        $this->registerManager('mbbanp', '\Snap\manager\MbbAnPManager', array('spotorder'));
        $this->registerManager('logistic', '\Snap\manager\LogisticManager', array());
        $this->registerManager('redemption', '\Snap\manager\RedemptionManager', array('logistic'));
        $this->registerManager('replenishment', '\Snap\manager\ReplenishmentManager', array('logistic'));
        $this->registerManager('buyback', '\Snap\manager\BuybackManager', array('logistic'));
        $this->registerManager('bankvault', '\Snap\manager\BankVaultManager', array());
        $this->registerManager('partner', '\Snap\manager\partnermanager', array());
        $this->registerManager('queue', '\Snap\manager\QueueManager', array());
        $this->registerManager('ftpprocessor', '\Snap\manager\FtpProcessorManager', array());
        $this->registerManager('eventtest', '\Snap\manager\EventTestManager', array());
        $this->registerManager('sms', '\Snap\manager\SmsManager', array());
        $this->registerManager('reporting', '\Snap\manager\ReportingManager', array());
        $this->registerManager('goodsreceivednote', '\Snap\manager\GoodsReceivedNoteManager', array());
        $this->registerManager('documents', '\Snap\manager\DocumentsManager', array());
        // $this->registerManager('workflow', '\Snap\manager\workflowmanager', array('settlement', 'partner', 'account', 'member', 'fundin'));
        $this->registerManager('notification', '\Snap\manager\notificationManager',
                                array('spotorder', 'buyback', 'futureorder', 'price', 'api', 'redemption', 'bankvault', 'partner', 'mbbanp', 'logistic', 'eventtest'));

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
            "/root/developer" => gettext("Developer access only modules"),
            "/root/developer/tag" => gettext("Tag Editing access only modules"),
            "/root/operator"  => gettext("Operator special access modules"),
            
            "/root/gtp" => gettext("GTP Modules"),
            "/root/mbb" => gettext("MIB Modules"),
            "/root/gtp/order" => gettext("Order management for GTP"),
            "/root/gtp/order/list" => gettext("View order for GTP"),
            "/root/gtp/order/add" => gettext("Add order for GTP"),
            "/root/gtp/order/edit" => gettext("Edit order for GTP"),
            "/root/gtp/order/cancel" => gettext("Cancel order for GTP"),
            "/root/gtp/order/confirm" => gettext("Confirm order for GTP"),
            "/root/gtp/order/submit" => gettext("Submit order to SAP for GTP"),
            "/root/mbb/order" => gettext("Order management for MBB"),
            "/root/mbb/order/list" => gettext("View order for MBB"),
            "/root/mbb/order/add" => gettext("Add order for MBB"),
            "/root/mbb/order/edit" => gettext("Edit order for MBB"),
            "/root/mbb/order/cancel" => gettext("Cancel order for MBB"),
            "/root/mbb/order/confirm" => gettext("Confirm order for MBB"),
            "/root/mbb/order/export" => gettext("Export order for MBB"),
            "/root/gtp/ftrorder" => gettext("Future order management for GTP"),
            "/root/gtp/ftrorder/list" => gettext("View future order for GTP"),
            "/root/gtp/ftrorder/add" => gettext("Add future order for GTP"),
            "/root/gtp/ftrorder/edit" => gettext("Edit future order for GTP"),
            "/root/gtp/ftrorder/cancel" => gettext("Cancel future order for GTP"),
            "/root/gtp/ftrorder/confirm" => gettext("Confirm future order for GTP"),
            //"/root/gtp/ftrordercancel" => gettext("Cancelled future order management for GTP"),
            //"/root/gtp/ftrordercancel/list" => gettext("View cancelled future order for GTP"),
            "/root/mbb/ftrorder" => gettext("Future order management for MBB"),
            "/root/mbb/ftrorder/list" => gettext("View future order for MBB"),
            "/root/mbb/ftrorder/add" => gettext("Add future order for MBB"),
            "/root/mbb/ftrorder/edit" => gettext("Edit future order for MBB"),
            "/root/mbb/ftrorder/cancel" => gettext("Cancel future order for MBB"),
            "/root/mbb/ftrorder/confirm" => gettext("Confirm future order for MBB"),
            "/root/mbb/ftrorder/export" => gettext("Export future order for MBB"),
            //"/root/mbb/ftrordercancel" => gettext("Cancelled future order management for MBB"),
            //"/root/mbb/ftrordercancel/list" => gettext("View cancelled future order for MBB"),	

            "/root/gtp/limits" => gettext("Daily Limit for partner"),
            "/root/gtp/partnerlimits" => gettext("View all Product Limits for partner (internal use only)"),

            "/root/gtp/sale" => gettext("Salesman Trading"),
            "/root/gtp/coretrades" => gettext("Enable All Corepartner for GTP Spot Order Special Trading"),
            //"/root/gtp/sale/trade" => gettext("Sales Trade"),
            //"/root/gtp/sale/list" => gettext("View sales trade"),
            //"/root/gtp/sale/edit" => gettext("Edit sales trade"),
            //"/root/gtp/sale/confirm" => gettext("Confirm sales trade"),

            "/root/gtp/cust" => gettext("Customer Trading"),
            //"/root/gtp/cust/trade" => gettext("Customer Trade"),
            //"/root/gtp/cust/list" => gettext("View Customer Trade"),
            //"/root/gtp/cust/edit" => gettext("Edit Customer Trade"),
            //"/root/gtp/cust/confirm" => gettext("Confirm Customer Trade"),

            "/root/gtp/collection" => gettext("Collection"),
            "/root/gtp/collection/list" => gettext("View Collection"),
            "/root/gtp/collection/add" => gettext("Add Collection"),
            "/root/gtp/collection/edit" => gettext("Edit Collection"),
            "/root/gtp/collection/confirm" => gettext("Confirm Collection"),
            "/root/gtp/collection/export" => gettext("Export Collection"),

            "/root/gtp/unfulfilledorder" => gettext("Unfulfilled PO"),
            //"/root/gtp/unfulfilledorder/list" => gettext("View Unfulfilled Order"),
            //"/root/gtp/unfulfilledorder/edit" => gettext("Edit unfulfilled order"),
            //"/root/gtp/unfulfilledorder/confirm" => gettext("Confirm unfulfilled order"),

           // "/root/gtp/salescommission" => gettext("Sales commission management"),
           // "/root/gtp/salescommission/list" => gettext("View sales commission"),
        
            "/root/gtp/logistic" => gettext("Logistics management for GTP"),
            "/root/gtp/logistic/list" => gettext("View logistics information for GTP"),
            "/root/gtp/logistic/add" => gettext("Add logistics for GTP"),
            "/root/gtp/logistic/edit" => gettext("Edit logistics for GTP"),
            "/root/gtp/logistic/delete" => gettext("Delete logistics for GTP"),
            "/root/gtp/logistic/complete" => gettext("Complete logistics for GTP"),

            "/root/mbb/buyback" => gettext("Buyback management for Mib"),
            "/root/mbb/buyback/list" => gettext("View buyback for Mib"),
            "/root/mbb/buyback/add" => gettext("Add buyback for Mib"),
            "/root/mbb/buyback/edit" => gettext("Edit buyback for Mib"),
            "/root/mbb/buyback/complete" => gettext("Complete buyback for Mib"),
            "/root/mbb/buyback/export" => gettext("Export buyback for Mib"),

            
            "/root/mbb/redemption" => gettext("Redemption management for Mib"),
            "/root/mbb/redemption/list" => gettext("View redemption for Mib"),
            "/root/mbb/redemption/add" => gettext("Add redemption for Mib"),
            "/root/mbb/redemption/edit" => gettext("Edit redemption for Mib"),
            "/root/mbb/redemption/complete" => gettext("Complete redemption for Mib"),
            "/root/mbb/redemption/export" => gettext("Export redemption for Mib"),
            "/root/mbb/replenishment" => gettext("Replenishment management for Mib"),
            "/root/mbb/replenishment/list" => gettext("View replenishment for Mib"),
            "/root/mbb/replenishment/add" => gettext("Add replenishment for Mib"),
            "/root/mbb/replenishment/edit" => gettext("Edit replenishment for Mib"),
            "/root/mbb/replenishment/complete" => gettext("Complete replenishment for Mib"),
            "/root/mbb/replenishment/export" => gettext("Export replenishment for Mib"),

            "/root/mbb/logistic" => gettext("Logistics management for Mib"),
            "/root/mbb/logistic/list" => gettext("View logistics information for Mib"),
            "/root/mbb/logistic/add" => gettext("Add logistics for Mib"),
            "/root/mbb/logistic/edit" => gettext("Edit logistics for Mib"),
            "/root/mbb/logistic/delete" => gettext("Delete logistics for Mib"),
            "/root/mbb/logistic/complete" => gettext("Complete logistics for Mib"),

            "/root/mbb/vault" => gettext("Bank vault management"),
            "/root/mbb/vault/list" => gettext("View item"),
            "/root/mbb/vault/add" => gettext("Add item"),
            "/root/mbb/vault/edit" => gettext("Edit item"),
            "/root/mbb/vault/return" => gettext("Return item"),
            "/root/mbb/vault/transfer" => gettext("Transfer item"),

            "/root/mbb/vault/request" => gettext("Request item"), // maker
            "/root/mbb/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            "/root/mbb/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
            
            "/root/mbb/anppool" => gettext("A&P Pool"),
            "/root/mbb/anppool/list" => gettext("View A&P Pool"),
            "/root/mbb/anppool/export" => gettext("Export A&P Pool"),
           
            "/root/mbb/goldbarstatus/" => gettext("Gold Bar Status"),
            "/root/mbb/goldbarstatus/list" => gettext("View Gold Bar Status"),

            "/root/mbb/sale" => gettext("Salesman Trading for MIB"),

            "/root/pos" => gettext("POS Modules"),
            "/root/pos/buyback" => gettext("Buyback management for Pos"),
            "/root/pos/buyback/list" => gettext("View buyback for Pos"),
            "/root/pos/buyback/add" => gettext("Add buyback for Pos"),
            "/root/pos/buyback/edit" => gettext("Edit buyback for Pos"),
            "/root/pos/buyback/complete" => gettext("Complete buyback for Pos"),
            "/root/pos/buyback/export" => gettext("Export buyback for Pos"),
            "/root/pos/tender" => gettext("Tender management for Pos"),
            "/root/pos/tender/list" => gettext("View tender for Pos"),
            "/root/pos/tender/add" => gettext("Add tender for Pos"),
            "/root/pos/tender/edit" => gettext("Edit tender for Pos"),
            "/root/pos/tender/complete" => gettext("Complete tender for Pos"),
            "/root/pos/tender/export" => gettext("Export tender for Pos"),
            "/root/pos/tender/import" => gettext("Import tender for Pos"),
            "/root/pos/tender/upload" => gettext("Upload tender for Pos"),
            "/root/pos/collection" => gettext("Collection management for Pos"),
            "/root/pos/collection/list" => gettext("View collection for Pos"),
            "/root/pos/collection/add" => gettext("Add collection for Pos"),
            "/root/pos/collection/edit" => gettext("Edit collection for Pos"),
            "/root/pos/collection/complete" => gettext("Complete collection for Pos"),
            "/root/pos/collection/export" => gettext("Export collection for Pos"),

            // add tekun
            "/root/tekun" => gettext("Tekun Modules"),
            "/root/tekun/buyback" => gettext("Buyback management for Tekun"),
            "/root/tekun/buyback/list" => gettext("View buyback for Tekun"),
            "/root/tekun/buyback/add" => gettext("Add buyback for Tekun"),
            "/root/tekun/buyback/edit" => gettext("Edit buyback for Tekun"),
            "/root/tekun/buyback/complete" => gettext("Complete buyback for Tekun"),
            "/root/tekun/buyback/export" => gettext("Export buyback for Tekun"),
            "/root/tekun/tender" => gettext("Tender management for Tekun"),
            "/root/tekun/tender/list" => gettext("View tender for Tekun"),
            "/root/tekun/tender/add" => gettext("Add tender for Tekun"),
            "/root/tekun/tender/edit" => gettext("Edit tender for Tekun"),
            "/root/tekun/tender/complete" => gettext("Complete tender for Tekun"),
            "/root/tekun/tender/export" => gettext("Export tender for Tekun"),
            "/root/tekun/tender/import" => gettext("Import tender for Tekun"),
            "/root/tekun/tender/upload" => gettext("Upload tender for Tekun"),
            "/root/tekun/collection" => gettext("Collection management for Tekun"),
            "/root/tekun/collection/list" => gettext("View collection for Tekun"),
            "/root/tekun/collection/add" => gettext("Add collection for Tekun"),
            "/root/tekun/collection/edit" => gettext("Edit collection for Tekun"),
            "/root/tekun/collection/complete" => gettext("Complete collection for Tekun"),
            "/root/tekun/collection/export" => gettext("Export collection for Tekun"),

            // add koponas

            "/root/koponas/buyback" => gettext("Buyback management for Koponas"),
            "/root/koponas/buyback/list" => gettext("View buyback for Koponas"),
            "/root/koponas/buyback/add" => gettext("Add buyback for Koponas"),
            "/root/koponas/buyback/edit" => gettext("Edit buyback for Koponas"),
            "/root/koponas/buyback/complete" => gettext("Complete buyback for Koponas"),
            "/root/koponas/buyback/export" => gettext("Export buyback for Koponas"),
            "/root/koponas/tender" => gettext("Tender management for Koponas"),
            "/root/koponas/tender/list" => gettext("View tender for Koponas"),
            "/root/koponas/tender/add" => gettext("Add tender for Koponas"),
            "/root/koponas/tender/edit" => gettext("Edit tender for Koponas"),
            "/root/koponas/tender/complete" => gettext("Complete tender for Koponas"),
            "/root/koponas/tender/export" => gettext("Export tender for Koponas"),
            "/root/koponas/tender/import" => gettext("Import tender for Koponas"),
            "/root/koponas/tender/upload" => gettext("Upload tender for Koponas"),
            "/root/koponas/collection" => gettext("Collection management for Koponas"),
            "/root/koponas/collection/list" => gettext("View collection for Koponas"),
            "/root/koponas/collection/add" => gettext("Add collection for Koponas"),
            "/root/koponas/collection/edit" => gettext("Edit collection for Koponas"),
            "/root/koponas/collection/complete" => gettext("Complete collection for Koponas"),
            "/root/koponas/collection/export" => gettext("Export collection for Koponas"),
            
            // add sahabat
            "/root/sahabat" => gettext("Sahabat Modules"),
            "/root/sahabat/buyback" => gettext("Buyback management for Sahabat"),
            "/root/sahabat/buyback/list" => gettext("View buyback for Sahabat"),
            "/root/sahabat/buyback/add" => gettext("Add buyback for Sahabat"),
            "/root/sahabat/buyback/edit" => gettext("Edit buyback for Sahabat"),
            "/root/sahabat/buyback/complete" => gettext("Complete buyback for Sahabat"),
            "/root/sahabat/buyback/export" => gettext("Export buyback for Sahabat"),
            "/root/sahabat/tender" => gettext("Tender management for Sahabat"),
            "/root/sahabat/tender/list" => gettext("View tender for Sahabat"),
            "/root/sahabat/tender/add" => gettext("Add tender for Sahabat"),
            "/root/sahabat/tender/edit" => gettext("Edit tender for Sahabat"),
            "/root/sahabat/tender/complete" => gettext("Complete tender for Sahabat"),
            "/root/sahabat/tender/export" => gettext("Export tender for Sahabat"),
            "/root/sahabat/tender/import" => gettext("Import tender for Sahabat"),
            "/root/sahabat/tender/upload" => gettext("Upload tender for Sahabat"),
            "/root/sahabat/collection" => gettext("Collection management for Sahabat"),
            "/root/sahabat/collection/list" => gettext("View collection for Sahabat"),
            "/root/sahabat/collection/add" => gettext("Add collection for Sahabat"),
            "/root/sahabat/collection/edit" => gettext("Edit collection for Sahabat"),
            "/root/sahabat/collection/complete" => gettext("Complete collection for Sahabat"),
            "/root/sahabat/collection/export" => gettext("Export collection for Sahabat"),

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
            "/root/mbb/apfund" => gettext("MBB AP Fund pool management"),
            "/root/mbb/apfund/list" => gettext("View MBB AP Fund pool management"),

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

            "/root/common" => gettext("Common Modules"),
            // "/root/common/vault" => gettext("Common Vault Functions"),
            // "/root/common/vault/list" => gettext("View Common DGV"),
            // "/root/common/dgv" => gettext("Common DGV"),
            // "/root/common/dgv/list" => gettext("View Common DGV"),
            "/root/common/vault" => gettext("Common DGV Vault"),
            "/root/common/vault/list" => gettext("View Common DGV Vault"),
            // "/root/common/vault/approve" => gettext("Approve Activation for Common DGV Vault"),
            "/root/common/vault/transfer" => gettext("Allow Transfer for Common DGV Vault"),
            "/root/common/vault/return" => gettext("Allow Return for Common DGV Vault"),
            "/root/common/vault/request" => gettext("Request item"), // maker
            "/root/common/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // "/root/common/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            "/root/common/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            
            "/root/bmmb/mintedbar" => gettext("Minted warehouse management for BMMB"),
            "/root/bmmb/mintedbar/list" => gettext("View minted warehouse"),
            // extend mygtp on gtpcontroller for some bo config controller diff
            "/root/bmmb" => gettext("BMMB Modules"),
            "/root/go" => gettext("GO Modules"),
            "/root/one" => gettext("ONECENT Modules"),
            "/root/onecall" => gettext("ONECALL Modules"),
            "/root/air" => gettext("AIR Modules"),
            "/root/mcash" => gettext("MCASH Modules"),
            "/root/toyyib" => gettext("TOYYIB Modules"),
            "/root/hope" => gettext("HOPE Modules"),
            "/root/mbsb" => gettext("MBSB Modules"),
            "/root/nubex" => gettext("NUBEX Modules"),
            "/root/red" => gettext("REDGOLD Modules"),
            "/root/igold" => gettext("IGOLD Modules"),
            "/root/ktp" => gettext("PKB Modules"),
            "/root/bursa" => gettext("BURSA Modules"),
            "/root/bsn" => gettext("BSN Modules"),

            
            "/root/ktp" => gettext("PKB Modules"),
            "/root/kodimas" => gettext("Kodimas Modules"),
            "/root/kgoldaffi" => gettext("Kodimas Affiliate Modules"),
            "/root/koponas" => gettext("Koponas Modules"),
            "/root/waqaf" => gettext("Waqaf Modules"),
            "/root/kasih" => gettext("Kasih Modules"),
            "/root/noor" => gettext("Noor Modules"),
            "/root/posarrahnu" => gettext("Posarrahnu Modules"),

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
            "/root/bmmb/profile/activate" => gettext("Activate account holder"),

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


            "/root/bmmb/vault" => gettext("Inventory management"),
            "/root/bmmb/vault/list" => gettext("View inventory"),
            "/root/bmmb/vault/add" => gettext("Add item to inventory"),
            "/root/bmmb/vault/edit" => gettext("Edit item in inventory"),
            "/root/bmmb/vault/return" => gettext("Return item"),
            "/root/bmmb/vault/transfer" => gettext("Transfer item"),

            "/root/bmmb/vault/request" => gettext("Request item"), // maker
            "/root/bmmb/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            "/root/bmmb/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/bmmb/report" => gettext("Reporting management"),
            "/root/bmmb/report/commission/list" => gettext("Commission Reporting"),
            "/root/bmmb/report/storagefee/list" => gettext("Storage Fee Reporting"),
            "/root/bmmb/report/adminfee/list" => gettext("Admin Fee Reporting"),
            "/root/bmmb/report/monthlysummary/list" => gettext("Monthly Transaction Summary Reporting"),
            "/root/bmmb/report/registration" => gettext("Failed Registration Reporting"),
            "/root/bmmb/report/registration/list" => gettext("View Failed Registration Reporting"),

            "/root/bmmb/accountclosure" => gettext("Account closure management"),
            "/root/bmmb/accountclosure/list" => gettext("View account closure"),
            "/root/bmmb/accountclosure/close" => gettext("Close account"),

            "/root/bmmb/pricealert" => gettext("Price alert management"),
            "/root/bmmb/pricealert/list" => gettext("View price alert"),


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
            "/root/go/profile/activate" => gettext("Activate account holder"),

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

            // "/root/go/vault/request" => gettext("Request item"), // maker
            // "/root/go/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/go/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)


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
            "/root/go/accountclosure/close" => gettext("Close account"),

            "/root/go/pricealert" => gettext("Price alert management"),
            "/root/go/pricealert/list" => gettext("View price alert"),

            "/root/go/sale" => gettext("Spot Order Special for GOPAYZ"),

            "/root/go/transfergold" => gettext("Transfer Gold for GOPAYZ"),
            "/root/go/transfergold/list" => gettext("View Transfer Gold"),
            "/root/go/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/go/promo" => gettext("Promo for GOPAYZ"),
            "/root/go/promo/list" => gettext("View Promo"),
            "/root/go/promo/export" => gettext("Export Promo"),
            // End GO permissions

            // One Permissions
            "/root/one/redemption" => gettext("Conversion management"),
            "/root/one/redemption/list" => gettext("View conversion"),
            "/root/one/redemption/add" => gettext("Add conversion"),
            "/root/one/redemption/edit" => gettext("Edit conversion"),
            "/root/one/redemption/export" => gettext("Export conversion"),

            "/root/one/logistic" => gettext("Logistics management for ONE"),
            "/root/one/logistic/list" => gettext("View logistics information for ONE"),
            "/root/one/logistic/add" => gettext("Add logistics for ONE"),
            "/root/one/logistic/edit" => gettext("Edit logistics for ONE"),
            "/root/one/logistic/delete" => gettext("Delete logistics for ONE"),
            "/root/one/logistic/complete" => gettext("Complete logistics for ONE"),

            "/root/one/disbursement" => gettext("Disbursement management"),
            "/root/one/disbursement/list" => gettext("View disbursement"),
            "/root/one/disbursement/add" => gettext("Add disbursement"),
            "/root/one/disbursement/edit" => gettext("Edit disbursement"),

            "/root/one/profile" => gettext("Account holder profile management"),
            "/root/one/profile/list" => gettext("View user profile"),
            "/root/one/profile/add" => gettext("Add user profile"),
            "/root/one/profile/edit" => gettext("Edit user profile"),
            "/root/one/profile/suspend" => gettext("Suspend account holder"),
            "/root/one/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/one/profile/activate" => gettext("Activate account holder"),

            "/root/one/approval" => gettext("PEP approval management"),
            "/root/one/approval/list" => gettext("View PEP"),
            "/root/one/approval/approve" => gettext("Approve or reject PEP"),

            "/root/one/fpx" => gettext("FPX management"),
            "/root/one/fpx/list" => gettext("View FPX"),

            "/root/one/ekyc" => gettext("eKYC management"),
            "/root/one/ekyc/list" => gettext("View eKYC"),

            "/root/one/goldtransaction" => gettext("Gold transaction management"),
            "/root/one/goldtransaction/list" => gettext("View gold transaction"),
            "/root/one/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/one/storagefee" => gettext("Storage Fee management"),
            "/root/one/storagefee/list" => gettext("View storage fee"),
            "/root/one/fee" => gettext("Fee management"),
            "/root/one/fee/list" => gettext("View fee management"),
            "/root/one/fee/add" => gettext("Add fee"),
            "/root/one/fee/edit" => gettext("Edit fee"),


            "/root/one/vault" => gettext("Inventory management"),
            "/root/one/vault/list" => gettext("View inventory"),
            "/root/one/vault/add" => gettext("Add item to inventory"),
            "/root/one/vault/edit" => gettext("Edit item in inventory"),
            "/root/one/vault/return" => gettext("Return item"),
            "/root/one/vault/transfer" => gettext("Transfer item"),

            // "/root/one/vault/request" => gettext("Request item"), // maker
            // "/root/one/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/one/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)


            "/root/one/report" => gettext("Reporting management for ONECENT"),
            "/root/one/report/commission" => gettext("Commission Reporting"),
            "/root/one/report/commission/list" => gettext("View Commission Reporting"),
            "/root/one/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/one/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/one/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/one/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/one/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/one/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/one/accountclosure" => gettext("Account closure management"),
            "/root/one/accountclosure/list" => gettext("View account closure"),
            "/root/one/accountclosure/close" => gettext("Close account"),

            "/root/one/pricealert" => gettext("Price alert management"),
            "/root/one/pricealert/list" => gettext("View price alert"),

            "/root/one/sale" => gettext("Spot Order Special for ONECENT"),

            "/root/one/transfergold" => gettext("Transfer Gold for ONECENT"),
            "/root/one/transfergold/list" => gettext("View Transfer Gold"),
            "/root/one/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/one/promo" => gettext("Promo for ONECENT"),
            "/root/one/promo/list" => gettext("View Promo"),
            "/root/one/promo/export" => gettext("Export Promo"),
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
            "/root/onecall/profile/activate" => gettext("Activate account holder"),

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

            // "/root/onecall/vault/request" => gettext("Request item"), // maker
            // "/root/onecall/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/onecall/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/onecall/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            
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
            "/root/onecall/accountclosure/close" => gettext("Close account"),

            "/root/onecall/pricealert" => gettext("Price alert management for ONECALL"),
            "/root/onecall/pricealert/list" => gettext("View price alert"),

            "/root/onecall/sale" => gettext("Spot Order Special for ONECALL"),

            "/root/onecall/transfergold" => gettext("Transfer Gold for ONECALL"),
            "/root/onecall/transfergold/list" => gettext("View Transfer Gold"),
            "/root/onecall/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/onecall/promo" => gettext("Promo for ONECALL"),
            "/root/onecall/promo/list" => gettext("View Promo"),
            "/root/onecall/promo/export" => gettext("Export Promo"),
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
             "/root/air/profile/activate" => gettext("Activate account holder"),

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

            //  "/root/air/vault/request" => gettext("Request item"), // maker
            //  "/root/air/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //  // "/root/air/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //  "/root/air/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

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
             "/root/air/accountclosure/close" => gettext("Close account"),

             "/root/air/pricealert" => gettext("Price alert management for AIRGOLD"),
             "/root/air/pricealert/list" => gettext("View price alert"),

             "/root/air/sale" => gettext("Spot Order Special for AIRGOLD"),

             "/root/air/transfergold" => gettext("Transfer Gold for AIRGOLD"),
             "/root/air/transfergold/list" => gettext("View Transfer Gold"),
             "/root/air/transfergold/export" => gettext("Export Transfer Gold"),

             "/root/air/promo" => gettext("Promo for AIRGOLD"),
             "/root/air/promo/list" => gettext("View Promo"),
             "/root/air/promo/export" => gettext("Export Promo"),
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
              "/root/mcash/profile/activate" => gettext("Activate account holder"),

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
              "/root/mcash/fee/list" => gettext("View fee management"),
              "/root/mcash/fee/add" => gettext("Add fee"),
              "/root/mcash/fee/edit" => gettext("Edit fee"),


              "/root/mcash/vault" => gettext("Inventory management for MCASH"),
              "/root/mcash/vault/list" => gettext("View inventory"),
              "/root/mcash/vault/add" => gettext("Add item to inventory"),
              "/root/mcash/vault/edit" => gettext("Edit item in inventory"),
              "/root/mcash/vault/return" => gettext("Return item"),
              "/root/mcash/vault/transfer" => gettext("Transfer item"),

            //   "/root/mcash/vault/request" => gettext("Request item"), // maker
            //   "/root/mcash/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //   // "/root/mcash/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //   "/root/mcash/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

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
              "/root/mcash/accountclosure/close" => gettext("Close account"),

              "/root/mcash/pricealert" => gettext("Price alert management for MCASH"),
              "/root/mcash/pricealert/list" => gettext("View price alert"),

              "/root/mcash/sale" => gettext("Spot Order Special for MCASH"),

              "/root/mcash/transfergold" => gettext("Transfer Gold for MCASH"),
              "/root/mcash/transfergold/list" => gettext("View Transfer Gold"),
              "/root/mcash/transfergold/export" => gettext("Export Transfer Gold"),

              "/root/mcash/promo" => gettext("Promo for MCASH"),
              "/root/mcash/promo/list" => gettext("View Promo"),
              "/root/mcash/promo/export" => gettext("Export Promo"),
              // End MCASH Permissions
              

               // TOYYIB Permissions
               "/root/toyyib/redemption" => gettext("Conversion management for TOYYIB"),
               "/root/toyyib/redemption/list" => gettext("View conversion"),
               "/root/toyyib/redemption/add" => gettext("Add conversion"),
               "/root/toyyib/redemption/edit" => gettext("Edit conversion"),
               "/root/toyyib/redemption/export" => gettext("Export conversion"),
 
               "/root/toyyib/logistic" => gettext("Logistics management for TOYYIB"),
               "/root/toyyib/logistic/list" => gettext("View logistics information"),
               "/root/toyyib/logistic/add" => gettext("Add logistics"),
               "/root/toyyib/logistic/edit" => gettext("Edit logistics"),
               "/root/toyyib/logistic/delete" => gettext("Delete logistics"),
               "/root/toyyib/logistic/complete" => gettext("Complete logistics"),
 
               "/root/toyyib/disbursement" => gettext("Disbursement management for TOYYIB"),
               "/root/toyyib/disbursement/list" => gettext("View disbursement"),
               "/root/toyyib/disbursement/add" => gettext("Add disbursement"),
               "/root/toyyib/disbursement/edit" => gettext("Edit disbursement"),
 
               "/root/toyyib/profile" => gettext("Account holder profile management for TOYYIB"),
               "/root/toyyib/profile/list" => gettext("View user profile"),
               "/root/toyyib/profile/add" => gettext("Add user profile"),
               "/root/toyyib/profile/edit" => gettext("Edit user profile"),
               "/root/toyyib/profile/suspend" => gettext("Suspend account holder"),
               "/root/toyyib/profile/unsuspend" => gettext("Unsuspend account holder"),
               "/root/toyyib/profile/activate" => gettext("Activate account holder"),

               "/root/toyyib/approval" => gettext("PEP approval management for TOYYIB"),
               "/root/toyyib/approval/list" => gettext("View PEP"),
               "/root/toyyib/approval/approve" => gettext("Approve or reject PEP"),
 
               "/root/toyyib/fpx" => gettext("FPX management for TOYYIB"),
               "/root/toyyib/fpx/list" => gettext("View FPX"),
 
               "/root/toyyib/ekyc" => gettext("eKYC management for TOYYIB"),
               "/root/toyyib/ekyc/list" => gettext("View eKYC"),
 
               "/root/toyyib/goldtransaction" => gettext("Gold transaction management for TOYYIB"),
               "/root/toyyib/goldtransaction/list" => gettext("View gold transaction"),
               "/root/toyyib/goldtransaction/export" => gettext("Export gold transaction statement"),
 
               "/root/toyyib/storagefee" => gettext("Storage Fee management for TOYYIB"),
               "/root/toyyib/storagefee/list" => gettext("View storage fee"),
               "/root/toyyib/fee" => gettext("Fee management"),
               "/root/toyyib/fee/list" => gettext("View fee management"),
               "/root/toyyib/fee/add" => gettext("Add fee"),
               "/root/toyyib/fee/edit" => gettext("Edit fee"),
 
 
               "/root/toyyib/vault" => gettext("Inventory management for TOYYIB"),
               "/root/toyyib/vault/list" => gettext("View inventory"),
               "/root/toyyib/vault/add" => gettext("Add item to inventory"),
               "/root/toyyib/vault/edit" => gettext("Edit item in inventory"),
               "/root/toyyib/vault/return" => gettext("Return item"),
               "/root/toyyib/vault/transfer" => gettext("Transfer item"),
 
             //   "/root/toyyib/vault/request" => gettext("Request item"), // maker
             //   "/root/toyyib/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
             //   // "/root/toyyib/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
             //   "/root/toyyib/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
               "/root/toyyib/report" => gettext("Reporting management for TOYYIB"),
               "/root/toyyib/report/commission" => gettext("Commission Reporting"),
               "/root/toyyib/report/commission/list" => gettext("View Commission Reporting"),
               "/root/toyyib/report/storagefee" => gettext("Storage Fee Reporting"),
               "/root/toyyib/report/storagefee/list" => gettext("View Storage Fee Reporting"),
               "/root/toyyib/report/adminfee" => gettext("Admin Fee Reporting"),
               "/root/toyyib/report/adminfee/list" => gettext("View Admin Fee Reporting"),
               "/root/toyyib/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
               "/root/toyyib/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
 
               "/root/toyyib/accountclosure" => gettext("Account closure management for TOYYIB"),
               "/root/toyyib/accountclosure/list" => gettext("View account closure"),
               "/root/toyyib/accountclosure/close" => gettext("Close account"),

                "/root/toyyib/pricealert" => gettext("Price alert management for TOYYIB"),
                "/root/toyyib/pricealert/list" => gettext("View price alert"),
    
                "/root/toyyib/sale" => gettext("Spot Order Special for TOYYIB"),

                "/root/toyyib/transfergold" => gettext("Transfer Gold for TOYYIB"),
                "/root/toyyib/transfergold/list" => gettext("View Transfer Gold"),
                "/root/toyyib/transfergold/export" => gettext("Export Transfer Gold"),

                "/root/toyyib/promo" => gettext("Promo for TOYYIB"),
                "/root/toyyib/promo/list" => gettext("View Promo"),
                "/root/toyyib/promo/export" => gettext("Export Promo"),
                // End TOYYIB Permissions

             // HOPE Permissions
             "/root/hope/redemption" => gettext("Conversion management for HOPE"),
             "/root/hope/redemption/list" => gettext("View conversion"),
             "/root/hope/redemption/add" => gettext("Add conversion"),
             "/root/hope/redemption/edit" => gettext("Edit conversion"),
             "/root/hope/redemption/export" => gettext("Export conversion"),

             "/root/hope/logistic" => gettext("Logistics management for HOPE"),
             "/root/hope/logistic/list" => gettext("View logistics information"),
             "/root/hope/logistic/add" => gettext("Add logistics"),
             "/root/hope/logistic/edit" => gettext("Edit logistics"),
             "/root/hope/logistic/delete" => gettext("Delete logistics"),
             "/root/hope/logistic/complete" => gettext("Complete logistics"),

             "/root/hope/disbursement" => gettext("Disbursement management for HOPE"),
             "/root/hope/disbursement/list" => gettext("View disbursement"),
             "/root/hope/disbursement/add" => gettext("Add disbursement"),
             "/root/hope/disbursement/edit" => gettext("Edit disbursement"),

             "/root/hope/profile" => gettext("Account holder profile management for HOPE"),
             "/root/hope/profile/list" => gettext("View user profile"),
             "/root/hope/profile/add" => gettext("Add user profile"),
             "/root/hope/profile/edit" => gettext("Edit user profile"),
             "/root/hope/profile/suspend" => gettext("Suspend account holder"),
             "/root/hope/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/hope/profile/activate" => gettext("Activate account holder"),

             "/root/hope/approval" => gettext("PEP approval management for HOPE"),
             "/root/hope/approval/list" => gettext("View PEP"),
             "/root/hope/approval/approve" => gettext("Approve or reject PEP"),

             "/root/hope/fpx" => gettext("FPX management for HOPE"),
             "/root/hope/fpx/list" => gettext("View FPX"),

             "/root/hope/ekyc" => gettext("eKYC management for HOPE"),
             "/root/hope/ekyc/list" => gettext("View eKYC"),

             "/root/hope/goldtransaction" => gettext("Gold transaction management for HOPE"),
             "/root/hope/goldtransaction/list" => gettext("View gold transaction"),
             "/root/hope/goldtransaction/export" => gettext("Export gold transaction statement"),

             "/root/hope/storagefee" => gettext("Storage Fee management for HOPE"),
             "/root/hope/storagefee/list" => gettext("View storage fee"),
             "/root/hope/fee" => gettext("Fee management"),
             "/root/hope/fee/list" => gettext("View fee management"),
             "/root/hope/fee/add" => gettext("Add fee"),
             "/root/hope/fee/edit" => gettext("Edit fee"),


             "/root/hope/vault" => gettext("Inventory management for HOPE"),
             "/root/hope/vault/list" => gettext("View inventory"),
             "/root/hope/vault/add" => gettext("Add item to inventory"),
             "/root/hope/vault/edit" => gettext("Edit item in inventory"),
             "/root/hope/vault/return" => gettext("Return item"),
             "/root/hope/vault/transfer" => gettext("Transfer item"),

           //   "/root/hope/vault/request" => gettext("Request item"), // maker
           //   "/root/hope/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
           //   // "/root/hope/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
           //   "/root/hope/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

             "/root/hope/report" => gettext("Reporting management for HOPE"),
             "/root/hope/report/commission" => gettext("Commission Reporting"),
             "/root/hope/report/commission/list" => gettext("View Commission Reporting"),
             "/root/hope/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/hope/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/hope/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/hope/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/hope/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/hope/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


             "/root/hope/accountclosure" => gettext("Account closure management for HOPE"),
             "/root/hope/accountclosure/list" => gettext("View account closure"),
             "/root/hope/accountclosure/close" => gettext("Close account"),

            "/root/hope/pricealert" => gettext("Price alert management for HOPE"),
            "/root/hope/pricealert/list" => gettext("View price alert"),

            "/root/hope/sale" => gettext("Spot Order Special for HOPE"),

            "/root/hope/transfergold" => gettext("Transfer Gold for HOPE"),
            "/root/hope/transfergold/list" => gettext("View Transfer Gold"),
            "/root/hope/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/hope/promo" => gettext("Promo for HOPE"),
            "/root/hope/promo/list" => gettext("View Promo"),
            "/root/hope/promo/export" => gettext("Export Promo"),
            // End hope Permissions

             // MBSB Permissions
             "/root/mbsb/redemption" => gettext("Conversion management for MBSB"),
             "/root/mbsb/redemption/list" => gettext("View conversion"),
             "/root/mbsb/redemption/add" => gettext("Add conversion"),
             "/root/mbsb/redemption/edit" => gettext("Edit conversion"),
             "/root/mbsb/redemption/export" => gettext("Export conversion"),

             "/root/mbsb/logistic" => gettext("Logistics management for MBSB"),
             "/root/mbsb/logistic/list" => gettext("View logistics information"),
             "/root/mbsb/logistic/add" => gettext("Add logistics"),
             "/root/mbsb/logistic/edit" => gettext("Edit logistics"),
             "/root/mbsb/logistic/delete" => gettext("Delete logistics"),
             "/root/mbsb/logistic/complete" => gettext("Complete logistics"),

             "/root/mbsb/disbursement" => gettext("Disbursement management for MBSB"),
             "/root/mbsb/disbursement/list" => gettext("View disbursement"),
             "/root/mbsb/disbursement/add" => gettext("Add disbursement"),
             "/root/mbsb/disbursement/edit" => gettext("Edit disbursement"),

             "/root/mbsb/profile" => gettext("Account holder profile management for MBSB"),
             "/root/mbsb/profile/list" => gettext("View user profile"),
             "/root/mbsb/profile/add" => gettext("Add user profile"),
             "/root/mbsb/profile/edit" => gettext("Edit user profile"),
             "/root/mbsb/profile/suspend" => gettext("Suspend account holder"),
             "/root/mbsb/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/mbsb/profile/activate" => gettext("Activate account holder"),

             "/root/mbsb/approval" => gettext("PEP approval management for MBSB"),
             "/root/mbsb/approval/list" => gettext("View PEP"),
             "/root/mbsb/approval/approve" => gettext("Approve or reject PEP"),

             "/root/mbsb/fpx" => gettext("FPX management for MBSB"),
             "/root/mbsb/fpx/list" => gettext("View FPX"),

             "/root/mbsb/ekyc" => gettext("eKYC management for MBSB"),
             "/root/mbsb/ekyc/list" => gettext("View eKYC"),

             "/root/mbsb/goldtransaction" => gettext("Gold transaction management for MBSB"),
             "/root/mbsb/goldtransaction/list" => gettext("View gold transaction"),
             "/root/mbsb/goldtransaction/export" => gettext("Export gold transaction statement"),

             "/root/mbsb/storagefee" => gettext("Storage Fee management for MBSB"),
             "/root/mbsb/storagefee/list" => gettext("View storage fee"),
             "/root/mbsb/fee" => gettext("Fee management"),
             "/root/mbsb/fee/list" => gettext("View fee management"),
             "/root/mbsb/fee/add" => gettext("Add fee"),
             "/root/mbsb/fee/edit" => gettext("Edit fee"),


             "/root/mbsb/vault" => gettext("Inventory management for MBSB"),
             "/root/mbsb/vault/list" => gettext("View inventory"),
             "/root/mbsb/vault/add" => gettext("Add item to inventory"),
             "/root/mbsb/vault/edit" => gettext("Edit item in inventory"),
             "/root/mbsb/vault/return" => gettext("Return item"),
             "/root/mbsb/vault/transfer" => gettext("Transfer item"),

           //   "/root/mbsb/vault/request" => gettext("Request item"), // maker
           //   "/root/mbsb/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
           //   // "/root/mbsb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
           //   "/root/mbsb/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

             "/root/mbsb/report" => gettext("Reporting management for MBSB"),
             "/root/mbsb/report/commission" => gettext("Commission Reporting"),
             "/root/mbsb/report/commission/list" => gettext("View Commission Reporting"),
             "/root/mbsb/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/mbsb/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/mbsb/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/mbsb/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/mbsb/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/mbsb/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


             "/root/mbsb/accountclosure" => gettext("Account closure management for MBSB"),
             "/root/mbsb/accountclosure/list" => gettext("View account closure"),
             "/root/mbsb/accountclosure/close" => gettext("Close account"),

            "/root/mbsb/pricealert" => gettext("Price alert management for MBSB"),
            "/root/mbsb/pricealert/list" => gettext("View price alert"),

            "/root/mbsb/sale" => gettext("Spot Order Special for MBSB"),

            "/root/mbsb/transfergold" => gettext("Transfer Gold for MBSB"),
            "/root/mbsb/transfergold/list" => gettext("View Transfer Gold"),
            "/root/mbsb/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/mbsb/promo" => gettext("Promo for MBSB"),
            "/root/mbsb/promo/list" => gettext("View Promo"),
            "/root/mbsb/promo/export" => gettext("Export Promo"),
            // End MBSB Permissions

              // RED Permissions
              "/root/red/redemption" => gettext("Conversion management for REDGOLD"),
              "/root/red/redemption/list" => gettext("View conversion"),
              "/root/red/redemption/add" => gettext("Add conversion"),
              "/root/red/redemption/edit" => gettext("Edit conversion"),
              "/root/red/redemption/export" => gettext("Export conversion"),
 
              "/root/red/logistic" => gettext("Logistics management for REDGOLD"),
              "/root/red/logistic/list" => gettext("View logistics information"),
              "/root/red/logistic/add" => gettext("Add logistics"),
              "/root/red/logistic/edit" => gettext("Edit logistics"),
              "/root/red/logistic/delete" => gettext("Delete logistics"),
              "/root/red/logistic/complete" => gettext("Complete logistics"),
 
              "/root/red/disbursement" => gettext("Disbursement management for REDGOLD"),
              "/root/red/disbursement/list" => gettext("View disbursement"),
              "/root/red/disbursement/add" => gettext("Add disbursement"),
              "/root/red/disbursement/edit" => gettext("Edit disbursement"),
 
              "/root/red/profile" => gettext("Account holder profile management for REDGOLD"),
              "/root/red/profile/list" => gettext("View user profile"),
              "/root/red/profile/add" => gettext("Add user profile"),
              "/root/red/profile/edit" => gettext("Edit user profile"),
              "/root/red/profile/suspend" => gettext("Suspend account holder"),
              "/root/red/profile/unsuspend" => gettext("Unsuspend account holder"),
              "/root/red/profile/activate" => gettext("Activate account holder"),

              "/root/red/approval" => gettext("PEP approval management for REDGOLD"),
              "/root/red/approval/list" => gettext("View PEP"),
              "/root/red/approval/approve" => gettext("Approve or reject PEP"),
 
              "/root/red/fpx" => gettext("FPX management for REDGOLD"),
              "/root/red/fpx/list" => gettext("View FPX"),
 
              "/root/red/ekyc" => gettext("eKYC management for REDGOLD"),
              "/root/red/ekyc/list" => gettext("View eKYC"),
 
              "/root/red/goldtransaction" => gettext("Gold transaction management for REDGOLD"),
              "/root/red/goldtransaction/list" => gettext("View gold transaction"),
              "/root/red/goldtransaction/export" => gettext("Export gold transaction statement"),
 
              "/root/red/storagefee" => gettext("Storage Fee management for REDGOLD"),
              "/root/red/storagefee/list" => gettext("View storage fee"),
              "/root/red/fee" => gettext("Fee management"),
              "/root/red/fee/list" => gettext("View fee management"),
              "/root/red/fee/add" => gettext("Add fee"),
              "/root/red/fee/edit" => gettext("Edit fee"),
 
 
              "/root/red/vault" => gettext("Inventory management for REDGOLD"),
              "/root/red/vault/list" => gettext("View inventory"),
              "/root/red/vault/add" => gettext("Add item to inventory"),
              "/root/red/vault/edit" => gettext("Edit item in inventory"),
              "/root/red/vault/return" => gettext("Return item"),
              "/root/red/vault/transfer" => gettext("Transfer item"),
 
            //   "/root/red/vault/request" => gettext("Request item"), // maker
            //   "/root/red/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //   // "/root/red/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //   "/root/red/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
              "/root/red/report" => gettext("Reporting management for REDGOLD"),
              "/root/red/report/commission" => gettext("Commission Reporting"),
              "/root/red/report/commission/list" => gettext("View Commission Reporting"),
              "/root/red/report/storagefee" => gettext("Storage Fee Reporting"),
              "/root/red/report/storagefee/list" => gettext("View Storage Fee Reporting"),
              "/root/red/report/adminfee" => gettext("Admin Fee Reporting"),
              "/root/red/report/adminfee/list" => gettext("View Admin Fee Reporting"),
              "/root/red/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
              "/root/red/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
 
              "/root/red/accountclosure" => gettext("Account closure management for REDGOLD"),
              "/root/red/accountclosure/list" => gettext("View account closure"),
              "/root/red/accountclosure/close" => gettext("Close account"),
              
             "/root/red/pricealert" => gettext("Price alert management for REDGOLD"),
             "/root/red/pricealert/list" => gettext("View price alert"),
 
             "/root/red/sale" => gettext("Spot Order Special for REDGOLD"),

             "/root/red/transfergold" => gettext("Transfer Gold for REDGOLD"),
             "/root/red/transfergold/list" => gettext("View Transfer Gold"),
             "/root/red/transfergold/export" => gettext("Export Transfer Gold"),

             "/root/red/promo" => gettext("Promo for REDGOLD"),
             "/root/red/promo/list" => gettext("View Promo"),
             "/root/red/promo/export" => gettext("Export Promo"),
             // End RED Permissions
             
             // NUBEX Permissions
             "/root/nubex/redemption" => gettext("Conversion management for NUBEX"),
             "/root/nubex/redemption/list" => gettext("View conversion"),
             "/root/nubex/redemption/add" => gettext("Add conversion"),
             "/root/nubex/redemption/edit" => gettext("Edit conversion"),
             "/root/nubex/redemption/export" => gettext("Export conversion"),

             "/root/nubex/logistic" => gettext("Logistics management for NUBEX"),
             "/root/nubex/logistic/list" => gettext("View logistics information"),
             "/root/nubex/logistic/add" => gettext("Add logistics"),
             "/root/nubex/logistic/edit" => gettext("Edit logistics"),
             "/root/nubex/logistic/delete" => gettext("Delete logistics"),
             "/root/nubex/logistic/complete" => gettext("Complete logistics"),

             "/root/nubex/disbursement" => gettext("Disbursement management for NUBEX"),
             "/root/nubex/disbursement/list" => gettext("View disbursement"),
             "/root/nubex/disbursement/add" => gettext("Add disbursement"),
             "/root/nubex/disbursement/edit" => gettext("Edit disbursement"),

             "/root/nubex/profile" => gettext("Account holder profile management for NUBEX"),
             "/root/nubex/profile/list" => gettext("View user profile"),
             "/root/nubex/profile/add" => gettext("Add user profile"),
             "/root/nubex/profile/edit" => gettext("Edit user profile"),
             "/root/nubex/profile/suspend" => gettext("Suspend account holder"),
             "/root/nubex/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/nubex/profile/activate" => gettext("Activate account holder"),

             "/root/nubex/approval" => gettext("PEP approval management for NUBEX"),
             "/root/nubex/approval/list" => gettext("View PEP"),
             "/root/nubex/approval/approve" => gettext("Approve or reject PEP"),

             "/root/nubex/fpx" => gettext("FPX management for NUBEX"),
             "/root/nubex/fpx/list" => gettext("View FPX"),

             "/root/nubex/ekyc" => gettext("eKYC management for NUBEX"),
             "/root/nubex/ekyc/list" => gettext("View eKYC"),

             "/root/nubex/goldtransaction" => gettext("Gold transaction management for NUBEX"),
             "/root/nubex/goldtransaction/list" => gettext("View gold transaction"),
             "/root/nubex/goldtransaction/export" => gettext("Export gold transaction statement"),

             "/root/nubex/storagefee" => gettext("Storage Fee management for NUBEX"),
             "/root/nubex/storagefee/list" => gettext("View storage fee"),
             "/root/nubex/fee" => gettext("Fee management"),
             "/root/nubex/fee/list" => gettext("View fee management"),
             "/root/nubex/fee/add" => gettext("Add fee"),
             "/root/nubex/fee/edit" => gettext("Edit fee"),


             "/root/nubex/vault" => gettext("Inventory management for NUBEX"),
             "/root/nubex/vault/list" => gettext("View inventory"),
             "/root/nubex/vault/add" => gettext("Add item to inventory"),
             "/root/nubex/vault/edit" => gettext("Edit item in inventory"),
             "/root/nubex/vault/return" => gettext("Return item"),
             "/root/nubex/vault/transfer" => gettext("Transfer item"),

           //   "/root/nubex/vault/request" => gettext("Request item"), // maker
           //   "/root/nubex/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
           //   // "/root/nubex/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
           //   "/root/nubex/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

             "/root/nubex/report" => gettext("Reporting management for NUBEX"),
             "/root/nubex/report/commission" => gettext("Commission Reporting"),
             "/root/nubex/report/commission/list" => gettext("View Commission Reporting"),
             "/root/nubex/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/nubex/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/nubex/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/nubex/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/nubex/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/nubex/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


             "/root/nubex/accountclosure" => gettext("Account closure management for NUBEX"),
             "/root/nubex/accountclosure/list" => gettext("View account closure"),
             "/root/nubex/accountclosure/close" => gettext("Close account"),

            "/root/nubex/pricealert" => gettext("Price alert management for NUBEX"),
            "/root/nubex/pricealert/list" => gettext("View price alert"),

            "/root/nubex/sale" => gettext("Spot Order Special for NUBEX"),

            "/root/nubex/transfergold" => gettext("Transfer Gold for NUBEX"),
            "/root/nubex/transfergold/list" => gettext("View Transfer Gold"),
            "/root/nubex/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/nubex/promo" => gettext("Promo for NUBEX"),
            "/root/nubex/promo/list" => gettext("View Promo"),
            "/root/nubex/promo/export" => gettext("Export Promo"),
            // End nubex Permissions
               

            // BURSA Permissions
            "/root/bursa/mintedbar" => gettext("Minted warehouse management for BURSA"),
            "/root/bursa/mintedbar/list" => gettext("View minted warehouse"),

            "/root/bursa/order" => gettext("Order management for BURSA"),
            "/root/bursa/order/list" => gettext("View order for BURSA"),
            "/root/bursa/order/add" => gettext("Add order for BURSA"),
            "/root/bursa/order/edit" => gettext("Edit order for BURSA"),
            "/root/bursa/order/cancel" => gettext("Cancel order for BURSA"),
            "/root/bursa/order/confirm" => gettext("Confirm order for BURSA"),
            "/root/bursa/order/export" => gettext("Export order for BURSA"),

            "/root/bursa/redemption" => gettext("Conversion management for BURSA"),
            "/root/bursa/redemption/list" => gettext("View conversion"),
            "/root/bursa/redemption/add" => gettext("Add conversion"),
            "/root/bursa/redemption/edit" => gettext("Edit conversion"),
            "/root/bursa/redemption/export" => gettext("Export conversion"),

            "/root/bursa/logistic" => gettext("Logistics management for BURSA"),
            "/root/bursa/logistic/list" => gettext("View logistics information"),
            "/root/bursa/logistic/add" => gettext("Add logistics"),
            "/root/bursa/logistic/edit" => gettext("Edit logistics"),
            "/root/bursa/logistic/delete" => gettext("Delete logistics"),
            "/root/bursa/logistic/complete" => gettext("Complete logistics"),

            "/root/bursa/disbursement" => gettext("Disbursement management for BURSA"),
            "/root/bursa/disbursement/list" => gettext("View disbursement"),
            "/root/bursa/disbursement/add" => gettext("Add disbursement"),
            "/root/bursa/disbursement/edit" => gettext("Edit disbursement"),

            "/root/bursa/profile" => gettext("Account holder profile management for BURSA"),
            "/root/bursa/profile/list" => gettext("View user profile"),
            "/root/bursa/profile/add" => gettext("Add user profile"),
            "/root/bursa/profile/edit" => gettext("Edit user profile"),
            "/root/bursa/profile/suspend" => gettext("Suspend account holder"),
            "/root/bursa/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/bursa/profile/activate" => gettext("Activate account holder"),

            "/root/bursa/approval" => gettext("PEP approval management for BURSA"),
            "/root/bursa/approval/list" => gettext("View PEP"),
            "/root/bursa/approval/approve" => gettext("Approve or reject PEP"),

            "/root/bursa/fpx" => gettext("FPX management for BURSA"),
            "/root/bursa/fpx/list" => gettext("View FPX"),

            "/root/bursa/ekyc" => gettext("eKYC management for BURSA"),
            "/root/bursa/ekyc/list" => gettext("View eKYC"),

            "/root/bursa/goldtransaction" => gettext("Gold transaction management for BURSA"),
            "/root/bursa/goldtransaction/list" => gettext("View gold transaction"),
            "/root/bursa/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/bursa/storagefee" => gettext("Storage Fee management for BURSA"),
            "/root/bursa/storagefee/list" => gettext("View storage fee"),
            "/root/bursa/fee" => gettext("Fee management"),
            "/root/bursa/fee/list" => gettext("View fee management"),
            "/root/bursa/fee/add" => gettext("Add fee"),
            "/root/bursa/fee/edit" => gettext("Edit fee"),


            "/root/bursa/vault" => gettext("Inventory management for BURSA"),
            "/root/bursa/vault/list" => gettext("View inventory"),
            "/root/bursa/vault/add" => gettext("Add item to inventory"),
            "/root/bursa/vault/edit" => gettext("Edit item in inventory"),
            "/root/bursa/vault/return" => gettext("Return item"),
            "/root/bursa/vault/transfer" => gettext("Transfer item"),

            "/root/bursa/vault/request" => gettext("Request item"), // maker
            "/root/bursa/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // "/root/bursa/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            "/root/bursa/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

          //   "/root/bursa/vault/request" => gettext("Request item"), // maker
          //   "/root/bursa/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
          //   // "/root/bursa/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
          //   "/root/bursa/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/bursa/report" => gettext("Reporting management for BURSA"),
            "/root/bursa/report/commission" => gettext("Commission Reporting"),
            "/root/bursa/report/commission/list" => gettext("View Commission Reporting"),
            "/root/bursa/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/bursa/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/bursa/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/bursa/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/bursa/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/bursa/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/bursa/accountclosure" => gettext("Account closure management for BURSA"),
            "/root/bursa/accountclosure/list" => gettext("View account closure"),
            "/root/bursa/accountclosure/close" => gettext("Close account"),

           "/root/bursa/pricealert" => gettext("Price alert management for BURSA"),
           "/root/bursa/pricealert/list" => gettext("View price alert"),

           "/root/bursa/sale" => gettext("Spot Order Special for BURSA"),

           "/root/bursa/transfergold" => gettext("Transfer Gold for BURSA"),
           "/root/bursa/transfergold/list" => gettext("View Transfer Gold"),
           "/root/bursa/transfergold/export" => gettext("Export Transfer Gold"),

           "/root/bursa/promo" => gettext("Promo for BURSA"),
           "/root/bursa/promo/list" => gettext("View Promo"),
           "/root/bursa/promo/export" => gettext("Export Promo"),
           // End bursa Permissions

           // BSN Permissions
           "/root/bsn/mintedbar" => gettext("Minted warehouse management for bsn"),
           "/root/bsn/mintedbar/list" => gettext("View minted warehouse"),

           "/root/bsn/order" => gettext("Order management for bsn"),
           "/root/bsn/order/list" => gettext("View order for bsn"),
           "/root/bsn/order/add" => gettext("Add order for bsn"),
           "/root/bsn/order/edit" => gettext("Edit order for bsn"),
           "/root/bsn/order/cancel" => gettext("Cancel order for bsn"),
           "/root/bsn/order/confirm" => gettext("Confirm order for bsn"),
           "/root/bsn/order/export" => gettext("Export order for bsn"),

           "/root/bsn/redemption" => gettext("Conversion management for bsn"),
           "/root/bsn/redemption/list" => gettext("View conversion"),
           "/root/bsn/redemption/add" => gettext("Add conversion"),
           "/root/bsn/redemption/edit" => gettext("Edit conversion"),
           "/root/bsn/redemption/export" => gettext("Export conversion"),

           "/root/bsn/logistic" => gettext("Logistics management for bsn"),
           "/root/bsn/logistic/list" => gettext("View logistics information"),
           "/root/bsn/logistic/add" => gettext("Add logistics"),
           "/root/bsn/logistic/edit" => gettext("Edit logistics"),
           "/root/bsn/logistic/delete" => gettext("Delete logistics"),
           "/root/bsn/logistic/complete" => gettext("Complete logistics"),

           "/root/bsn/disbursement" => gettext("Disbursement management for bsn"),
           "/root/bsn/disbursement/list" => gettext("View disbursement"),
           "/root/bsn/disbursement/add" => gettext("Add disbursement"),
           "/root/bsn/disbursement/edit" => gettext("Edit disbursement"),

           "/root/bsn/profile" => gettext("Account holder profile management for bsn"),
           "/root/bsn/profile/list" => gettext("View user profile"),
           "/root/bsn/profile/add" => gettext("Add user profile"),
           "/root/bsn/profile/edit" => gettext("Edit user profile"),
           "/root/bsn/profile/suspend" => gettext("Suspend account holder"),
           "/root/bsn/profile/unsuspend" => gettext("Unsuspend account holder"),
           "/root/bsn/profile/activate" => gettext("Activate account holder"),

           "/root/bsn/approval" => gettext("PEP approval management for bsn"),
           "/root/bsn/approval/list" => gettext("View PEP"),
           "/root/bsn/approval/approve" => gettext("Approve or reject PEP"),

           "/root/bsn/fpx" => gettext("FPX management for bsn"),
           "/root/bsn/fpx/list" => gettext("View FPX"),

           "/root/bsn/ekyc" => gettext("eKYC management for bsn"),
           "/root/bsn/ekyc/list" => gettext("View eKYC"),

           "/root/bsn/goldtransaction" => gettext("Gold transaction management for bsn"),
           "/root/bsn/goldtransaction/list" => gettext("View gold transaction"),
           "/root/bsn/goldtransaction/export" => gettext("Export gold transaction statement"),

           "/root/bsn/storagefee" => gettext("Storage Fee management for bsn"),
           "/root/bsn/storagefee/list" => gettext("View storage fee"),
           "/root/bsn/fee" => gettext("Fee management"),
           "/root/bsn/fee/list" => gettext("View fee management"),
           "/root/bsn/fee/add" => gettext("Add fee"),
           "/root/bsn/fee/edit" => gettext("Edit fee"),


           "/root/bsn/vault" => gettext("Inventory management for bsn"),
           "/root/bsn/vault/list" => gettext("View inventory"),
           "/root/bsn/vault/add" => gettext("Add item to inventory"),
           "/root/bsn/vault/edit" => gettext("Edit item in inventory"),
           "/root/bsn/vault/return" => gettext("Return item"),
           "/root/bsn/vault/transfer" => gettext("Transfer item"),

           "/root/bsn/vault/request" => gettext("Request item"), // maker
           "/root/bsn/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
           // "/root/bsn/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
           "/root/bsn/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

         //   "/root/bsn/vault/request" => gettext("Request item"), // maker
         //   "/root/bsn/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
         //   // "/root/bsn/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
         //   "/root/bsn/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

           "/root/bsn/report" => gettext("Reporting management for bsn"),
           "/root/bsn/report/commission" => gettext("Commission Reporting"),
           "/root/bsn/report/commission/list" => gettext("View Commission Reporting"),
           "/root/bsn/report/storagefee" => gettext("Storage Fee Reporting"),
           "/root/bsn/report/storagefee/list" => gettext("View Storage Fee Reporting"),
           "/root/bsn/report/adminfee" => gettext("Admin Fee Reporting"),
           "/root/bsn/report/adminfee/list" => gettext("View Admin Fee Reporting"),
           "/root/bsn/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
           "/root/bsn/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


           "/root/bsn/accountclosure" => gettext("Account closure management for bsn"),
           "/root/bsn/accountclosure/list" => gettext("View account closure"),
           "/root/bsn/accountclosure/close" => gettext("Close account"),

          "/root/bsn/pricealert" => gettext("Price alert management for bsn"),
          "/root/bsn/pricealert/list" => gettext("View price alert"),

          "/root/bsn/sale" => gettext("Spot Order Special for bsn"),

          "/root/bsn/transfergold" => gettext("Transfer Gold for bsn"),
          "/root/bsn/transfergold/list" => gettext("View Transfer Gold"),
          "/root/bsn/transfergold/export" => gettext("Export Transfer Gold"),

          "/root/bsn/promo" => gettext("Promo for bsn"),
          "/root/bsn/promo/list" => gettext("View Promo"),
          "/root/bsn/promo/export" => gettext("Export Promo"),
          // End bsn Permissions

            // WAVPAY Permissions
            "/root/wavpay/redemption" => gettext("Conversion management for WAVPAY"),
            "/root/wavpay/redemption/list" => gettext("View conversion"),
            "/root/wavpay/redemption/add" => gettext("Add conversion"),
            "/root/wavpay/redemption/edit" => gettext("Edit conversion"),
            "/root/wavpay/redemption/export" => gettext("Export conversion"),

            "/root/wavpay/logistic" => gettext("Logistics management for WAVPAY"),
            "/root/wavpay/logistic/list" => gettext("View logistics information"),
            "/root/wavpay/logistic/add" => gettext("Add logistics"),
            "/root/wavpay/logistic/edit" => gettext("Edit logistics"),
            "/root/wavpay/logistic/delete" => gettext("Delete logistics"),
            "/root/wavpay/logistic/complete" => gettext("Complete logistics"),

            "/root/wavpay/disbursement" => gettext("Disbursement management for WAVPAY"),
            "/root/wavpay/disbursement/list" => gettext("View disbursement"),
            "/root/wavpay/disbursement/add" => gettext("Add disbursement"),
            "/root/wavpay/disbursement/edit" => gettext("Edit disbursement"),

            "/root/wavpay/profile" => gettext("Account holder profile management for WAVPAY"),
            "/root/wavpay/profile/list" => gettext("View user profile"),
            "/root/wavpay/profile/add" => gettext("Add user profile"),
            "/root/wavpay/profile/edit" => gettext("Edit user profile"),
            "/root/wavpay/profile/suspend" => gettext("Suspend account holder"),
            "/root/wavpay/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/wavpay/profile/activate" => gettext("Activate account holder"),

            "/root/wavpay/approval" => gettext("PEP approval management for WAVPAY"),
            "/root/wavpay/approval/list" => gettext("View PEP"),
            "/root/wavpay/approval/approve" => gettext("Approve or reject PEP"),

            "/root/wavpay/fpx" => gettext("FPX management for WAVPAY"),
            "/root/wavpay/fpx/list" => gettext("View FPX"),

            "/root/wavpay/ekyc" => gettext("eKYC management for WAVPAY"),
            "/root/wavpay/ekyc/list" => gettext("View eKYC"),

            "/root/wavpay/goldtransaction" => gettext("Gold transaction management for WAVPAY"),
            "/root/wavpay/goldtransaction/list" => gettext("View gold transaction"),
            "/root/wavpay/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/wavpay/storagefee" => gettext("Storage Fee management for WAVPAY"),
            "/root/wavpay/storagefee/list" => gettext("View storage fee"),
            "/root/wavpay/fee" => gettext("Fee management"),
            "/root/wavpay/fee/list" => gettext("View fee management"),
            "/root/wavpay/fee/add" => gettext("Add fee"),
            "/root/wavpay/fee/edit" => gettext("Edit fee"),


            "/root/wavpay/vault" => gettext("Inventory management for WAVPAY"),
            "/root/wavpay/vault/list" => gettext("View inventory"),
            "/root/wavpay/vault/add" => gettext("Add item to inventory"),
            "/root/wavpay/vault/edit" => gettext("Edit item in inventory"),
            "/root/wavpay/vault/return" => gettext("Return item"),
            "/root/wavpay/vault/transfer" => gettext("Transfer item"),

          //   "/root/wavpay/vault/request" => gettext("Request item"), // maker
          //   "/root/wavpay/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
          //   // "/root/wavpay/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
          //   "/root/wavpay/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/wavpay/report" => gettext("Reporting management for WAVPAY"),
            "/root/wavpay/report/commission" => gettext("Commission Reporting"),
            "/root/wavpay/report/commission/list" => gettext("View Commission Reporting"),
            "/root/wavpay/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/wavpay/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/wavpay/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/wavpay/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/wavpay/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/wavpay/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/wavpay/accountclosure" => gettext("Account closure management for WAVPAY"),
            "/root/wavpay/accountclosure/list" => gettext("View account closure"),
            "/root/wavpay/accountclosure/close" => gettext("Close account"),

           "/root/wavpay/pricealert" => gettext("Price alert management for WAVPAY"),
           "/root/wavpay/pricealert/list" => gettext("View price alert"),

           "/root/wavpay/sale" => gettext("Spot Order Special for WAVPAY"),

           "/root/wavpay/transfergold" => gettext("Transfer Gold for WAVPAY"),
           "/root/wavpay/transfergold/list" => gettext("View Transfer Gold"),
           "/root/wavpay/transfergold/export" => gettext("Export Transfer Gold"),

           "/root/wavpay/promo" => gettext("Promo for WAVPAY"),
           "/root/wavpay/promo/list" => gettext("View Promo"),
           "/root/wavpay/promo/export" => gettext("Export Promo"),
           // End WAVPAY Permissions

               
             // IGOLD Permissions
             "/root/igold/redemption" => gettext("Conversion management for IGOLD"),
             "/root/igold/redemption/list" => gettext("View conversion"),
             "/root/igold/redemption/add" => gettext("Add conversion"),
             "/root/igold/redemption/edit" => gettext("Edit conversion"),
             "/root/igold/redemption/export" => gettext("Export conversion"),

             "/root/igold/logistic" => gettext("Logistics management for IGOLD"),
             "/root/igold/logistic/list" => gettext("View logistics information"),
             "/root/igold/logistic/add" => gettext("Add logistics"),
             "/root/igold/logistic/edit" => gettext("Edit logistics"),
             "/root/igold/logistic/delete" => gettext("Delete logistics"),
             "/root/igold/logistic/complete" => gettext("Complete logistics"),

             "/root/igold/disbursement" => gettext("Disbursement management for IGOLD"),
             "/root/igold/disbursement/list" => gettext("View disbursement"),
             "/root/igold/disbursement/add" => gettext("Add disbursement"),
             "/root/igold/disbursement/edit" => gettext("Edit disbursement"),

             "/root/igold/profile" => gettext("Account holder profile management for IGOLD"),
             "/root/igold/profile/list" => gettext("View user profile"),
             "/root/igold/profile/add" => gettext("Add user profile"),
             "/root/igold/profile/edit" => gettext("Edit user profile"),
             "/root/igold/profile/suspend" => gettext("Suspend account holder"),
             "/root/igold/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/igold/profile/activate" => gettext("Activate account holder"),

             "/root/igold/approval" => gettext("PEP approval management for IGOLD"),
             "/root/igold/approval/list" => gettext("View PEP"),
             "/root/igold/approval/approve" => gettext("Approve or reject PEP"),

             "/root/igold/fpx" => gettext("FPX management for IGOLD"),
             "/root/igold/fpx/list" => gettext("View FPX"),

             "/root/igold/ekyc" => gettext("eKYC management for IGOLD"),
             "/root/igold/ekyc/list" => gettext("View eKYC"),

             "/root/igold/goldtransaction" => gettext("Gold transaction management for IGOLD"),
             "/root/igold/goldtransaction/list" => gettext("View gold transaction"),
             "/root/igold/goldtransaction/export" => gettext("Export gold transaction statement"),

             "/root/igold/storagefee" => gettext("Storage Fee management for IGOLD"),
             "/root/igold/storagefee/list" => gettext("View storage fee"),
             "/root/igold/fee" => gettext("Fee management"),
             "/root/igold/fee/list" => gettext("View fee management"),
             "/root/igold/fee/add" => gettext("Add fee"),
             "/root/igold/fee/edit" => gettext("Edit fee"),


             "/root/igold/vault" => gettext("Inventory management for IGOLD"),
             "/root/igold/vault/list" => gettext("View inventory"),
             "/root/igold/vault/add" => gettext("Add item to inventory"),
             "/root/igold/vault/edit" => gettext("Edit item in inventory"),
             "/root/igold/vault/return" => gettext("Return item"),
             "/root/igold/vault/transfer" => gettext("Transfer item"),

           //   "/root/igold/vault/request" => gettext("Request item"), // maker
           //   "/root/igold/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
           //   // "/root/igold/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
           //   "/root/igold/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

             "/root/igold/report" => gettext("Reporting management for IGOLD"),
             "/root/igold/report/commission" => gettext("Commission Reporting"),
             "/root/igold/report/commission/list" => gettext("View Commission Reporting"),
             "/root/igold/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/igold/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/igold/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/igold/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/igold/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/igold/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


             "/root/igold/accountclosure" => gettext("Account closure management for IGOLD"),
             "/root/igold/accountclosure/list" => gettext("View account closure"),
             "/root/igold/accountclosure/close" => gettext("Close account"),

            "/root/igold/pricealert" => gettext("Price alert management for IGOLD"),
            "/root/igold/pricealert/list" => gettext("View price alert"),

            "/root/igold/sale" => gettext("Spot Order Special for IGOLD"),

            "/root/igold/transfergold" => gettext("Transfer Gold for IGOLD"),
            "/root/igold/transfergold/list" => gettext("View Transfer Gold"),
            "/root/igold/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/igold/promo" => gettext("Promo for IGOLD"),
            "/root/igold/promo/list" => gettext("View Promo"),
            "/root/igold/promo/export" => gettext("Export Promo"),
            // End igold Permissions

            // PKB Permissions
            "/root/ktp/redemption" => gettext("Conversion management for PKB"),
            "/root/ktp/redemption/list" => gettext("View conversion"),
            "/root/ktp/redemption/add" => gettext("Add conversion"),
            "/root/ktp/redemption/edit" => gettext("Edit conversion"),
            "/root/ktp/redemption/export" => gettext("Export conversion"),

            "/root/ktp/logistic" => gettext("Logistics management for PKB"),
            "/root/ktp/logistic/list" => gettext("View logistics information"),
            "/root/ktp/logistic/add" => gettext("Add logistics"),
            "/root/ktp/logistic/edit" => gettext("Edit logistics"),
            "/root/ktp/logistic/delete" => gettext("Delete logistics"),
            "/root/ktp/logistic/complete" => gettext("Complete logistics"),

            "/root/ktp/disbursement" => gettext("Disbursement management for PKB"),
            "/root/ktp/disbursement/list" => gettext("View disbursement"),
            "/root/ktp/disbursement/add" => gettext("Add disbursement"),
            "/root/ktp/disbursement/edit" => gettext("Edit disbursement"),

            "/root/ktp/profile" => gettext("Account holder profile management for PKB"),
            "/root/ktp/profile/list" => gettext("View user profile"),
            "/root/ktp/profile/add" => gettext("Add user profile"),
            "/root/ktp/profile/edit" => gettext("Edit user profile"),
            "/root/ktp/profile/suspend" => gettext("Suspend account holder"),
            "/root/ktp/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/ktp/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/ktp/profile/updateMember" => gettext("Update account holder member list"),
            "/root/ktp/profile/activate" => gettext("Activate account holder"),

            "/root/ktp/approval" => gettext("PEP approval management for PKB"),
            "/root/ktp/approval/list" => gettext("View PEP"),
            "/root/ktp/approval/approve" => gettext("Approve or reject PEP"),

            "/root/ktp/fpx" => gettext("FPX management for PKB"),
            "/root/ktp/fpx/list" => gettext("View FPX"),

            "/root/ktp/ekyc" => gettext("eKYC management for PKB"),
            "/root/ktp/ekyc/list" => gettext("View eKYC"),

            "/root/ktp/goldtransaction" => gettext("Gold transaction management for PKB"),
            "/root/ktp/goldtransaction/list" => gettext("View gold transaction"),
            "/root/ktp/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/ktp/storagefee" => gettext("Storage Fee management for PKB"),
            "/root/ktp/storagefee/list" => gettext("View storage fee"),
            "/root/ktp/fee" => gettext("Fee management"),
            "/root/ktp/fee/list" => gettext("View fee management"),
            "/root/ktp/fee/add" => gettext("Add fee"),
            "/root/ktp/fee/edit" => gettext("Edit fee"),


            "/root/ktp/vault" => gettext("Inventory management for PKB"),
            "/root/ktp/vault/list" => gettext("View inventory"),
            "/root/ktp/vault/add" => gettext("Add item to inventory"),
            "/root/ktp/vault/edit" => gettext("Edit item in inventory"),
            "/root/ktp/vault/return" => gettext("Return item"),
            "/root/ktp/vault/transfer" => gettext("Transfer item"),

            // "/root/ktp/vault/request" => gettext("Request item"), // maker
            // "/root/ktp/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/ktp/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/ktp/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/ktp/report" => gettext("Reporting management for PKB"),
            "/root/ktp/report/commission" => gettext("Commission Reporting"),
            "/root/ktp/report/commission/list" => gettext("View Commission Reporting"),
            "/root/ktp/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/ktp/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/ktp/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/ktp/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/ktp/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/ktp/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/ktp/accountclosure" => gettext("Account closure management for PKB"),
            "/root/ktp/accountclosure/list" => gettext("View account closure"),
            "/root/ktp/accountclosure/close" => gettext("Close account"),

            "/root/ktp/pricealert" => gettext("Price alert management for PKB"),
            "/root/ktp/pricealert/list" => gettext("View price alert"),

            "/root/ktp/sale" => gettext("Spot Order Special for PKB"),

            "/root/ktp/transfergold" => gettext("Transfer Gold for PKB"),
            "/root/ktp/transfergold/list" => gettext("View Transfer Gold"),
            "/root/ktp/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/ktp/promo" => gettext("Promo for PKB"),
            "/root/ktp/promo/list" => gettext("View Promo"),
            "/root/ktp/promo/export" => gettext("Export Promo"),
            // End KTP Permissions

             // PKB Affiliate Permissions
             "/root/pkbaffi/" => gettext("PKB Affiliate Modules"),
             "/root/pkbaffi/redemption" => gettext("Conversion management for PKB Affiliate"),
             "/root/pkbaffi/redemption/list" => gettext("View conversion"),
             "/root/pkbaffi/redemption/add" => gettext("Add conversion"),
             "/root/pkbaffi/redemption/edit" => gettext("Edit conversion"),
             "/root/pkbaffi/redemption/export" => gettext("Export conversion"),
 
             "/root/pkbaffi/logistic" => gettext("Logistics management for PKB Affiliate"),
             "/root/pkbaffi/logistic/list" => gettext("View logistics information"),
             "/root/pkbaffi/logistic/add" => gettext("Add logistics"),
             "/root/pkbaffi/logistic/edit" => gettext("Edit logistics"),
             "/root/pkbaffi/logistic/delete" => gettext("Delete logistics"),
             "/root/pkbaffi/logistic/complete" => gettext("Complete logistics"),
 
             "/root/pkbaffi/disbursement" => gettext("Disbursement management for PKB Affiliate"),
             "/root/pkbaffi/disbursement/list" => gettext("View disbursement"),
             "/root/pkbaffi/disbursement/add" => gettext("Add disbursement"),
             "/root/pkbaffi/disbursement/edit" => gettext("Edit disbursement"),
 
             "/root/pkbaffi/profile" => gettext("Account holder profile management for PKB Affiliate"),
             "/root/pkbaffi/profile/list" => gettext("View user profile"),
             "/root/pkbaffi/profile/add" => gettext("Add user profile"),
             "/root/pkbaffi/profile/edit" => gettext("Edit user profile"),
             "/root/pkbaffi/profile/suspend" => gettext("Suspend account holder"),
             "/root/pkbaffi/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/pkbaffi/profile/updateLoan" => gettext("Update account holder loans"),
             "/root/pkbaffi/profile/updateMember" => gettext("Update account holder member list"),     
             "/root/pkbaffi/profile/activate" => gettext("Activate account holder"),

             "/root/pkbaffi/approval" => gettext("PEP approval management for PKB Affiliate"),
             "/root/pkbaffi/approval/list" => gettext("View PEP"),
             "/root/pkbaffi/approval/approve" => gettext("Approve or reject PEP"),
 
             "/root/pkbaffi/fpx" => gettext("FPX management for PKB Affiliate"),
             "/root/pkbaffi/fpx/list" => gettext("View FPX"),
 
             "/root/pkbaffi/ekyc" => gettext("eKYC management for PKB Affiliate"),
             "/root/pkbaffi/ekyc/list" => gettext("View eKYC"),
 
             "/root/pkbaffi/goldtransaction" => gettext("Gold transaction management for PKB Affiliate"),
             "/root/pkbaffi/goldtransaction/list" => gettext("View gold transaction"),
             "/root/pkbaffi/goldtransaction/export" => gettext("Export gold transaction statement"),
 
             "/root/pkbaffi/storagefee" => gettext("Storage Fee management for PKB Affiliate"),
             "/root/pkbaffi/storagefee/list" => gettext("View storage fee"),
             "/root/pkbaffi/fee" => gettext("Fee management"),
             "/root/pkbaffi/fee/list" => gettext("View fee management"),
             "/root/pkbaffi/fee/add" => gettext("Add fee"),
             "/root/pkbaffi/fee/edit" => gettext("Edit fee"),
 
 
             "/root/pkbaffi/vault" => gettext("Inventory management for PKB Affiliate"),
             "/root/pkbaffi/vault/list" => gettext("View inventory"),
             "/root/pkbaffi/vault/add" => gettext("Add item to inventory"),
             "/root/pkbaffi/vault/edit" => gettext("Edit item in inventory"),
             "/root/pkbaffi/vault/return" => gettext("Return item"),
             "/root/pkbaffi/vault/transfer" => gettext("Transfer item"),

            //  "/root/pkbaffi/vault/request" => gettext("Request item"), // maker
            //  "/root/pkbaffi/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //  // "/root/pkbaffi/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //  "/root/pkbaffi/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
             "/root/pkbaffi/report" => gettext("Reporting management for PKB Affiliate"),
             "/root/pkbaffi/report/commission" => gettext("Commission Reporting"),
             "/root/pkbaffi/report/commission/list" => gettext("View Commission Reporting"),
             "/root/pkbaffi/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/pkbaffi/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/pkbaffi/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/pkbaffi/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/pkbaffi/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/pkbaffi/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
 
             "/root/pkbaffi/accountclosure" => gettext("Account closure management for PKB Affiliate"),
             "/root/pkbaffi/accountclosure/list" => gettext("View account closure"),
             "/root/pkbaffi/accountclosure/close" => gettext("Close account"),

             "/root/pkbaffi/pricealert" => gettext("Price alert management for PKB Affiliate"),
             "/root/pkbaffi/pricealert/list" => gettext("View price alert"),
 
             "/root/pkbaffi/sale" => gettext("Spot Order Special for PKB Affiliate"),

             "/root/pkbaffi/transfergold" => gettext("Transfer Gold for PKB Affiliate"),
             "/root/pkbaffi/transfergold/list" => gettext("View Transfer Gold"),
             "/root/pkbaffi/transfergold/export" => gettext("Export Transfer Gold"),

             "/root/pkbaffi/promo" => gettext("Promo for PKB Affliate"),
             "/root/pkbaffi/promo/list" => gettext("View Promo"),
             "/root/pkbaffi/promo/export" => gettext("Export Promo"),
             // End PKB Affiliate Permissions

            // Kopetro Permissions
            "/root/kopetro" => gettext("Kopetro Modules"),
            "/root/kopetro/redemption" => gettext("Conversion management for Kopetro"),
            "/root/kopetro/redemption/list" => gettext("View conversion"),
            "/root/kopetro/redemption/add" => gettext("Add conversion"),
            "/root/kopetro/redemption/edit" => gettext("Edit conversion"),
            "/root/kopetro/redemption/export" => gettext("Export conversion"),

            "/root/kopetro/logistic" => gettext("Logistics management for Kopetro"),
            "/root/kopetro/logistic/list" => gettext("View logistics information"),
            "/root/kopetro/logistic/add" => gettext("Add logistics"),
            "/root/kopetro/logistic/edit" => gettext("Edit logistics"),
            "/root/kopetro/logistic/delete" => gettext("Delete logistics"),
            "/root/kopetro/logistic/complete" => gettext("Complete logistics"),

            "/root/kopetro/disbursement" => gettext("Disbursement management for Kopetro"),
            "/root/kopetro/disbursement/list" => gettext("View disbursement"),
            "/root/kopetro/disbursement/add" => gettext("Add disbursement"),
            "/root/kopetro/disbursement/edit" => gettext("Edit disbursement"),

            "/root/kopetro/profile" => gettext("Account holder profile management for Kopetro"),
            "/root/kopetro/profile/list" => gettext("View user profile"),
            "/root/kopetro/profile/add" => gettext("Add user profile"),
            "/root/kopetro/profile/edit" => gettext("Edit user profile"),
            "/root/kopetro/profile/suspend" => gettext("Suspend account holder"),
            "/root/kopetro/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/kopetro/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/kopetro/profile/updateMember" => gettext("Update account holder member list"),
            "/root/kopetro/profile/activate" => gettext("Activate account holder"),

            "/root/kopetro/approval" => gettext("PEP approval management for Kopetro"),
            "/root/kopetro/approval/list" => gettext("View PEP"),
            "/root/kopetro/approval/approve" => gettext("Approve or reject PEP"),

            "/root/kopetro/fpx" => gettext("FPX management for Kopetro"),
            "/root/kopetro/fpx/list" => gettext("View FPX"),

            "/root/kopetro/ekyc" => gettext("eKYC management for Kopetro"),
            "/root/kopetro/ekyc/list" => gettext("View eKYC"),

            "/root/kopetro/goldtransaction" => gettext("Gold transaction management for Kopetro"),
            "/root/kopetro/goldtransaction/list" => gettext("View gold transaction"),
            "/root/kopetro/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/kopetro/storagefee" => gettext("Storage Fee management for Kopetro"),
            "/root/kopetro/storagefee/list" => gettext("View storage fee"),
            "/root/kopetro/fee" => gettext("Fee management"),
            "/root/kopetro/fee/list" => gettext("View fee management"),
            "/root/kopetro/fee/add" => gettext("Add fee"),
            "/root/kopetro/fee/edit" => gettext("Edit fee"),


            "/root/kopetro/vault" => gettext("Inventory management for Kopetro"),
            "/root/kopetro/vault/list" => gettext("View inventory"),
            "/root/kopetro/vault/add" => gettext("Add item to inventory"),
            "/root/kopetro/vault/edit" => gettext("Edit item in inventory"),
            "/root/kopetro/vault/return" => gettext("Return item"),
            "/root/kopetro/vault/transfer" => gettext("Transfer item"),

            // "/root/kopetro/vault/request" => gettext("Request item"), // maker
            // "/root/kopetro/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/kopetro/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/kopetro/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/kopetro/report" => gettext("Reporting management for Kopetro"),
            "/root/kopetro/report/commission" => gettext("Commission Reporting"),
            "/root/kopetro/report/commission/list" => gettext("View Commission Reporting"),
            "/root/kopetro/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/kopetro/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/kopetro/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/kopetro/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/kopetro/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/kopetro/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/kopetro/accountclosure" => gettext("Account closure management for Kopetro"),
            "/root/kopetro/accountclosure/list" => gettext("View account closure"),
            "/root/kopetro/accountclosure/close" => gettext("Close account"),

            "/root/kopetro/pricealert" => gettext("Price alert management for Kopetro"),
            "/root/kopetro/pricealert/list" => gettext("View price alert"),

            "/root/kopetro/sale" => gettext("Spot Order Special for Kopetro"),

            "/root/kopetro/transfergold" => gettext("Transfer Gold for Kopetro"),
            "/root/kopetro/transfergold/list" => gettext("View Transfer Gold"),
            "/root/kopetro/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/kopetro/promo" => gettext("Promo for Kopetro"),
            "/root/kopetro/promo/list" => gettext("View Promo"),
            "/root/kopetro/promo/export" => gettext("Export Promo"),
            // End Kopetro Permissions


            // Koperasi Tentera Permissions
            "/root/kopttr/" => gettext("Koperasi Tentera Modules"),
            "/root/kopttr/redemption" => gettext("Conversion management for Koperasi Tentera"),
            "/root/kopttr/redemption/list" => gettext("View conversion"),
            "/root/kopttr/redemption/add" => gettext("Add conversion"),
            "/root/kopttr/redemption/edit" => gettext("Edit conversion"),
            "/root/kopttr/redemption/export" => gettext("Export conversion"),

            "/root/kopttr/logistic" => gettext("Logistics management for Koperasi Tentera"),
            "/root/kopttr/logistic/list" => gettext("View logistics information"),
            "/root/kopttr/logistic/add" => gettext("Add logistics"),
            "/root/kopttr/logistic/edit" => gettext("Edit logistics"),
            "/root/kopttr/logistic/delete" => gettext("Delete logistics"),
            "/root/kopttr/logistic/complete" => gettext("Complete logistics"),

            "/root/kopttr/disbursement" => gettext("Disbursement management for Koperasi Tentera"),
            "/root/kopttr/disbursement/list" => gettext("View disbursement"),
            "/root/kopttr/disbursement/add" => gettext("Add disbursement"),
            "/root/kopttr/disbursement/edit" => gettext("Edit disbursement"),

            "/root/kopttr/profile" => gettext("Account holder profile management for Koperasi Tentera"),
            "/root/kopttr/profile/list" => gettext("View user profile"),
            "/root/kopttr/profile/add" => gettext("Add user profile"),
            "/root/kopttr/profile/edit" => gettext("Edit user profile"),
            "/root/kopttr/profile/suspend" => gettext("Suspend account holder"),
            "/root/kopttr/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/kopttr/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/kopttr/profile/updateMember" => gettext("Update account holder member list"),
            "/root/kopttr/profile/activate" => gettext("Activate account holder"),

            "/root/kopttr/approval" => gettext("PEP approval management for Koperasi Tentera"),
            "/root/kopttr/approval/list" => gettext("View PEP"),
            "/root/kopttr/approval/approve" => gettext("Approve or reject PEP"),

            "/root/kopttr/fpx" => gettext("FPX management for Koperasi Tentera"),
            "/root/kopttr/fpx/list" => gettext("View FPX"),

            "/root/kopttr/ekyc" => gettext("eKYC management for Koperasi Tentera"),
            "/root/kopttr/ekyc/list" => gettext("View eKYC"),

            "/root/kopttr/goldtransaction" => gettext("Gold transaction management for Koperasi Tentera"),
            "/root/kopttr/goldtransaction/list" => gettext("View gold transaction"),
            "/root/kopttr/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/kopttr/storagefee" => gettext("Storage Fee management for Koperasi Tentera"),
            "/root/kopttr/storagefee/list" => gettext("View storage fee"),
            "/root/kopttr/fee" => gettext("Fee management"),
            "/root/kopttr/fee/list" => gettext("View fee management"),
            "/root/kopttr/fee/add" => gettext("Add fee"),
            "/root/kopttr/fee/edit" => gettext("Edit fee"),


            "/root/kopttr/vault" => gettext("Inventory management for Koperasi Tentera"),
            "/root/kopttr/vault/list" => gettext("View inventory"),
            "/root/kopttr/vault/add" => gettext("Add item to inventory"),
            "/root/kopttr/vault/edit" => gettext("Edit item in inventory"),
            "/root/kopttr/vault/return" => gettext("Return item"),
            "/root/kopttr/vault/transfer" => gettext("Transfer item"),

            // "/root/kopttr/vault/request" => gettext("Request item"), // maker
            // "/root/kopttr/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/kopttr/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/kopttr/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/kopttr/report" => gettext("Reporting management for Koperasi Tentera"),
            "/root/kopttr/report/commission" => gettext("Commission Reporting"),
            "/root/kopttr/report/commission/list" => gettext("View Commission Reporting"),
            "/root/kopttr/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/kopttr/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/kopttr/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/kopttr/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/kopttr/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/kopttr/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/kopttr/accountclosure" => gettext("Account closure management for Koperasi Tentera"),
            "/root/kopttr/accountclosure/list" => gettext("View account closure"),
            "/root/kopttr/accountclosure/close" => gettext("Close account"),

            "/root/kopttr/pricealert" => gettext("Price alert management for Koperasi Tentera"),
            "/root/kopttr/pricealert/list" => gettext("View price alert"),

            "/root/kopttr/sale" => gettext("Spot Order Special for Koperasi Tentera"),

            "/root/kopttr/transfergold" => gettext("Transfer Gold for Koperasi Tentera"),
            "/root/kopttr/transfergold/list" => gettext("View Transfer Gold"),
            "/root/kopttr/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/kopttr/promo" => gettext("Promo for Koperasi Tentera"),
            "/root/kopttr/promo/list" => gettext("View Promo"),
            "/root/kopttr/promo/export" => gettext("Export Promo"),
            // End KTP Permissions

             // bumira Permissions
             "/root/bumira" => gettext("Bumira Modules"),
             "/root/bumira/redemption" => gettext("Conversion management for Bumira"),
             "/root/bumira/redemption/list" => gettext("View conversion"),
             "/root/bumira/redemption/add" => gettext("Add conversion"),
             "/root/bumira/redemption/edit" => gettext("Edit conversion"),
             "/root/bumira/redemption/export" => gettext("Export conversion"),
 
             "/root/bumira/logistic" => gettext("Logistics management for Bumira"),
             "/root/bumira/logistic/list" => gettext("View logistics information"),
             "/root/bumira/logistic/add" => gettext("Add logistics"),
             "/root/bumira/logistic/edit" => gettext("Edit logistics"),
             "/root/bumira/logistic/delete" => gettext("Delete logistics"),
             "/root/bumira/logistic/complete" => gettext("Complete logistics"),
 
             "/root/bumira/disbursement" => gettext("Disbursement management for Bumira"),
             "/root/bumira/disbursement/list" => gettext("View disbursement"),
             "/root/bumira/disbursement/add" => gettext("Add disbursement"),
             "/root/bumira/disbursement/edit" => gettext("Edit disbursement"),
 
             "/root/bumira/profile" => gettext("Account holder profile management for Bumira"),
             "/root/bumira/profile/list" => gettext("View user profile"),
             "/root/bumira/profile/add" => gettext("Add user profile"),
             "/root/bumira/profile/edit" => gettext("Edit user profile"),
             "/root/bumira/profile/suspend" => gettext("Suspend account holder"),
             "/root/bumira/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/bumira/profile/updateLoan" => gettext("Update account holder loans"),
             "/root/bumira/profile/updateMember" => gettext("Update account holder member list"),
             "/root/bumira/profile/activate" => gettext("Activate account holder"),

             "/root/bumira/approval" => gettext("PEP approval management for Bumira"),
             "/root/bumira/approval/list" => gettext("View PEP"),
             "/root/bumira/approval/approve" => gettext("Approve or reject PEP"),
 
             "/root/bumira/fpx" => gettext("FPX management for Bumira"),
             "/root/bumira/fpx/list" => gettext("View FPX"),
 
             "/root/bumira/ekyc" => gettext("eKYC management for Bumira"),
             "/root/bumira/ekyc/list" => gettext("View eKYC"),
 
             "/root/bumira/goldtransaction" => gettext("Gold transaction management for Bumira"),
             "/root/bumira/goldtransaction/list" => gettext("View gold transaction"),
             "/root/bumira/goldtransaction/export" => gettext("Export gold transaction statement"),
 
             "/root/bumira/storagefee" => gettext("Storage Fee management for Bumira"),
             "/root/bumira/storagefee/list" => gettext("View storage fee"),
             "/root/bumira/fee" => gettext("Fee management"),
             "/root/bumira/fee/list" => gettext("View fee management"),
             "/root/bumira/fee/add" => gettext("Add fee"),
             "/root/bumira/fee/edit" => gettext("Edit fee"),
 
 
             "/root/bumira/vault" => gettext("Inventory management for Bumira"),
             "/root/bumira/vault/list" => gettext("View inventory"),
             "/root/bumira/vault/add" => gettext("Add item to inventory"),
             "/root/bumira/vault/edit" => gettext("Edit item in inventory"),
             "/root/bumira/vault/return" => gettext("Return item"),
             "/root/bumira/vault/transfer" => gettext("Transfer item"),

            //  "/root/bumira/vault/request" => gettext("Request item"), // maker
            //  "/root/bumira/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //  // "/root/bumira/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //  "/root/bumira/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
             "/root/bumira/report" => gettext("Reporting management for Bumira"),
             "/root/bumira/report/commission" => gettext("Commission Reporting"),
             "/root/bumira/report/commission/list" => gettext("View Commission Reporting"),
             "/root/bumira/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/bumira/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/bumira/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/bumira/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/bumira/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/bumira/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
 
             "/root/bumira/accountclosure" => gettext("Account closure management for Bumira"),
             "/root/bumira/accountclosure/list" => gettext("View account closure"),
             "/root/bumira/accountclosure/close" => gettext("Close account"),

             "/root/bumira/pricealert" => gettext("Price alert management for Bumira"),
             "/root/bumira/pricealert/list" => gettext("View price alert"),
 
             "/root/bumira/sale" => gettext("Spot Order Special for Bumira"),

             "/root/bumira/transfergold" => gettext("Transfer Gold for Bumira"),
             "/root/bumira/transfergold/list" => gettext("View Transfer Gold"),
             "/root/bumira/transfergold/export" => gettext("Export Transfer Gold"),

             "/root/bumira/promo" => gettext("Promo for Bumira"),
             "/root/bumira/promo/list" => gettext("View Promo"),
             "/root/bumira/promo/export" => gettext("Export Promo"),
             // End bumira Permissions


             // Kodimas Permissions
            "/root/kodimas/redemption" => gettext("Conversion management for KODIMAS"),
            "/root/kodimas/redemption/list" => gettext("View conversion"),
            "/root/kodimas/redemption/add" => gettext("Add conversion"),
            "/root/kodimas/redemption/edit" => gettext("Edit conversion"),
            "/root/kodimas/redemption/export" => gettext("Export conversion"),

            "/root/kodimas/logistic" => gettext("Logistics management for KODIMAS"),
            "/root/kodimas/logistic/list" => gettext("View logistics information"),
            "/root/kodimas/logistic/add" => gettext("Add logistics"),
            "/root/kodimas/logistic/edit" => gettext("Edit logistics"),
            "/root/kodimas/logistic/delete" => gettext("Delete logistics"),
            "/root/kodimas/logistic/complete" => gettext("Complete logistics"),

            "/root/kodimas/disbursement" => gettext("Disbursement management for KODIMAS"),
            "/root/kodimas/disbursement/list" => gettext("View disbursement"),
            "/root/kodimas/disbursement/add" => gettext("Add disbursement"),
            "/root/kodimas/disbursement/edit" => gettext("Edit disbursement"),

            "/root/kodimas/profile" => gettext("Account holder profile management for KODIMAS"),
            "/root/kodimas/profile/list" => gettext("View user profile"),
            "/root/kodimas/profile/add" => gettext("Add user profile"),
            "/root/kodimas/profile/edit" => gettext("Edit user profile"),
            "/root/kodimas/profile/suspend" => gettext("Suspend account holder"),
            "/root/kodimas/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/kodimas/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/kodimas/profile/updateMember" => gettext("Update account holder member list"),
            "/root/kodimas/profile/activate" => gettext("Activate account holder"),

            "/root/kodimas/approval" => gettext("PEP approval management for KODIMAS"),
            "/root/kodimas/approval/list" => gettext("View PEP"),
            "/root/kodimas/approval/approve" => gettext("Approve or reject PEP"),

            "/root/kodimas/fpx" => gettext("FPX management for KODIMAS"),
            "/root/kodimas/fpx/list" => gettext("View FPX"),

            "/root/kodimas/ekyc" => gettext("eKYC management for KODIMAS"),
            "/root/kodimas/ekyc/list" => gettext("View eKYC"),

            "/root/kodimas/goldtransaction" => gettext("Gold transaction management for KODIMAS"),
            "/root/kodimas/goldtransaction/list" => gettext("View gold transaction"),
            "/root/kodimas/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/kodimas/storagefee" => gettext("Storage Fee management for KODIMAS"),
            "/root/kodimas/storagefee/list" => gettext("View storage fee"),
            "/root/kodimas/fee" => gettext("Fee management"),
            "/root/kodimas/fee/list" => gettext("View fee management"),
            "/root/kodimas/fee/add" => gettext("Add fee"),
            "/root/kodimas/fee/edit" => gettext("Edit fee"),


            "/root/kodimas/vault" => gettext("Inventory management for KODIMAS"),
            "/root/kodimas/vault/list" => gettext("View inventory"),
            "/root/kodimas/vault/add" => gettext("Add item to inventory"),
            "/root/kodimas/vault/edit" => gettext("Edit item in inventory"),
            "/root/kodimas/vault/return" => gettext("Return item"),
            "/root/kodimas/vault/transfer" => gettext("Transfer item"),

            // "/root/kodimas/vault/request" => gettext("Request item"), // maker
            // "/root/kodimas/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/kodimas/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/kodimas/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/kodimas/report" => gettext("Reporting management for KODIMAS"),
            "/root/kodimas/report/commission" => gettext("Commission Reporting"),
            "/root/kodimas/report/commission/list" => gettext("View Commission Reporting"),
            "/root/kodimas/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/kodimas/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/kodimas/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/kodimas/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/kodimas/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/kodimas/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/kodimas/accountclosure" => gettext("Account closure management for KODIMAS"),
            "/root/kodimas/accountclosure/list" => gettext("View account closure"),
            "/root/kodimas/accountclosure/close" => gettext("Close account"),

            "/root/kodimas/pricealert" => gettext("Price alert management for KODIMAS"),
            "/root/kodimas/pricealert/list" => gettext("View price alert"),

            "/root/kodimas/sale" => gettext("Spot Order Special for KODIMAS"),

            "/root/kodimas/transfergold" => gettext("Transfer Gold for KODIMAS"),
            "/root/kodimas/transfergold/list" => gettext("View Transfer Gold"),
            "/root/kodimas/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/kodimas/promo" => gettext("Promo for KODIMAS"),
            "/root/kodimas/promo/list" => gettext("View Promo"),
            "/root/kodimas/promo/export" => gettext("Export Promo"),
            // End kodimas Permissions

             // KGOLD Affiliate Permissions
             "/root/kgoldaffi/" => gettext("KGOLD Affiliate Modules"),
             "/root/kgoldaffi/redemption" => gettext("Conversion management for KGOLD Affiliate"),
             "/root/kgoldaffi/redemption/list" => gettext("View conversion"),
             "/root/kgoldaffi/redemption/add" => gettext("Add conversion"),
             "/root/kgoldaffi/redemption/edit" => gettext("Edit conversion"),
             "/root/kgoldaffi/redemption/export" => gettext("Export conversion"),
 
             "/root/kgoldaffi/logistic" => gettext("Logistics management for KGOLD Affiliate"),
             "/root/kgoldaffi/logistic/list" => gettext("View logistics information"),
             "/root/kgoldaffi/logistic/add" => gettext("Add logistics"),
             "/root/kgoldaffi/logistic/edit" => gettext("Edit logistics"),
             "/root/kgoldaffi/logistic/delete" => gettext("Delete logistics"),
             "/root/kgoldaffi/logistic/complete" => gettext("Complete logistics"),
 
             "/root/kgoldaffi/disbursement" => gettext("Disbursement management for KGOLD Affiliate"),
             "/root/kgoldaffi/disbursement/list" => gettext("View disbursement"),
             "/root/kgoldaffi/disbursement/add" => gettext("Add disbursement"),
             "/root/kgoldaffi/disbursement/edit" => gettext("Edit disbursement"),
 
             "/root/kgoldaffi/profile" => gettext("Account holder profile management for KGOLD Affiliate"),
             "/root/kgoldaffi/profile/list" => gettext("View user profile"),
             "/root/kgoldaffi/profile/add" => gettext("Add user profile"),
             "/root/kgoldaffi/profile/edit" => gettext("Edit user profile"),
             "/root/kgoldaffi/profile/suspend" => gettext("Suspend account holder"),
             "/root/kgoldaffi/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/kgoldaffi/profile/updateLoan" => gettext("Update account holder loans"),
             "/root/kgoldaffi/profile/updateMember" => gettext("Update account holder member list"),     
             "/root/kgoldaffi/profile/activate" => gettext("Activate account holder"),

             "/root/kgoldaffi/approval" => gettext("PEP approval management for KGOLD Affiliate"),
             "/root/kgoldaffi/approval/list" => gettext("View PEP"),
             "/root/kgoldaffi/approval/approve" => gettext("Approve or reject PEP"),
 
             "/root/kgoldaffi/fpx" => gettext("FPX management for KGOLD Affiliate"),
             "/root/kgoldaffi/fpx/list" => gettext("View FPX"),
 
             "/root/kgoldaffi/ekyc" => gettext("eKYC management for KGOLD Affiliate"),
             "/root/kgoldaffi/ekyc/list" => gettext("View eKYC"),
 
             "/root/kgoldaffi/goldtransaction" => gettext("Gold transaction management for KGOLD Affiliate"),
             "/root/kgoldaffi/goldtransaction/list" => gettext("View gold transaction"),
             "/root/kgoldaffi/goldtransaction/export" => gettext("Export gold transaction statement"),
 
             "/root/kgoldaffi/storagefee" => gettext("Storage Fee management for KGOLD Affiliate"),
             "/root/kgoldaffi/storagefee/list" => gettext("View storage fee"),
             "/root/kgoldaffi/fee" => gettext("Fee management"),
             "/root/kgoldaffi/fee/list" => gettext("View fee management"),
             "/root/kgoldaffi/fee/add" => gettext("Add fee"),
             "/root/kgoldaffi/fee/edit" => gettext("Edit fee"),
 
 
             "/root/kgoldaffi/vault" => gettext("Inventory management for KGOLD Affiliate"),
             "/root/kgoldaffi/vault/list" => gettext("View inventory"),
             "/root/kgoldaffi/vault/add" => gettext("Add item to inventory"),
             "/root/kgoldaffi/vault/edit" => gettext("Edit item in inventory"),
             "/root/kgoldaffi/vault/return" => gettext("Return item"),
             "/root/kgoldaffi/vault/transfer" => gettext("Transfer item"),

            //  "/root/kgoldaffi/vault/request" => gettext("Request item"), // maker
            //  "/root/kgoldaffi/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            //  // "/root/kgoldaffi/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            //  "/root/kgoldaffi/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
             "/root/kgoldaffi/report" => gettext("Reporting management for KGOLD Affiliate"),
             "/root/kgoldaffi/report/commission" => gettext("Commission Reporting"),
             "/root/kgoldaffi/report/commission/list" => gettext("View Commission Reporting"),
             "/root/kgoldaffi/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/kgoldaffi/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/kgoldaffi/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/kgoldaffi/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/kgoldaffi/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/kgoldaffi/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
 
             "/root/kgoldaffi/accountclosure" => gettext("Account closure management for KGOLD Affiliate"),
             "/root/kgoldaffi/accountclosure/list" => gettext("View account closure"),
             "/root/kgoldaffi/accountclosure/close" => gettext("Close account"),

             "/root/kgoldaffi/pricealert" => gettext("Price alert management for KGOLD Affiliate"),
             "/root/kgoldaffi/pricealert/list" => gettext("View price alert"),
 
             "/root/kgoldaffi/sale" => gettext("Spot Order Special for KGOLD Affiliate"),

             "/root/kgoldaffi/transfergold" => gettext("Transfer Gold for KGOLD Affiliate"),
             "/root/kgoldaffi/transfergold/list" => gettext("View Transfer Gold"),
             "/root/kgoldaffi/transfergold/export" => gettext("Export Transfer Gold"),

             "/root/kgoldaffi/promo" => gettext("Promo for KGOLD Affiliate"),
             "/root/kgoldaffi/promo/list" => gettext("View Promo"),
             "/root/kgoldaffi/promo/export" => gettext("Export Promo"),
             // End KGOLD Affiliate Permissions

            // Koponas Permissions
            "/root/koponas/redemption" => gettext("Conversion management for KOPONAS"),
            "/root/koponas/redemption/list" => gettext("View conversion"),
            "/root/koponas/redemption/add" => gettext("Add conversion"),
            "/root/koponas/redemption/edit" => gettext("Edit conversion"),
            "/root/koponas/redemption/export" => gettext("Export conversion"),

            "/root/koponas/logistic" => gettext("Logistics management for KOPONAS"),
            "/root/koponas/logistic/list" => gettext("View logistics information"),
            "/root/koponas/logistic/add" => gettext("Add logistics"),
            "/root/koponas/logistic/edit" => gettext("Edit logistics"),
            "/root/koponas/logistic/delete" => gettext("Delete logistics"),
            "/root/koponas/logistic/complete" => gettext("Complete logistics"),

            "/root/koponas/disbursement" => gettext("Disbursement management for KOPONAS"),
            "/root/koponas/disbursement/list" => gettext("View disbursement"),
            "/root/koponas/disbursement/add" => gettext("Add disbursement"),
            "/root/koponas/disbursement/edit" => gettext("Edit disbursement"),

            "/root/koponas/profile" => gettext("Account holder profile management for KOPONAS"),
            "/root/koponas/profile/list" => gettext("View user profile"),
            "/root/koponas/profile/add" => gettext("Add user profile"),
            "/root/koponas/profile/edit" => gettext("Edit user profile"),
            "/root/koponas/profile/suspend" => gettext("Suspend account holder"),
            "/root/koponas/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/koponas/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/koponas/profile/updateMember" => gettext("Update account holder member list"),
            "/root/koponas/profile/activate" => gettext("Activate account holder"),

            "/root/koponas/approval" => gettext("PEP approval management for KOPONAS"),
            "/root/koponas/approval/list" => gettext("View PEP"),
            "/root/koponas/approval/approve" => gettext("Approve or reject PEP"),

            "/root/koponas/fpx" => gettext("FPX management for KOPONAS"),
            "/root/koponas/fpx/list" => gettext("View FPX"),

            "/root/koponas/ekyc" => gettext("eKYC management for KOPONAS"),
            "/root/koponas/ekyc/list" => gettext("View eKYC"),

            "/root/koponas/goldtransaction" => gettext("Gold transaction management for KOPONAS"),
            "/root/koponas/goldtransaction/list" => gettext("View gold transaction"),
            "/root/koponas/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/koponas/storagefee" => gettext("Storage Fee management for KOPONAS"),
            "/root/koponas/storagefee/list" => gettext("View storage fee"),
            "/root/koponas/fee" => gettext("Fee management"),
            "/root/koponas/fee/list" => gettext("View fee management"),
            "/root/koponas/fee/add" => gettext("Add fee"),
            "/root/koponas/fee/edit" => gettext("Edit fee"),


            "/root/koponas/vault" => gettext("Inventory management for KOPONAS"),
            "/root/koponas/vault/list" => gettext("View inventory"),
            "/root/koponas/vault/add" => gettext("Add item to inventory"),
            "/root/koponas/vault/edit" => gettext("Edit item in inventory"),
            "/root/koponas/vault/return" => gettext("Return item"),
            "/root/koponas/vault/transfer" => gettext("Transfer item"),

            // "/root/koponas/vault/request" => gettext("Request item"), // maker
            // "/root/koponas/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/koponas/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/koponas/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/koponas/report" => gettext("Reporting management for KOPONAS"),
            "/root/koponas/report/commission" => gettext("Commission Reporting"),
            "/root/koponas/report/commission/list" => gettext("View Commission Reporting"),
            "/root/koponas/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/koponas/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/koponas/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/koponas/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/koponas/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/koponas/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/koponas/accountclosure" => gettext("Account closure management for KOPONAS"),
            "/root/koponas/accountclosure/list" => gettext("View account closure"),
            "/root/koponas/accountclosure/close" => gettext("Close account"),

            "/root/koponas/pricealert" => gettext("Price alert management for KOPONAS"),
            "/root/koponas/pricealert/list" => gettext("View price alert"),

            "/root/koponas/sale" => gettext("Spot Order Special for KOPONAS"),

            "/root/koponas/transfergold" => gettext("Transfer Gold for KOPONAS"),
            "/root/koponas/transfergold/list" => gettext("View Transfer Gold"),
            "/root/koponas/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/koponas/promo" => gettext("Promo for KOPONAS"),
            "/root/koponas/promo/list" => gettext("View Promo"),
            "/root/koponas/promo/export" => gettext("Export Promo"),
            // End KOPONAS Permissions

             // noor permissions
             "/root/noor/redemption" => gettext("Conversion management"),
             "/root/noor/redemption/list" => gettext("View conversion"),
             "/root/noor/redemption/add" => gettext("Add conversion"),
             "/root/noor/redemption/edit" => gettext("Edit conversion"),
             "/root/noor/redemption/export" => gettext("Export conversion"),
 
             "/root/noor/logistic" => gettext("Logistics management for Noor"),
             "/root/noor/logistic/list" => gettext("View logistics information for Noor"),
             "/root/noor/logistic/add" => gettext("Add logistics for Noor"),
             "/root/noor/logistic/edit" => gettext("Edit logistics for Noor"),
             "/root/noor/logistic/delete" => gettext("Delete logistics for Noor"),
             "/root/noor/logistic/complete" => gettext("Complete logistics for Noor"),
 
             "/root/noor/disbursement" => gettext("Disbursement management"),
             "/root/noor/disbursement/list" => gettext("View disbursement"),
             "/root/noor/disbursement/add" => gettext("Add disbursement"),
             "/root/noor/disbursement/edit" => gettext("Edit disbursement"),
 
             "/root/noor/profile" => gettext("Account holder profile management"),
             "/root/noor/profile/list" => gettext("View user profile"),
             "/root/noor/profile/add" => gettext("Add user profile"),
             "/root/noor/profile/edit" => gettext("Edit user profile"),
             "/root/noor/profile/suspend" => gettext("Suspend account holder"),
             "/root/noor/profile/unsuspend" => gettext("Unsuspend account holder"),
             "/root/noor/profile/activate" => gettext("Activate account holder"),

             "/root/noor/approval" => gettext("PEP approval management"),
             "/root/noor/approval/list" => gettext("View PEP"),
             "/root/noor/approval/approve" => gettext("Approve or reject PEP"),
 
             "/root/noor/fpx" => gettext("FPX management"),
             "/root/noor/fpx/list" => gettext("View FPX"),
 
             "/root/noor/ekyc" => gettext("eKYC management"),
             "/root/noor/ekyc/list" => gettext("View eKYC"),
 
             "/root/noor/goldtransaction" => gettext("Gold transaction management"),
             "/root/noor/goldtransaction/list" => gettext("View gold transaction"),
             "/root/noor/goldtransaction/export" => gettext("Export gold transaction statement"),
 
             "/root/noor/storagefee" => gettext("Storage Fee management"),
             "/root/noor/storagefee/list" => gettext("View storage fee"),
             "/root/noor/fee" => gettext("Fee management"),
             "/root/noor/fee/list" => gettext("View fee management"),
             "/root/noor/fee/add" => gettext("Add fee"),
             "/root/noor/fee/edit" => gettext("Edit fee"),
 
 
             "/root/noor/vault" => gettext("Inventory management"),
             "/root/noor/vault/list" => gettext("View inventory"),
             "/root/noor/vault/add" => gettext("Add item to inventory"),
             "/root/noor/vault/edit" => gettext("Edit item in inventory"),
             "/root/noor/vault/return" => gettext("Return item"),
             "/root/noor/vault/transfer" => gettext("Transfer item"),
 
             // "/root/noor/vault/request" => gettext("Request item"), // maker
             // "/root/noor/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
             // // "/root/bmmb/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
             // "/root/noor/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
 
 
             "/root/noor/report" => gettext("Reporting management"),
             "/root/noor/report/commission" => gettext("Commission Reporting"),
             "/root/noor/report/commission/list" => gettext("View Commission Reporting"),
             "/root/noor/report/storagefee" => gettext("Storage Fee Reporting"),
             "/root/noor/report/storagefee/list" => gettext("View Storage Fee Reporting"),
             "/root/noor/report/adminfee" => gettext("Admin Fee Reporting"),
             "/root/noor/report/adminfee/list" => gettext("View Admin Fee Reporting"),
             "/root/noor/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
             "/root/noor/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
 
             "/root/noor/accountclosure" => gettext("Account closure management"),
             "/root/noor/accountclosure/list" => gettext("View account closure"),
             "/root/noor/accountclosure/close" => gettext("Close account"),

             "/root/noor/pricealert" => gettext("Price alert management"),
             "/root/noor/pricealert/list" => gettext("View price alert"),
 
             "/root/noor/sale" => gettext("Spot Order Special for Noor"),
 
             "/root/noor/transfergold" => gettext("Transfer Gold for Noor"),
             "/root/noor/transfergold/list" => gettext("View Transfer Gold"),
             "/root/noor/transfergold/export" => gettext("Export Transfer Gold"),
 
             "/root/noor/promo" => gettext("Promo for Noor"),
             "/root/noor/promo/list" => gettext("View Promo"),
             "/root/noor/promo/export" => gettext("Export Promo"),
             // End noor permissions

             // Waqaf Permissions
            "/root/waqaf/redemption" => gettext("Conversion management for WAQAF"),
            "/root/waqaf/redemption/list" => gettext("View conversion"),
            "/root/waqaf/redemption/add" => gettext("Add conversion"),
            "/root/waqaf/redemption/edit" => gettext("Edit conversion"),
            "/root/waqaf/redemption/export" => gettext("Export conversion"),

            "/root/waqaf/logistic" => gettext("Logistics management for WAQAF"),
            "/root/waqaf/logistic/list" => gettext("View logistics information"),
            "/root/waqaf/logistic/add" => gettext("Add logistics"),
            "/root/waqaf/logistic/edit" => gettext("Edit logistics"),
            "/root/waqaf/logistic/delete" => gettext("Delete logistics"),
            "/root/waqaf/logistic/complete" => gettext("Complete logistics"),

            "/root/waqaf/disbursement" => gettext("Disbursement management for WAQAF"),
            "/root/waqaf/disbursement/list" => gettext("View disbursement"),
            "/root/waqaf/disbursement/add" => gettext("Add disbursement"),
            "/root/waqaf/disbursement/edit" => gettext("Edit disbursement"),

            "/root/waqaf/profile" => gettext("Account holder profile management for WAQAF"),
            "/root/waqaf/profile/list" => gettext("View user profile"),
            "/root/waqaf/profile/add" => gettext("Add user profile"),
            "/root/waqaf/profile/edit" => gettext("Edit user profile"),
            "/root/waqaf/profile/suspend" => gettext("Suspend account holder"),
            "/root/waqaf/profile/unsuspend" => gettext("Unsuspend account holder"),
            "/root/waqaf/profile/updateLoan" => gettext("Update account holder loans"),
            "/root/waqaf/profile/updateMember" => gettext("Update account holder member list"),
            "/root/waqaf/profile/activate" => gettext("Activate account holder"),

            "/root/waqaf/approval" => gettext("PEP approval management for WAQAF"),
            "/root/waqaf/approval/list" => gettext("View PEP"),
            "/root/waqaf/approval/approve" => gettext("Approve or reject PEP"),

            "/root/waqaf/fpx" => gettext("FPX management for WAQAF"),
            "/root/waqaf/fpx/list" => gettext("View FPX"),

            "/root/waqaf/ekyc" => gettext("eKYC management for WAQAF"),
            "/root/waqaf/ekyc/list" => gettext("View eKYC"),

            "/root/waqaf/goldtransaction" => gettext("Gold transaction management for WAQAF"),
            "/root/waqaf/goldtransaction/list" => gettext("View gold transaction"),
            "/root/waqaf/goldtransaction/export" => gettext("Export gold transaction statement"),

            "/root/waqaf/storagefee" => gettext("Storage Fee management for WAQAF"),
            "/root/waqaf/storagefee/list" => gettext("View storage fee"),
            "/root/waqaf/fee" => gettext("Fee management"),
            "/root/waqaf/fee/list" => gettext("View fee management"),
            "/root/waqaf/fee/add" => gettext("Add fee"),
            "/root/waqaf/fee/edit" => gettext("Edit fee"),


            "/root/waqaf/vault" => gettext("Inventory management for WAQAF"),
            "/root/waqaf/vault/list" => gettext("View inventory"),
            "/root/waqaf/vault/add" => gettext("Add item to inventory"),
            "/root/waqaf/vault/edit" => gettext("Edit item in inventory"),
            "/root/waqaf/vault/return" => gettext("Return item"),
            "/root/waqaf/vault/transfer" => gettext("Transfer item"),

            // "/root/waqaf/vault/request" => gettext("Request item"), // maker
            // "/root/waqaf/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
            // // "/root/waqaf/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
            // "/root/waqaf/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)

            "/root/waqaf/report" => gettext("Reporting management for WAQAF"),
            "/root/waqaf/report/commission" => gettext("Commission Reporting"),
            "/root/waqaf/report/commission/list" => gettext("View Commission Reporting"),
            "/root/waqaf/report/storagefee" => gettext("Storage Fee Reporting"),
            "/root/waqaf/report/storagefee/list" => gettext("View Storage Fee Reporting"),
            "/root/waqaf/report/adminfee" => gettext("Admin Fee Reporting"),
            "/root/waqaf/report/adminfee/list" => gettext("View Admin Fee Reporting"),
            "/root/waqaf/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
            "/root/waqaf/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),


            "/root/waqaf/accountclosure" => gettext("Account closure management for WAQAF"),
            "/root/waqaf/accountclosure/list" => gettext("View account closure"),
            "/root/waqaf/accountclosure/close" => gettext("Close account"),

            "/root/waqaf/pricealert" => gettext("Price alert management for WAQAF"),
            "/root/waqaf/pricealert/list" => gettext("View price alert"),

            "/root/waqaf/sale" => gettext("Spot Order Special for WAQAF"),

            "/root/waqaf/transfergold" => gettext("Transfer Gold for WAQAF"),
            "/root/waqaf/transfergold/list" => gettext("View Transfer Gold"),
            "/root/waqaf/transfergold/export" => gettext("Export Transfer Gold"),

            "/root/waqaf/promo" => gettext("Promo for WAQAF"),
            "/root/waqaf/promo/list" => gettext("View Promo"),
            "/root/waqaf/promo/export" => gettext("Export Promo"),
            // End WAQAF Permissions

              // KASIH Permissions
              "/root/kasih/redemption" => gettext("Conversion management for KASIH"),
              "/root/kasih/redemption/list" => gettext("View conversion"),
              "/root/kasih/redemption/add" => gettext("Add conversion"),
              "/root/kasih/redemption/edit" => gettext("Edit conversion"),
              "/root/kasih/redemption/export" => gettext("Export conversion"),
  
              "/root/kasih/logistic" => gettext("Logistics management for KASIH"),
              "/root/kasih/logistic/list" => gettext("View logistics information"),
              "/root/kasih/logistic/add" => gettext("Add logistics"),
              "/root/kasih/logistic/edit" => gettext("Edit logistics"),
              "/root/kasih/logistic/delete" => gettext("Delete logistics"),
              "/root/kasih/logistic/complete" => gettext("Complete logistics"),
  
              "/root/kasih/disbursement" => gettext("Disbursement management for KASIH"),
              "/root/kasih/disbursement/list" => gettext("View disbursement"),
              "/root/kasih/disbursement/add" => gettext("Add disbursement"),
              "/root/kasih/disbursement/edit" => gettext("Edit disbursement"),
  
              "/root/kasih/profile" => gettext("Account holder profile management for KASIH"),
              "/root/kasih/profile/list" => gettext("View user profile"),
              "/root/kasih/profile/add" => gettext("Add user profile"),
              "/root/kasih/profile/edit" => gettext("Edit user profile"),
              "/root/kasih/profile/suspend" => gettext("Suspend account holder"),
              "/root/kasih/profile/unsuspend" => gettext("Unsuspend account holder"),
              "/root/kasih/profile/updateLoan" => gettext("Update account holder loans"),
              "/root/kasih/profile/updateMember" => gettext("Update account holder member list"),
              "/root/kasih/profile/activate" => gettext("Activate account holder"),
  
              "/root/kasih/approval" => gettext("PEP approval management for KASIH"),
              "/root/kasih/approval/list" => gettext("View PEP"),
              "/root/kasih/approval/approve" => gettext("Approve or reject PEP"),
  
              "/root/kasih/fpx" => gettext("FPX management for KASIH"),
              "/root/kasih/fpx/list" => gettext("View FPX"),
  
              "/root/kasih/ekyc" => gettext("eKYC management for KASIH"),
              "/root/kasih/ekyc/list" => gettext("View eKYC"),
  
              "/root/kasih/goldtransaction" => gettext("Gold transaction management for KASIH"),
              "/root/kasih/goldtransaction/list" => gettext("View gold transaction"),
              "/root/kasih/goldtransaction/export" => gettext("Export gold transaction statement"),
  
              "/root/kasih/storagefee" => gettext("Storage Fee management for KASIH"),
              "/root/kasih/storagefee/list" => gettext("View storage fee"),
              "/root/kasih/fee" => gettext("Fee management"),
              "/root/kasih/fee/list" => gettext("View fee management"),
              "/root/kasih/fee/add" => gettext("Add fee"),
              "/root/kasih/fee/edit" => gettext("Edit fee"),
  
  
              "/root/kasih/vault" => gettext("Inventory management for KASIH"),
              "/root/kasih/vault/list" => gettext("View inventory"),
              "/root/kasih/vault/add" => gettext("Add item to inventory"),
              "/root/kasih/vault/edit" => gettext("Edit item in inventory"),
              "/root/kasih/vault/return" => gettext("Return item"),
              "/root/kasih/vault/transfer" => gettext("Transfer item"),
  
              // "/root/kasih/vault/request" => gettext("Request item"), // maker
              // "/root/kasih/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
              // // "/root/kasih/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
              // "/root/kasih/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
  
              "/root/kasih/report" => gettext("Reporting management for KASIH"),
              "/root/kasih/report/commission" => gettext("Commission Reporting"),
              "/root/kasih/report/commission/list" => gettext("View Commission Reporting"),
              "/root/kasih/report/storagefee" => gettext("Storage Fee Reporting"),
              "/root/kasih/report/storagefee/list" => gettext("View Storage Fee Reporting"),
              "/root/kasih/report/adminfee" => gettext("Admin Fee Reporting"),
              "/root/kasih/report/adminfee/list" => gettext("View Admin Fee Reporting"),
              "/root/kasih/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
              "/root/kasih/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
  
  
              "/root/kasih/accountclosure" => gettext("Account closure management for KASIH"),
              "/root/kasih/accountclosure/list" => gettext("View account closure"),
              "/root/kasih/accountclosure/close" => gettext("Close account"),
  
              "/root/kasih/pricealert" => gettext("Price alert management for KASIH"),
              "/root/kasih/pricealert/list" => gettext("View price alert"),
  
              "/root/kasih/sale" => gettext("Spot Order Special for KASIH"),
  
              "/root/kasih/transfergold" => gettext("Transfer Gold for KASIH"),
              "/root/kasih/transfergold/list" => gettext("View Transfer Gold"),
              "/root/kasih/transfergold/export" => gettext("Export Transfer Gold"),
  
              "/root/kasih/promo" => gettext("Promo for KASIH"),
              "/root/kasih/promo/list" => gettext("View Promo"),
              "/root/kasih/promo/export" => gettext("Export Promo"),
              // End KASIH Permissions
  

              // POSARRAHNU Permissions
              "/root/posarrahnu/redemption" => gettext("Conversion management for POSARRAHNU"),
              "/root/posarrahnu/redemption/list" => gettext("View conversion"),
              "/root/posarrahnu/redemption/add" => gettext("Add conversion"),
              "/root/posarrahnu/redemption/edit" => gettext("Edit conversion"),
              "/root/posarrahnu/redemption/export" => gettext("Export conversion"),
  
              "/root/posarrahnu/logistic" => gettext("Logistics management for POSARRAHNU"),
              "/root/posarrahnu/logistic/list" => gettext("View logistics information"),
              "/root/posarrahnu/logistic/add" => gettext("Add logistics"),
              "/root/posarrahnu/logistic/edit" => gettext("Edit logistics"),
              "/root/posarrahnu/logistic/delete" => gettext("Delete logistics"),
              "/root/posarrahnu/logistic/complete" => gettext("Complete logistics"),
  
              "/root/posarrahnu/disbursement" => gettext("Disbursement management for POSARRAHNU"),
              "/root/posarrahnu/disbursement/list" => gettext("View disbursement"),
              "/root/posarrahnu/disbursement/add" => gettext("Add disbursement"),
              "/root/posarrahnu/disbursement/edit" => gettext("Edit disbursement"),
  
              "/root/posarrahnu/profile" => gettext("Account holder profile management for POSARRAHNU"),
              "/root/posarrahnu/profile/list" => gettext("View user profile"),
              "/root/posarrahnu/profile/add" => gettext("Add user profile"),
              "/root/posarrahnu/profile/edit" => gettext("Edit user profile"),
              "/root/posarrahnu/profile/suspend" => gettext("Suspend account holder"),
              "/root/posarrahnu/profile/unsuspend" => gettext("Unsuspend account holder"),
              "/root/posarrahnu/profile/updateLoan" => gettext("Update account holder loans"),
              "/root/posarrahnu/profile/updateMember" => gettext("Update account holder member list"),
              "/root/posarrahnu/profile/activate" => gettext("Activate account holder"),

              "/root/posarrahnu/approval" => gettext("PEP approval management for POSARRAHNU"),
              "/root/posarrahnu/approval/list" => gettext("View PEP"),
              "/root/posarrahnu/approval/approve" => gettext("Approve or reject PEP"),
  
              "/root/posarrahnu/fpx" => gettext("FPX management for POSARRAHNU"),
              "/root/posarrahnu/fpx/list" => gettext("View FPX"),
  
              "/root/posarrahnu/ekyc" => gettext("eKYC management for POSARRAHNU"),
              "/root/posarrahnu/ekyc/list" => gettext("View eKYC"),
  
              "/root/posarrahnu/goldtransaction" => gettext("Gold transaction management for POSARRAHNU"),
              "/root/posarrahnu/goldtransaction/list" => gettext("View gold transaction"),
              "/root/posarrahnu/goldtransaction/export" => gettext("Export gold transaction statement"),
  
              "/root/posarrahnu/storagefee" => gettext("Storage Fee management for POSARRAHNU"),
              "/root/posarrahnu/storagefee/list" => gettext("View storage fee"),
              "/root/posarrahnu/fee" => gettext("Fee management"),
              "/root/posarrahnu/fee/list" => gettext("View fee management"),
              "/root/posarrahnu/fee/add" => gettext("Add fee"),
              "/root/posarrahnu/fee/edit" => gettext("Edit fee"),
  
  
              "/root/posarrahnu/vault" => gettext("Inventory management for POSARRAHNU"),
              "/root/posarrahnu/vault/list" => gettext("View inventory"),
              "/root/posarrahnu/vault/add" => gettext("Add item to inventory"),
              "/root/posarrahnu/vault/edit" => gettext("Edit item in inventory"),
              "/root/posarrahnu/vault/return" => gettext("Return item"),
              "/root/posarrahnu/vault/transfer" => gettext("Transfer item"),
  
              // "/root/posarrahnu/vault/request" => gettext("Request item"), // maker
              // "/root/posarrahnu/vault/approve" => gettext("Approve item"), // checker 1 ( level 1)
              // // "/root/posarrahnu/vault/confirm" => gettext("Confirm item"), // checker 2 ( level 2)
              // "/root/posarrahnu/vault/complete" => gettext("Complete item"), // checker 2 ( level 2)
  
              "/root/posarrahnu/report" => gettext("Reporting management for POSARRAHNU"),
              "/root/posarrahnu/report/commission" => gettext("Commission Reporting"),
              "/root/posarrahnu/report/commission/list" => gettext("View Commission Reporting"),
              "/root/posarrahnu/report/storagefee" => gettext("Storage Fee Reporting"),
              "/root/posarrahnu/report/storagefee/list" => gettext("View Storage Fee Reporting"),
              "/root/posarrahnu/report/adminfee" => gettext("Admin Fee Reporting"),
              "/root/posarrahnu/report/adminfee/list" => gettext("View Admin Fee Reporting"),
              "/root/posarrahnu/report/monthlysummary" => gettext("Monthly Transaction Summary Reporting"),
              "/root/posarrahnu/report/monthlysummary/list" => gettext("View Monthly Transaction Summary Reporting"),
  
  
              "/root/posarrahnu/accountclosure" => gettext("Account closure management for POSARRAHNU"),
              "/root/posarrahnu/accountclosure/list" => gettext("View account closure"),
              "/root/posarrahnu/accountclosure/close" => gettext("Close account"),
  
              "/root/posarrahnu/pricealert" => gettext("Price alert management for POSARRAHNU"),
              "/root/posarrahnu/pricealert/list" => gettext("View price alert"),
  
              "/root/posarrahnu/sale" => gettext("Spot Order Special for POSARRAHNU"),
  
              "/root/posarrahnu/transfergold" => gettext("Transfer Gold for POSARRAHNU"),
              "/root/posarrahnu/transfergold/list" => gettext("View Transfer Gold"),
              "/root/posarrahnu/transfergold/export" => gettext("Export Transfer Gold"),
  
              "/root/posarrahnu/promo" => gettext("Promo for POSARRAHNU"),
              "/root/posarrahnu/promo/list" => gettext("View Promo"),
              "/root/posarrahnu/promo/export" => gettext("Export Promo"),
              // End POSARRAHNU Permissions
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

    function isHuntoo()
    {
        return defined('SNAPAPP_MODE_HUNTOO');
    }

    function isBackOffice()
    {
        return defined('SNAPAPP_MODE_BACKOFFICE_OPERATOR') || defined('SNAPAPP_MODE_BACKOFFICE_PARTNER');
    }

    function isBackOfficeForPartner()
    {
        return defined('SNAPAPP_MODE_BACKOFFICE_PARTNER');
    }

    function isBackOfficeForOperator()
    {
        return defined('SNAPAPP_MODE_BACKOFFICE_OPERATOR');
    }
}
?>
