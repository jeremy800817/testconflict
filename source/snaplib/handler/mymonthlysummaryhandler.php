<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\object\MyLedger;
use Snap\object\Partner;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class mymonthlysummaryhandler extends CompositeHandler
{
    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb/report;/root/go/report;/root/one/report;/root/onecall/report;/root/air/report;/root/mcash/report;', 'monthlysummary');

        $this->mapActionToRights('list', '/root/bmmb/report/monthlysummary/list;/root/go/report/monthlysummary/list;/root/one/report/monthlysummary/list;/root/onecall/report/monthlysummary/list;/root/air/report/monthlysummary/list;/root/mcash/report/monthlysummary/list;/root/toyyib/report/monthlysummary/list;/root/ktp/report/monthlysummary/list;/root/kopetro/report/monthlysummary/list;/root/kopttr/report/monthlysummary/list;/root/pkbaffi/report/monthlysummary/list;/root/bumira/report/monthlysummary/list;/root/nubex/report/monthlysummary/list;/root/hope/report/monthlysummary/list;/root/mbsb/report/monthlysummary/list;/root/red/report/monthlysummary/list;/root/kodimas/report/monthlysummary/list;/root/kgoldaffi/report/monthlysummary/list;/root/koponas/report/monthlysummary/list;/root/wavpay/report/monthlysummary/list;/root/noor/report/monthlysummary/list;/root/waqaf/report/monthlysummary/list;/root/kasih/report/monthlysummary/list;/root/posarrahnu/report/monthlysummary/list;/root/igold/report/monthlysummary/list;/root/bursa/report/monthlysummary/list;/root/bsn/report/monthlysummary/list;');
        $this->mapActionToRights('exportExcel', '/root/bmmb/report/monthlysummary/list;/root/go/report/monthlysummary/list;/root/one/report/monthlysummary/list;/root/onecall/report/monthlysummary/list;/root/air/report/monthlysummary/list;/root/mcash/report/monthlysummary/list;/root/toyyib/report/monthlysummary/list;/root/ktp/report/monthlysummary/list;/root/kopetro/report/monthlysummary/list;/root/kopttr/report/monthlysummary/list;/root/pkbaffi/report/monthlysummary/list;/root/bumira/report/monthlysummary/list;/root/nubex/report/monthlysummary/list;/root/hope/report/monthlysummary/list;/root/mbsb/report/monthlysummary/list;/root/red/report/monthlysummary/list;/root/kodimas/report/monthlysummary/list;/root/kgoldaffi/report/monthlysummary/list;/root/koponas/report/monthlysummary/list;/root/wavpay/report/monthlysummary/list;/root/noor/report/monthlysummary/list;/root/waqaf/report/monthlysummary/list;/root/kasih/report/monthlysummary/list;/root/posarrahnu/report/monthlysummary/list;/root/igold/report/monthlysummary/list;/root/bursa/report/monthlysummary/list;/root/bsn/report/monthlysummary/list;');
        $this->mapActionToRights('exportTransaction', '/root/bmmb/report/monthlysummary/list;/root/go/report/monthlysummary/list;/root/one/report/monthlysummary/list;/root/onecall/report/monthlysummary/list;/root/air/report/monthlysummary/list;/root/mcash/report/monthlysummary/list;/root/toyyib/report/monthlysummary/list;/root/ktp/report/monthlysummary/list;/root/kopetro/report/monthlysummary/list;/root/kopttr/report/monthlysummary/list;/root/pkbaffi/report/monthlysummary/list;/root/bumira/report/monthlysummary/list;/root/nubex/report/monthlysummary/list;/root/hope/report/monthlysummary/list;/root/mbsb/report/monthlysummary/list;/root/red/report/monthlysummary/list;/root/kodimas/report/monthlysummary/list;/root/kgoldaffi/report/monthlysummary/list;/root/koponas/report/monthlysummary/list;/root/wavpay/report/monthlysummary/list;/root/noor/report/monthlysummary/list;/root/waqaf/report/monthlysummary/list;/root/kasih/report/monthlysummary/list;/root/posarrahnu/report/monthlysummary/list;/root/igold/report/monthlysummary/list;/root/bursa/report/monthlysummary/list;/root/bsn/report/monthlysummary/list;');
        $this->mapActionToRights('getMerchantList', '/root/bmmb/report/monthlysummary/list;/root/go/report/monthlysummary/list;/root/one/report/monthlysummary/list;/root/onecall/report/monthlysummary/list;/root/air/report/monthlysummary/list;/root/mcash/report/monthlysummary/list;/root/toyyib/report/monthlysummary/list;/root/ktp/report/monthlysummary/list;/root/kopetro/report/monthlysummary/list;/root/kopttr/report/monthlysummary/list;/root/pkbaffi/report/monthlysummary/list;/root/bumira/report/monthlysummary/list;/root/nubex/report/monthlysummary/list;/root/hope/report/monthlysummary/list;/root/mbsb/report/monthlysummary/list;/root/red/report/monthlysummary/list;/root/kodimas/report/monthlysummary/list;/root/kgoldaffi/report/monthlysummary/list;/root/koponas/report/monthlysummary/list;/root/wavpay/report/monthlysummary/list;/root/noor/report/monthlysummary/list;/root/waqaf/report/monthlysummary/list;/root/kasih/report/monthlysummary/list;/root/posarrahnu/report/monthlysummary/list;/root/igold/report/monthlysummary/list;/root/bursa/report/monthlysummary/list;/root/bsn/report/monthlysummary/list;');

        $this->app = $app;
        $this->currentStore = $app->myaccountholderStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'ktp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }

        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
            ->andWhere('parent','=', Partner::PARENT_AFFILIATE)
            ->orWhere('parent','=', Partner::PARENT_AFFILIATEPUBLIC)
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
            ->andWhere('parent','=', Partner::PARENT_AFFILIATE)
            ->orWhere('parent','=', Partner::PARENT_AFFILIATEPUBLIC)
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        $sqlHandle->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);

        return array($params, $sqlHandle, $fields);
    }

    function onPreListing($objects, $params, $records)
    {
        $accHolderIds = array_map(function ($accHolder) {
            return $accHolder['id'];
        }, $records);

        $store = $this->app->myledgerStore();
        $prefix = $store->getColumnPrefix();
        $handler = $store->searchView(true, 1);
        $handler = $handler->select([
            'accountholderid',
            $handler->raw("SUM({$prefix}credit-{$prefix}debit) as {$prefix}xaubalance"),
            $handler->raw("SUM({$prefix}amountin-{$prefix}amountout) as {$prefix}amountbalance")
        ]);
        $handler->whereIn('accountholderid', $accHolderIds);
        $handler->groupBy(['accountholderid']);

        if (isset($params['monthend'])) {
            $monthEnd = new \DateTime($params['monthend'], $this->app->getUserTimezone());
            $monthEnd = \Snap\common::convertUserDatetimeToUTC($monthEnd);
            $handler->where('transactiondate', '>=', '2020-01-01 00:00:00');
            $handler->where('transactiondate', '<=', $monthEnd->format('Y-m-d H:i:s'));
        }
        $handler->where('status', MyLedger::STATUS_ACTIVE);
        $balances = $handler->execute();

        $ledgers = [];
        foreach($balances as $balance) {
            $ledgers[$balance->accountholderid] = $balance;
        }

        foreach($records as $index => $record) {
            $ledger = $ledgers[$records[$index]['id']];
            $records[$index]['amountbalance'] = $ledger->amountbalance;
            $records[$index]['xaubalance'] = $ledger->xaubalance;
        }

        return $records;
    }

    function exportExcel($app, $params)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_SUMMARY_LISTING_';
        } elseif (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_SUMMARY_LISTING_';
        } elseif (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONECENT_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.onecall.partner.id'};
            $modulename = 'ONECALL_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.air.partner.id'};
            $modulename = 'AIR_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mcash.partner.id'};
            $modulename = 'MGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.toyyib.partner.id'};
            $modulename = 'TOYYIB_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.nubex.partner.id'};
            $modulename = 'NUBEX_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.hope.partner.id'};
            $modulename = 'HOPE_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mbsb.partner.id'};
            $modulename = 'MBSB_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.red.partner.id'};
            $modulename = 'REDGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.wavpay.partner.id'};
            $modulename = 'WAVPAYGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.noor.partner.id'};
            $modulename = 'NOOR_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POSARRAHNUGOLD_SUMMARY_LISTING_';
        } 
        elseif (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.bsn.partner.id'} ?? $app->getConfig()->{'gtp.bsn.partner.id'};
            $modulename = 'BSN_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.igold.partner.id'};
            $modulename = 'IGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIH_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPETRO_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPTTR_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIHAFFI_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'BUMIRA_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLDAFFI_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KiGA_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'ANNURGOLD_SUMMARY_LISTING_';
        }
        elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KASIHGOLD_SUMMARY_LISTING_';
        }

        $header     = json_decode($params["header"]);
        $monthEnd   = $params['monthend'];
        $monthEnd   = new \DateTime($monthEnd, $this->app->getUserTimezone()); 
        $modulename = $modulename . $monthEnd->format('Y-m-d');
        $monthEnd   = \Snap\common::convertUserDatetimeToUTC($monthEnd);

        $conditions = function ($query) use ($partnerid) {
            $query->where('investmentmade', MyAccountHolder::INVESTMENT_MADE);
            if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
                $query->where('partnerid','IN', $partnerid);
            }
            else{
                $query->where('partnerid', $partnerid);
            }
            
        };

        $app = $this->app;
        
        $resultCallback = function ($records) use ($app, $params) {

            $accHolderCodes = array_map(function ($accHolder) {
                return $accHolder->accountholdercode;
            }, $records);

    
            $store = $this->app->myledgerStore();
            $prefix = $store->getColumnPrefix();
            $handler = $store->searchView(true, 1);
            $handler = $handler->select([
                'achaccountholdercode',
                $handler->raw("SUM({$prefix}credit-{$prefix}debit) as {$prefix}xaubalance"),
                $handler->raw("SUM({$prefix}amountin-{$prefix}amountout) as {$prefix}amountbalance")
            ]);
            // $handler->whereIn('achaccountholdercode', $accHolderCodes);
            $handler->where('achaccountholdercode','IN', $accHolderCodes);
            $handler->groupBy(['accountholderid']);
    
            if (isset($params['monthend'])) {
                $monthEnd = new \DateTime($params['monthend'], $this->app->getUserTimezone());
                $monthEnd = \Snap\common::convertUserDatetimeToUTC($monthEnd);
                $handler->where('transactiondate', '>=', '2020-01-01 00:00:00');
                $handler->where('transactiondate', '<=', $monthEnd->format('Y-m-d H:i:s'));
            }
            $handler->where('status', MyLedger::STATUS_ACTIVE);
            $balances = $handler->execute();
    
            $ledgers = [];
            foreach($balances as $balance) {
                $ledgers[$balance->achaccountholdercode] = $balance;
            }

            foreach($records as $index => $record) {
                $ledger = $ledgers[$records[$index]->accountholdercode];
                $records[$index] = $record->toArray();
                $records[$index]['amountbalance'] = $ledger->amountbalance;
                $records[$index]['xaubalance'] = $ledger->xaubalance;
                $records[$index] = $app->myaccountholderStore()->create($records[$index]);
            }

            return $records;
        };

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateAccountReport($this->currentStore, $header, $modulename, null, $conditions, $resultCallback);
    }

    function exportTransaction($app, $params)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = $this->app->mygtpstatementManager();

        $accHolder = $this->app->myaccountholderStore()->searchTable()
            ->select(['id', 'partnerid', 'accountholdercode', 'fullname', 'mykadno'])
            ->where('id', $params['accountholderid'])
            ->one();
        
        $dateEnd   = $params['monthend'];
        $dateEnd   = new \DateTime($dateEnd, $this->app->getUserTimezone());
        $dateStart = new \DateTime($dateEnd->format('Y-m-01 00:00:00'), $this->app->getUserTimezone());
        $untilDate = clone $dateStart;
        $untilDate = new \DateTime($untilDate->format('Y-m-d 23:59:59'), $this->app->getUserTimezone());
        $untilDate->modify("-1 day");

        $records   = $this->getStatementRecords($accHolder, $dateStart, $dateEnd, $untilDate);
        $titleDate = 'Transaction Listing For Individual Customers as At ' . $dateEnd->format('d F Y');
        $totalRows = count($records);
        $startRow  = 7 + 1;
        $endRow    = $startRow + $totalRows;

        $filename = '##ACCOUNTHOLDERCODE##_##DATE##';
        $tags     = ['##ACCOUNTHOLDERCODE##', '##DATE##'];
        $fillers  = [$dateEnd->format('d F Y'), $accHolder->accountholdercode];
        $filename = str_replace($tags, $fillers, $filename);
        $html     = $statementManager->getStatementAsHtml($accHolder, $titleDate, $records, true);

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

    //get the merchant list for checkbox display: KTP
    function getMerchantList($app,$params){
        // get all group from pkb@uat
        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        $merchantdata=array();
        foreach ($partners as $partner){
             $arr = array('id'=>$partner->id,'name'=>$partner->name,'parent'=>$partner->parent);
             array_push($merchantdata,$arr);
        }
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
		return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}
}
