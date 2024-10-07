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
class MyOccupationCategoryNotFound extends ApiException
{
    protected const ERR_MYOCCUPATIONCATEGORYNOTFOUND = 'Unable to find the occupation category for the provided id ({value})';
}
