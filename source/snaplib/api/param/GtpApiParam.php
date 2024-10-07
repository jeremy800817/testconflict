<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param;

Use Snap\InputException;
Use Snap\IEntity;
Use Snap\object\Partner;
Use Snap\object\Order;
Use Snap\object\OrderQueue;
Use Snap\api\param\ApiParam;
Use Snap\api\param\validator\ApiParamValidator;
Use Snap\api\param\validator\GtpApiParamValidator;
Use Snap\api\param\converter\ApiParamConverter;
Use Snap\api\param\converter\GtpApiParamConverter;
Use Snap\api\param\extractor\ApiParamExtractor;
Use Snap\api\param\extractor\GtpApiParamExtractor;

/**
 * This class specifically provide overrides for GTP api protocol.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class GtpApiParam extends ApiParam
{
    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * 
     * @param  String $version Version number that we would like the params to get
     * @return GtpApiParam derived class
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
     * @return ApiPAramValidator
     */
    protected function getValidator($app) : ApiParamValidator
    {
        if(!$this->validator) {
            $this->validator = new GtpApiParamValidator($app);
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
        if(!$this->conveterr) {
            $this->conveter = new \Snap\api\param\converter\GtpApiParamConverter($app);
        }
        return $this->conveter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  param\validator\App    $app     App object
     * @param  param\converter\string $config  param\validator\Configuration to be used for this validator
     * @return param\extractor\GtpApiParamExtractorparam\converter\
     */
    protected function getExtractor($app) : ApiParamExtractor
    {
        if(!$this->extractor) {
            $this->extractor = new GtpApiParamExtractor($app);
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
}
?>
