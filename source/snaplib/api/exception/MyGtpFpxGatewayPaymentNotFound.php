<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;


/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 16-Jun-2021
 */
class MyGtpFpxGatewayPaymentNotFound extends ApiException
{
    protected const ERR_MYGTPFPXGATEWAYPAYMENTNOTFOUND = 'Transaction reference ({paymentrefno}) not found.';
}

?>