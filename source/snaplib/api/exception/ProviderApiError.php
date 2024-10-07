<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\exception;

Use Snap\api\exception\ApiException;

class ProviderApiError extends ApiException
{
    protected const ERR_PROVIDERAPIERROR = '{message}';
}
