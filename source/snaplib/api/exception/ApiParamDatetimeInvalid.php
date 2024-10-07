<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\exception;

Use Snap\api\exception\ApiException;

/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 23-Dec-2020
 */
class ApiParamDatetimeInvalid extends ApiException
{
    protected const ERR_APIPARAMDATETIMEINVALID = 'The request parameter ({param}) is invalid. {message}';
}
?>