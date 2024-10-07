<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\exception;


/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 20-Oct-2020
 */
class EkycAlreadyPassed extends ApiException
{
    protected const ERR_EKYCALREADYPASSED = 'E-KYC verification was already completed';
}

?>