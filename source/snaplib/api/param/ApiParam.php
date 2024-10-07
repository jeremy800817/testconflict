<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param;

Use Snap\TLogging;
Use Snap\InputException;
Use Snap\IEntity;
Use Snap\object\Partner;
Use Snap\object\Order;
Use Snap\object\FutureOrder;
Use Snap\api\param\validator\ApiParamValidator;
Use Snap\api\param\converter\ApiParamConverter;
Use Snap\api\param\extractor\ApiParamExtractor;

/**
 * This class managed parameters that are used for API communication with client.
 * The class will be responsible for:
 * 1)  Understanding the order of the request parameters and action needed
 * 2)  Able to determine validity of its parameter limits and values.
 * 3)  Able to transform from / to the parameters given the required objects.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
*/
abstract class ApiParam {
    Use TLogging;

    protected $paramsMap = [];
    protected $data = [];
    protected $validator = null;
    protected $converter = null;
    protected $extractor = null;
    protected $actionType = null;

    /**
     * Provided with the request parameters from sender api call, we will need to determine what are the action
     * that is requested by API and then validate the parameters and extract data from it.
     * 
     * @param  array $params       Request parameters provided
     * @return String              Action type name
     */
    abstract public function decodeActionType($params);

    public function getActionType() {
        return $this->actionType;
    }

    public function setActionType($action) {
        $this->actionType = $action;
    }

    /**
     * Base parameter to register the parameters to be used for each available action to the API.
     * 
     * @param  String $actionType    Action that the parameter should be registered to.
     * @param  String $apiRequestKey The parameter name and its property
     * @param  String $validators    Type of validator (piped | for multiple entries)
     * @param  String $converter     Converting the data to system usable objects
     * @param  String $extractor     Used to generate parameters to respond to the client
     */
    protected function registerParameter($actionType, $apiRequestKey, $validators, $converter, $extractor)
    {
        $this->paramsMap[$actionType][$apiRequestKey] = ['validator' => $validators, 'converter' => $converter, 'extractor' => $extractor];
    }
    
    /**
     * Returns the validator that is to be used for this class
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiPAramValidator
     */
    protected function getValidator($app) : ApiParamValidator
    {
        if(!$this->validator) {
            $this->validator = new ApiParamValidator($app);
        }
        return $this->validator;
    }

    /**
     * Returns the converter that will be used to translate api parameters into objects
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiParamConveter
     */
    protected function getConverter($app) : ApiParamConverter
    {
        if(!$this->conveter) {
            $this->conveter = new ApiParamConveter($app);
        }
        return $this->conveter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  App    $app     App object
     * @param  string $config  Configuration to be used for this validator
     * @return ApiParamExtractor
     */
    protected function getExtractor($app) : ApiParamExtractor
    {
        if(!$this->extractor) {
            $this->extractor = new ApiParamExtractor($app);
        }
        return $this->extractor;
    }
    
    /**
     * This is the main method to be called to format a proper response to a client
     * 
     * @param  App        $app                   App object
     * @param  Sring      $actionType            The response action type to format
     * @param  array      $dataObjects           Objects to be used for formatting the response.  The keys for the objects include
     * @param  aray       $originalRequestParams The original request parameters received
     * @return array
     */
    public function encode($app, $actionType, $dataObjects, $originalRequestParams)
    {
        $this->logDebug("Now in " . __CLASS__ . " encoding the request for action $actionType", MX_LOG_DEBUG);
        $responseData = [];
        if(!isset($this->paramsMap[$actionType])) {
            throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction($this, ['value' => $requestActionType]);
        }
        $usedParams = $this->paramsMap[$actionType];
        foreach($usedParams as $responseKey => $paramConfig) {
            if(isset($paramConfig['extractor'])) {
                $extractor = $this->getExtractor($app);
                $extractor->extract($paramConfig['extractor'], $responseKey, $dataObjects, $originalRequestParams, $this->data);
            } else {
                throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction($this, ['value' => $responseKey]);
            }
        }
        return $this->data;
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
        $this->logDebug("In " . __METHOD__ . " decoding the request for action $requestActionType", MX_LOG_DEBUG);
        if(! isset($this->paramsMap[$requestActionType])) {
            $this->logDebug("In " . __METHOD__ . " decoding the request for action $requestActionType", MX_LOG_DEBUG);
            throw \Snap\api\exception\ApiParamRequestNotFound::fromTransaction($this, ['value' => $requestActionType]);
        }
        $expectedActionParams = $this->paramsMap[$requestActionType];
        $this->logDebug("Number of parameters available for $requestActionType is " . count($expectedActionParams));
        foreach($expectedActionParams as $requestKey => $paramConfig) {
            $validator = $this->getValidator($app);
            if($validator->pass($paramConfig['validator'], $requestKey, $params[$requestKey], $params) && isset($params[$requestKey])) {
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