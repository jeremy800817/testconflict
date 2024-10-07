<?php

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2021
* @copyright Silverstream Technology Sdn Bhd. 2021

* @author Rinston Oliver <rinston@silverstream.my>
* @version 1.0
* @created 17-Jul-2022
*/

namespace Snap\job;

use Snap\App;
use Snap\object\Logistic;
use Snap\object\Redemption;
use Snap\object\Partner;
use Exception;
use Throwable;
/**
 * 
 *  
 * 
 * @package Snap\job
 */
class RetrieveRedemptionItemsJob extends basejob
{

    public function doJob($app, $params = array())
    {
        $this->log("RetrieveRedemptionItemsJob start", SNAP_LOG_INFO);
        try{
            $cutoffdate = New \DateTime("2 weeks ago");

            $redemption = $app->redemptionStore()->searchTable()->select()
                ->where('gtpstatus','IN', [Redemption::GTPSTATUS_PENDINGSAP, Redemption::GTPSTATUS_FAILTRANSFER])
                ->andWhere('status','IN',[Redemption::STATUS_CONFIRMED])
                ->andWhere('createdon','>=',$cutoffdate->format('Y-m-d 00:00:00'))
                ->execute();
            
            if(count($redemption)> 0){
                foreach($redemption as $record){
                    $response = $app->apiManager()->getRedemptionItemsFromGTP($record);

                    $data = json_decode($response, true);
                    if($data['success']){
                        $record->items = $data['data']['redemptionItems'];
                        $record->gtpstatus = Redemption::GTPSTATUS_SUCCESS;
                        $updateRedemption = $app->redemptionStore()->save($record);
                    }
                    else{
                        echo "RetrieveRedemptionItemsJob return false: ".$data['error_message'];
                        $this->log("RetrieveRedemptionItemsJob return false: ".$data['error_message'], SNAP_LOG_INFO);            
                    }
                }
            }
        }
        catch(\Throwable $e){
            echo $e->getMessage();
            $this->log("RetrieveRedemptionItemsJob encountered error: ".$e->getMessage(), SNAP_LOG_INFO);
        }

        $this->log("RetrieveRedemptionItemsJob end", SNAP_LOG_INFO);
    }

    public function describeOptions()
    {
        return [
            'masterid' => array('required' =>  false,  'type' => 'int', 'desc' => 'refer to par_group column in partner table')
        ];
    }

}