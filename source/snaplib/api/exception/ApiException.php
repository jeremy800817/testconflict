<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
namespace Snap\api\exception;

Use Snap\object\fundin;
Use Snap\object\fundout;
Use Snap\object\settlement;
Use \Exception as Exception;

/**
 * @author Devon
 * @version 1.0
 * @created 06-Nov-2019 5:27:52 PM
 */
class ApiException extends \RuntimeException
{
    public $field = '';
    /**
    * The message template used to format the exception.  Can use braces {}to
    * provide object property to the string.
    * @var string
    */
    const ERR_ApiException = "Application generated an exception for {type} {partnerrefid} ({id})";

    /**
     * Used to format message specified for deposit transaction
     * @param $transaction
     */
    public static function fromTransaction($transaction, $messageTagReplacement = null, $code = 0, Exception $previous = null)
    {
        $type = explode('\\', get_class($transaction));
        $type = array_pop($type);
        $message = self::buildMessage($transaction, $type, $messageTagReplacement);
        $exception = new static($message, $code, $previous);
        if(isset($messageTagReplacement['param']) || isset($messageTagReplacement['field']) ) {
            $exception->field = isset($messageTagReplacement['field']) ? $messageTagReplacement['field'] : $messageTagReplacement['param'];
        }
        return $exception;
    }

    protected static function buildMessage($object, $type, $messageTagReplacement = null)
    {
        $className = explode('\\', get_called_class());
        $className = array_pop($className);
        $classMessageConst = 'ERR_'. strtoupper($className) . "_" . strtoupper($type);
        $messageTemplate = @constant(get_called_class()."::$classMessageConst");
        if (null == $messageTemplate || 0 == strlen($messageTemplate)) {
            $classMessageConst = 'ERR_'. strtoupper($className);
            $messageTemplate = @constant(get_called_class()."::$classMessageConst");
            if (null == $messageTemplate) {
                $messageTemplate = self::ERR_ApiException;
            }
        }

        $patterns = ['/{type}/'];
        $replacements = [$type];
        if (preg_match_all('/(\{[a-zA-Z0-9]+\})/', $messageTemplate, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $field = substr($matches[1][$i], 1, strlen($matches[1][$i])-2);
                if (/*isset($object->{$field}) && */ 0 != @strlen($object->{$field})) {
                    $patterns[] = "/\\{".$field."\\}/";
                    $replacements[] = gettext($object->{$field});
                }
            }
        }
        if ($messageTagReplacement) {
            foreach ($messageTagReplacement as $key => $value) {
                $patterns[] = "/\{$key\}/";
                $replacements[] = htmlspecialchars(gettext($value));
            }
        }
        return preg_replace($patterns, $replacements, gettext($messageTemplate));
    }
}
?>