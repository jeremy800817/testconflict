<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\exception;

use Snap\api\exception\ApiException;

/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 09-Oct-2020 4:30:00 PM
 */
class ApiParamEmailInvalid extends ApiException
{
    protected const ERR_APIPARAMEMAILINVALID = 'The request parameter ({param}) with data ({value}) is not a valid email address.';
}
