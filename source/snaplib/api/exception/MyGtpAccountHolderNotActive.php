<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;


/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 05-Aug-2021
 */
class MyGtpAccountHolderNotActive extends ApiException
{
    protected const ERR_MYGTPACCOUNTHOLDERNOTACTIVE = 'Account holder is not active. {message}';
}

?>