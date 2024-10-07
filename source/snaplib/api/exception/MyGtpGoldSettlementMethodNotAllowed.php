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
 * @created 25-Nov-2020
 */
class MyGtpGoldSettlementMethodNotAllowed extends ApiException
{
    protected const ERR_MYGTPGOLDSETTLEMENTMETHODNOTALLOWED = 'Settlement method ({settlementmethod}) not allowed for transaction {refno}.';
}

?>