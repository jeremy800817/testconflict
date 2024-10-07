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
class RetrieveLogisticDataFromGtpJob extends basejob
{

    public function doJob($app, $params = array())
    {
        $this->log("RetrieveLogisticDataFromGtpJob start", SNAP_LOG_INFO);
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

            $logistic = $app->logisticStore()->searchTable()->select()
                ->where('partnerid', 'IN', $partnerids)
                ->execute();


            if($logistic){
                $typeIds = [];
                foreach($logistic as $record){
                    array_push($typeIds,$record->typeid);
                }

                $redemption = $app->redemptionStore()->searchTable()->select()
                    ->where('partnerid', 'IN', $partnerids)
                    ->andWhere('id', 'NOT IN', $typeIds)
                    ->andWhere('createdon', '>', '2023-07-18 16:59:59')
                    ->execute();
            }else{
                $redemption = $app->redemptionStore()->searchTable()->select()
                    ->where('partnerid', 'IN', $partnerids)
                    ->andWhere('createdon', '>', '2023-07-18 16:59:59')
                    ->execute();
            }
            // $redemption = $app->redemptionStore()->searchTable()->select()->where('redemptionno', 'E2307150001D')->execute();

            if(count($redemption)> 0){
                foreach($redemption as $record){
                    $response = $app->apiManager()->getLogisticRecordFromGTP($record, $partnerCodeGtp);

                    //echo $response."\n";

                    $data = json_decode($response, true);
                    if($data['success']){

                        $logistic = $data['data']['logistic'];
                        $logisticlog = $data['data']['logisticlog'];

                        $savedlogistic = false;
                        if(count($logistic) > 0){
                            if(isset($logistic['id'])) unset($logistic['id']);
                            $savedlogistic = $this->saveLogistic($app, $logistic, $record);
                        }

                        if($savedlogistic){
                            foreach($logisticlog as $record){
                                if(isset($record['id'])) unset($record['id']);
                            }
                            $savedlogisticlog = $this->saveLogisticLog($app, $logisticlog, $savedlogistic);
                        }
                    }
                }
            }
        }
        catch(\Throwable $e){
            $this->log("RetrieveLogisticDataFromGtpJob encountered error: ".$e->getMessage(), SNAP_LOG_INFO);
        }

        $this->log("RetrieveLogisticDataFromGtpJob end", SNAP_LOG_INFO);
    }

    public function saveLogistic($app, $logistic, $redemption){
        $schedule_date = new \DateTime("now", new \DateTimeZone("UTC") );
        $schedule_date->setTimeZone(new \DateTimeZone('Asia/Kuala_Lumpur'));
        $triggerOn =  $schedule_date->format('Y-m-d H:i:s');
		
		$logistic['partnerid'] = $redemption->partnerid;
		$logistic['typeid'] = $redemption->id;
        $logistic['createdon'] = $triggerOn;
        $logistic['modifiedon'] = $triggerOn;

        $savedlogistic = $app->logisticStore()->create($logistic);
        $savedlogistic = $app->logisticStore()->save($savedlogistic);

        return $savedlogistic;
    }

    public function saveLogisticLog($app, $logisticlog, $logistic){
        foreach($logisticlog as $record){
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

        return true;
    }


    public function describeOptions()
    {
        return [
            'masterid' => array('required' =>  false,  'type' => 'int', 'desc' => 'refer to par_group column in partner table')
        ];
    }

}