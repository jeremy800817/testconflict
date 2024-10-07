<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyMonthlyStorageFee;
use Snap\object\Partner;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 */
class otcoutstandingstoragefeejobhandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('
        /root/bmmb/managementfeereport/deductionsuccess;/root/go/managementfeereport/deductionsuccess;/root/one/managementfeereport/deductionsuccess;/root/onecall/managementfeereport/deductionsuccess;/root/air/managementfeereport/deductionsuccess;/root/mcash/managementfeereport/deductionsuccess;/root/ktp/managementfeereport/deductionsuccess;/root/kopetro/managementfeereport/deductionsuccess;/root/kopttr/managementfeereport/deductionsuccess;/root/pkbaffi/managementfeereport/deductionsuccess;/root/bumira/managementfeereport/deductionsuccess;/root/toyyib/managementfeereport/deductionsuccess;/root/nubex/managementfeereport/deductionsuccess;/root/hope/managementfeereport/deductionsuccess;/root/mbsb/managementfeereport/deductionsuccess;/root/red/managementfeereport/deductionsuccess;/root/kodimas/managementfeereport/deductionsuccess;/root/kgoldaffi/managementfeereport/deductionsuccess;/root/koponas/managementfeereport/deductionsuccess;/root/wavpay/managementfeereport/deductionsuccess;/root/noor/managementfeereport/deductionsuccess;/root/waqaf/managementfeereport/deductionsuccess;/root/kasih/managementfeereport/deductionsuccess;/root/posarrahnu/managementfeereport/deductionsuccess;/root/igold/managementfeereport/deductionsuccess;/root/bursa/managementfeereport/deductionsuccess;/root/bsn/managementfeereport/deductionsuccess;/root/alrajhi/managementfeereport/deductionsuccess;/root/bimb/managementfeereport/deductionsuccess;
        /root/bmmb/managementfeereport/deductionfailed;/root/go/managementfeereport/deductionfailed;/root/one/managementfeereport/deductionfailed;/root/onecall/managementfeereport/deductionfailed;/root/air/managementfeereport/deductionfailed;/root/mcash/managementfeereport/deductionfailed;/root/ktp/managementfeereport/deductionfailed;/root/kopetro/managementfeereport/deductionfailed;/root/kopttr/managementfeereport/deductionfailed;/root/pkbaffi/managementfeereport/deductionfailed;/root/bumira/managementfeereport/deductionfailed;/root/toyyib/managementfeereport/deductionfailed;/root/nubex/managementfeereport/deductionfailed;/root/hope/managementfeereport/deductionfailed;/root/mbsb/managementfeereport/deductionfailed;/root/red/managementfeereport/deductionfailed;/root/kodimas/managementfeereport/deductionfailed;/root/kgoldaffi/managementfeereport/deductionfailed;/root/koponas/managementfeereport/deductionfailed;/root/wavpay/managementfeereport/deductionfailed;/root/noor/managementfeereport/deductionfailed;/root/waqaf/managementfeereport/deductionfailed;/root/kasih/managementfeereport/deductionfailed;/root/posarrahnu/managementfeereport/deductionfailed;/root/igold/managementfeereport/deductionfailed;/root/bursa/managementfeereport/deductionfailed;/root/bsn/managementfeereport/deductionfailed;/root/alrajhi/managementfeereport/deductionfailed;/root/bimb/managementfeereport/deductionfailed;/root/bsn/managementfeereport/list;', 
        'otcoutstandingstoragefeejob');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('exportExcel', 'list');
        
        $this->app = $app;

        $this->currentStore = $app->otcoutstandingstoragefeejobStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));

    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {

        if (isset($params['status'])) {
            if ($params['status'] == 'Success'){
                $sqlHandle->andWhere('status', 1);
            }else if ($params['status'] == 'Failed'){
                $sqlHandle->andWhere('status', 2);
            }
        }
        return array($params, $sqlHandle, $fields);
    }

    function onPreListing($objects, $params, $records)
    {
        foreach($records as $key => $record ) {

            $achpartnerid =  $this->app->myaccountholderStore()
                            ->searchTable(false)
                            ->select(['partnerid'])  
                            ->where('partnercusid', $records[$key]['achpartnercusid'])
                            ->one();
                            
            $partnercode =  $this->app->partnerStore()
                            ->searchTable(false)
                            ->select(['code'])  
                            ->where('id', $achpartnerid['ach_partnerid'])
                            ->one();

            $records[$key]['achaccountholdercode'] = $partnercode['par_code'].'-'.$this->app->getConfig()->{'gtp.alrajhi.relationshiptypecode'}.'-'.$records[$key]['achaccountholdercode'];                
        }

        return $records;
    }

    function exportExcel($app, $params){

        if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            if($params['status'] == 'success'){
                $modulename = 'ALRAJHI_MANAGEMENT_FEE_STATUS_SUCCESS';
            }else{
                $modulename = 'ALRAJHI_MANAGEMENT_FEE_STATUS_FAILED';
            }
        }

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        ob_start();
        $this->doAction($app, 'list', $params);
        $data = ob_get_contents();
        ob_end_clean();
        $list = json_decode($data);

        $filteredRecords = [];

        foreach ($list->records as $record) {

            $recordDate = strtotime($record->createdon); 

            if ($recordDate >= strtotime($dateRange->startDate) && $recordDate <= strtotime($dateRange->endDate)) {   
                if($params['status'] == 'success'){
                    if($record->status == 1){
                        $record->status = gettext("Success");
                        $filteredRecords[] = $record;
                    }
                }else if($params['status'] == 'failed'){
                    if($record->status == 2){
                        $record->status = gettext("Failed");
                        $filteredRecords[] = $record;
                    }
                } else {
					if($record->status == 1){
                        $record->status = gettext("Success");
                    }
					if($record->status == 2){
                        $record->status = gettext("Failed");
                    }
					$filteredRecords[] = $record;
				}  
            }
        }
        
        $this->app->reportingManager()->generateReportWithList($filteredRecords, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, '', '', $this->currentStore->getTableName());
    }
}
