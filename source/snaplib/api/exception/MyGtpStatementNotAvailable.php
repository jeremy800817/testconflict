<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;


/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 01-Mar-2021
 */
class MyGtpStatementNotAvailable extends ApiException
{
    protected const ERR_MYGTPSTATEMENTNOTAVAILABLE = 'Statement records from date {start} to {end} is not available for this account holder.';
}

?>