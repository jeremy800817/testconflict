<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\exception;

use Snap\api\exception\ApiException;

/**
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 * @created 27-Oct-2020
 */
class MyGtpAccountClosurePending extends ApiException
{
    protected const ERR_MYGTPACCOUNTCLOSUREPENDING = 'Previous account closure request is still in progress';
}
