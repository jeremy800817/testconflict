<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;

class MyGtpTransactionExists extends ApiException
{
    protected const ERR_MYGTPTRANSACTIONEXISTS = 'Transaction reference already exists. {message}';
}

?>