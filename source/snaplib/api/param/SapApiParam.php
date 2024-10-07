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
Use Snap\object\FutureOrder;
Use Snap\api\param\ApiParam;
Use Snap\api\param\validator\ApiParamValidator;
Use Snap\api\param\validator\SapApiParamValidator;
Use Snap\api\param\converter\ApiParamConverter;
Use Snap\api\param\converter\SapApiParamConverter;
Use Snap\api\param\extractor\ApiParamExtractor;
Use Snap\api\param\extractor\SapApiParamExtractor;

/**
 * This class specifically provide overrides for GTP api protocol.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param
 */
class SapApiParam extends ApiParam
{
    /**
     * This is the factory method that will instantiate the appropriate API param class to handle the request.
     * 
     * @param  String $version Version number that we would like the params to get
     * @return SapApiParam derived class
     */
    static function getInstance($version)
    {
        $className = __CLASS__ . preg_replace('/\./', '_', $version);
        if(class_exists($className)) {
            return new $className;
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
            $this->validator = new SapApiParamValidator($app);
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
            $this->conveter = new \Snap\api\param\converter\SapApiParamConverter($app);
        }
        return $this->conveter;
    }

    /**
     * Returns the extractor that can be used to format a parameter for responding to client
     * 
     * @param  param\validator\App    $app     App object
     * @param  param\converter\string $config  param\validator\Configuration to be used for this validator
     * @return param\extractor\SapApiParamExtractorparam\converter\
     */
    protected function getExtractor($app) : ApiParamExtractor
    {
        if(!$this->extractor) {
            $this->extractor = new SapApiParamExtractor($app);
        }
        return $this->extractor;
    }

    public function decodeActionType($params)
    {
        if(isset($params['action'])) {
            return $params['action'];
        }
        return null;
    }

    protected function __construct()
    {
        //TODO:  Register all the actions and parameters to be used here.
    }
}
?>
