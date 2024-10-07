<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\param\validator;

use Snap\api\exception\ApiParamStringDisallowedCharacters;
use Snap\api\param\validator\GtpApiParamValidator;

/**
 * MyGTP api specific validator
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.validator
*/
class MyGtpApiParamValidator extends GtpApiParamValidator {

    /**
     * Method to test if the given value is a valid email address
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testEmail($conditions, $key, $value, $originalRequestParams)
    {
        $pattern = '/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*' .
            '@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/';

        // current \Snap\Common::validateEmail($value) is prompting warning thus returing false
        if (0 < strlen($value) && !preg_match($pattern, $value)) {
            throw \Snap\api\exception\ApiParamEmailInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        };

        return true;
    }

    /**
     * Method to test if the given value is a valid phone number format
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testPhone($conditions, $key, $value, $originalRequestParams)
    {
        $formatMap = [
            'mobile-my' => '/^(\+601)[0-9][0-9]{7,9}$/',
            'default' => '/^(\+601)[0-9][0-9]{7,9}$/',
        ];

        if(!empty($value)){
            // If no conditions were provided
            if (empty($conditions)) {
                if (!preg_match($formatMap['default'], $value)) {
                    throw \Snap\api\exception\ApiParamPhoneInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
                }

                return true;
            }

            if (!isset($formatMap[$conditions[0]]) || !preg_match($formatMap[$conditions[0]], $value)) {
                throw \Snap\api\exception\ApiParamPhoneInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
            }
        } 

        return true;
    }

    /**
     *  Method to test if the param has a matching confirm. For example "password", and
     * "confirm_password"
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testConfirm($conditions, $key, $value, $originalRequestParams)
    {
        if (!isset($originalRequestParams['confirm_' . $key])) {
            throw \Snap\api\exception\ApiParamConfirmationInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }

        if ($value !== $originalRequestParams['confirm_' . $key]) {
            throw \Snap\api\exception\ApiParamConfirmationInvalid::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }

        return true;
    }

    protected function testBearer($conditions, $key, $value, $originalRequestParams)
    {
        if (0 < strlen($value)) {
            $bearer = $value;
        } else {
            $bearer = $this->app->mygtpauthManager()->extractBearerTokenFromHeader();
        }

        if (! $bearer) {
            http_response_code(401);
            throw \Snap\api\exception\ApiInvalidAccessToken::fromTransaction([]);
        }

        return true;
    }
    
    /**
     *  Method to test if the param value record exists in the database
     *  Useage: exists|storeName|attribute
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testExists($conditions, $key, $value, $originalRequestParams)
    {
        try {

            $store = $this->app->{$conditions[0] . 'Store'}();
            $exists = $store->searchTable()->select()->where($conditions[1], $value)->exists();

            if (!$exists) {
                throw \Snap\api\exception\ApiParamRecordNotFound::fromTransaction($this, ['param' => $key, 'value' => $value]);
            }

            return true;
        } catch (\Error $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], ['message' => 'Caught Exception: ' . $e->getmessage()]);
        }
    }

    /**
     *  Method to test if the other params have value and if does, this param should
     *  has a value too. Else, bypass validation by returning false
     * 
     *  Usage: requiredWithAny|param_1|param_2
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testRequiredWithAny($conditions, $key, $value, $originalRequestParams)
    {
        // If any of the conditions is not empty
        foreach ($conditions as $condition) {
            if (!empty($originalRequestParams[$condition])) {
                return $this->testRequired($conditions, $key, $value, $originalRequestParams);
            }
        }

        return false;
    }

    public function testDatetimeCompare($conditions, $key, $value, $originalRequestParams)
    {
        $date1 = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        $date2 = \DateTime::createFromFormat('Y-m-d H:i:s', $originalRequestParams[$conditions[1]]);

        switch ($conditions[0]) {
            case '<':
                $valid = $date1 < $date2;
                $message = "Must be smaller than ({$conditions[1]})";
                break;
            case '>':
                $valid = $date1 > $date2;
                $message = "Must be larger than ({$conditions[1]})";
                break;
            case '<=':
                $valid = $date1 <=$date2;
                $message = "Must be smaller than or equal to ({$conditions[1]})";
                break;
            case '>=':
                $valid = $date1 >=$date2;
                $message = "Must be larger than or equal to ({$conditions[1]})";
                break;
            case '==':
                $valid = $date1 == $date2;
                $message = "Must be equal to ({$conditions[1]})";
                break;
            case '<>':
            case '!=':
                $valid = $date1 != $date2;
                $message = "Must not be equal to ({$conditions[1]})";
                break;
            default:
                throw \Snap\api\exception\ApiParamDatetimeInvalid::fromTransaction([], ['param' => $key, 'message' => "Invalid date comparison operator provided ({$conditions[0]})"]);
        }

        if (! $valid) {
            throw \Snap\api\exception\ApiParamDatetimeInvalid::fromTransaction([], ['param' => $key, 'message' => $message]);
        }

        return true;
    }
    
    /**
     *  Method to test if the other param value matches the expected value
     * 
     *  Usage: requiredWhen|other_param|value
     *
     * @param  array  $conditions            	Conditions for the validation
     * @param  string $key                      Request parameter name to validate
     * @param  string $value                    Request parameter value to validate
     * @param  array  $originalRequestParams    The original request parameters
     * @return bool
     */
    protected function testRequiredWhen($conditions, $key, $value, $originalRequestParams)
    {
        $expectedValue   = $conditions[1];

        // Pass since the other param does not exist
        if ('null' !== $expectedValue && ! isset($originalRequestParams[$conditions[0]])) {
            return true;
        }

        if (! isset($originalRequestParams[$conditions[0]])) {
            $otherParamValue = 'null';
        } else {
            $otherParamValue = $originalRequestParams[$conditions[0]];
        }

        // For 'false' is not equal to boolean false
        if ('false' === $otherParamValue || 'true' === $otherParamValue) {
            $otherParamValue = filter_var($otherParamValue, FILTER_VALIDATE_BOOLEAN);
        }

        // For 'false' is not equal to boolean false
        if ('false' === $expectedValue || 'true' === $expectedValue) {
            $expectedValue = filter_var($expectedValue, FILTER_VALIDATE_BOOLEAN);
        }

        // Check the other param value
        if ($otherParamValue === $expectedValue) {
            return $this->testRequired($conditions, $key, $value, $originalRequestParams);
        }

        return true;
    }

    protected function testFullName($conditions, $key, $value, $originalRequestParams)
    {
        // if (! preg_match('/^[a-zA-Z\/\. ]+$/', $value)) {
        $pattern = '/^[a-zA-Z\/\-\'.@ ]+$/';
        if (! preg_match($pattern, $value)) {
            throw ApiParamStringDisallowedCharacters::fromTransaction([], ['param' => $key, 'value' => $value]);
        }

        return true;
    }

    protected function testOptionalContains($conditions, $key, $value, $originalRequestParams)
    {
        if (isset($originalRequestParams[$key]) && !in_array(strtolower($value), $conditions)) {
            throw \Snap\api\exception\ApiParamNotOneOf::fromTransaction($this, ['param' => $key, 'value' => $value, 'options' => join(',', $conditions)]);
        }
        return true;
    }
}
?>
