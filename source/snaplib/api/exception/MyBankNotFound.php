<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */

namespace Snap\api\exception;

use Snap\api\exception\ApiException;

/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 14-Oct-2020 4:47:00 PM
 */
class MyBankNotFound extends ApiException
{
    protected const ERR_MYBANKNOTFOUND = 'Unable to find the bank record for the provided id ({value})';
}
