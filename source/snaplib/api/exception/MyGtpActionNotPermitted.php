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
 * @created 26-Oct-2020
 */
class MyGtpActionNotPermitted extends ApiException
{
    protected const ERR_MYGTPACTIONNOTPERMITTED = 'Action is not permitted. {message}';
}

?>