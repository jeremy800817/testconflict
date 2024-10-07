<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
namespace Snap\api\exception;

Use Snap\api\exception\ApiException;

class OrderDenominationException extends ApiException
{
    protected const ERR_ORDERDENOMINATIONEXCEPTION = 'Order amount could not denoted by - {productDenomination}';
}
?>