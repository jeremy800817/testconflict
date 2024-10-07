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
class RetrieveLogisticLogUpdateJob extends basejob
{

    public function doJob($app, $params = array())
    {
        $this->log("RetrieveLogisticLogUpdateJob start", SNAP_LOG_INFO);
        try{
            $masterid = $params['masterid'];

            $partners = $app->partnerStore()->searchTable()->select()->where('group',$masterid)->execute();

            //to grab merchant id for api
            $partnerGtp = $app->partnerStore()->searchTable()->select()->where('id',$masterid)->one();
            $partnerCodeGtp = $partnerGtp->code;

            $partnerids = [];
            foreach($partners as $partner){
                array_push($partnerids,$partner->id);
            }

            $logistic = $app->logisticStore()->searchTable()->select()->where('status','!=',Logistic::STATUS_COMPLETED)->andWhere('partnerid', 'IN', $partnerids)->execute();

            $typeIds = [];
            foreach($logistic as $record){
                array_push($typeIds,$record->typeid);
            }

            $redemption = $app->redemptionStore()->searchTable()->select()->where('id','IN', $typeIds)->execute();

            if(count($redemption)> 0){
                foreach($redemption as $record){
                    
                    $response = $app->apiManager()->getLogisticRecordFromGTP($record, $partnerCodeGtp);

                    $data = json_decode($response, true);
                    if($data['success']){
                        $logistic = $data['data']['logistic'];
                        $logisticlog = $data['data']['logisticlog'];
                        
                        if(count($logistic) > 0){
                            $lgs = $app->logisticStore()->searchTable()->select()->where('typeid',$record->id)->one();
                            $lgs->status = $logistic['status'];
                            $lgs = $app->logisticStore()->save($lgs);
                        }
                        
                        foreach($logisticlog as $rc){
                            if(isset($rc['id'])) unset($rc['id']);
                        }
                        $savedlogisticlog = $this->saveLogisticLog($app, $logisticlog, $record);
                        
                    }
                }
            }
        }
        catch(\Throwable $e){
            $this->log("RetrieveLogisticLogUpdateJob encountered error: ".$e->getMessage(), SNAP_LOG_INFO);
        }

        $this->log("RetrieveLogisticLogUpdateJob end", SNAP_LOG_INFO);
    }

    public function saveLogisticLog($app, $logisticlog, $redemption){
        $logistic = $app->logisticStore()->searchTable()->select()->where('typeid', $redemption->id)->andWhere('partnerid', $redemption->partnerid)->one();
		
        $existinglog = $app->logisticlogStore()->searchTable()->select()->where('logisticid', $logistic->id)->execute();
        $values = [];
        foreach($existinglog as $record){
            array_push($values, $record->value);
        }

        foreach($logisticlog as $record){
            if(!in_array($record['value'], $values)){
                $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
                $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
                $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
				
				if(isset($record['id'])) unset($record['id']);

                $record['createdon'] = $triggerOn;
                $record['modifiedon'] = $triggerOn;
                $record['logisticid'] = $logistic->id;

                $savedlogisticlog = $app->logisticlogStore()->create($record);
                $savedlogisticlog = $app->logisticlogStore()->save($savedlogisticlog);
            }

        }
    }


    public function describeOptions()
    {
        return [
            'masterid' => array('required' =>  false,  'type' => 'int', 'desc' => 'refer to par_group column in partner table')
        ];
    }

}