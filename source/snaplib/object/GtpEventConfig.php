<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\object;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\IEventConfig;

/**
 *
 * This class implements the IEventConfig to provide more customisable configuration for different
 * applications
 *
 * @author Devon
 * @version 1.0
 * @created 2017/8/25 10:24 AM
 */
class GtpEventConfig implements IEventConfig {
    Use \Snap\TLogging;

    private $eventAction = array();
    private $eventModules = array();
    private $eventGroupTypes = array();
    private $sessionGroupTypeId = null;
    private $sessionGroupId = null;

    /**
     * Constructor  Creates a new object
     * @access public
     * @param
     * @return
     *
     * @param
     */
    function __construct() {

    }

    /**
     * @return Array[]   Expects to return an array of ['id', 'name', 'desc'] values representing the actions that is to be taken.
     *                   The data in this map is primarily used for displaying to the user what actions is a particular event
     *                   associated to.
     */
    function getEventActionMap($app) {
        $this->eventAction = array(
            array( 'id' => 0, 'name' => 'none', 'desc' => 'None' ),
            array( 'id' => 1, 'name' => 'new', 'desc' => 'New' ),
            array( 'id' => 2, 'name' => 'edit', 'desc' => 'Edit' ),
            array( 'id' => 3, 'name' => 'delete', 'desc' => 'Delete' ),
            array( 'id' => 4, 'name' => 'freeze', 'desc' => 'Freeze' ),
            array( 'id' => 5, 'name' => 'unfreeze', 'desc' => 'Unfreeze' ),
            array( 'id' => 6, 'name' => 'approve', 'desc' => 'Approve' ),
            array( 'id' => 7, 'name' => 'reject', 'desc' => 'Reject' ),
            array( 'id' => 8, 'name' => 'verify', 'desc' => 'Verify' ),
            array( 'id' => 9, 'name' => 'assign', 'desc' => 'Assign' ),
            array( 'id' => 10, 'name' => 'cancel', 'desc' => 'Cancel' ),
            array( 'id' => 11, 'name' => 'other', 'desc' => 'Other' ),
            array( 'id' => 12, 'name' => 'reverse', 'desc' => 'Reverse' ),
            array( 'id' => 13, 'name' => 'confirm', 'desc' => 'Confirm' ),
            array( 'id' => 14, 'name' => 'aperatorapprove', 'desc' => 'Operator Approve' ),
            array( 'id' => 15, 'name' => 'operatorreject', 'desc' => 'Operator Reject' ),
            array( 'id' => 16, 'name' => 'changerequest', 'desc' => 'Change Request' ),
            array( 'id' => 17, 'name' => 'print', 'desc' => 'Print' ),
        );

        return $this->eventAction;
    }

    /**
     * @return Array[]   Expects to return an array of ['id', 'name', 'desc'] values representing the modules that is to be taken.
     *                   The data in this map is primarily used for displaying to the user what actions is a particular event
     *                   associated to.
     */
    function getEventModuleMap($app) {
        $this->eventModules = array(
            // home
            array( 'id' => 1, 'module' => 'Digital', 'module_desc' => 'Digital', 'name' => 'digiorder', 'desc' => 'Digital Order'),
            array( 'id' => 2, 'module' => 'Digital', 'module_desc' => 'Digital', 'name' => 'digifutureorder', 'desc' => 'Digital Future Order'),
            array( 'id' => 3, 'module' => 'Digital', 'module_desc' => 'Digital', 'name' => 'digivault', 'desc' => 'Vault Management'),
            array( 'id' => 4, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'phyorder', 'desc' => 'Physical Orders'),
            array( 'id' => 5, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'phyfutureorder', 'desc' => 'Physical Future Orders'),
            array( 'id' => 6, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'phygrn', 'desc' => 'Goods receive note'),
            array( 'id' => 7, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'replendish', 'desc' => 'Replendishment'),
            array( 'id' => 8, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'redemption', 'desc' => 'Redemption'),
            array( 'id' => 9, 'module' => 'Physical', 'module_desc' => 'Physical', 'name' => 'logistic', 'desc' => 'Logistic'),
            array( 'id' => 10, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'pricestream', 'desc' => 'Price Stream'),
            array( 'id' => 11, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'pricevalidation', 'desc' => 'Price validation'),
            array( 'id' => 12, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'partner', 'desc' => 'Partner'),
            array( 'id' => 13, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'product', 'desc' => 'Product'),
            array('id' => 14, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'tradingschedule', 'desc' => 'Trading schedule'),
            array( 'id' => 99, 'module' => 'system_settings', 'module_desc' => 'Digital', 'name' => 'eventtesting', 'desc' => 'Event Testing'),

            // array( 'id' => 15, 'module' => 'reports', 'module_desc' => 'Reports', 'name' => 'reports', 'desc' => 'Reports'),
            // // system settings
            // array( 'id' => 16, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'tag_setting', 'desc' => 'Tag Setting'),
            // array( 'id' => 17, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'branch_setting', 'desc' => 'Branch Setting'),
            // array( 'id' => 18, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'assetcat_setting', 'desc' => 'Asset Category Setting'),
            // array( 'id' => 19, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'inventorycat_setting', 'desc' => 'Inventory Category Setting'),
            // array( 'id' => 20, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'servicecat_setting', 'desc' => 'Service Category Setting'),
            // array( 'id' => 21, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'treatmentpkg_setting', 'desc' => 'Treatment Package Setting'),
            // array( 'id' => 22, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'sponsor_setting', 'desc' => 'Sponsor Setting'),
            // array( 'id' => 23, 'module' => 'system_settings', 'module_desc' => 'System Settings', 'name' => 'vendor_setting', 'desc' => 'Vendor Setting'),
            // // event
            // array( 'id' => 24, 'module' => 'event', 'module_desc' => 'Event', 'name' => 'event_template', 'desc' => 'Event Template'),
            // array( 'id' => 25, 'module' => 'event', 'module_desc' => 'Event', 'name' => 'event_subsription', 'desc' => 'Event Subscription Setting'),
            // array( 'id' => 26, 'module' => 'event', 'module_desc' => 'Event', 'name' => 'event_log', 'desc' => 'Event Log Setting'),
            // // manage access
            // array( 'id' => 27, 'module' => 'manage_access', 'module_desc' => 'Manage Access', 'name' => 'users', 'desc' => 'Users'),
            // array( 'id' => 28, 'module' => 'manage_access', 'module_desc' => 'Manage Access', 'name' => 'roles', 'desc' => 'Roles'),
            // array( 'id' => 29, 'module' => 'manage_access', 'module_desc' => 'Manage Access', 'name' => 'restrict_ip', 'desc' => 'Restrict IP'),
        );

        return $this->eventModules;
    }

    /**
     * @return Array[]   Expects to return an array of ['id', 'name'] values representing the group type that are available
     *                   in the system.  The group type is a category used to differentiate users within the system.  E.g.  partnertype / branch
     */
    function getEventGroupTypeMap($app) {
        $this->eventGroupTypes = array(
            array('id' => 0, 'name' => 'Operator', 'desc' => 'Operator'),
            array('id' => 1, 'name' => 'Partner', 'desc' => 'Merchant'),
            array('id' => 2, 'name' => 'Trader', 'desc' => 'Trader'),
            array('id' => 3, 'name' => 'Sales', 'desc' => 'Salesman'),
        );

        return $this->eventGroupTypes;
    }

    /**
     * @return Array[]   Returns array of the ['id', 'object'] representing the event procesor available to process event.  Triggers that are available
     *                    without its event processor can not be successfully processed.
     */
    function getEventProcessor($app) {
        /**  TO BE IMPLEMENTED */
    }

    /**
     * Returns the category of user
     * @return int  The ID of the group type (in the above map) that the current session user belongs to
     */
    function getSessionGroupTypeId($app) {
        $this->getEventGroupTypeMap($app);
        if ($app->getUsersession()->getUser()->partnerid != null) {
            $this->sessionGroupTypeId = $this->eventGroupTypes[1]['id'];
        } else {
            $this->sessionGroupTypeId = $this->eventGroupTypes[0]['id'];
        }
        return $this->sessionGroupTypeId;
    }

    /**
     * Returns the category of user
     * @return int  The ID of the group that the current session user belongs to
     */
    function getSessionGroupId($app) {
        if ($app->getUsersession()->getUser()->partnerid != null) $this->sessionGroupId = $app->getUsersession()->getUser()->partnerid;
        else $this->sessionGroupId = 0;

        return $this->sessionGroupId;
    }


}
?>
