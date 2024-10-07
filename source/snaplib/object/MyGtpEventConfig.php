<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\IObservation;

/**
 *
 * This class extends the GtpEventConfig class
 *
 * @author Azam <azam@silverstream.my>
 *
 * @created 2020/10/22 6:14 PM
 */
class MyGtpEventConfig extends GtpEventConfig
{
    const EVENT_REGISTER_ACCOUNT          = 'REGISTRATION'; // Welcome email
    const EVENT_ACCOUNT_VERIFICATION      = 'ACCOUNT_VERIFICATION'; // Welcome email with verification link
    const EVENT_PHONE_VERIFICATION        = 'PHONE_VERIFICATION'; // Phone verification code
    const EVENT_RESET_PASSWORD            = 'RESET_PASSWORD';
    const EVENT_FORGOT_PASSWORD           = 'FORGOT_PASSWORD';
    const EVENT_FORGOT_PASSWORD_PHONE     = 'FORGOT_PASSWORD_PHONE';
    const EVENT_EKYC_RESULT_PASSED        = 'EKYC_RESULT_PASSED';
    const EVENT_EKYC_RESULT_FAILED        = 'EKYC_RESULT_FAILED';
    const EVENT_EKYC_PROVIDER_ERROR       = 'EKYC_PROVIDER_ERROR';
    const EVENT_EKYC_VERIFICATION_FAILED  = 'EKYC_VERIFICATION_FAILED'; // Submission failed
    const EVENT_AMLA_FAILED               = 'AMLA_FAILED';
    const EVENT_EKYC_PASSED               = 'EKYC_PASSED'; // Operator approve EKYC
    const EVENT_PEP_PASSED                = 'PEP_PASSED'; // Operator approve PEP
    const EVENT_PEP_FAILED                = 'PEP_FAILED'; // Operator reject PEP
    const EVENT_PRICE_ALERT_MATCH         = 'PRICE_ALERT_MATCH'; // Submission failed
    const EVENT_GOLDTRANSACTION_CONFIRMED = 'GOLDTRANSACTION_CONFIRMED'; // Gold transaction confirmed
    const EVENT_GOLDTRANSACTION_FAIL      = 'GOLDTRANSACTION_FAIL'; // Gold transaction failed
    const EVENT_GOLDTRANSACTION_EDIT_REF  = 'GOLDTRANSACTION_EDIT_REF'; // Gold transaction edit refno
    const EVENT_CONVERSION_CREATE         = 'CONVERSION_CREATE';
    const EVENT_CONVERSION_CONFIRMED      = 'CONVERSION_CONFIRMED';
    const EVENT_CONVERSION_COMPLETED      = 'CONVERSION_COMPLETED';
    const EVENT_FORGOT_PIN                = 'FORGOT_PIN';
    const EVENT_RESET_PIN                 = 'RESET_PIN';
    const EVENT_DISBURSEMENT_CONFIRMED    = 'DISBURSEMENT_CONFIRMED';
    const EVENT_ACCOUNTHOLDER_LOAN_FAILED = 'ACCOUNTHOLDER_LOAN_FAILED';

    const EVENT_CLOSE_ACCOUNT             = 'CLOSE_ACCOUNT';
    const EVENT_CLOSE_DORMANT_ACCOUNT     = 'CLOSE_DORMANT_ACCOUNT';
    
    const EVENT_REMIND_DORMANT_ACCOUNT    = 'REMIND_DORMANT_ACCOUNT';
    const EVENT_REMIND_INCOMPLETE_PROFILE = 'REMIND_INCOMPLETE_PROFILE';
    const EVENT_SEND_PUSH                 = 'SEND_PUSH';

    // OTC EVENTS
    const EVENT_REGISTRATION_INFORMATION      = 'REGISTRATION_INFORMATION'; // Welcome email with otc registration information

    private $eventModules = array();

    /**
     * Constructor  Creates a new object
     * @access public
     * @param
     * @return
     *
     * @param
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Array[]   Expects to return an array of ['id', 'name'] values representing the group type that are available
     *                   in the system.  The group type is a category used to differentiate users within the system.  E.g.  partnertype / branch
     */
    public function getEventGroupTypeMap($app)
    {
        $this->eventGroupTypes = array(
            array('id' => 100, 'name' => 'Account Holder', 'desc' => 'Account Holder'),
        );

        return array_merge(parent::getEventGroupTypeMap($app), $this->eventGroupTypes);
    }

    /**
     * @return Array[]   Expects to return an array of ['id', 'name', 'desc'] values representing the modules that is to be taken.
     *                   The data in this map is primarily used for displaying to the user what actions is a particular event
     *                   associated to.
     */
    public function getEventModuleMap($app)
    {
        $this->eventModules = array(
            array('id' => 100, 'module' => 'system_settings', 'module_desc' => 'Account', 'name' => 'myaccountholder', 'desc' => 'Account Holder'),
        );

        return array_merge(parent::getEventModuleMap($app), $this->eventModules);
    }
}
