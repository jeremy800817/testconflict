<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\api\exception;


/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 22-Feb-2021
 */
class ApiParamStringDisallowedCharacters extends ApiException
{
    protected const ERR_APIPARAMSTRINGDISALLOWEDCHARACTERS = 'Field {param} contains disallowed characters. ({value})';
}

?>