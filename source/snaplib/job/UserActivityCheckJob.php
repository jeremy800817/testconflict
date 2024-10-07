<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\object\MyGoldTransaction;
use Snap\object\User;

use Exception;
use Throwable;
use DateTime;

/**
 *
 * @author Chen <chen.teng.siang@silverstream.my>
 * @version 1.0my
 * @package  snap.job
 */
class UserActivityCheckJob  extends basejob
{
    protected $arr = array();
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()){
        try{
            $this->log("start: ". __CLASS__);
            $expiredcutOff = $app->getConfig()->{'user.expired.cutoffday'};
            $expiredcutOffTrx = $app->getConfig()->{'user.expired.expiredcutOffTrx'};
            $change = '-'.$expiredcutOff.' days';
            $changeTrx = '-'.$expiredcutOffTrx.' days';
            
            $now = new DateTime();
            $date = new DateTime($change);
            $dateTrx = new DateTime($changeTrx);
            $cutoff = new DateTime('2023-01-01 00:00:00');
            $user = $app->userStore()->searchTable()->select()
                ->where('createdon', '>=', $cutoff->format('Y-m-d H:i:s'))
                ->andWhere('status',User::STATUS_ACTIVE)
                ->get();

            $this->log("cutoff date: ". $date->format('Y-m-d H:i:s'));
            foreach($user as $a){
                $confirm = false;

                $paymentcheck = $app->mypaymentdetailStore()->searchTable()->select()
                    ->where('createdby',$a->id)
                    ->orderBy('id','desc')
                    ->one();
                
                $registercheck = $app->myaccountholderStore()->searchTable()->select()
                    ->andWhere('createdby',$a->id)
                    ->orderBy('id','desc')
                    ->one();

                $transfergoldcheck = $app->mytransfergoldStore()->searchTable()->select()
                    ->andWhere('createdby',$a->id)
                    ->orderBy('id','desc')
                    ->one();

                if($a->lastlogin < $date && $a->lastlogin->format('Y-m-d H:i:s') != '-0001-11-30 00:00:00'){
                    $this->log(__CLASS__ .": id-> ". $a->id ." status change due to last login. Cutoff date -> ".$date->format('Y-m-d H:i:s')." Last Login -> ".$a->lastlogin->format('Y-m-d H:i:s'));
                    $confirm = true;
                }else{
                    if(($paymentcheck && $paymentcheck->createdon < $dateTrx) || !$paymentcheck){
                        if(($registercheck && $registercheck->createdon < $dateTrx) || !$registercheck){
                            if(($transfergoldcheck && $transfergoldcheck->createdon < $dateTrx) || !$transfergoldcheck) $confirm = true;
                        }
                    } 
                    
                }

                if($confirm){
                    //echo $a->username."\n";
                    $this->log(__CLASS__ .": id-> ". $a->id ." change status to Expired");
                    $a->status = User::STATUS_EXPIRED;
                    $a = $app->userStore()->save($a);
                }
            }

        }
        catch(Throwable $e){
            echo $e->getMessage()." at line ".__LINE__;
            $this->addErrorLog($e->getMessage());
        }
        
        
    }
    
    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *            'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *            'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    function describeOptions()
    {
        // return [
        //     'partner' => array('required' => true, 'type' => 'int', 'desc' => 'partner id')
        // ];
    }
}
