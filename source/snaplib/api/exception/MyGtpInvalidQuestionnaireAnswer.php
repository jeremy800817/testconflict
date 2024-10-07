<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\api\exception;


/**
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 05-Jan-2021
 */
class MyGtpInvalidQuestionnaireAnswer extends ApiException
{
    protected const ERR_MYGTPINVALIDQUESTIONNAIREANSWER = 'Invalid questionnaire answer. {extra_message}';
}

?>