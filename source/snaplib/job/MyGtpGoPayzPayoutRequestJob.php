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
use Snap\manager\ApiManager;
use Snap\manager\MyGtpDisbursementManager;
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
class MyGtpGoPayzPayoutRequestJob extends basejob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array())
    {
        if (! isset($params['partnercode']) || 0 == strlen($params['partnercode'])) {
            $this->logDebug(__METHOD__ . '(): No partner code was given');
            return;
        }
        $this->logDebug(__METHOD__ . '(): -------- Begin GoPayz Payment File Request Creation --------');

        try {
            $partnercode = $params['partnercode'];            
            $partner = $app->partnerStore()->getByField('code', $partnercode);
            
            $now = new \DateTime('now',$app->getUserTimezone());
            $dateStart = new \DateTime($now->format('Y-m-d 00:00:00'), $app->getUserTimezone());
            $dateEnd   = new \DateTime($now->format('Y-m-d 23:59:59'), $app->getUserTimezone());            
    
            $dateStart->setTimezone($app->getServerTimezone());
            $dateEnd->setTimezone($app->getServerTimezone());
            
            $fileCreated = $this->processPendingPaymentGoldTransactions($app, $partner, $now, $dateStart, $dateEnd);
            if ($fileCreated) {
                $this->encryptPaymentFile($app,$now);
            } else {
                $this->log(__METHOD__ . "(): Payment file not created due to no pending transactions for partner({$partnercode})", SNAP_LOG_INFO);
                $this->logDebug(__METHOD__ . '(): -------- Begin creating empty payment file --------');
                $this->createEmptyFile($app,$now);
                $this->logDebug(__METHOD__ . '(): -------- Finished creating empty payment file --------');
                $this->encryptPaymentFile($app,$now);                
            }

        } catch (\Exception $e) {
            $this->log(__METHOD__ . "(): Error when trying to generate payment request file for partner ({$partnercode}), " . $e->getMessage(), SNAP_LOG_ERROR);
        }

        $this->logDebug(__METHOD__ . '(): -------- Finished GoPayz Payment File Request Creation --------');
    }

    private function createEmptyFile($app, $date)
    {
        $orgCode       = $app->getConfig()->{'mygtp.gopayzpayout.code'};
        $formattedDate = $date->format('Ymd');
        $outputPath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'} . DIRECTORY_SEPARATOR;
        $requestPath   = $app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'} . DIRECTORY_SEPARATOR . "{$orgCode}{$formattedDate}*.csv";
        
        $files = glob($requestPath);
        $sequence = count($files) + 1;
        $sequence = sprintf('%04d', $sequence);

        $name = "$orgCode$formattedDate$sequence.csv";
        $file = $outputPath . DIRECTORY_SEPARATOR . $name;
        
        $header = "H,$orgCode,$formattedDate,$sequence" . PHP_EOL;
        $footer = 'T,0,0' . PHP_EOL;
        $this->logDebug(__METHOD__ . ': Begin saving ' . $name);

        $f = fopen($file, 'w');        
        fputs($f, $header);
        fputs($f, $footer);
		fclose($f);

        $this->logDebug(__METHOD__ . ': Finished saving' . $name);
        $this->logDebug(__METHOD__ . ': File location - ' . $file);
    }

    private function encryptPaymentFile($app, $date)
    {
        $formattedDate = $date->format('Ymd');
        $code = $app->getConfig()->{'mygtp.gopayzpayout.code'};
        $requestPath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'} . DIRECTORY_SEPARATOR . "{$code}{$formattedDate}*.csv";
        $outputPath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'} . DIRECTORY_SEPARATOR;
        $fingerprint = $app->getConfig()->{'mygtp.gopayzpayout.keyfingerprint'};

        /** @var ApiManager $apiMgr */
        $apiMgr = $app->apiManager();

        $files = glob($requestPath);

        foreach($files as $f)  {
            if (is_file($f)) {
                $this->encrypt($f, $fingerprint, $outputPath . basename($f));
            }

            $apiMgr->ftpAceToGopayz(basename($f));
        }
    }

    private function processPendingPaymentGoldTransactions($app, $partner, $now, $dateStart, $dateEnd)
    {
        $settings = $app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        $walletName = basename(str_replace('\\', '/', $settings->partnerpaymentprovider));
        
        $this->logDebug(__METHOD__ . ": Using {$walletName}Payout class for processing payment");
        
        $className = "\\Snap\\api\\payout\\{$walletName}Payout";
        $payout = BasePayout::getInstance($className);

        /** @var MyGtpDisbursementManager $disbursementMgr */
        $disbursementMgr = $app->mygtpDisbursementManager();

        /** @var MyGtpTransactionManager $goldTxMgr */
        // $goldTxMgr = $app->mygtptransactionManager();
        /** @var MyGoldTransaction[] $pendingPaymentGoldTx  */
        $pendingPaymentGoldTx = $app->mygoldtransactionStore()->searchView()->select()
                                        ->where('ordpartnerid', $partner->id)
                                        ->whereIn('status', [MyGoldTransaction::STATUS_CONFIRMED, MyGoldTransaction::STATUS_PENDING_PAYMENT])
                                        ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                                        ->whereIn('ordstatus', [Order::STATUS_CONFIRMED, Order::STATUS_PENDING])
                                        ->andWhere('settlementmethod', MyGoldTransaction::SETTLEMENT_METHOD_WALLET)
                                        ->whereNull('dbmstatus')
                                        // ->andWhere('createdon', '>=', $dateStart->format('Y-m-d H:i:s'))
                                        ->andWhere('createdon', '<=', $dateEnd->format('Y-m-d H:i:s'))
                                        ->orderby('createdon', 'asc')
                                        ->execute();

        /** @var MyAccountHolder[] $accHolders */
        $accHolders = [];        

        $count = count($pendingPaymentGoldTx);
        $this->logDebug(__METHOD__ . "(): Found {$count} pending transactions for partner {$partner->code}");

        foreach ($pendingPaymentGoldTx as $goldTx) {
            
            $this->logDebug(__METHOD__ . '(): -------- Begin processing transaction ' . $goldTx->refno . ' --------');

            if (! isset($accHolders[$goldTx->ordbuyerid])) {
                $accHolders[$goldTx->ordbuyerid] = $app->myaccountHolderStore()->getById($goldTx->ordbuyerid);
            }
            
            try {
                if (0 < strlen($accHolders[$goldTx->ordbuyerid]->partnercusid)) {
                    $disbursement = $disbursementMgr->createWalletDisbursementForGoldTransaction($goldTx, $partner, $accHolders[$goldTx->ordbuyerid], $walletName);
                    $payout->createPayout($accHolders[$goldTx->ordbuyerid], $disbursement);
                    $this->logDebug(__METHOD__ . '(): -------- Finished processing transaction ' . $goldTx->refno . ' --------');
                } else {
                    $code = $accHolders[$goldTx->ordbuyerid]->accountholdercode;
                    $this->log(__METHOD__ . "(): Error account holder ({$code}) does not have wallet account number" . $goldTx->refno, SNAP_LOG_ERROR);

                }
            } catch (\Exception $e) {
                $this->log(__METHOD__ . '(): Error while trying to create GoPayz disbursement for gold transaction ' . $goldTx->refno . ' ' . $e->getMessage());
            }
            
        

        }
        
        return 0 < count($accHolders);
    }


    function describeOptions()
    {
        return [
            'partnercode' => ['required' => true, 'type' => 'string', 'desc' => "Partner code to get the payment provider setting."],            
        ];
    }

    private function encrypt($fileName, $fingerprint, $outputName = null)
    {        
        $this->logDebug(__METHOD__ . '(): -------- Begin encrypting ' . $fileName . '--------');
        // $args = ['--batch', '--yes','--quiet', '--output ' . escapeshellarg($outputName)]; 
        $args = ['--batch', '--yes','--quiet'];        
        $cmd = sprintf('gpg %s -r %s --encrypt %s', implode(' ', $args), escapeshellarg($fingerprint), escapeshellarg($fileName));
        $result = $this->run($cmd);
        $this->logDebug(__METHOD__ . '(): -------- Finished encrypting ' . $fileName . '--------');

        return $result;
    }

    private function run($cmd)
    {
        $result = null;
        $output = [];
        exec($cmd, $output, $result);
        // Return true on successfull command invocation
        return $result === 0;
    }
}
