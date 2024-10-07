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
 * @created 21-Oct-2020
 */
class MyPartnerApiMissing extends ApiException
{
    protected const ERR_MYPARTNERAPIMISSING = 'Unable to find {type} API for partner {code}';
}

?>