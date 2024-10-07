<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Snap\App;
use Snap\Object\MyTransferGold;
use Exception;

/**
 *
 * @author Rinston <rinston@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessPendingApprovalTransferGoldJob  extends basejob
{
    protected $arr = array();
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()){
		if($app->getConfig()->{'otc.job.diffserver'} == '1'){
			$partnerid = $params['partnerids'];
            $partner = $app->partnerStore()->searchTable()->select()->where('group', $partnerid)->execute();
            $partnerIds = [];
            foreach($partner as $record){
                array_push($partnerIds, $record->id);
            }
		}
		else{
			$partnerIds = explode(',', $params['partnerids']);
		}
        $cutOffTime = new \DateTime('3 minute ago');
        $cutOffTimeFormatted = $cutOffTime->format('Y-m-d H:i:s');
        $this->log("Checking for pending transactions before ". $cutOffTimeFormatted, SNAP_LOG_INFO);
        $requireapprovaltransfergold = $app->mytransfergoldStore()->searchView()->select()
                                        ->where('createdon', '<=', $cutOffTimeFormatted)
                                        ->andWhere('partnerid', 'in', $partnerIds)
                                        ->andWhere('status', MyTransferGold::STATUS_REQUIREAPPROVAL)
                                        ->execute();
        if(count($requireapprovaltransfergold) > 0){
            foreach($requireapprovaltransfergold as $record){
                echo $record->refno." here\n";
                try{
                    $startedTransaction = $app->getDBHandle()->inTransaction();
                    if (!$startedTransaction) {
                        $ownsTransaction = $app->getDBHandle()->beginTransaction();
                    }
                    $transfergold = $record;
                    $transfergold->status = MyTransferGold::STATUS_TIMEOUTAPPROVAL;
                    $transfergold = $app->mytransfergoldStore()->save($transfergold);
                    if($ownsTransaction){
                        $app->getDBHandle()->commit();
                    }
                }
                catch(\Throwable $e){
                    if($ownsTransaction){
                        $app->getDBHandle()->rollback();
                    }
                }
            }
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
        return [];
    }
}
