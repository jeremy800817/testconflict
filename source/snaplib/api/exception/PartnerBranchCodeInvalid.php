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
 * @created 13-Nov-2020 6:07:00 PM
 */
class PartnerBranchCodeInvalid extends ApiException
{
    protected const ERR_PARTNERBRANCHCODEINVALID = 'The branch code ({code}) provided is not found, invalid or not active.';
}
