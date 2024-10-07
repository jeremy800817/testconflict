<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\gtp;

Use Snap\IApiProcessor;
Use Snap\api\gtp\GtpApiSender;

/**
 * This class implements responding to client request with XML formatted data
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.gtp
 */
class GtpXMLApiSender extends GtpApiSender
{

    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null)
    {
        $xw = new \XMLWriter();
        $xw->openMemory();
        $xw->setIndentString("\t");
        $xw->setIndent(true);
        $xw->startDocument("1.0");
        $this->asXml($xw, array('response' => $responseData));
        $xw->endDocument();
        if(!$destination) {
            echo $xw->outputMemory();
        } else {
            $xw->asXML($destination);
        }
    }

    /**
     * Actual method to build the XML data set.
     * @param  XmlWriter $xw
     * @param  array     $dataArr Data to format
     */
    private function asXml($xw, $dataArr) {
        foreach($dataArr as $key => $value) {
            if(is_array($value)) {
                $xw->startElement($key);
                $this->asXml($xw, $value);
                $xw->endElement();
            } else {
                $xw->writeElement($key, $value);
            }
        }
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>