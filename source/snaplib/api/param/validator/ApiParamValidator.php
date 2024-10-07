<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\validator;

Use Snap\TLogging;
Use Snap\InputException;
Use Snap\IEntity;

/**
 * Validation class implementation. 
  *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.validator
*/
class ApiParamValidator {
    Use TLogging;

    /**
     * App
     * @var \Snap\App
     */
    protected $app = null;

    /**
     * Array to store data to be used to build digest for checking against.
     * @var array
     */
    protected $digestBlock = [];

    /**
     * The partner api key to be used to generate the digest.
     * @var string
     */
    protected $secretDigestKey = '';

    /**
     * Constructor method
     */
    public function __construct($app)
    {
        $this->app= $app;
    }

    /**
     * Actual logic to run through all the validation settings.  It will get from the config string each test that needs to
     * be done by running method prefixed with 'test' and the config setting name.  If validation fails, all the methods
     * should throw an exception that will subsequently be translated into proper error.
     * 
     * @param  String $validatorConfig      
     * @param  String $key                  
     * @param  String $value                
     * @param  array $originalRequestParams 
     * @return boolean
     */
    public function pass($validatorConfig, $key, $value, $originalRequestParams) 
    {
        $this->log("In " . __METHOD__ . " validing key $key with value $value", SNAP_LOG_DEBUG);
        $pass = true; 
        $validatorConfig = explode(';', $validatorConfig);
        foreach($validatorConfig as $aTest) {
            $aTest = explode('|', trim($aTest));
            if(0 == strlen($aTest[0])) {
                continue;
            }
            $methodName = 'test' . $aTest[0];
            $aTest = array_splice($aTest, 1);
            $this->log("...performaing method $methodName with parameters " . json_encode($aTest), SNAP_LOG_DEBUG);
            $pass = call_user_func_array([$this, $methodName], [$aTest, $key, $value, $originalRequestParams]);
            if(! $pass) {
                $pass = false;  //not pass.
                break;
            }
        }
        return $pass;
    }

    protected function testNumeric($conditions, $key, $value, $originalRequestParams)
    {
        if(!is_numeric($value)) {
            throw \Snap\api\exception\ApiParamNotNumeric::fromTransaction($this, ['param' => $key]);
        }
        for($i = 0; $i < count($conditions); $i++) {
            $testCondition = explode('=', $conditions[$i]);
            if(('>' == $testCondition[0] && $value <= $testCondition[1]) ||
                ('>=' == $testCondition[0] && $value < $testCondition[1]) ||
                ('<' == $testCondition[0] && $value >= $testCondition[1]) ||
                ('<=' == $testCondition[0] && $value > $testCondition[1])) {
                throw \Snap\api\exception\ApiParamNotNumeric::fromTransaction($this, ['param' => $key]);
            }
        }
        return true;
    }

    protected function testRequired($conditions, $key, $value, $originalRequestParams)
    {
        if(!isset($originalRequestParams[$key]) || '' === $originalRequestParams[$key]) {
            throw \Snap\api\exception\ApiParamRequired::fromTransaction($this, ['param' => $key]);
        } else {
            return true;
        }
    }
    protected function testString($conditions, $key, $value, $originalRequestParams)
    {
        for($i = 0; $i < count($conditions); $i++) {
            $testCondition = explode('=', strtolower($conditions[$i]));
            switch($testCondition[0]) {
                case 'max':
                    if(strlen($value) > intval($testCondition[1])) {
                        throw \Snap\api\exception\ApiParamStringValue::fromTransaction($this, 
                                        ['param' => $key, 'value' => $value, 'desc' => '> ' . $testCondition[1]]);
                    }
                    break;
                case 'min':
                    if(strlen($value) < intval($testCondition[1])) {
                        throw \Snap\api\exception\ApiParamStringValue::fromTransaction($this, 
                                        ['param' => $key, 'value' => $value, 'desc' => '< ' . $testCondition[1]]);
                    }
                    break;
            }
        }
        return true;
    }

    protected function testContains($conditions, $key, $value, $originalRequestParams)
    {
        if(!in_array(strtolower($value), $conditions)) {
            throw \Snap\api\exception\ApiParamNotOneOf::fromTransaction($this, ['param' => $key, 'value' => $value, 'options' => join(',', $conditions)]);
        }
        return true;
    }

    protected function testDatetime($conditions, $key, $value, $originalRequestParams)
    {
        if(0 < strlen($value) && ! \Snap\Common::validateDatetime($value)) {
            throw \Snap\api\exception\ApiParamInvalidDatetimeFormat::fromTransaction($this, ['param' => $key, 'value' => $value]);
        }
        return true;
    }

}
?>
