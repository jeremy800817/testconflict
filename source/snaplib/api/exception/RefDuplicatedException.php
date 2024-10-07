<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
namespace Snap\api\exception;

Use Snap\api\exception\ApiException;

/**
 * @author Devon
 * @version 1.0
 * @created 06-Nov-2019 5:27:52 PM
 */
class RefDuplicatedException extends ApiException
{
    protected const ERR_REFDUPLICATEDEXCEPTION = 'The {action} reference id ({partnerrefno}) for merchant {code} is duplicated.  There is already existing {action} with the reference';
}
?>