<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
namespace Snap\api\param;

use Snap\api\param\ApiParam;
use Snap\api\param\validator\MyGtpApiParamValidator;
use Snap\api\param\extractor\MyGtpApiParamExtractor;
use Snap\api\param\converter\MyGtpApiParamConverter;
use Snap\api\param\validator\ApiParamValidator;
use Snap\api\param\extractor\ApiParamExtractor;
use Snap\api\param\converter\ApiParamConverter;

/**
 * This class specifically provide overrides for MyGTP api protocol.
 *
 * @author Cheok <cheok@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class MyGtpApiParam extends ApiParam
{
    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * 
     * @param  String $version Version number that we would like the params to get
     * @return MyGtpApiParam derived class
     */
    static function getInstance($version)
    {
        $className = __CLASS__ . preg_replace('/\./', '_', strtolower($version));
        if(class_exists($className)) {
            return new $className;
        } else {
            throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction([], ['param' => 'version', 'value' => $version]);
        }
        return new self;
    }

    /**
     * Returns the validator that is to be used for this class
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiParamValidator
     */
    protected function getValidator($app) : ApiParamValidator
    {
        if(!$this->validator) {
            $this->validator = new MyGtpApiParamValidator($app);
        }
        return $this->validator;
    }

    /**
     * Returns the converter that will be used to translate api parameters into objects
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this converter
     * @return ApiParamConveter
     */
    protected function getConverter($app) : ApiParamConverter
    {
        if(!$this->converter) {
            $this->converter = new MyGtpApiParamConverter($app);
        }
        return $this->converter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  param\validator\App    $app     App object
     * @param  string                 $config  Configuration to be used for this extractor
     * @return ApiParamExtractor
     */
    protected function getExtractor($app) : ApiParamExtractor
    {
        if(!$this->extractor) {
            $this->extractor = new MyGtpApiParamExtractor($app);
        }
        return $this->extractor;
    }

    public function decodeActionType($params)
    {
        $params['action'] = strtolower($params['action']);
        if(isset($this->paramsMap[$params['action']])) {
            return $params['action'];
        } else {
            throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction([], ['param' => 'action', 'value' => $params['action']]);
        }
        return null;
    }

    protected function __construct()
    {
        //TODO:  Register all the actions and parameters to be used here.
    }

     /**
     * This is the main method to be called to translate and validate incoming api parameters
     * 
     * @param  App        $app              App object
     * @param  array      $params          Income request parameters
     * @return array   Response data
     */
    public function decode($app, $params)
    {
        if(0 == strlen($this->actionType)) {
            $requestActionType = $this->actionType = $this->decodeActionType($params);
        } else {
            $requestActionType = $this->actionType;
        }
        $this->logDebug("In " . __METHOD__ . " decoding the request for action $requestActionType", SNAP_LOG_DEBUG);
        if(! isset($this->paramsMap[$requestActionType])) {
            $this->logDebug("In " . __METHOD__ . " decoding the request for action $requestActionType", SNAP_LOG_DEBUG);
            throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction($this, ['value' => $requestActionType]);
        }
        $expectedActionParams = $this->paramsMap[$requestActionType];
        $this->logDebug("Number of parameters available for $requestActionType is " . count($expectedActionParams));
        foreach($expectedActionParams as $requestKey => $paramConfig) {
            $validator = $this->getValidator($app);
            if($validator->pass($paramConfig['validator'], $requestKey, $params[$requestKey], $params)) {
                $this->log("validation passed for $requestKey, converting data now....", SNAP_LOG_DEBUG);
                if(isset($paramConfig['converter']) && 0 < strlen($paramConfig['converter'])) {
                    $this->log("found converter for $requestKey....{$paramConfig['converter']}", SNAP_LOG_DEBUG);
                    $conveter = $this->getConverter($app);
                    $conveter->convert($paramConfig['converter'], $requestKey, $params[$requestKey], $params, $this->data);
                } else {
                    $this->log("no converter found for $requestKey, filling up default data....", SNAP_LOG_DEBUG);
                    $this->data[$requestKey] = $params[$requestKey];
                }
            }
        }
        return $this->data;
    }
}
?>
