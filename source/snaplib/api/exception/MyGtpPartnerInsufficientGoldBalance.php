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
 * @created 14-Apr-2021
 */
class MyGtpPartnerInsufficientGoldBalance extends ApiException
{
    protected const ERR_MYGTPPARTNERINSUFFICIENTGOLDBALANCE = 'System is unable to process your current order at the moment, please contact our customer service for assistance.';
}

?>