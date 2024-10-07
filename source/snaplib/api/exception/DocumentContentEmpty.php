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
class DocumentContentEmpty extends ApiException
{
    protected const ERR_DOCUMENTCONTENTEMPTY = 'The content of the document ({code}) is empty for selected language ({language})';
}
