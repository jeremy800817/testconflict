<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author Nurdianah <dianah@silverstream.my>
 * @version 1.0
 * @package  snap.job
 */
class ProcessBulkPaymentResponse extends basejob {

	/**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array()) {
        if(!defined('SNAPAPP_DBACTION_USERID')) define('SNAPAPP_DBACTION_USERID', 2);
        $now        = new \DateTime('now', $app->getUserTimezone());
        $nowDate    = new \DateTime($now->format('Y-m-d H:i:s'), $app->getUserTimezone()); 
        $curDate    = $nowDate->format('Y-m-d H:i:s'); //GMT
        
        /*get file path*/
        if(isset($params['file'])) $file = $params['file'];

        if (! isset($file) || 0 == strlen($file)) {
            $this->logDebug(__METHOD__ . '(): No file location was given');
            return;
        }

        $fullpath = $app->getConfig()->{'gtp.bulkpayment.response'}.$file.'.TXT';

        $this->logDebug(__METHOD__ . '(): -------- Begin Update Status for Bulk Payment {$file} --------');

        try {
            if (! file_exists($fullpath)) {
                throw new \Exception("File could not found {$fullpath}");
            }

            $apiManager = $app->apiManager();
            $response = $apiManager->processMBBResponse($curDate,$file);

            $disbursementManager = $app->mygtpdisbursementManager();
            $update = $disbursementManager->updateStatusFromMbbResponse($response);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error when trying to process payment status for file {$file} " . $e->getMessage(), SNAP_LOG_ERROR);
            throw $e;
        } finally {
            $this->logDebug(__METHOD__ . '(): -------- Finished Update Status for Bulk Payment {$file} --------');
        }
    }

    function describeOptions() {
    }

}

?>