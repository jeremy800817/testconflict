<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\manager\MyGtpAccountManager;
use Snap\object\MyAccountClosure;
use Snap\object\MyCloseReason;
use Snap\object\MyLocalizedContent;
use Snap\object\Partner;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class myaccountclosureHandler extends CompositeHandler
{
    private $currentStore = null;

    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb', 'accountclosure');

        $this->mapActionToRights('list', '/root/bmmb/accountclosure/list;/root/go/accountclosure/list;/root/one/accountclosure/list;/root/onecall/accountclosure/list;/root/air/accountclosure/list;/root/mcash/accountclosure/list;/root/toyyib/accountclosure/list;/root/ktp/accountclosure/list;/root/pkbaffi/accountclosure/list;/root/bumira/accountclosure/list;/root/nubex/accountclosure/list;/root/hope/accountclosure/list;/root/mbsb/accountclosure/list;/root/red/accountclosure/list;/root/kodimas/accountclosure/list;/root/kgoldaffi/accountclosure/list;/root/koponas/accountclosure/list;/root/wavpay/accountclosure/list;/root/noor/accountclosure/list;/root/waqaf/accountclosure/list;/root/kasih/accountclosure/list;/root/posarrahnu/accountclosure/list;/root/igold/accountclosure/list;/root/bursa/accountclosure/list;/root/bsn/accountclosure/list;');
        $this->mapActionToRights('closeAccountHolder', '/root/bmmb/accountclosure/close;/root/go/accountclosure/close;/root/one/accountclosure/close;/root/onecall/accountclosure/close;/root/air/accountclosure/close;/root/mcash/accountclosure/close;/root/toyyib/accountclosure/list;/root/ktp/accountclosure/close;/root/pkbaffi/accountclosure/close;/root/bumira/accountclosure/close;/root/nubex/accountclosure/close;/root/hope/accountclosure/close;/root/mbsb/accountclosure/close;/root/red/accountclosure/close;/root/kodimas/accountclosure/close;/root/kgoldaffi/accountclosure/close;/root/koponas/accountclosure/close;/root/wavpay/accountclosure/close;/root/noor/accountclosure/close;/root/waqaf/accountclosure/close;/root/kasih/accountclosure/close;/root/posarrahnu/accountclosure/close;/root/igold/accountclosure/close;/root/bursa/accountclosure/close;/root/bsn/accountclosure/close;');
        $this->mapActionToRights('exportExcel', '/root/bmmb/accountclosure/list;/root/go/accountclosure/list;/root/one/accountclosure/list;/root/onecall/accountclosure/list;/root/air/accountclosure/list;/root/mcash/accountclosure/list;/root/toyyib/accountclosure/list;/root/ktp/accountclosure/list;/root/pkbaffi/accountclosure/list;/root/bumira/accountclosure/list;/root/nubex/accountclosure/list;/root/hope/accountclosure/list;/root/mbsb/accountclosure/list;/root/red/accountclosure/list;/root/kodimas/accountclosure/list;/root/kgoldaffi/accountclosure/list;/root/koponas/accountclosure/list;/root/wavpay/accountclosure/list;/root/noor/accountclosure/list;/root/waqaf/accountclosure/list;/root/kasih/accountclosure/list;/root/posarrahnu/accountclosure/list;/root/igold/accountclosure/list;/root/bursa/accountclosure/list;/root/bsn/accountclosure/list;');
        $this->mapActionToRights('getMerchantList', '/root/bmmb/accountclosure/list;/root/go/accountclosure/list;/root/one/accountclosure/list;/root/onecall/accountclosure/list;/root/air/accountclosure/list;/root/mcash/accountclosure/list;/root/toyyib/accountclosure/list;/root/ktp/accountclosure/list;/root/pkbaffi/accountclosure/list;/root/bumira/accountclosure/list;/root/nubex/accountclosure/list;/root/hope/accountclosure/list;/root/mbsb/accountclosure/list;/root/red/accountclosure/list;/root/kodimas/accountclosure/list;/root/kgoldaffi/accountclosure/list;/root/koponas/accountclosure/list;/root/wavpay/accountclosure/list;/root/noor/accountclosure/list;/root/waqaf/accountclosure/list;/root/kasih/accountclosure/list;/root/posarrahnu/accountclosure/list;/root/igold/accountclosure/list;/root/bursa/accountclosure/list;/root/bsn/accountclosure/list;');

        $this->app = $app;
        $this->currentStore = $myaccountclosureStore = $app->myaccountclosureFactory();
        $this->addChild(new ext6gridhandler($this, $myaccountclosureStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('achpartnerid', $partnerId);
        }



        //added on 10 Dec 2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('achpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('achpartnerid', 'IN', $partnerId);
        }
        
        return array($params, $sqlHandle, $fields);
    }

    /**
     * Function to masssage data before listing
     *
     * @param  MyAccountClosure[] $objects
     * @param  array $params
     * @param  array $records
     * @return array
     */
    function onPreListing($objects, $params, $records)
    {
        foreach($records as $key => $record) {
            $records[$key]['statustext'] = MyAccountClosure::convertStatusString($records[$key]['status']);
        }

        return $records;
	}

    public function closeAccountHolder($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);
            $partner = $accountHolder->getPartner();

            $closeReason = $app->myclosereasonStore()->getById(0);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->closeAccount($partner, $accountHolder, $closeReason, '1.0my', null, true, $params['remarks']);
            echo json_encode(['success' => true]);
        } catch (\Snap\api\exception\MyGtpAccountHolderNoAccountNameOrNumber $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'User bank info is incomplete!.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    function exportExcel($app, $params)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
        }else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
        }else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
        }else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
        }else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
        }else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = explode(",",$params['selected']);
        }

        $header = json_decode($params["header"]);
        $modulename = 'ACCOUNT_CLOSURE';
        $dateRange = json_decode($params["daterange"]);
        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            if ('statustext' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . MyAccountClosure::STATUS_PENDING . " THEN 'Pending'" .
                    "WHEN `{$prefix}status` = " . MyAccountClosure::STATUS_IN_PROGRESS . " THEN 'In Progress'" .
                    "WHEN `{$prefix}status` = " . MyAccountClosure::STATUS_APPROVED . " THEN 'APPROVED'" .
                    "WHEN `{$prefix}status` = " . MyAccountClosure::STATUS_REJECTED . " THEN 'REJECTED'" .
                    "ELSE 'UNKNOWN' END as `{$prefix}status`"                    
                );

                $header[$key]->index->original = 'status';
                break;
            } 
        }

        $dateStart = $dateRange->startDate; 
        $dateEnd = $dateRange->endDate;

        $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone()); 
            $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone()); 
            $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
            $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
            $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
            $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
            $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);


        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $conditions = function ($q) use ($startAt, $endAt, $partnerId) {
                $q->where('requestedon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('requestedon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('achpartnerid', 'IN', $partnerId);
            };
           
        }
        elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $conditions = function ($q) use ($startAt, $endAt, $partnerId) {
                $q->where('requestedon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('requestedon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('achpartnerid', 'IN', $partnerId);
            };
           
        }
        else{
            $conditions = function ($q) use ($startAt, $endAt, $partnerId) {
                $q->where('requestedon', '>=', $startAt->format('Y-m-d H:i:s'));
                $q->andWhere('requestedon', '<=', $endAt->format('Y-m-d H:i:s'));
                $q->andWhere('achpartnerid', '=', $partnerId);
            };
            
        }
     

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null);
    }

    //get the merchant list for checkbox display: KTP
    function getMerchantList($app,$params){
		//for now this is dummy data. The id is taken from existing partner
		// $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }

        $merchantdata=array();
        foreach ($partners as $partner){
             $arr = array('id'=>$partner->id,'name'=>$partner->name,'parent'=>$partner->parent);
             array_push($merchantdata,$arr);
        }
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}
}
