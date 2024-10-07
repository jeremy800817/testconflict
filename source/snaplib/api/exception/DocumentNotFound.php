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
 * @created 14-Nov-2020 4:47:00 PM
 */
class DocumentNotFound extends ApiException
{
    protected const ERR_DOCUMENTNOTFOUND = 'Unable to find the document for the provided code ({code})';
}
