<?php

namespace Snap\api\payout;

use Snap\api\exception\GeneralException;
use Snap\IObservation;
use Snap\object\MyAccountHolder;
use Snap\object\MyDisbursement;

class GoPayzPayout extends BasePayout
{
    protected $code;
    protected $description;
    protected $responsepath;
    protected $requestpath;

    public function __construct($app)
    {
        parent::__construct($app);

        $this->code = $app->getConfig()->{'mygtp.gopayzpayout.code'};
        $this->description = $app->getConfig()->{'mygtp.gopayzpayout.description'};
        $this->responsepath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.responsepath'};
        $this->requestpath = $app->getConfig()->{'mygtp.gopayzpayout.ftp.requestpath'};

        if (! $this->code) {
            throw new \Exception("GoPayz Payment Organization Code not provided in config");
        }

        if (! $this->description) {
            throw new \Exception("GoPayz Payment Organization Description not provided in config");
        }

        if (! $this->responsepath) {
            throw new \Exception("GoPayz Payment FTP Response path not provided in config");
        }

        if (! $this->requestpath) {
            throw new \Exception("GoPayz Payment FTP Request path not provided in config");
        }
    }

    public function handleResponse($params, $body)
    {
        $this->log("----- GoPayzPayout transaction response START -----", SNAP_LOG_DEBUG);
        $data = $params['data'];
        $this->log(json_encode($data), SNAP_LOG_DEBUG);

        $header = [];
        $body = [];
        foreach($data as $i => $line) {
            if (0 === $i) {
                $header = $this->parseHeader($line);
                $this->log("Parsing response file dated {$header[1]} with sequence number: {$data[2]}.", SNAP_LOG_DEBUG);

            } elseif (count($data) - 1 === $i) {
                $footer = $this->parseFooter($line);
                $this->log("Total Records: {$footer[1]}", SNAP_LOG_DEBUG);
                $this->log("Total Amount: {$footer[2]}", SNAP_LOG_DEBUG);
                $this->log("Total Success: {$footer[3]} - Success Amount: {$footer[4]}", SNAP_LOG_DEBUG);
                $this->log("Total Failed: {$footer[5]} - Failed Amount: {$footer[6]}", SNAP_LOG_DEBUG);
            } else {
                try {
                    $startedTransaction = $this->app->getDBHandle()->inTransaction();
                    if (!$startedTransaction) {
                        $ownsTransaction = $this->app->getDBHandle()->beginTransaction();
                    }

                    $this->log("Parsing body {$line}", SNAP_LOG_DEBUG);
                    $lineData = $this->parseBody($line);                
                    $this->processPaymentResponse($header, $lineData);
                    if ($ownsTransaction) {
                        $this->app->getDBHandle()->commit();
                    }
                } catch (\Exception $e) {
                    if ($ownsTransaction) {
                        $this->app->getDBHandle()->rollBack();
                    }

                    $this->log(__METHOD__. "(): ".$e->getMessage(), SNAP_LOG_ERROR);                             
                }                
            }
        }

        $this->log("----- GoPayzPayout transaction response END -----", SNAP_LOG_DEBUG);
    }

    /**
     * Create a payout request
     * 
     * @param MyAccountHolder $accHolder 
     * @param MyDisbursement $disbursement 
     * @return bool 
     */
    public function createPayout($accHolder, $disbursement)
    {
        $now = new \DateTime('now', $this->app->getUserTimezone());
        $date = $now->format('Ymd');
        $sequence = 1;

        if ($this->checkExistingFile($this->code, $date, $sequence)) {
            $this->appendExistingRequestFile($accHolder, $disbursement, $date, $sequence);            
        } else {
            $this->createNewRequestFile($accHolder, $disbursement, $date, $sequence);
        }


        $disbursementStore = $this->app->mydisbursementStore();
        $disbursement->requestedon = $now;
        $disbursement = $disbursementStore->save($disbursement);
        
        return true;
    }

    /**
     * Manually reads payout status from provider's server
     * 
     * @param MyDisbursement $disbursement 
     * @return exit 
     * @throws GeneralException 
     */
    public function getPayoutStatus($disbursement)
    {
        // Manual Payout should not reach here
        throw GeneralException::fromTransaction([], [
            'message'   => "Not implemented"
        ]);

    }

    /**
     * Process the payment response from GOPAYZ for a transaction
     * 
     * Refer to the GOPAYZ Payment Interface specs
     *
     * @param  array $header   File header
     * @param  array $lineData
     * @return bool
     */
    public function processPaymentResponse($header, $lineData)
    {
        $refNo = trim($lineData[2]);

        // 00 Success, 01 Invalid Account ... , 02 Failed
        $statusCode = trim($lineData[9]);
        $statusDesc = trim($lineData[10]);

        $disbursement = $this->app->mydisbursementStore()->getByField('transactionrefno', $refNo);
        $initialStatus = $disbursement->status;

        if ('00' == $statusCode) {
            // $disbursement->bankrefno      = trim($lineData[1]);
            $disbursement->gatewayrefno   = implode("", array_map('trim', $header));

            // Response File previous was using D6 [index 5] for amount 
            // 2021-07-30 Response file is using D7 [index 6] as amount since Response ACE202107280001R.csv.gpg            
            $disbursement->verifiedamount = trim($lineData[6]) / 100;
            $disbursement = $this->app->mydisbursementStore()->save($disbursement);
            $this->log(__METHOD__ . ": Success payment response received for {$refNo}, code {$statusCode} desc {$statusDesc}" , SNAP_LOG_DEBUG);
            $action = IObservation::ACTION_CONFIRM;
        } elseif ('01' == $statusCode) {
            // If previously there was 2 failed request, then we need to flag as cancelled 
            // instead of creating new request for this on next batch
            $times = $this->app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', 'LIKE', "%{$disbursement->transactionrefno}%")->count();
            $action = 2 < $times ? IObservation::ACTION_REJECT : IObservation::ACTION_CHANGEREQUEST;
            $this->log(__METHOD__ . ": Invalid payment response received for {$refNo}, code {$statusCode} desc {$statusDesc}" , SNAP_LOG_ERROR);
        } else {
            $action = IObservation::ACTION_CHANGEREQUEST;
            $this->log(__METHOD__ . ": Failed payment response received for {$refNo}, code {$statusCode} desc {$statusDesc}" , SNAP_LOG_ERROR);
        }

        $this->notify(new IObservation($disbursement, $action, $initialStatus));
    }

    protected function buildHeaderData($orgCode, $date, $sequence)
    {
        $sequence = sprintf('%04d', $sequence);
        return array('H',$orgCode,$date,$sequence);
    }

    protected function buildLineData($txSequence, $refNo, $orgDesc, $accNoIndicator, $accNo, $amount, $txDate, $paymentDesc) 
    {
        $amount = sprintf('%.2f',$amount) * 100;
        $txSequence = sprintf('%07d', $txSequence);
        return array('D',$txSequence,$refNo,$orgDesc,$accNoIndicator,$accNo,$amount,$txDate,$paymentDesc);
    }

    protected function buildFooterData($recordsNum, $totalAmount)
    {        
        return array('T',$recordsNum,$totalAmount);
    }

    protected function parseHeader($line)
    {
        $header = explode(",", $line);
        if (0 < count($header) && 'H' === $header[0]) {
            return $header;
        }

        return false;
    }

    protected function parseBody($line)
    {
        $body = explode(",", $line);
        if (0 < count($body) && 'D' === $body[0]) {
            return $body;
        }

        return false;

    }

    protected function parseFooter($line)
    {
        $footer = explode(",", $line);
        if (0 < count($footer) && 'F' === $footer[0]) {
            return $footer;
        }

        return false;

    }

    public function loadDataFromFile($date, $name) 
    {
        $data = [];
        $file = $this->requestpath . DIRECTORY_SEPARATOR . $name;
        $f = fopen($file, 'r');

        while ($row = fgetcsv($f)) {
            $data[] = $row;
        }
        fclose($f);

        return $data;
    }

    protected function saveDataToFile($data, $date, $name)
    {
        $this->logDebug(__METHOD__ . ': Begin saving ' . $name);
        $file = $this->requestpath . DIRECTORY_SEPARATOR . $name;
        $f = fopen($file, 'w');
        foreach ($data as $i => $row) {            
            $line  = ($i == count($data) - 1) ? implode(',', $row) : implode(',', $row) . "\n";
            fputs($f, $line);
        }
		fclose($f);

        $this->logDebug(__METHOD__ . ': Finished saving' . $name);
        $this->logDebug(__METHOD__ . ': File location - ' . $file);
    }

    protected function checkExistingFile($orgCode, $date, $sequence)
    {
        $sequence = sprintf('%04d', $sequence);
        $file = $this->requestpath  . DIRECTORY_SEPARATOR . $this->generateFileName($orgCode, $date, $sequence);
        return file_exists($file);
    }

    protected function checkExistingDir($date)
    {        
        $file = $this->requestpath . DIRECTORY_SEPARATOR . $date;
        return file_exists($file);
    }

    protected function createNewDir($date)
    {
        $dir = $this->requestpath . DIRECTORY_SEPARATOR . $date;
        mkdir($dir, 0755);
    }

    protected function generateFileName($orgCode, $date, $sequence) {
        $sequence = sprintf('%04d', $sequence);
        return "$orgCode$date$sequence.csv";
    }

    protected function getTotalAmount($data) {
        $totalAmount = 0;
        foreach($data as $row) {
            $totalAmount = $totalAmount + $row[6];
        }

        return $totalAmount;
    }

    /**
     * Create new request file
     *
     * @param MyAccountHolder $accHolder
     * @param MyDisbursement $disbursement
     * @param string $date
     * @param int $sequence
     * @return bool
     */
    protected function createNewRequestFile($accHolder, $disbursement, $date, $sequence)
    {
        $this->logDebug(__METHOD__ . ': Begin create new payment request file.');

        $data = [];
        $file = $this->generateFileName($this->code, $date, $sequence);
        $data[] = $this->buildHeaderData($this->code, $date, $sequence);
        $txSequence = 1;
        $amount = $disbursement->amount - $disbursement->fee;
        $data[] = $this->buildLineData($txSequence, $disbursement->transactionrefno, $this->description, 1, $accHolder->partnercusid, $amount, $date, 'Gold Sell');
        
        $totalAmount = sprintf('%.2f',$amount) * 100;        
        $data[] = $this->buildFooterData($txSequence, $totalAmount);
        $this->saveDataToFile($data, $date, $file);
        $this->logDebug(__METHOD__ . ': Finished creating payment request file ' . $file);
        return true;
    }

    /**
     * Append new line data to existing request file for the day
     *
     * @param MyAccountHolder $accHolder
     * @param MyDisbursement $disbursement
     * @param string $date
     * @return bool
     */
    protected function appendExistingRequestFile($accHolder, $disbursement, $date, $sequence)
    {
        $file = $this->generateFileName($this->code, $date, $sequence);
        $this->logDebug(__METHOD__ . ': Begin appending record to ' . $file);
        $data = $this->loadDataFromFile($date, $file);
        $txSequence = count($data) - 1;
        $amount = $disbursement->amount - $disbursement->fee;
        $line = $this->buildLineData($txSequence, $disbursement->transactionrefno, $this->description, 1, $accHolder->partnercusid, $amount, $date, 'Gold Sell');

        // Remove header
        $header = array_shift($data);
        
        // Replace footer line with new data line
        $data[$txSequence - 1] = $line;
        $totalAmount = $this->getTotalAmount($data);

        // Re-Insert header
        array_unshift($data, $header);

        // Push new footer
        $data[] = $this->buildFooterData($txSequence, $totalAmount);
        $this->saveDataToFile($data, $date, $file);
        $this->logDebug(__METHOD__ . ': Finished appending record to ' . $file);
        return true;
    }
}
?>