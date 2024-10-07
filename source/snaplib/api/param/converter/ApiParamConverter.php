<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\converter;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * The main purpose of this class is to be able to convert the incoming request parameter data into
 * system usable representation.  It converts incoming data into system objects for further processing.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.converter
*/
class ApiParamConverter {
    Use \Snap\TLogging;

    protected $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Main method called by ApiParam derived class to process the conversion.  The config generally define a method
     * which should translate to a method name (prefixed with convert) that does the processing and conversion of data
     * into more usable form
     * 
     * @param  String $converterConfig Definitions to let the converter know (method name) to implement the conversion.
     * @param  String $key             Api Key
     * @param  mixed  $value           Data given from incoming request
     * @param  array $originalParams   Incoming request parameters
     * @param  array &$finalParams     Converted data storage array
     * @return mixed                    Converted data
     */
    public function convert($converterConfig, $key, $value, $originalParams, &$finalParams)
    {
        $this->log("Converting parameter $key using the config $converterConfig", SNAP_LOG_ERROR);
        $converterConfig = explode(';', $converterConfig);
        foreach($converterConfig as $converter) {
            $converter = explode('|', trim($converter));
            if(0 == strlen($converter[0])) {
                continue;
            }
            $methodName = 'convert' . $converter[0];
            $converter = array_splice($converter, 1);
            if(method_exists($this, $methodName)) {
                $this->log("converting with method " . __CLASS__ . "::$methodName() for $key", SNAP_LOG_DEBUG);
                $finalParams = array_merge( $finalParams, call_user_func_array([$this, $methodName], [$converter, $key, $value, $originalParams]));

            }
            if(! $value) {
                break;
            }
        }
        return $value;
    }
}
?>