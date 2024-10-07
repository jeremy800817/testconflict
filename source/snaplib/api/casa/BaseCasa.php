<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 12-Nov-2020
*/

namespace Snap\api\casa;

use Snap\ICasaOperation;
use Snap\IObservable;
use Snap\object\ApiLogs;
use Snap\TLogging;
use Snap\TObservable;

abstract class BaseCasa implements ICasaOperation, IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    protected $app = null;

    protected function __construct($app) {
        $this->app = $app;
        
        // Attach observers
        $this->attach($app->mygtptransactionManager());
        $this->attach($app->mygtpconversionManager());
        $this->attach($app->mygtpdisbursementManager());
    }

    /**
     * Returns an instance of the CASA handler
     * 
     * @return BaseCasa
     */
    final static public function getInstance($handlerName) {
        $matches = [];
        if (preg_match('/^\\\\Snap\\\\api\\\\casa\\\\(.*)$/',$handlerName, $matches)) {
            $handlerName = $matches[1];
        }

        $classNameArr = explode('\\', __CLASS__);
        array_pop($classNameArr);
        $classNameArr[] = $handlerName;

        $className = implode('\\', $classNameArr);
        if(class_exists($className)) {
            $app = \Snap\App::getInstance();
            $app->logDebug("Instantiating CASA handler $className");
            return new $className($app);
        }
    }

    /**
     * Log string to apilogs
     * 
     * @param   string  $responseString
     * @param   object  $object
     * @return void
     */
    protected function logApiResponse($responseString, $object)
    {
        $this->app->startCLIJob("MyGtpCasaApiLogsResponseJob.php", [
            'gatewayrefno' => $object->id,
            'response' => $responseString,
            'classname' => array_pop(explode('\\', get_class($object)))  
        ]);
    }

    /**
     * @param   string  $requestData    The request data to be sent
     * @param   object  $object         The associated object
     * 
     * @return void
     */
    protected function logApiRequest($requestData, $object)
    {
        $log = $this->app->apilogStore()->create([
            'type'  => ApiLogs::TYPE_MYGTP_CASA,
            'fromip' => 'localhost',
            'requestdata' => $requestData,
            'systeminitiate' => 1,
            'refobject' => array_pop(explode('\\', get_class($object))),
            'refobjectid' => $object->id,
            'status'    => 1
        ]);

        $log = $this->app->apilogStore()->save($log);
    }
}


?>