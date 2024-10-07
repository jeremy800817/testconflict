<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */

namespace Snap\api\exception;

use Snap\api\exception\ApiException;

/**
 * @author Ang <ang@silverstream.my>
 * @version 1.0
 * @created 27-Apr-2021 10:41:00 AM
 */
class MyOccupationSubCategoryNotFound extends ApiException
{
    protected const ERR_MYOCCUPATIONSUBCATEGORYNOTFOUND = 'Unable to find the occupation sub category for the provided id ({value})';
}
