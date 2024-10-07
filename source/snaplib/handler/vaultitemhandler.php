<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use \Snap\store\dbdatastore as DbDatastore;
use Snap\App;
use \Snap\object\Documents;
use \Snap\object\VaultItem;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Html;
/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Calvin(calvin.thien@ace2u.com)
 * @version 1.0
 */
class vaultitemhandler extends CompositeHandler
{
    function __construct(App $app)
    {

        //parent::__construct('/root/mbb;/root/bmmb;/root/go;/root/one;', 'vault');
        
        $this->mapActionToRights('list', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('requestForTransferItem', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('requestForTransferItemMultiple', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('requestForTransferItemMultipleConfirmation', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('requestTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('cancelTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('confirmTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('returnToHq', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');   
        $this->mapActionToRights('returnToHqForce', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');      
        $this->mapActionToRights('returnItem', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('requestActivateItemForTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('approvePendingItemForTransfer', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('confirmReturn', '/root/common/vault/edit;/root/mbb/vault/edit;/root/bmmb/vault/edit;/root/go/vault/edit;/root/one/vault/edit;/root/onecall/vault/edit;/root/air/vault/edit;/root/mcash/vault/edit;/root/ktp/vault/edit;/root/bsn/vault/edit;/root/alrajhi/vault/edit;/root/posarrahnu/vault/edit;/root/bursa/vault/edit;');
        $this->mapActionToRights('detailview', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getSummary', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getLocation', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getTransferLocationsStart', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getTransferLocationsIntermediate', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getTransferLocationsEnd', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getTransferLocationsAll', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getStatusCount', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getPendingDocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('createdocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('getPrintDocuments', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        $this->mapActionToRights('exportVaultList', '/root/common/vault/list;/root/mbb/vault/list;/root/bmmb/vault/list;/root/go/vault/list;/root/one/vault/list;/root/onecall/vault/list;/root/air/vault/list;/root/mcash/vault/list;/root/ktp/vault/list;/root/bsn/vault/list;/root/alrajhi/vault/list;/root/posarrahnu/vault/list;/root/bursa/vault/list;');
        // $this->mapActionToRights('getCommonVaultInfo', '/root/common/vault/list;');
        $this->mapActionToRights('getCommonVaultInfo', '/root/common/vault/list;/root/mbb/vault/list;/root/common/vault/list;');
        $this->mapActionToRights('exportexcel', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/vault/list;');
        $this->mapActionToRights('infoTable', '/root/common/vault/list;/root/mbb/vault/list;/root/ktp/vault/list;');
        $this->mapActionToRights('exportexcel2', '/root/bmmb/goldtransaction/list;/root/go/goldtransaction/list;/root/one/goldtransaction/list;/root/onecall/goldtransaction/list;/root/air/goldtransaction/list;/root/mcash/goldtransaction/list;/root/toyyib/goldtransaction/list;/root/ktp/goldtransaction/list;/root/kopetro/goldtransaction/list;/root/kopttr/goldtransaction/list;/root/pkbaffi/goldtransaction/list;/root/bumira/goldtransaction/list;/root/nubex/goldtransaction/list;/root/hope/goldtransaction/list;/root/mbsb/goldtransaction/list;/root/red/goldtransaction/list;/root/kodimas/goldtransaction/list;/root/kgoldaffi/goldtransaction/list;/root/koponas/goldtransaction/list;/root/wavpay/goldtransaction/list;/root/noor/goldtransaction/list;/root/bsn/goldtransaction/list;/root/alrajhi/goldtransaction/list;/root/posarrahnu/goldtransaction/list;/root/waqaf/goldtransaction/list;/root/igold/goldtransaction/list;/root/kasih/goldtransaction/list;/root/bursa/vault/list;');
        $this->mapActionToRights('vaultdata', '/all/access');
        $this->mapActionToRights('VaultDataAPI', '/all/access');
        $this->mapActionToRights('generateExportFile2', '/all/access');

        $this->app = $app;
        $vaultitemStore = $app->vaultitemfactory();

        $this->currentStore = $vaultitemStore;
        $this->addChild(new ext6gridhandler($this, $vaultitemStore, 1));
    }

    /*
    function onPreQueryListing($params,$sqlHandle, $records) {

        print_r($params);
        $params['partnername'] = 'mibvaultitem';
        // Check for the partner of vault
        if('bmmbvaultitem' == $params['partnername']){
            $partnerid=$this->app->getConfig()->{'gtp.bmmb.partner.id'};   
        } else if ('mibvaultitem' == $params['partnername']){
            $partnerid=$this->app->getConfig()->{'gtp.mib.partner.id'};   
        } else {
            throw new \Exception("Partner id does not exists");
        }
       

        //$partnerid=$this->app->getConfig()->{'gtp.mib.partner.id'};   
        if($partnerid==null){            
            throw new \Exception("Partner id does not exists");
        }     
        $sqlHandle->andWhere('partnerid', $partnerid);      
        return array($params, $sqlHandle, $records);   
    }*/

    /**
     * function to massage data before listing
     **/

    function exportexcel()
    {
        // if (isset($params['partnercode']) && 'BMMB' == $params['partnercode']) {
        //     $modulename = 'BMMB_SUMMARY_LISTINGS_AT_';
        // }

        // die($_GET['header']);

        // $header = json_decode($params["header"]);

        // $dateRange = json_decode($params["daterange"]);
        // $dateEnd = new \DateTime('now', $this->app->getUserTimezone());


        if (isset($_GET['partnercode']) && 'BMMB' == $_GET['partnercode']) {
            $modulename = 'BMMB_SUMMARY_LISTINGS_AT_';
        }

        $header = json_decode($_GET["header"]);

        $dateRange = json_decode($_GET["daterange"]);
        $dateEnd = new \DateTime('now', $this->app->getUserTimezone());


        $modulename = $modulename . $dateEnd->format('Y-m-d');
        // $dateEnd = $dateEnd->format('Y-m-d 23:59:59');

        // $reportingManager = $this->app->reportingManager();


        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);

        // $reportingManager->generateAccountReport($this->currentStore, $header, $dateStart, $dateEnd, $modulename, false, null, null, null, false);
    }

    function exportexcel2($app, $params)
    {
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // Start Query
        // OTC
        if (isset($_GET['partnercode']) && 'BSN' === $_GET['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }
        else if (isset($_GET['partnercode']) && 'ALRAJHI' === $_GET['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        }
        else if (isset($_GET['partnercode']) && 'POSARRAHNU' === $_GET['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
        }

        if (isset($_GET['partnercode']) && 'BMMB' == $_GET['partnercode']) {
            $modulename = 'BMMB_SUMMARY_LISTINGS_AT_';
        }

        $partner = $this->app->partnerStore()->getById($partnerid);
        $partnercode = $partner->code;  
    
        $responseData = $this->VaultDataAPI($partnercode);

        $vaultdata = [];

        foreach ($responseData->partner_Gold as $index => $data) {
            $allocatedon = $data->allocatedon;
            $createdon = $data->createdon;
            $modifiedon = $data->modifiedon;

            $return = [
                'id' => $data->id,
                'partnerid' => $data->partnerid,
                'vaultlocationid' => $data->vaultlocationid,
                'productid' => $data->productid,
                'weight' => $data->weight,
                'brand' => $data->brand,
                'serialno' => $data->serialno,
                'allocated' => $data->allocated,
                'allocatedon' =>  date('Y-m-d H:i:s', strtotime($allocatedon->date)),
                'utilised' => $data->utilised,
                'movetovaultlocationid' => $data->movetovaultlocationid,
                'moverequestedon' => date('Y-m-d H:i:s', strtotime($data->moverequestedon)),
                'movecompletedon' => date('Y-m-d H:i:s', strtotime($data->movecompletedon)),
                'returnedon' =>  date('Y-m-d H:i:s', strtotime($data->returnedon)),
                'newvaultlocationid' => $data->newvaultlocationid,
                'deliveryordernumber' => $data->deliveryordernumber,
                'sharedgv' => $data->sharedgv,
                'createdon' =>  date('Y-m-d H:i:s', strtotime($createdon->date)),
                'createdby' => $data->createdby,
                'modifiedon' =>  date('Y-m-d H:i:s', strtotime($modifiedon->date)),
                'modifiedby' => $data->modifiedby,
                'status' => $data->status,
                'vaultlocationname' => $data->vaultlocationname,
                'vaultlocationtype' => $data->vaultlocationtype,
                'vaultlocationdefault' => $data->vaultlocationdefault,
                'movetolocationpartnerid' => $data->movetolocationpartnerid,
                'movetovaultlocationname' => $data->movetovaultlocationname,
                'newvaultlocationname' => $data->newvaultlocationname,
                'partnername' => $data->partnername,
                'partnercode' => $data->partnercode,
                'productname' => $data->productname,
                'productcode' => $data->productcode,
                'createdbyname' => $data->createdbyname,
                'modifiedbyname' => $data->modifiedbyname                
            ];
            $vaultdata[] = $return;
        }

        $header = json_decode($_GET["header"]);
        $dateRange = json_decode($_GET["daterange"]);
        $dateEnd = new \DateTime('now', $this->app->getUserTimezone());

        $startDate = $dateRange->startDate;
        $endDate = $dateRange->endDate;
        
        $modulename = $modulename . $dateEnd->format('Y-m-d');

        $startDate = date('Y-m-d H:i:s', strtotime($startDate));
        $endDate = date('Y-m-d H:i:s', strtotime($endDate));
        
        $filteredData = array_filter($vaultdata, function ($item) use ($startDate, $endDate) {
            $createdDate = $item['createdon'];
            return ($createdDate >= $startDate && $createdDate <= $endDate);
        });
        
        $this->generateExportFile2($filteredData,$modulename);
    }

    function generateExportFile2($data, $modulename)
    {
        // print_r($data);
        // exit;

        $datenow = \Snap\common::convertUTCToUserDatetime(new \DateTime());
        $datenow = $datenow->format('Y-m-d_H-i-s');
        $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
        if ($developmentEnv) {
            $environtmentFileName = '_DEMO_';
        } else {
            $environtmentFileName = '_';
        }

        $filename = 'ACE' . $environtmentFileName . $modulename . '_EXPORT_' . $datenow . '.xlsx';
        $spreadsheet = new Spreadsheet();
        $date = new \DateTime();
        $sheet = $spreadsheet->getActiveSheet();
        $spreadsheet->getActiveSheet()->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'Report generated as at '. $date->format('Y-m-d H:i:s'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13.5);
        $sheet->setCellValue('A5', 'Vault Location');
        $sheet->setCellValue('B5', 'Serial No');
        $sheet->setCellValue('C5', 'ID');
        $sheet->setCellValue('D5', 'Allocated');
        $sheet->setCellValue('E5', 'Allocated On');
        $sheet->setCellValue('F5', 'Move to Vault Location');
        $sheet->setCellValue('G5', 'Move Request On');
        $sheet->setCellValue('H5', 'Move Completed On');
        $sheet->setCellValue('I5', 'Returned On');
        $sheet->setCellValue('J5', 'New Vault Location');
        $sheet->setCellValue('K5', 'Delivery Order No');
        $sheet->setCellValue('L5', 'Brand');
        $sheet->setCellValue('M5', 'Created On');
        $sheet->setCellValue('N5', 'Modified On');
        $sheet->setCellValue('O5', 'Status');
        $sheet->setCellValue('P5', 'Product Code');

        $sheet->getStyle('A5')->getFont()->setBold(true);
        $sheet->getStyle('B5')->getFont()->setBold(true);
        $sheet->getStyle('C5')->getFont()->setBold(true);
        $sheet->getStyle('D5')->getFont()->setBold(true);
        $sheet->getStyle('E5')->getFont()->setBold(true);
        $sheet->getStyle('F5')->getFont()->setBold(true);
        $sheet->getStyle('G5')->getFont()->setBold(true);
        $sheet->getStyle('H5')->getFont()->setBold(true);
        $sheet->getStyle('I5')->getFont()->setBold(true);
        $sheet->getStyle('J5')->getFont()->setBold(true);
        $sheet->getStyle('K5')->getFont()->setBold(true);
        $sheet->getStyle('L5')->getFont()->setBold(true);
        $sheet->getStyle('M5')->getFont()->setBold(true);
        $sheet->getStyle('N5')->getFont()->setBold(true);
        $sheet->getStyle('O5')->getFont()->setBold(true);
        $sheet->getStyle('P5')->getFont()->setBold(true);

        $rows = 6;

        foreach ($data as $details) {

            $allocatedValue = $details['allocated'];
            $statusValue = $details['status'];

            if ($allocatedValue == 1 && $statusValue == 1) {
                $output = 'Allocated Active';
            } elseif (($statusValue == 3 || $statusValue == 4) && $allocatedValue == 1) {
                $output = 'Allocated Inactive';
            } elseif ($statusValue == 2 && $allocatedValue == 1) {
                $output = 'Allocated Transferring';
            } elseif ($allocatedValue == 1) {
                $output = 'Unallocated Active';
            } elseif (($statusValue == 3 || $statusValue == 4) && $allocatedValue == 0) {
                $output = 'Unallocated Inactive';
            } elseif ($statusValue == 2 && $allocatedValue == 0) {
                $output = 'Unallocated Transferring';
            } elseif ($statusValue == 0 && $allocatedValue == 0) {
                $output = 'Unallocated Pending';
            } else {
                $output = '';
            }

            $sheet->setCellValue('A' . $rows, $details['vaultlocationname']);
            $sheet->setCellValue('B' . $rows, $details['serialno']);
            $sheet->setCellValue('C' . $rows, $details['id']);
            $sheet->setCellValue('D' . $rows, $allocatedValue == 1 ? 'Yes' : 'No');
            $sheet->setCellValue('E' . $rows, $details['allocatedon']);
            $sheet->setCellValue('F' . $rows, $details['movetovaultlocationname']);
            $sheet->setCellValue('G' . $rows, $details['moverequestedon']);
            $sheet->setCellValue('H' . $rows, $details['movecompletedon']);
            $sheet->setCellValue('I' . $rows, date('Y-m-d H:i:s', strtotime($details['returnedon'])));
            $sheet->setCellValue('J' . $rows, $details['newvaultlocationname']);
            $sheet->setCellValue('K' . $rows, $details['deliveryordernumber']);
            $sheet->setCellValue('L' . $rows, $details['brand']);
            $sheet->setCellValue('M' . $rows, $details['createdon']);
            $sheet->setCellValue('N' . $rows, $details['modifiedon']);
            $sheet->setCellValue('O' . $rows, $output);
            $sheet->setCellValue('P' . $rows, $details['productcode']);


            $rows++;
        }

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
        ob_end_clean();
        $writer->save('php://output');
        exit();
    }

    function onPreListing($objects, $params, $records)
    {

        $app = App::getInstance();

        $userType = $this->app->getUserSession()->getUser()->type;

        foreach ($records as $key => $record) {
            // Acquire progress percentage.
            if (!$records[$key]['movetovaultlocationname']) {
                $records[$key]['movetovaultlocationname'] = "-";
            }
            if (!$records[$key]['newvaultlocationname']) {
                $records[$key]['newvaultlocationname'] = "-";
            }
        }

        return $records;
    }

    function vaultdata($app, $params)
    {
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;
        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
           
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
           
        } else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.air.partner.id'};
           
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};
           
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.nubex.partner.id'};
        }
        //added on 13/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.ktp.partner.id'};
           
        }
        // OTC
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        }
        else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
        }
    
        $partner = $this->app->partnerStore()->getById($partnerid);
        $partnercode = $partner->code;  
    
        $responseData = $this->VaultDataAPI($partnercode);

        $vaultdata = [];

        foreach ($responseData->partner_Gold as $index => $data) {
            $allocatedon = $data->allocatedon;
            $createdon = $data->createdon;
            $modifiedon = $data->modifiedon;

            $return = [
                'id' => $data->id,
                'partnerid' => $data->partnerid,
                'vaultlocationid' => $data->vaultlocationid,
                'productid' => $data->productid,
                'weight' => $data->weight,
                'brand' => $data->brand,
                'serialno' => $data->serialno,
                'allocated' => $data->allocated,
                'allocatedon' =>  date('Y-m-d H:i:s', strtotime($allocatedon->date)),
                'utilised' => $data->utilised,
                'movetovaultlocationid' => $data->movetovaultlocationid,
                'moverequestedon' => date('Y-m-d H:i:s', strtotime($data->moverequestedon)),
                'movecompletedon' => date('Y-m-d H:i:s', strtotime($data->movecompletedon)),
                'returnedon' =>  date('Y-m-d H:i:s', strtotime($data->returnedon)),
                'newvaultlocationid' => $data->newvaultlocationid,
                'deliveryordernumber' => $data->deliveryordernumber,
                'sharedgv' => $data->sharedgv,
                'createdon' =>  date('Y-m-d H:i:s', strtotime($createdon->date)),
                'createdby' => $data->createdby,
                'modifiedon' =>  date('Y-m-d H:i:s', strtotime($modifiedon->date)),
                'modifiedby' => $data->modifiedby,
                'status' => $data->status,
                'vaultlocationname' => $data->vaultlocationname,
                'vaultlocationtype' => $data->vaultlocationtype,
                'vaultlocationdefault' => $data->vaultlocationdefault,
                'movetolocationpartnerid' => $data->movetolocationpartnerid,
                'movetovaultlocationname' => $data->movetovaultlocationname,
                'newvaultlocationname' => $data->newvaultlocationname,
                'partnername' => $data->partnername,
                'partnercode' => $data->partnercode,
                'productname' => $data->productname,
                'productcode' => $data->productcode,
                'createdbyname' => $data->createdbyname,
                'modifiedbyname' => $data->modifiedbyname                
            ];
            $vaultdata[] = $return;
        }
        return json_encode($vaultdata);
    }

    public function VaultDataAPI($partnercode){

        try{
            $postdata = [
                "version" => "1.0my",
		        "merchant_id" =>$partnercode,
		        "projectbase" => $this->app->getConfig()->{'projectBase'},
                "action" => "vault_store_inventory"
            ];
        
            $options = [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
            ];

	    $proxyUrl = $this->app->getConfig()->{'gtp.server.proxyurl'};
                        if ($proxyUrl) {
                                $options['proxy'] = $proxyUrl;
                        }
 
            $client         = new \GuzzleHttp\Client($options);
            $url            = $this->app->getConfig()->{'mygtp.api.url'};
            // $url            = "http://localhost:8081/gtp/source/snapapp/mygtp.php";
            $response       = $client->post($url, ['json' => $postdata]);

            return json_decode($response->getBody()->getContents());
        }
        catch(\Throwable $e){
            echo $e->getMessage();
            exit;
        }
    }

    function getSummary($app, $params)
    {

        // Get User Partner Id
        $userPartnerId = $this->app->getUserSession()->getUser()->partnerid;

        // Check for the partner of vault
        if ('bmmb' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
        } else if ('mib' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerid)->where('weight', 1000)->execute();
        } else if ('bursa' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerid)->where('weight', 1000)->execute();
        }
        //OTC Start
        else if ('BSN' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }
        else if ('ALRAJHI' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};            
        }
        else if ('POSARRAHNU' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};            
        } 
        else if ($userPartnerId != '') {
            $partnerid = $userPartnerId;
        }
        else {
            throw new \Exception("Partner id does not exists");
        }
        //OTC end        
        if (!$items) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerid)->execute();
        }
        //$withdocount=$withoutdocount=$transferringcount=$returncount=0;
        // Initialize count
        $countInAceHq = $countInAceG4s = $countInMbbG4s = $withDoCount = $withoutDoCount = $transferringCount = $total = 0;

        // Init Values
        $withDoCount = 0;
        $withoutDoCount = 0;
        $transferringCount = 0;
        //Initialize Serial Number Counter
        $withDoSerialNumbers = array();
        $withoutDoSerialNumbers = array();
        $transferringSerialNumbers = array();

        if (count($items) > 0) {
            foreach ($items as $aItem) {
                if ($aItem->vaultlocationid &&  1 == $aItem->status) {
                    $withDoCount++;
                    $withDoSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                if (!$aItem->vaultlocationid && null == $aItem->deliveryordernumber && 1 == $aItem->status) {
                    $withoutDoCount++;
                    $withoutDoSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                //if($aItem->allocated == 1){$withdocount++;}
                //if($aItem->status == 1 && $aItem->allocated==1){$withoutdocount++;};
                if (2 == $aItem->status && 1 == $aItem->allocated) {
                    $transferringCount++;

                    if (0 == $aItem->vaultlocationid) {
                        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();
                        foreach ($vaultLocations as $vaultLocation) {
                            $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name, 'type' => $vaultLocation->type);
                            $from = $vaultLocation->name;
                        }
                    } else {
                        $fromVaultLocation = $this->app->vaultlocationStore()->getById($aItem->vaultlocationid);
                        $from =  $fromVaultLocation->name;
                    }

                    $toVaultLocation = $this->app->vaultlocationStore()->getById($aItem->movetovaultlocationid);

                    $transferringSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'from' => $from, 'to' => $toVaultLocation->name, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                //if(($aItem->status == 3 || $aItem->status == 4) && $aItem->allocated==0){$returncount++;};	 

                if (1 == $aItem->vaultlocationid) {
                    $countInAceHq++;
                    $aceHqSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                if (2 == $aItem->vaultlocationid) {
                    $countInAceG4s++;
                    $aceG4sSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                if (3 == $aItem->vaultlocationid) {
                    $countInMbbG4s++;
                    $mbbG4sSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
                if (9 == $aItem->vaultlocationid) {
                    $countInMbbG4s++;
                    $mbbG4sSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
            }
            $total = $countInAceHq + $countInAceG4s + $countInMbbG4s;
        }

        // Add customer holding fields
        $partner = $this->app->partnerStore()->getById($partnerid);
        $return = $this->app->bankVaultManager()->getUtilizationValues($partner);

        // Calculate balance
        // Trim 6dp to 3dp for prd display
        $vaultAmount = number_format((float)$return['vaultamount'], 3, '.', '');
        $totalUtilization = number_format((float)$return['totalutilization'], 3, '.', '');
        $withoutDoXau =  number_format((float)$return['withoutdoxau'], 3, '.', '');


        echo json_encode([
            'success' => true,
            'withdocount' => $withDoCount,
            'withoutdocount' => $withoutDoCount,
            'transferringcount' => $transferringCount,
            //'returncount'=> $returnCount,
            'hqcount' => $countInAceHq,
            'aceg4scount' => $countInAceG4s,
            'mbbg4scount' => $countInMbbG4s,
            'total' => $total,
            'withdoserialnumbers' => $withDoSerialNumbers,
            'withoutdoserialnumbers' => $withoutDoSerialNumbers,
            'transferringserialnumbers' => $transferringSerialNumbers,
            'acehqserialnumbers' => $aceHqSerialNumbers,
            'aceg4sserialnumbers' => $aceG4sSerialNumbers,
            'mbbg4sserialnumbers' => $mbbG4sSerialNumbers,
            'totalserialnumbers' => $totalSerialNumbers,
            'userpartnerid' => $userPartnerId,
            'vaultAmount' => $vaultAmount,
            'totalUtilization' => $totalUtilization,
            'withoutDoXau' => $withoutDoXau,
        ]);
    }


    public function getLocation()
    {
        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
        $locations = array();
        foreach ($vaultLocations as $vaultLocation) {
            $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name, 'type' => $vaultLocation->type);
        }
        echo json_encode(array('location' => $locations));
    }

    public function getTransferLocationsStart($app, $params)
    {
        //$mbbpartnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};

        $locations = array();
        if ('bmmb' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
        } else if ('mib' == $params['origintype']) {

            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
        } else if ('bursa' == $params['origintype']) {

            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }else if ('common' == $params['origintype']) {
            // Do nothing, skip exception throw for common dgv
        } else {
            throw new \Exception("Partner id does not exists");
        }

        // Add intermediate to modules that have intermediate point
        if ('common' == $params['origintype'] || 'mib' == $params['origintype']) {
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('type', 'Intermediate')->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
        }
        if ('bursa' == $params['origintype']) {
            $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('type', 'Intermediate')->execute();
            foreach ($vaultLocations as $vaultLocation) {
                $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name);
            }
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_name', 'BURSA G4S Rack')->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
        }

        if ('bmmb' == $params['origintype'] || 'mib' == $params['origintype']) {
            $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('type', 'End')->andWhere('partnerid', $partnerid)->execute();

            foreach ($vaultLocations as $vaultLocation) {
                $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name);
            }
        }


        echo json_encode(array('locations' => $locations));
    }

    public function getTransferLocationsIntermediate($app, $params)
    {

        if ('bmmb' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
        } else if ('mib' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
        } else if ('bursa' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        } else if ('common' == $params['origintype']) {
            // Do nothing, skip exception throw for common dgv
        } else {
            throw new \Exception("Partner id does not exists");
        }

        $locations = array();

        // If partner has endpoint, give end
        if ('bmmb' == $params['origintype'] || 'mib' == $params['origintype']) {
            // $partnerid=$this->app->getConfig()->{'gtp.bmmb.partner.id'};   
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name, 'type' => $moreVaultLocation->type);
            }
        }
        // Default
        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();

        foreach ($vaultLocations as $vaultLocation) {
            $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name, 'type' => $vaultLocation->type);
        }
        if ('bursa' == $params['origintype']) {
            // $partnerid=$this->app->getConfig()->{'gtp.bmmb.partner.id'};   
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name, 'type' => $moreVaultLocation->type);
            }

            $additionalVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($additionalVaultLocations as $additionalVaultLocation) {
                $locations[] = array('id' => $additionalVaultLocation->id, 'name' => $additionalVaultLocation->name, 'type' => $additionalVaultLocation->type);
            }
        }

        echo json_encode(array('locations' => $locations));
    }

    public function getTransferLocationsEnd($app, $params)
    {

        $locations = array();
        if ('bmmb' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};

            $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();

            foreach ($vaultLocations as $vaultLocation) {
                $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name);
            }
        } else if ('mib' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
        } else if ('bursa' == $params['origintype']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            //v$vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();
            $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
            foreach ($vaultLocations as $vaultLocation) {
                $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name);
            }
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('id', 9)->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
        } else {
            throw new \Exception("Partner id does not exists");
        }


        echo json_encode(array('locations' => $locations));
    }

    public function getTransferLocationsAll($app, $params)
    {

        $locations = array();

        // Get all locations not associated with any partner id
        $vaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Start')->execute();

        foreach ($vaultLocations as $vaultLocation) {
            $locations[] = array('id' => $vaultLocation->id, 'name' => $vaultLocation->name);
        }

        // Get Partner Associated locations
        if ('BMMB' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('GO' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('ONE' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('MIB' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('BURSA' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $moreVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'Intermediate')->execute();
            foreach ($moreVaultLocations as $moreVaultLocation) {
                $locations[] = array('id' => $moreVaultLocation->id, 'name' => $moreVaultLocation->name);
            }
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        }
        // added 12/01/2022
        // start
        else if ('KTP' == $params['partnercode']) {
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', 'PKB@UAT')->execute();
            $partnerid = array();
            foreach ($partners as $partner) {
                array_push($partnerid, $partner->id);
            }
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', 'IN', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('BSN' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('ALRAJHI' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        } else if ('POSARRAHNU' == $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
            $partnerVaultLocations = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $partnerid)->execute();
            foreach ($partnerVaultLocations as $partnerVaultLocation) {
                $locations[] = array('id' => $partnerVaultLocation->id, 'name' => $partnerVaultLocation->name);
            }
        }
        //end
        else {
            throw new \Exception("Partner id does not exists");
        }


        echo json_encode(array('locations' => $locations));
    }
    /*
    function getSummary($app,$params){
        $items = $this->app->vaultitemStore()->searchTable()->select()->execute();
        $countInAceHq=$countInAceG4s=$countInMbbG4s=$total=0;
        if (count($items) > 0){
            foreach($items as $aItem){
               if($aItem->vaultlocationid==1)$countInAceHq++;
               if($aItem->vaultlocationid==2)$countInAceG4s++;
               if($aItem->vaultlocationid==3)$countInMbbG4s++;
            }
            $total=$countInAceHq+$countInAceG4s+$countInMbbG4s;
        }      
        echo json_encode([ 'success' => true,'allocatedcount' => $allocatedcount,'availablecount' => $availablecount,'onrequestcount' => $onrequestcount,'returncount'=>$returncount]);  
        //echo json_encode([ 'success' => true,'hqcount' => $countInAceHq,'aceg4scount' => $countInAceG4s,'mbbg4scount' => $countInMbbG4s,'total'=>$total]);  
    }*/

    function requestForTransferItem($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', [$params['serialno']])->execute();
            if ($items) {
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location = $locationStore->searchTable()->select()->where('id', $params['vaultto'])->one();
                try {
                    $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location);
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }


    function requestForTransferItemMultiple($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer')  || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            // Pending MIB 
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location = $locationStore->searchTable()->select()->where('id', $params['vaultto'])->one();
                try {
                    $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location, $with_confirm_no_more_pending_extra_clicking = true); // why previously is requestMoveItemToLocationBmmb
                    if ($return) {
                        if ($params['documentdateon']) {
                            $doc_date = new \DateTime($params['documentdateon'], $this->app->getUserTimezone());
                        } else {
                            $doc_date = new \DateTime('now', $this->app->getUserTimezone());
                        }
                        $returns = $app->bankvaultManager()->markItemsArrivedAtLocation($items, $doc_date);
                    }
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError  $e) {
                    // Handle exceptions
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                } catch (\Exception  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function requestForTransferItemMultipleConfirmation($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer')) {
            // Pending MIB 
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location = $locationStore->searchTable()->select()->where('id', $params['vaultto'])->one();
                try {
                    if ($params['documentdateon']) {
                        $doc_date = new \DateTime($params['documentdateon'], $this->app->getUserTimezone());
                    } else {
                        $doc_date = new \DateTime('now', $this->app->getUserTimezone());
                    }
                    $confirmation = true; // bmmb need confirmation
                    $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location, false, $confirmation, $doc_date); // why previously is requestMoveItemToLocationBmmb

                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError  $e) {
                    // Handle exceptions
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                } catch (\Exception  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function requestTransfer($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', [$params['serialno']])->execute();
            if ($items) {
                $vaultItemStore = $app->vaultitemStore();
                $locationStore = $app->vaultLocationStore();
                $location1 = $locationStore->searchTable()->select()->where('name', 'ACE G4S Rack')->one();
                $location2 = $locationStore->searchTable()->select()->where('name', 'MBB G4S Rack')->one();
                try {
                    $return = $app->bankvaultManager()->requestMoveItemToLocation($items, $location2);
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }
    function cancelTransfer($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                try {
                    $return = $app->bankvaultManager()->cancelMoveRequest($items);
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }
    /*
    function confirmTransfer($app,$params){
        if($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer')){  
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();            
            if($items){
                try{
                    $return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);
					// if ($return[0]->vaultlocationid == 2){
					// 	// return new vaultlocationid as G4S
					// 	// TO-DO - FIX , TEMP - GUI NOT SUPPORT NOW
                    //     $documents = $app->DocumentsManager()->createPendingDocuments($return[0], 'TransferNote', 'VaultItem');
                    // }
                    echo json_encode([ 'success' => true]);   
                }catch(\Snap\api\exception\VaultItemError $e){
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
                }                              
            }else{
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);  
            } 
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);             
        }      
    }*/

    function confirmTransfer($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                try {
                    //$return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);

                    if (true == $params['ismib']) {
                        // Get mib partner id
                        $mibpartnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};

                        //if Mbb, mark items
                        // create vault init to check mbb in order to deallocate bar
                        $mibLocation = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $mibpartnerid)->one();

                        // If bar is coming from mib
                        // get endlocation for selected records
                        $item = $items[0];

                        // If endlocation is same as vaultlocation id                 
                        // Additional update if its mib
                        if ($mibLocation->id === $item->vaultlocationid) {
                            $return = $app->bankvaultManager()->markItemsArrivedAtLocationDeallocated($items);
                        } else {
                            $return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);
                        }
                    } else {
                        // Do other modules
                        $return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);
                    }
                    // if ($return[0]->vaultlocationid == 2){
                    // 	// return new vaultlocationid as G4S
                    // 	// TO-DO - FIX , TEMP - GUI NOT SUPPORT NOW
                    //     $documents = $app->DocumentsManager()->createPendingDocuments($return[0], 'TransferNote', 'VaultItem');
                    // }

                    // If its bmmb module, return updated balance
                    if (true == $params['isbmmbvaultbalance']) {
                        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
                            $partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
                        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
                            $partnerID = $app->getConfig()->{'gtp.bursa.partner.id'};
                        }
                       
                        $partner = $this->app->partnerStore()->getById($partnerID);
                        $bmmbbalance = $this->app->mygtptransactionManager()->getPartnerBalances($partner);

                        echo json_encode(['success' => true, 'balance' => $bmmbbalance]);
                    } else {
                        echo json_encode(['success' => true]);
                    }
                } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function returnToHq($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                try {
                    //$return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);

                    if (true == $params['ismib']) {
                        // Get mib partner id
                        $mibpartnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};

                        //if Mbb, mark items
                        // create vault init to check mbb in order to deallocate bar
                        $mibLocation = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_type', 'End')->andWhere('stl_partnerid', $mibpartnerid)->one();

                        // If bar is coming from mib
                        // get endlocation for selected records
                        $item = $items[0];

                        // If endlocation is same as vaultlocation id                 
                        // Additional update if its mib
                        if ($mibLocation->id === $item->vaultlocationid) {
                            $return = $app->bankvaultManager()->mark_Item_Deallocated_And_Returned_Directly($items);
                            echo json_encode(['success' => true]);
                        } else {
                            throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'Vault item must be at partner location']);
                        }
                    } else {
                        // Do other modules
                        throw \Snap\api\exception\VaultItemError::fromTransaction([], ['message' => 'This function is only available for MBB']);
                    }
                } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                    // Handle general case
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function returnToHqForce($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/transfer') || $this->app->hasPermission('/root/bmmb/vault/transfer') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/transfer') || $this->app->hasPermission('/root/one/vault/transfer') || $this->app->hasPermission('/root/common/vault/transfer') || $this->app->hasPermission('/root/ktp/vault/transfer') || $this->app->hasPermission('/root/bsn/vault/transfer') || $this->app->hasPermission('/root/alrajhi/vault/transfer') || $this->app->hasPermission('/root/posarrahnu/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {

                // Add new check to function
                if (true == $params['direct']) {
                    // Do duirect call without transaction
                    try {
                        $force = true;
                        $return = $app->bankvaultManager()->mark_Item_Deallocated_And_Returned_Directly_Without_Transaction($items, $force);
                        echo json_encode(['success' => true]);
                    } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                        // Handle general case
                        echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                    } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                        echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                    }
                } else {
                    // Original Function
                    try {
                        $force = true;
                        $return = $app->bankvaultManager()->mark_Item_Deallocated_And_Returned_Directly($items, $force);
                        echo json_encode(['success' => true]);
                    } catch (\Snap\api\exception\MyGtpPartnerInsufficientGoldBalance  $e) {
                        // Handle general case
                        echo json_encode(['success' => false, 'field' => '', 'errorMessage' =>  'Partner balance is not sufficient']);
                    } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                        echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                    }
                }
                // End

            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function returnItem($app, $params)
    {
        if ($this->app->hasPermission('/root/mbb/vault/return') || $this->app->hasPermission('/root/bmmb/vault/return') || $this->app->hasPermission('/root/bursa/vault/transfer') || $this->app->hasPermission('/root/go/vault/return') || $this->app->hasPermission('/root/one/vault/return') || $this->app->hasPermission('/root/ktp/vault/return')  || $this->app->hasPermission('/root/bsn/vault/return') || $this->app->hasPermission('/root/alrajhi/vault/return') || $this->app->hasPermission('/root/posarrahnu/vault/return')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            try {
                $return = $app->bankvaultManager()->markItemReturned($items);

                // If its bmmb module, return updated balance
                if (true == $params['isbmmbvaultbalance']) {
                    $partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
                    $partner = $this->app->partnerStore()->getById($partnerID);
                    $bmmbbalance = $this->app->mygtptransactionManager()->getPartnerBalances($partner);

                    echo json_encode(['success' => true, 'balance' => $bmmbbalance]);
                } else {
                    echo json_encode(['success' => true]);
                }
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function requestActivateItemForTransfer($app, $params)
    {
        if ($this->app->hasPermission('/root/bmmb/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                try {
                    $return = $app->bankvaultManager()->requestActivateItemForTransfer($items);
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    function approvePendingItemForTransfer($app, $params)
    {
        if ($this->app->hasPermission('/root/bmmb/vault/transfer')) {
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', $params['serialno'])->execute();
            if ($items) {
                try {
                    $return = $app->bankvaultManager()->approvePendingItemForTransfer($items);
                    echo json_encode(['success' => true]);
                } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
                    echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
                }
            } else {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        }
    }

    public function getStatusCount($app, $params)
    {
        $userPartnerID = $this->app->getUserSession()->getUser()->partnerid;
        $partnerID = $app->getConfig()->{'gtp.bmmb.partner.id'};
        if ($partnerID == null) {
            throw new \Exception("Partner id does not exists");
        }

        //Initialize Serial Number Counter
        $logicalSerialNumbers = array();
        $countInAceHQSerialNumbers = array();
        $countInBMMBg4sSerialNumbers = array();

        $items = $this->app->vaultitemStore()->searchTable()->select()->where('partnerid', $partnerID)->where('weight', 1000)->execute();
        $logical = $countInAceHQ = $countInBMMBg4s = $total = $overall = 0;
        if (count($items) > 0) {
            foreach ($items as $aItem) {
                if (1 == $aItem->vaultlocationid) {
                    $countInAceHQ++;
                    $countInAceHQSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $overallSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                }

                if (4 == $aItem->vaultlocationid) {
                    $countInBMMBg4s++;
                    $countInBMMBg4sSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'deliveryordernumber' => $aItem->deliveryordernumber,  'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $totalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $overallSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                }

                if (
                    $aItem->productid != null &&
                    $aItem->weight != null &&
                    $aItem->partnerid != null &&
                    $aItem->serialno != null &&
                    $aItem->deliveryordernumber == null &&
                    $aItem->allocated == 0 &&
                    ($aItem->allocatedon == '0000-00-00 00:00:00' || $aItem->allocatedon == null) &&
                    $aItem->status == 1
                ) {
                    $logical++;
                    $logicalSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                    $overallSerialNumbers[] = array('id' => $aItem->id, 'name' => $aItem->serialno, 'allocatedon' => $aItem->allocatedon != "0000-00-00 00:00:00" ? $aItem->allocatedon->format('Y-m-d H:i:s') : "",);
                };
            }
            $total = $countInAceHQ + $countInBMMBg4s;
            $overall = $logical + $countInAceHQ + $countInBMMBg4s;
        }

        $partner = $this->app->partnerStore()->getById($partnerID);
        $return = $this->app->mygtptransactionManager()->getPartnerBalances($partner);

        // Calculate balance
        $vaultAmountBmmb = $return['vaultamount'];
        $totalCustomerHoldingBmmb = $return['totalcustomerholding'];
        $totalBalanceBmmb = $return['totalbalance'];
        $pendingTransactionBmmb = $return['pendingtransaction'];

        echo json_encode([
            'success' => true,
            'logicalCount' => $logical,
            'hqCount' => $countInAceHQ,
            'bmmbG4Scount' => $countInBMMBg4s,
            'total' => $total,
            'overall' => $overall,
            'logicalCountSerialNumbers' => $logicalSerialNumbers,
            'hqCountSerialNumbers' => $countInAceHQSerialNumbers,
            'bmmbG4ScountSerialNumbers' => $countInBMMBg4sSerialNumbers,
            'totalSerialNumbers' => $totalSerialNumbers,
            'overallSerialNumbers' => $overallSerialNumbers,
            'userpartnerid' => $userPartnerID,
            'vaultAmountBmmb' => $vaultAmountBmmb,
            'totalCustomerHoldingBmmb' => $totalCustomerHoldingBmmb,
            'totalBalanceBmmb' => $totalBalanceBmmb,
            'pendingTransactionBmmb' => $pendingTransactionBmmb,

        ]);
    }

    /* function confirmReturn($app,$params){
        if($this->app->hasPermission('/root/dg999/vault/return')){ 
            $items = $this->app->vaultitemStore()->searchTable()->select()->whereIn('serialno', [$params['serialno']])->execute(); 
            try{
                $return = $app->bankvaultManager()->markItemReturned($items);
                echo json_encode([ 'success' => true]);  
            }catch(\Snap\api\exception\VaultItemError $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);                   
            }           
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No permission']);
        } 
    } */


    function getPrintDocuments($app, $params)
    {
        $vaultitemId = $params['id'];
        $document = $this->app->DocumentsStore()->searchTable()->select()->where('transactiontype', 'VaultItem')
            ->andWhere('transactionid', $vaultitemId)
            ->andWhere('status', Documents::STATUS_ACTIVE)
            ->orderby('id', 'DESC')
            ->one();

        if (!$document) {
            throw new \Exception('Invalid document. Please create document first');
        }


        $scheduledOn = $document->scheduledon;
        $document = $this->app->DocumentsStore()->searchTable()->select()->where('typenumber', $document->typenumber)->orderby('id', 'DESC')->execute();

        try {


            if ($document[0]->type == 'ConsignmentNote') {
                $documentTitle = 'Consignment Note';
                $documentHeader = 'Consignment Note';
            }
            if ($document[0]->type == 'TransferNote') {
                $documentTitle = 'Transfer Note';
                $documentHeader = 'Transfer Note (Vault)';
            }
            if ($document[0]->type == 'DeliveryNote') {
                $documentTitle = 'Deliver Note';
                $documentHeader = 'Deliver Note';
            }
            if ($document->type == 'TRANSFER' || $document->type == 'TRANSFERCONFIRMATION') {
                $documentTitle = 'Transfer Note';
                $documentHeader = 'Transfer Note';
            }
            // Save time printed for document
            $save = $this->app->DocumentsManager()->savePrintDate($document);

            $vaultitem = $this->app->VaultItemStore()->getById($vaultitemId, array(), false, 1);

            // if (!$vaultitem->vaultlocationid){
            //     $addressFrom = '
            //     No. 19-1, Jalan USJ 10/1D,
            //     47620 Subang Jaya, Selangor
            //     ';

            // }else{
            //     $addressFrom = $this->getVaultLocationAddress($vaultitem->vaultlocationid);

            // }

            // if (!$vaultitem->movetovaultlocationid){
            //     // $addressFrom = $this->getBranchAddress(14011);
            // }else{
            //     $addressTo = $this->getVaultLocationAddress($vaultitem->movetovaultlocationid);
            // }

            // Set from and to locations
            $vaultLocationstart = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_id', $params['from'])->one();
            $vaultLocationend = $this->app->vaultlocationStore()->searchTable()->select()->where('stl_id', $params['to'])->one();
            $fromLocation = $vaultLocationstart->name;
            $toLocation = $vaultLocationend->name;
            // Set Address
            // Cast to Int
            $addressFrom = $this->getVaultLocationAddress((int)$params['from']);
            $addressTo = $this->getVaultLocationAddress((int)$params['to']);
            /*
			$addressFrom = '
			No. 19-1, Jalan USJ 10/1D,
			47620 Subang Jaya, Selangor
			';

			$addressTo = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
            */


            $lists = [];
            foreach ($document as $doc) {
                $vaultitem = $this->app->VaultItemStore()->getById($doc->transactionid, array(), false, 1);
                $lists[] = [
                    "productname" => $vaultitem->productname,
                    "serialno" => $vaultitem->serialno,
                    "weight" => $vaultitem->weight
                ];
            }

            $noteDate = $document[0]->createdon;
            $content = $this->documentHTML($document[0]->typenumber, $documentTitle, $addressFrom, $addressTo, $lists, $noteDate, $fromLocation, $toLocation, $documentHeader, $scheduledOn);

            echo $content;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    private function getBranchAddress($branchid)
    {
        $branch = $this->app->partnerStore()->getRelatedStore('branches');;
        $branch = $branch->getByField('code', $branchid);

        $address = $branch->address . ',';
        $address .= ($branch->postcode) ? $branch->postcode . ',' : '';
        $address .= ($branch->city) ? $branch->city : '';
        return $address;
    }

    private function getVaultLocationAddress($vaultlocationid)
    {
        if ($vaultlocationid == 1) {
            //ACE HQ
            $address = 'No. 19-1, Jalan USJ 10/1D,
            47620 Subang Jaya, Selangor';
        }
        if ($vaultlocationid == 2) {
            //ACE G4S
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }
        if ($vaultlocationid == 3) {
            //MBB G4S
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }
        if ($vaultlocationid == 4) {
            //BMMB G4S
            $address = '
			Bank Muamalat Malaysia Berhad,<br />
			1st Floor, Podium Block,	
			Menara Bumiputra, 21, Jalan Melaka, 50100 Kuala Lumpur, Wilayah Persekutuan';
        }
        if ($vaultlocationid == 5) {
            //GO G4S ??
            $address = '
			SAFEGUARDS G4S SDN BHD,
			Lot 14, Jalan 241, Seksyen 51A, 46100 Petaling Jaya, Selangor';
        }

        return $address;
    }


    private function documentHTML($typenumber, $documentTitle, $addressFrom, $addressTo, $lists, $noteDate, $fromLocation = null, $toLocation = null, $documentHeader = null, $scheduledOn = null)
    {

        $noteNo = $typenumber;

        if (null == $fromLocation) {
            $fromLocation = "";
        }
        if (null == $toLocation) {
            $toLocation = "";
        }
        if (null == $documentHeader) {
            $documentHeader = $documentTitle;
        }
        if (null == $scheduledOn || $scheduledOn == '0000-00-00 00:00:00' || !$scheduledOn) {
            $scheduledOn = "-";
        } else {
            $scheduledOn = $scheduledOn->format('d-M-Y');
        }
        // "code":"GS-999-9-5g","serialnumber":"SN5-0032","weight":"5.000000"

        $html = '
		<html lang="en">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>ACEGTP: ' . $documentTitle . '</title>
			</head>
			<body>
				
				<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:22pt;"><span style="font-family:Cambria;">ACE Capital Growth Sdn. Bhd.</span></p>
				<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:18pt;"><span style="font-family:Cambria;">' . $documentHeader . '</span></p>
				<div style="text-align:center; ">
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto; border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
							<tr style="height:0.05pt;">
								<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Date created: </span>' . $noteDate->format('d-M-Y') . '</p>
								</td>
								<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">' . $documentTitle . ' No: ' . $noteNo . '</span></p>
								</td>
							</tr>
                            <tr>
                                <td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">Schedule Date: </span>' . $scheduledOn . '</p>
								</td>
                            </tr>
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
							<tr style="height:0.05pt;">
								<td style="width:202.1pt; border-right-style:solid; border-right-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">From: ' . $fromLocation . '</span></p>
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
										' . $addressFrom . '
									</p>
								</td>
								<td style="width:202.1pt; border-left-style:solid; border-left-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">To: ' . $toLocation . '</span></p>
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;">
										' . $addressTo . '
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #000000; border-collapse:collapse;">
						<tbody>
						<tr style="height:0.05pt;">
							<td style="width:22.95pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">No</span></p>
							</td>
							<td style="width:123.9pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Product Type</span></p>
							</td>
							<td style="width:140.1pt; border-right-style:solid; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Serial Number</span></p>
							</td>
							<td style="width:95.65pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
								<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">Quantity</span></p>
							</td>
						</tr>

						';
        foreach ($lists as $y => $list) {
            $y++;
            $html .= '
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-top-style:solid; border-top-width:0.75pt; border-right-style:solid; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">' . $y . '</span></p>
								</td>
								<td style="width:123.9pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">' . $list['productname'] . '</span></p>
								</td>
								<td style="width:140.1pt; border-style:solid; border-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">' . $list['serialno'] . '</span></p>
								</td>
								<td style="width:95.65pt; border-top-style:solid; border-top-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; font-size:11pt;"><span style="font-family:Calibri;">1</span></p>
								</td>
							</tr>
						';
        }

        $html .= '
							
						</tbody>
					</table>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<p style="margin-top:0pt; margin-bottom:0pt; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
					<table cellpadding="0" cellspacing="0" style="margin: auto;border:0.75pt solid #ffffff; border-collapse:collapse;">
						<tbody>
							
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Prepared By</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;&hellip;..</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Full Name</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<!--<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">NRIC No</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>-->
							<!--<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Date / Time</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>-->
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>
							<!--<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">Company Stamp</span></p>
								</td>
								
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;"></span></p>
								</td>
							</tr>-->
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
							<tr style="height:0.05pt;">
								<td style="width:22.95pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:left; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:140.1pt; border-right-style:solid;border-color: transparent; border-right-width:0.75pt; border-left-style:solid; border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								<td style="width:95.65pt; border-left-style:solid; border-color: transparent;border-left-width:0.75pt; border-bottom-style:solid; border-bottom-width:0.75pt; padding-right:5.03pt; padding-left:5.03pt; vertical-align:top; background-color:#ffffff;">
									<p style="margin-top:0pt; margin-bottom:0pt; text-align:center; font-size:12pt;"><span style="font-family:Cambria;">&nbsp;</span></p>
								</td>
								
							</tr>
						</tbody>
					</table>
				</div>

			</body>
			</html>
		';

        return $html;
    }

    public function getPendingDocuments($app, $params)
    {
        if (!$params['query']) {
            $return = $this->app->DocumentsManager()->getPendingDocuments('TransferNote');
        }
        $return = $this->app->DocumentsManager()->getPendingDocuments($params['query']);
        foreach ($return as $x => $_return) {
            // TEMP
            if ($_return['moverequestedon'] != '0000-00-00 00:00:00') {
                $return[$x]['moverequestedondate'] = $_return['moverequestedon']->format('Y-m-d H:i:s');
            }
            if ($_return['movecompletedon'] != '0000-00-00 00:00:00') {
                $return[$x]['movecompletedondate'] = $_return['movecompletedon']->format('Y-m-d H:i:s');
            }
        }
        echo json_encode($return);
    }

    public function createDocuments($app, $params)
    {

        try {

            $data = json_decode($params['data']);
            // $params["po"]; // vaultitem(s) id
            // $params["type"]; // document->type

            $entities = [];
            foreach ($data->po as $tran) {
                $entities[] = $this->app->documentsStore()->searchTable()->select()
                    ->where('transactionid', $tran->id)
                    ->andWhere('transactiontype', 'VaultItem')
                    ->andWhere('type', $data->type)
                    ->andWhere('status', 1)
                    ->one();
            }

            $return = $this->app->DocumentsManager()->createDocuments($entities, $data->type, 'VaultItem', $params['scheduledate'] ?? null);

            if ($return) {
                // save date to document manager
                // if there is data, save scheduled date 
                // and timestamp for print
                echo json_encode(array('success' => true, 'id' => $return->id));
            }
        } catch (\Snap\api\exception\VaultItemError | \Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    function exportVaultList($app, $params)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_VAULT';
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_VAULT';
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_VAULT';
        } else if (isset($params['partnercode']) && 'MIB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mib.partner.id'};
            $modulename = 'MIB_VAULT';
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_VAULT';
        }
        //added 12/01/2022
        //start
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', 'PKB@UAT')->execute();

            $partnerid = array();
            foreach ($partners as $partner) {
                array_push($partnerid, $partner->id);
            }
            $modulename = "KTP_VAULT";
        }
        // OTC MODULES
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'};
            $modulename = 'BSN_VAULT';
        } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            $modulename = 'ALRAJHI_VAULT';
        } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'};
            $modulename = 'POSARRAHNU_VAULT';
        } else if (isset($params['partnercode']) && 'common' === $params['partnercode']) {
            $partners =  $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();

            $partnerid = array();
            foreach ($partners as $partner) {
                array_push($partnerid, $partner->id);
            }
            $modulename = "COMMON_DGV_VAULT";
        }
        //end
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        //added 12/01/2022
        //start
        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $conditions = ['partnerid', 'IN', $partnerid];
        } else if (isset($params['partnercode']) && 'common' === $params['partnercode']) {
            // $conditions = ['partnerid','IN', $partnerid];
            $conditions = ['sharedgv' => true];
        } else {
            $conditions = ['partnerid' => $partnerid];
        }
        //end

        // $conditions = ['partnerid' => $partnerid];

        $statusRenderer = [
            0 => "Pending",
            1 => "Active",
            2 => "Transferring",
            3 => "Inactive",
            4 => "Removed",
            5 => "Pending Allocation"
        ];


        // Custom rendering based on module 
        // load formating decimal START
        if ($this->currentStore->getTableName() == 'vaultitem') {
            $prefix = $this->currentStore->getColumnPrefix();
            foreach ($header as $x => $headerColumn) {
                // Do custom formatting depending on module
                $original = $headerColumn->index;


                if ('allocated' === $headerColumn->index) {
                    $header[$x]->index = $this->currentStore->searchTable(false)->raw(
                        "CASE WHEN `{$prefix}allocated` = 0 THEN 'No'
                        WHEN `{$prefix}allocated` = 1 THEN 'Yes' END as `{$prefix}allocated`"
                    );
                    $header[$x]->index->original = $original;
                }
            }
        }


        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    }

    function getCommonVaultInfo($app, $params)
    {
        // $app->bankvaultManager()->getCurrentDgvUsage(false, $partnerId);
        $totalSharedUsage = $app->bankvaultmanager()->getSharedDgvAmount();

        //added 12/01/2022
        //start
        if ($params["partnercode"] == "KTP") {
            $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group', '=', 'PKB@UAT')->execute();
        } else {
            $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        }
        //end

        // $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        $output = [];
        foreach ($shareDgvPartners as $partner) {
            $usage_as_grams = $app->bankvaultManager()->getCurrentDgvUsage(false, $partner->id);
            $data = [
                'name' => $partner->name,
                'partnerid' => $partner->id,
                'usage' => number_format((float)$usage_as_grams / $totalSharedUsage * 100, 2, '.', ''),
                'grams' => number_format((float)$usage_as_grams, 3, '.', ''),
            ];
            $output[] = $data;
        }
        // calculate free usage;
        $total_used = $app->bankvaultManager()->getCurrentDgvUsage();
        $total_free = $totalSharedUsage - $total_used;
        $data_free_usage = [
            'name' => 'Free',
            'partnerid' => 0,
            'usage' => number_format((float)$total_free / $totalSharedUsage * 100, 2, '.', ''),
            'grams' => number_format((float)$total_free, 3, '.', ''),
        ];
        $output[] = $data_free_usage;

        // $output['info'] = $this->infoTable($infoData);

        echo json_encode($output);
    }

    function infoTable($app, $params)
    {
        $totalSharedUsage = $app->bankvaultmanager()->getSharedDgvAmount();
        //added 12/01/2022
        //start
        if ($params['partnercode'] == "KTP") {
            $partnercode = $this->app->getConfig()->{'ktp.pkb.partner.id'};
            // $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group','=', 'PKB@UAT')->execute();
            $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->andWhere('group', '=', $partnercode)->execute();
        } else {
            $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        }
        //end
        // $shareDgvPartners = $app->partnerStore()->searchTable()->select()->where('sharedgv', true)->execute();
        $output = [];
        $total_used = 0;
        foreach ($shareDgvPartners as $partner) {
            $usage_as_grams = $app->bankvaultManager()->getCurrentDgvUsage(false, $partner->id);
            $data = [
                'name' => $partner->name,
                'partnerid' => $partner->id,
                'usage' => $usage_as_grams / $totalSharedUsage * 100,
                'grams' => $usage_as_grams
            ];
            $output[] = $data;
            $total_used_gram += $data['grams'];
            $total_used_percent += $data['usage'];
        }
        // Add 3 s.f round 
        $total_used_gram = number_format((float)$total_used_gram, 3, '.', '');
        $total_used_percent = number_format((float)$total_used_percent, 2, '.', '');

        $html = '
            <div style="display: flex; justify-content: center;margin:5px 0">
                <div style="text-align:center; margin: 0 10px;">
                    <div style="font-size: 20px; font-weight: bold;">' . $totalSharedUsage . '</div>
                    <div>Common Vault (g)</div>
                </div>
                <div style="text-align:center; margin: 0 10px;">
                    <div style="font-size: 20px; font-weight: bold;">' . $total_used_gram . '</div>
                    <div>Total Customer Holding (g)</div>
                </div>
                <div style="text-align:center; margin: 0 10px;">
                    <div style="font-size: 20px; font-weight: bold;">' . number_format((float)($totalSharedUsage - $total_used_gram), 3, '.', '') . '</div>
                    <div>Balance (g)</div>
                </div>
            </div>
        ';
        $html .= '<style>
        .table100.ver1 {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 40px 0 rgb(0 0 0 / 15%);
            -moz-box-shadow: 0 0 40px 0 rgba(0,0,0,.15);
            -webkit-box-shadow: 0 0 40px 0 rgb(0 0 0 / 15%);
            -o-box-shadow: 0 0 40px 0 rgba(0,0,0,.15);
            -ms-box-shadow: 0 0 40px 0 rgba(0,0,0,.15);
        }
        .table100.ver2 .table100-head {
            box-shadow: 0 5px 20px 0 rgb(0 0 0 / 10%);
            -moz-box-shadow: 0 5px 20px 0 rgba(0,0,0,.1);
            -webkit-box-shadow: 0 5px 20px 0 rgb(0 0 0 / 10%);
            -o-box-shadow: 0 5px 20px 0 rgba(0,0,0,.1);
            -ms-box-shadow: 0 5px 20px 0 rgba(0,0,0,.1);
        }
        .table100.ver2 th {
            font-size: 14px;
            color: #fa4251;
            line-height: 1.4;
            background-color: transparent;
        }
        .table100-head th {
            padding-top: 8px;
            padding-bottom: 8px;
        }
        .table100.ver2 table {
            width: 100%;
        }
        .table100.ver2 .table100-body tr {
            border-bottom: 1px solid #f2f2f2;
        }
        .table100.ver2 td {
            font-size: 15px;
            color: gray;
            line-height: 1.4;
        }
        .table100-body td {
            padding-top: 6px;
            padding-bottom: 6px;
        }
        .column1, .column2, .column3 {
            width: 33%;
            text-align: center;
        }
        .table100-body {
            max-height: 405px;
            max-height: 100%;
            overflow: auto;
        }
        </style>
        <div class="table100 ver2 m-b-110">
            <div class="table100-head">
                <table>
                    <thead>
                        <tr class="row100 head">
                            <th class="cell100 column1">Partner name</th>
                            <th class="cell100 column2">Percentage %</th>
                            <th class="cell100 column3">Usage (gram)</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="table100-body js-pscroll ps ps--active-y">
                <table>
                    <tbody>
                        ';
        foreach ($output as $partner) {

            $html .= '
                                <tr class="row100 body">
                                    <td class="cell100 column1">' . $partner['name'] . '</td>
                                    <td class="cell100 column2">' . number_format((float)$partner['usage'], 2, '.', '') . '</td>
                                    <td class="cell100 column3">' . number_format((float)$partner['grams'], 3, '.', '') . '</td>
                                </tr>
                            ';
        }
        $html .= '
                            <tr class="row100 body">
                                <td class="cell100 column1"><b>Total</b></td>
                                <td class="cell100 column2"><b>' . $total_used_usage . '</b></td>
                                <td class="cell100 column3"><b>' . $total_used_gram . '</b></td>
                            </tr>
                        ';

        $html .= '
                    </tbody>
                </table>
            </div>
        </div>';
        // return $html;
        $return['html'] = $html;

        $logical = $this->app->vaultitemStore()->searchTable()->select()
            ->where('partnerid', 0)
            // ->whereNull('deliveryordernumber')
            ->andWhere('deliveryordernumber', '')
            // ->andWhere('deliveryordernumber', 'NULL')
            ->andWhere('status', VaultItem::STATUS_ACTIVE)
            ->andWhere('sharedgv', true)
            ->count();
        $reserved = $this->app->vaultitemStore()->searchTable()->select()
            ->where('partnerid', 0)
            ->whereNotNull('deliveryordernumber')
            ->andwhere('deliveryordernumber', '!=', '')
            ->andWhere('status', VaultItem::STATUS_ACTIVE)
            ->andWhere('sharedgv', true)
            ->count();
        $total = $this->app->vaultitemStore()->searchTable()->select()
            ->where('partnerid', 0)
            ->andWhere('status', VaultItem::STATUS_ACTIVE)
            ->andWhere('sharedgv', true)
            ->count();

        $sidebar = '
        <div style="margin:5px 0">
            <div style="text-align:center; margin-bottom: 15px; vertical-align: middle;line-height: 40px;background-color: #8dddc3;">
                <div style="font-size:1.5em;font-weight:900;width:100%;">' . $logical . '</div>
                <div style="font-size:1.2em;">Logical</div>
            </div>
            <div style="text-align:center; margin-bottom: 15px; vertical-align: middle;line-height: 40px;background-color: #caefae;">
                <div style="font-size:1.5em;font-weight:900;width:100%;">' . $reserved . '</div>
                <div style="font-size:1.2em;">Reserved</div>
            </div>
            <div style="text-align:center; margin-bottom: 15px; vertical-align: middle;line-height: 40px;background-color: #fbde4f;">
                <div style="font-size:1.5em;font-weight:900;width:100%;">' . $total . '</div>
                <div style="font-size:1.2em;">Total</div>
            </div>
        </div>';
        // $sidebar = '
        // <div style="margin:5px 0">
        //     <div style="text-align:center; margin: 0 10px;margin:8px;padding: 15px;background-color: #8dddc3;color: #ffffff;">
        //         <div style="font-size: 20px; font-weight: bold;">'.$logical.'</div>
        //         <div>Logical</div>
        //     </div>
        //     <div style="text-align:center; margin: 0 10px;margin:8px;padding: 15px;background-color: #caefae;color: #ffffff;">
        //         <div style="font-size: 20px; font-weight: bold;">'.$reserved.'</div>
        //         <div>Reserved</div>
        //     </div>
        //     <div style="text-align:center; margin: 0 10px;margin:8px;padding: 15px;background-color: #fbde4f;color: #ffffff;">
        //         <div style="font-size: 20px; font-weight: bold;">'.$total.'</div>
        //         <div>Total</div>
        //     </div>
        // </div>';
        // $sidebar['logical'] = $logical;
        // $sidebar['reserved'] = $reserved;
        // $sidebar['total'] = $total;

        $return['sidebar'] = $sidebar;

        $return['success'] = true;
        echo json_encode($return);
    }
}
