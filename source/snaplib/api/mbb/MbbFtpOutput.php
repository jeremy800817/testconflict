<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mbb;

class MbbFtpOutput 
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
        'phyrep' => [ 
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10, 'value' => 'PHYREP'),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 23, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 21, 'value' => ''),
            ],
            'totalline' => 50,
        ],
        'phycourier' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10, 'value' => 'PHYCOURIER'),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 23, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 121, 'value' => ''),
            ],
            'totalline' => 150,
        ],
        'dailytrn' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10, 'value' => 'DAILYTRN'),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 23, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 51, 'value' => ''),
            ],
            'totalline' => 80,
        ],
        'spcdelv' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10, 'value' => 'SPCDELV'),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 23, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 111, 'value' => '')
            ],
            'totalline' => 140,
        ],
        'dailygpprice1' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 13, 'value' => 'DAILYGPPRICE1'),
                array('field_name' => 'header_filedate', 'start' => 15, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 23, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 26, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 32, 'length' => 18, 'value' => ''),
            ],
            'totalline' => 50,
        ],
        'dailygpprice2' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 13, 'value' => 'DAILYGPPRICE2'),
                array('field_name' => 'header_filedate', 'start' => 15, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 23, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 26, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 32, 'length' => 18, 'value' => ''),
            ],
            'totalline' => 50,
        ],
        'dailygpprice3' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 13, 'value' => 'DAILYGPPRICE3'),
                array('field_name' => 'header_filedate', 'start' => 15, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 23, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 26, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 32, 'length' => 18, 'value' => ''),
            ],
            'totalline' => 50,
        ],
        'dailygpprice4' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => '', //add own locahost path
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2, 'value' => 'BH'),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 13, 'value' => 'DAILYGPPRICE4'),
                array('field_name' => 'header_filedate', 'start' => 15, 'length' => 8, 'value' => ''),
                array('field_name' => 'header_filesequence', 'start' => 23, 'length' => 3, 'value' => '001'),
                array('field_name' => 'header_totalpcs', 'start' => 26, 'length' => 6, 'value' => ''),
                array('field_name' => 'header_filler', 'start' => 32, 'length' => 18, 'value' => ''),
            ],
            'totalline' => 50,
        ],
    ];

    
    public function __construct($app,$debugOn = false)
    {
        $this->runSftp = ! $debugOn;
        $this->app = $app;

        $this->path = $this->app->getConfig()->{'gtp.ftp.tombb'};
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

    public function setCustomizeValue($fileformat,$aItem){
        if($fileformat == 'phyrep') {
            $serialno = $aItem->serialno;
            //getbranch code
            $branch = $this->app->partnerStore()->getRelatedStore('branches');
            $getBranch = $branch->getByField('id', $aItem->branchid);
            $branchcode = $getBranch->code;

            //getproductid to convert denomination
            $product = $this->app->productStore()->getByField('id', $aItem->productid);
            $denomination = str_replace('GS-999-9-', '', $product->code);
            if($denomination == '1g') $denom = 'A';
            if($denomination == '5g') $denom = 'B';
            if($denomination == '10g') $denom = 'C';
            if($denomination == '50g') $denom = 'D';
            if($denomination == '100g') $denom = 'E';
            if($denomination == '1000g') $denom = 'F';

            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_branchid', 'start' => 2, 'length' => 5, 'value' => $branchcode, 'type' => 'numeric'),
                array('field_name' => 'detail_denomination', 'start' => 7, 'length' => 1, 'value' => $denom, 'type' => 'string'),
                array('field_name' => 'detail_serialno', 'start' => 8, 'length' => 14, 'value' => $this->convertSerialNo($serialno), 'type' => 'string'),
                array('field_name' => 'detail_filler', 'start' => 22, 'length' => 28, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'phycourier'){
            $dateGrab = ($aItem['deliverydate'] != null) ? $aItem['deliverydate']->format('dmY') : '00000000';
            $dateDelivery   = $dateGrab;
            if(empty($aItem['vendorrefno'])) $refNo = $aItem['transactionRefNo'];
            else $refNo = $aItem['vendorrefno'];
            $remark         = $aItem['remarks']; //Logistic table does not have remarks
            $attemps        = $aItem['attemps'];
            if($attemps >= 3) $return = 'Y';
            else $return = 'N';
            
            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_courierrefno', 'start' => 2, 'length' => 16, 'value' => $refNo, 'type' => 'string'),
                array('field_name' => 'detail_trackingno', 'start' =>18, 'length' => 20, 'value' => $aItem['awbno'], 'type' => 'string'),
                array('field_name' => 'detail_deliverydate', 'start' => 38, 'length' => 8, 'value' => $dateDelivery, 'type' => 'string'),
                array('field_name' => 'detail_courieragent', 'start' => 46, 'length' => 30, 'value' => $aItem['vendorname'], 'type' => 'string'),
                array('field_name' => 'detail_returnundelivered', 'start' => 76, 'length' => 1, 'value' => $return, 'type' => 'string'),
                array('field_name' => 'detail_remark', 'start' => 77, 'length' => 40, 'value' => $remark, 'type' => 'string'),
                array('field_name' => 'detail_filler', 'start' => 117, 'length' => 33, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'spcdelv') {
            $dateGrab = ($aItem['deliverydate'] != null) ? $aItem['deliverydate']->format('dmY') : '00000000';
            $dateDelivery   = $dateGrab;
            if(empty($aItem['vendorrefno'])) $refNo = $aItem['transactionRefNo'];
            else $refNo = $aItem['vendorrefno'];
            $remark         = $aItem['remarks']; //Logistic table does not have remarks
            $attemps        = $aItem['attemps'];
            if($attemps >= 3) $return = 'Y';
            else $return = 'N';
            
            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_branchid', 'start' => 2, 'length' => 5, 'value' => $aItem['branchid'], 'type' => 'numeric'),
                array('field_name' => 'detail_courierrefno', 'start' => 7, 'length' => 16, 'value' => $refNo, 'type' => 'string'),
                array('field_name' => 'detail_trackingno', 'start' =>23, 'length' => 20, 'value' => $aItem['awbno'], 'type' => 'string'),
                array('field_name' => 'detail_deliverydate', 'start' => 43, 'length' => 8, 'value' => $dateDelivery, 'type' => 'string'),
                array('field_name' => 'detail_courieragent', 'start' => 51, 'length' => 30, 'value' => $aItem['vendorname'], 'type' => 'string'),
                array('field_name' => 'detail_returnundelivered', 'start' => 81, 'length' => 1, 'value' => $return, 'type' => 'string'),
                array('field_name' => 'detail_remark', 'start' => 82, 'length' => 40, 'value' => $remark, 'type' => 'string'),
                array('field_name' => 'detail_filler', 'start' => 122, 'length' => 18, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'dailygpprice1') {
            /*$timestamp = $aItem['createdon'];
            $timestamppricesourceon = $aItem['pricesourceon'];
            $pricebuy = $aItem['pricebuy'];
            $pricesell = $aItem['pricesell'];*/
            $timestamp = $aItem->createdon->format('His');
            if($aItem->requestedtype == 'CompanyBuy') {
                $pricebuy = number_format($aItem->price,2,'.','');
                $pricesell = 0;
            } else {
                $pricebuy = 0;
                $pricesell = number_format($aItem->price,2,'.','');
            }

            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_pricetimestamp', 'start' => 2, 'length' => 6, 'value' => $timestamp, 'type' => 'string'),
                array('field_name' => 'detail_pricebuy', 'start' =>8, 'length' => 8, 'value' => $pricebuy, 'type' => 'numeric'),
                array('field_name' => 'detail_pricesell', 'start' => 16, 'length' => 8, 'value' => $pricesell, 'type' => 'numeric'),
                array('field_name' => 'detail_filler', 'start' => 24, 'length' => 26, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'dailygpprice2') {
            $timestamp = $aItem->createdon->format('His');
            if($aItem->requestedtype == 'CompanyBuy') {
                $pricebuy = number_format($aItem->price,2,'.','');
                $pricesell = 0;
            } else {
                $pricebuy = 0;
                $pricesell = number_format($aItem->price,2,'.','');
            }

            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_pricetimestamp', 'start' => 2, 'length' => 6, 'value' => $timestamp, 'type' => 'string'),
                array('field_name' => 'detail_pricebuy', 'start' =>8, 'length' => 8, 'value' => $pricebuy, 'type' => 'numeric'),
                array('field_name' => 'detail_pricesell', 'start' => 16, 'length' => 8, 'value' => $pricesell, 'type' => 'numeric'),
                array('field_name' => 'detail_filler', 'start' => 24, 'length' => 26, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'dailygpprice3') {
            $timestamp = $aItem->createdon->format('His');
            if($aItem->type == 'CompanyBuy') {
                $pricebuy = number_format($aItem->cancelprice,2,'.','');
                $pricesell = 0;
            } else {
                $pricebuy = 0;
                $pricesell = number_format($aItem->cancelprice,2,'.','');
            }

            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_pricetimestamp', 'start' => 2, 'length' => 6, 'value' => $timestamp, 'type' => 'string'),
                array('field_name' => 'detail_pricebuy', 'start' =>8, 'length' => 8, 'value' => $pricebuy, 'type' => 'numeric'),
                array('field_name' => 'detail_pricesell', 'start' => 16, 'length' => 8, 'value' => $pricesell, 'type' => 'numeric'),
                array('field_name' => 'detail_filler', 'start' => 24, 'length' => 26, 'value' => '', 'type' => 'string'),
            ];
        } else if($fileformat == 'dailygpprice4') {
            $timestamp = $aItem->createdon->format('His');
            if($aItem->ordertype == 'CompanyBuy') {
                $pricebuy = number_format($aItem->pricetarget,2,'.','');
                $pricesell = 0;
                if($aItem->companybuyppg != NULL) $pricelive = number_format($aItem->companybuyppg,2,'.','');
                else $pricelive = 0;
            } else {
                $pricebuy = 0;
                $pricesell = number_format($aItem->pricetarget,2,'.','');
                if($aItem->companysellppg != NULL ) $pricelive = number_format($aItem->companysellppg,2,'.','');
                else $pricelive = 0;
            }

            $columnName = [
                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2, 'value' => 'DT', 'type' => 'string'),
                array('field_name' => 'detail_pricetimestamp', 'start' => 2, 'length' => 6, 'value' => $timestamp, 'type' => 'string'),
                array('field_name' => 'detail_pricebuy', 'start' =>8, 'length' => 8, 'value' => $pricebuy, 'type' => 'numeric'),
                array('field_name' => 'detail_pricesell', 'start' => 16, 'length' => 8, 'value' => $pricesell, 'type' => 'numeric'),
                array('field_name' => 'detail_pricelive', 'start' => 24, 'length' => 8, 'value' => $pricelive, 'type' => 'numeric'),
                array('field_name' => 'detail_filler', 'start' => 32, 'length' => 18, 'value' => '', 'type' => 'string'),
            ];
        }
        $this->choppingMap = array_merge($this->choppingMap,$columnName);
    }

    /**
     * Handles a single line
     *
     * @param string $buffer
     * @return array
     */
    private function createHeader($item,$headerDate,$totalItem){
        //$totalLine = count((array)$item);
        $newline = '';
        $lastPosition = 0;
        $mapEntryCount = 0;

        foreach($this->choppingMap as $aChoppingMap)
        {
            $mapEntryCount++;
            $start = isset($aChoppingMap['start']) ? $aChoppingMap['start'] : $lastPosition;
            $lastPosition = $i === $mapEntryCount-1 ? 0 : $lastPosition = $start + $aChoppingMap['length'];
            $value = isset($aChoppingMap['value']) ? $aChoppingMap['value'] : '';
            if($aChoppingMap['field_name'] == 'header_filedate') $value = $headerDate;
            if($aChoppingMap['field_name'] == 'header_totalpcs') $value = $totalItem;

            $filler = is_numeric($value) ? 0 : ' ';
            $fillerdirection = is_numeric($value) ? STR_PAD_LEFT : STR_PAD_RIGHT;

            $fill = str_pad($value,$aChoppingMap['length'], $filler, $fillerdirection);

            $newline .= $fill;
        }
        return  $newline;  
    }

    public function checkFile($fieldName,$mbbFormatCode,$item,$date,$totalItem,$totalPage,$page) {
        $me = $this;
        $newline = '';
        $lastPosition = 0;
        $mapEntryCount = 0;
        $me->setChoppingMap( $me->formatMap[$mbbFormatCode]['format']);
        $totalline = $me->formatMap[$mbbFormatCode]['totalline'];
        $dateToHeader       = date('dmY',$date);

        //create header text
        $getHeader = $me->createHeader($item,$dateToHeader,$totalItem);
        if($page <= $totalPage){
            foreach($item as $aItem){
                    $me->setCustomizeValue($mbbFormatCode,$aItem);
                    //$me->setAllChoppingMap( $customizeValue);
            }

            foreach($this->choppingMap as $aChoppingMap)
            {
                if(strpos($aChoppingMap['field_name'],'detail') !== false){
                    $mapEntryCount++;
                    $start = isset($aChoppingMap['start']) ? $aChoppingMap['start'] : $lastPosition;
                    $lastPosition = $i === $mapEntryCount-1 ? 0 : $lastPosition = $start + $aChoppingMap['length'];

                    $value = $aChoppingMap['value'];
                    $datatype = $aChoppingMap['type'];
                    $filler = ($datatype == 'numeric') ? 0 : ' ';
                    $fillerdirection = ($datatype == 'numeric') ? STR_PAD_LEFT : STR_PAD_RIGHT;

                    $fill = str_pad($value,$aChoppingMap['length'], $filler, $fillerdirection);
                    $newline.= $fill;
                    if($lastPosition == $totalline) $newline.= PHP_EOL;
                }
            } 
        }

        $me->setWholeline($newline);
        
        $merge .= $getHeader.PHP_EOL;
        $merge .= $me->wholeLine;

        if($page == $totalPage){
            //$path = $me->formatMap[$mbbFormatCode]['path'];
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
                    $apiManager->ftpAceToMbb($fieldName);
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

    public function convertSerialNo($serialno){
        $length = strlen($serialno);
        $alphaLength = 3;
        $alphaMaxLength = 6;
        $noLength = 8;

        //remove any character
        $res = preg_replace("/[^a-zA-Z0-9]/", "", $serialno);

        //split letter and numbers
        preg_match_all('/(\d)|(\w)/', $res, $matches);
        $numbers = implode($matches[1]);
        $letters = implode($matches[2]);

        $update .= (strlen($letters) < $alphaMaxLength) ? str_pad($letters,$alphaMaxLength, ' ', STR_PAD_RIGHT) : $letters;

        //check length of string for number and fill in spaces
        $update .= (strlen($numbers) < $noLength) ? str_pad($numbers,$noLength, ' ', STR_PAD_LEFT) : $numbers;

        return $update;
    }
}

?>