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
 * @created 25-Jan-2021
 */
class MyGtpAccountHolderNotExist extends ApiException
{
    protected const ERR_MYGTPACCOUNTHOLDERNOTEXIST = 'User does not exist. {message}';
}

?>