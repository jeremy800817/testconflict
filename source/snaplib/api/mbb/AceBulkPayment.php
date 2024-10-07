<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mbb;

/**
 * This class represents a job to generate text file for customer sell transactions
 *
 * @author DK <dianah@silverstream.my>
 * @version 1.0m
 * @since 20220215
 * @package  snap.job
 */
class AceBulkPayment 
{
    /**
     * contains arrays describing start, length and name
     * for each value encoded in a line of the file to be parsed.
     *
     * @var array
     */
    protected $choppingMap = array();
    protected $wholeLine;

    /**
     * file to be parsed
     *
     * @var string
     */
    protected $file;
    protected $app = null;
    private $path;
    private $runSftp = true;
    private $callbackComponents = [];
    private $validChecker = [];
    private $formatMap = [
        'format' => [
            'header' => [
                array('field_name' => 'header_rectype', 'value' => '00|'),
                array('field_name' => 'header_corporateid', 'value' => ''),
                array('field_name' => 'header_clientbatchid', 'value' => ''),

                array('field_name' => 'header_fillin', 'value' => '', 'length' => 25) 
                /*important info: at least 28 pipes. Therefore, header_fillin length is 28 - 3(number of fields exclude header_fillin). In future, when add fields, please minus 28 with latest number of fields.*/
            ],
            'footer' => [
                array('field_name' => 'footer_rectype', 'value' => '99|'),
                array('field_name' => 'footer_totalcount', 'value' => ''),
                array('field_name' => 'footer_totaldebitingamt', 'value' => ''),
                array('field_name' => 'footer_hashingvalue', 'value' => ''),

                array('field_name' => 'header_fillin', 'value' => '', 'length' => 25) 
            ]
        ]
    ];
    
    public function __construct($app,$debugOn = false)
    {
        $this->runSftp = ! $debugOn;
        $this->app = $app;

        $this->path = $this->app->getConfig()->{'gtp.bulkpayment.temp'};
    }

    /**
     * Expects an array with n arrays containing :
     * 'field_name', 'start', 'length'
     * 
     * => array(
     *     array('field_name' => 'id,' 'start' => 0, 'length' => 2),
     *     ...
     * )
     *
     * @param array $map
     */
    
    public function setChoppingMap(array $map)
    {
        $this->choppingMap = $map;
    }

    public function setWholeLine($line)
    {
        $this->wholeLine .= $line;
    }

    /**
     * Setter for the file to be parsed
     * @param string $pathToFile /path/to/file.dat
     */
    public function setFilePath($pathToFile)
    {
        $this->file = (string) $pathToFile;
    }  

    public function setRecordsValue($fileformat,$aItem,$additionalDetails){
        if($fileformat == 'record') {
            /*format amount to 2 decimal. Dont rounding*/
            //$amountDec = bcdiv($aItem->amount, 1, 2);
            $amountDec = round($aItem->amount, 2); //ace requested to rounding on 20220825
            $formatDec = number_format($amountDec, 2, '.', '');
            /**/

            /*check mykadno. if have letter,not ctizen*/
            $identificationNo = $aItem->accmykadno;
            $checknum = is_numeric($identificationNo);
            if($checknum) $citizen = 'Y';
            else $citizen = 'N';
            /**/

            /*payment method filter*/
            #IT - INTRABANK
            #IG - GIRO
            #IM - RENTAS
            $sbankcode = $aItem->bankswiftcode;
            if($sbankcode == $additionalDetails['defbank']) $paymethod = 'IT';
            else $paymethod = 'IG';
            /**/

            /*records details*/
            $record_type = array('value' => '01|', 'length' => strlen('01|'));
            $record_paymethod = array('value' => $paymethod.'|', 'length' => strlen($paymethod.'|'));
            $record_provprod = array('value' => $additionalDetails['provprod'].'|', 'length' => strlen($additionalDetails['provprod'].'|'));
            $record_currentdate = array('value' => $additionalDetails['valuedate'].'|', 'length' => strlen($additionalDetails['valuedate'].'|'));
            //$record_custcode = array('value' => $aItem->accountholdercode.'|', 'length' => strlen($aItem->accountholdercode.'|'));
            $record_custcode = array('value' => $aItem->ordorderno.'|', 'length' => strlen($aItem->ordorderno.'|'));
            $record_debitref = array('value' => $aItem->transactionrefno.'|', 'length' => strlen($aItem->transactionrefno.'|'));
            $record_transcurrency = array('value' => $additionalDetails['currfix'].'|', 'length' => strlen($additionalDetails['currfix'].'|'));
            $record_transamount = array('value' => $formatDec.'|', 'length' => strlen($formatDec.'|'));
            $record_debacccurr = array('value' => 'Y|', 'length' => strlen('Y|'));
            $record_debitingcurr = array('value' => $additionalDetails['currfix'].'|', 'length' => strlen($additionalDetails['currfix'].'|'));
            $record_craccno = array('value' => $additionalDetails['aceaccno'].'|', 'length' => strlen($additionalDetails['aceaccno'].'|'));
            $record_dbaccno = array('value' => $aItem->accountnumber.'|', 'length' => strlen($aItem->accountnumber.'|'));
            $record_favbeneficiary = array('value' => '0|', 'length' => strlen('0|'));
            $record_residentindicator = array('value' => $citizen.'|', 'length' => strlen($citizen.'|'));
            $record_beneficiaryfullname = array('value' => $aItem->accountname.'|', 'length' => strlen($aItem->accountname.'|'));
            $record_newidno = array('value' => $identificationNo.'|', 'length' => strlen($identificationNo.'|'));
            $record_oldidno = array('value' => '0|', 'length' => strlen('0|'));
            $record_bizregno = array('value' => '0|', 'length' => strlen('0|'));
            $record_poarmpassno = array('value' => '0|', 'length' => strlen('0|'));
            $record_bankcode = array('value' => $sbankcode.'|', 'length' => strlen($sbankcode.'|'));
            $record_chargesborneby = array('value' => '01|', 'length' => strlen('01|'));

            /*paymentdetails*/
            $record_payrecordtype = array('value' => '02|', 'length' => strlen('02|'));
            $record_payadvtype = array('value' => 'PA|', 'length' => strlen('PA|'));
            //$record_paycustrefno = array('value' => $aItem->accountholdercode.'|', 'length' => strlen($aItem->accountholdercode.'|'));
            $record_paycustrefno = array('value' => $aItem->ordorderno.'|', 'length' => strlen($aItem->ordorderno.'|'));
            $record_paycustemail = array('value' => $aItem->accemail.'|', 'length' => strlen($aItem->accemail.'|'));
            $record_payadvdetail = array('value' => $aItem->transactionrefno.'|', 'length' => strlen($aItem->transactionrefno.'|'));
            $record_payadvamount = array('value' => $formatDec.'|', 'length' => strlen($formatDec.'|'));

            $columnName = [
                array('field_name' => 'record_type', 'value' => $record_type['value'], 'length' => $record_type['length']),
                array('field_name' => 'record_paymethod', 'value' => $record_paymethod['value'], 'length' => $record_paymethod['length']),
                array('field_name' => 'record_provprod', 'value' => $record_provprod['value'], 'length' => $record_provprod['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 1),
                array('field_name' => 'record_currentdate', 'value' => $record_currentdate['value'], 'length' => $record_currentdate['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 2),
                array('field_name' => 'record_custcode', 'value' => $record_custcode['value'], 'length' => $record_custcode['length']),
                array('field_name' => 'record_debitref', 'value' => $record_debitref['value'], 'length' => $record_debitref['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 1),
                array('field_name' => 'record_transcurrency', 'value' => $record_transcurrency['value'], 'length' => $record_transcurrency['length']),
                array('field_name' => 'record_transamount', 'value' => $record_transamount['value'], 'length' => $record_transamount['length']),
                array('field_name' => 'record_debacccurr', 'value' => $record_debacccurr['value'], 'length' => $record_debacccurr['length']),
                array('field_name' => 'record_debitingcurr', 'value' => $record_debitingcurr['value'], 'length' => $record_debitingcurr['length']),
                array('field_name' => 'record_craccno', 'value' => $record_craccno['value'], 'length' => $record_craccno['length']),
                array('field_name' => 'record_dbaccno', 'value' => $record_dbaccno['value'], 'length' => $record_dbaccno['length']),
                //array('field_name' => 'record_favbeneficiary', 'value' => $record_favbeneficiary['value'], 'length' => $record_favbeneficiary['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 2),
                array('field_name' => 'record_residentindicator', 'value' => $record_residentindicator['value'], 'length' => $record_residentindicator['length']),
                array('field_name' => 'record_beneficiaryfullname', 'value' => $record_beneficiaryfullname['value'], 'length' => $record_beneficiaryfullname['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 4),
                array('field_name' => 'record_newidno', 'value' => $record_newidno['value'], 'length' => $record_newidno['length']),
                array('field_name' => 'record_oldidno', 'value' => $record_oldidno['value'], 'length' => $record_oldidno['length']),
                array('field_name' => 'record_bizregno', 'value' => $record_bizregno['value'], 'length' => $record_bizregno['length']),
                array('field_name' => 'record_poarmpassno', 'value' => $record_poarmpassno['value'], 'length' => $record_poarmpassno['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 8),
                array('field_name' => 'record_bankcode', 'value' => $record_bankcode['value'], 'length' => $record_bankcode['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 72),
                array('field_name' => 'record_chargesborneby', 'value' => $record_chargesborneby['value'], 'length' => $record_chargesborneby['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 226),
                array('field_name' => 'record_newline'),

                array('field_name' => 'record_payrecordtype', 'value' => $record_payrecordtype['value'], 'length' => $record_payrecordtype['length']),
                array('field_name' => 'record_payadvtype', 'value' => $record_payadvtype['value'], 'length' => $record_payadvtype['length']),
                array('field_name' => 'record_paycustrefno', 'value' => $record_paycustrefno['value'], 'length' => $record_paycustrefno['length']),
                array('field_name' => 'record_paycustemail', 'value' => $record_paycustemail['value'], 'length' => $record_paycustemail['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 2),
                array('field_name' => 'record_payadvdetail', 'value' => $record_payadvdetail['value'], 'length' => $record_payadvdetail['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 6),
                array('field_name' => 'record_payadvamount', 'value' => $record_payadvamount['value'], 'length' => $record_payadvamount['length']),
                array('field_name' => 'record_gap', 'value' => '', 'length' => 25),
                array('field_name' => 'record_newline')
            ];

            foreach($columnName as $aLine){
                $calculateLineTotal += $aLine['length'];
            }
            $columnName[] = array('lineT' => $calculateLineTotal);
        }
        $this->choppingMap = array_merge($this->choppingMap,$columnName);
    }

    /**
     * Handles a single line
     *
     * @param string $buffer
     * @return array
     */
    private function createHeader($details){
        //$totalLine = count((array)$item);
        $newline = '';
        foreach($this->choppingMap['header'] as $aChoppingMap)
            {
                $value = isset($aChoppingMap['value']) ? $aChoppingMap['value'] : '';
                if($aChoppingMap['field_name'] == 'header_corporateid') $value = $details['aceaccid'].'|';
                if($aChoppingMap['field_name'] == 'header_clientbatchid') $value = $details['clientbatchid'].'|';
                $filler = '|';
                $fillerdirection = STR_PAD_RIGHT;
                $fill = str_pad($value,$aChoppingMap['length'], $filler, $fillerdirection);
                $newline .= $fill;
            }
        return $newline;  
    }

    /**
     * Handles a single line
     *
     * @param string $buffer
     * @return array
     */
    private function createFooter($item,$totalItem,$totalAmount){
        //$totalLine = count((array)$item);

        //$totalAmt = bcdiv($totalAmount, 1, 2);
        $totalAmt = round($totalAmount, 2); //ace requested to rounding on 20220825
        foreach($item as $aItem){
            /*format amount to 2 decimal. Dont rounding*/
            //$amountDec = bcdiv($aItem->amount, 1, 2);
            $amountDec = round($aItem->amount, 2); //ace requested to rounding on 20220825
            $formatDec = number_format($amountDec, 2, '.', '');
            $debacc = $aItem->accountnumber;
            /**/
            $listAcc[] = [
                'accno' => $debacc,
                'amount' => $formatDec
            ];
        }
        $sendConvert = $this->convertHashing($listAcc);

        $newline = '';
        foreach($this->choppingMap['footer'] as $aChoppingMap)
            {
                $value = isset($aChoppingMap['value']) ? $aChoppingMap['value'] : '';
                if($aChoppingMap['field_name'] == 'footer_totalcount') $value = $totalItem.'|';
                if($aChoppingMap['field_name'] == 'footer_totaldebitingamt') $value = $totalAmt.'|';
                if($aChoppingMap['field_name'] == 'footer_hashingvalue') $value = $sendConvert.'|';
                $filler = '|';
                $fillerdirection = STR_PAD_RIGHT;
                $fill = str_pad($value,$aChoppingMap['length'], $filler, $fillerdirection);
                $newline .= $fill;
            }
        return $newline;  
    }

    public function checkFile($item,$date,$totalItem,$totalPage,$page,$additionalDetails) {
        $me = $this;
        $newline = '';
        $lastPosition = 0;
        $mapEntryCount = 0;
        $me->setChoppingMap( $me->formatMap['format']);

        //create header text
        $getHeader = $me->createHeader($additionalDetails);

        if($page <= $totalPage){
            foreach($item as $aItem){
                /*format amount to 2 decimal. Dont rounding*/
                //$amountDec = bcdiv($aItem->amount, 1, 2);
                $amountDec = round($aItem->amount, 2); //ace requested to rounding on 20220825
                $formatDec = number_format($amountDec, 2, '.', '');
                /**/
                $me->setRecordsValue('record',$aItem,$additionalDetails);
                $totalAmount += $formatDec; //for footer
            }

            foreach($this->choppingMap as $aChoppingMap)
            {
                if(strpos($aChoppingMap['field_name'],'record') !== false){
                    $value = $aChoppingMap['value'];
                    $filler = '|';
                    $fillerdirection = STR_PAD_RIGHT;
                    $fill = str_pad($value,$aChoppingMap['length'], $filler, $fillerdirection);
                    $newline.= $fill;
                    if($aChoppingMap['field_name'] == 'record_newline') $newline.= PHP_EOL;
                }
            } 
        }

        $me->setWholeline($newline);

        //create footer text
        $getFooter = $me->createFooter($item,$totalItem,$totalAmount);

        $merge .= $getHeader.PHP_EOL;
        $merge .= $me->wholeLine;
        $merge .= $getFooter.PHP_EOL;

        if($page == $totalPage){
            $fieldName = $additionalDetails['filename'];
            $path = $this->path;
            $me->setFilePath($path.$fieldName);
            $ftpname = $this->file.".TXT";

            $fp = fopen($ftpname,"w");
            if ($fp) {
                $write = fwrite($fp,$merge);
                if($write) {
                    fclose($fp);
                    /*send to apimanager to save log*/
                    $apiManager = $this->app->apiManager();
                    $apiManager->aceBulkPayment($fieldName);
                    /*to unset variable to empty again*/
                    unset($me->wholeLine);
                    return true;
                } else {
                    throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to proceed with writing file.']);
                    return false;
                }
            } else {
                throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to proceed with open/create file.']);
                return false;
            }
        }
    }

    public function convertHashing($value){
        $divideByDef = 2000;
        $i = 1;
        foreach($value as $aList){
            /*step01*/
            $spCaseNumber = preg_replace('/[^0-9]/', '', $aList['amount']); //MBB received only hex. need to remove '.'
            $hashAmt = ($spCaseNumber % $divideByDef) + $i;

            /*step02*/
            $updateStr = substr($aList['accno'], -6);

            if(is_numeric($updateStr)){
                $sumofdigit = array_sum(str_split($updateStr));
            } else {
                $splitAcc = str_split($updateStr);
                $sumofdigit = 0;
                foreach($splitAcc as $aSplit){
                    if(is_numeric($aSplit)) $convertHash = $aSplit;
                    else $convertHash = ord($aSplit);

                    $sumofdigit += $convertHash;
                }
            }

            $hashAcc = ($sumofdigit*2) + $i;
            $totalHash = $hashAmt + $hashAcc;
            $grandTotalHash += $totalHash;
            $i++;
        }

        return $grandTotalHash;

    }
}

?>