<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyGoldTransaction;
use Snap\object\Order;
use Snap\object\Partner;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class mycommissionhandler extends CompositeHandler
{
    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb/report', 'commission');

        $this->mapActionToRights('list', '/root/bmmb/report/commission/list;/root/go/report/commission/list;/root/one/report/commission/list;/root/onecall/report/commission/list;/root/air/report/commission/list;/root/mcash/report/commission/list;/root/toyyib/report/commission/list;/root/ktp/report/commission/list;/root/kopetro/report/commission/list;/root/kopttr/report/commission/list;/root/pkbaffi/report/commission/list;/root/bumira/report/commission/list;/root/nubex/report/commission/list;/root/hope/report/commission/list;/root/mbsb/report/commission/list;/root/red/report/commission/list;/root/kodimas/report/commission/list;/root/kgoldaffi/report/commission/list;/root/koponas/report/commission/list;/root/wavpay/report/commission/list;/root/noor/report/commission/list;/root/waqaf/report/commission/list;/root/kasih/report/commission/list;/root/posarrahnu/report/commission/list;/root/igold/report/commission/list;/root/bursa/report/commission/list;/root/bsn/report/commission/list;');
        $this->mapActionToRights('exportExcel', '/root/bmmb/report/commission/list;/root/go/report/commission/list;/root/one/report/commission/list;/root/onecall/report/commission/list;/root/air/report/commission/list;/root/mcash/report/commission/list;/root/toyyib/report/commission/list;/root/ktp/report/commission/list;/root/kopetro/report/commission/list;/root/kopttr/report/commission/list;/root/pkbaffi/report/commission/list;/root/bumira/report/commission/list;/root/nubex/report/commission/list;/root/hope/report/commission/list;/root/mbsb/report/commission/list;/root/red/report/commission/list;/root/kodimas/report/commission/list;/root/kgoldaffi/report/commission/list;/root/koponas/report/commission/list;/root/wavpay/report/commission/list;/root/noor/report/commission/list;/root/waqaf/report/commission/list;/root/kasih/report/commission/list;/root/posarrahnu/report/commission/list;/root/igold/report/commission/list;/root/bursa/report/commission/list;/root/bsn/report/commission/list;');
        $this->mapActionToRights('getMerchantList', '/root/bmmb/report/commission/list;/root/go/report/commission/list;/root/one/report/commission/list;/root/onecall/report/commission/list;/root/air/report/commission/list;/root/mcash/report/commission/list;/root/toyyib/report/commission/list;/root/ktp/report/commission/list;/root/kopetro/report/commission/list;/root/kopttr/report/commission/list;/root/pkbaffi/report/commission/list;/root/bumira/report/commission/list;/root/nubex/report/commission/list;/root/hope/report/commission/list;/root/mbsb/report/commission/list;/root/red/report/commission/list;/root/kodimas/report/commission/list;/root/kgoldaffi/report/commission/list;/root/koponas/report/commission/list;/root/wavpay/report/commission/list;/root/noor/report/commission/list;/root/waqaf/report/commission/list;/root/kasih/report/commission/list;/root/posarrahnu/report/commission/list;/root/igold/report/commission/list;/root/bursa/report/commission/list;/root/bsn/report/commission/list;');

        $this->app = $app;

        $this->currentStore = $app->mygoldtransactionStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);  
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        }
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('ordpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
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
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
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
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('ordpartnerid', 'IN', $partnerId);
            $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerId)->execute();
            $multiple = true;
        }

        $sqlHandle->where(function($q) {
            $q->where('ordstatus', Order::STATUS_COMPLETED);
            $q->orWhere('ordstatus', Order::STATUS_CONFIRMED);
            $q->orWhere(function ($r) {
                $r->where('ordstatus', Order::STATUS_PENDING);
                $r->andWhere('ordtype', Order::TYPE_COMPANYBUY);
            });
        });

          // $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerId);   
        // $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('ordpartnerid', 'IN', $partnerId)->execute(); 
        if(!$multiple){
            // If single partner
            $from = $settings->dgpeakhourfrom;
            $from->setTimeZone($this->app->getServerTimeZone());            
            $to = $settings->dgpeakhourto;
            $to->setTimeZone($this->app->getServerTimeZone());
            
            if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                $sqlHandle->where(function($q) use ($from, $to) {
                    $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                    $q->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                });
            } else {
                $sqlHandle->where(function($q) use ($from, $to) {
                    $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                    $q->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                });
            }
        }
        // else if (is_array($partnerId)) {
        //     $sqlHandle = $this->handleArrayPartnersPreQueryListing($params, $sqlHandle, $fields, $partnerId);
        // } 
        else{
            // If multi partner
            $setting = $this->app->mypartnersettingStore()->getByField('partnerid', $params['selected']); 
            // Get individual settings
            $from = $setting->dgpeakhourfrom;
            $from->setTimeZone($this->app->getServerTimeZone());            
            $to = $setting->dgpeakhourto;
            $to->setTimeZone($this->app->getServerTimeZone());
    
            // $sqlHandle->where(function($q) {
            //     $q->where('ordstatus', Order::STATUS_COMPLETED);
            //     $q->orWhere('ordstatus', Order::STATUS_CONFIRMED);
            //     $q->orWhere(function ($r) {
            //         $r->where('ordstatus', Order::STATUS_PENDING);
            //         $r->andWhere('ordtype', Order::TYPE_COMPANYBUY);
            //     });
            // });

            $sqlHandle->andWhere('ordpartnerid', $params['selected']);

            if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                $sqlHandle->where(function($q) use ($from, $to) {
                    $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                    $q->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                });
            } else {
                $sqlHandle->where(function($q) use ($from, $to) {
                    $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                    $q->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                });
            }

            // foreach ($partnerId as $part){
            //     $setting = $this->app->mypartnersettingStore()->getByField('partnerid', $part); 
            //     // Get individual settings
            //     $from = $setting->dgpeakhourfrom;
            //     $from->setTimeZone($this->app->getServerTimeZone());            
            //     $to = $setting->dgpeakhourto;
            //     $to->setTimeZone($this->app->getServerTimeZone());

            //     // Get Time Range and Partner
                
            // }

            // $sqlHandle->where(function($q) {
            //     $q->where('ordstatus', Order::STATUS_COMPLETED);
            //     $q->orWhere('ordstatus', Order::STATUS_CONFIRMED);
            //     $q->orWhere(function ($r) {
            //         $r->where('ordstatus', Order::STATUS_PENDING);
            //         $r->andWhere('ordtype', Order::TYPE_COMPANYBUY);
            //     });
            // });

            // // Full filter
            // if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
            //     $sqlHandle->where(function($q) use ($from, $to) {
            //         $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
            //         $q->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
            //     });
            // } else {
            //     $sqlHandle->where(function($q) use ($from, $to) {
            //         $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
            //         $q->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
            //     });
            // }
        }

        return array($params, $sqlHandle, $fields);
    }

    function doAction($app, $action, $params)
    {
        if ($action == 'list') {
            $filters = json_decode($params['filter'], true);

            foreach ($filters as $key => $filter)
                if ('createdon' == $filters[$key]['property']) {
                    $filters[$key]['value'] = [\Snap\common::convertUserDatetimeToUTC(new \DateTime($filter['value'][0], $app->getUserTimezone()))->format('Y-m-d H:i:s'), \Snap\common::convertUserDatetimeToUTC(new \DateTime($filter['value'][1], $app->getUserTimezone()))->format('Y-m-d H:i:s')];
                }
        }
        $params['filter'] = json_encode($filters);
        return parent::doAction($app, $action, $params);
    }

    /**
     * Function to masssage data before listing
     *
     * @param  MyGoldTransaction[] $objects
     * @param  array $params
     * @param  array $records
     * @return array
     */
    function onPreListing($objects, $params, $records)
    {
        $partnerid = 0;
        
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bmmb.partner.id'};            
        } elseif (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.go.partner.id'};
        } elseif (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.one.partner.id'};
        } elseif (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.onecall.partner.id'};
        } elseif (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mcash.partner.id'};
        }
        else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.air.partner.id'};
        }
        else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
        }
        else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.nubex.partner.id'};
        }
        else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.hope.partner.id'};
        }
        else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
        }
        else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.red.partner.id'};
        }
        else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
        }elseif (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.noor.partner.id'};
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
        $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
        }
        else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.igold.partner.id'};
        }
        else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
             $partnerid = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'}; //later need to change this to the real config partner id

            // $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->get();
            // return $this->handleArrayPartnersPreListing($params, $records, $partners);
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'}; //later need to change this to the real config partner id
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerid = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'}; //later need to change this to the real config partner id
        }

        if($params['selected']){
            $partnerid = $params['selected'];
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $params['selected']);
        }else{
            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerid);
        }
        // Non-array / Single partner id will reach below
        

        if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
            $partnerCompanySellCommission = $settings->dgpartnersellcommission ?? 0;
            $partnerCompanyBuyCommission  = $settings->dgpartnerbuycommission ?? 0;
        } else {            
            $partnerCompanySellCommission = $settings->dgpeakpartnersellcommission ?? 0;
            $partnerCompanyBuyCommission  = $settings->dgpeakpartnerbuycommission ?? 0;
        }        
        
        $aceCompanySellCommission     = $settings->dgacesellcommission ?? 0;
        $aceCompanyBuyCommission      = $settings->dgacebuycommission ?? 0;

        $affiliateCompanySellCommission     = $settings->dgaffiliatesellcommission ?? 0;
        $affiliateCompanyBuyCommission      = $settings->dgaffiliatebuycommission ?? 0;
        
        if (0 < $partnerid) {
            $partner = $this->app->partnerStore()->getById($partnerid);
            $calc = $partner->calculator();
    
            foreach ($records as $key => $record) {
                if ('CompanySell' === $record['ordtype']) {
                    $records[$key]['partnercommission']        = $calc->multiply($record['ordxau'], $partnerCompanySellCommission);
                    $records[$key]['partnercommissionpergram'] = $calc->round($partnerCompanySellCommission);
                    $records[$key]['acecommission']            = $calc->multiply($record['ordxau'], $aceCompanySellCommission);
                    $records[$key]['acecommissionpergram']     = $calc->round($aceCompanySellCommission);
                    $records[$key]['affiliatecommission']            = $calc->multiply($record['ordxau'], $affiliateCompanySellCommission);
                    $records[$key]['affiliatecommissionpergram']     = $calc->round($affiliateCompanySellCommission);
                } elseif ('CompanyBuy' === $record['ordtype']) {
                    $records[$key]['partnercommission']        = $calc->multiply($record['ordxau'], $partnerCompanyBuyCommission);
                    $records[$key]['partnercommissionpergram'] = $calc->round($partnerCompanyBuyCommission);
                    $records[$key]['acecommission']            = $calc->multiply($record['ordxau'], $aceCompanyBuyCommission);
                    $records[$key]['acecommissionpergram']     = $calc->round($aceCompanyBuyCommission);
                    $records[$key]['affiliatecommission']            = $calc->multiply($record['ordxau'], $affiliateCompanyBuyCommission);
                    $records[$key]['affiliatecommissionpergram']     = $calc->round($affiliateCompanyBuyCommission);
                }
            }
        }

        return $records;
    }

    function exportExcel($app, $params)
    {
        $partnerid = 0;
        $modulename = 'GTP_BUY_SELL_COMMISSION';

        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_BUY_SELL_COMMISSION';
        } elseif (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.onecall.partner.id'};
            $modulename = 'ONECALL_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mcash.partner.id'};
            $modulename = 'MGOLD_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.toyyib.partner.id'};
            $modulename = 'TOYYIB_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.nubex.partner.id'};
            $modulename = 'NUBEX_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.hope.partner.id'};
            $modulename = 'HOPE_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.mbsb.partner.id'};
            $modulename = 'MBSB_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.red.partner.id'};
            $modulename = 'REDGOLD_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.wavpay.partner.id'};
            $modulename = 'WAVPAYGOLD_BUY_SELL_COMMISSION';
        }elseif (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.noor.partner.id'};
            $modulename = 'NOOR_BUY_SELL_COMMISSION';
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POSARRAHNUGOLD_BUY_SELL_COMMISSION';
        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'otc.bsn.partner.id'} ?? $app->getConfig()->{'gtp.bsn.partner.id'};
            $modulename = 'BSN_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.igold.partner.id'};
            $modulename = 'IGOLD_BUY_SELL_COMMISSION';
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.air.partner.id'};
            $modulename = 'AIR_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIH_EMAS_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPETRO_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KOPTTR_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'PITIH_EMAS_AFFI_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'BUMIRA_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLD_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KGOLDAFFI_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KiGA_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'ANNURGOLD_BUY_SELL_COMMISSION';
        }
        elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerid = explode(',',$params['selected']);
            $modulename = 'KASIHGOLD_BUY_SELL_COMMISSION';
        }
        // $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerid);
        // if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
        //     $modulename .= '_NON_PEAK_HOUR';
        //     $partnerCompanySellCommission = $settings->dgpartnersellcommission ?? 0;
        //     $partnerCompanyBuyCommission  = $settings->dgpartnerbuycommission ?? 0;
        // } else {
            
        //     $partnerCompanySellCommission = $settings->dgpeakpartnersellcommission ?? 0;
        //     $partnerCompanyBuyCommission  = $settings->dgpeakpartnerbuycommission ?? 0;
        // }
        
        // $aceCompanySellCommission     = $settings->dgacesellcommission ?? 0;
        // $aceCompanyBuyCommission      = $settings->dgacebuycommission ?? 0;

        // $header          = json_decode($params["header"]);
        // $dateRange       = json_decode($params["daterange"]);
        // $companyBuyType  = Order::TYPE_COMPANYBUY;
        // $companySellType = Order::TYPE_COMPANYSELL;

        // $prefix = $this->currentStore->getColumnPrefix();

        // foreach ($header as $key => $column) {

        //     // Overwrite index value with expression
        //     $original = $column->index;
        //     if ('partnercommission' === $column->index) {
        //         $header[$key]->index = $this->currentStore->searchTable(false)->raw(
        //             "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
        //                   THEN `{$prefix}ordxau` * {$partnerCompanyBuyCommission} 
        //                   WHEN `{$prefix}ordtype` = '{$companySellType}' 
        //                   THEN `{$prefix}ordxau` * {$partnerCompanySellCommission} END as `{$prefix}partnercommission`"
        //         );

        //         $header[$key]->index->original = $original;
        //     } elseif ('partnercommissionpergram' === $column->index) {
        //         $header[$key]->index = $this->currentStore->searchTable(false)->raw(
        //             "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
        //                   THEN '{$partnerCompanyBuyCommission}' 
        //                   WHEN `{$prefix}ordtype` = '{$companySellType}' 
        //                   THEN '{$partnerCompanySellCommission}' END as `{$prefix}partnercommissionpergram`"
        //         );

        //         $header[$key]->index->original = $original;
        //     } elseif ('acecommission' === $column->index) {
        //         $header[$key]->index = $this->currentStore->searchTable(false)->raw(
        //             "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
        //                   THEN `{$prefix}ordxau` * {$aceCompanyBuyCommission} 
        //                   WHEN `{$prefix}ordtype` = '{$companySellType}' 
        //                   THEN `{$prefix}ordxau` * {$aceCompanySellCommission} END as `{$prefix}acecommission`"
        //         );
        //         $header[$key]->index->original = $original;
        //     } elseif ('acecommissionpergram' === $column->index) {
        //         $header[$key]->index = $this->currentStore->searchTable(false)->raw(
        //             "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
        //                   THEN '{$aceCompanyBuyCommission}' 
        //                   WHEN `{$prefix}ordtype` = '{$companySellType}' 
        //                   THEN '{$aceCompanySellCommission}' END as `{$prefix}acecommissionpergram`"
        //         );
        //         $header[$key]->index->original = $original;
        //     } elseif ('ordstatus' === $column->index) {
        //         $header[$key]->index = $this->currentStore->searchTable(false)->raw(
        //             "CASE WHEN `{$prefix}ordstatus` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
        //              WHEN `{$prefix}ordstatus` = " . Order::STATUS_PENDING . " THEN 'Pending Payment'
        //              WHEN `{$prefix}ordstatus` = " . Order::STATUS_COMPLETED . " THEN 'Completed' END as `{$prefix}ordstatus`"
        //         );
        //         $header[$key]->index->original = $original;
        //     }
        // }

        // if (0 < $partnerid) {
        //     $conditions = ['ordpartnerid' => $partnerid];
        // }

        // $dateStart = $dateRange->startDate; 
        // $dateEnd = $dateRange->endDate;

        // $dateStart = new \DateTime($dateStart, $this->app->getUserTimezone()); 
        // $dateEnd = new \DateTime($dateEnd, $this->app->getUserTimezone()); 
        // $dateStart = \Snap\common::convertUTCToUserDatetime($dateStart);
        // $startAt = new \DateTime($dateStart->format('Y-m-d 00:00:00'));
        // $startAt = \Snap\common::convertUserDatetimeToUTC($startAt);
        // $endAt = new \DateTime($dateEnd->format('Y-m-d 23:59:59'));
        // $endAt = \Snap\common::convertUserDatetimeToUTC($endAt);

        // $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerid);
        // $from = $settings->dgpeakhourfrom;
        // $from->setTimeZone($this->app->getServerTimeZone());            
        // $to = $settings->dgpeakhourto;
        // $to->setTimeZone($this->app->getServerTimeZone());

        // $conditions = function ($q) use ($startAt, $endAt, $partnerid, $params, $from, $to) {
        //     $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
        //     $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
        //     if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
        //         $q->andWhere('ordpartnerid', 'IN', $partnerid);
        //     }
        //     else{
        //         $q->andWhere('ordpartnerid', $partnerid);
        //     }
        //     $q->andWhere(function($r) {
        //         $r->where('ordstatus', Order::STATUS_COMPLETED);
        //         $r->orWhere('ordstatus', Order::STATUS_CONFIRMED);
        //         $r->orWhere(function ($s) {
        //             $s->where('ordstatus', Order::STATUS_PENDING);
        //             $s->andWhere('ordtype', Order::TYPE_COMPANYBUY);
        //         });
        //     });

        //     if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
        //         $q->where(function($r) use ($from, $to) {
        //             $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
        //             $r->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
        //         });
        //     } else {
        //         $q->where(function($r) use ($from, $to) {
        //             $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
        //             $r->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
        //         });
        //     }
        // };

        if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
            $modulename .= '_NON_PEAK_HOUR';
        }

        if (is_array($partnerid)) {
            list($header, $conditions) = $this->handleArrayPartnersExportExcel($params, $partnerid);
        } else {
            list($header, $conditions) = $this->handleSinglePartnerExportExcel($params, $partnerid);
        }


        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null);
    }


    protected function handleArrayPartnersExportExcel($params, array $partnerid)
    {
        $settings = $this->app->mypartnersettingStore()
                    ->searchTable()
                    ->select()
                    ->where('partnerid', 'IN',$partnerid)
                    ->forwardKey('partnerid')
                    ->get();

        $conditions                   = null;
        $header                       = json_decode($params["header"]);
        $dateRange                    = json_decode($params["daterange"]);

        $companyBuyType  = Order::TYPE_COMPANYBUY;
        $companySellType = Order::TYPE_COMPANYSELL;

        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('partnercommission' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                        $partnerCompanySellCommission = $setting->dgpartnersellcommission ?? 0;
                        $partnerCompanyBuyCommission  = $setting->dgpartnerbuycommission ?? 0;
                    } else {
                        $partnerCompanySellCommission = $setting->dgpeakpartnersellcommission ?? 0;
                        $partnerCompanyBuyCommission  = $setting->dgpeakpartnerbuycommission ?? 0;
                    }
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$partnerCompanyBuyCommission}
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$partnerCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}partnercommission`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('partnercommissionpergram' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                        $partnerCompanySellCommission = $setting->dgpartnersellcommission ?? 0;
                        $partnerCompanyBuyCommission  = $setting->dgpartnerbuycommission ?? 0;
                    } else {
                        $partnerCompanySellCommission = $setting->dgpeakpartnersellcommission ?? 0;
                        $partnerCompanyBuyCommission  = $setting->dgpeakpartnerbuycommission ?? 0;
                    }
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$partnerCompanyBuyCommission}
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$partnerCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}partnercommissionpergram`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('acecommission' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    $aceCompanySellCommission     = $setting->dgacesellcommission ?? 0;
                    $aceCompanyBuyCommission      = $setting->dgacebuycommission ?? 0;
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$aceCompanyBuyCommission} 
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$aceCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}acecommission`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('acecommissionpergram' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    $aceCompanySellCommission     = $setting->dgacesellcommission ?? 0;
                    $aceCompanyBuyCommission      = $setting->dgacebuycommission ?? 0;
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$aceCompanyBuyCommission} 
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$aceCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}acecommissionpergram`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('affiliatecommission' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    $affiliateCompanySellCommission     = $setting->dgaffiliatesellcommission ?? 0;
                    $affiliateCompanyBuyCommission      = $setting->dgaffiliatebuycommission ?? 0;
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$affiliateCompanyBuyCommission} 
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN `{$prefix}ordxau` * {$affiliateCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}affiliatecommission`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('affiliatecommissionpergram' === $column->index) {
                $rawQuery = "CASE ";
                foreach ($settings as $setting) {
                    $affiliateCompanySellCommission     = $setting->dgaffiliatesellcommission ?? 0;
                    $affiliateCompanyBuyCommission      = $setting->dgaffiliatebuycommission ?? 0;
                    $rawQuery .=  "WHEN `{$prefix}ordtype` = '{$companyBuyType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$affiliateCompanyBuyCommission} 
                                   WHEN `{$prefix}ordtype` = '{$companySellType}' AND `{$prefix}ordpartnerid` = {$setting->partnerid} THEN {$affiliateCompanySellCommission}" . PHP_EOL;
                    
                }
                $rawQuery .=  " END as `{$prefix}affiliatecommissionpergram`";
                $header[$key]->index = $this->currentStore->searchTable(false)->raw($rawQuery);
                $header[$key]->index->original = $original;
            } elseif ('ordstatus' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordstatus` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
                     WHEN `{$prefix}ordstatus` = " . Order::STATUS_PENDING . " THEN 'Pending Payment'
                     WHEN `{$prefix}ordstatus` = " . Order::STATUS_COMPLETED . " THEN 'Completed' END as `{$prefix}ordstatus`"
                );
                $header[$key]->index->original = $original;
            }
        }

        if (0 < $partnerid) {
            $conditions = ['ordpartnerid' => $partnerid];
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

        $conditions = function ($q) use ($startAt, $endAt, $partnerid, $params, $settings) {
            $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
            $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            // if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            //     $q->andWhere('ordpartnerid', 'IN', $partnerid);
            // }
            // else{
            //     $q->andWhere('ordpartnerid', $partnerid);
            // }
            $q->andWhere('ordpartnerid', 'IN', $partnerid);
            $q->andWhere(function($r) {
                $r->where('ordstatus', Order::STATUS_COMPLETED);
                $r->orWhere('ordstatus', Order::STATUS_CONFIRMED);
                $r->orWhere(function ($s) {
                    $s->where('ordstatus', Order::STATUS_PENDING);
                    $s->andWhere('ordtype', Order::TYPE_COMPANYBUY);
                });
            });

            // -- NON PEAK
            // WHERE (
            //    (partnerid = AAA AND (time < peakhourpartnerAAAfrom OR time > peakhourpartnerAAAto )
            //    OR (partnerid = BBB AND (time < peakhourpartnerBBfrom OR time > peakhourpartnerBBBto )
            //    OR (partnerid = CCC AND (time < peakhourpartnerBBfrom OR time > peakhourpartnerCCCto )
            // -- PEAK
            // WHERE (
            //    (partnerid = AAA AND (time < peakhourpartnerAAAfrom AND time > peakhourpartnerAAAto )
            //    OR (partnerid = BBB AND (time >= peakhourpartnerBBfrom AND time <= peakhourpartnerBBBto )
            //    OR (partnerid = CCC AND (time >= peakhourpartnerBBfrom AND time <= peakhourpartnerCCCto )
            $q->where(function($sqlQuery) use ($settings, $params) {
                foreach($settings as $setting) {
                    
                    $sqlQuery->orWhere(function($query) use ($params, $setting) {
                        
                        $from = $setting->dgpeakhourfrom;
                        $from->setTimeZone($this->app->getServerTimeZone());            
                        $to = $setting->dgpeakhourto;
                        $to->setTimeZone($this->app->getServerTimeZone());
                        
                        $query->where('ordpartnerid', $setting->partnerid);
                        if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                            $query->where(function($qr) use ($from, $to) {
                                $qr->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                                $qr->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                            });
                        } else {
                            $query->where(function($qr) use ($from, $to) {
                                $qr->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                                $qr->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                            });
                        }
                    });
                    
                }
            });
        };

        return [$header, $conditions];
    }

    protected function handleSinglePartnerExportExcel($params, $partnerid)
    {
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerid);

        $partnerCompanySellCommission = 0;
        $partnerCompanyBuyCommission  = 0;
        $aceCompanySellCommission     = 0;
        $aceCompanyBuyCommission      = 0;
        $conditions                   = null;
        $header                       = json_decode($params["header"]);
        $dateRange                    = json_decode($params["daterange"]);

        if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
            $partnerCompanySellCommission = $settings->dgpartnersellcommission ?? 0;
            $partnerCompanyBuyCommission  = $settings->dgpartnerbuycommission ?? 0;
        } else {
            
            $partnerCompanySellCommission = $settings->dgpeakpartnersellcommission ?? 0;
            $partnerCompanyBuyCommission  = $settings->dgpeakpartnerbuycommission ?? 0;
        }
        
        $aceCompanySellCommission     = $settings->dgacesellcommission ?? 0;
        $aceCompanyBuyCommission      = $settings->dgacebuycommission ?? 0;

        $affiliateCompanySellCommission     = $settings->dgaffiliatesellcommission ?? 0;
        $affiliateCompanyBuyCommission      = $settings->dgaffiliatebuycommission ?? 0;

        $companyBuyType  = Order::TYPE_COMPANYBUY;
        $companySellType = Order::TYPE_COMPANYSELL;

        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('partnercommission' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN `{$prefix}ordxau` * {$partnerCompanyBuyCommission} 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN `{$prefix}ordxau` * {$partnerCompanySellCommission} END as `{$prefix}partnercommission`"
                );

                $header[$key]->index->original = $original;
            } elseif ('partnercommissionpergram' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN '{$partnerCompanyBuyCommission}' 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN '{$partnerCompanySellCommission}' END as `{$prefix}partnercommissionpergram`"
                );

                $header[$key]->index->original = $original;
            } elseif ('acecommission' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanyBuyCommission} 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN `{$prefix}ordxau` * {$aceCompanySellCommission} END as `{$prefix}acecommission`"
                );
                $header[$key]->index->original = $original;
            } elseif ('acecommissionpergram' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN '{$aceCompanyBuyCommission}' 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN '{$aceCompanySellCommission}' END as `{$prefix}acecommissionpergram`"
                );
                $header[$key]->index->original = $original;
            } elseif ('affiliatecommission' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN `{$prefix}ordxau` * {$affiliateCompanyBuyCommission} 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN `{$prefix}ordxau` * {$affiliateCompanySellCommission} END as `{$prefix}affiliatecommission`"
                );
                $header[$key]->index->original = $original;
            } elseif ('affiliatecommissionpergram' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordtype` = '{$companyBuyType}' 
                          THEN '{$affiliateCompanyBuyCommission}' 
                          WHEN `{$prefix}ordtype` = '{$companySellType}' 
                          THEN '{$affiliateCompanySellCommission}' END as `{$prefix}affiliatecommissionpergram`"
                );
                $header[$key]->index->original = $original;
            } elseif ('ordstatus' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ordstatus` = " . Order::STATUS_CONFIRMED . " THEN 'Confirmed'
                     WHEN `{$prefix}ordstatus` = " . Order::STATUS_PENDING . " THEN 'Pending Payment'
                     WHEN `{$prefix}ordstatus` = " . Order::STATUS_COMPLETED . " THEN 'Completed' END as `{$prefix}ordstatus`"
                );
                $header[$key]->index->original = $original;
            }
        }

        if (0 < $partnerid) {
            $conditions = ['ordpartnerid' => $partnerid];
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

        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partnerid);
        $from = $settings->dgpeakhourfrom;
        $from->setTimeZone($this->app->getServerTimeZone());            
        $to = $settings->dgpeakhourto;
        $to->setTimeZone($this->app->getServerTimeZone());

        $conditions = function ($q) use ($startAt, $endAt, $partnerid, $params, $from, $to) {
            $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
            $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
                $q->andWhere('ordpartnerid', 'IN', $partnerid);
            }
            else{
                $q->andWhere('ordpartnerid', $partnerid);
            }
            $q->andWhere(function($r) {
                $r->where('ordstatus', Order::STATUS_COMPLETED);
                $r->orWhere('ordstatus', Order::STATUS_CONFIRMED);
                $r->orWhere(function ($s) {
                    $s->where('ordstatus', Order::STATUS_PENDING);
                    $s->andWhere('ordtype', Order::TYPE_COMPANYBUY);
                });
            });

            if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                $q->where(function($r) use ($from, $to) {
                    $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                    $r->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                });
            } else {
                $q->where(function($r) use ($from, $to) {
                    $r->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                    $r->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                });
            }
        };

        return [$header, $conditions];
    }


    function getMerchantList($app,$params){
		//for now this is dummy data. The id is taken from existing partner
		// $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
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
        if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
        }
        if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }
        // $partners = $app->partnerStore()->searchTable()->select()->where('group','=', 'PKB@UAT')->execute();
        $merchantdata=array();
        foreach ($partners as $partner){
             $arr = array('id'=>$partner->id,'name'=>$partner->name,'parent'=>$partner->parent);
             array_push($merchantdata,$arr);
        }
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}

    protected function handleArrayPartnersPreQueryListing($params, $sqlHandle, $fields, $partnerId)
    {
        $settingsArr = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN',$partnerId)->get();
        
        // -- NON PEAK
        // WHERE (
        //    (partnerid = AAA AND (time < peakhourpartnerAAAfrom OR time > peakhourpartnerAAAto )
        //    OR (partnerid = BBB AND (time < peakhourpartnerBBfrom OR time > peakhourpartnerBBBto )
        //    OR (partnerid = CCC AND (time < peakhourpartnerBBfrom OR time > peakhourpartnerCCCto )
        // -- PEAK
        // WHERE (
        //    (partnerid = AAA AND (time < peakhourpartnerAAAfrom AND time > peakhourpartnerAAAto )
        //    OR (partnerid = BBB AND (time >= peakhourpartnerBBfrom AND time <= peakhourpartnerBBBto )
        //    OR (partnerid = CCC AND (time >= peakhourpartnerBBfrom AND time <= peakhourpartnerCCCto )
        $sqlHandle->where(function($sqlQuery) use ($settingsArr, $params) {
            foreach($settingsArr as $setting) {
                
                $sqlQuery->orWhere(function($query) use ($params, $setting) {
                    
                    $from = $setting->dgpeakhourfrom;
                    $from->setTimeZone($this->app->getServerTimeZone());            
                    $to = $setting->dgpeakhourto;
                    $to->setTimeZone($this->app->getServerTimeZone());
                    
                    $query->where('ordpartnerid', $setting->partnerid);
                    if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                        $query->where(function($q) use ($from, $to) {
                            $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<', $from->format('H:i:s'));
                            $q->orWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>', $to->format('H:i:s'));            
                        });
                    } else {
                        $query->where(function($q) use ($from, $to) {
                            $q->where(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '>=', $from->format('H:i:s'));
                            $q->andWhere(new \ClanCats\Hydrahon\Query\Expression('TIME(gtr_createdon)'), '<=', $to->format('H:i:s'));            
                        });
                    }
                });
                
            }
        });


        return $sqlHandle;
    }

    protected function handleArrayPartnersPreListing($params, $records, array $partners) 
    {
        $partnerIds = [];
        foreach ($partners as $partner) {
            array_push($partnerIds, $partner->id);
        }

        $settings = $this->app->mypartnersettingStore()->searchTable()->select()->where('partnerid', 'IN', $partnerIds)->forwardKey('partnerid')->get();
        $partners = $this->app->partnerStore()->searchTable()->select()->where('id', 'IN', $partnerIds)->forwardKey('partnerid')->get();
        
        foreach ($records as $key => $record) {

            if (true == filter_var($params['nonpeak'], FILTER_VALIDATE_BOOL)) {
                $partnerCompanySellCommission = $settings[$record['ordpartnerid']]->dgpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings[$record['ordpartnerid']]->dgpartnerbuycommission ?? 0;
            } else {            
                $partnerCompanySellCommission = $settings[$record['ordpartnerid']]->dgpeakpartnersellcommission ?? 0;
                $partnerCompanyBuyCommission  = $settings[$record['ordpartnerid']]->dgpeakpartnerbuycommission ?? 0;
            }        
            
            $aceCompanySellCommission = $settings[$record['ordpartnerid']]->dgacesellcommission ?? 0;
            $aceCompanyBuyCommission  = $settings[$record['ordpartnerid']]->dgacebuycommission ?? 0;
    
            $affiliateCompanySellCommission = $settings[$record['ordpartnerid']]->dgaffiliatesellcommission ?? 0;
            $affiliateCompanyBuyCommission  = $settings[$record['ordpartnerid']]->dgaffiliatebuycommission ?? 0;

            $calc = $partners[$records->ordpartnerid]->calculator();
            if ('CompanySell' === $record['ordtype']) {
                $records[$key]['partnercommission']          = $calc->multiply($record['ordxau'], $partnerCompanySellCommission);
                $records[$key]['partnercommissionpergram']   = $calc->round($partnerCompanySellCommission);
                $records[$key]['acecommission']              = $calc->multiply($record['ordxau'], $aceCompanySellCommission);
                $records[$key]['acecommissionpergram']       = $calc->round($aceCompanySellCommission);
                $records[$key]['affiliatecommission']        = $calc->multiply($record['ordxau'], $affiliateCompanySellCommission);
                $records[$key]['affiliatecommissionpergram'] = $calc->round($affiliateCompanySellCommission);
            } elseif ('CompanyBuy' === $record['ordtype']) {
                $records[$key]['partnercommission']          = $calc->multiply($record['ordxau'], $partnerCompanyBuyCommission);
                $records[$key]['partnercommissionpergram']   = $calc->round($partnerCompanyBuyCommission);
                $records[$key]['acecommission']              = $calc->multiply($record['ordxau'], $aceCompanyBuyCommission);
                $records[$key]['acecommissionpergram']       = $calc->round($aceCompanyBuyCommission);
                $records[$key]['affiliatecommission']        = $calc->multiply($record['ordxau'], $affiliateCompanyBuyCommission);
                $records[$key]['affiliatecommissionpergram'] = $calc->round($affiliateCompanyBuyCommission);
            }
        }
        

        return $records;
    }
}
