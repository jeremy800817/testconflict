<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\InputException;
use Snap\object\MyLedger;
use Snap\sqlrecorder;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam(azam@@silverstream.my)
 * @version 1.0
 */
class mygoldstatementHandler extends CompositeHandler
{
    protected $currentStore;

    function __construct(App $app)
    {
        parent::__construct('/root/mbsb;/root/bmmb;/root/ktp;/root/bsn;/root/alrajhi;/root/posarrahnu', 'goldtransaction');

        $this->mapActionToRights('list', 'list;/root/mbsb/profile/list;/root/bmmb/profile/list;/root/ktp/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list');
        $this->mapActionToRights('exportExcel', 'export;/root/mbsb/profile/list;/root/bmmb/profile/list;/root/ktp/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list');
        $this->mapActionToRights('monthlystoragefee', 'list;/root/mbsb/profile/list;/root/bmmb/profile/list;/root/ktp/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list');
        $this->mapActionToRights('exportMonthlyStorageFeeExcel', 'list;/root/mbsb/profile/list;/root/bmmb/profile/list;/root/ktp/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list');

        $this->app = $app;
        $this->currentStore = $app->myledgerStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    public function onPreListing($objects, $params, $records)
    {
        $accHolder = $this->app->myaccountholderStore()->searchTable()
            ->select(['id', 'accountholdercode', 'fullname'])
            ->where('id', $params['accountholderid'])
            ->one();

        /** @var \Snap\manager\MyGtpStatementManager $statementManager */
        $statementManager = $this->app->mygtpstatementManager();

        if (1 == $params['page']) {

            if (!isset($params["filter"])) {
                $openingBalance = $statementManager->fillRecord($accHolder, 'Opening Balance');
                array_unshift($records, $openingBalance);
            } elseif (isset($params["filter"])) {

                $filters = json_decode($params['filter'], true);
                $dateStart = $filters[0]['value'][0];
                $dateEnd   = $filters[0]['value'][1];

                list($dateStart, $dateEnd, $untilDate) = $this->prepareStatementDates($dateStart, $dateEnd);
                $openingBalance = $statementManager->getStatementOpeningBalance($accHolder, $untilDate, null);
                array_unshift($records, $openingBalance);
            }
        } else {
            if ($record = reset($records)) {
                $ledgerStore = $this->currentStore;
                $ledgerHdl = $ledgerStore->searchView(false);
                $ledger = $ledgerHdl->select()
                                    ->addFieldSum('credit', 'credit')
                                    ->addFieldSum('debit', 'debit')
                                    ->addFieldSum('amountin', 'amountin')
                                    ->addFieldSum('amountout', 'amountout')
                                    ->where('accountholderid', $params['accountholderid'])
                                    ->where('status', MyLedger::STATUS_ACTIVE)
                                    ->where('id', '<', $record['id'])
                                    ->one();

                $records[0]['xaubalance'] = ($records[0]['credit'] - $records[0]['debit']) + ($ledger['credit'] - $ledger['debit']);
                $records[0]['amountbalance'] = ($records[0]['amountin'] - $records[0]['amountout']) + ($ledger['amountin'] - $ledger['amountout']);
            }
        }

        $records = $statementManager->formatStatementRecords($records, null);

        return $records;
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        $sqlHandle->andWhere(function ($q) use ($params) {
            $q->where('accountholderid', $params['accountholderid']);
        });

        $sqlHandle->where('status', MyLedger::STATUS_ACTIVE);

        return array($params, $sqlHandle, $fields);
    }

    function exportExcel($app, $params)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = $this->app->mygtpstatementManager();

        $accHolder = $this->app->myaccountholderStore()->searchTable()
            ->select(['id', 'partnerid', 'accountholdercode', 'fullname', 'mykadno'])
            ->where('id', $params['accountholderid'])
            ->one();

        $partner = $this->app->partnerStore()->getById($accHolder->partnerid);

        $dateRange = json_decode($params["daterange"]);
        $dateStart = $dateRange->startDate;
        $dateEnd   = $dateRange->endDate;
        list($dateStart, $dateEnd, $untilDate) = $this->prepareStatementDates($dateStart, $dateEnd);

        $records   = $this->getStatementRecords($accHolder, $dateStart, $dateEnd, $untilDate);
        $titleDate = 'Transaction Listing For Individual Customers as At ' . $dateEnd->format('d F Y');
        $totalRows = count($records);
        $startRow  = 7 + 1;
        $endRow    = $startRow + $totalRows;

        $filename = '##PARNERNAME##_STATEMENT_##ACCOUNTHOLDERCODE##_##DATE##';
        $tags     = ['##PARNERNAME##','##ACCOUNTHOLDERCODE##', '##DATE##'];
        $fillers  = [$partner->name, $accHolder->accountholdercode, $dateEnd->format('Y-m-d')];
        $filename = str_replace($tags, $fillers, $filename);
        $html     = $statementManager->getStatementAsHtml($accHolder, $titleDate, $records, true, true);

        $statementManager->exportHtmlAsExcel($html, $filename, function ($spreadsheet) use ($startRow, $endRow) {

            $headerRow = $startRow - 1;
            $spreadsheet->getActiveSheet()->getStyle("A{$headerRow}:Z{$headerRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle("A{$headerRow}:Z{$headerRow}")->getFont()->setBold(true);
            $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $spreadsheet->getActiveSheet()->getStyle("D{$startRow}:D{$endRow}")->getNumberFormat()->setFormatCode('0.000');
            $spreadsheet->getActiveSheet()->getStyle("E{$startRow}:G{$endRow}")->getNumberFormat()->setFormatCode('0.000');
            $spreadsheet->getActiveSheet()->getStyle("H{$startRow}:J{$endRow}")->getNumberFormat()->setFormatCode('0.00');

            $spreadsheet->getActiveSheet()->getStyle("E{$endRow}:J{$endRow}")->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
            $spreadsheet->getActiveSheet()->getStyle("E{$endRow}:J{$endRow}")->getBorders()->getTop()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);

            foreach (range('A', 'C') as $columnID) {
                $spreadsheet->getActiveSheet()->getColumnDimension($columnID)->setWidth(20);
            }

            foreach (range('D', 'Z') as $columnID) {
                $spreadsheet->getActiveSheet()->getColumnDimension($columnID)->setWidth(16);
            }

            return $spreadsheet;
        });
    }

    public function exportPdf($app, $params)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = $this->app->mygtpstatementManager();

        $accHolder = $this->app->myaccountholderStore()->searchTable()
            ->select(['id', 'partnerid', 'accountholdercode', 'fullname', 'mykadno'])
            ->where('id', $params['accountholderid'])
            ->one();

        $dateRange = json_decode($params["daterange"]);
        $dateStart = $dateRange->startDate;
        $dateEnd   = $dateRange->endDate;
        list($dateStart, $dateEnd, $untilDate) = $this->prepareStatementDates($dateStart, $dateEnd);

        $titleDate = 'Transaction Listing For Individual Customers as At ' . $dateEnd->format('d F Y');
        $records   = $this->getStatementRecords($accHolder, $dateStart, $dateEnd, $untilDate);
        $content   = $statementManager->exportHtmlAsPdfString($statementManager->getStatementAsHtml($accHolder, $titleDate, $records, true, true));
        $filename  = '##ACCOUNTHOLDERCODE##_##DATE##';
        $tags      = ['##ACCOUNTHOLDERCODE##', '##DATE##'];
        $fillers   = [$dateEnd->format('d F Y'), $accHolder->accountholdercode];
        $filename  = str_replace($tags, $fillers, $filename);

        header('Content-Type: application/pdf');
        header("Content-Length: " . strlen($content));
        header('Content-disposition: inline; filename="' . $filename . '.pdf"');
        header('Cache-Control: public, must-revalidate, max-age=3600');
        header('Pragma: public');

        echo $content;
    }

    function prepareStatementDates($dateStart, $dateEnd)
    {
        $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone());
        $dateEnd   = new \DateTime($dateEnd, $this->app->getUserTimezone());

        $dateStart = new \DateTime($dateStart->format('Y-m-d 00:00:00'), $this->app->getUserTimezone());
        $dateEnd   = new \DateTime($dateEnd->format('Y-m-d 23:59:59'), $this->app->getUserTimezone());

        $untilDate = clone $dateStart;
        $untilDate = new \DateTime($untilDate->format('Y-m-d 23:59:59'), $this->app->getUserTimezone());
        $untilDate->modify("-1 day");

        return [$dateStart, $dateEnd, $untilDate];
    }

    function getStatementRecords($accHolder, $dateStart, $dateEnd, $untilDate, $openingBalance = true, $condition = null)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = $this->app->mygtpstatementManager();

        $records = $statementManager->getStatementRecords($accHolder, $dateStart, $dateEnd, $condition);

        if ($openingBalance) {
            $openingBalance = $statementManager->getStatementOpeningBalance($accHolder, $untilDate);
            array_unshift($records, $openingBalance);
        }

        return $statementManager->formatStatementRecords($records);
    }
}
