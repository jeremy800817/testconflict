<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\manager;

use \Snap\InputException;
use \Snap\TLogging;
use \Snap\IObserver;
use \Snap\IObservable;
use \Snap\IObservation;
use \Snap\object\ExportLogs;
use \Snap\object\VaultItem;
use \Snap\object\VaultLocation;
use \Snap\object\Order;
use \Snap\object\Redemption;
use \Snap\object\MyLedger;
use \Snap\object\MyAccountHolder;
use \Snap\object\Partner;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportingManager implements IObservable
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

    // $conditions = ['column' => 'value']; 
    // $specialRenderer = [
    //     'decode' => 'json',
    //     'sqlfield' => 'items',
    //     'displayfield' => ['sapreturnid','serialnumber', 'code'] // JSON OBJECT FIELD
    // ];
    // $specialRenderer data sqlfield will not included in EXT Grid panel which had different renderer on EXT
    // $dateConditionColumnsName data for special column on vw_ example mbbanpfund, user view should be get order->createdon instead of mbbapfund createdon
    public function generateExportFile($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $dateConditionColumnsName = null, $statusRenderer = null, $fromJob = false, $fromJobMode = '')
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {
            $headerText = [];
            $headerSQL = [];

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);

                // add in virtual column next to right of status -- REF:READABLE_STATUS
                if ($headerColumn->text == 'Status' && $statusRenderer) {
                    array_push($headerText, 'Readable Status');
                }
            }
            // print_r($headerText);exit;

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // special column name
            if ($dateConditionColumnsName) {
                $createdon = $dateConditionColumnsName;
            } else {
                $createdon = 'createdon';
            }

            // _PENDING on view/table decision
            $query = $currentStore->searchView()->select($headerSQL)
                ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'));
            // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)

            // $query->addField($query->raw('"" AS MASD'));

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();

            // If any rendering done 
            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            // print_r($queryData[0]));exit;
            if ($statusRenderer) {
                // $conditions = [];
                // foreach ($statusRenderer as $status_db => $status_text){
                //     $conditions[] = $status_db;
                //     $conditions[] = '"'.$status_text.'"';
                // }
                // $conditions[] = '"???"'; // status ELSE
                $conditions = [];
                foreach ($statusRenderer as $status_db => $status_text) {
                    $conditions[] = $status_db . ',' . '"' . $status_text . '"';
                }
                // $status_formula = '=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), ' .implode($conditions, ","). ')'; // excel 2019 + only
                // $status_formula = '=IFS(' .'OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='.implode($conditions, ',OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='). ')'; // excel all version


            } else {
                $status_formula = null;
            }

            $status_formula = $statusRenderer;

            // print_r($status_formula);exit;
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer, $status_formula);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
	    foreach($spreadsheet->getActiveSheet()->getRowIterator() as $row){
                foreach($row->getCellIterator() as $cell){
                    $cell->setValueExplicit($cell->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
	    // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;

                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
                if ($headerColumn->convert) {
                    $column_convert = $headerColumn->convert;
                    array_push($columns, ['column' => $column, 'convert' => $column_convert]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'start' => 2,
                // 'end' => $totalrow + 1,
                'end' => $totalrow + 3
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                // Filter length if exceed 10
                if ($column['convert'] == 'string') {
                    // $styles = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['endconvert'];
                    // $spreadsheet->getActiveSheet()->getStyle($style)->setQuotePrefix(true);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode('#');
                }
            }

            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // =SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1, "asd","??")

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            if($fromJob){
                $by_username = 'fromJob';
                $schedule_date = new \DateTime($dateEnd->format('Y-m-d H:i:s'), new \DateTimeZone('UTC') );
                // $schedule_date->setTimeZone(new \DateTimeZone($this->app->getConfig()->{'snap.timezone.user'}));
                $datenow2 = $schedule_date->format('Y-m-d');
                
                if($fromJobMode == 'monthly'){
                    $p = "_MONTHLYTRN_";
                    $datenow2 = $schedule_date->format('M_Y');
                }
                else if($fromJobMode == 'daily'){
                    $p = "_DAILYTRN_";
                    $datenow2 = $schedule_date->format('Ymd');
                }
                else{$p = "_";}
                // $filename = BURSAGOLD_DAILYTRN_20230626.xlsx
                // $filename = BURSAGOLD_MONTHLYTRN_20230626.xlsx
                $filename = $modulename.$p.$datenow2.'.xlsx';
            }
            else{
                $by_username = $this->app->getUserSession()->getUsername();
                $spreadsheet->getProperties()
                    ->setCreator($by_username)
                    ->setLastModifiedBy($by_username)
                    ->setTitle("ACE".$environtmentFileName.$modulename."_EXPORT_".$by_username)
                    ->setSubject("ACE".$environtmentFileName.$modulename."_EXPORT")
                    ->setDescription(
                        $filename . $by_username
                    );
            }

            // $by_username = $this->app->getUserSession()->getUsername();
            // $spreadsheet->getProperties()
            //     ->setCreator($by_username)
            //     ->setLastModifiedBy($by_username)
            //     ->setTitle("ACE" . $environtmentFileName . $modulename . "_EXPORT_" . $by_username)
            //     ->setSubject("ACE" . $environtmentFileName . $modulename . "_EXPORT")
            //     ->setDescription(
            //         $filename . $by_username
            //     );

            $spreadsheet->getActiveSheet()->setTitle($by_username . $environtmentFileName . $modulename);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            if($fromJob){
                $reportpath = $this->app->getConfig()->{'mygtp.acereport.general'};
                $pathToSave = $reportpath.$filename;

                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                
                $writer->save($pathToSave);

                return [$pathToSave,$filename];
            }
            else{
                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $writer->save("php://output");
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateExportFileWithCalculations($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null)
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // _PENDING on view/table decision
            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)

            if ($conditions) {
                // $query->andWhere($conditions);
                if ($conditions) {
                    if (count($conditions) == 3) {
                        // has IN, 3 parameter
                        $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                    } else {
                        $query->andWhere($conditions);
                    }
                }
            }
            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END

            for ($i = $rows['start']; $i <= $rows['end']; $i++) {
                $totalrange = 'D' . $i . ':' . 'J' . $i;

                $spreadsheet->getActiveSheet()->setCellValue('K' . $i, '=SUM(' . $totalrange . ')');
            }

            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set value for column 
                // If at column K, do something


                // // Column K = Fixed total amount
                // if("K" == $column_alphabet){

                //     $totalrange = 'D'.$rows['start']+$i.':'.'J'.$rows['start']+$i;

                //     $spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['start']+$i,'=SUM('.$totalrange.')');

                // }

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');

                //$spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['end'], $totalcolumn);

                // Set Duitnow Amount

                // Set Check
                //$spreadsheet->getActiveSheet()->setCellValue($checker.$rows['header'],'1');



            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateExportFileWithJsonFields($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $dateConditionColumnsName = null, $statusRenderer = null, $fromJob = false, $fromJobMode = '')
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {
            $headerText = [];
            $headerSQL = [];

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);

                // add in virtual column next to right of status -- REF:READABLE_STATUS
                if ($headerColumn->text == 'Status' && $statusRenderer) {
                    array_push($headerText, 'Readable Status');
                }
            }
            // print_r($headerText);exit;

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    // array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                    if ($specialRenderer['isdisplayedinreport'] !== false) {
                        array_push($headerText, $specialRenderer['sqlfield']);
                    }
                }
            }

            // special column name
            if ($dateConditionColumnsName) {
                $createdon = $dateConditionColumnsName;
            } else {
                $createdon = 'createdon';
            }

            // _PENDING on view/table decision
            $query = $currentStore->searchView()->select($headerSQL)
                ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'));
            // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)

            // $query->addField($query->raw('"" AS MASD'));

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();

            // If any rendering done 
            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            // print_r($queryData[0]));exit;
            if ($statusRenderer) {
                // $conditions = [];
                // foreach ($statusRenderer as $status_db => $status_text){
                //     $conditions[] = $status_db;
                //     $conditions[] = '"'.$status_text.'"';
                // }
                // $conditions[] = '"???"'; // status ELSE
                $conditions = [];
                foreach ($statusRenderer as $status_db => $status_text) {
                    $conditions[] = $status_db . ',' . '"' . $status_text . '"';
                }
                // $status_formula = '=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), ' .implode($conditions, ","). ')'; // excel 2019 + only
                // $status_formula = '=IFS(' .'OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='.implode($conditions, ',OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='). ')'; // excel all version


            } else {
                $status_formula = null;
            }

            $status_formula = $statusRenderer;

            // print_r($status_formula);exit;
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContentWithJson($headerSQL, $queryData, $specialRenderer, $status_formula);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;

                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
                if ($headerColumn->convert) {
                    $column_convert = $headerColumn->convert;
                    array_push($columns, ['column' => $column, 'convert' => $column_convert]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'start' => 2,
                // 'end' => $totalrow + 1,
                'end' => $totalrow + 3
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                // Filter length if exceed 10
                if ($column['convert'] == 'string') {
                    // $styles = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['endconvert'];
                    // $spreadsheet->getActiveSheet()->getStyle($style)->setQuotePrefix(true);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode('#');
                }
            }

            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // =SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1, "asd","??")

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            if($fromJob){
                $by_username = 'fromJob';
                
                $schedule_date = new \DateTime($dateEnd->format('Y-m-d H:i:s'), new \DateTimeZone('UTC') );
                // $schedule_date->setTimeZone(new \DateTimeZone($this->app->getConfig()->{'snap.timezone.user'}));
                $datenow2 = $schedule_date->format('Y-m-d');
                
                if($fromJobMode == 'monthly'){
                    $p = "_MONTHLYCONVERSION_";
                    $datenow2 = $schedule_date->format('M_Y');
                }
                else if($fromJobMode == 'daily'){
                    $p = "_CONVERSION_";
                    $datenow2 = $schedule_date->format('Ymd');
                }
                else{$p = "";}

                $filename = $modulename.$p.$datenow2.'.xlsx';
            }
            else{
                $by_username = $this->app->getUserSession()->getUsername();
                $spreadsheet->getProperties()
                    ->setCreator($by_username)
                    ->setLastModifiedBy($by_username)
                    ->setTitle("ACE".$environtmentFileName.$modulename."_EXPORT_".$by_username)
                    ->setSubject("ACE".$environtmentFileName.$modulename."_EXPORT")
                    ->setDescription(
                        $filename . $by_username
                    );
            }

            // $by_username = $this->app->getUserSession()->getUsername();
            // $spreadsheet->getProperties()
            //     ->setCreator($by_username)
            //     ->setLastModifiedBy($by_username)
            //     ->setTitle("ACE" . $environtmentFileName . $modulename . "_EXPORT_" . $by_username)
            //     ->setSubject("ACE" . $environtmentFileName . $modulename . "_EXPORT")
            //     ->setDescription(
            //         $filename . $by_username
	    //     );
	    $sheet_title = $by_username . $environtmentFileName . $modulename;
	    if(strlen($sheet_title)>30){
	        $sheet_title = substr($sheet_title,0,30);
	    }
            //$spreadsheet->getActiveSheet()->setTitle($by_username . $environtmentFileName . $modulename);
	    $spreadsheet->getActiveSheet()->setTitle($sheet_title);
	    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            if($fromJob){
                $reportpath = $this->app->getConfig()->{'mygtp.acereport.general'};
                $pathToSave = $reportpath.$filename;

                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                
                $writer->save($pathToSave);

                return [$pathToSave,$filename];
            }
            else{
                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                
                $writer->save("php://output");
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateReportWithList($list, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $originalPrice = null, $tableName = 'Unknown')
    {
        try {

            $headerText = [];
            $headerSQL = [];
            $queryData = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
                $matchedValue = null;
                foreach ($list as $item) {
                    if (property_exists($item, $headerColumn->index)) {
                        $matchedValue = $item->{$headerColumn->index};

                        break;
                    }
                }

                if ($matchedValue !== null) {
                    array_push($queryData, $matchedValue);
                } else {}
            }

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);

            $contentString = $this->createDirectContent($headerSQL, $list, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');

            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = count($list);
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            $alphabet = range('A', 'Z');
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                if ($column['decimal'] == 6) {
                    $decimal_format = '0.000000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            $projectBase = $this->app->getConfig()->{'projectBase'};

            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {

                $table = $tableName;
                $select = json_encode($headerSQL);
                $conditions = json_encode($conditions);
                $outputCount = $totalrow;
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            header('Content-Type: application/vnd.ms-excel');

            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  
        }
    }
    // $conditions = ['column' => 'value']; 
    // $specialRenderer = [
    //     'decode' => 'json',
    //     'sqlfield' => 'items',
    //     'displayfield' => ['sapreturnid','serialnumber', 'code'] // JSON OBJECT FIELD
    // ];
    // $specialRenderer data sqlfield will not included in EXT Grid panel which had different renderer on EXT
    // $dateConditionColumnsName data for special column on vw_ example mbbanpfund, user view should be get order->createdon instead of mbbapfund createdon
    public function generateMintedExportFile($data, $header, $dateStart, $dateEnd, $type)
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {
            $headerText = [];
            $headerSQL = [];

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            // Generate column create
            $text = '<table>
            ';
            foreach ($data as $row) {
                $text .= '
                <tr>';
                // foreach ($row as  $key => $value){
                //     $text .= '
                //     <td>
                //     '.$value.'
                //     </td>';
                // }
                foreach ($headerSQL as  $index) {
                    $value = $row[$index];
                    $text .= '
                    <td>
                    ' . $value . '
                    </td>';
                }
                $text .=
                    '</tr>';
            }
            $text .= '
            </table>';

            // End Create
            $headerString = $this->createHeader($headerText);
            $contentString = $text;
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . 'Minted' . '_EXPORT_' . $datenow . '.xlsx';

            $by_username = $this->app->getUserSession()->getUsername();
            $spreadsheet->getProperties()
                ->setCreator($by_username)
                ->setLastModifiedBy($by_username)
                ->setTitle("ACE" . $environtmentFileName . $type . "_EXPORT_" . $by_username)
                ->setSubject("ACE" . $environtmentFileName . $type . "_EXPORT")
                ->setDescription(
                    $filename . $by_username
                );
            $spreadsheet->getActiveSheet()->setTitle($by_username . $environtmentFileName);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');


            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    // generate daily limit ( partnerservice report )
    public function generateExportFileRelatedStore($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $dateConditionColumnsName = null, $statusRenderer = null, $resultCallback = null, $addPostQueryHeaders = null)
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {
            $headerText = [];
            $headerSQL = [];

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);

                // add in virtual column next to right of status -- REF:READABLE_STATUS
                if ($headerColumn->text == 'Status' && $statusRenderer) {
                    array_push($headerText, 'Readable Status');
                }
            }
            // print_r($headerText);exit;

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // special column name
            if ($dateConditionColumnsName) {
                $createdon = $dateConditionColumnsName;
            } else {
                $createdon = 'createdon';
            }

            // no header sql
            $query = $currentStore->searchView()->select($headerSQL);
            //   ->where('partnerid', 1);
            // ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
            // ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'));
            // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)

            // $query->addField($query->raw('"" AS MASD'));

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();


            if ($addPostQueryHeaders) {
                foreach ($addPostQueryHeaders as $headerColumn) {
                    array_push($headerText, $headerColumn->text);
                    array_push($headerSQL, $headerColumn->index);
                    array_push($header, $headerColumn);
                }
            }

            // If any callback done 
            if ($resultCallback) {
                $newData = $resultCallback($queryData);
            }

            // If any rendering done 
            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }



            // print_r($queryData[0]));exit;
            if ($newData) {
                $newFields = $newData;
            } else {
                $newFields = null;
            }

            $status_formula = $statusRenderer;

            // print_r($status_formula);exit;
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createCustomContent($headerSQL, $queryData, $specialRenderer, $newFields);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;

                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
                if ($headerColumn->convert) {
                    $column_convert = $headerColumn->convert;
                    array_push($columns, ['column' => $column, 'convert' => $column_convert]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'start' => 2,
                // 'end' => $totalrow + 1,
                'end' => $totalrow + 3
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                // Filter length if exceed 10
                if ($column['convert'] == 'string') {
                    // $styles = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['endconvert'];
                    // $spreadsheet->getActiveSheet()->getStyle($style)->setQuotePrefix(true);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode('#');
                }
            }

            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // =SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1, "asd","??")

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            $by_username = $this->app->getUserSession()->getUsername();
            $spreadsheet->getProperties()
                ->setCreator($by_username)
                ->setLastModifiedBy($by_username)
                ->setTitle("ACE" . $environtmentFileName . $modulename . "_EXPORT_" . $by_username)
                ->setSubject("ACE" . $environtmentFileName . $modulename . "_EXPORT")
                ->setDescription(
                    $filename . $by_username
                );
            $spreadsheet->getActiveSheet()->setTitle($by_username . $environtmentFileName . $modulename);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    // Generate new export file with search parameters
    public function generateExportFileFromSearchResult($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $dateConditionColumnsName = null, $statusRenderer = null, $searchResults = null)
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {
            $headerText = [];
            $headerSQL = [];

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);

                // add in virtual column next to right of status -- REF:READABLE_STATUS
                if ($headerColumn->text == 'Status' && $statusRenderer) {
                    array_push($headerText, 'Readable Status');
                }
            }
            // print_r($headerText);exit;

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    // array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                    if ($specialRenderer['isdisplayedinreport'] !== false) {
                        array_push($headerText, $specialRenderer['sqlfield']);
                    }
                }
            }

            // special column name
            if ($dateConditionColumnsName) {
                $createdon = $dateConditionColumnsName;
            } else {
                $createdon = 'createdon';
            }

            // _PENDING on view/table decision
            $query = $currentStore->searchView()->select($headerSQL)
                ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'));
            // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)

            // $query->addField($query->raw('"" AS MASD'));

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            if ($searchResults) {
                foreach ($searchResults as $key => $value) {
                    if (count($value) > 1) {
                        // has IN, 3 parameter
                        $query->andWhere($key, $value[0], $value[1]);
                    } else {
                        $query->andWhere($key, $value);
                    }
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();

            // If any rendering done 
            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            // print_r($queryData[0]));exit;
            if ($statusRenderer) {
                // $conditions = [];
                // foreach ($statusRenderer as $status_db => $status_text){
                //     $conditions[] = $status_db;
                //     $conditions[] = '"'.$status_text.'"';
                // }
                // $conditions[] = '"???"'; // status ELSE
                $conditions = [];
                foreach ($statusRenderer as $status_db => $status_text) {
                    $conditions[] = $status_db . ',' . '"' . $status_text . '"';
                }
                // $status_formula = '=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), ' .implode($conditions, ","). ')'; // excel 2019 + only
                // $status_formula = '=IFS(' .'OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='.implode($conditions, ',OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1)='). ')'; // excel all version


            } else {
                $status_formula = null;
            }

            $status_formula = $statusRenderer;

            // print_r($status_formula);exit;
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContentWithJson($headerSQL, $queryData, $specialRenderer, $status_formula);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;

                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
                if ($headerColumn->convert) {
                    $column_convert = $headerColumn->convert;
                    array_push($columns, ['column' => $column, 'convert' => $column_convert]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'start' => 2,
                // 'end' => $totalrow + 1,
                'end' => $totalrow + 3
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                // Filter length if exceed 10
                if ($column['convert'] == 'string') {
                    // $styles = $column_alphabet.$rows['start'].':'.$column_alphabet.$rows['endconvert'];
                    // $spreadsheet->getActiveSheet()->getStyle($style)->setQuotePrefix(true);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode('#');
                }
            }

            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // =SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1, "asd","??")

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            $by_username = $this->app->getUserSession()->getUsername();
            $spreadsheet->getProperties()
                ->setCreator($by_username)
                ->setLastModifiedBy($by_username)
                ->setTitle("ACE" . $environtmentFileName . $modulename . "_EXPORT_" . $by_username)
                ->setSubject("ACE" . $environtmentFileName . $modulename . "_EXPORT")
                ->setDescription(
                    $filename . $by_username
                );
            $spreadsheet->getActiveSheet()->setTitle($by_username . $environtmentFileName . $modulename);
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateTransactionReportFromSearchResult($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $searchResults = null, $originalPrice = null)
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // _PENDING on view/table decision
            /*
            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
                // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)
            */

            /*
            if('dailytransactionsell' == $type){
                $searchtype =  \Snap\object\Order::TYPE_COMPANYSELL;
            }else if ('dailytransactionbuy' == $type){
                $searchtype =   \Snap\object\Order::TYPE_COMPANYBUY;
            }
            */

            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            //->andWhere('ordtype', $searchtype);

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            if ($searchResults) {
                foreach ($searchResults as $key => $value) {
                    if (count($value) > 1) {
                        // has IN, 3 parameter
                        $query->andWhere($key, $value[0], $value[1]);
                    } else {
                        $query->andWhere($key, $value);
                    }
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();


            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            //$spreadsheet->getActiveSheet()->getStyle('B2')
            //->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');

                //$spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['end'], $totalcolumn);

                // Set Duitnow Amount

                // Set Check
                //$spreadsheet->getActiveSheet()->setCellValue($checker.$rows['header'],'1');



            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateTransactionReport($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $originalPrice = null)
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // _PENDING on view/table decision
            /*
            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
                // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)
            */

            /*
            if('dailytransactionsell' == $type){
                $searchtype =  \Snap\object\Order::TYPE_COMPANYSELL;
            }else if ('dailytransactionbuy' == $type){
                $searchtype =   \Snap\object\Order::TYPE_COMPANYBUY;
            }
            */

            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            //->andWhere('ordtype', $searchtype);

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();


            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            //$spreadsheet->getActiveSheet()->getStyle('B2')
            //->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');

                //$spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['end'], $totalcolumn);

                // Set Duitnow Amount

                // Set Check
                //$spreadsheet->getActiveSheet()->setCellValue($checker.$rows['header'],'1');



            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    // generate transaction report with list data
    public function generateTransactionReportWithList($list, $header, $dateStart = '', $dateEnd = '', $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $originalPrice = null, $tableName = 'Unknown')
    {
        try {
        
        
            $headerText = [];
            $headerSQL = [];
            $queryData = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
                // Find the matched value in $list based on the header index
                $matchedValue = null;
                foreach ($list as $item) {
                    if (array_key_exists($headerColumn->index, $item)) {
                        $matchedValue = $item->{$headerColumn->index};
                        
                        break;
                    }
                }
                
                if ($matchedValue !== null) {
                    // Value found, do something with $matchedValue
                    // For example, push it into the $queryData array:
                    array_push($queryData, $matchedValue);
                } else {
                    // Value not found
                }
            }

            // $query = $currentStore->searchView()->select($headerSQL)
            //     ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
            //     ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            // //->andWhere('ordtype', $searchtype);

            // if ($conditions) {
            //     if (count($conditions) == 3) {
            //         // has IN, 3 parameter
            //         $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
            //     } else {
            //         $query->andWhere($conditions);
            //     }
            // }
            // $query->orderBy("id", "DESC");
            // $queryData = $query->execute();


            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);

            $contentString = $this->createDirectContent($headerSQL, $list, $specialRenderer);
            $excelpages = $headerString . $contentString;
            
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
	    foreach($spreadsheet->getActiveSheet()->getRowIterator() as $row){
                foreach($row->getCellIterator() as $cell){
                    $cell->setValueExplicit($cell->getValue(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
	    $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            //$spreadsheet->getActiveSheet()->getStyle('B2')
            //->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = count($list);
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
                $spreadsheet->getActiveSheet()->getStyle($column_alphabet . $rows['total'])->getNumberFormat()->setFormatCode('#,##0.' . str_repeat('0', $column['decimal']));
                //$spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['end'], $totalcolumn);

                // Set Duitnow Amount

                // Set Check
                //$spreadsheet->getActiveSheet()->setCellValue($checker.$rows['header'],'1');



            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $tableName;
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $totalrow;
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateCustomTransactionReport($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $conditions_2 = null, $conditions_3 = null, $specialRenderer = null)
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // _PENDING on view/table decision
            /*
            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
                // ->andWhere('status', \Snap\object\MbbApFund::STATUS_ACTIVE)
            */

            /*
            if('dailytransactionsell' == $type){
                $searchtype =  \Snap\object\Order::TYPE_COMPANYSELL;
            }else if ('dailytransactionbuy' == $type){
                $searchtype =   \Snap\object\Order::TYPE_COMPANYBUY;
            }
            */

            $query = $currentStore->searchView()->select($headerSQL)
                ->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            //->andWhere('ordtype', $searchtype);

            if ($conditions) {
                if (count($conditions) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
                } else {
                    $query->andWhere($conditions);
                }
            }

            if ($conditions_2) {
                if (count($conditions_2) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions_2[0], $conditions_2[1], $conditions_2[2]);
                } else {
                    $query->andWhere($conditions_2);
                }
            }

            if ($conditions_3) {
                if (count($conditions_3) == 3) {
                    // has IN, 3 parameter
                    $query->andWhere($conditions_3[0], $conditions_3[1], $conditions_3[2]);
                } else {
                    $query->andWhere($conditions_3);
                }
            }

            $query->orderBy("id", "DESC");
            $queryData = $query->execute();


            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddHeader('&C&HPlease treat this document as confidential!');
            // $spreadsheet->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B&RPage &P of &N');

            //$spreadsheet->getActiveSheet()->getStyle('B2')
            //->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            // summary
            // $row_data_first = 2;
            // $row_data_last = $spreadsheet->getActiveSheet()->getHighestRow();
            // $spreadsheet->getActiveSheet()
            //     ->setCellValue(
            //         'c6',
            //         '=SUM(C2:C5)'
            //     );
            // summary end

            // load formating decimal START
            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            // $alphabet[3]; // returns D
            // array_search('D', $alphabet); // returns 3
            // load formating decimal END


            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');

                //$spreadsheet->getActiveSheet()->setCellValue($column_alphabet.$rows['end'], $totalcolumn);

                // Set Duitnow Amount

                // Set Check
                //$spreadsheet->getActiveSheet()->setCellValue($checker.$rows['header'],'1');



            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function doExportZip($app, $params)
    {
        // $statusRenderer = [
        //     1 => 'active',
        //     2 => 'inactive'
        // ];
        try {

            // Init back
            if ($params['currentstore'] != null) {
                $TABLE_NAME = $params['currentstore'];
                $currentStore = $app->{$TABLE_NAME . 'Store'}();
                // $currentStore = $app->mbbapfundStore();
            } else {
                $this->log("No store selected, unable to identify store", SNAP_LOG_ERROR);
                throw  new \Exception('Internal Error. Unable to proceed export with unidentified store.');
            }

            $header = $params['header'];
            $dateStart = $params['datestart'];
            $dateEnd = $params['dateend'];
            $modulename = $params['modulename'];

            if ($params['summary'] != null) {
                $summary = $params['summary'];
            } else {
                $summary = false;
            }

            if ($params['filename'] != null) {
                $filename = $params['filename'];
            } else {
                $filename = null;;
            }

            if ($params['conditions'] != null) {
                $conditions = $params['conditions'];
            } else {
                $conditions = null;
            }

            if ($params['specialrenderer'] != null) {
                $specialRenderer = $params['specialrenderer'];
            } else {
                $specialRenderer = null;
            }

            if ($params['dateconditioncolumnsname'] != null) {
                $dateConditionColumnsName = $params['dateconditioncolumnsname'];
            } else {
                $dateConditionColumnsName = null;
            }

            if ($params['statusrenderer'] != null) {
                $statusRenderer = $params['statusrenderer'];
            } else {
                $statusRenderer = null;
            }

            // end init for job process

            $headerText = [];
            $headerSQL = [];

            // quick init for indexing
            // array_push($headerText, 'Index');
            // array_push($headerSQL, 'index');
            // end indexing
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn['text']);


                // call hydrahon if there are custom renders
                if ($headerColumn['hydrahon']) {
                    $original = $headerColumn['index'];
                    $headerColumn['index'] = $currentStore->searchTable(false)->raw(
                        $headerColumn['value']
                    );
                    $headerColumn['index']->original = $original;
                    $kiku = "a";
                    array_push($headerSQL, $headerColumn['index']);
                } else {
                    array_push($headerSQL, $headerColumn['index']);
                }


                // add in virtual column next to right of status -- REF:READABLE_STATUS
                if ($headerColumn->text == 'Status' && $statusRenderer) {
                    array_push($headerText, 'Readable Status');
                }
            }
            // print_r($headerText);exit;

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $saveStartAt = $startAt;
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $saveEndAt = $endAt;
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }

            // special column name
            if ($dateConditionColumnsName) {
                $createdon = $dateConditionColumnsName;
            } else {
                $createdon = 'createdon';
            }

            // Add new functions
            //limit and page
            // start updated by weng on 2021/10/22 for export file
            $limit = 500; /* tested for more than 20k records */

            // Do Query Count first
            // $queryA = $currentStore->searchView()->select()
            // ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
            // ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'));
            $arrayQuery = $currentStore->searchView(false)->select()
                ->addFieldCount('id', 'id')
                ->one();


            // $queryA->execute();
            $queryCount = (int)$arrayQuery['id'];


            $totalRecords = $queryCount;

            //zip filename & xls filename
            // Set zip path
            $dir = basename(dirname(__FILE__));


            // Check count and do something
            if ($queryCount) {

                $totalPages = ceil($totalRecords / $limit);
            }

            // Set filename and path
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }


            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');


            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_FROM' . $dateStart . 'TO' . $dateEnd;

            // check dir if not available create folder
            if (!file_exists('./downloads')) {
                mkdir('downloads', 0777, true);
            }
            $zipFile = SNAPAPP_DIR . 'downloads/' . $filename . '.zip';
            $xlsFile = SNAPAPP_DIR . 'downloads/' . $filename . '.csv';
            // $zipFile = 'C:/Users/HP/Desktop/backup/' . $filename . '.zip';
            // $xlsFile = 'C:/Users/HP/Desktop/backup/' . $filename . '.xlsx';

            //header
            if (0 < $totalRecords) {
                $fp = fopen($xlsFile, 'w');
                //file_put_contents($xlsFile, $this->writeXlsHeader( $gridFields), FILE_APPEND);
                fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
                fputcsv($fp, $headerText); //generate head with header text
            }

            $index = 0;
            for ($page = 1; $page <= $totalPages; $page++) {
                $params['exportpage'] = $page;
                $queryData = array();
                $offset = ($page - 1) * $limit;
                // actual data from query
                $objects = $this->splitQuery($currentStore, $headerSQL, $conditions, $createdon, $startAt, $endAt, $limit, $offset);

                // set name back to original
                foreach ($headerSQL as $key => $val) {
                    if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                        $headerSQL[$key] = $val->original;
                    }
                }

                // Merge data as querydata
                foreach ($objects as    $obj) {
                    $queryData[] = $obj;
                    $index++;
                }
                // Save 
                foreach ($queryData as $oneRecord) {
                    // $row = array();
                    foreach ($headerSQL as $dataIndex => $dataTitle) {
                        // if(!$headerSQL == "index"){
                        //     $newRecord[$dataTitle] = $oneRecord->{$dataTitle};
                        // }else{
                        //     $newRecord[$dataTitle] = $oneRecord->{$dataTitle};
                        // }
                        // $row[$dataTitle] = $oneRecord[$dataIndex];
                        // if(gettype($oneRecord->{$dataTitle}) == 'object'){
                        //     $this->log("This is the headersql for createdon".$headerSQL."  And this is the ". $oneRecord->{$dataTitle}->date."type is ".gettype($oneRecord->{$dataTitle}->format('Y-m-d_H-i-s')));
                        // }
                        // $this->log("This is the headersql".$dataIndex."  And this is the ". $oneRecord->{$dataTitle}."type is ".gettype($oneRecord->{$dataTitle}). "object ". $oneRecord->{$dataTitle}->format('Y-m-d_H-i-s'));
                        $this->log("This is the headersql" . $dataIndex . "  And this is the " . $oneRecord->{$dataTitle} . "type is " . gettype($oneRecord->{$dataTitle}) . "object " . $oneRecord->{$dataTitle});
                        if ($oneRecord->{$dataTitle} instanceof \DateTime) {
                            $newRecord[$dataTitle] = $oneRecord->{$dataTitle}->format("Y-m-d H:i:s");
                        } else {
                            $newRecord[$dataTitle] = $oneRecord->{$dataTitle};
                        }
                    }
                    // array_push($row, $newRecord);
                    //write data to file
                    //file_put_contents($xlsFile, $exporter->formatOneRecord($oneRow, $gridFields), FILE_APPEND);
                    fputcsv($fp, $newRecord, ',', '"');
                }
                $totalRecords += count($queryData);
            }



            $by_username = $params['username'];
            // $spreadsheet->getProperties()
            //     ->setCreator($by_username)
            //     ->setLastModifiedBy($by_username)
            //     ->setTitle("ACE".$environtmentFileName.$modulename."_EXPORT_".$by_username)
            //     ->setSubject("ACE".$environtmentFileName.$modulename."_EXPORT")
            //     ->setDescription(
            //         $filename . $by_username
            //     );
            // $spreadsheet->getActiveSheet()->setTitle($by_username.$environtmentFileName.$modulename);
            // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $email = $params['email'];
            $userId = (int)$params['userid'];
            $remarks = "Generated by DoExportZipCliJob";

            // Create a temporary file of and write our data to it
            $baseXLSFile = basename($xlsFile);
            $realXLSFile = realpath($xlsFile);

            if ($realXLSFile) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $saveStartAt; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $saveEndAt; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                // $outputCount = $query->count('id');
                $outputCount = $queryCount;

                $createExportLog = $this->app->ExportLogStore()->create([
                    'table' => $table,
                    'select' => $select,
                    'where' => $conditions,
                    'datestart' => $selectDateStart,
                    'dateend' => $selectDateEnd,
                    'outputcount' => $outputCount,
                    'status' => ExportLogs::STATUS_ACTIVE,
                    'sendtoemail' => $email,
                    'remarks' => $remarks,
                    'actionby' => $userId,
                ]);
                $this->app->ExportLogStore()->save($createExportLog);
            }

            // ob_end_clean();
            // header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment; filename="'.$xlsFile.'"');

            // Zip file save
            // Now zip the file and move the temporary file to it
            $baseZIPFile = basename($zipFile);

            // test 
            //  $smtpObject = $this->app->getMailer();

            //  $smtpObject->setFrom('noreply@ace2u.com', 'testmail');
            //  $smtpObject->addAddress('ang@silverstream.my', 'ang@silverstream.my');
            //  $smtpObject->Subject = 'test file ' . SNAPAPP_DIR.'downloads';
            //  $smtpObject->Body = 'Please download the attachment.'.$zipFile;
            // //  $smtpObject->AddAttachment($zipFile);
            //  $smtpObject->SMTPDebug = 0;  
            //  $emailSend = $smtpObject->send();
            // end

            $zip = new \ZipArchive();
            $zip->open($zipFile, \ZipArchive::CREATE);
            $zip->addFile($xlsFile, basename($xlsFile));
            $zip->close();


            unlink($xlsFile);

            // try sth 
            // $list = array (
            //     array('aaa', 'bbb', 'ccc', 'dddd'),
            //     array('123', '456', '789'),
            //     array('"aaa"', '"bbb"')
            // );

            // $csvfile = SNAPAPP_DIR . '/downloads/file.csv';
            // echo $csvfile;
            // $fp = fopen($csvfile, 'w');

            // foreach ($list as $v) {
            //     fputcsv($fp, $v);
            // }

            // fclose($fp);

            // $zip = new \ZipArchive();
            // $file = SNAPAPP_DIR . "/downloads/test112.zip";

            // if ($zip->open($file, \ZipArchive::CREATE)!==TRUE) {
            //     exit("cannot open <$file>\n");
            // }

            // $zip->addFromString("testfilephp.txt" . time(), "#1 This is a test string added as testfilephp.txt.\n");
            // $zip->addFromString("testfilephp2.txt" . time(), "#2 This is a test string added as testfilephp2.txt.\n");
            // $zip->addFile($csvfile, "file.csv");
            // echo "numfiles: " . $zip->numFiles . "\n";
            // echo "status:" . $zip->status . "\n";

            // $zip->close();

            // unlink($csvfile);


            // end try 

            //email
            // start updated by weng on 2021/10/22 for export file
            try {
                // if ($bEmail) {
                //     $email = $params['zipemail'];
                //     $exporter->exportZip($zipFile, true, $email);
                // } else { //download
                //     $exporter->exportZip($zipFile);
                // }
                $this->exportZip($zipFile, true, $email, $environtmentFileName . $modulename);
                $this->log("[ExportFile] Able to send download file [{$baseZIPFile}] to [{$email}] {logto=graylog exportfile=1}", SNAP_LOG_ERROR);
            } catch (\Exception $e) {
                $this->log("[ExportFile] Unable to send download file [{$baseZIPFile}] to [{$email}] with error [{$e->getMessage()}] {logto=graylog exportfile=1}", SNAP_LOG_ERROR);
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to Send Mail", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    /** 
     * This Function serves as an export zip 
     */
    function exportZip($filename = '', $toEmail = false, $emailAddress = '', $environtmentFileName)
    {
        //Ready for output of the data
        $contenttransferencoding = 'BINARY';
        $contenttype = 'application/zip';

        //export zip to email
        if ($toEmail ==  true) {
            // start updated by weng on 2021/10/22 for export file
            try {
                $this->exportZipToEmail($filename, $emailAddress, $environtmentFileName);
            } catch (\Exception $e) {
                throw $e;
            }
            // end updated by weng on 2021/10/22
        } else { //download zip from page
            header('Pragma: public');     // required
            header('Expires: 0');        // no cache
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Type: ' . $contenttype);
            if (strlen($contenttransferencoding) > 0) header('Content-Transfer-Encoding: ' . $contenttransferencoding);
            header("Content-Disposition: attachment; filename=\"" . urlencode(basename($filename)) . "\"");
            header("Content-Length: " . filesize($filename));
            header("fileName: " . urlencode(basename($filename)) . "");
            header('Connection: close');
            readfile($filename);
        }
        return true;
    }

    /** 
     * This Function serves as an export to email 
     */
    function exportZipToEmail($filename, $email, $environtmentFileName)
    {
        // /* Exception class. */
        // require_once(mxApp::getInstance()->getLibPath().'adaptor/phpmailer/src/Exception.php');	
        // /* The main PHPMailer class. */
        // require_once(mxApp::getInstance()->getLibPath().'adaptor/phpmailer/src/PHPMailer.php');
        // /* The SMTP class. */
        // require_once(mxApp::getInstance()->getLibPath().'adaptor/phpmailer/src/SMTP.php');

        // start updated by weng on 2022/01/12 to send email thru mailgun smtp
        //$mail = new PHPMailer(TRUE);
        // $smtpObject = $this->app->getMailer();
        // $smtpObject = new PHPMailer(TRUE);
        $mailer = $this->app->getMailer();

        try {
            // try csv export
            $attachmentName = basename($filename);
            $subject = "Zip Export for " . $attachmentName;
            $body = 'Please download the attachment below';
            // $subject = 'Download file ' . basename($filename);
            // $body = 'Please download the attachment.';



            $senderEmail = $this->app->getConfig()->{'snap.mailer.senderemail'};
            $senderName = 'ACE';

            $mailer->addaddress($email);
            $mailer->setFrom($senderEmail, $senderName);

            $mailer->addAttachment($filename, $attachmentName);
            $mailer->Subject = $subject;
            $mailer->Body    = $body;
            $mailer->send();

            // $smtpObject->setFrom('noreply@ace2u.com', 'ACE_'.$environtmentFileName);
            // $smtpObject->addAddress($email, $email);
            // $smtpObject->Subject = 'Download file ' . basename($filename);
            // $smtpObject->Body = 'Please download the attachment.';
            // $smtpObject->addAttachment($filename, basename($filename));
            // // $smtpObject->SMTPDebug = 0;  
            // $smtpObject->send();

            // unlink files
            if (file_exists($filename)) {
                unlink($filename);
            }
            // unlink($zipFile);
        } catch (\Exception $e) {
            /* PHPMailer exception. */
            $erroMsg = $e->getMessage();
            $this->log(__METHOD__ . "Error to get Send Email out for zip" . $erroMsg, SNAP_LOG_ERROR);
            throw $e;
        } catch (\Exception $e) {
            /* PHP exception (note the backslash to select the global namespace Exception class). */
            $msg = $e->getMessage();
            $this->log(__METHOD__ . "Error to proceed", SNAP_LOG_ERROR);
            throw $e;
        }
        // end updated by weng on 2022/01/12
    }
    /** 
     * This Function serves as partition for sql query 
     */
    function splitQuery($currentStore, $headerSQL, $conditions, $createdon, $startAt, $endAt, $limit, $offset)
    {

        // $createdon = 'createdon';

        $query = $currentStore->searchView()->select($headerSQL)
            ->where($createdon, '>=', $startAt->format('Y-m-d H:i:s'))
            ->andWhere($createdon, '<=', $endAt->format('Y-m-d H:i:s'))
            ->orderBy("id", "DESC")
            ->limit($offset, $limit);

        if ($conditions) {
            if (count($conditions) == 3) {
                // has IN, 3 parameter
                $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
            } else {
                $query->andWhere($conditions);
            }
        }

        $return = $query->execute();

        $total = $query->count('id');

        // foreach ($return as $x => $temp){
        //     $createdon = $this->convertUTCToUserDatetime($temp['ord_createdon']);
        //     $return[$x]['ord_createdon'] = $createdon->format('Y-m-d H:i:s');
        // }
        $return = $return;

        return $return;
    }

    /** 
     * This Function serves as a zip export
     */
    function generateExportFileZip($currentStore, $header, $dateStart, $dateEnd, $modulename, $summary = false, $filename = null, $conditions = null, $specialRenderer = null, $dateConditionColumnsName = null, $statusRenderer = null, $email = null)
    {
        $bSuccess = false;
        //$email = $this->user->email;
        // $email = $params['zipemail'];


        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) == 0) {
            /*
			$http = 'http://';
			if(!empty($_SERVER['HTTPS'])) {
				$http = 'https://';
			}
			$profileUrl = $http . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$profileUrl = preg_replace('/(\\?.*)$/', '', $url);
			$profileUrl .= '?hdl=main&aot=user';
			$errmsg = "Invalid email format '".$email."', please check your <a href='".$profileUrl."'>user profile</a>"; 
			*/
            $errmsg = "Invalid email format '" . $email . "'";
        } else {
            $bSuccess = true;
            $errmsg = "Zip file will be send to " . $email . " after processing. if you do not receive this email please check your spam or junk mail folder.";
            // $params['conditions'] = $this->getAllNameValues($params, $this->user, $this->getObject($params['aot']));
            $params['userId'] = $this->user->id;
            $params['exportclijob'] = '1';

            // params for export
            if (!$summary) {
                $summary = false;
            }
            if (!$filename) {
                $filename = null;
            }
            if (!$conditions) {
                $conditions = null;
            }
            if (!$specialRenderer) {
                $specialRenderer = null;
            }
            if (!$dateConditionColumnsName) {
                $dateConditionColumnsName = null;
            }
            if (!$statusRenderer) {
                $statusRenderer = null;
            }
            if (!$statusRenderer) {
                $statusRenderer = null;
            }
            if (!$email) {
                $email = null;
            }

            // get username
            $by_username = $this->app->getUserSession()->getUsername();
            $userId = $this->app->getUserSession()->getUser()->id;
            $this->app->startCLIJob("DoExportZipCliJob.php", [
                'currentstore'  => $currentStore,
                'header'        => $header,
                'datestart'     => $dateStart,
                'dateend'       => $dateEnd,
                'modulename'    => $modulename,
                'summary'       => $summary,
                'filename'      => $filename,
                'conditions'    => $conditions,
                'specialrenderer'  => $specialRenderer,
                'dateconditioncolumnsname' => $dateConditionColumnsName,
                'statusrenderer'  => $statusRenderer,
                'email' => $email,
                'username' => $by_username,
                'userid' => $userId,
            ], true);
        }
        echo json_encode(array('success' => $bSuccess, 'errmsg' =>    $errmsg));
    }

    public function generateAccountReport($currentStore, $header, $modulename, $filename = null, $conditions = null, $resultCallback = null)
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL);

            if ($conditions) {
                $query->where($conditions);
            }

            $queryData = $query->execute();

            if ($resultCallback) {
                $queryData = $resultCallback($queryData);
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

            // load formating decimal START
            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            $spreadsheet->getActiveSheet()->getStyle("A1:Z1")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $alphabet = range('A', 'Z');

            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];
                $style = $column_alphabet . $rows['total'];
                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];
                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];
                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data for DAILYTRN", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateMyGtpReport($currentStore, $header, $modulename = null, $filename = null, $conditions = null, $specialRenderer = null, $viewIndex = 1, $alignRight = true, $saveToFile = false, $orderBy = 'DESC')
    {
        try {
            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }
            
            if ($specialRenderer) {
                if ($specialRenderer['decode'] == 'json') {
                    array_push($headerText, $specialRenderer['sqlfield']);
                    array_push($headerSQL, $specialRenderer['sqlfield']);
                }
            }
            
            $query = $currentStore->searchView(true, $viewIndex)->select($headerSQL);
            
            if ($conditions) {
                $query->where($conditions);
            }

            // if ($conditions){
            //     if (count($conditions) == 3){
            //         // has IN, 3 parameter
            //         $query->andWhere($conditions[0], $conditions[1], $conditions[2]);
            //     }else{
            //         $query->andWhere($conditions);
            //     }
            // }
            $query->orderBy("id", "DESC");
            $queryData = $query->execute();
            
            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }
            
            
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;
            
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            if ($alignRight) {
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }
            
            // load formating decimal START
            $columns = [];
            
            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];
            
            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];
                
                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];
                
                $decimal_format = '0.' . str_pad('', intval($column['decimal']), 0);
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }
            
            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];
                
                $style = $column_alphabet . $rows['total'];
                
                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];
                
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                
                // Compare and check if prices match
                
                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            
            if ($saveToFile) {
                $temp = tmpfile();
                $writer->save($temp);
                return $temp;
            }
            
            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = 'BSN' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save("php://output");
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateTransactionReportByAuto($partner, $currentStore, $currentdate, $modulename, $reportpath, $emailOut = false, $emailList)
    {
        try {
            $date               = date('Y-m-d h:i:s', $currentdate);
            $fpxCharge          = $this->app->getConfig()->{'mygtp.m1.charge'};
            $headerText = [];
            $headerSQL = [];

            $createDateStart    = date_create($date);
            $modifyDateStart    = date_modify($createDateStart, "-2 day");
            $startDate          = date_format($modifyDateStart, "Y-m-d 16:00:00");
            $createDateEnd      = date_create($date);
            $modifyDateEnd      = date_modify($createDateEnd, "-1 day");
            $endDate            = date_format($modifyDateEnd, "Y-m-d 15:59:59");
            $reportdate         = date('Ymd', $currentdate);

            if ($currentStore == 'mygoldtransaction') {
                $partneridColName = 'ordpartnerid';
                $currentStore  = $this->app->mygoldtransactionStore();
                $prefix = $currentStore->getColumnPrefix();
                $header = [
                    (object)["text" => "Date", "index" => "createdon"],
                    (object)["text" => "Transaction Ref No", "index" => "refno", "decimal" => 0],
                    (object)["text" => "Customer Name", "index" => "achfullname"],
                    (object)["text" => "Customer Code", "index" => "achcode"],
                    (object)["text" => "Customer NRIC", "index" => "achmykadno"],
                    (object)["text" => "Customer Phone", "index" => "achphoneno"],
                    (object)["text" => "Customer Email", "index" => "achemail"],
                    (object)["text" => "Customer Type", "index" => "achtype"],
                    (object)["text" => "Booking On", "index" => "ordbookingon"],
                    (object)["text" => "Order No", "index" => "ordorderno"],
                    (object)["text" => "Order Price", "index" => "ordprice", "decimal" => 2],
                    (object)["text" => "Xau Weight (g)", "index" => "ordxau", "decimal" => 3],
                    (object)["text" => "Total Amount (RM)", "index" => "ordamount", "decimal" => 2],
                    (object)["text" => "Incoming/ Outgoing Payment (RM)", "index" => "dbmpdtverifiedamount", "decimal" => 2],
                    (object)["text" => "Product", "index" => "ordproductname"],
                    (object)["text" => "Ace Buy/Sell", "index" => "ordtype"],
                    (object)["text" => "Status", "index" => "status"],
                    (object)["text" => "Settlement Method", "index" => "settlementmethod"],
                    (object)["text" => "Bank Name", "index" => "dbmbankname"],
                    (object)["text" => "Account Name", "index" => "dbmaccountname"],
                    (object)["text" => "Account No", "index" => "dbmaccountnumber"],
                    (object)["text" => "Transaction Fee (RM)", "index" => "ordfee", "decimal" => 2],
                    (object)["text" => "FPX Cost (RM)", "index" => "fpxcost", "decimal" => 3],
                    (object)["text" => "FPX Net Amount (RM)", "index" => "fpxnetamount", "decimal" => 3],
                    (object)["text" => "Gateway Ref No", "index" => "dbmpdtgatewayrefno"],
                    (object)["text" => "Ref No", "index" => "dbmpdtreferenceno"],
                    (object)["text" => "Transaction Date", "index" => "dbmpdtrequestedon"],
                    (object)["text" => "Bank Ref No", "index" => "dbmbankrefno"],
                    (object)["text" => "Completed On", "index" => "completedon"],
                    (object)["text" => "Ace Bank Code", "index" => "dbmacebankcode"],
                    (object)["text" => "Campaign Code", "index" => "campaigncode"]
                ];

                foreach ($header as $x => $headerColumn) {
                    $original = $headerColumn->index;
                    if ('status' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}status` = 0 THEN 'Pending Payment'
                            WHEN `{$prefix}status` = 1 THEN 'Confirmed'
                            WHEN `{$prefix}status` = 2 THEN 'Paid'
                            WHEN `{$prefix}status` = 3 THEN 'Failed'
                            WHEN `{$prefix}status` = 4 THEN 'Reversed' END as `{$prefix}status`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('achtype' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}achtype` = 0 THEN 'Basic'
                            WHEN `{$prefix}achtype` = 1 THEN 'Premium' END as `{$prefix}achtype`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('fpxnetamount' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "IF(`{$prefix}settlementmethod` = 'FPX' AND `{$prefix}ordfee` != 0, GREATEST(`{$prefix}ordfee` - {$fpxCharge},0), 0.000) as `{$prefix}fpxnetamount`"
                            //"GREATEST(`{$prefix}ordfee` - {$fpxCharge},0) as `{$prefix}fpxnetamount`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('fpxcost' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "IF(`{$prefix}settlementmethod` = 'FPX' AND `{$prefix}ordfee` != 0, {$fpxCharge}, 0.000) as `{$prefix}fpxcost`"
                            //"{$fpxCharge} as `{$prefix}fpxcost`"
                        );
                        $header[$x]->index->original = $original;
                    }
                }
            } else if ($currentStore == 'myaccountholder') {
                $partneridColName = 'partnerid';
                $currentStore  = $this->app->myaccountholderStore();
                $prefix = $currentStore->getColumnPrefix();
                $header = [
                    (object)["text" => "Partner Name", "index" => "partnername"],
                    (object)["text" => "Customer Name", "index" => "fullname"],
                    (object)["text" => "Customer Code", "index" => "accountholdercode"],
                    (object)["text" => "Customer NRIC", "index" => "mykadno"],
                    (object)["text" => "Customer Phone", "index" => "phoneno"],
                    (object)["text" => "Customer Email", "index" => "email"],
                    (object)["text" => "Campaign Code", "index" => "campaigncode"],
                    (object)["text" => "Customer Type", "index" => "type"],
                    (object)["text" => "Address Line 1", "index" => "addressline1"],
                    (object)["text" => "Address Line 2", "index" => "addressline2"],
                    (object)["text" => "Postcode", "index" => "addresspostcode"],
                    (object)["text" => "City", "index" => "addresscity"],
                    (object)["text" => "State", "index" => "addressstate"],
                    (object)["text" => "Status", "index" => "status"],
                    (object)["text" => "Created On", "index" => "createdon"]
                ];

                foreach ($header as $x => $headerColumn) {
                    $original = $headerColumn->index;
                    if ('status' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive'
                             WHEN `{$prefix}status` = " . 1 . " THEN 'Active'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_SUSPENDED . " THEN 'Suspended'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_BLACKLISTED . " THEN 'Blacklisted'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_CLOSED . " THEN 'Closed' END as `{$prefix}status`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('type' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}type` = 0 THEN 'Basic'
                            WHEN `{$prefix}type` = 1 THEN 'Premium' END as `{$prefix}type`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('ispep' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}ispep` = " . 0 . " THEN 'No'
                             WHEN `{$prefix}ispep` = " . 1 . " THEN 'Yes' END as `{$prefix}ispep`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('pepstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}ispep` <> " . MyAccountHolder::PEP_FLAG . " THEN 'N/A'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed'" .
                                "ELSE 'Pending' END as `{$prefix}pepstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('kycstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed' END as `{$prefix}kycstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('amlastatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PENDING . " THEN 'Pending'
                             WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PASSED . " THEN 'Passed'
                             WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_FAILED . " THEN 'Failed' END as `{$prefix}amlastatus`"
                        );
                        $header[$x]->index->original = $original;
                    }
                }
            } else if ($currentStore == 'myconversion') {
                $partneridColName = 'rdmpartnerid';
                $currentStore  = $this->app->myconversionStore();
                $prefix = $currentStore->getColumnPrefix();
                $header = [
                    (object)["text" => "Redemption Id", "index" => "redemptionid"],
                    (object)["text" => "Customer Name", "index" => "accountholdername"],
                    (object)["text" => "Customer Code", "index" => "accountholdercode"],
                    (object)["text" => "Conversion Status", "index" => "status"],
                    (object)["text" => "Redemption Status", "index" => "rdmstatus"],
                    (object)["text" => "Logistic Fee Payment", "index" => "logisticfeepaymentmode"],
                    (object)["text" => "Campaign Code", "index" => "campaigncode"],
                    (object)["text" => "Customer Type", "index" => "accounttype"],
                    (object)["text" => "Redemption No", "index" => "rdmredemptionno"],
                    (object)["text" => "Address", "index" => "rdmdeliveryaddress"],
                    (object)["text" => "Postcode", "index" => "rdmdeliverypostcode"],
                    (object)["text" => "City", "index" => "rdmdeliverystate"],
                    (object)["text" => "State", "index" => "rdmdeliverycountry"],
                    (object)["text" => "Delivery Contact Name 1", "index" => "rdmdeliverycontactname1"],
                    (object)["text" => "Delivery Contact No 1", "index" => "rdmdeliverycontactno1"],
                    (object)["text" => "Delivery Contact Name 2", "index" => "rdmdeliverycontactname2"],
                    (object)["text" => "Delivery Contact No 2", "index" => "rdmdeliverycontactno2"],
                    (object)["text" => "Created On", "index" => "createdon"]
                ];

                foreach ($header as $x => $headerColumn) {
                    $original = $headerColumn->index;
                    if ('status' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}status` = 0 THEN 'Pending Payment'
                            WHEN `{$prefix}status` = 1 THEN 'Paid'
                            WHEN `{$prefix}status` = 2 THEN 'Expired'
                            WHEN `{$prefix}status` = 3 THEN 'Payment Cancelled'
                            WHEN `{$prefix}status` = 4 THEN 'Reversed' END as `{$prefix}status`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('rdmstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}rdmstatus` = 0 THEN 'Pending'
                            WHEN `{$prefix}rdmstatus` = 1 THEN 'Confirmed'
                            WHEN `{$prefix}rdmstatus` = 2 THEN 'Completed'
                            WHEN `{$prefix}rdmstatus` = 3 THEN 'Failed'
                            WHEN `{$prefix}rdmstatus` = 4 THEN 'Process Delivery'
                            WHEN `{$prefix}rdmstatus` = 5 THEN 'Cancelled'
                            WHEN `{$prefix}rdmstatus` = 6 THEN 'Reversed'
                            WHEN `{$prefix}rdmstatus` = 7 THEN 'Failed Delivery' END as `{$prefix}rdmstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('accounttype' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}accounttype` = 0 THEN 'Basic'
                            WHEN `{$prefix}accounttype` = 1 THEN 'Premium' END as `{$prefix}accounttype`"
                        );
                        $header[$x]->index->original = $original;
                    }
                }
            }

            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL)
                ->where($partneridColName, $partner->id)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate);

            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }

            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');

            $columns = [];
            $totalcolumn = count($headerText);
            $checker = $totalcolumn + 1;

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            // excel row start at 2nd AS ROW2, 1st for header
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                //'totalstart' => $totalrow + 2,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                if ($column['decimal'] == 2) {
                    $decimal_format = '0.00';
                }
                if ($column['decimal'] == 3) {
                    $decimal_format = '0.000';
                }
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            foreach ($columns as $column) {

                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match
                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $startDate; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $endDate; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $subject        = $partner->name . "_" . strtoupper($modulename) . "_" . $reportdate;
            $bodyEmail      = "Please find the attached file " . strtoupper($modulename) . " for your reference.";

            $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow = $datenow->format('Y-m-d_H-i-s');
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $filename = $partner->name . '_' . $reportdate . '_' . strtoupper($modulename) . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save($pathToSave);
            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }
    public function
    generatePartnersTransactionReportByAutotxt($partner, $currentStore, $dateStart, $dateEnd, $modulename, $reportpath, $reportname, $emailOut = false, $emailList)
    {
        // echo $reportpath;

        try {
            $fpxCharge          = $this->app->getConfig()->{'mygtp.m1.charge'};
            $bmmbpartnerid      = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $redonepartnerid    = $this->app->getConfig()->{'gtp.redone.partner.id'};
            $mergepartner       = $this->app->getConfig()->{'mygtp.mainpartner.merge'}; //check if need to merge partner that have multiple subpartner.currently ktp

            $headerText = [];
            $headerSQL = [];

            /****/
            $dateStart      = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd        = new \DateTime($dateEnd, $this->app->getUserTimezone());
            //$dateStart      = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt        = new \DateTime($dateStart->format('Y-m-d H:i:s'));
            $saveStartAt    = $startAt;
            $startAt        = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt          = new \DateTime($dateEnd->format('Y-m-d H:i:s'));
            $saveEndAt      = $endAt;
            $endAt          = \Snap\common::convertUserDatetimeToUTC($endAt);

            $continueFunct = true;

            if ($currentStore == 'mygoldtransaction') {
                $partneridColName = 'ordpartnerid';
                //continue generate report at different function

                $this->generateLatestDailyTransactionMyGtptxt($partner, $startAt, $endAt, $reportpath, $reportname, $emailOut, $emailList, $modulename, $mergepartner);
                $continueFunct = false;
            }

            if ($continueFunct) {
                $specialRenderer = [
                    'decode' => 'json',
                    'sqlfield' => 'rdmitems',
                    'displayfield' => ['serialnumber', 'code']
                ];

                foreach ($header as $headerColumn) {
                    array_push($headerText, $headerColumn->text);
                    array_push($headerSQL, $headerColumn->index);
                }

                if ($modulename == 'dailylogin') $columnUse = 'lastloginon';
                else $columnUse = 'createdon';

                if ($mergepartner) {
                    $partnerListArr = [];
                    $partnerList = $this->app->partnerStore()->searchView()->select()
                        ->where('group', $partner->id)
                        ->andWhere('status', Partner::STATUS_ACTIVE)
                        ->execute();
                    if (count($partnerList) > 0) {
                        foreach ($partnerList as $aPartnerList) {
                            $partnerListArr[] = $aPartnerList->id;
                        }
                    }

                    $partnerToList = $partnerListArr;
                } else $partnerToList = array($partner->id);



                $query = $currentStore->searchView()->select($headerSQL)
                    ->where($partneridColName, 'IN', $partnerToList)
                    ->andWhere($columnUse, '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere($columnUse, '<=', $endAt->format('Y-m-d H:i:s'))
                    ->orderBy('id');

                // echo $query;
                // die();

                $queryData = $query->execute();

                foreach ($headerSQL as $key => $val) {
                    if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                        $headerSQL[$key] = $val->original;
                    }
                }

                $headerString = $this->createHeader($headerText);
                $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
                $excelpages = $headerString . $contentString;

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
                $spreadsheet = $reader->loadFromString($excelpages);
                $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');

                $columns = [];
                $totalcolumn = count($headerText);
                $checker = $totalcolumn + 1;

                foreach ($header as $x => $headerColumn) {
                    if ($headerColumn->decimal) {
                        $column = $x; // 0 = A, 1 = B;
                        $column_decimal = $headerColumn->decimal;
                        array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                    }
                }
                $rows = [];
                $totalrow = $query->count();
                // excel row start at 2nd AS ROW2, 1st for header
                $rows = [
                    'header' => 1,
                    'start' => 2,
                    'end' => $totalrow + 3,
                    //'totalstart' => $totalrow + 2,
                    'total' => $totalrow + 5,
                ];

                // formating A1:A100;
                $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
                foreach ($columns as $column) {
                    $column_alphabet = $alphabet[$column['column']];

                    $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                    if ($column['decimal'] == 2) {
                        $decimal_format = '0.00';
                    }
                    if ($column['decimal'] == 3) {
                        $decimal_format = '0.000';
                    }
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                foreach ($columns as $column) {

                    $column_alphabet = $alphabet[$column['column']];

                    $style = $column_alphabet . $rows['total'];

                    $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                    $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                    // Compare and check if prices match
                    // Set sum for prices
                    // Set Sum totals
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
                }

                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

                if ($writer) {
                    // save log
                    $table = $currentStore->getTableName();
                    $select = json_encode($headerSQL);
                    $selectDateStart = $startAt->format('Y-m-d H:i:s'); // USER TIME, when in DB WILL BE UTC
                    $selectDateEnd = $endAt->format('Y-m-d H:i:s'); // USER TIME, when in DB WILL BE UTC
                    $conditions = json_encode($conditions);
                    $outputCount = $query->count('id');
                }

                $subject        = $reportname;
                $bodyEmail      = "Please find the attached file " . strtoupper($modulename) . " for your reference.";

                $filename = $reportname . '.xlsx';
                $pathToSave = $reportpath . $filename;

                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $writer->save($pathToSave);
                //  if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generatePartnersTransactionReportByAuto($partner, $currentStore, $dateStart, $dateEnd, $modulename, $reportpath, $reportname, $emailOut = false, $emailList)
    {
        try {
            $fpxCharge          = $this->app->getConfig()->{'mygtp.m1.charge'};
            $bmmbpartnerid      = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $redonepartnerid    = $this->app->getConfig()->{'gtp.redone.partner.id'};
            $mergepartner       = $this->app->getConfig()->{'mygtp.mainpartner.merge'}; //check if need to merge partner that have multiple subpartner.currently ktp

            $headerText = [];
            $headerSQL = [];

            /****/
            $dateStart      = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd        = new \DateTime($dateEnd, $this->app->getUserTimezone());
            //$dateStart      = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt        = new \DateTime($dateStart->format('Y-m-d H:i:s'));
            $saveStartAt    = $startAt;
            $startAt        = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt          = new \DateTime($dateEnd->format('Y-m-d H:i:s'));
            $saveEndAt      = $endAt;
            $endAt          = \Snap\common::convertUserDatetimeToUTC($endAt);

            $continueFunct = true;

            if ($currentStore == 'mygoldtransaction') {
                $partneridColName = 'ordpartnerid';
                //continue generate report at different function
                $this->generateLatestDailyTransactionMyGtp($partner, $startAt, $endAt, $reportpath, $reportname, $emailOut, $emailList, $modulename, $mergepartner);
                $continueFunct = false;
            } else if ($currentStore == 'myaccountholder') {
                $partneridColName = 'partnerid';
                $currentStore  = $this->app->myaccountholderStore();
                $prefix = $currentStore->getColumnPrefix();
                $header = [
                    (object)["text" => "Partner Name", "index" => "partnername"],
                    (object)["text" => "Customer Name", "index" => "fullname"],
                    (object)["text" => "Customer Code", "index" => "accountholdercode"],
                    (object)["text" => "Customer NRIC", "index" => "mykadno"],
                    (object)["text" => "Customer Phone", "index" => "phoneno"],
                    (object)["text" => "Customer Email", "index" => "email"],
                    (object)["text" => "Campaign Code", "index" => "campaigncode"],
                    (object)["text" => "Customer Type", "index" => "type"],
                    (object)["text" => "Address Line 1", "index" => "addressline1"],
                    (object)["text" => "Address Line 2", "index" => "addressline2"],
                    (object)["text" => "Postcode", "index" => "addresspostcode"],
                    (object)["text" => "City", "index" => "addresscity"],
                    (object)["text" => "State", "index" => "addressstate"],
                    (object)["text" => "Status", "index" => "status"],
                    (object)["text" => "Last Login On", "index" => "lastloginon"],
                    (object)["text" => "Created On", "index" => "createdon"]
                ];

                if ($bmmbpartnerid == $partner->id) {
                    //array_push($header,(object)["text"=>"Is Pep","index"=>"ispep"],(object)["text"=>"PEP Status","index"=>"pepstatus"],(object)["text"=>"KYC Status","index"=>"kycstatus"],(object)["text"=>"AMLA Status","index"=>"amlastatus"]);
                    array_push($header, (object)["text" => "Is Pep", "index" => "ispep"]);
                    array_push($header, (object)["text" => "PEP Status", "index" => "pepstatus"]);
                    array_push($header, (object)["text" => "KYC Status", "index" => "kycstatus"]);
                    array_push($header, (object)["text" => "AMLA Status", "index" => "amlastatus"]);
                    array_push($header, (object)["text" => "Referral Branch", "index" => "referralbranchcode"]);
                    array_push($header, (object)["text" => "Salespersonnel ID", "index" => "referralsalespersoncode"]);
                }

                if ($redonepartnerid && $redonepartnerid == $partner->id) {
                    array_push($header, (object)["text" => "Partner Customer Id", "index" => "partnercusid"]);
                }

                if ($mergepartner) {
                    array_push($header, (object)["text" => "Partner Name", "index" => "partnername"]);
                }

                foreach ($header as $x => $headerColumn) {
                    $original = $headerColumn->index;
                    if ('status' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive'
                             WHEN `{$prefix}status` = " . 1 . " THEN 'Active'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_SUSPENDED . " THEN 'Suspended'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_BLACKLISTED . " THEN 'Blacklisted'
                             WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_CLOSED . " THEN 'Closed' END as `{$prefix}status`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('type' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}type` = 0 THEN 'Basic'
                            WHEN `{$prefix}type` = 1 THEN 'Premium' END as `{$prefix}type`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('ispep' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}ispep` = " . 0 . " THEN 'No'
                             WHEN `{$prefix}ispep` = " . 1 . " THEN 'Yes' END as `{$prefix}ispep`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('pepstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}ispep` <> " . MyAccountHolder::PEP_FLAG . " THEN 'N/A'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'" .
                                "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed'" .
                                "ELSE 'Pending' END as `{$prefix}pepstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('kycstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'
                             WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed' END as `{$prefix}kycstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('amlastatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PENDING . " THEN 'Pending'
                             WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PASSED . " THEN 'Passed'
                             WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_FAILED . " THEN 'Failed' END as `{$prefix}amlastatus`"
                        );
                        $header[$x]->index->original = $original;
                    }
                }
            } else if ($currentStore == 'myconversion') {
                $partneridColName = 'rdmpartnerid';
                $currentStore  = $this->app->myconversionStore();
                $prefix = $currentStore->getColumnPrefix();
                $header = [
                    (object)["text" => "Date", "index" => "createdon"],
                    (object)["text" => "Customer Code", "index" => "accountholdercode"],
                    (object)["text" => "Customer Name", "index" => "accountholdername"],
                    (object)["text" => "Conversion Type", "index" => "rdmtype"],
                    (object)["text" => "Serial Number / Item Code", "index" => "rdmitems"],
                    //(object)["text"=>"Item Code","index"=>""],
                    (object)["text" => "Total Weight", "index" => "rdmtotalweight", "decimal" => 3],
                    (object)["text" => "Total Quantity", "index" => "rdmtotalquantity", "decimal" => 3],
                    (object)["text" => "Premium Fee", "index" => "premiumfee", "decimal" => 2],
                    (object)["text" => "Insurance Fee", "index" => "rdminsurancefee", "decimal" => 2],
                    (object)["text" => "Handling Fee", "index" => "handlingfee", "decimal" => 2],
                    (object)["text" => "Conversion Fee", "index" => "rdmredemptionfee", "decimal" => 2],
                    (object)["text" => "Delivery Address", "index" => "rdmdeliveryaddress"],
                    (object)["text" => "Delivery Postcode", "index" => "rdmdeliverypostcode"],
                    (object)["text" => "Delivery State", "index" => "rdmdeliverystate"],
                    (object)["text" => "Delivery State", "index" => "rdmdeliverystate"],
                    (object)["text" => "Delivery Country", "index" => "rdmdeliverycountry"],
                    (object)["text" => "Delivery Contact Name", "index" => "rdmdeliverycontactname1"],
                    (object)["text" => "Delivery Contact No", "index" => "rdmdeliverycontactno1"],
                    (object)["text" => "Payment Status", "index" => "pdtstatus"],
                    (object)["text" => "Conversion Status", "index" => "status"],
                ];

                foreach ($header as $x => $headerColumn) {
                    $original = $headerColumn->index;
                    if ('status' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}status` = 0 THEN 'Pending Payment'
                            WHEN `{$prefix}status` = 1 THEN 'Paid'
                            WHEN `{$prefix}status` = 2 THEN 'Expired'
                            WHEN `{$prefix}status` = 3 THEN 'Payment Cancelled'
                            WHEN `{$prefix}status` = 4 THEN 'Reversed' END as `{$prefix}status`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('rdmstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}rdmstatus` = 0 THEN 'Pending'
                            WHEN `{$prefix}rdmstatus` = 1 THEN 'Confirmed'
                            WHEN `{$prefix}rdmstatus` = 2 THEN 'Completed'
                            WHEN `{$prefix}rdmstatus` = 3 THEN 'Failed'
                            WHEN `{$prefix}rdmstatus` = 4 THEN 'Process Delivery'
                            WHEN `{$prefix}rdmstatus` = 5 THEN 'Cancelled'
                            WHEN `{$prefix}rdmstatus` = 6 THEN 'Reversed'
                            WHEN `{$prefix}rdmstatus` = 7 THEN 'Failed Delivery' END as `{$prefix}rdmstatus`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('accounttype' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}accounttype` = 0 THEN 'Basic'
                            WHEN `{$prefix}accounttype` = 1 THEN 'Premium' END as `{$prefix}accounttype`"
                        );
                        $header[$x]->index->original = $original;
                    } elseif ('pdtstatus' === $headerColumn->index) {
                        $header[$x]->index = $currentStore->searchTable(false)->raw(
                            "CASE WHEN `{$prefix}pdtstatus` = 0 THEN 'Pending'
                            WHEN `{$prefix}pdtstatus` = 1 THEN 'Success'
                            WHEN `{$prefix}pdtstatus` = 2 THEN 'Pending payment'
                            WHEN `{$prefix}pdtstatus` = 3 THEN 'Cancelled'
                            WHEN `{$prefix}pdtstatus` = 4 THEN 'Failed'
                            WHEN `{$prefix}pdtstatus` = 5 THEN 'Refunded' END as `{$prefix}pdtstatus`"
                        );
                        $header[$x]->index->original = $original;
                    }
                }
            }

            if ($continueFunct) {
                $specialRenderer = [
                    'decode' => 'json',
                    'sqlfield' => 'rdmitems',
                    'displayfield' => ['serialnumber', 'code']
                ];

                foreach ($header as $headerColumn) {
                    array_push($headerText, $headerColumn->text);
                    array_push($headerSQL, $headerColumn->index);
                }

                if ($modulename == 'dailylogin') $columnUse = 'lastloginon';
                else $columnUse = 'createdon';

                if ($mergepartner) {
                    $partnerListArr = [];
                    $partnerList = $this->app->partnerStore()->searchView()->select()
                        ->where('group', $partner->id)
                        ->andWhere('status', Partner::STATUS_ACTIVE)
                        ->execute();
                    if (count($partnerList) > 0) {
                        foreach ($partnerList as $aPartnerList) {
                            $partnerListArr[] = $aPartnerList->id;
                        }
                    }

                    $partnerToList = $partnerListArr;
                } else $partnerToList = array($partner->id);

                $query = $currentStore->searchView()->select($headerSQL)
                    ->where($partneridColName, 'IN', $partnerToList)
                    ->andWhere($columnUse, '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere($columnUse, '<=', $endAt->format('Y-m-d H:i:s'))
                    ->orderBy('id');

                $queryData = $query->execute();

                foreach ($headerSQL as $key => $val) {
                    if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                        $headerSQL[$key] = $val->original;
                    }
                }

                $headerString = $this->createHeader($headerText);
                $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
                $excelpages = $headerString . $contentString;

                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
                $spreadsheet = $reader->loadFromString($excelpages);
                $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(25);
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');

                $columns = [];
                $totalcolumn = count($headerText);
                $checker = $totalcolumn + 1;

                foreach ($header as $x => $headerColumn) {
                    if ($headerColumn->decimal) {
                        $column = $x; // 0 = A, 1 = B;
                        $column_decimal = $headerColumn->decimal;
                        array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                    }
                }
                $rows = [];
                $totalrow = $query->count();
                // excel row start at 2nd AS ROW2, 1st for header
                $rows = [
                    'header' => 1,
                    'start' => 2,
                    'end' => $totalrow + 3,
                    //'totalstart' => $totalrow + 2,
                    'total' => $totalrow + 5,
                ];

                // formating A1:A100;
                $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
                foreach ($columns as $column) {
                    $column_alphabet = $alphabet[$column['column']];

                    $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                    if ($column['decimal'] == 2) {
                        $decimal_format = '0.00';
                    }
                    if ($column['decimal'] == 3) {
                        $decimal_format = '0.000';
                    }
                    $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
                }

                foreach ($columns as $column) {

                    $column_alphabet = $alphabet[$column['column']];

                    $style = $column_alphabet . $rows['total'];

                    $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                    $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                    $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                    // Compare and check if prices match
                    // Set sum for prices
                    // Set Sum totals
                    $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
                }

                $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

                if ($writer) {
                    // save log
                    $table = $currentStore->getTableName();
                    $select = json_encode($headerSQL);
                    $selectDateStart = $startAt->format('Y-m-d H:i:s'); // USER TIME, when in DB WILL BE UTC
                    $selectDateEnd = $endAt->format('Y-m-d H:i:s'); // USER TIME, when in DB WILL BE UTC
                    $conditions = json_encode($conditions);
                    $outputCount = $query->count('id');
                }

                $subject        = $reportname;
                $bodyEmail      = "Please find the attached file " . strtoupper($modulename) . " for your reference.";

                $filename = $reportname . '.xlsx';
                $pathToSave = $reportpath . $filename;

                // ob_end_clean();
                header('Content-Type: application/vnd.ms-excel');
                // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="' . $filename . '"');

                $writer->save($pathToSave);
                if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }
    public function generateLatestDailyTransactionMyGtptxt($partner, $startAt, $endAt, $reportpath, $reportname, $emailOut, $emailList, $modulename, $mergepartner)
    {



        try {
            $headerText = [];
            // Date timestamp for header
            $datenow    = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow    = $datenow->format('Y-m-d H:i:s');
            $fpxCharge    = $this->app->getConfig()->{'mygtp.m1.charge'};

            /*mergepartner*/

            if ($mergepartner) {
                $partnerListArr = [];
                $partnerList = $this->app->partnerStore()->searchView()->select()
                    ->where('group', $partner)
                    ->andWhere('status', Partner::STATUS_ACTIVE)
                    ->execute();
                if (count($partnerList) > 0) {
                    foreach ($partnerList as $aPartnerList) {
                        $partnerListArr[] = $aPartnerList->id;
                    }
                }
                $partnerToList = $partnerListArr;
            } else $partnerToList = array($partner);


            $orderStore        = $this->app->mygoldtransactionStore();
            $orderTransactions = $orderStore->searchView()->select()
                ->where('gtr_ordpartnerid', 'IN', $partnerToList)
                ->andWhere('gtr_createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('gtr_createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                ->groupBy('gtr_id')
                ->orderBy('gtr_id')
                ->execute();





            if (count($orderTransactions) > 0) {
                $filenamex = $reportname . '.txt';
                $pathToSave = $reportpath;
                $filename = $pathToSave . $filenamex;
                $content = "DAILYTRN \n";
                $file = fopen($filename, "w");
                foreach ($orderTransactions as $anOrder) {
                    /*statusname*/
                    if ($anOrder->ordtype == "CompanySell") $statusname = 'SELL';
                    elseif ($anOrder->ordtype == "CompanyBuy") $statusname = 'Buy';
                    else $statusname = '';
                    $content .=  $anOrder->refno . "\t" . $statusname . "\t" .  $anOrder->ordorderno . "\t" . $anOrder->ordpartnerid . "\t" . $anOrder->ordproductname;
                    $content .= "\n";
                }
            }

            // Write the content to the file
            fwrite(
                $file,
                $content
            );
            echo "succesfully" .  $pathToSave;
            fclose($file);
            // $subject        = $reportname;
            // $bodyEmail      = "Please find the attached file " . strtoupper($modulename) . " for your reference.";
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
        // if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);


    }

    public function generateLatestDailyTransactionMyGtp($partner, $startAt, $endAt, $reportpath, $reportname, $emailOut, $emailList, $modulename, $mergepartner)
    {

        try {
            $headerText = [];
            // Date timestamp for header
            $datenow    = \Snap\common::convertUTCToUserDatetime(new \DateTime());
            $datenow    = $datenow->format('Y-m-d H:i:s');
            $fpxCharge    = $this->app->getConfig()->{'mygtp.m1.charge'};

            /*mergepartner*/

            if ($mergepartner) {
                $partnerListArr = [];
                $partnerList = $this->app->partnerStore()->searchView()->select()
                    ->where('group', $partner->id)
                    ->andWhere('status', Partner::STATUS_ACTIVE)
                    ->execute();
                if (count($partnerList) > 0) {
                    foreach ($partnerList as $aPartnerList) {
                        $partnerListArr[] = $aPartnerList->id;
                    }
                }

                $partnerToList = $partnerListArr;
            } else $partnerToList = array($partner->id);

            /*get order transactions*/

            $orderStore        = $this->app->mygoldtransactionStore();
            $orderTransactions = $orderStore->searchView()->select()
                //->where('gtr_id', '=', 3)
                ->where('ordpartnerid', 'IN', $partnerToList)
                ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                ->groupBy('id')
                ->orderBy('id')
                ->execute();
            if (count($orderTransactions) > 0) {
                foreach ($orderTransactions as $anOrder) {
                    /*statusname*/

                    if ($anOrder->ordstatus == Order::STATUS_PENDING) $statusname = 'PENDING';
                    elseif ($anOrder->ordstatus == Order::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                    elseif ($anOrder->ordstatus == Order::STATUS_PENDINGPAYMENT) $statusname = 'PENDING PAYMENT';
                    elseif ($anOrder->ordstatus == Order::STATUS_PENDINGCANCEL) $statusname = 'PENDING CANCEL';
                    elseif ($anOrder->ordstatus == Order::STATUS_CANCELLED) $statusname = 'CANCELLED';
                    elseif ($anOrder->ordstatus == Order::STATUS_COMPLETED) $statusname = 'COMPLETED';
                    elseif ($anOrder->ordstatus == Order::STATUS_EXPIRED) $statusname = 'EXPIRED';

                    /*fpxnetamount*/
                    if ($anOrder->settlementmethod == 'FPX' and $anOrder->ordfee != 0) {
                        $fpxnetamount = $anOrder->ordfee - $fpxCharge;


                        if ($fpxnetamount < 0) $fpxnetamount = 0.00;
                        $fpxcost = $fpxCharge;
                    } else {
                        $fpxnetamount = 0.00;
                        $fpxcost = 0.00;
                    }

                    $fullList[] = [
                        "A"  => ["value" => $statusname, "type" => "string"],
                        "B"  => ["value" => $anOrder->createdon->format('Y-m-d H:i:s'), "type" => "date"],
                        "C"  => ["value" => $anOrder->ordtype, "type" => "string"],
                        "D"  => ["value" => $anOrder->ordpartnername, "type" => "string"],
                        "E"  => ["value" => $anOrder->achcode, "type" => "string"],
                        "F"  => ["value" => $anOrder->achfullname, "type" => "string"],
                        "G"  => ["value" => $anOrder->achmykadno, "type" => "number"],
                        "H"  => ["value" => $anOrder->achemail, "type" => "string"],
                        "I"  => ["value" => $anOrder->achphoneno, "type" => "string"],
                        "J"  => ["value" => $anOrder->ordorderno, "type" => "string"],
                        "K"  => ["value" => $anOrder->ordremarks, "type" => "string"],
                        "L"  => ["value" => $anOrder->ordxau, "type" => "number", "decimal" => '0.000'],
                        "M"  => ["value" => $anOrder->ordprice, "type" => "number", "decimal" => '0.00'],
                        "N"  => ["value" => $anOrder->ordamount, "type" => "number", "decimal" => '0.00'],
                        "O"  => ["value" => $fpxnetamount, "type" => "number", "decimal" => '0.00'],
                        "P"  => ["value" => $fpxcost, "type" => "number", "decimal" => '0.00'],
                        "Q"  => ["value" => $anOrder->ordfee, "type" => "number", "decimal" => '0.00'],
                        "R"  => ["value" => $anOrder->dbmpdtverifiedamount, "type" => "number", "decimal" => '0.00'],
                        "S"  => ["value" => $anOrder->settlementmethod, "type" => "string"],
                        "T"  => ["value" => $anOrder->refno, "type" => "string"],
                        "U"  => ["value" => $anOrder->dbmbankname, "type" => "string"],
                        "V"  => ["value" => $anOrder->dbmaccountname, "type" => "string"],
                        "W"  => ["value" => $anOrder->dbmaccountnumber, "type" => "number"],
                        "X"  => ["value" => $anOrder->salespersoncode, "type" => "string"],
                        "Y"  => ["value" => $anOrder->referralbranchcode, "type" => "string"],
                        "Z"  => ["value" => ""],
                        "AA"  => ["value" => ""],
                        "AB" => ["value" => ""],
                        "AC" => ["value" => ""],
                        "AD" => ["value" => ""],
                        "AE" => ["value" => ""],
                        "AF" => ["value" => ""],
                        "AG" => ["value" => ""],
                        "AH" => ["value" => ""],
                    ];
                }
            }
            /*end get order transactions*/

            /*get conversion transactions*/
            $myconversionStore  = $this->app->myconversionStore();
            $redemptionStore  = $this->app->redemptionStore();
            $accHolderStore  = $this->app->myaccountholderStore();
            $redeemTransactions = $myconversionStore->searchView()->select()
                ->where('rdmpartnerid', 'IN', $partnerToList)
                ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                ->orderBy('id')
                ->execute();
            if (count($redeemTransactions) > 0) {
                foreach ($redeemTransactions as $aRedemption) {
                    $productcodestring = "";
                    $serialnumberstring = "";
                    //get redemption obj
                    //$redemptionObj = $redemptionStore->getByField('partnerrefno', $aRedemption->refno);
                    $redemptionObj = $redemptionStore->searchView()->select()->where('partnerrefno', $aRedemption->refno)->one();
                    $itemsList = json_decode($redemptionObj->items, true);

                    if ($aRedemption->rdmstatus == Redemption::STATUS_PENDING) $statusname = 'PENDING';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_CONFIRMED) $statusname = 'CONFIRMED';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_COMPLETED) $statusname = 'COMPLETED';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_FAILED) $statusname = 'FAILED';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_PROCESSDELIVERY) $statusname = 'PROCESS DELIVERY';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_CANCELLED) $statusname = 'CANCELLED';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_REVERSED) $statusname = 'REVERSED';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_FAILEDDELIVERY) $statusname = 'FAILED DELIVERY';
                    elseif ($aRedemption->rdmstatus == Redemption::STATUS_SUCCESS) $statusname = 'SUCCESSFUL';

                    $itemsCount = count($itemsList);
                    $i = 1;
                    foreach ($itemsList as $aItems) {
                        if ($i != $itemsCount) $addNewLine = "\r";
                        $productcodestring .= $aItems['code'] . $addNewLine;
                        $serialnumberstring .= $aItems['serialnumber'] . $addNewLine;
                        $i++;
                    }

                    //get accholder
                    $accHolderObj = $accHolderStore->getByField('accountholdercode', $aRedemption->accountholdercode);

                    $fullList[] = [
                        "A"  => ["value" => $statusname, "type" => "string"],
                        "B"  => ["value" => $aRedemption->createdon->format('Y-m-d H:i:s'), "type" => "date"],
                        "C"  => ["value" => 'Redemption/Conversion'],
                        "D"  => ["value" => $redemptionObj->partnername, "type" => "string"],
                        "E"  => ["value" => $aRedemption->accountholdercode, "type" => "string"],
                        "F"  => ["value" => $aRedemption->accountholdername, "type" => "string"],
                        "G"  => ["value" => $accHolderObj->mykadno, "type" => "number"],
                        "H"  => ["value" => $accHolderObj->email, "type" => "string"],
                        "I"  => ["value" => $accHolderObj->phoneno, "type" => "string"],
                        "J"  => ["value" => $aRedemption->rdmredemptionno, "type" => "string"],
                        "K"  => ["value" => ''],
                        "L"  => ["value" => $aRedemption->rdmtotalweight, "type" => "number", "decimal" => '0.000'],
                        "M"  => ["value" => ''],
                        "N"  => ["value" => $aRedemption->pdtamount, "type" => "number", "decimal" => '0.00'],
                        "O"  => ["value" => ''],
                        "P"  => ["value" => ''],
                        "Q"  => ["value" => $aRedemption->pdtcustomerfee, "type" => "number", "decimal" => '0.00'],
                        "R"  => ["value" => $aRedemption->pdtamount, "type" => "number", "decimal" => '0.00'],
                        "S"  => ["value" => $aRedemption->logisticfeepaymentmode, "type" => "string"],
                        "T"  => ["value" => $aRedemption->refno, "type" => "string"],
                        "U"  => ["value" => ''],
                        "V"  => ["value" => ''],
                        "W"  => ["value" => ''],
                        "X"  => ["value" => ''],
                        "Y"  => ["value" => ''],
                        "Z"  => ["value" => $aRedemption->rdmtype, "type" => "string"],
                        "AA"  => ["value" => $productcodestring, "type" => "string"],
                        "AB" => ["value" => $serialnumberstring, "type" => "string"],
                        "AC" => ["value" => $aRedemption->rdmdeliverycontactname1, "type" => "string"],
                        "AD" => ["value" => $aRedemption->rdmdeliverycontactno1, "type" => "string"],
                        "AE" => ["value" => $aRedemption->rdmdeliveryaddress, "type" => "string"],
                        "AF" => ["value" => $aRedemption->rdmdeliverypostcode, "type" => "string"],
                        "AG" => ["value" => $aRedemption->rdmdeliverystate, "type" => "string"],
                        "AH" => ["value" => $aRedemption->rdmdeliverycountry, "type" => "string"],
                    ];
                }
            }
            /*end get conversion transactions*/

            $numberofColumns = 33;

            /*create header*/
            $header = [
                'A' => 'Status',
                'B' => 'Date',
                'C' => 'GTPType',
                'D' => 'Partner',
                'E' => 'CustomerCode',
                'F' => 'CustomerName',
                'G' => 'CustomerNRIC',
                'H' => 'CustomerEmail',
                'I' => 'CustomerPhone',
                'J' => 'OrderNo/ConversionNo',
                'K' => 'OrderReference',
                'L' => 'XauWeight(g)',
                'M' => 'OrderPrice(RM)',
                'N' => 'TotalAmount(RM)',
                'O' => 'FPXNetAmount(RM)',
                'P' => 'FPXCost(RM)',
                'Q' => 'TransactionFee(RM)',
                'R' => 'Incoming/OutgoingPayment(RM)',
                'S' => 'SettlementMethod',
                'T' => 'TransactionRefNo/PartnerRefNo',
                'U' => 'BankName',
                'V' => 'AccountName',
                'W' => 'AccountNo',
                'X' => 'SalesPersonCode',
                'Y' => 'ReferralBranchCode',
                'Z' => 'ConversionType',
                'AA' => 'ItemCode',
                'AB' => 'SerialNumber',
                'AC' => 'DeliveryContactName',
                'AD' => 'DeliveryContactNo',
                'AE' => 'DeliveryAddress',
                'AF' => 'DeliveryPostcode',
                'AG' => 'DeliveryState',
                'AH' => 'DeliveryCountry',
            ];

            $filename = $reportname . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // echo $pathToSave;
            // die();


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->mergeCells('A1:B1');
            $sheet->setCellValue('A1', 'Report generated as at ' . $datenow);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13.5);

            foreach ($header as $key => $value) {
                /*create header*/
                $sheet->setCellValue($key . '3', $value)->getColumnDimension($key)->setWidth(25);
                $sheet->getStyle($key . '3')->getFont()->setBold(true);
            }

            $i = 3;
            foreach ($fullList as $aList) {
                $i++;
                foreach ($aList as $key => $aValue) {
                    if ($aValue['type'] == 'number') {
                        if (isset($aValue['decimal'])) {
                            $space = "";
                            $sheet->getStyle($key . $i)->getNumberFormat()->setFormatCode($aValue['decimal']);
                        } else $space = " ";
                    }
                    $sheet->setCellValue($key . $i, $space . $aValue['value'])->getColumnDimension($key);
                    $sheet->getStyle($key . $i)->getAlignment()->setHorizontal('right');
                }
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }


        $writer = new Xlsx($spreadsheet);
        $writer->save($pathToSave);

        $subject        = $reportname;
        $bodyEmail      = "Please find the attached file " . strtoupper($modulename) . " for your reference.";

        if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
    }

    public function generateCommissionReportByAuto($partner, $currentdate, $dateStart, $dateEnd, $reportpath, $emailOut = false, $emailList, $reportname, $peak, $alignRight = true)
    {
        try {
            $partnerid                    = $partner->id;
            $partnerCompanySellCommission = 0;
            $partnerCompanyBuyCommission  = 0;
            $aceCompanySellCommission     = 0;
            $aceCompanyBuyCommission      = 0;
            $conditions                   = null;
            $partnerSettingStore          = $this->app->mypartnersettingStore();
            $currentStore                 = $this->app->mygoldtransactionStore();
            $companyBuyType               = 'CompanyBuy';
            $companySellType              = 'CompanySell';

            $settings   = $partnerSettingStore->getByField('partnerid', $partnerid);
            $partner    = $this->app->partnerStore()->getById($partnerid);
            $calc       = $partner->calculator();

            if (!$peak) {
                $partnerCompanySellCommission = $settings->dgpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings->dgpartnerbuycommission ?? 0;
            } else {
                $partnerCompanySellCommission = $settings->dgpeakpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings->dgpeakpartnerbuycommission ?? 0;
            }

            $aceCompanySellCommission     = $settings->dgacesellcommission ?? 0;
            $aceCompanyBuyCommission      = $settings->dgacebuycommission ?? 0;

            $header = [
                (object)["text" => "Booking On", "index" => "ordbookingon"],
                (object)["text" => "Customer Code", "index" => "achcode"],
                (object)["text" => "Customer Name", "index" => "achfullname"],
                (object)["text" => "Customer NRIC", "index" => "achmykadno"],
                (object)["text" => "Customer Email", "index" => "achemail"],
                (object)["text" => "Customer Phone", "index" => "achphoneno"],
                (object)["text" => "Transaction Ref No", "index" => "refno"],
                (object)["text" => "Order No", "index" => "ordorderno"],
                (object)["text" => "Xau Weight (g)", "index" => "ordxau", "decimal" => 3],
                (object)["text" => "Order Price", "index" => "ordprice", "decimal" => 2],
                (object)["text" => "Total Amount (RM)", "index" => "ordamount", "decimal" => 3],
                (object)["text" => "Ace Buy/Sell", "index" => "ordtype"],
                (object)["text" => "Status", "index" => "ordstatus"],
                (object)["text" => "Settlement Method", "index" => "settlementmethod"],
                (object)["text" => "Partner Per Gram (RM)", "index" => "partnercommissionpergram", "decimal" => 2],
                (object)["text" => "Partner Commission (RM)", "index" => "partnercommission", "decimal" => 2],
                (object)["text" => "Referral Branch", "index" => "referralbranchcode"],
                (object)["text" => "Salespersonnel ID", "index" => "salespersoncode"],
                /*(object)["text"=>"ACE Per Gram (RM)","index"=>"acecommissionpergram","decimal"=>2],
                (object)["text"=>"ACE Commission (RM)","index"=>"acecommission","decimal"=>2],  */
            ];

            $prefix = $currentStore->getColumnPrefix();
            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;
                if ('partnercommission' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                              THEN `{$prefix}ordxau` * {$partnerCompanyBuyCommission} 
                              WHEN `{$prefix}ordtype` = '{$companySellType}' 
                              THEN `{$prefix}ordxau` * {$partnerCompanySellCommission} END as `{$prefix}partnercommission`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('partnercommissionpergram' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                              THEN '{$partnerCompanyBuyCommission}' 
                              WHEN `{$prefix}ordtype` = '{$companySellType}' 
                              THEN '{$partnerCompanySellCommission}' END as `{$prefix}partnercommissionpergram`"
                    );

                    $header[$key]->index->original = $original;
                } elseif ('acecommission' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanyBuyCommission} 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanySellCommission} END as `{$prefix}acecommission`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('acecommissionpergram' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                              THEN '{$aceCompanyBuyCommission}' 
                              WHEN `{$prefix}ordtype` = '{$companySellType}' 
                              THEN '{$aceCompanySellCommission}' END as `{$prefix}acecommissionpergram`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('ordstatus' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordstatus` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
                         WHEN `{$prefix}ordstatus` = " . Order::STATUS_PENDING . " THEN 'Pending Payment'
                         WHEN `{$prefix}ordstatus` = " . Order::STATUS_COMPLETED . " THEN 'Completed' END as `{$prefix}ordstatus`"
                    );
                    $header[$key]->index->original = $original;
                }
            }

            if (0 < $partnerid) {
                $conditions = ['ordpartnerid' => $partner->id];
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            $from = $settings->dgpeakhourfrom;
            $from->setTimeZone($this->app->getServerTimeZone());
            $to = $settings->dgpeakhourto;
            $to->setTimeZone($this->app->getServerTimeZone());

            $conditions = function ($q) use ($startAt, $endAt, $partnerid, $from, $to, $peak) {
                $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('ordpartnerid', $partnerid);
                $q->where(function ($r) {
                    $r->where('ordstatus', Order::STATUS_COMPLETED);
                    $r->orWhere('ordstatus', Order::STATUS_CONFIRMED);
                    $r->orWhere(function ($s) {
                        $s->where('ordstatus', Order::STATUS_PENDING);
                        $s->andWhere('ordtype', Order::TYPE_COMPANYBUY);
                    });
                });

                if (!$peak) {
                    $q->where(function ($r) use ($from, $to) {
                        $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                        $r->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));
                    });
                } else {
                    $q->where(function ($r) use ($from, $to) {
                        $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                        $r->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));
                    });
                }
            };

            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL);

            if ($conditions) {
                $query->where($conditions);
            }
            $query->orderBy("id", "DESC");

            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            if ($alignRight) {
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // load formating decimal START
            $columns = [];

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];

                $decimal_format = '0.' . str_pad('', intval($column['decimal']), 0);
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            $filename = $reportname . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save($pathToSave);

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $startDate; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $endDate; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $subject        = $reportname;
            $bodyEmail      = "Please find the attached file " . $reportname . " for your reference.";

            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateCommissionReportLatest($partner, $currentdate, $dateStart, $dateEnd, $reportpath, $emailOut = false, $emailList, $reportname, $alignRight = true)
    {
        try {
            $partnerid                    = $partner->id;
            $partnerCompanySellCommission = 0;
            $partnerCompanyBuyCommission  = 0;
            $aceCompanySellCommission     = 0;
            $aceCompanyBuyCommission      = 0;
            $conditions                   = null;
            $partnerSettingStore          = $this->app->mypartnersettingStore();
            $currentStore                 = $this->app->mygoldtransactionStore();
            $companyBuyType               = 'CompanyBuy';
            $companySellType              = 'CompanySell';

            $settings   = $partnerSettingStore->getByField('partnerid', $partnerid);
            $partner    = $this->app->partnerStore()->getById($partnerid);
            $calc       = $partner->calculator();

            /*if(!$peak){
                $partnerCompanySellCommission = $settings->dgpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings->dgpartnerbuycommission ?? 0;
            } else {
                $partnerCompanySellCommission = $settings->dgpeakpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings->dgpeakpartnerbuycommission ?? 0;
            }*/

            $partnerCompanySellNonPeakCommission = $settings->dgpartnersellcommission ?? 0;
            $partnerCompanyBuyNonPeakCommission  = $settings->dgpartnerbuycommission ?? 0;
            $partnerCompanySellPeakCommission = $settings->dgpeakpartnersellcommission ?? 0;
            $partnerCompanyBuyPeakCommission  = $settings->dgpeakpartnerbuycommission ?? 0;

            $aceCompanySellCommission     = $settings->dgacesellcommission ?? 0;
            $aceCompanyBuyCommission      = $settings->dgacebuycommission ?? 0;

            $from = $settings->dgpeakhourfrom;
            $from->setTimeZone($this->app->getServerTimeZone());
            $to = $settings->dgpeakhourto;
            $to->setTimeZone($this->app->getServerTimeZone());

            $header = [
                (object)["text" => "Status", "index" => "ordstatus"],
                (object)["text" => "Date", "index" => "ordbookingon"],
                (object)["text" => "GTP Type", "index" => "ordtype"],
                (object)["text" => "Partner", "index" => "ordpartnername"],
                (object)["text" => "Referral Affiliate Name", "index" => "referralbranchname"],
                (object)["text" => "Customer Code", "index" => "achcode"],
                (object)["text" => "Customer Name", "index" => "achfullname"],
                (object)["text" => "Customer NRIC", "index" => "achmykadno"],
                (object)["text" => "Customer Email", "index" => "achemail"],
                (object)["text" => "Customer Phone", "index" => "achphoneno"],
                (object)["text" => "Order No", "index" => "ordorderno"],
                (object)["text" => "Xau Weight (g)", "index" => "ordxau", "decimal" => 3],
                (object)["text" => "Order Price", "index" => "ordprice", "decimal" => 2],
                (object)["text" => "Total Amount (RM)", "index" => "ordamount", "decimal" => 3],
                (object)["text" => "Settlement Method", "index" => "settlementmethod"],
                (object)["text" => "Transaction Ref No", "index" => "refno"],
                (object)["text" => "Commission type", "index" => "peakstatus"],
                (object)["text" => "Partner Per Gram (RM)", "index" => "partnercommissionpergram", "decimal" => 2],
                (object)["text" => "Partner Commission (RM)", "index" => "partnercommission", "decimal" => 2],
                (object)["text" => "Referral Branch", "index" => "referralbranchcode"],
                (object)["text" => "Salespersonnel ID", "index" => "salespersoncode"],
                //(object)["text"=>"ACE Per Gram (RM)","index"=>"acecommissionpergram","decimal"=>2],
                //(object)["text"=>"ACE Commission (RM)","index"=>"acecommission","decimal"=>2],                 
            ];

            $prefix = $currentStore->getColumnPrefix();
            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;
                if ('partnercommission' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' and (TIME(`{$prefix}createdon`) < '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) > '" . $to->format('H:i:s') . "')
                              THEN `{$prefix}ordxau` * {$partnerCompanyBuyNonPeakCommission} 
                              WHEN `{$prefix}ordtype` = '{$companyBuyType}' and (TIME(`{$prefix}createdon`) >= '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) <= '" . $to->format('H:i:s') . "')
                              THEN `{$prefix}ordxau` * {$partnerCompanyBuyPeakCommission}
                              WHEN `{$prefix}ordtype` = '{$companySellType}' and (TIME(`{$prefix}createdon`) < '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) > '" . $to->format('H:i:s') . "')
                              THEN `{$prefix}ordxau` * {$partnerCompanySellNonPeakCommission}
                              WHEN `{$prefix}ordtype` = '{$companySellType}' and (TIME(`{$prefix}createdon`) >= '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) <= '" . $to->format('H:i:s') . "')
                              THEN `{$prefix}ordxau` * {$partnerCompanySellPeakCommission}
                            END as `{$prefix}partnercommission`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('partnercommissionpergram' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' and (TIME(`{$prefix}createdon`) < '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) > '" . $to->format('H:i:s') . "')
                              THEN '{$partnerCompanyBuyNonPeakCommission}' 
                              WHEN `{$prefix}ordtype` = '{$companyBuyType}' and (TIME(`{$prefix}createdon`) >= '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) <= '" . $to->format('H:i:s') . "')
                              THEN '{$partnerCompanyBuyPeakCommission}' 
                              WHEN `{$prefix}ordtype` = '{$companySellType}' and (TIME(`{$prefix}createdon`) < '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) > '" . $to->format('H:i:s') . "') 
                              THEN '{$partnerCompanySellNonPeakCommission}'
                              WHEN `{$prefix}ordtype` = '{$companySellType}' and (TIME(`{$prefix}createdon`) >= '" . $from->format('H:i:s') . "' OR TIME(`{$prefix}createdon`) <= '" . $to->format('H:i:s') . "')
                              THEN '{$partnerCompanySellPeakCommission}'
                              END as `{$prefix}partnercommissionpergram`"
                    );

                    $header[$key]->index->original = $original;
                } elseif ('acecommission' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanyBuyCommission} 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanySellCommission} END as `{$prefix}acecommission`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('acecommissionpergram' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                              THEN '{$aceCompanyBuyCommission}' 
                              WHEN `{$prefix}ordtype` = '{$companySellType}' 
                              THEN '{$aceCompanySellCommission}' END as `{$prefix}acecommissionpergram`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('ordstatus' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}ordstatus` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
                         WHEN `{$prefix}ordstatus` = " . Order::STATUS_PENDING . " THEN 'Pending Payment'
                         WHEN `{$prefix}ordstatus` = " . Order::STATUS_COMPLETED . " THEN 'Completed' END as `{$prefix}ordstatus`"
                    );
                    $header[$key]->index->original = $original;
                } elseif ('peakstatus' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "CASE WHEN TIME(`{$prefix}createdon`) < '" . $from->format('H:i:s') . "' THEN 'Off-Peak Hour'
                         WHEN TIME(`{$prefix}createdon`) > '" . $to->format('H:i:s') . "' THEN 'Off-Peak Hour'
                         WHEN TIME(`{$prefix}createdon`) >= '" . $from->format('H:i:s') . "' THEN 'Peak Hour'
                         WHEN TIME(`{$prefix}createdon`) <= '" . $to->format('H:i:s') . "' THEN 'Peak Hour' END as `{$prefix}peakstatus`"
                    );
                    $header[$key]->index->original = $original;
                }
            }

            if (0 < $partnerid) {
                $conditions = ['ordpartnerid' => $partner->id];
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            $conditions = function ($q) use ($startAt, $endAt, $partnerid, $from, $to, $peak) {
                $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('ordpartnerid', $partnerid);
                $q->where(function ($r) {
                    $r->where('ordstatus', Order::STATUS_COMPLETED);
                    $r->orWhere('ordstatus', Order::STATUS_CONFIRMED);
                    $r->orWhere(function ($s) {
                        $s->where('ordstatus', Order::STATUS_PENDING);
                        $s->andWhere('ordtype', Order::TYPE_COMPANYBUY);
                    });
                });

                /*if (!$peak) {
                    $q->where(function($r) use ($from, $to) {
                        $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                        $r->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                    });
                } else {
                    $q->where(function($r) use ($from, $to) {
                        $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                        $r->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                    });
                }*/
            };

            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL);

            if ($conditions) {
                $query->where($conditions);
            }
            $query->orderBy("id", "DESC");

            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            if ($alignRight) {
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // load formating decimal START
            $columns = [];

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];

                $decimal_format = '0.' . str_pad('', intval($column['decimal']), 0);
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            $filename = $reportname . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save($pathToSave);

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $startDate; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $endDate; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $subject        = $reportname;
            $bodyEmail      = "Please find the attached file " . $reportname . " for your reference.";

            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateAdminStorageFeeReportByAuto($partner, $currentdate, $dateStart, $dateEnd, $reportpath, $emailOut = false, $emailList, $reportname)
    {
        try {
            $currentStore                 = $this->app->mymonthlystoragefeeStore();
            $partnerid                    = $partner->id;
            $header = [
                (object)["text" => "Date & Time", "index" => "chargedon"],
                (object)["text" => "Ace Ref", "index" => "refno"],
                (object)["text" => "Cust Code", "index" => "achaccountholdercode"],
                (object)["text" => "Cust Name", "index" => "achfullname"],
                (object)["text" => "NRIC", "index" => "achmykadno"],
                (object)["text" => "Customer XAU Holding (g)", "index" => "ledcurrentxau", "decimal" => 3],
                (object)["text" => "Gold Price (Rm/g)", "index" => "price", "decimal" => 2],
                (object)["text" => "Admin Fee XAU Charge (g)", "index" => "adminfeexau", "decimal" => 6],
                (object)["text" => "Admin Fee Amount (RM)", "index" => "adminfeeamount", "decimal" => 2],
                (object)["text" => "Storage Fee XAU Charge (g)", "index" => "storagefeexau", "decimal" => 6],
                (object)["text" => "Storage Fee Amount (RM)", "index" => "storagefeeamount", "decimal" => 2],
                (object)["text" => "XAU Calculated (g)", "index" => "xau", "decimal" => 6]
            ];

            $prefix = $currentStore->getColumnPrefix();
            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;

                if ('storagefeeamount' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "`{$prefix}storagefeexau` * `{$prefix}price` as `{$prefix}storagefeeamount`"
                    );
                    $header[$key]->index->original = $original;
                }

                if ('adminfeeamount' === $column->index) {
                    $header[$key]->index = $currentStore->searchTable(false)->raw(
                        "`{$prefix}adminfeexau` * `{$prefix}price` as `{$prefix}adminfeeamount`"
                    );

                    $header[$key]->index->original = $original;
                }
            }

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            $conditions = function ($q) use ($startAt, $endAt, $partnerid) {
                $q->where('chargedon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('chargedon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('partnerid', $partnerid);
            };

            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL);

            if ($conditions) {
                $query->where($conditions);
            }
            $query->orderBy("id", "DESC");

            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            if ($alignRight) {
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // load formating decimal START
            $columns = [];

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];

                $decimal_format = '0.' . str_pad('', intval($column['decimal']), 0);
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            $filename = $reportname . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save($pathToSave);

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $startDate; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $endDate; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $subject        = $reportname;
            $bodyEmail      = "Please find the attached file " . $reportname . " for your reference.";

            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateAdminStorageFeeDailyReportByAuto($partner, $currentdate, $dateStart, $dateEnd, $reportpath, $emailOut = false, $emailList, $reportname)
    {
        try {
            $currentStore                 = $this->app->mydailystoragefeeStore();
            $partnerid                    = $partner->id;
            $header = [
                (object)["text" => "Date & Time", "index" => "calculatedon"],
                (object)["text" => "Cust Code", "index" => "achaccountholdercode"],
                (object)["text" => "Cust Name", "index" => "achfullname"],
                (object)["text" => "NRIC", "index" => "achmykadno"],
                (object)["text" => "Customer XAU Holding (g)", "index" => "ledcurrentxau", "decimal" => 6],
                (object)["text" => "Admin Fee XAU Charge (g)", "index" => "adminfeexau", "decimal" => 6],
                (object)["text" => "Storage Fee XAU Charge (g)", "index" => "storagefeexau", "decimal" => 6],
                (object)["text" => "XAU Calculated (g)", "index" => "xau", "decimal" => 6]
            ];

            $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone());
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

            $conditions = function ($q) use ($startAt, $endAt, $partnerid) {
                $q->where('calculatedon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('calculatedon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('partnerid', $partnerid);
            };

            $headerText = [];
            $headerSQL = [];
            foreach ($header as $headerColumn) {
                array_push($headerText, $headerColumn->text);
                array_push($headerSQL, $headerColumn->index);
            }

            $query = $currentStore->searchView()->select($headerSQL);

            if ($conditions) {
                $query->where($conditions);
            }
            $query->orderBy("id", "DESC");

            $queryData = $query->execute();

            foreach ($headerSQL as $key => $val) {
                if ($val instanceof \ClanCats\Hydrahon\Query\Expression) {
                    $headerSQL[$key] = $val->original;
                }
            }
            $headerString = $this->createHeader($headerText);
            $contentString = $this->createContent($headerSQL, $queryData, $specialRenderer);
            $excelpages = $headerString . $contentString;

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
            $spreadsheet = $reader->loadFromString($excelpages);
            $spreadsheet->getDefaultStyle()->getNumberFormat()->setFormatCode('#');
            $spreadsheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(18);
            if ($alignRight) {
                $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            }

            // load formating decimal START
            $columns = [];

            foreach ($header as $x => $headerColumn) {
                if ($headerColumn->decimal) {
                    $column = $x; // 0 = A, 1 = B;
                    $column_decimal = $headerColumn->decimal;
                    array_push($columns, ['column' => $column, 'decimal' => $column_decimal]);
                }
            }
            $rows = [];
            $totalrow = $query->count();
            $rows = [
                'header' => 1,
                'start' => 2,
                'end' => $totalrow + 3,
                'total' => $totalrow + 5,
            ];

            // formating A1:A100;
            $alphabet = range('A', 'Z'); // LIMITATION->CURRENT, if exceed Z column will be double alphabet
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['total'];

                $decimal_format = '0.' . str_pad('', intval($column['decimal']), 0);
                $spreadsheet->getActiveSheet()->getStyle($style)->getNumberFormat()->setFormatCode($decimal_format);
            }

            // Apply border style for total count
            // Set design for total
            foreach ($columns as $column) {
                $column_alphabet = $alphabet[$column['column']];

                $style = $column_alphabet . $rows['total'];

                $range = $column_alphabet . $rows['start'] . ':' . $column_alphabet . $rows['end'];

                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
                $spreadsheet->getActiveSheet()->getStyle($style)->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

                // Compare and check if prices match

                // Set sum for prices
                // Set Sum totals
                $spreadsheet->getActiveSheet()->setCellValue($column_alphabet . $rows['total'], '=SUM(' . $range . ')');
            }

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            $filename = $reportname . '.xlsx';
            $pathToSave = $reportpath . $filename;

            // ob_end_clean();
            header('Content-Type: application/vnd.ms-excel');
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $writer->save($pathToSave);

            if ($writer) {
                // save log
                $table = $currentStore->getTableName();
                $select = json_encode($headerSQL);
                $selectDateStart = $startDate; // USER TIME, when in DB WILL BE UTC
                $selectDateEnd = $endDate; // USER TIME, when in DB WILL BE UTC
                $conditions = json_encode($conditions);
                $outputCount = $query->count('id');
            }

            $subject        = $reportname;
            $bodyEmail      = "Please find the attached file " . $reportname . " for your reference.";

            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function getCumulativeTrans($currentdate, $filename, $partnerids, $reportpath, $emaillist, $emailout, $sharedgv)
    {
        $date               = date('Y-m-d h:i:s', $currentdate);
        $createDateStart    = date_create($date);
        $modifyDateStart    = date_modify($createDateStart, "-2 day");
        $startDate          = date_format($modifyDateStart, "Y-m-d 16:00:00");
        $createDateEnd      = date_create($date);
        $modifyDateEnd      = date_modify($createDateEnd, "-1 day");
        $endDate            = date_format($modifyDateEnd, "Y-m-d 15:59:59");
        $endDateLedger      = date_format($modifyDateEnd, "Y-m-d 16:10:00");
        $dateOfData         = date_format($modifyDateEnd, "d-m-Y");
        $reportdate         = date('Ymd', $currentdate);
        $getLastDayOfPrevMonth = date("Y-n-j 15:59:59", strtotime("last day of previous month"));
        $getOtherDbPartner  = $this->app->getConfig()->{'mygtp.partner.sharedgv.diffdb'};

        try {
            if (!$sharedgv) {
                $partnerid          = $partnerids;
                $allocated          = 1;
            } else {
                $partnerid   = 0;
                $allocated   = 0;
            }

            $dgvItems = $this->app->vaultitemStore()->searchView()->select()
                //->Where('vaultlocationid',1)
                ->andWhere('sharedgv', $sharedgv)
                ->andWhere('partnerid', $partnerid)
                ->andWhere('allocated', $allocated)
                ->andWhere('status', VaultItem::STATUS_ACTIVE)
                ->orderby(['serialno'])
                ->execute();
            if (count($dgvItems) > 0) {
                foreach ($dgvItems as $aItem) {
                    $weightKilobar += $aItem->weight;
                }
            }

            $ledgerStore = $this->app->myledgerStore();
            $ledgerHdl = $ledgerStore->searchTable(false);

            $chunkPartnerIds = explode(",", $partnerids);

            foreach ($chunkPartnerIds as $aPartner) {
                $partner            = $this->app->partnerStore()->getById($aPartner);
                $partnerName        = strtok($partner->code,  '@');
                $totalBuyTransaction = $ledgerHdl->select()
                    ->addFieldSum('credit', 'credit_xau')
                    ->where('accountholderid', 0)
                    ->where('type', MyLedger::TYPE_ACEBUY)
                    ->andWhere('partnerid', $aPartner)
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->andWhere('createdon', '<=', $endDateLedger)
                    ->first();
                $totalBuy = $totalBuyTransaction['credit_xau'];

                $totalSellTransaction = $ledgerHdl->select()
                    ->addFieldSum('debit', 'debit_xau')
                    ->where('accountholderid', 0)
                    ->where('type', MyLedger::TYPE_ACESELL)
                    ->andWhere('partnerid', $aPartner)
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->andWhere('createdon', '<=', $endDateLedger)
                    ->first();
                $totalSell = $totalSellTransaction['debit_xau'];

                $totalSellSPTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $aPartner)
                    ->andWhere('type', Order::TYPE_COMPANYSELL)
                    ->andWhere('createdon', '<=', $endDate)
                    ->andWhere('remarks', 'LIKE', 'Special order%')
                    ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
                    ->first();
                $totalSellSP = $totalSellSPTransaction['total_xau'];

                $totalBuySPTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $aPartner)
                    ->andWhere('type', Order::TYPE_COMPANYBUY)
                    ->andWhere('createdon', '<=', $endDate)
                    ->andWhere('remarks', 'LIKE', 'Special order%')
                    ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
                    ->first();
                $totalBuySP = $totalBuySPTransaction['total_xau'];

                $totalRedemptionTransaction = $ledgerHdl->select()
                    ->addFieldSum('credit', 'credit_xau')
                    ->where('accountholderid', 0)
                    ->where('type', MyLedger::TYPE_ACEREDEEM)
                    ->andWhere('partnerid', $aPartner)
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->andWhere('createdon', '<=', $endDateLedger)
                    ->first();
                $totalRedemption = $totalRedemptionTransaction['credit_xau'];

                $explodeListPartner = explode(',', $getOtherDbPartner);
                if (in_array($aPartner, $explodeListPartner)) {
                    $totalStorageFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                        ->addFieldSum('storagefeexau', 'storagexau')
                        ->where('refno', 'LIKE', '%' . $partner->code . '%')
                        //->andWhere('status', 1)
                        ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                        ->first();

                    $totalAdminFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                        ->addFieldSum('adminfeexau', 'adminsxau')
                        ->where('refno', 'LIKE', '%' . $partner->code . '%')
                        //->andWhere('status', 1)
                        ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                        ->first();
                } else {
                    $totalStorageFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                        ->addFieldSum('storagefeexau', 'storagexau')
                        ->where('partnerid', $aPartner)
                        //->andWhere('status', 1)
                        ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                        ->first();

                    $totalAdminFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                        ->addFieldSum('adminfeexau', 'adminsxau')
                        ->where('partnerid', $aPartner)
                        //->andWhere('status', 1)
                        ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                        ->first();
                }

                $totalStorageBuy = $totalStorageFee['storagexau'];
                $totalAdminBuy = $totalAdminFee['adminsxau'];

                $gtpUtilization = ($totalSell + $totalSellSP) - ($totalBuy + $totalBuySP) - $totalRedemption - ($totalStorageBuy + $totalAdminBuy);
                //$utilisedTotalPercentage = number_format($gtpUtilization/$weightKilobar*100,2);

                $dataPartnerExcel[$partnerName] = array(
                    'totalBuy' => $totalBuy,
                    'totalSell' => $totalSell,
                    'totalSellSP' => $totalSellSP,
                    'totalBuySP' => $totalBuySP,
                    'totalRedemption' => $totalRedemption,
                    'totalStorageBuy' => $totalStorageBuy,
                    'totalAdminBuy' => $totalAdminBuy,
                    'gtpUtilization' => $gtpUtilization,
                );

                // 25-AUG-2022_1107 add in sharedgv_redis update daily - start
                if ($sharedgv) {
                    $updateShareDGV_CACHE[$aPartner] = [
                        'gtpUtilization' => $gtpUtilization,
                    ];
                    if (!isset($updateShareDGV_CACHE_total)) {
                        $updateShareDGV_CACHE_total = 0;
                    }
                    $updateShareDGV_CACHE_total += $gtpUtilization;
                }
                // 25-AUG-2022_1107 add in sharedgv_redis update daily - end
            }

            // 25-AUG-2022_1107 add in sharedgv_redis update daily - start
            if ($sharedgv) {
                $cacher = $this->app->getCacher();
                $shareDgvUsageKey = '{shareDgvUsage}:total';
                $updateShareDGV_CACHE_total;
                $cacher->set($shareDgvUsageKey, $updateShareDGV_CACHE_total);
                foreach ($updateShareDGV_CACHE as $partnerid => $partnerinfo) {
                    $shareDgvUsagePartnerKey = '{shareDgvUsage}:' . $partnerid;
                    $cacher->set($shareDgvUsagePartnerKey, $partnerinfo['gtpUtilization']);
                }
            }
            // 25-AUG-2022_1107 add in sharedgv_redis update daily - end

            if ($sharedgv) {
                $usefilename = 'DAILY_COMMON_DGV_';
                $excelTab = 'COMMON DGV KILOBAR LIST';
                $excelListText = 'COMMON DGV KILOBAR';
            } else {
                foreach ($dataPartnerExcel as $key => $data) {
                    $partnerName = $key;
                }
                $usefilename = 'DAILY_DGV_' . $partnerName . "_";
                $excelTab = 'DGV KILOBAR LIST';
                $excelListText = 'DGV KILOBAR';
            }

            $pathToSave = $reportpath . $usefilename . $reportdate . '.xlsx';
            $filename = $usefilename . $reportdate . '.xlsx';
            $spreadsheet = new Spreadsheet();

            //1st sheet
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($excelTab);
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', $excelListText . ' as at ' . $dateOfData);
            $sheet->setCellValue('A3', 'SERIALNO')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'WEIGHT (g)')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'LOCATION')->getColumnDimension('C')->setAutoSize(true);
            //$sheet->setCellValue('D3', 'Allocated')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('D3', 'STATUS')->getColumnDimension('D')->setAutoSize(true);
            $j = 3;
            foreach ($dgvItems as $aItem) {
                $j++;
                if ($aItem->status == 0) $statusName = 'PENDING';
                elseif ($aItem->status == 1) $statusName = 'ACTIVE';
                elseif ($aItem->status == 2) $statusName = 'TRANSFERRING';
                elseif ($aItem->status == 3) $statusName = 'INACTIVE';
                elseif ($aItem->status == 4) $statusName = 'REMOVED';
                elseif ($aItem->status == 5) $statusName = 'PENDING ALLOCATION';

                $allocatedKilobar = ($aItem->allocated == 1) ? 'YES' : 'NO';
                $sheet->setCellValue('A' . $j, $aItem->serialno)->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B' . $j, $aItem->weight)->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C' . $j, $aItem->vaultlocationname)->getColumnDimension('C')->setAutoSize(true);
                //$sheet->setCellValue('D'.$j, $allocatedKilobar)->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('D' . $j, $statusName)->getColumnDimension('D')->setAutoSize(true);
            }


            $spreadsheet->createSheet();
            $sheet1 = $spreadsheet->setActiveSheetIndex(1);
            $sheet1->setTitle('DGV UTILIZATION');
            $sheet1->mergeCells('A1:I1');
            $sheet1->setCellValue('A1', 'DGV Utilization as at ' . $dateOfData);
            $sheet1->mergeCells('A3:I3')->getStyle('A3:I3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('66B2FF');
            $sheet1->setCellValue('A3', 'GTP')->getColumnDimension('A')->setAutoSize(true);
            $sheet1->setCellValue('A4', 'PARTNER')->getColumnDimension('A')->setAutoSize(true);
            //$sheet1->setCellValue('B4', 'DATE')->getColumnDimension('B')->setAutoSize(true);
            $sheet1->setCellValue('B4', 'TOTAL SELL (g)')->getColumnDimension('B')->setAutoSize(true);
            $sheet1->setCellValue('C4', 'TOTAL SELL SP (g)')->getColumnDimension('C')->setAutoSize(true);
            $sheet1->setCellValue('D4', 'TOTAL BUY (g)')->getColumnDimension('D')->setAutoSize(true);
            $sheet1->setCellValue('E4', 'TOTAL BUY SP (g)')->getColumnDimension('E')->setAutoSize(true);
            $sheet1->setCellValue('F4', 'TOTAL REDEMPTION (g)')->getColumnDimension('F')->setAutoSize(true);
            $sheet1->setCellValue('G4', 'TOTAL ADMIN FEES (g)')->getColumnDimension('G')->setAutoSize(true);
            $sheet1->setCellValue('H4', 'TOTAL STORAGE FEES (g)')->getColumnDimension('H')->setAutoSize(true);
            $sheet1->setCellValue('I4', 'DGV UTILIZED (g)')->getColumnDimension('I')->setAutoSize(true);
            $sheetnum = 5;
            foreach ($dataPartnerExcel as $key => $aData) {
                $sheet1->setCellValue('A' . $sheetnum, $key)->getColumnDimension('A')->setAutoSize(true);
                //$sheet1->setCellValue('B'.$sheetnum, $dateOfData)->getColumnDimension('B')->setAutoSize(true);
                $sheet1->getStyle('B' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('B' . $sheetnum, $aData['totalSell'] != 0 ? $aData['totalSell'] : 0)->getColumnDimension('B')->setAutoSize(true);
                $sheet1->getStyle('C' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('C' . $sheetnum, $aData['totalSellSP'] != 0 ? $aData['totalSellSP'] : 0)->getColumnDimension('C')->setAutoSize(true);
                $sheet1->getStyle('D' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('D' . $sheetnum, $aData['totalBuy'] != 0 ? $aData['totalBuy'] : 0)->getColumnDimension('D')->setAutoSize(true);
                $sheet1->getStyle('E' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('E' . $sheetnum, $aData['totalBuySP'] != 0 ? $aData['totalBuySP'] : 0)->getColumnDimension('E')->setAutoSize(true);
                $sheet1->getStyle('F' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('F' . $sheetnum, $aData['totalRedemption'] != 0 ? $aData['totalRedemption'] : 0)->getColumnDimension('F')->setAutoSize(true);
                $sheet1->getStyle('G' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('G' . $sheetnum, $aData['totalAdminBuy'] != 0 ? $aData['totalAdminBuy'] : 0)->getColumnDimension('G')->setAutoSize(true);
                $sheet1->getStyle('H' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('H' . $sheetnum, $aData['totalStorageBuy'] != 0 ? $aData['totalStorageBuy'] : 0)->getColumnDimension('H')->setAutoSize(true);
                $sheet1->getStyle('I' . $sheetnum)->getNumberFormat()->setFormatCode('0.000');
                $sheet1->setCellValue('I' . $sheetnum, $aData['gtpUtilization'])->getColumnDimension('I')->setAutoSize(true);
                $sheetnum++;
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);

            //sendEmail
            //$emailConfig = 'sendtoBMMBdailydgv';
            //$emailSubject = $settings->sapdgcode." - DAILYDVG {$reportdate} & DGV Stock Status - Utilised($utilisedTotalPercentage%)";
            $emailSubject = $usefilename . " {$reportdate}";
            $bodyEmail    = "Please find the attached file " . $filename . " for your reference.";
            //$sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathToSave,$filename);
            if ($emailout) $this->sendNotifyEmailReport($bodyEmail, $emailSubject, $emaillist, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to update extraction data from Shared DAILYDVG", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function createSharedMintedReport($currentdate, $filename, $reportpath, $emaillist, $emailout, $mintedShared)
    {
        $date               = date('Y-m-d h:i:s', $currentdate);
        $createDateStart    = date_create($date);
        $modifyDateStart    = date_modify($createDateStart, "-2 day");
        $startDate          = date_format($modifyDateStart, "Y-m-d 16:00:00");
        $createDateEnd      = date_create($date);
        $modifyDateEnd      = date_modify($createDateEnd, "-1 day");
        $endDate            = date_format($modifyDateEnd, "Y-m-d 15:59:59");
        $endDateLedger      = date_format($modifyDateEnd, "Y-m-d 16:10:00");
        $dateOfData         = date_format($modifyDateEnd, "d-m-Y");
        $reportdate         = date('Ymd', $currentdate);
        $pathToSave = $reportpath . 'DAILY_COMMON_MINTED_' . $reportdate . '.xlsx';
        $filename = 'DAILY_COMMON_MINTED_' . $reportdate . '.xlsx';
        $spreadsheet = new Spreadsheet();

        //1st sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('COMMON MINTED LIST');
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Minted Serial Number as at ' . $dateOfData);
        $sheet->setCellValue('A3', 'SerialNo')->getColumnDimension('A')->setAutoSize(true);
        $sheet->setCellValue('B3', 'Weight')->getColumnDimension('B')->setAutoSize(true);
        $sheet->setCellValue('C3', 'ItemCode')->getColumnDimension('C')->setAutoSize(true);
        $j = 3;
        foreach ($mintedShared['inventoryList'] as $key => $aItem) {
            $j++;
            $sheet->setCellValue('A' . $j, $aItem['Serial'])->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B' . $j, $aItem['Total Qty'])->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C' . $j, $aItem['ItemCode'])->getColumnDimension('C')->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pathToSave);

        //sendEmail
        //$emailConfig = 'sendtoBMMBdailydgv';
        //$emailSubject = $settings->sapdgcode." - DAILYDVG {$reportdate} & DGV Stock Status - Utilised($utilisedTotalPercentage%)";
        $emailSubject = "DAILY_COMMON_MINTED {$reportdate}";
        $bodyEmail    = "Please find the attached file " . $filename . " for your reference.";
        if ($emailout) $this->sendNotifyEmailReport($bodyEmail, $emailSubject, $emaillist, $pathToSave, $filename);
    }

    public function getTransactionsTotal($currentdate, $filename, $partner, $server, $reportpath, $emaillist, $emailout, $partnername)
    {
        $date               = date('Y-m-d h:i:s', $currentdate);
        $createDateStart    = date_create($date);
        $modifyDateStart    = date_modify($createDateStart, "-2 day");
        $startDate          = date_format($modifyDateStart, "Y-m-d 16:00:00");
        $createDateEnd      = date_create($date);
        $modifyDateEnd      = date_modify($createDateEnd, "-1 day");
        $endDate            = date_format($modifyDateEnd, "Y-m-d 15:59:59");
        $endDateLedger      = date_format($modifyDateEnd, "Y-m-d 16:10:00");
        $dateOfData         = date_format($modifyDateEnd, "d-m-Y");
        $reportdate         = date('Ymd', $currentdate);
        $partnerSettingStore = $this->app->mypartnersettingStore();
        $settings           = $partnerSettingStore->getByField('partnerid', $partner->id);
        $nameOfPartner      = strtoupper($partnername);

        //$testDate   = strtotime('2021-11-01 17:00:00');
        //echo date("Y-n-j 15:59:59", strtotime("last day of previous month",$testDate));
        //$getLastDayOfPrevMonth = date("Y-n-j 15:59:59", strtotime("last day of previous month",$testDate));
        $getLastDayOfPrevMonth = date("Y-n-j 15:59:59", strtotime("last day of previous month"));

        //if(null != $server) $extraFlag = "#####".strtoupper($server)."#####"; 
        if (null != $server) $extraFlag = strtoupper($server);

        try {
            //get location id
            $locationVault = $this->app->vaultlocationStore()->searchTable()->select()
                ->where('partnerid', $partner->id)
                ->andWhere('status', 1)
                ->andWhere('type', VaultLocation::TYPE_END)
                ->one();

            $items = $this->app->vaultitemStore()->searchView()->select()
                ->where('partnerid', $partner->id)
                //->andWhere('vaultlocationid', $locationVault->id)
                ->andWhere('allocated', 1)
                ->andWhere('status', VaultItem::STATUS_ACTIVE)
                ->orderby(['serialno'])
                ->execute();
            if (count($items) > 0) {
                foreach ($items as $aItem) {
                    $weightKilobar += $aItem->weight;
                }
            }

            $ledgerStore = $this->app->myledgerStore();
            $ledgerHdl = $ledgerStore->searchTable(false);
            $p = $ledgerStore->getColumnPrefix();

            $totalBuyTransaction = $ledgerHdl->select()
                ->addFieldSum('credit', 'credit_xau')
                ->where('accountholderid', 0)
                ->where('type', MyLedger::TYPE_ACEBUY)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                ->andWhere('createdon', '<=', $endDateLedger)
                ->first();
            $totalBuy = $totalBuyTransaction['credit_xau'];

            $totalSellTransaction = $ledgerHdl->select()
                ->addFieldSum('debit', 'debit_xau')
                ->where('accountholderid', 0)
                ->where('type', MyLedger::TYPE_ACESELL)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                ->andWhere('createdon', '<=', $endDateLedger)
                ->first();
            $totalSell = $totalSellTransaction['debit_xau'];

            /*$totalSellSPTransaction = $ledgerHdl->select()
                                ->addFieldSum('debit', 'debit_xau')
                                ->where('accountholderid', 0)
                                ->where('type', MyLedger::TYPE_PROMO)
                                ->andWhere('partnerid', $partner->id)
                                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                                ->andWhere('remarks', 'LIKE', 'Special Spot Order%')
                                ->andWhere('createdon', '<=',$endDate)
                                ->first();
            $totalSellSP = $totalSellSPTransaction['debit_xau'];*/

            $totalSellSPTransaction = $this->app->orderStore()->searchTable(false)->select()
                ->addFieldSum('xau', 'total_xau')
                ->where('partnerid', $partner->id)
                ->andWhere('type', Order::TYPE_COMPANYSELL)
                ->andWhere('createdon', '<=', $endDate)
                ->andWhere('remarks', 'LIKE', 'Special order%')
                ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
                ->first();
            $totalSellSP = $totalSellSPTransaction['total_xau'];

            $totalBuySPTransaction = $this->app->orderStore()->searchTable(false)->select()
                ->addFieldSum('xau', 'total_xau')
                ->where('partnerid', $partner->id)
                ->andWhere('type', Order::TYPE_COMPANYBUY)
                ->andWhere('createdon', '<=', $endDate)
                ->andWhere('remarks', 'LIKE', 'Special order%')
                ->andWhere('status', 'IN', [Order::STATUS_CONFIRMED, Order::STATUS_COMPLETED])
                ->first();
            $totalBuySP = $totalBuySPTransaction['total_xau'];

            $totalRedemptionTransaction = $ledgerHdl->select()
                ->addFieldSum('credit', 'credit_xau')
                ->where('accountholderid', 0)
                ->where('type', MyLedger::TYPE_ACEREDEEM)
                ->andWhere('partnerid', $partner->id)
                ->andWhere('status', MyLedger::STATUS_ACTIVE)
                ->andWhere('createdon', '<=', $endDateLedger)
                ->first();
            $totalRedemption = $totalRedemptionTransaction['credit_xau'];

            $totalStorageFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                ->addFieldSum('storagefeexau', 'storagexau')
                ->where('partnerid', $partner->id)
                //->andWhere('status', 1)
                ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                ->first();
            $totalStorageBuy = $totalStorageFee['storagexau'];

            $totalAdminFee = $this->app->mymonthlystoragefeeStore()->searchView(false)->select()
                ->addFieldSum('adminfeexau', 'adminsxau')
                ->where('partnerid', $partner->id)
                //->andWhere('status', 1)
                ->andWhere('chargedon', '<=', $getLastDayOfPrevMonth)
                ->first();
            $totalAdminBuy = $totalAdminFee['adminsxau'];



            /*$totalBuyTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner->id)
                    ->andWhere('type', Order::TYPE_COMPANYBUY)
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status', Order::STATUS_CONFIRMED)
                    ->first();
            $totalBuy = $totalBuyTransaction['total_xau'];

            $totalSellTransaction = $this->app->orderStore()->searchTable(false)->select()
                    ->addFieldSum('xau', 'total_xau')
                    ->where('partnerid', $partner->id)
                    ->andWhere('type', Order::TYPE_COMPANYSELL)
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status', Order::STATUS_CONFIRMED)
                    ->first();
            $totalSell = $totalSellTransaction['total_xau'];*/

            /*$totalRedemptionTransaction = $this->app->redemptionStore()->searchTable(false)->select()
                    ->addFieldSum('totalweight', 'total_xau')
                    ->where('partnerid', $partner->id)
                    ->andWhere('createdon', '<=',$endDate)
                    ->andWhere('status', Redemption::STATUS_COMPLETED)
                    ->first();
            $totalRedemption = $totalRedemptionTransaction['total_xau'];*/

            $gtpUtilization = ($totalSell + $totalSellSP) - ($totalBuy + $totalBuySP) - $totalRedemption - ($totalStorageBuy + $totalAdminBuy);
            $utilisedTotalPercentage = number_format($gtpUtilization / $weightKilobar * 100, 2);

            /*$bodyEmail .= "\nDGV Stock Status - Utilised($utilisedTotalPercentage%).\n";
            if(50 < $utilisedTotalPercentage){ // when percentage is almost 100%. if more than 50%
                $bodyEmail .= "DGV stock for ".$partner->name." allocated already more than 50% utilised- {$utilisedTotalPercentage}/100.\n\n";
            } else {
                $bodyEmail .= "DGV stock for ".$partner->name." allocated till today utilised {$utilisedTotalPercentage}/100.\n\n";
                
            }
            $bodyEmail .= "There are total of {$gtpUtilization}g out of {$weightKilobar}g which are still active.\n\n";*/

            $pathToSave = $reportpath . $nameOfPartner . '_DAILYDGV_' . $reportdate . '.xlsx';
            $filename = $nameOfPartner . '_DAILYDGV_' . $reportdate . '.xlsx';
            $spreadsheet = new Spreadsheet();

            //1st sheet
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle($nameOfPartner . ' DGV KILOBAR LIST');
            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', $nameOfPartner . ' DGV Kilobar Serial Number as at ' . $dateOfData);
            $sheet->setCellValue('A3', 'SerialNo')->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B3', 'Weight')->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C3', 'Location')->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D3', 'Allocated')->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E3', 'Status')->getColumnDimension('E')->setAutoSize(true);
            $j = 3;
            foreach ($items as $aItem) {
                $j++;
                if ($aItem->status == 0) $statusName = 'PENDING';
                elseif ($aItem->status == 1) $statusName = 'ACTIVE';
                elseif ($aItem->status == 2) $statusName = 'TRANSFERRING';
                elseif ($aItem->status == 3) $statusName = 'INACTIVE';
                elseif ($aItem->status == 4) $statusName = 'REMOVED';
                elseif ($aItem->status == 5) $statusName = 'PENDING ALLOCATION';

                $allocatedKilobar = ($aItem->allocated == 1) ? 'YES' : 'NO';
                $sheet->setCellValue('A' . $j, $aItem->serialno)->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B' . $j, $aItem->weight)->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C' . $j, $aItem->vaultlocationname)->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D' . $j, $allocatedKilobar)->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E' . $j, $statusName)->getColumnDimension('E')->setAutoSize(true);
            }

            //2ND sheet
            $spreadsheet->createSheet();
            $sheet2 = $spreadsheet->setActiveSheetIndex(1);
            $sheet2->setTitle('DGV UTILIZATION');
            $sheet2->mergeCells('A1:I1');
            $sheet2->setCellValue('A1', 'DGV Utilization as at ' . $dateOfData);
            $sheet2->mergeCells('A3:I3')->getStyle('A3:I3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('66B2FF');
            $sheet2->setCellValue('A3', 'GTP')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('A4', 'DATE')->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B4', 'TOTAL SELL')->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C4', 'TOTAL SELL SP')->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D4', 'TOTAL BUY')->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E4', 'TOTAL BUY SP')->getColumnDimension('E')->setAutoSize(true);
            $sheet2->setCellValue('F4', 'TOTAL REDEMPTION')->getColumnDimension('F')->setAutoSize(true);
            $sheet2->setCellValue('G4', 'TOTAL ADMIN FEES')->getColumnDimension('G')->setAutoSize(true);
            $sheet2->setCellValue('H4', 'TOTAL STORAGE FEES')->getColumnDimension('H')->setAutoSize(true);
            $sheet2->setCellValue('I4', 'DGV UTILIZATION')->getColumnDimension('I')->setAutoSize(true);

            $sheet2->setCellValue('A5', $dateOfData)->getColumnDimension('A')->setAutoSize(true);
            $sheet2->getStyle('B5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('B5', $totalSell != 0 ? $totalSell : 0)->getColumnDimension('B')->setAutoSize(true);
            $sheet2->getStyle('C5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('C5', $totalSellSP != 0 ? $totalSellSP : 0)->getColumnDimension('C')->setAutoSize(true);
            $sheet2->getStyle('D5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('D5', $totalBuy != 0 ? $totalBuy : 0)->getColumnDimension('D')->setAutoSize(true);
            $sheet2->getStyle('E5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('E5', $totalBuySP != 0 ? $totalBuySP : 0)->getColumnDimension('E')->setAutoSize(true);
            $sheet2->getStyle('F5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('F5', $totalRedemption != 0 ? $totalRedemption : 0)->getColumnDimension('F')->setAutoSize(true);
            $sheet2->getStyle('G5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('G5', $totalAdminBuy != 0 ? $totalAdminBuy : 0)->getColumnDimension('G')->setAutoSize(true);
            $sheet2->getStyle('H5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('H5', $totalStorageBuy != 0 ? $totalStorageBuy : 0)->getColumnDimension('H')->setAutoSize(true);
            $sheet2->getStyle('I5')->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('I5', $gtpUtilization)->getColumnDimension('I')->setAutoSize(true);

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);

            //sendEmail
            //$emailConfig = 'sendtoBMMBdailydgv';
            //$emailSubject = $settings->sapdgcode." - DAILYDVG {$reportdate} & DGV Stock Status - Utilised($utilisedTotalPercentage%)";
            $emailSubject = $nameOfPartner . " - DAILYDVG {$reportdate}";
            $bodyEmail    = "Please find the attached file " . $filename . " for your reference.";
            //$sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathToSave,$filename);
            if ($emailout) $this->sendNotifyEmailReport($bodyEmail, $emailSubject, $emaillist, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to update extraction data from " . $nameOfPartner . " DAILYDVG", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function sendNotifyEmailReport($bodyEmail, $subject, $sendToEmail, $file = null, $attachment = null)
    {
        $mailer = $this->app->getMailer();
        $emailTo = explode(',', $sendToEmail); //compulsory email

        foreach ($emailTo as $anEmail) {
            $mailer->addAddress($anEmail);
            $this->logDebug(__METHOD__ . " Add email address " . $anEmail . " as recipient for " . $subject);
        }

        $mailer->addAttachment($file, $attachment);
        $this->logDebug(__METHOD__ . " Prepare to send email for " . $subject);
        $mailer->Subject = $subject;
        $mailer->Body    = $bodyEmail;
        $mailer->send();
        $this->logDebug(__METHOD__ . " Sending email to recipient is success for " . $subject);
    }

    public function generateCompanyBuyReportByAuto($partner, $currentdate, $dateStart, $dateEnd, $emailList, $reportname)
    {
        try {
            $date = new \DateTime(date('Y-m-d H:i:s', $currentdate), $this->app->getUserTimezone());
            $header = [
                (object)['text' => 'Date', 'index' => 'createdon'],
                (object)['text' => 'Account Number', 'index' => 'dbmaccountnumber'],
                (object)['text' => 'Customer Code', 'index' => 'achcode'],
                (object)['text' => 'Customer Name', 'index' => 'achfullname'],
                (object)['text' => 'Customer NRIC', 'index' => 'achmykadno'],
                (object)['text' => 'Customer Phone', 'index' => 'achphoneno'],
                (object)['text' => 'Customer Email', 'index' => 'achemail'],
                (object)['text' => 'Type', 'index' => 'ordtype'],
                (object)['text' => 'Product', 'index' => 'ordproductname'],
                (object)['text' => 'Xau Weight (g)', 'index' => 'ordxau', 'decimal' => 3],
                (object)['text' => 'Order Price', 'index' => 'ordprice', 'decimal' => 2],
                (object)['text' => 'Original Amount (RM)', 'index' => 'originalamount', 'decimal' => 2],
                (object)['text' => 'Transaction Fee', 'index' => 'ordfee', 'decimal' => 2],
                (object)['text' => 'Total Amount (RM)', 'index' => 'ordamount', 'decimal' => 2],
                (object)['text' => 'Order No', 'index' => 'ordorderno'],
                (object)['text' => 'Transaction Reference No', 'index' => 'dbmtransactionrefno'],
                (object)['text' => 'Payment Ref No', 'index' => 'dbmpdtreferenceno'],
                (object)['text' => 'Settlement Method', 'index' => 'settlementmethod'],
                (object)['text' => 'Campaign Code', 'index' => 'campaigncode'],
                (object)['text' => 'Status', 'index' => 'status'],
            ];

            $prefix = $this->app->mygoldtransactionStore()->getColumnPrefix();

            foreach ($header as $key => $column) {
                // Overwrite index value with expression
                $original = $column->index;
                if ('status' === $column->index) {
                    $header[$key]->index = $this->app->mygoldtransactionStore()->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}status` = 0 THEN 'Pending Payment' " .
                            "WHEN `{$prefix}status` = 1 THEN 'Confirmed' " .
                            "WHEN `{$prefix}status` = 2 THEN 'Paid' " .
                            "WHEN `{$prefix}status` = 3 THEN 'Failed' " .
                            "WHEN `{$prefix}status` = 4 THEN 'Reversed' " .
                            "ELSE 'UNKNOWN' END as `{$prefix}status` "
                    );
                    $header[$key]->index->original = $original;
                }
            }

            $dateStart = new \DateTime($date->format('Y-m-d 00:00:00'), $this->app->getUserTimezone());
            $dateEnd   = new \DateTime($date->format('Y-m-d 23:59:59'), $this->app->getUserTimezone());
            $dateStart->modify("-1 day");
            $dateEnd->modify("-1 day");
            $dateStart->setTimezone($this->app->getServerTimezone());
            $dateEnd->setTimezone($this->app->getServerTimezone());;
            $conditions = function ($query) use ($dateStart, $dateEnd, $partner) {
                $query->where('ordpartnerid', $partner->id)
                    ->whereIn('status', [1, 0])
                    ->andWhere('ordtype', Order::TYPE_COMPANYBUY)
                    ->whereIn('ordstatus', [1, 0])
                    ->andWhere('settlementmethod', 'WALLET')
                    ->andWhere('createdon', '<=', $dateEnd->format('Y-m-d H:i:s'));
            };

            $projectBase = $this->app->getConfig()->{'projectBase'};

            $filepath = $this->generateMyGtpReport($this->app->mygoldtransactionStore(), $header, $reportname, null, $conditions, null, 1, false, true, 'ASC');
            $attachment = stream_get_meta_data($filepath)['uri'];

            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            if ($developmentEnv) {
                $environtmentFileName = '_DEMO_';
            } else {
                $environtmentFileName = '_';
            }
            $reportdate         = date('Ymd', $currentdate);
            $attachmentName = $partner->name . $environtmentFileName . strtoupper($reportname) . '_' . $reportdate . '.xlsx';
            $subject        = $partner->name . $environtmentFileName . strtoupper($reportname) . '_' . $reportdate;
            $bodyEmail      = "Please find the attached file " . $attachmentName . " for your reference.";

            if ($emailList != null) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $attachment, $attachmentName);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    private function createHeader($data)
    {

        // Date timestamp for header
        $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
        $datenow = $datenow->format('Y-m-d H:i:s');

        $text = '<h3>Report generated as at ' . $datenow . '</h3><br>';

        $text .= '
            <tr>';
        foreach ($data as $column) {
            $text .= '
                <th><b>' . $column . '</b></th>
                ';
        }
        $text .= '
            </tr>
        ';
        return $text;
    }
    private function createContent($headerSQL, $data, $specialRenderer = null, $status_formula = null)
    {
        // $status_formula = "=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1,'active',2,'inactive',??)";
        $text = '<table>
        ';
        foreach ($data as $row) {
            $text .= '
            <tr>';
            foreach ($headerSQL as $index) {
                $text .= '
                <td>
                ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                </td>';

                // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                if ($index == "status" && $status_formula) {
                    // $text .= "<td>".$status_formula."</td>";
                    $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                }
            }
            $text .=
                '</tr>';
        }
        $text .= '
        </table>';

        // print_r($text);exit;
        return $text;
    }

    private function createCustomContent($headerSQL, $data, $specialRenderer = null, $newFields = null)
    {
        // $status_formula = "=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1,'active',2,'inactive',??)";
        $text = '<table>
        ';
        foreach ($data as $key => $row) {
            $text .= '
            <tr>';
            foreach ($headerSQL as $index) {


                // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                if ($index == "buybalance" && $newFields) {
                    $buybalance = $newFields[$key]['buybalance'];
                    // $text .= "<td>".$status_formula."</td>";
                    $text .= "<td>" . $buybalance . "</td>";
                } else if ($index == "sellbalance" && $newFields) {
                    $sellbalance = $newFields[$key]['sellbalance'];
                    // $text .= "<td>".$status_formula."</td>";
                    $text .= "<td>" . $sellbalance . "</td>";
                } else {
                    $text .= '
                    <td>
                    ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                    </td>';
                }
            }
            $text .=
                '</tr>';
        }
        $text .= '
        </table>';

        // print_r($text);exit;
        return $text;
    }

    // Separate Json Items into multiple fields
    private function createContentWithJson($headerSQL, $data, $specialRenderer = null, $status_formula = null)
    {
        // $status_formula = "=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1,'active',2,'inactive',??)";
        $text = '<table>
        ';
        // Check if there are any Json fields to decode
        if ($specialRenderer['decode'] == 'json') {
            // Get said json field to extract from;
            $jsonfield = $specialRenderer['sqlfield'];
        }
        foreach ($data as $row) {
            // check each object to see if there are any json fields
            /**
             * Special Renderer Params - 
             * decode Type = json
             * sqlfield = the field with json
             * displayfield = json items to show
             * separatefield = fields to separate into different rows
             */
            $rawJsonItems = $row->{$specialRenderer['sqlfield']};
            $jsonArray = json_decode($rawJsonItems);
            $sizeCount = count($jsonArray);
            $separateFieldColumns = $specialRenderer['separatefield'];

            // Check if sqlfield is to be skipped, if yes skip display
            if (true != $specialRenderer['isdisplayedinreport']) {
                // pop sqlfield from array
                if (($key = array_search($specialRenderer['sqlfield'], $headerSQL)) !== false) {
                    unset($headerSQL[$key]);
                }
            }


            /** Basically merge parent cells and spearate the json cells into different rows
             *  parents : <td colspan = $sizeCount>
             *  
             *  Sets parent by json item size and loop items multiplied by size
             */

            // Create looping based on size
            // Loop fields to be separated based on sizecount;
            // Create table if size is bigger than one
            if ($sizeCount > 1) {
                $num = 0;
                for ($num = 0; $num < $sizeCount; $num++) {

                    $text .= '
                    <tr>';

                    // $text .= '
                    // <tr rowspan='.$sizeCount.'>';

                    foreach ($headerSQL as $index) {

                        // Do check and determine whether it is parent or child
                        if (in_array($index, $separateFieldColumns)) {
                            // Loop fields to be separated based on sizecount;
                            // Create table if size is bigger than one
                            // Replace json array values into fields if exist in json
                            if (array_key_exists($index, $jsonArray[$num])) {


                                $text .= '
                                <td>
                                ' . $this->createColumnFormatWithJson($row, $index, $jsonArray[$num]) . '
                                </td>';
                            } else {
                                $text .= '
                                <td>
                                ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                                </td>';
                            }


                            // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                            if ($index == "status" && $status_formula) {
                                // $text .= "<td>".$status_formula."</td>";
                                $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                            }
                        } else {
                            // This is the default header, 
                            // Run this once to create the top record

                            // For parent fields that are not separated ( merge )
                            // rowspan='.$sizeCount.'
                            if ($num == 0) {
                                $text .= '
                                <td rowspan=' . $sizeCount . '>
                                ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                                </td>';
                            }


                            // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                            if ($index == "status" && $status_formula) {
                                // $text .= "<td>".$status_formula."</td>";
                                $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                            }
                        }
                    }
                    $text .=
                        '</tr>';
                }
            } else {
                // For parent fields that are not separated ( merge )
                // rowspan='.$sizeCount.'
                $text .= '
                    <tr>';

                // $text .= '
                // <tr rowspan='.$sizeCount.'>';

                foreach ($headerSQL as $index) {
                    // For parent fields that are not separated ( merge )
                    // rowspan='.$sizeCount.'
                    if (in_array($index, $separateFieldColumns)) {
                        // Loop fields to be separated based on sizecount;
                        // Create table if size is bigger than one
                        // Replace json array values into fields if exist in json
                        // Json array is 0 as record is always one
                        if (array_key_exists($index, $jsonArray[0])) {


                            $text .= '
                                <td>
                                ' . $this->createColumnFormatWithJson($row, $index, $jsonArray[0]) . '
                                </td>';
                        } else {
                            $text .= '
                                <td>
                                ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                                </td>';
                        }


                        // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                        if ($index == "status" && $status_formula) {
                            // $text .= "<td>".$status_formula."</td>";
                            $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                        }
                    } else {
                        // This is the default header, 
                        // Run this once to create the top record

                        // For parent fields that are not separated ( merge )
                        // rowspan='.$sizeCount.'

                        $text .= '
                            <td>
                            ' . $this->createColumnFormat($row, $index, $specialRenderer) . '
                            </td>';




                        // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                        if ($index == "status" && $status_formula) {
                            // $text .= "<td>".$status_formula."</td>";
                            $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                        }
                    }
                    // $text .= '
                    // <td>
                    // '.$this->createColumnFormat($row, $index, $specialRenderer).'
                    // </td>';

                    // add in readable status on any position from front end, next to right of STATUS column. -- REF:READABLE_STATUS
                    if ($index == "status" && $status_formula) {
                        // $text .= "<td>".$status_formula."</td>";
                        $text .= "<td>" . $this->render_readable_status_string($row->{$index}, $status_formula) . "</td>";
                    }
                }
                $text .=
                    '</tr>';
            }
        }
        $text .= '
        </table>';

        // print_r($text);exit;
        return $text;
    }

    private function createDirectContent($headerSQL, $data, $specialRenderer = null, $status_formula = null)
    {
        // $status_formula = "=SWITCH(OFFSET(INDIRECT(ADDRESS(ROW(), COLUMN())),0,-1), 1,'active',2,'inactive',??)";
        $text = '<table>
        ';
    
        foreach ($data as $row) {
            $text .= '
            <tr>';
            foreach ($headerSQL as $index) {
                $text .= '
                <td>
                ' . $this->createDirectColumnFormat($row, $index, $specialRenderer) . '
                </td>';
            }
            $text .=
                '</tr>';
        }
        $text .= '
        </table>';

        // print_r($text);exit;
        return $text;
    }

    private function createColumnFormat($row, $index, $specialRenderer = null)
    {
        $data = $row->toArray();
        if ($data[$index] instanceof \DateTime) {
            return $data[$index]->format('d-m-Y H:i:s');
        }
        if ($specialRenderer) {
            if ($index == $specialRenderer['sqlfield'] && $specialRenderer['decode'] == 'json') {
                // [{"sapreturnid":2621,"code":"GS-999-9-100g","serialnumber":"IGR510126","weight":"100.000000","sapreverseno":"1887"}]
                $data_json = json_decode($data[$index]);
                $return_data = '';
                foreach ($data_json as $x => $json_array) {
                    $x++;
                    //$return_data .= 'item ('.$x.') - ';
                    foreach ($specialRenderer['displayfield'] as $displayfield) {
                        $return_data .= $json_array->$displayfield . ' ';
                    }
                    $return_data .= '<br>';
                }
                return $return_data;
            }
        }
        return $data[$index];
    }


    private function createColumnFormatWithJson($row, $index, $jsonArray = null)
    {
        $data = $row->toArray();
        if ($data[$index] instanceof \DateTime) {
            return $data[$index]->format('d-m-Y H:i:s');
        }
        if ($jsonArray) {
            // [{"sapreturnid":2621,"code":"GS-999-9-100g","serialnumber":"IGR510126","weight":"100.000000","sapreverseno":"1887"}]
            $data[$index] = $jsonArray->{$index};
        }
        return $data[$index];
    }

    private function createDirectColumnFormat($row, $index, $specialRenderer = null)
    {
        $data = $row;
        // CUstom date matcher
        // date pattern :  2023-06-08T18:58:59+08:00
        $pattern = "/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}$/";
        $pattern2 = "/-\d{3}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\+\d{2}:\d{2}/";
        if (preg_match($pattern, $data->{$index}) || preg_match($pattern2, $data->{$index})) {
            if(preg_match($pattern2, $data->{$index}))
                $formattedDateTime = '';
            else{    
                $datetime = \DateTime::createFromFormat('Y-m-d\TH:i:sP', $data->{$index});
                $formattedDateTime = $datetime->format('d-m-Y H:i:s');
            }
            return $formattedDateTime;
        }

        // if ($data->{$index} instanceof \DateTime) {
        //     print("nanilo");exit;
        //     return $data->{$index}->format('d-m-Y H:i:s');
        // }
        return $data->{$index};
    }

    private function render_readable_status_string($raw_status, $statuses)
    {
        return $statuses[$raw_status];
    }

    private function createStatusWorksheet($statusRenderer = null)
    {
        // $statusRenderer = [
        //     0 => "Pending",
        //     1 => "Confirmed",
        //     2 => "Pending Payment",
        //     3 => "Pending Cancel",
        //     4 => "Reversal",
        //     5 => "Completed",
        //     6 => "Expired"
        // ];
        $text = '<table>
        ';
        foreach ($statusRenderer as $status_code => $status_text) {
            $text .= '
            <tr>';
            $text .= '
                <td>
                ' . $status_code . '
                </td>';
            $text .= '
                <td>
                ' . $status_text . '
                </td>';
            $text .=
                '</tr>';
        }
        $text .= '
        </table>';

        return $text;
    }

    public function generateCoreReportByAuto($currentStore, $category, $dateStart, $dateEnd, $modulename, $reportpath, $reportname, $emailOut = false, $emailList)
    {
        try {
            /****/
            $dateStart      = new \DateTime($dateStart, $this->app->getUserTimezone());
            $dateEnd        = new \DateTime($dateEnd, $this->app->getUserTimezone());
            //$dateStart      = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt        = new \DateTime($dateStart->format('Y-m-d H:i:s'));
            $saveStartAt    = $startAt;
            $startAt        = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt          = new \DateTime($dateEnd->format('Y-m-d H:i:s'));
            $saveEndAt      = $endAt;
            $endAt          = \Snap\common::convertUserDatetimeToUTC($endAt);

            $getMonthYear   = $startAt->format('My');

            if ($currentStore == 'partner') {
                $partnerList = $this->app->partnerStore()->searchView()->select()
                    ->where('corepartner', 1)
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    //->andWhere('status',Partner::STATUS_ACTIVE)
                    ->orderby('id')
                    ->execute();

                $spreadsheet = new Spreadsheet();

                //1st sheet
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle(strtoupper($getMonthYear));
                $sheet->setCellValue('A1', 'No')->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B1', 'UserID')->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C1', 'Create Date')->getColumnDimension('C')->setAutoSize(true);
                $sheet->setCellValue('D1', 'Name')->getColumnDimension('D')->setAutoSize(true);
                $sheet->setCellValue('E1', 'Salesman')->getColumnDimension('E')->setAutoSize(true);
                $sheet->getStyle("A1:E1")->getFont()->setBold(true);

                $j = 1;
                $countData = count($partnerList);
                foreach ($partnerList as $aItem) {
                    $j++;
                    $sheet->setCellValue('A' . $j, $j - 1)->getColumnDimension('A')->setAutoSize(true);
                    $sheet->setCellValue('B' . $j, $aItem->id)->getColumnDimension('B')->setAutoSize(true);
                    $sheet->setCellValue('C' . $j, $aItem->createdon->format('Y-m-d H:i:s'))->getColumnDimension('C')->setAutoSize(true);
                    $sheet->setCellValue('D' . $j, $aItem->name)->getColumnDimension('D')->setAutoSize(true);
                    $sheet->setCellValue('E' . $j, $aItem->salespersonname)->getColumnDimension('E')->setAutoSize(true);
                }
            } elseif ($currentStore == 'order') {
                if ($category == 'sell') $ordType = 'CompanySell';
                elseif ($category == 'buy') $ordType = 'CompanyBuy';

                /*get partnerid core*/
                $core_partners = [];
                $partnerList = $this->app->partnerStore()->searchView()->select()
                    ->where('corepartner', 1)
                    ->orderby('id')
                    ->execute();
                foreach ($partnerList as $aPartnerList) {
                    array_push($core_partners, $aPartnerList->id);
                }

                $orderStore = $this->app->orderStore()->searchView()->select();
                $orderStore->where('ord_type', $ordType)
                    //->andWhere('ord_partnerid','IN',$core_partners)
                    ->andWhere('ord_apiversion', 'MANUAL')
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    ->orderby('partnername');
                $orderList = $orderStore->execute();


                $groupStore = $this->app->orderStore()->searchView()->select(['partnername', 'productname', 'productid', 'salespersonname', 'fee']);
                $groupStore->addFieldSum('xau', 'ord_xau')
                    ->where('ord_type', $ordType)
                    //->andWhere('ord_partnerid','IN',$core_partners)
                    ->andWhere('ord_apiversion', 'MANUAL')
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    ->groupBy(['partnername', 'productid', 'salespersonname', 'fee']);
                $groupList  = $groupStore->execute();

                foreach ($groupList as $aList) {
                    $sales[$aList->salespersonname][$aList->productid][] = array(
                        'productname' => $aList->productname,
                        'xau'         => $aList->xau,
                        'comms'       => $aList->xau * $aList->fee,
                    );
                }

                $salesmanStore = $this->app->orderStore()->searchView()->select(['productname', 'productid', 'salespersonname', 'fee']);
                $salesmanStore->addFieldSum('xau', 'ord_xau')
                    ->where('ord_type', $ordType)
                    //->andWhere('ord_partnerid','IN',$core_partners)
                    ->andWhere('ord_apiversion', 'MANUAL')
                    ->andWhere('createdon', '>=', $startAt->format('Y-m-d H:i:s'))
                    ->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'))
                    ->groupBy(['salespersonname', 'productid']);
                $salesmanList  = $salesmanStore->execute();


                if ($category == 'sell') {
                    $spreadsheet = new Spreadsheet();

                    //1st sheet
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle(strtoupper($getMonthYear));
                    $sheet->setCellValue('A1', '#Customer')->getColumnDimension('A')->setAutoSize(true);
                    $sheet->setCellValue('B1', 'Sales')->getColumnDimension('B')->setAutoSize(true);
                    $sheet->setCellValue('C1', 'prodtype')->getColumnDimension('C')->setAutoSize(true);
                    $sheet->setCellValue('D1', 'product_name')->getColumnDimension('D')->setAutoSize(true);
                    $sheet->setCellValue('E1', 'qtyXAU')->getColumnDimension('E')->setAutoSize(true);
                    $sheet->setCellValue('F1', 'PremiumFee')->getColumnDimension('F')->setAutoSize(true);
                    $sheet->setCellValue('G1', 'comms')->getColumnDimension('G')->setAutoSize(true);

                    $sheet->setCellValue('J1', 'Vendor')->getColumnDimension('J')->setAutoSize(true);
                    $sheet->setCellValue('K1', 'Sales')->getColumnDimension('K')->setAutoSize(true);
                    $sheet->setCellValue('L1', 'prodtype')->getColumnDimension('L')->setAutoSize(true);
                    $sheet->setCellValue('M1', 'product_name')->getColumnDimension('M')->setAutoSize(true);
                    $sheet->setCellValue('N1', 'qtyXAU')->getColumnDimension('N')->setAutoSize(true);
                    $sheet->setCellValue('O1', 'PremiumFee')->getColumnDimension('O')->setAutoSize(true);
                    $sheet->setCellValue('P1', 'comms')->getColumnDimension('P')->setAutoSize(true);
                    $sheet->getStyle("A1:P1")->getFont()->setBold(true);
                    $j = 1;
                    foreach ($orderList as $aItem) {
                        $comms = $aItem->xau * $aItem->fee;
                        $totalSumXau += $aItem->xau;
                        $totalSumFee += $aItem->fee;
                        $totalSumComms += $comms;
                        $j++;
                        $sheet->setCellValue('A' . $j, $aItem->partnername)->getColumnDimension('A')->setAutoSize(true);
                        $sheet->setCellValue('B' . $j, $aItem->salespersonname)->getColumnDimension('B')->setAutoSize(true);
                        $sheet->setCellValue('C' . $j, $aItem->productid)->getColumnDimension('C')->setAutoSize(true);
                        $sheet->setCellValue('D' . $j, $aItem->productname)->getColumnDimension('D')->setAutoSize(true);
                        $sheet->setCellValue('E' . $j, $aItem->xau)->getColumnDimension('E')->setAutoSize(true);
                        $sheet->getStyle('E' . $j)->getNumberFormat()->setFormatCode('0.000');
                        $sheet->setCellValue('F' . $j, $aItem->fee)->getColumnDimension('F')->setAutoSize(true);
                        $sheet->getStyle('F' . $j)->getNumberFormat()->setFormatCode('0.00');
                        $sheet->setCellValue('G' . $j, $comms)->getColumnDimension('G')->setAutoSize(true);
                        $sheet->getStyle('G' . $j)->getNumberFormat()->setFormatCode('0.00');
                    }
                    $sheet->setCellValue('E' . ($j + 1), $totalSumXau)->getColumnDimension('E')->setAutoSize(true);
                    $sheet->getStyle('E' . ($j + 1))->getNumberFormat()->setFormatCode('0.000');

                    $sheet->setCellValue('F' . ($j + 1), $totalSumFee)->getColumnDimension('F')->setAutoSize(true);
                    $sheet->getStyle('F' . ($j + 1))->getNumberFormat()->setFormatCode('0.00');

                    $sheet->setCellValue('G' . ($j + 1), $totalSumComms)->getColumnDimension('G')->setAutoSize(true);
                    $sheet->getStyle('G' . ($j + 1))->getNumberFormat()->setFormatCode('0.00');

                    $k = 1;
                    foreach ($groupList as $aGroup) {
                        $comms = $aGroup->xau * $aGroup->fee;
                        $totalSumGXau += $aGroup->xau;
                        $totalSumGFee += $aGroup->fee;
                        $totalSumGComms += $comms;
                        $k++;
                        $sheet->setCellValue('J' . $k, $aGroup->partnername)->getColumnDimension('J')->setAutoSize(true);
                        $sheet->setCellValue('K' . $k, $aGroup->salespersonname)->getColumnDimension('K')->setAutoSize(true);
                        $sheet->setCellValue('L' . $k, $aGroup->productid)->getColumnDimension('L')->setAutoSize(true);
                        $sheet->setCellValue('M' . $k, $aGroup->productname)->getColumnDimension('M')->setAutoSize(true); //productname
                        $sheet->setCellValue('N' . $k, $aGroup->xau)->getColumnDimension('N')->setAutoSize(true); //xau
                        $sheet->getStyle('N' . $k)->getNumberFormat()->setFormatCode('0.000');
                        $sheet->setCellValue('O' . $k, $aGroup->fee)->getColumnDimension('O')->setAutoSize(true); //premiumfee
                        $sheet->getStyle('O' . $k)->getNumberFormat()->setFormatCode('0.00');
                        $sheet->setCellValue('P' . $k, $comms)->getColumnDimension('P')->setAutoSize(true); //comms
                        $sheet->getStyle('P' . $k)->getNumberFormat()->setFormatCode('0.00');
                    }
                    $sheet->setCellValue('N' . ($k + 1), $totalSumGXau)->getColumnDimension('N')->setAutoSize(true);
                    $sheet->getStyle('N' . ($k + 1))->getNumberFormat()->setFormatCode('0.000');

                    $sheet->setCellValue('O' . ($k + 1), $totalSumGFee)->getColumnDimension('O')->setAutoSize(true);
                    $sheet->getStyle('O' . ($k + 1))->getNumberFormat()->setFormatCode('0.00');
                    $sheet->setCellValue('P' . ($k + 1), $totalSumGComms)->getColumnDimension('P')->setAutoSize(true);
                    $sheet->getStyle('P' . ($k + 1))->getNumberFormat()->setFormatCode('0.00');
                } else {
                    $spreadsheet = new Spreadsheet();

                    //1st sheet
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle(strtoupper($getMonthYear));
                    $sheet->setCellValue('A1', 'bookingOrderId')->getColumnDimension('A')->setAutoSize(true);
                    $sheet->setCellValue('B1', 'GTP_UserID')->getColumnDimension('B')->setAutoSize(true);
                    $sheet->setCellValue('C1', 'Vendor')->getColumnDimension('C')->setAutoSize(true);
                    $sheet->setCellValue('D1', 'IS_SAPBPCode')->getColumnDimension('D')->setAutoSize(true);
                    $sheet->setCellValue('E1', 'Sales')->getColumnDimension('E')->setAutoSize(true);
                    $sheet->setCellValue('F1', 'prodtype')->getColumnDimension('F')->setAutoSize(true);
                    $sheet->setCellValue('G1', 'product_name')->getColumnDimension('G')->setAutoSize(true);
                    $sheet->setCellValue('H1', 'qtyXAU')->getColumnDimension('H')->setAutoSize(true);
                    $sheet->setCellValue('I1', 'RefineFee')->getColumnDimension('I')->setAutoSize(true);
                    $sheet->setCellValue('J1', 'comms')->getColumnDimension('J')->setAutoSize(true);

                    $sheet->setCellValue('M1', 'Vendor')->getColumnDimension('M')->setAutoSize(true);
                    $sheet->setCellValue('N1', 'Sales')->getColumnDimension('N')->setAutoSize(true);
                    $sheet->setCellValue('O1', 'prodtype')->getColumnDimension('O')->setAutoSize(true);
                    $sheet->setCellValue('P1', 'product_name')->getColumnDimension('P')->setAutoSize(true);
                    $sheet->setCellValue('Q1', 'qtyXAU')->getColumnDimension('Q')->setAutoSize(true);
                    $sheet->setCellValue('R1', 'RefineFee')->getColumnDimension('R')->setAutoSize(true);
                    $sheet->setCellValue('S1', 'comms')->getColumnDimension('S')->setAutoSize(true);
                    $sheet->getStyle("A1:S1")->getFont()->setBold(true);

                    $j = 1;
                    foreach ($orderList as $aItem) {
                        $j++;
                        $partner    = $this->app->partnerStore()->getById($aItem->partnerid);
                        $user       = $this->app->userStore()->getById($aItem->partnerid);
                        $comms      = $aItem->xau * $aItem->fee;
                        $totalSumXau += $aItem->xau;
                        $totalSumFee += $aItem->fee;
                        $totalSumComms += $comms;

                        $sheet->setCellValue('A' . $j, $aItem->orderno)->getColumnDimension('A')->setAutoSize(true);
                        $sheet->setCellValue('B' . $j, $user->username)->getColumnDimension('B')->setAutoSize(true);
                        $sheet->setCellValue('C' . $j, $aItem->partnername)->getColumnDimension('C')->setAutoSize(true);
                        $sheet->setCellValue('D' . $j, $partner->sapcompanybuycode1)->getColumnDimension('D')->setAutoSize(true);
                        $sheet->setCellValue('E' . $j, $aItem->salespersonname)->getColumnDimension('E')->setAutoSize(true);
                        $sheet->setCellValue('F' . $j, $aItem->productid)->getColumnDimension('F')->setAutoSize(true);
                        $sheet->setCellValue('G' . $j, $aItem->productname)->getColumnDimension('G')->setAutoSize(true);
                        $sheet->setCellValue('H' . $j, $aItem->xau)->getColumnDimension('H')->setAutoSize(true);
                        $sheet->getStyle('H' . $j)->getNumberFormat()->setFormatCode('0.000');
                        $sheet->setCellValue('I' . $j, '(' . number_format(abs($aItem->fee), 2, '.', '') . ')')->getColumnDimension('I')->setAutoSize(true);
                        $sheet->getStyle('I' . $j)->getAlignment()->setHorizontal('right');
                        $sheet->setCellValue('J' . $j, '(' . number_format(abs($comms), 2, '.', '') . ')')->getColumnDimension('J')->setAutoSize(true);
                        $sheet->getStyle('J' . $j)->getAlignment()->setHorizontal('right');
                    }

                    $sheet->setCellValue('H' . ($j + 1), $totalSumXau)->getColumnDimension('H')->setAutoSize(true);
                    $sheet->getStyle('H' . ($j + 1))->getNumberFormat()->setFormatCode('0.000');

                    /*$rangeFee = "F2:F".($j+1);
                    $sheet->setCellValue('F'.($j+2),'=SUM('.$rangeFee.')');*/

                    $sheet->setCellValue('I' . ($j + 1), '(' . number_format(abs($totalSumFee), 2, '.', '') . ')')->getColumnDimension('I')->setAutoSize(true);
                    $sheet->getStyle('I' . ($j + 1))->getAlignment()->setHorizontal('right');
                    $sheet->setCellValue('J' . ($j + 1), '(' . number_format(abs($totalSumComms), 2, '.', '') . ')')->getColumnDimension('J')->setAutoSize(true);
                    $sheet->getStyle('J' . ($j + 1))->getAlignment()->setHorizontal('right');

                    $k = 1;
                    foreach ($groupList as $aGroup) {
                        $comms          = $aGroup->xau * $aGroup->fee;
                        $displayComms   = '(' . number_format(abs($comms), 2, '.', '') . ')';
                        $displayFee     = '(' . number_format(abs($aGroup->fee), 2, '.', '') . ')';
                        $totalSumGXau   += $aGroup->xau;
                        $totalSumGFee   += $aGroup->fee;
                        $totalSumGComms += $comms;
                        $displayTotalSumFee   = '(' . number_format(abs($totalSumGFee), 2, '.', '') . ')';
                        $displayTotalSumComms = '(' . number_format(abs($totalSumGComms), 2, '.', '') . ')';
                        $k++;
                        $sheet->setCellValue('M' . $k, $aGroup->partnername)->getColumnDimension('M')->setAutoSize(true);
                        $sheet->setCellValue('N' . $k, $aGroup->salespersonname)->getColumnDimension('N')->setAutoSize(true);
                        $sheet->setCellValue('O' . $k, $aGroup->productid)->getColumnDimension('O')->setAutoSize(true);
                        $sheet->setCellValue('P' . $k, $aGroup->productname)->getColumnDimension('P')->setAutoSize(true); //productname
                        $sheet->setCellValue('Q' . $k, $aGroup->xau)->getColumnDimension('Q')->setAutoSize(true); //xau
                        $sheet->getStyle('Q' . $k)->getNumberFormat()->setFormatCode('0.000');
                        $sheet->setCellValue('R' . $k, $displayFee)->getColumnDimension('R')->setAutoSize(true); //premiumfee
                        $sheet->getStyle('R' . $k)->getAlignment()->setHorizontal('right');
                        $sheet->setCellValue('S' . $k, $displayComms)->getColumnDimension('S')->setAutoSize(true); //comms
                        $sheet->getStyle('S' . $k)->getAlignment()->setHorizontal('right');
                    }
                    $sheet->setCellValue('Q' . ($k + 1), $totalSumGXau)->getColumnDimension('Q')->setAutoSize(true);
                    $sheet->getStyle('Q' . ($k + 1))->getNumberFormat()->setFormatCode('0.000');

                    $sheet->setCellValue('R' . ($k + 1), $displayTotalSumFee)->getColumnDimension('R')->setAutoSize(true);
                    $sheet->getStyle('R' . ($k + 1))->getAlignment()->setHorizontal('right');
                    $sheet->setCellValue('S' . ($k + 1), $displayTotalSumComms)->getColumnDimension('S')->setAutoSize(true);
                    $sheet->getStyle('S' . ($k + 1))->getAlignment()->setHorizontal('right');

                    $l = $k + 5;
                    $sheet->setCellValue('M' . $l, 'Sales')->getColumnDimension('M')->setAutoSize(true);
                    $sheet->setCellValue('N' . $l, 'qtyXAU')->getColumnDimension('N')->setAutoSize(true);
                    $sheet->setCellValue('O' . $l, 'comms')->getColumnDimension('O')->setAutoSize(true);
                    $sheet->setCellValue('P' . $l, 'product_name')->getColumnDimension('P')->setAutoSize(true);
                    $sheet->getStyle("M" . $l . ":P" . $l)->getFont()->setBold(true);

                    foreach ($sales as $key => $aValue) {
                        foreach ($aValue as $key1 => $aValue1) {
                            $l++;
                            foreach ($aValue1 as $key2 => $aValue2) {
                                $productname    = $aValue2['productname'];
                                $xau            = $aValue2['xau'];
                                $comms          = $aValue2['comms'];

                                $totalSValue[$key][$key1]['xau']        += $xau;
                                $totalSValue[$key][$key1]['comms']      += $comms;
                            }

                            $totalSumSXau     += $totalSValue[$key][$key1]['xau'];
                            $totalSumSComms   += $totalSValue[$key][$key1]['comms'];

                            //print_r($totalSValue);
                            $sheet->setCellValue('M' . $l, $key)->getColumnDimension('M')->setAutoSize(true);
                            $sheet->setCellValue('N' . $l, $totalSValue[$key][$key1]['xau'])->getColumnDimension('N')->setAutoSize(true); //xau
                            $sheet->getStyle('N' . $l)->getNumberFormat()->setFormatCode('0.000');
                            $sheet->setCellValue('O' . $l, '(' . number_format(abs($totalSValue[$key][$key1]['comms']), 2, '.', '') . ')')->getColumnDimension('O')->setAutoSize(true); //comms
                            $sheet->getStyle('O' . $l)->getAlignment()->setHorizontal('right');
                            $sheet->setCellValue('P' . $l, $productname)->getColumnDimension('P')->setAutoSize(true);
                        }
                    }
                    $sheet->setCellValue('N' . ($l + 1), $totalSumSXau)->getColumnDimension('N')->setAutoSize(true);
                    $sheet->getStyle('N' . ($l + 1))->getNumberFormat()->setFormatCode('0.000');

                    $sheet->setCellValue('O' . ($l + 1), '(' . number_format(abs($totalSumSComms), 2, '.', '') . ')')->getColumnDimension('O')->setAutoSize(true);
                    $sheet->getStyle('O' . ($l + 1))->getAlignment()->setHorizontal('right');
                }
            }

            $subject        = $reportname;
            $filename       = $reportname . '.xlsx';
            $pathToSave     = $reportpath . $filename;

            $writer = new Xlsx($spreadsheet);
            $writer->save($pathToSave);

            $emailSubject = $reportname;
            $bodyEmail    = "Please find the attached file " . $filename . " for your reference.";
            if ($emailOut) $this->sendNotifyEmailReport($bodyEmail, $subject, $emailList, $pathToSave, $filename);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . "Error to get data", SNAP_LOG_ERROR);
            throw $e;  //rethrow the exception
        }
    }

    public function generateRedemptionReport($partnerid, $reportname, $reportpath)
    {
        /*get partner obj*/
        $partner    = $this->app->partnerStore()->getById($partnerid);

        $redeemArr          = [];
        $weightMinted       = [];
        /*get redemption*/
        $redemption = $this->app->redemptionStore()->searchView()->select()
            ->andWhere('partnerid', $partnerid)
            ->andWhere('status', "IN", [Redemption::STATUS_COMPLETED, Redemption::STATUS_CONFIRMED])
            ->execute();

        if (count($redemption) > 0) {
            foreach ($redemption as $aRedemption) {
                $dateCreated = strtotime($aRedemption->createdon->format('Y-m-d H:i:s'));
                $yearOfDate = date("Y", $dateCreated);

                /*getbranch*/
                $numlength = strlen((string)$aRedemption->branchid);
                if (is_numeric($aRedemption->branchid) && $numlength < 5) $updatebranchcode = str_pad($aRedemption->branchid, 5, '0', STR_PAD_LEFT);
                else $updatebranchcode = $aRedemption->branchid;
                $branches = $partner->getBranch($updatebranchcode);

                $redeemArr[$yearOfDate][$updatebranchcode]['branchname'] = $branches->name;
                $redeemArr[$yearOfDate][$updatebranchcode]['redemptionbranchid'] = $aRedemption->branchid;

                /*get denomination*/
                $items = json_decode($aRedemption->items, true);
                foreach ($items as $aItems) {
                    $redeemArr[$yearOfDate][$updatebranchcode]['denomination'][$aRedemption->type][$aItems['weight']][]    = $aItems['serialnumber'];
                }
            }
        }

        $tabCount = 0;
        $pathToSave = $reportpath . $reportname . '.xlsx';
        $filename = 'FODLYREC_' . $dateFile . '.xlsx';
        $spreadsheet = new Spreadsheet();

        foreach ($redeemArr as $key => $aRedeemArr) {
            $spreadsheet->createSheet();
            $sheet = $spreadsheet->setActiveSheetIndex($tabCount);
            $sheet->setTitle('' . $key . '');

            $sheet->setCellValue('A1', 'No')->getColumnDimension('A')->setAutoSize(true);
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->setCellValue('B1', 'Branch Name')->getColumnDimension('B')->setAutoSize(true);
            $sheet->getStyle('B1')->getFont()->setBold(true);
            $sheet->setCellValue('C1', 'Branch Code')->getColumnDimension('C')->setAutoSize(true);
            $sheet->getStyle('C1')->getFont()->setBold(true);
            $sheet->setCellValue('D1', 'Type')->getColumnDimension('D')->setAutoSize(true);
            $sheet->getStyle('D1')->getFont()->setBold(true);
            $sheet->setCellValue('E1', '1g')->getColumnDimension('E')->setWidth(10);
            $sheet->getStyle('E1')->getFont()->setBold(true);
            $sheet->setCellValue('F1', '5g')->getColumnDimension('F')->setWidth(10);
            $sheet->getStyle('F1')->getFont()->setBold(true);
            $sheet->setCellValue('G1', '10g')->getColumnDimension('G')->setWidth(10);
            $sheet->getStyle('G1')->getFont()->setBold(true);
            $sheet->setCellValue('H1', '50g')->getColumnDimension('H')->setWidth(10);
            $sheet->getStyle('H1')->getFont()->setBold(true);
            $sheet->setCellValue('I1', '100g')->getColumnDimension('I')->setWidth(10);
            $sheet->getStyle('I1')->getFont()->setBold(true);
            $sheet->setCellValue('J1', '1000g')->getColumnDimension('J')->setWidth(10);
            $sheet->getStyle('J1')->getFont()->setBold(true);

            $i = 1;
            $count = 0;
            foreach ($aRedeemArr as $key2 => $aValue) {
                $i++;
                $count++;
                if ($i != 2) $i += 3;
                $otc            = $i;
                $delivery       = $otc + 1;
                $appointment    = $delivery + 1;
                $sheet->setCellValue('A' . $i, $count)->getColumnDimension('A')->setAutoSize(true);
                $sheet->setCellValue('B' . $i, $aValue['branchname'])->getColumnDimension('B')->setAutoSize(true);
                $sheet->setCellValue('C' . $i, $key2)->getColumnDimension('C')->setAutoSize(true);
                $sheet->getStyle('C' . $i)->getAlignment()->setHorizontal('right');
                $sheet->setCellValue('D' . $otc, 'OTC')->getColumnDimension('D')->setAutoSize(true);
                $sheet->getStyle('D' . $otc)->getFont()->setBold(true);
                $sheet->setCellValue('D' . $delivery, 'Delivery')->getColumnDimension('D')->setAutoSize(true);
                $sheet->getStyle('D' . $delivery)->getFont()->setBold(true);
                $sheet->setCellValue('D' . $appointment, 'Appointment')->getColumnDimension('D')->setAutoSize(true);
                $sheet->getStyle('D' . $appointment)->getFont()->setBold(true);
                foreach ($aValue['denomination'] as $key => $aDenom) {
                    if ($key == 'Branch') $value = $otc;
                    if ($key == 'Delivery') $value = $delivery;
                    if ($key == 'Appointment') $value = $appointment;

                    $sheet->setCellValue('E' . $value, count($aDenom['1.000000']))->getColumnDimension('E')->setWidth(10);
                    $sheet->setCellValue('F' . $value, count($aDenom['5.000000']))->getColumnDimension('F')->setWidth(10);
                    $sheet->setCellValue('G' . $value, count($aDenom['10.000000']))->getColumnDimension('G')->setWidth(10);
                    $sheet->setCellValue('H' . $value, count($aDenom['50.000000']))->getColumnDimension('H')->setWidth(10);
                    $sheet->setCellValue('I' . $value, count($aDenom['100.000000']))->getColumnDimension('I')->setWidth(10);
                    $sheet->setCellValue('J' . $value, count($aDenom['1000.000000']))->getColumnDimension('J')->setWidth(10);
                }
            }
            $tabCount++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pathToSave);
    }

    public function checkTransactionReconcile($transaction, $date, $partner, $flag, $filename = null, $reportpath)
    {
        $getDate            = date('Y-m-d h:i:s', $date);
        $createDateStart    = date_create($getDate);
        // 2023-04-25: tested on localhost,, the start date and end date will not take data of yesterday. i.e:
        // current date : 2023-04-25
        // start date : 2023-04-22
        // end date : 2023-04-23
        // data of 2023-04-24 is missing.
        // therefore change the date to -2 day for start date and -1 day for end date
        // $modifyDateStart    = date_modify($createDateStart,"-3 day");
        $modifyDateStart    = date_modify($createDateStart, "-2 day");
        $startDate          = date_format($modifyDateStart, "Y-m-d 16:00:00");
        $createDateEnd      = date_create($getDate);
        // $modifyDateEnd      = date_modify($createDateEnd,"-2 day");
        $modifyDateEnd      = date_modify($createDateEnd, "-1 day");
        $endDate            = date_format($modifyDateEnd, "Y-m-d 15:59:59");
        $dateFile           = date('dmY', $date);

        foreach ($transaction as $atransaction) {
            $ordernoSap = $atransaction['RefNo1'];
            $transactionNum = $atransaction['DocNum'];
            $orderItem = $this->app->orderStore()
                ->searchView()
                ->select()
                ->where('partnerid', $partner['id'])
                ->andWhere('orderno', $ordernoSap)
                ->andWhere('createdon', '>=', $startDate)
                ->andWhere('createdon', '<=', $endDate)
                ->execute();
            if (count($orderItem) > 0) {
                foreach ($orderItem as $anOrder) {
                    $gtpMatchArr = array(
                        'gtp_transactioncode' => $anOrder->orderno,
                        'gtp_partnerrefid' => $anOrder->partnerrefid,
                        'gtp_type' => $anOrder->type,
                        'gtp_transactiongram' => $anOrder->xau,
                        'gtp_transactiongoldprice' => $anOrder->price,
                        'gtp_transactionamount' => $anOrder->amount,
                        'gtp_status' => $anOrder->statusname,
                        'gtp_uuid' => $anOrder->uuid,
                        'gtp_transactiontime' => $anOrder->createdon->format('Y-m-d His')
                    );
                    $reconArr[$ordernoSap] = array_merge($atransaction, $gtpMatchArr);
                    $anOrder->reconciledsaprefno = $transactionNum;
                    $update = $this->app->orderStore()->save($anOrder);
                    if (!$update) {
                        $bodyEmail .= "Unable to update reconciledsaprefno details to order - $anOrder->orderno.\n";
                        //throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to update reconciledsaprefno details to order '.$aItem->id]);
                    }
                }
            } else {
                $buybackCase = explode('-', $ordernoSap);
                $buybackItem = $this->app->buybackStore()
                    ->searchView()
                    ->select()
                    ->where('partnerid', $partner['id'])
                    ->andWhere('buybackno', $buybackCase[0])
                    ->andWhere('createdon', '>=', $startDate)
                    ->andWhere('createdon', '<=', $endDate)
                    ->execute();
                if (count($buybackItem) > 0) {
                    foreach ($buybackItem as $anBuyback) {
                        $gtpMatchArr = array(
                            'gtp_transactioncode' => $anBuyback->buybackno,
                            'gtp_partnerrefid' => $anBuyback->partnerrefno,
                            'gtp_type' => 'Buyback',
                            'gtp_transactiongram' => $anBuyback->totalweight,
                            'gtp_transactiongoldprice' => $anBuyback->price,
                            'gtp_transactionamount' => $anBuyback->totalamount,
                            'gtp_status' => $anBuyback->statusname,
                            'gtp_uuid' => '',
                            'gtp_transactiontime' => $anBuyback->createdon->format('Y-m-d His')
                        );
                        $reconArr[$ordernoSap] = array_merge($atransaction, $gtpMatchArr);

                        $mergeSapRefNo = $ordernoSap . '-' . $transactionNum;
                        if (strpos($anBuyback->reconciledsaprefno, $mergeSapRefNo) !== false) {
                            $anBuyback->reconciledsaprefno = $anBuyback->reconciledsaprefno;
                        } else {
                            $anBuyback->reconciledsaprefno .= $mergeSapRefNo . ', ';
                        }
                        $update = $this->app->buybackStore()->save($anBuyback);
                        if (!$update) {
                            $bodyEmail .= "Unable to update reconciledsaprefno details to buyback - $anBuyback->buybackno.\n";
                            //throw \Snap\api\exception\GeneralException::fromTransaction([], [ 'message' => 'Unable to update reconciledsaprefno details to order '.$aItem->id]);
                        }
                    }
                } else {
                    $unmatchSap[] = $atransaction;
                }
            }
        }

        /*Get gtp transaaction unmatch*/
        $orderItem = $this->app->orderStore()
            ->searchView()
            ->select()
            ->where('partnerid', $partner['id'])
            ->andWhere('reconciledsaprefno', 0)
            ->andWhere('createdon', '>=', $startDate)
            ->andWhere('createdon', '<=', $endDate)
            ->execute();
        if (count($orderItem) > 0) {
            foreach ($orderItem as $anOrder) {
                $gtpUnmatch[] = array(
                    'gtp_transactioncode' => $anOrder->orderno,
                    'gtp_partnerrefid' => $anOrder->partnerrefid,
                    'gtp_type' => $anOrder->type,
                    'gtp_transactiongram' => $anOrder->xau,
                    'gtp_transactiongoldprice' => $anOrder->price,
                    'gtp_transactionamount' => $anOrder->amount,
                    'gtp_status' => $anOrder->statusname,
                    'gtp_uuid' => $anOrder->uuid,
                    'gtp_transactiontime' => $anOrder->createdon->format('Y-m-d His')
                );
            }
        }

        /*Get gtp transaaction unmatch*/
        $buybackItem = $this->app->buybackStore()
            ->searchView()
            ->select()
            ->where('partnerid', $partner['id'])
            ->andWhere('reconciledsaprefno', 0)
            ->andWhere('createdon', '>=', $startDate)
            ->andWhere('createdon', '<=', $endDate)
            ->execute();
        if (count($buybackItem) > 0) {
            foreach ($buybackItem as $anBuyback) {
                $gtpUnmatch[] = array(
                    'gtp_transactioncode' => $anBuyback->buybackno,
                    'gtp_partnerrefid' => $anBuyback->partnerrefno,
                    'gtp_type' => 'Buyback',
                    'gtp_transactiongram' => $anBuyback->totalweight,
                    'gtp_transactiongoldprice' => $anBuyback->price,
                    'gtp_transactionamount' => $anBuyback->totalamount,
                    'gtp_status' => $anBuyback->statusname,
                    'gtp_uuid' => '',
                    'gtp_transactiontime' => $anBuyback->createdon->format('Y-m-d His')
                );
            }
        }

        //$reportpath = $this->app->getConfig()->{'gtp.ftp.report'};
        if ($partnername != null) $partnername = "_" . $partner['sapcode'] . "_";

        //$pathToSave = $reportpath.'RECON_SO_PO_'.$partner['sapcode'].'_'.$dateFile.'.xlsx';
        $pathToSave = $reportpath . $filename . '.xlsx';

        ///$filename = 'RECON_SO_PO_'.$partner['sapcode'].'_'.$dateFile.'.xlsx';
        $filename = $filename . '.xlsx';

        //$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        //$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
        //$spreadsheet = $reader->load($pathToSave);

        $spreadsheet = new Spreadsheet();
        //$spreadsheet->createSheet();
        //$sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Match Reconcile SAP and GTP');
        $sheet->setCellValue('A1', 'SAP DocDate')->getColumnDimension('A')->setAutoSize(true);
        $sheet->setCellValue('B1', 'SAP DocNum')->getColumnDimension('B')->setAutoSize(true);
        $sheet->setCellValue('C1', 'SAP DocType')->getColumnDimension('C')->setAutoSize(true);
        $sheet->setCellValue('D1', 'SAP CardCode')->getColumnDimension('D')->setAutoSize(true);
        $sheet->setCellValue('E1', 'SAP CardName')->getColumnDimension('E')->setAutoSize(true);
        $sheet->setCellValue('F1', 'SAP Price')->getColumnDimension('F')->setAutoSize(true);
        $sheet->setCellValue('G1', 'SAP Quantity')->getColumnDimension('F')->setAutoSize(true);
        $sheet->setCellValue('H1', 'SAP LineTotal')->getColumnDimension('H')->setAutoSize(true);
        $sheet->setCellValue('I1', 'SAP NumAtCard')->getColumnDimension('I')->setAutoSize(true);
        $sheet->setCellValue('J1', 'SAP RefNo')->getColumnDimension('J')->setAutoSize(true);
        $sheet->setCellValue('K1', 'SAP RefNo1')->getColumnDimension('K')->setAutoSize(true);
        $sheet->setCellValue('L1', 'SAP RefNo3')->getColumnDimension('L')->setAutoSize(true);
        $sheet->setCellValue('M1', 'GTP DateTime')->getColumnDimension('M')->setAutoSize(true);
        $sheet->setCellValue('N1', 'GTP PartnerRefId')->getColumnDimension('N')->setAutoSize(true);
        $sheet->setCellValue('O1', 'GTP OrderNo')->getColumnDimension('O')->setAutoSize(true);
        $sheet->setCellValue('P1', 'GTP Price')->getColumnDimension('P')->setAutoSize(true);
        $sheet->setCellValue('Q1', 'GTP Quantity')->getColumnDimension('Q')->setAutoSize(true);
        $sheet->setCellValue('R1', 'GTP Amount')->getColumnDimension('R')->setAutoSize(true);
        $sheet->setCellValue('S1', 'GTP PriceId')->getColumnDimension('S')->setAutoSize(true);
        $sheet->setCellValue('T1', 'GTP Status')->getColumnDimension('T')->setAutoSize(true);

        $j = 1;
        foreach ($reconArr as $key => $aData) {
            $j++;
            $sheet->setCellValue('A' . $j, $aData['DocDate'])->getColumnDimension('A')->setAutoSize(true);
            $sheet->setCellValue('B' . $j, $aData['DocNum'])->getColumnDimension('B')->setAutoSize(true);
            $sheet->setCellValue('C' . $j, $aData['DocType'])->getColumnDimension('C')->setAutoSize(true);
            $sheet->setCellValue('D' . $j, $aData['CardCode'])->getColumnDimension('D')->setAutoSize(true);
            $sheet->setCellValue('E' . $j, $aData['CardName'])->getColumnDimension('E')->setAutoSize(true);
            $sheet->getStyle('F' . $j)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('F' . $j, $aData['Price'])->getColumnDimension('F')->setAutoSize(true);
            $sheet->getStyle('G' . $j)->getNumberFormat()->setFormatCode('0.000');
            $sheet->setCellValue('G' . $j, $aData['Quantity'])->getColumnDimension('G')->setAutoSize(true);
            $sheet->getStyle('H' . $j)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('H' . $j, $aData['LineTotal'])->getColumnDimension('H')->setAutoSize(true);
            $sheet->setCellValue('I' . $j, $aData['NumAtCard'])->getColumnDimension('I')->setAutoSize(true);
            $sheet->setCellValue('J' . $j, $aData['RefNo'])->getColumnDimension('J')->setAutoSize(true);
            $sheet->setCellValue('K' . $j, $aData['RefNo1'])->getColumnDimension('K')->setAutoSize(true);
            $sheet->setCellValue('L' . $j, $aData['RefNo3'])->getColumnDimension('L')->setAutoSize(true);
            $sheet->setCellValue('M' . $j, $aData['gtp_transactiontime'])->getColumnDimension('M')->setAutoSize(true);
            $sheet->setCellValue('N' . $j, " " . $aData['gtp_partnerrefid'])->getColumnDimension('N')->setAutoSize(true);
            $sheet->setCellValue('O' . $j, $aData['gtp_transactioncode'])->getColumnDimension('O')->setAutoSize(true);
            $sheet->getStyle('P' . $j)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('P' . $j, $aData['gtp_transactiongoldprice'])->getColumnDimension('P')->setAutoSize(true);
            $sheet->getStyle('Q' . $j)->getNumberFormat()->setFormatCode('0.000');
            $sheet->setCellValue('Q' . $j, $aData['gtp_transactiongram'])->getColumnDimension('Q')->setAutoSize(true);
            $sheet->getStyle('R' . $j)->getNumberFormat()->setFormatCode('0.00');
            $sheet->setCellValue('R' . $j, $aData['gtp_transactionamount'])->getColumnDimension('R')->setAutoSize(true);
            $sheet->setCellValue('S' . $j, $aData['gtp_uuid'])->getColumnDimension('S')->setAutoSize(true);
            $sheet->setCellValue('T' . $j, $aData['gtp_status'])->getColumnDimension('T')->setAutoSize(true);
        }

        /*create new tab*/
        $spreadsheet->createSheet();
        $sheet2 = $spreadsheet->setActiveSheetIndex(1);
        $sheet2->setTitle('Unmatch Transaction SAP');
        $sheet2->setCellValue('A1', 'SAP DocDate')->getColumnDimension('A')->setAutoSize(true);
        $sheet2->setCellValue('B1', 'SAP DocNum')->getColumnDimension('B')->setAutoSize(true);
        $sheet2->setCellValue('C1', 'SAP DocType')->getColumnDimension('C')->setAutoSize(true);
        $sheet2->setCellValue('D1', 'SAP CardCode')->getColumnDimension('D')->setAutoSize(true);
        $sheet2->setCellValue('E1', 'SAP CardName')->getColumnDimension('E')->setAutoSize(true);
        $sheet2->setCellValue('F1', 'SAP Price')->getColumnDimension('F')->setAutoSize(true);
        $sheet2->setCellValue('G1', 'SAP Quantity')->getColumnDimension('G')->setAutoSize(true);
        $sheet2->setCellValue('H1', 'SAP LineTotal')->getColumnDimension('H')->setAutoSize(true);
        $sheet2->setCellValue('I1', 'SAP NumAtCard')->getColumnDimension('I')->setAutoSize(true);
        $sheet2->setCellValue('J1', 'SAP RefNo')->getColumnDimension('J')->setAutoSize(true);
        $sheet2->setCellValue('K1', 'SAP RefNo1')->getColumnDimension('K')->setAutoSize(true);
        $sheet2->setCellValue('L1', 'SAP RefNo3')->getColumnDimension('L')->setAutoSize(true);

        $k = 1;
        foreach ($unmatchSap as $key => $aData) {
            $k++;
            $sheet2->setCellValue('A' . $k, $aData['DocDate'])->getColumnDimension('A')->setAutoSize(true);
            $sheet2->setCellValue('B' . $k, $aData['DocNum'])->getColumnDimension('B')->setAutoSize(true);
            $sheet2->setCellValue('C' . $k, $aData['DocType'])->getColumnDimension('C')->setAutoSize(true);
            $sheet2->setCellValue('D' . $k, $aData['CardCode'])->getColumnDimension('D')->setAutoSize(true);
            $sheet2->setCellValue('E' . $k, $aData['CardName'])->getColumnDimension('E')->setAutoSize(true);
            $sheet2->getStyle('F' . $j)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->setCellValue('F' . $k, $aData['Price'])->getColumnDimension('F')->setAutoSize(true);
            $sheet2->getStyle('G' . $j)->getNumberFormat()->setFormatCode('0.000');
            $sheet2->setCellValue('G' . $k, $aData['Quantity'])->getColumnDimension('G')->setAutoSize(true);
            $sheet2->getStyle('H' . $k)->getNumberFormat()->setFormatCode('0.00');
            $sheet2->setCellValue('H' . $k, $aData['LineTotal'])->getColumnDimension('H')->setAutoSize(true);
            $sheet2->setCellValue('I' . $k, $aData['NumAtCard'])->getColumnDimension('I')->setAutoSize(true);
            $sheet2->setCellValue('J' . $k, $aData['RefNo'])->getColumnDimension('J')->setAutoSize(true);
            $sheet2->setCellValue('K' . $k, $aData['RefNo1'])->getColumnDimension('K')->setAutoSize(true);
            $sheet2->setCellValue('L' . $k, $aData['RefNo3'])->getColumnDimension('L')->setAutoSize(true);
        }

        /*create new tab*/
        $spreadsheet->createSheet();
        $sheet3 = $spreadsheet->setActiveSheetIndex(2);
        $sheet3->setTitle('Unmatch Transaction GTP');
        $sheet3->setCellValue('A1', 'GTP DateTime')->getColumnDimension('A')->setAutoSize(true);
        $sheet3->setCellValue('B1', 'GTP PartnerRefId')->getColumnDimension('B')->setAutoSize(true);
        $sheet3->setCellValue('C1', 'GTP OrderNo')->getColumnDimension('C')->setAutoSize(true);
        $sheet3->setCellValue('D1', 'GTP Price')->getColumnDimension('D')->setAutoSize(true);
        $sheet3->setCellValue('E1', 'GTP Quantity')->getColumnDimension('E')->setAutoSize(true);
        $sheet3->setCellValue('F1', 'GTP Amount')->getColumnDimension('F')->setAutoSize(true);
        $sheet3->setCellValue('G1', 'GTP PriceId')->getColumnDimension('G')->setAutoSize(true);
        $sheet3->setCellValue('H1', 'GTP Status')->getColumnDimension('H')->setAutoSize(true);

        $l = 1;
        foreach ($gtpUnmatch as $key => $aData) {
            $l++;
            $sheet3->setCellValue('A' . $l, $aData['gtp_transactiontime'])->getColumnDimension('A')->setAutoSize(true);
            $sheet3->setCellValue('B' . $l, " " . $aData['gtp_partnerrefid'])->getColumnDimension('B')->setAutoSize(true);
            $sheet3->setCellValue('C' . $l, $aData['gtp_transactioncode'])->getColumnDimension('C')->setAutoSize(true);
            $sheet3->getStyle('D' . $l)->getNumberFormat()->setFormatCode('0.00');
            $sheet3->setCellValue('D' . $l, $aData['gtp_transactiongoldprice'])->getColumnDimension('D')->setAutoSize(true);
            $sheet3->getStyle('E' . $l)->getNumberFormat()->setFormatCode('0.000');
            $sheet3->setCellValue('E' . $l, $aData['gtp_transactiongram'])->getColumnDimension('E')->setAutoSize(true);
            $sheet3->getStyle('F' . $l)->getNumberFormat()->setFormatCode('0.00');
            $sheet3->setCellValue('F' . $l, $aData['gtp_transactionamount'])->getColumnDimension('F')->setAutoSize(true);
            $sheet3->setCellValue('G' . $l, $aData['gtp_uuid'])->getColumnDimension('G')->setAutoSize(true);
            $sheet3->setCellValue('H' . $l, $aData['gtp_statusname'])->getColumnDimension('H')->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($pathToSave);

        //send/notify email.
        $sendTo = $this->app->getConfig()->{'mygtp.email.sendtoreconcile'};
        //$emailConfig = 'sendtoreconcile';
        $emailSubject = $flag . ' RECONCILE SO/PO SAP ' . $dateFile;
        //$emailSubject = $filename;
        $bodyEmail .= "\nSUCCESSFULLY GENERATED.\n";
        //$sendEmail = $this->app->apiManager()->sendNotifyEmail($bodyEmail,$emailSubject,$emailConfig,$pathToSave,$filename);

        $this->sendNotifyEmailReport($bodyEmail, $emailSubject, $sendTo, $pathToSave, $filename);
    }
}
