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
class ApiParamPartnerInvalid extends ApiException
{
    protected const ERR_APIPARAMPARTNERINVALID = 'The request parameter {param} ({value}) is not found, invalid or does not support API access.';
}
?>