<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 20-Jan-2021
*/

namespace Snap\api\exception;

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 20-Jan-2021
 */
class MyOtcPartnerCusidExists extends ApiException
{
    protected const ERR_MYOTCPARTNERCUSIDEXISTS = 'Account Number ({partnercusid}) already exists.';
}

?>