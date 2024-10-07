<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 18-May-2021
*/

namespace Snap\job;

/**
 * This class is so created for the purpose of recording CASA responses into ApiLogs table.
 * Searching & saving ApiLogs during CASA callback will response to be extremely slow, thus this job is used to save responses in the background. 
 * 
 * @package Snap\job
 */
class MyGtpCasaApiLogsResponseJob extends basejob
{

    public function doJob($app, $params = array())
    {
        $log = $app->apilogStore()->searchTable()->select()
                    ->where('refobject', $params['classname'])
                    ->andWhere('refobjectid', $params['gatewayrefno'])
                    ->one();

        if (! $log) {
            $this->logDebug("Unable to find apilog for payment {$params['gatewayrefno']}.");
            return;
        }
        $log->responsedata = $params['response'];
        $log = $app->apilogStore()->save($log);
    }


    public function describeOptions()
    {
        return [
            'gatewayrefno' => array('required' =>  true,  'type' => 'string', 'desc' => 'ID of MyPaymentDetail/MyDisbursement'),
            'response'     => ['required' => true, 'type' => 'string', 'desc' => "Response from CASA Gateway"]
        ];
    }

}