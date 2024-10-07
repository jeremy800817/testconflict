<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\param\extractor;

Use Snap\InputException;
Use Snap\IEntity;

/**
 * This class implements main logic to extract data from system objects to format a viable response to client.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.param.extractor
 */
class ApiParamExtractor {
    Use \Snap\TLogging;

    /**
     * Hold the app instance
     * @var \Snap\App
     */
    protected $app = null;

    /**
     * Constructor
     */
    public function __constructor($app)
    {
        $this->app = $app;
    }

    /**
     * Main logic that integrates with ApiParam method to implement the extraction logic.  It will
     * delegates all its actual functionality to derived class that will call method prefix with "extract"
     * 
     * @param  String $extractorConfig       Configure method to do extract for
     * @param  String $key                   Key to keep data
     * @param  array  $processedData         Given objects
     * @param  array  $originalRequestParams Original request parameters
     * @param  array  $finalParams           Container to store the extracted info.
     * @return array
     */
    public function extract($extractorConfig, $key, $processedData, $originalRequestParams, &$finalParams)
    {
        $this->log("into " . __METHOD__ . " for $key with config $extractorConfig", SNAP_LOG_ERROR);
        $extractedArray = explode(';', $extractorConfig);
        foreach($extractedArray as $anExtractMethod) {
            $anExtractMethod = explode('|', trim($anExtractMethod));
            if(0 == strlen($anExtractMethod[0])) {
                continue;
            }
            $methodName = 'extract'.$anExtractMethod[0];
            $methodParams = array_splice($anExtractMethod, 1);
            if(method_exists($this, $methodName)) {
                $finalParams = array_merge( $finalParams, 
                                    call_user_func_array([$this, $methodName], [$methodParams, $key, $processedData, $originalRequestParams, $finalParams]));
            }
        }
        return $finalParams;
    }

    public function serialNoFormatToMbb($serialno){
        $length = strlen($serialno);
        $alphaLength = 3;
        $alphaMaxLength = 6;
        $noLength = 8;

        //remove any character
        $res = preg_replace("/[^a-zA-Z0-9]/", "", $serialno);

        //split letter and numbers
        preg_match_all('/(\d)|(\w)/', $res, $matches);
        $numbers = implode($matches[1]);
        $letters = implode($matches[2]);

        $update .= (strlen($letters) < $alphaMaxLength) ? str_pad($letters,$alphaMaxLength, ' ', STR_PAD_RIGHT) : $letters;

        //check length of string for number and fill in spaces
        $update .= (strlen($numbers) < $noLength) ? str_pad($numbers,$noLength, ' ', STR_PAD_LEFT) : $numbers;

        return $update;
    }
}
?>