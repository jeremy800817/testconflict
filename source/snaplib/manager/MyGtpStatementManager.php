<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\manager;

use Snap\IObservable;
use Snap\object\MyAccountHolder;
use Snap\object\MyLedger;
use Spipu\Html2Pdf\Html2Pdf;

class MyGtpStatementManager implements IObservable
{
    use \Snap\TLogging;
    use \Snap\TObservable;

    private $app = null;

    public $startDate;
    public $endData;
    public $data;
    public $type;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the statement records for the account holder. If startDate and endDate is null, get all records
     *
     * @param MyAccountHolder $accountHolder
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param Closure/array $condition
     * @return array
     */
    public function getStatementRecords(MyAccountHolder $accountHolder, $startDate = null, $endDate = null, $condition = null)
    {
        $query = $this->app->myledgerStore()
            ->searchView(false, 1)
            ->select();

        if ($startDate) {
            $startDate = \Snap\common::convertUserDatetimeToUTC($startDate);
            $query->where('transactiondate', '>=', $startDate->format('Y-m-d H:i:s'));
        }

        if ($endDate) {
            $endDate = \Snap\common::convertUserDatetimeToUTC($endDate);
            $query->where('transactiondate', '<=', $endDate->format('Y-m-d H:i:s'));
        }

        if ($condition) {
            $query->where($condition);
        }

        $query->where('status', MyLedger::STATUS_ACTIVE);

        $records = $query->where('accountholderid', $accountHolder->id)->execute();

        return $records;
    }

    /**
     * Get the statement balance for the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  \DateTime $untilDate
     * @return array
     */
    public function getStatementOpeningBalance(MyAccountHolder $accountHolder, $untilDate, $prefix = 'led_')
    {
        $records = $this->getStatementRecords($accountHolder, null, $untilDate);

        $totalAmountIn  = 0;
        $totalAmountOut = 0;
        $totalXauIn     = 0;
        $totalXauOut    = 0;

        foreach ($records as $key => $record) {
            $totalAmountIn  = $totalAmountIn + $records[$key]['led_amountin'];
            $totalAmountOut = $totalAmountOut + $records[$key]['led_amountout'];
            $totalXauIn     = $totalXauIn + $records[$key]['led_debit'];
            $totalXauOut    = $totalXauOut + $records[$key]['led_credit'];
        }

        $openingBalance = $this->fillRecord(
            $accountHolder, 
            'Opening Balance', 
            $totalXauIn, 
            $totalXauOut, 
            $totalAmountIn, 
            $totalAmountOut, 
            $prefix
        );

        return $openingBalance;
    }

    /**
     * Fill statement array with data
     *
     * @param MyAccountHolder $accountHolder
     * @param float  $type
     * @param float  $totalXauIn
     * @param float  $totalXauOut
     * @param float  $totalAmountIn
     * @param float  $totalAmountOut
     * @param string $prefix
     * @return void
     */
    public function fillRecord(MyAccountHolder $accountHolder, $type, $totalXauIn = null, $totalXauOut = null, $totalAmountIn = null, $totalAmountOut = null, $prefix = '')
    {
        return  array(
            $prefix . 'id' => null,
            $prefix . 'type' => $type,
            $prefix . 'accountholderid' => $accountHolder->id,
            $prefix . 'accountholdercode' => $accountHolder->accountholdercode,
            $prefix . 'accountholderfullname' => $accountHolder->fullname,
            $prefix . 'typeid' => null,
            $prefix . 'credit' => number_format($totalXauOut, 3, '.', ''),
            $prefix . 'debit' => number_format($totalXauIn, 3, '.', ''),
            $prefix . 'refno' => null,
            $prefix . 'transactiondate' => null,
            $prefix . 'goldprice' => null,
            $prefix . 'prevxaubalance' => null,
            $prefix . 'xaubalance' =>  number_format($totalXauOut - $totalXauIn, 3, '.', ''),
            $prefix . 'amountin' =>  number_format($totalAmountIn, 2, '.', ''),
            $prefix . 'amountout' =>  number_format($totalAmountOut, 2, '.', ''),
            $prefix . 'prevamountbalance' => null,
            $prefix . 'amountbalance' => number_format($totalAmountIn, 2, '.', '') - number_format($totalAmountOut, 2, '.', ''),
            $prefix . 'status' => 1
        );
    }

    /**
     * Get list of valid statement months for the account holder 
     * in the format array(array(m, Y),array(m, Y), ...)
     *
     * @param  MyAccountHolder $accountHolder
     * 
     * @return array
     */
    public function getValidStatementMonths(MyAccountHolder $accountHolder)
    {
        $date     = $accountHolder->createdon->format('Y-m-d H:i:s');
        $start    = (new \DateTime($date, $this->app->getUserTimezone()))->modify('first day of this month');
        $end      = (new \DateTime('now', $this->app->getUserTimezone()))->modify('first day of this month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);
        $data     = array();

        foreach ($period as $dt) {
            $month = array(
                $dt->format("m"),
                $dt->format("Y")
            );

            array_push($data, $month);
        }

        if (1 == count($data)) {
            return [];
        }

        return $data;
    }

    /**
     * Export the html content string as pdf string
     * @param  string $html
     * @param  string $orientation
     * @return string
     */
    public function exportHtmlAsPdfString($html, $orientation = 'L')
    {
        if ('L' !== $orientation) {
            $orientation = 'P';
        }

        $html2pdf = new Html2Pdf($orientation, 'A4', 'en', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->writeHTML($html);

        // For $dest = S, $name is ignored, as we are returning string
        return $html2pdf->output('export.pdf', 'S');
    }

    /**
     * Export the statement html to excel
     * @param  string   $html
     * @param  string   $filename
     * @param  callable $formatter
     * 
     * @return string
     */
    public function exportHtmlAsExcel($html, $filename, $formatter = null, $saveToFile = false)
    {
        try {

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($html);

            if (is_callable($formatter)) {
                $spreadsheet = $formatter($spreadsheet);
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($saveToFile) {
                $temp = tmpfile();
                $writer->save($temp);
                return $temp;
            }

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for $filename", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    /**
     * Format statement records value
     *
     * @param array $records
     * @return array
     */
    public function formatStatementRecords($records, $prefix = 'led_', $index = 1)
    {
        foreach ($records as $key => $record) {
            
            $records[$key][$prefix . 'index'] = $index++;
            $records[$key][$prefix . 'type'] = $records[$key][$prefix . 'type'] == 'BUY_CASA' ? 'Customer Buy' : $records[$key][$prefix . 'type'];
            $records[$key][$prefix . 'type'] = $records[$key][$prefix . 'type'] == 'SELL_CASA' ? 'Customer Sell' : $records[$key][$prefix . 'type'];
            $records[$key][$prefix . 'type'] = $records[$key][$prefix . 'type'] == 'CONVERSION' ? 'Gold Redemption' : $records[$key][$prefix . 'type'];
            $records[$key][$prefix . 'type'] = $records[$key][$prefix . 'type'] == 'CONVERSION_FEE' ? 'Redemption Fee' : $records[$key][$prefix . 'type'];
            $records[$key][$prefix . 'type'] = ucwords(strtolower(str_replace('_', ' ', $records[$key][$prefix . 'type'])));
            $records[$key][$prefix . 'credit'] = number_format($records[$key][$prefix . 'credit'], 3, '.', '');
            $records[$key][$prefix . 'debit'] = number_format($records[$key][$prefix . 'debit'], 3, '.', '');
            $records[$key][$prefix . 'prevxaubalance'] = number_format($records[$key - 1][$prefix . 'xaubalance'], 3, '.', '');
            $records[$key][$prefix . 'prevamountbalance'] = $records[$key - 1][$prefix . 'amountbalance'];
            $records[$key][$prefix . 'xaubalance'] = $records[$key][$prefix . 'xaubalance'] ?? number_format($records[$key][$prefix . 'prevxaubalance'] - $records[$key][$prefix . 'debit'] + $records[$key][$prefix . 'credit'], 3, '.', '');;
            $records[$key][$prefix . 'amountin'] = number_format($records[$key][$prefix . 'amountin'], 2, '.', '');
            $records[$key][$prefix . 'amountout'] = number_format($records[$key][$prefix . 'amountout'], 2, '.', '');
            $records[$key][$prefix . 'amountbalance'] = $records[$key][$prefix . 'amountbalance'] ?? number_format($records[$key][$prefix . 'prevamountbalance'] + $records[$key][$prefix . 'amountin'] - $records[$key][$prefix . 'amountout'], 2, '.', '');

            $txDate = $record[$prefix.'transactiondate'];
            $txDateObj = new \DateTime($txDate, $this->app->getServerTimezone());
            $txDateObj->setTimezone($this->app->getUserTimezone());
            $records[$key][$prefix . 'transactiondate'] = $txDate ? $txDateObj->format('Y-m-d H:i:s') : '';
        }

        return $records;
    }

    /**
     * Get the statement as html for the account holder
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string $titleDate
     * @param  array  $records
     * @return string
     */
    public function getStatementAsHtml(MyAccountHolder $accountHolder, $titleDate, $records, $includeFooter = true, $adminView = false)
    {
        $unchargedBalance = $this->app->myGtpAccountManager()->getAccountHolderUnchargedStorageFees($accountHolder);
        $template         = $this->getStatementTemplate($adminView);
        $html             = $this->applyStatementTemplate($accountHolder, $titleDate, $records, $unchargedBalance, $template, $includeFooter, $adminView);

        return $html;
    }

    public function sendEmail($accountHolder, $statement, $useJob = false)
    {
        if ($useJob) {
            $this->addEmailJob();
        } else {
            $this->emailToAccountHolder();
        }
    }

    public function addEmailJob()
    {

    }

    public function emailToAccountHolder()
    {
        
    }

    /**
     * This method apply statement template for the statement records and account holder information
     *
     * @param  MyAccountHolder $accountHolder
     * @param  string          $titleDate
     * @param  array           $records
     * @param  string          $template
     * @return string
     */
    private function applyStatementTemplate(MyAccountHolder $accountHolder, $titleDate, $records, $unchargedBalance, $template, $includeFooter = true, $adminView = false)
    {
        $tags = ['##RESOURCEPATH##','##ACCOUNTHOLDERCODE##', '##ACCOUNTHOLDERNAME##','##ACCOUNTHOLDERNRIC##', '##STATEMENTDATE##'];
        $fillers = [SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'resource', $accountHolder->accountholdercode, $accountHolder->fullname, $accountHolder->mykadno, $titleDate];
        $template = str_replace($tags, $fillers, $template);

        $replacements = '';
        preg_match('/((?<=##BLOCKSTART##)[\s\S]*?(?=##BLOCKEND##))/', $template, $matches);

        $tags = [
            '##REFNO##',
            '##DATE##',
            '##TYPE##',
            '##GOLDPRICE##',
            '##XAUIN##',
            '##XAUOUT##',
            '##XAUBALANCE##',
            '##AMOUNTIN##',
            '##AMOUNTOUT##',
            '##AMOUNTBALANCE##',
            '##REMARKS##'
        ];

        $totalAmountIn  = 0;
        $totalAmountOut = 0;
        $totalXauIn     = 0;
        $totalXauOut    = 0;

        foreach ($records as $record) {
            $type = ucwords(strtolower(str_replace('_', ' ', $record['led_type'])));
            // If not admin view (is user view) and not monthly storage fee then remarks is empty
            $remarks = !$adminView && ucwords(strtolower(str_replace('_', ' ', $record['led_type']))) != ucwords(strtolower(str_replace('_', ' ', MyLedger::TYPE_STORAGE_FEE))) ? '' : $record['led_remarks'];
            $goldPrice = number_format($record['led_ordgoldprice'],2,'.','');
            $amountIn  = !$adminView ? 0 : $record['led_amountin'];
            $amountOut = !$adminView ? 0 : $record['led_amountout'];
            $fillers = [$record['led_refno'], $record['led_transactiondate'], $type, $goldPrice, $record['led_debit'], $record['led_credit'], $record['led_xaubalance'], $amountIn, $amountOut, $record['led_amountbalance'], $remarks];
            $replacements .= str_replace($tags, $fillers, $matches[0]);

            $totalAmountIn  = $totalAmountIn + $amountIn;
            $totalAmountOut = $totalAmountOut + $amountOut;
            $totalXauIn     = $totalXauIn + $record['led_debit'];
            $totalXauOut    = $totalXauOut + $record['led_credit'];
        }
        $tags = ['##TOTALXAUIN##', '##TOTALXAUOUT##', '##TOTALXAUBALANCE##', '##TOTALAMOUNTIN##', '##TOTALAMOUNTOUT##', '##TOTALAMOUNTBALANCE##'];
        $fillers = [number_format($totalXauIn, 3, '.', ''), number_format($totalXauOut, 3, '.', ''), number_format($totalXauOut - $totalXauIn, 3, '.', ''), number_format($totalAmountIn, 2, '.', ''), number_format($totalAmountOut, 2, '.', ''), number_format($totalAmountIn - $totalAmountOut, 2, '.', '')];
        $template = str_replace($tags, $fillers, $template);

        $footer = '';
        if ($includeFooter) {
            preg_match('/((?<=##FOOTERSTART##)[\s\S]*?(?=##FOOTEREND##))/', $template, $footerMatches);
            $tags = ['##PREVIOUSXAUBALANCE##', '##AVAILABLEXAUBALANCE##'];
            $fillers = [number_format($records[0]['led_xaubalance'], 3, '.', ''), number_format($totalXauOut - $totalXauIn - $unchargedBalance, 3, '.', '')];
            $footer = str_replace($tags, $fillers, $footerMatches[0]);
        } 

        $template = preg_replace('/##FOOTERSTART##[\s\S]*##FOOTEREND##/', $footer, $template);

        $emptyReplacements = '';
        if (empty($records)) {
            $this->logDebug(__METHOD__ . "(): Empty statement records for account holder ({$accountHolder->id}) for date ({$titleDate})");

            preg_match('/((?<=##EMPTYSTART##)[\s\S]*?(?=##EMPTYEND##))/', $template, $emptyMatches);
            $tags = ['##EMPTYPLACEHOLDER##'];
            $fillers = [gettext('No record at the moment')];
            $emptyReplacements = str_replace($tags, $fillers, $emptyMatches[0]);
        }

        $template = preg_replace('/##EMPTYSTART##[\s\S]*##EMPTYEND##/', $emptyReplacements, $template);

        return preg_replace('/##BLOCKSTART##[\s\S]*##BLOCKEND##/', $replacements, $template);
    }


    /**
     * The HTML template for transaction statement of account holder.
     * Customer perspective: XAU IN (Company XAU OUT) - BUY, 
     *                       XAU OUT (Company XAU IN) - Sell
     *
     * @return string
     */
    protected function getStatementTemplate($adminView = false)
    {
        $projectBase = $this->app->getConfig()->{'projectBase'};

        $dir = SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR . 'html';
        if ($adminView) {
            $location = $dir . DIRECTORY_SEPARATOR . 'statement.html';
        } else {
            $location = $dir . DIRECTORY_SEPARATOR . strtolower(str_replace(' ', '_', $projectBase)) . '_statement.html';
        }
        
        if (! file_exists($location)) {
            $location = $dir . DIRECTORY_SEPARATOR . 'statement.html';
        }

        return file_get_contents($location);
    }
}
