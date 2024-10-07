<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;


/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 27-May-2021
 */
class MyGtpPartnerSettingsNotInitialized extends ApiException
{
    protected const ERR_MYGTPPARTNERSETTINGSNOTINITIALIZED = 'Please initialize partner settings for partner {code}.';
}

?>