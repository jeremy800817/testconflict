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

use Snap\object\MyDisbursement;

/**
 * This class is so created for the purpose of recording FPX responses into ApiLogs table.
 * Searching & saving ApiLogs during FPX callback will response to be extremely slow, thus this job is used to save responses in the background. 
 * 
 * @package Snap\job
 */
class MyGtpPayoutApiLogsResponseJob extends basejob
{

    public function doJob($app, $params = array())
    {
        $log = $app->apilogStore()->searchTable()->select()
                    ->where('refobject', array_pop(explode('\\', MyDisbursement::class)))
                    ->andWhere('refobjectid', $params['gatewayrefno'])
                    ->one();

        if (! $log) {
            $this->logDebug("Unable to find apilog for disbursement {$params['gatewayrefno']}.");
            return;
        }
        $log->responsedata = $params['response'];
        $log = $app->apilogStore()->save($log);
    }


    public function describeOptions()
    {
        return [
            'gatewayrefno' => array('required' =>  true,  'type' => 'string', 'desc' => 'ID of MyDisbursement'),
            'response'     => ['required' => true, 'type' => 'string', 'desc' => "Response from Payout Gateway"]
        ];
    }

}