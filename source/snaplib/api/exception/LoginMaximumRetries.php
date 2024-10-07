<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\exception;


/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 13-Oct-2020
 */
class LoginMaximumRetries extends ApiException
{
    protected const ERR_LOGINMAXIMUMRETRIES = 'You have reached {failTimes} times login attempts. Please proceed to "Reset Password"  action to login the account.';
}

?>