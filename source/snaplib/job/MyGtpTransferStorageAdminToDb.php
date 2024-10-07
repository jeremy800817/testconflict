<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use Exception;
use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;

/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @author   Dianah <dianah@silverstream.my>
 * @version  1.0my
 * @package  snap.job
 */

class MyGtpTransferStorageAdminToDb extends basejob
{
	public function doJob($app, $params = array())
    {
        //trigger : php /usr/local/nginx/html/ktp/source/snaplib/cli.php -c /usr/local/nginx/html/ktp/source/snapapp/gopayz.ini -f /usr/local/nginx/html/ktp/source/snaplib/job/MyGtpManualTransferOrderJob.php -p "orderid=xxx,xxx,xxx"

        if(isset($params['partnerid'])) $partnerid = $params['partnerid'];
        if(isset($params['store'])) $store = $params['store'];
        if(isset($params['version'])) $version = $params['version'];
        $partner        = $app->partnerStore()->getById($partnerid);

        if($store == "mymonthlystoragefee") $transactions = $app->mymonthlystoragefeeStore()->searchView()->select()->where('partnerid', $partnerid)->execute();
        
        $this->log("########START SENDING ".$store." FOR PARTNER ".$partner->code." BETWEEN DB##########", SNAP_LOG_DEBUG);
        $transferTransaction = $app->apiManager()->transferTransactionBetweenDb($partner,$transactions,$store,$version); 
        $this->log("########END SENDING ".$store." FOR PARTNER ".$partner->code." BETWEEN DB##########", SNAP_LOG_DEBUG);
            
    }

    public function describeOptions()
    {
        /*return [
            'partnerids' => array('required' => true,  'type' => 'string', 'desc' => 'Comma separated list of partner ids'),
            'month' => array('required' => false,  'type' => 'int', 'desc' => 'The month to of the storage fee to submit to SAP'),
            'latestpricedate' => array('required' => false,  'type' => 'int', 'desc' => 'Set to 1 to select latest pricestream >= 8:30 AM'),
        ];*/
    }
	
}