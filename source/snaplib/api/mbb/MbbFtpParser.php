<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mbb;

class MbbFtpParser
{
    /**
     * contains arrays describing start, length and name
     * for each value encoded in a line of the file to be parsed.
     *
     * @var array
     */
    protected $choppingMap = array();

    /**
     * file to be parsed
     *
     * @var string
     */
    protected $file;

    /**
     *
     * @var Closure
     */
    protected $callback = null;

    /**
     *
     * @var Closure
     */
    protected $preflightCheck = null;

    /**
     *
     * @var array
     */
    protected $content = array();
    protected $app = null;
    private $path;
    private $runSftp = true;
    private $callbackComponents = [];
    private $validChecker = [];
    private $formatMap = [
        'phyinv' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 31),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_branchid', 'start' => 2, 'length' => 5),
                array('field_name' => 'detail_denomination', 'start' => 7, 'length' => 1),
                array('field_name' => 'detail_serialno', 'start' => 8, 'length' => 14),
                array('field_name' => 'detail_status', 'start' => 22, 'length' => 2),
                array('field_name' => 'detail_loadingdate', 'start' => 24, 'length' => 8),
                array('field_name' => 'detail_filler', 'start' => 32, 'length' => 28),

                array('field_name' => 'summary_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'summary_branchid', 'start' => 2, 'length' => 5),
                array('field_name' => 'summary_denomination', 'start' => 7, 'length' => 1),
                array('field_name' => 'summary_quantity', 'start' => 8, 'length' => 2),
                array('field_name' => 'summary_date', 'start' => 10, 'length' => 8),
                array('field_name' => 'summary_filler', 'start' => 18, 'length' => 42),
            ],
            'transform'  => [
                'transformSerialNo' => 'detail_serialno',
            ]
        ],

        'fodlyrec' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 91),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_forefno', 'start' => 2, 'length' => 14),
                array('field_name' => 'detail_type', 'start' => 16, 'length' => 4),
                array('field_name' => 'detail_bookingprice', 'start' => 20, 'length' => 8),
                array('field_name' => 'detail_bookgram', 'start' => 28, 'length' => 16),
                array('field_name' => 'detail_bookingdate', 'start' => 44, 'length' => 8),
                array('field_name' => 'detail_expireddate', 'start' => 52, 'length' => 8),
                array('field_name' => 'detail_status', 'start' => 60, 'length' => 20),
                array('field_name' => 'detail_filler', 'start' => 80, 'length' => 40),
            ],
            'transform'  => [
            ]
        ],
        'dailydvg' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 51),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_serialno', 'start' => 2, 'length' => 14),
                array('field_name' => 'detail_weight', 'start' => 16, 'length' => 16),
                array('field_name' => 'detail_dgv', 'start' => 32, 'length' => 16),
                array('field_name' => 'detail_ownerflag', 'start' => 48, 'length' => 3),
                array('field_name' => 'detail_filler', 'start' => 51, 'length' => 29),
            ],
            'transform'  => [
                'transformSerialNo' => 'detail_serialno',
                /*'transformPartnerCode' => 'xxxxx'*/
            ]
        ],

        'phyrtn' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => 'C:\\laragon\\www\\gtp2\\source\\ftp\\',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 91),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_branchid', 'start' => 2, 'length' => 5),
                array('field_name' => 'detail_denomination', 'start' => 7, 'length' => 1),
                array('field_name' => 'detail_serialno', 'start' => 8, 'length' => 14),
                array('field_name' => 'detail_returndate', 'start' => 22, 'length' => 8),
                array('field_name' => 'detail_returnreason', 'start' => 30, 'length' => 20),
                array('field_name' => 'detail_buybackdate', 'start' => 50, 'length' => 8),
                array('field_name' => 'detail_filler', 'start' => 58, 'length' => 32),
            ],
            'transform'  => [
                'transformSerialNo' => 'detail_serialno',
                //'convertBranchId' => 'detail_branchid'
                //'transformPartnerCode' => 'xxxxx'
            ]
        ],

        'dailytrn' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => 'C:\\laragon\\www\\gtp2\\source\\ftp\\',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 76),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_transactiontime', 'start' => 2, 'length' => 6),
                array('field_name' => 'detail_transactioncode', 'start' => 8, 'length' => 20),
                array('field_name' => 'detail_transactiondesc', 'start' => 28, 'length' => 25),
                array('field_name' => 'detail_transactiontype', 'start' => 53, 'length' => 2),
                array('field_name' => 'detail_transactiongram', 'start' => 55, 'length' => 16),
                array('field_name' => 'detail_transactionsource', 'start' => 71, 'length' => 3),
                array('field_name' => 'detail_reversaltran', 'start' => 74, 'length' => 1),
                array('field_name' => 'detail_transactiongoldprice', 'start' => 75, 'length' => 15),
                array('field_name' => 'detail_transactionamount', 'start' => 90, 'length' => 15),
                //array('field_name' => 'detail_filler', 'start' => 81, 'length' => 9),
            ],
            'transform'  => [
                //'transformSerialNo' => 'detail_serialno',
                //'convertBranchId' => 'detail_branchid'
                //'transformPartnerCode' => 'xxxxx'
            ]
        ],

        'phycourec' => [
            //'path' => '/data/aceftp/data/out/PHYRTN',
            //'path' => $this->app->getConfig()->{'gtp.ftp.frommbb'},
            'format' => [
                array('field_name' => 'header_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'header_filename', 'start' => 2, 'length' => 10),
                array('field_name' => 'header_filedate', 'start' => 12, 'length' => 8),
                array('field_name' => 'header_filesequence', 'start' => 20, 'length' => 3),
                array('field_name' => 'header_totalline', 'start' => 23, 'length' => 6),
                array('field_name' => 'header_filler', 'start' => 29, 'length' => 121),

                array('field_name' => 'detail_code', 'start' => 0, 'length' => 2),
                array('field_name' => 'detail_courierorderno', 'start' => 2, 'length' => 16),
                array('field_name' => 'detail_orderdate', 'start' => 18, 'length' => 8),
                array('field_name' => 'detail_branchid', 'start' => 26, 'length' => 5),
                array('field_name' => 'detail_trackingno', 'start' => 31, 'length' => 20),
                array('field_name' => 'detail_courieragent', 'start' => 51, 'length' => 30),
                array('field_name' => 'detail_deliverydate', 'start' => 81, 'length' => 8),
                array('field_name' => 'detail_denomination', 'start' => 89, 'length' => 1),
                array('field_name' => 'detail_serialnofiller', 'start' => 90, 'length' => 14),
                array('field_name' => 'detail_filler', 'start' => 104, 'length' => 48),
            ],
            'transform'  => [
                /*'transformSerialNo' => 'detail_serialno',
                'transformPartnerCode' => 'xxxxx'*/
            ]
        ],



    ];

    public function __construct($app,$debugOn = false)
    {
        $this->runSftp = ! $debugOn;
        $this->app = $app;

        $this->path = $this->app->getConfig()->{'gtp.ftp.frommbb'};
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

    /**
     * Setter for the file to be parsed
     * @param string $pathToFile /path/to/file.dat
     */
    public function setFilePath($pathToFile)
    {
        $this->file = (string) $pathToFile;
    }

    /**
     * Setter for registering a closure that
     * evaluates if a fetched line needs to be parsed.
     * 
     * The closure needs to
     * <ul>
     * <li>accept the unparsed current line as a string
     * <li>return a boolean value indicating whether or not this line should be parsed
     * </ul>
     *
     * @param \Closure $preflightCheck
     */
    public function setPreflightCheck(\Closure $preflightCheck)
    {
        $this->preflightCheck = $preflightCheck;
    }

    /**
     * Setter method for registering a callback which handles
     * each line *after* parsing.
     * 
     * The closure needs to
     * <ul>
     * <li>accept the parsed current line as an associative array
     * <li>return an associative array in the current file's format
     * </ul>
     *
     * @param \Closure $callback
     */
    public function setCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Returns all lines of the parsed content.
     *
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Main method for parsing.
     *
     * @return void
     * @throws ParserException
     */
    public function parse($mbbFormatCode)
    {
        //Check for file parameter
        if (!isset($this->file)) {
            //throw new ParserException('No file was specified!');
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'No file was specified']);
        }

        //Check for chopping map
        if (!isset($this->choppingMap)) {
            //throw new ParserException('A Chopping Map MUST be specified!');
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'A Chopping Map MUST be specified.']);
        }

        //Save pre check as local variable (as PHP does not recognize closures as class members)
        $preflightCheck = $this->preflightCheck;

        //Parse file line by line
        $this->content = array();
        $filePointer = fopen($this->file, "r");

        //this is for header. First line
        $firstline = fgets($filePointer, 4096);
        $this->content[] = $this->parseLine($firstline,'header');

        //get from header
        $getTotalLine = $this->content[0]['header_totalline'];

        /*to get total of line exclude header*/
        $count = 0-1;
        $fileInput = fopen($this->file, "r");
        if ($fileInput) {
            while (!feof($fileInput)) {
                $getBuffer = fgets($fileInput, 4096);
                if (!empty($getBuffer)) {
                    $count++;
                }
            }
        } else {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to proceed with open/read file.']);
            return false;
        }
        /*end to get total of line*/

        //get rest of lines
        $i=0;
        while (!feof($filePointer)) {
            $buffer = fgets($filePointer, 4096);
            if (!empty($buffer)) {
                // If a pre check was registered and it returns not true - the current line
                // does not need to be parsed
                if ($preflightCheck instanceof \Closure && $preflightCheck($buffer) !== true) {
                    continue;
                }

                if($mbbFormatCode == 'phyinv'){ //phyinv have extra summary
                    if($i < $getTotalLine) $this->content[] = $this->parseLine($buffer,'detail');
                    else $this->content[] = $this->parseLine($buffer,'summary');
                } else {
                    $this->content[] = $this->parseLine($buffer,'detail');
                }
                $i++;
            }
        }
        fclose($filePointer);
    }

    /**
     * Handles a single line
     *
     * @param string $buffer
     * @return array
     */
    private function parseLine($buffer,$cat)
    {
        $currentLine = array();
        $lastPosition = 0;
        $mapEntryCount = 0;
        foreach($this->choppingMap as $aChoppingMap)
        {
            if(strpos($aChoppingMap['field_name'], $cat) !== false) {
                $mapEntryCount++;
                // if start option was set, use it. otherwise use last known position
                $start = isset($aChoppingMap['start']) ? $aChoppingMap['start'] : $lastPosition;
                // last entry of map, reset position
                $lastPosition = $i === $mapEntryCount-1 ? 0 : $lastPosition = $start + $aChoppingMap['length'];
                $name = $aChoppingMap['field_name'];
                $currentLine[$name] = substr($buffer,$start,$aChoppingMap['length']);
                $currentLine[$name] = trim($currentLine[$name]);
            }
        }
        if($cat == 'detail') $callback = $this->callback;
        /**
         * If a call back function was registered - apply it to the current line
         */
        if ($callback instanceof \Closure) {
            $currentLine = $callback($currentLine);
        }
        return $currentLine;
    }

    //public function parseFile($fileName, $mbbFormatCode) { //mbbFormatCode = 'phyinv'/etc/etc
    public function parseFile($mbbFormatCode) {
        try {
            $me = $this;
            $me->setChoppingMap( $me->formatMap[$mbbFormatCode]['format']);

            //$path = $me->formatMap[$mbbFormatCode]['path'];
            $path = $this->path;

            //get file without knowing extension
            $result = glob ($path.strtoupper($mbbFormatCode).".*");
            $me->setFilePath($result[0]); //set file path to $this->file    

            //get filename with extension
            $filename = basename($result[0]).PHP_EOL;

            //start process
            $me->setCallback(
                function(array $currentLine) use($me,$mbbFormatCode) 
                {
                    foreach($me->formatMap[$mbbFormatCode]['transform'] as $func => $filename) {
                        $currentLine = call_user_func_array([$me,$func], [$currentLine, $filename]);
                    }
                    return $currentLine;
                }
            );
            $me->parse($mbbFormatCode);
            //print_r($me->getContent());
            return $me->getContent();
        } catch (\Exception $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => $e->getMessage()]);
            exit(1);
        }
        
    }

    private function transformSerialNo($currentLine, $fieldName) {
        $serialno = $this->convertSerialNo($currentLine[$fieldName]);
        $currentLine[$fieldName] = $serialno;
        return $currentLine;
    }


    public function convertSerialNo($serialno){
        $length = strlen($serialno);
        $alphaLength = 3;
        $alphaMaxLength = 6;
        $noLength = 8;

        //split letter and numbers
        preg_match_all('/(\d)|(\w)/', $serialno, $matches);

        $numbers = implode($matches[1]);
        $letters = implode($matches[2]);

        //check if there is spaces inside letters
        $updateSerialno .= (preg_match('/\s/',$letters)) ? str_replace(' ', '', $letters) : $letters;

        //check length of string for number and remove spaces
        $updateSerialno .= (preg_match('/\s/',$numbers)) ? str_replace(' ', '', $numbers) : $numbers;

        return $updateSerialno;
    }
}

?>