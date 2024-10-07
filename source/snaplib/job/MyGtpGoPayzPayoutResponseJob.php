<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\job;

use DateTime;
use Snap\api\payout\BasePayout;
use Snap\App;
use Snap\manager\ApiManager;
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
class MyGtpGoPayzPayoutResponseJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        /** @var ApiManager $apiMgr */
        $apiMgr = $app->apiManager();

        $date = new DateTime('now', $app->getUserTimezone());
        // $date->modify('-3 days');
        
        $code = $params['partnercode'];

        $this->logDebug(__METHOD__ . '(): -------- Begin GoPayz Payment Response Parsing --------');

        $partner = $app->partnerStore()->getByField('code', $code);
        $settings = $app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $walletName = basename(str_replace('\\', '/', $settings->partnerpaymentprovider));
        $this->logDebug(__METHOD__ . "(): Using {$walletName}Payout class");
        $className = "\\Snap\\api\\payout\\{$walletName}Payout";
        $payout = BasePayout::getInstance($className);

        $formattedDate = $date->format('Ymd');
        // $formattedDate = '20210702';
        $orgCode = $app->getConfig()->{'mygtp.gopayzpayout.code'};
        $responsePath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.responsepath'} . DIRECTORY_SEPARATOR . $orgCode . $formattedDate . '*R.csv.gpg';
        $path = $app->getConfig()->{'mygtp.gopayzpayout.ftp.responsepath'} . DIRECTORY_SEPARATOR;
        $files = glob($responsePath);        

        if (! count($files)) {
            $this->logDebug(__METHOD__ . '(): No payment file response found in ' . $path);
        }

        foreach($files as $f)  {
            
            if (is_file($f)) {
                $name = basename(str_replace('\\', '/', $f));
                
                if (file_exists($path . "processed_{$formattedDate}_$name") || file_exists($path . 'Archive' . DIRECTORY_SEPARATOR . "processed_{$formattedDate}_$name")) {
                    $this->logDebug(__METHOD__ . '(): Skipping previously processed payment response file ' . $name);
                    continue;
                }

                $output = [];
                $this->decrypt($f, $output);
                $this->logDebug(__METHOD__ . '(): -------- Begin handling decrypted response --------');
                $payout->handleResponse(['data' => $output], '');
                $apiMgr->ftpGopayz($name, $output);
                $this->logDebug(__METHOD__ . '(): -------- Finished handling decrypted response --------');
                $this->move($f, $path . "processed_{$formattedDate}_$name");
            }
        }

        $this->logDebug(__METHOD__ . '(): -------- Finished GoPayz Payment Response Parsing --------');
    }

    function describeOptions()
    {
        return [
            'partnercode' => ['required' => true, 'type' => 'string', 'desc' => "Partner code to get the payment provider setting."],
        ];
    }

    private function decrypt($fileName, &$output)
    {
        $this->logDebug(__METHOD__ . '(): -------- Begin decrypting ' . $fileName . ' --------');
        $args = ['--batch', '--yes','--quiet'];
        $cmd = sprintf('gpg %s --decrypt %s', implode(' ', $args), escapeshellarg($fileName));
        $result = $this->run($cmd, $output);
        $this->logDebug(__METHOD__ . '(): ' . json_encode($output));
        $this->logDebug(__METHOD__ . '(): -------- Finished decrypting ' . $fileName . ' --------');

        return $result;
    }

    public function move($source, $destination)
    {
        rename($source, $destination);
        $this->logDebug(__METHOD__ . '(): Flag file ' . $source . ' as processed ' . $destination);
    }

    private function run($cmd, &$output)
    {
        $result = null;
        $output = [];
        exec($cmd, $output, $result);
        // Return true on successfull command invocation
        return $result === 0;
    }
}
