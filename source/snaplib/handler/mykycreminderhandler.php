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

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class mykycreminderhandler extends CompositeHandler
{
    function __construct(App $app)
    {
        // parent::__construct('/root/bmmb/report', 'commission');

        $this->mapActionToRights('list', '/root/bmmb/report/commission/list;/root/go/report/commission/list;/root/one/report/commission/list;/root/onecall/report/commission/list;/root/air/report/commission/list;/root/mcash/report/commission/list;/root/toyyib/report/commission/list;/root/ktp/report/commission/list;/root/kopetro/report/commission/list;/root/kopttr/report/commission/list;/root/pkbaffi/report/commission/list;/root/bumira/report/commission/list;/root/nubex/report/commission/list;/root/hope/report/commission/list;/root/mbsb/report/commission/list;/root/red/report/commission/list;/root/kodimas/report/commission/list;');
        $this->mapActionToRights('exportExcel', '/root/bmmb/report/commission/list;/root/go/report/commission/list;/root/one/report/commission/list;/root/onecall/report/commission/list;/root/air/report/commission/list;/root/mcash/report/commission/list;/root/toyyib/report/commission/list;/root/ktp/report/commission/list;/root/kopetro/report/commission/list;/root/kopttr/report/commission/list;/root/pkbaffi/report/commission/list;/root/bumira/report/commission/list;/root/nubex/report/commission/list;/root/hope/report/commission/list;/root/mbsb/report/commission/list;/root/red/report/commission/list;/root/kodimas/report/commission/list;');

        $this->app = $app;

        $this->currentStore = $app->mykycreminderStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
            $app = App::getInstance();
            //$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
    
            // Start Query
            if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }
            //added on 13/12/2021
            else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }    
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }
            else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }
            else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }
            else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->andWhere('parent','=', '2')->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }
            else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }
            else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
                $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
                
                $partnerId = array();
                foreach ($partners as $partner){
                    array_push($partnerId,$partner->id);
                }
                $sqlHandle->andWhere('accountholderid', 'IN', $params['id']);
            }else{
                $sqlHandle->andWhere('accountholderid', $params['id']);
            }

        

        return array($params, $sqlHandle, $fields);
    }

   
    function onPreListing($objects, $params, $records)
    {
        $accHolderIds = [];
        // $max = count($records);
        foreach ($records as $key => $record) {
        
            $records[$key]['index'] = $key+1;
            // $max--;
        }

        return $records;
    }
}
