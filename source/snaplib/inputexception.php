<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
 * This class represents a generally user input type of exception dealing with data.
 *
 * @author   Devon Koh <devon@silverstream.my>
 * @version  1.0
 * @abstract
 * @package  snap.base
 */
class InputException extends \Exception
{
    const GENERAL_ERROR = 1;
    const INVALID_FIELD = 2;
    const FORMAT_ERROR = 3;
    const FIELD_ERROR = 4;

    private $field;

    public function __construct($message, $errorType, $errorField = '', Exception $previous = null)
    {
        $this->field = $errorField;
        parent::__construct($message, $errorType, $previous);
    }

    public function getErrorField()
    {
        return $this->field;
    }
}
?>