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
 * @created 27-Oct-2020
 */
class EkycSubmissionInvalid extends ApiException
{
    protected const ERR_EKYCSUBMISSIONINVALID = 'Submitted document(s) are invalid. {message}';
}

?>