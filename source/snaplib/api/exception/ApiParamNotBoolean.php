<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
namespace Snap\api\exception;

Use Snap\api\exception\ApiException;

/**
 * @author Cheok
 * @version 1.0
 */
class ApiParamNotBoolean extends ApiException
{
    protected const ERR_APIPARAMNOTBOOLEAN = 'The request parameter ({param}) is expected to be true/false.';
}
?>