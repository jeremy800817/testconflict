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
 * @created 21-Oct-2020 10:27:52 AM
 */
class ApiParamRecordNotFound extends ApiException
{
    protected const ERR_APIPARAMRECORDNOTFOUND = 'The {param} with value ({value}) record could not be found.';
}
