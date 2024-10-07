<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use DateTime;
use Exception;
use Snap\api\payout\BasePayout;
use Snap\App;
use Snap\manager\MyGtpDisbursementManager;
use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\object\Partner;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 * @package snap.job
 */
class MyGtpGoldTransferJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        // if (! isset($params['partneraccountholderid']) || 0 == strlen($params['partneraccountholderid'])) {
        //     $this->logDebug(__METHOD__ . '(): No partneraccountholderid was given');
        //     return;
        // }

        if (! isset($params['partnercode']) || 0 == strlen($params['partnercode'])) {
            $this->logDebug(__METHOD__ . '(): No partner code was given');
            return;
        }

        // if (! isset($params['xau']) || 0 >= $params['xau']) {
        //     $this->logDebug(__METHOD__ . '(): Invalid xau amount or no xau amount was given');
        //     return;
        // }

        // if (! isset($params['price']) || 0 >= $params['price']) {
        //     $this->logDebug(__METHOD__ . '(): Invalid price or no price was given');
        //     return;
        // }

        if (! isset($params['file']) || 0 == strlen($params['file'])) {
            $this->logDebug(__METHOD__ . '(): No file location was given');
            return;
        }
        
        $this->logDebug(__METHOD__ . '(): -------- Begin Gold Transfer Job --------');

        try {
            $partnercode            = $params['partnercode'];
            // $partneraccountholderid = $params['partneraccountholderid'];
            $file                   = $params['file'];
            // $xau                    = $params['xau'];
            // $price                  = $params['price'];

            if (! file_exists($file)) {
                throw new \Exception('File could not found');
            }
    
            /** @var Partner $partner */
            $partner = $app->partnerStore()->getByField('code', $partnercode);
            /** @var MyAccountHolder $sender */
            // $sender = $partner;
            
            // if ((! $sender) || $partner->id != $sender->partnerid) {
            //     $this->log(__METHOD__ . "(): Partner ({$partner->code}) accountholder record could not be found", SNAP_LOG_ERROR);
            //     throw new \Exception("Partner ({$partner->code}) accountholder record could not be found");
            // }
            $preCheck = $params['precheck'] ?? true;
            $skipHeader = $params['skipheader'] ?? true;
           
            /** @var MyGtpTransactionManager $txMgr  */
            $txMgr = $app->mygtptransactionManager();            
            $txMgr->importGoldTransfer($partner, $file, $skipHeader, $preCheck);
            
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error when trying to process gold transfer for partner ({$partnercode}), " . $e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        } finally {
            $this->logDebug(__METHOD__ . '(): -------- Finished Gold Transfer Job --------');
        }

    }

    public function creditToCustomer($from, $to, $amount, $remarks)
    {
        
    }

    function describeOptions()
    {
        return [
            'partnercode' => ['required' => true, 'type' => 'string', 'desc' => "Partner code to get the payment provider setting."],
            'file' => ['required' => true, 'type' => 'string', 'desc' => "The file contains the transfer list to import"],
            'precheck' => ['required' => false, 'type' => 'int', 'desc' => "Run pre-check instead without real import / transfer. Default to true"],
            'skipheader' => ['required' => false, 'type' => 'int', 'desc' => "Skip the first line of file. Default to true"],
            // 'xau' => ['required' => true, 'type' => 'string', 'desc' => "The amount of xau to be transferred"],
            // 'partneraccountholderid' => ['required' => true, 'type' => 'string', 'desc' => "Partner accountholderid."],
        ];
    }
}
