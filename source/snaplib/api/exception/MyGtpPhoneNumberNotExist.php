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
 * @created 21-Apr-2021
 */
class MyGtpPhoneNumberNotExist extends ApiException
{
    protected const ERR_MYGTPPHONENUMBERNOTEXIST = 'Account with phone number ({phone_number}) does not exist.';
}

?>