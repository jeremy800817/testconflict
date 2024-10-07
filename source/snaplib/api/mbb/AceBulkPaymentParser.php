<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mbb;

class AceBulkPaymentParser
{
    protected $app = null;
    protected $content = array();
    protected $file;
    protected $preflightCheck = null;
    private $runSftp = true;
    private $path;

    public function __construct($app,$debugOn = false)
    {
        $this->runSftp = ! $debugOn;
        $this->app = $app;

        $this->path = $this->app->getConfig()->{'gtp.bulkpayment.response'};
    }

    public function setFilePath($pathToFile)
    {
        $this->file = (string) $pathToFile;
    }

    public function setPreflightCheck(\Closure $preflightCheck)
    {
        $this->preflightCheck = $preflightCheck;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function parseFile($file) {
        try {
            $me     = $this;
            $path   = $this->path;
            $result = glob ($path.$file.".*");
            //$me->setFilePath($result[0]); //set file path to $this->file   

            $me->setFilePath($file['tmp_name']); //set file path to $this->file    

            //Parse file line by line
            $this->content = array();
            $filePointer = fopen($this->file, "r");

            $i=0;
            while (!feof($filePointer)) {
                $buffer = fgets($filePointer, 4096);
                $this->content[] = $buffer;
            }
            fclose($filePointer);

            $arrayTxt = $me->getContent();

            foreach($arrayTxt as $aTxt){
                $explodeLine    = preg_split('/\|+/', $aTxt, -1, PREG_SPLIT_NO_EMPTY); //string explode by pipe|
                //example of array from $explodeLine
                /*Array
                (
                    [header] => Array
                        (
                            [0] => 00
                            [1] => MYMACECGSB
                            [2] => 22042022GOGOLD
                            [3] => 26042022
                            [4] => 073022
                            [5] => Completed

                        )

                    [records] => Array
                        (
                            [GT202204210900002] => Array
                                (
                                    [0] => Array
                                        (
                                            [0] => 01
                                            [1] => IG
                                            [2] => Domestic Payments (MY)
                                            [3] => 22042022
                                            [4] => EGCE2EF34B
                                            [5] => GT202204210900002
                                            [6] => MYR
                                            [7] => 41.81
                                            [8] => Y
                                            [9] => MYR
                                            [10] => 512361171734
                                            [11] => 12168020211290
                                            [12] => Y
                                            [13] => NUR AISYAH FARHANA
                                            [14] => 880813085610
                                            [15] => 0
                                            [16] => 0
                                            [17] => 0
                                            [18] => BIMBMYKL
                                            [19] => 01
                                            [20] => Successful
                                            [21] => 26042022
                                            [22] => 22042022
                                            [23] => MYIG220422385253
                                            [24] => Successful  22042022

                                        )

                                    [1] => Array
                                        (
                                            [0] => 02
                                            [1] => PA
                                            [2] => EGCE2EF34B
                                            [3] => nuraisyah130888@gmail.com
                                            [4] => GT202204210900002
                                            [5] => 41.81
                                            [6] =>

                                        )

                                )

                            [GT202204210900001] => Array
                                (
                                    [0] => Array
                                        (
                                            [0] => 01
                                            [1] => IG
                                            [2] => Domestic Payments (MY)
                                            [3] => 22042022
                                            [4] => EGCE2EF34B
                                            [5] => GT202204210900001
                                            [6] => MYR
                                            [7] => 41.81
                                            [8] => Y
                                            [9] => MYR
                                            [10] => 512361171734
                                            [11] => 12168020211290
                                            [12] => Y
                                            [13] => NUR AISYAH FARHANA
                                            [14] => 880813085610
                                            [15] => 0
                                            [16] => 0
                                            [17] => 0
                                            [18] => BIMBMYKL
                                            [19] => 01
                                            [20] => Successful
                                            [21] => 26042022
                                            [22] => 22042022
                                            [23] => MYIG220422385253
                                            [24] => Successful  22042022

                                        )

                                    [1] => Array
                                        (
                                            [0] => 02
                                            [1] => PA
                                            [2] => EGCE2EF34B
                                            [3] => nuraisyah130888@gmail.com
                                            [4] => GT202204210900001
                                            [5] => 41.81
                                            [6] =>

                                        )

                                )

                            [GT202204212000003] => Array
                                (
                                    [0] => Array
                                        (
                                            [0] => 01
                                            [1] => IG
                                            [2] => Domestic Payments (MY)
                                            [3] => 22042022
                                            [4] => EG0C7EB437
                                            [5] => GT202204212000003
                                            [6] => MYR
                                            [7] => 354.97
                                            [8] => Y
                                            [9] => MYR
                                            [10] => 512361171734
                                            [11] => 7629831549
                                            [12] => Y
                                            [13] => ASLAN BIN SAMIAN
                                            [14] => 810815045379
                                            [15] => 0
                                            [16] => 0
                                            [17] => 0
                                            [18] => CIBBMYKL
                                            [19] => 01
                                            [20] => Successful
                                            [21] => 26042022
                                            [22] => 22042022
                                            [23] => MYIG220422385254
                                            [24] => Successful  22042022

                                        )

                                    [1] => Array
                                        (
                                            [0] => 02
                                            [1] => PA
                                            [2] => EG0C7EB437
                                            [3] => ahmadshahrul076@gmail.com
                                            [4] => GT202204212000003
                                            [5] => 354.97
                                            [6] =>

                                        )

                                )

                        )

                    [footer] => Array
                        (
                            [0] => 99
                            [1] => 2
                            [2] => 396.78
                            [3] => 1774
                            [4] =>

                        )

                )
                */

                if($explodeLine[0] == '00') $currentLine['header'] = $explodeLine;
                if($explodeLine[0] == '01' || $explodeLine[0] == '02') {
                    if($explodeLine[0] == '01') $key = $explodeLine[5];
                    if($explodeLine[0] == '02') $key = $explodeLine[4];

                    $currentLine['records'][$key][] = $explodeLine;
                }
                if($explodeLine[0] == '99') $currentLine['footer'] = $explodeLine;
            }
            return $currentLine;
        } catch (\Exception $e) {
            throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => $e->getMessage()]);
            exit(1);
        }
    }
}

?>