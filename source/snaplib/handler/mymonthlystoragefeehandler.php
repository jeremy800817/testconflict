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
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mymonthlystoragefeeHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/bmmb/report;/root/go/report;/root/one/report;/root/onecall/report;/root/air/report;/root/mcash/report;/root/ktp/report;/root/kopetro/report;/root/kopttr/report;/root/pkbaffi/report;/root/bumira/report;/root/toyyib/report;/root/nubex/report;/root/hope/report;/root/mbsb/report;/root/red/report;/root/kodimas/report;/root/kgoldaffi/report;/root/koponas/report;/root/wavpay/report;/root/noor/report;/root/waqaf/report;/root/kasih/report;/root/posarrahnu/report;/root/igold/report;/root/bursa/report;/root/bsn/report;', 'storagefee');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('exportExcel', 'list');
        $this->mapActionToRights('getMerchantList', 'list');


        $this->app = $app;

        $this->currentStore = $app->mymonthlystoragefeeStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function doAction($app, $action, $params)
    {
        if ($action == 'list') {
            $filters = json_decode($params['filter'], true);

            foreach ($filters as $key => $filter)
                if ('chargedon' == $filters[$key]['property']) {
                    $filters[$key]['value'] = [\Snap\common::convertUserDatetimeToUTC(new \DateTime($filter['value'][0]))->format('Y-m-d H:i:s'), \Snap\common::convertUserDatetimeToUTC(new \DateTime($filter['value'][1]))->format('Y-m-d H:i:s')];
                }
        }
        $params['filter'] = json_encode($filters);
        return parent::doAction($app, $action, $params);
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
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
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
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
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
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
        if (isset($params['accountholderid'])) {
            $sqlHandle->andWhere('accountholderid', $params['accountholderid']);
        }

        return array($params, $sqlHandle, $fields);
    }

    function onPreListing($objects, $params, $records)
    {
        foreach($records as $key => $record ) {
            $records[$key]['adminfeeamount'] = $records[$key]['adminfeexau'] * $records[$key]['price'];
            $records[$key]['storagefeeamount'] = $records[$key]['storagefeexau'] * $records[$key]['price'];
        }

        return $records;
    }

    function exportExcel($app, $params)
    {
        $header = json_decode($params["header"]);
        $modulename = 'STORAGE_FEE';
        $dateRange = json_decode($params["daterange"]);
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.onecall.partner.id'};
            $modulename = 'ONECALL_MONTHLY_STORAGE_FEE';
        }elseif (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.air.partner.id'};
            $modulename = 'AIR_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mcash.partner.id'};
            $modulename = 'MGOLD_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.toyyib.partner.id'};
            $modulename = 'TOYYIB_MONTHLY_STORAGE_FEE';
        }elseif (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.nubex.partner.id'};
            $modulename = 'NUBEX_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.hope.partner.id'};
            $modulename = 'HOPE_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mbsb.partner.id'};
            $modulename = 'MBSB_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.red.partner.id'};
            $modulename = 'REDGOLD_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.wavpay.partner.id'};
            $modulename = 'WAVPAYGOLD_MONTHLY_STORAGE_FEE';
        }elseif (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.noor.partner.id'};
            $modulename = 'NOOR_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POSARRAHNU_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.bsn.partner.id'} ?? $app->getConfig()->{'gtp.bsn.partner.id'};
            $modulename = 'BSN_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.igold.partner.id'};
            $modulename = 'IGOLD_MONTHLY_STORAGE_FEE';
        } elseif (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIH_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPETRO_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPTTR_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIHAFFI_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'BUMIRA_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLD_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLDAFFI_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KiGA_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'ANNURGOLD_MONTHLY_STORAGE_FEE';
        }
        elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            // $partnerid = $app->getConfig()->{'gtp.ktp.partner.id'};
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KASIHGOLD_MONTHLY_STORAGE_FEE';
        }

        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('storagefeeamount' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "`{$prefix}storagefeexau` * `{$prefix}price` as `{$prefix}storagefeeamount`"
                );

                $header[$key]->index->original = $original;
            } 

            if ('adminfeeamount' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "`{$prefix}adminfeexau` * `{$prefix}price` as `{$prefix}adminfeeamount`"
                );

                $header[$key]->index->original = $original;
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

        $conditions = function ($q) use ($startAt, $endAt, $partnerid) {
            $q->where('chargedon', '>=', $startAt->format('Y-m-d H:i:s'));
            $q->andWhere('chargedon', '<=', $endAt->format('Y-m-d H:i:s'));
            if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
                $q->andWhere('partnerid','IN', $partnerid);
            }
            else{
                $q->andWhere('partnerid', $partnerid);    
            }
            //$q->andWhere('partnerid', $partnerid);
        };

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null);
    }

    //get the merchant list for checkbox display: KTP
    function getMerchantList($app,$params){
		//for now this is dummy data. The id is taken from existing partner
		// $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
		// $partners = $app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
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
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}
}
