<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use \Snap\store\dbdatastore as DbDatastore;
use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\MyConversion;
use Snap\object\Redemption;
use Snap\object\rebateConfig;
use Snap\object\Partner;
use Snap\object\Logistic;
use Snap\api\casa\BaseCasa;

use Snap\api\exception\MyGtpProductInvalid;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myconversionHandler extends redemptionHandler
{


    function __construct(App $app)
    {

        $this->app = $app;

        $redemptionStore = $app->myconversionFactory();
        //parent::$children = [];
        $this->currentStore = $redemptionStore;
        $this->addChild(new ext6gridhandler($this, $redemptionStore, 1));

        parent::__construct($app);

        $this->mapActionToRights("exportExcel", '/root/bmmb/redemption/export;/root/go/redemption/export;/root/one/redemption/export;/root/onecall/redemption/export;/root/air/redemption/export;/root/mcash/redemption/export;/root/toyyib/redemption/export;/root/ktp/redemption/export;/root/kopetro/redemption/export;/root/kopttr/redemption/export;/root/pkbaffi/redemption/export;/root/bumira/redemption/export;/root/nubex/redemption/export;/root/hope/redemption/export;/root/mbsb/redemption/export;/root/red/redemption/export;/root/kodimas/redemption/export;/root/kgoldaffi/redemption/export;/root/koponas/redemption/export;/root/wavpay/redemption/export;/root/noor/redemption/export;/root/bsn/redemption/export;/root/alrajhi/redemption/export;/root/posarrahnu/redemption/export;/root/waqaf/redemption/export;/root/igold/redemption/export;/root/kasih/redemption/export;/root/bursa/redemption/export;');
        $this->mapActionToRights('detailview', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/noor/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
		$this->mapActionToRights('list', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/noor/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
        $this->mapActionToRights('getMerchantList', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/noor/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
        $this->mapActionToRights('updateStatus', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/noor/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
        $this->mapActionToRights('sendEmail', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/nooro/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
        $this->mapActionToRights('getConversionFee', '/root/bmmb/redemption/list;/root/go/redemption/list;/root/one/redemption/list;/root/onecall/redemption/list;/root/air/redemption/list;/root/mcash/redemption/list;/root/toyyib/redemption/list;/root/ktp/redemption/list;/root/kopetro/redemption/list;/root/kopttr/redemption/list;/root/pkbaffi/redemption/list;/root/bumira/redemption/list;/root/nubex/redemption/list;/root/hope/redemption/list;/root/mbsb/redemption/list;/root/red/redemption/list;/root/kodimas/redemption/list;/root/kgoldaffi/redemption/list;/root/koponas/redemption/list;/root/wavpay/redemption/list;/root/nooro/redemption/list;/root/bsn/redemption/list;/root/alrajhi/redemption/list;/root/posarrahnu/redemption/list;/root/waqaf/redemption/list;/root/igold/redemption/list;/root/kasih/redemption/list;/root/bursa/redemption/list;');
        $this->mapActionToRights('doOtcConversion', '/all/access');
        $this->mapActionToRights('printConversionOTC', '/all/access');
        $this->mapActionToRights('checkConversionStatus', '/all/access');
        $this->mapActionToRights('checkFirstTimeConversion', '/all/access');
    }


    function onPreListing($objects, $params, $records)
    {
        foreach ($records as $x => $record) {
            $records[$x]['rdmtotalfee'] = $record['rdmredemptionfee'] + $record['rdminsurancefee'] + $record['rdmhandlingfee'];
            if ($record['status'] != MyConversion::STATUS_PAYMENT_PAID) {
                continue;
            }
            $printHtml = '';
            $items = json_decode($record['rdmitems']);
            foreach ($items as $item) {
                $printHtml .= '<tr>';
                $printHtml .=     '<td style="text-align:center; width:200px">' . $item->serialnumber . '</td>';
                $printHtml .=     '<td style="text-align:center; width:200px">' . $item->code . '</td>';
                $printHtml .= '</tr>';
            }
            $records[$x]['child'] = $printHtml;
        }
        return $records;
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        $app = App::getInstance();
        //$partnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('rdmpartnerid', $partnerId);
        }
        // OTC MODULES
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerIdBSN = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerIdBSN)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();

            //check user state permission
            $userId = $this->app->getUserSession()->getUserId();
            $user = $this->app->userStore()->getById($userId);
            
            if($user->state){
				$partners = array();
            
                $partnerArr = $this->app->partnerStore()->searchView(true,1)->select()
                    ->where('state', $user->state)
                    ->andWhere('group', $partnerIdBSN)
                    ->execute();
                
                $partners = $partnerArr;
            }

            if ($partnerId != 0) {
                $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            } else {

                $groupPartnerIds = array();
                foreach ($partners as $partner) {
                    array_push($groupPartnerIds, $partner->id);
                }
                $sqlHandle->andWhere('rdmpartnerid', 'IN', $groupPartnerIds);
            }
        } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();
            if ($partnerId != 0) {
                $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            } else {

                $groupPartnerIds = array();
                foreach ($partners as $partner) {
                    array_push($groupPartnerIds, $partner->id);
                }
                $sqlHandle->andWhere('rdmpartnerid', 'IN', $groupPartnerIds);
            }
        } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();
            if ($partnerId != 0) {
                $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            } else {

                $groupPartnerIds = array();
                foreach ($partners as $partner) {
                    array_push($groupPartnerIds, $partner->id);
                }
                $sqlHandle->andWhere('rdmpartnerid', 'IN', $groupPartnerIds);
            }
        }
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('rdmpartnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)
                ->whereIn('parent', [Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
                ->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)
                ->whereIn('parent', [Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
                ->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        } else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();

            $partnerId = array();
            foreach ($partners as $partner) {
                array_push($partnerId, $partner->id);
            }
            $sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('rdmpartnerid', 'IN', $partnerId);
        }
        if(isset($params['id'])){
			$sqlHandle->andWhere("accountholderid", $params['id']);
		}
        return array($params, $sqlHandle, $fields);
    }

    function exportExcel($app, $params)
    {

        //$bmmbpartnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};

        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_CONVERSION';
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_CONVERSION';
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_CONVERSION';
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $modulename = 'ONECALL_CONVERSION';
        } else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $modulename = 'AIR_CONVERSION';
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $modulename = 'MCASH_CONVERSION';
        } else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $modulename = 'TOYYIB_CONVERSION';
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $modulename = 'NUBEX_CONVERSION';
        } else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $modulename = 'HOPE_CONVERSION';
        } else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $modulename = 'MBSB_CONVERSION';
        } else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $modulename = 'REDGOLD_CONVERSION';
        } else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $modulename = 'WAVPAYGOLD_CONVERSION';
        } else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $modulename = 'NOOR_CONVERSION';
        } else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $modulename = 'IGOLD_CONVERSION';
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_CONVERSION';
        }
        // OTC MODULES
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'};
            $modulename = 'BSN_CONVERSION';
        } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            $modulename = 'ALRAJHI_CONVERSION';
        } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POS_CONVERSION';
        }
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'PITIH_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KOPETRO_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KOPTTR_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'PITIHAFFI_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KGOLD_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KGOLDAFFI_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KiGA_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        } else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'ANNURGOLD_CONVERSION';
            $partnerId = explode(",", $params['selected']);
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            $modulename = 'KASIHGOLD_CONVERSION';
            $partnerId = explode(",",$params['selected']);
        }
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        //$modulename = 'BMMB_CONVERSION';
        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        } elseif (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        }
        elseif (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $conditions = ['rdmpartnerid', "IN", $partnerId];
        }
        else{
            $conditions = ['rdmpartnerid' => $partnerId];
        }
        // $conditions = ['rdmpartnerid' => $partnerId];

        $redemptionStore = $app->myconversionFactory();
        //parent::$children = [];
        $this->currentStore = $redemptionStore;

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('status' === $column->index) {

                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . MyConversion::STATUS_PAYMENT_PENDING . " THEN 'Pending Payment'
                     WHEN `{$prefix}status` = " . MyConversion::STATUS_PAYMENT_PAID . " THEN 'Paid'
                     WHEN `{$prefix}status` = " . MyConversion::STATUS_EXPIRED . " THEN 'Expired'
                     WHEN `{$prefix}status` = " . MyConversion::STATUS_PAYMENT_CANCELLED . " THEN 'Payment Cancelled'
                     WHEN `{$prefix}status` = " . MyConversion::STATUS_REVERSED . " THEN 'Reversed' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }
            if ('rdmstatus' === $column->index) {

                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_PENDING . " THEN 'Pending'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_CONFIRMED . " THEN 'Confirmed'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_COMPLETED . " THEN 'Completed'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_FAILED . " THEN 'Failed'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_PROCESSDELIVERY . " THEN 'Process Delivery'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_CANCELLED . " THEN 'Cancelled'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_REVERSED . " THEN 'Reversed'
                     WHEN `{$prefix}rdmstatus` = " . Redemption::STATUS_FAILEDDELIVERY . " THEN 'Failed Delivery' END as `{$prefix}rdmstatus`"
                );
                $header[$key]->index->original = $original;
            }

            if ('code' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "JSON_EXTRACT(`{$prefix}rdmitems`, '$[*].code')  as `{$prefix}code`"
                );
                $header[$key]->index->original = $original;
            }
            if ('serialnumber' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "JSON_EXTRACT(`{$prefix}rdmitems`, '$[*].serialnumber')  as `{$prefix}serialnumber`"
                );
                $header[$key]->index->original = $original;
            }
        }

        $specialRenderer = [
            'decode' => 'json',
            'sqlfield' => 'rdmitems',
            'displayfield' => ['sapreturnid', 'serialnumber', 'code'],
            'separatefield' => ['serialnumber', 'code', 'rdmbranchid', 'rdmbranchname', 'rdmstatus', 'rdmtype', 'status', 'createdon'],
            'isdisplayedinreport' => false
        ];

        $this->app->reportingManager()->generateExportFileWithJsonFields($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, $specialRenderer);
    }

    /*
        This method is to get data for view details
    */
    function detailview($app, $params)
    {
        //$object = $app->orderfactory()->getById($params['id']);

        $object = $this->app->myconversionStore()->searchView()->select()
            ->where('id', $params['id'])
            ->one();


        $partner = $app->partnerFactory()->getById($object->rdmpartnerid);
        $accountholder = $app->myaccountholderFactory()->getById($object->accountholderid);
        $productname = $app->productFactory()->getById($object->productid)->name;

        if ($object->rdmbookingon == '0000-00-00 00:00:00' || !$$object->rdmbookingon) {
            $bookingOn = '0000-00-00 00:00:00';
        } else {
            $bookingOn = $object->rdmbookingon->format('Y-m-d H:i:s');
        }

        if ($object->rdmconfirmon == '0000-00-00 00:00:00' || !$$object->rdmconfirmon) {
            $confirmedOn = '0000-00-00 00:00:00';
        } else {
            $confirmedOn = $object->rdmconfirmon->format('Y-m-d H:i:s');
        }

        if ($object->rdmappointmentdatetime == '0000-00-00 00:00:00' || !$$object->rdmappointmentdatetime) {
            $appointmentDateTime = '0000-00-00 00:00:00';
        } else {
            $appointmentDateTime = $object->rdmappointmentdatetime->format('Y-m-d H:i:s');
        }

        if ($object->rdmappointmenton == '0000-00-00 00:00:00' || !$object->rdmappointmenton) {
            $appointmentOn = '0000-00-00 00:00:00';
        } else {
            $appointmentOn = $object->rdmappointmenton->format('Y-m-d H:i:s');
        }
        //$cancelledOn = $object->cancelon ? $object->cancelon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';
        //$reconciledOn = $object->reconciledon ? $object->reconciledon->format('Y-m-d h:i:s') : '0000-00-00 00:00:00';

        if ($object->confirmby > 0) $confirmeduser = $app->userFactory()->getById($object->confirmby)->name;
        else $confirmeduser = 'System';
        if ($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
        else $modifieduser = 'System';
        if ($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
        else $createduser = 'System';

        // Conversion Status
        if ($object->status == 0) {
            $statusname = 'Expired';
        } else if ($object->status == 1) {
            $statusname = 'Paid';
        } else if ($object->status == 2) {
            $statusname = 'Pending';
        } else if ($object->status == 3) {
            $statusname = 'Cancelled';
        } else {
            $statusname = 'Unidentified';
        }

        // Redemption Status
        if ($object->rdmstatus == 0) {
            $rdmStatusName = 'Pending';
        } else if ($object->rdmstatus == 1) {
            $rdmStatusName = 'Confirmed';
        } else if ($object->rdmstatus == 2) {
            $rdmStatusName = 'Completed';
        } else if ($object->rdmstatus == 3) {
            $rdmStatusName = 'Failed';
        } else if ($object->rdmstatus == 4) {
            $rdmStatusName = 'Process Delivery';
        } else if ($object->rdmstatus == 5) {
            $rdmStatusName = 'Reversed';
        } else if ($object->rdmstatus == 6) {
            $rdmStatusName = 'Failed Delivery';
        } else {
            $rdmStatusName = 'Unidentified';
        }

        $items = json_decode($object->rdmitems);
        $serialNo = $items[0]->serialnumber;
        $code = $items[0]->code;

        $bookingPrice =  $partner->calculator()->round($object->rdmbookingprice);
        $confirmedPrice =  $partner->calculator()->round($object->rdmconfirmprice);
        $weight = $partner->calculator(false)->round($object->rdmtotalweight);
        $totalEstValue = $partner->calculator()->round($object->rdmamount);
        $redemptionFee = $partner->calculator()->round($object->rdmredemptionfee);
        $insuranceFee = $partner->calculator()->round($object->rdminsurancefee);
        $handlingFee = $partner->calculator()->round($object->rdmhandlingfee);
        $specialDeliveryFee = $partner->calculator()->round($object->rdmspecialdeliveryfee);

        // Possible additions to detailview when needed
        /*
   
            'commissionfee'    => null,
            'premiumfee'    => null,

		  '
		*/
        $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $object->getRefNo(true));
        $transactionFees = 0;
        if ($paymentDetail) {
            $calc = $partner->calculator(true);
            $transactionFees = number_format($calc->add($paymentDetail->gatewayfee, $paymentDetail->customerfee), 2, '.', '');
        }
        $detailRecord['default'] = [ //"ID" => $object->id,
            'Redemption ID' => $object->redemptionid,
            'Partner Reference No' => $object->rdmpartnerrefno,
            'Redemption No' => $object->rdmredemptionno,
            'API Version' => $object->rdmapiversion,
            'Type' => $object->rdmtype,
            'Product' => $productname,
            'Account Holder Name' => $accountholder->fullname,
            'Account Holder Code' => $accountholder->accountholdercode,

            'Logistic Fee Payment Mode' => $object->logisticfeepaymentmode,

            'Serial No' => $serialNo,
            'Gold Code' => $code,

            'Price' => $finalAcePrice,
            'Total Weight' => $weight,
            'Total Amount' => $totalEstValue,
            'Total Items' => $object->rdmtotalquantity,

            // 'Redemption Fee' => $redemptionFee,
            'Premium Fee' => $partner->calculator()->round(strval($object->premiumfee)),
            'Insurance Fee' => $insuranceFee,
            'Handling Fee' => $partner->calculator()->round(strval($object->handlingfee)),
            'Channel Marketing Fund' => $partner->calculator()->round(strval($object->commissionfee)),
            'Packing & Shipment Fund' => $partner->calculator()->round(strval($object->courierfee)),
            'Special Delivery Fee' => $specialDeliveryFee,
            'Transaction Fees' => $transactionFees,

            'Remarks' => $object->rdmremarks,
            'Brand' => $object->rdmxaubrand,
            'Delivery Address 1' => $object->rdmdeliveryaddress1,
            'Delivery Address 2' => $object->rdmdeliveryaddress2,
            'Delivery Address 3' => $object->rdmdeliveryaddress3,
            'Delivery Postcode' => $object->rdmdeliverypostcode,
            'Delivery State' => $object->rdmdeliverystate,

            'Delivery Contact Name 1' => $object->rdmdeliverycontactname1,
            'Delivery No 1' => $object->rdmdeliverycontactno1,
            'Delivery Contact Name 2' => $object->rdmdeliverycontactname2,
            'Delivery No 2' => $object->rdmdeliverycontactno2,

            'Booking On' => $bookingOn,
            'Booking Price' => $bookingPrice,
            'Confirmed On' => $confirmedOn,
            'Confirmed Price' => $confirmedPrice,
            'Confirmed By' => $confirmeduser,

            'Appointment Date' => $appointmentDateTime,
            'Appointment On' => $appointmentOn,

            'Conversion Status' => $statusname,
            'Redemption Status' => $rdmStatusName,
            'Created on' => $object->createdon->format('Y-m-d H:i:s'),
            'Created by' => $createduser,
            //'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
            //'Modified by' => $modifieduser,
        ];

        echo json_encode(array('success' => true, 'record' => $detailRecord));
    }

    function getMerchantList($app, $params)
    {
        //for now this is dummy data. The id is taken from existing partner
        // $merchantdata = array(array('id'=>'2917155','name'=>'KTP1'),array('id'=>'2917154','name'=>'KTP2'),array('id'=>'45','name'=>'KTP3'),array('id'=>'2917159','name'=>'KTP4'));
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'}  ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)
                ->whereIn('parent', [Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
                ->execute();
        } else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)
                ->whereIn('parent', [Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
                ->execute();
        } else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        } else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group', '=', $partnerId)->execute();
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
        }

        $merchantdata = array();
        foreach ($partners as $partner) {
            $arr = array('id' => $partner->id, 'name' => $partner->name, 'parent' => $partner->parent);
            array_push($merchantdata, $arr);
        }
        return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
    }

    public function updateStatus($app, $params)
    {

        $records = $this->app->myconversionStore()->searchView()->select()->whereIn('id', $params['id'])->execute();
        if ($records) {
            try {
                //$return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);

                $return = $app->myGtpConversionManager()->updateManualConversionStatus($records);

                echo json_encode(['success' => true]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
        }
    }

    public function sendEmail($app, $params)
    {

        $records = $this->app->myconversionStore()->searchView()->select()->whereIn('id', $params['id'])->execute();
        $email = 'helpdesk@ace2u.com';
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
        } else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
        } else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
        } else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
        } else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
        } else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
        } else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
        } else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            // $partner = $this->app->partnerStore()->getById($partnerId);
            // $email = 'ang@silverstream.my';
        } else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
        } else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
        } else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
        } else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
        } else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
        } else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
        } else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
        }
        // OTC Modules
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'};
        } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }
        if ($records) {
            try {
                //$return = $app->bankvaultManager()->markItemsArrivedAtLocation($items);

                $app->myGtpConversionManager()->sendEmailNotificationToPartner($records);

                echo json_encode(['success' => true]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'Item not available']);
        }
    }

    // Perform check to filter records
    private function getPartnerIdForBranch()
    {
        $app = App::getInstance();
        $userId = $app->getUserSession()->getUserId();
        $user = $app->userStore()->getById($userId);
        if ($user->partnerid > 0) {
            $partnerid = $user->partnerid;
        } else {
            $partnerid = 0;
        }
        return $partnerid;
    }

    // SET PARAMS
    /*
    * Params
    *
    *
    */
    public function getConversionFee($app, $params)
    {

        try {
            $product = $app->productStore()->getByField('code',  $params['product_code']);

            if ("DG-999-9" == $product->code) {
                $this->log(__METHOD__ . "(): Unable to perform conversion for product (Digital Gold)");
                throw MyGtpProductInvalid::fromTransaction([], ['code' => $product->code]);
            }

            $settlementMethodMap = [
                'fpx' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX,
                'wallet' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET,
            ];

            if (!isset($params['payment_mode'])) {
                $paymentMode = MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX;
            } else {
                $paymentMode = $settlementMethodMap[$params['payment_mode']];
            }

            $myaccountholder = $app->myaccountholderStore()->getById($params['accountholder_id']);
            $partner = $app->partnerStore()->getById($myaccountholder->partnerid);

            if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				// For Alrajhi get casa accounts
				$input['partyId'] = $myaccountholder->partnercusid;
				$casaAccounts = $this->getCasaAccounts($app, $input);
			} else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
				//$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
				// For Alrajhi get casa accounts
				$input['partyId'] = $myaccountholder->partnercusid;
				$casaAccounts = $this->getCasaAccounts($app, $input);
			}

            /*additional data to add to pass to manager*/
            $myaccountholder->additionaldata = array(
                "methodofreceive" => $params['methodofreceive'],
                "collectedbranchcode" => $params['collectedbranchcode'],
            );

            $generatedData = $app->mygtpconversionManager()
                ->getPreBookConversionAmount($myaccountholder, $partner, $product, $params['quantity'], $paymentMode);


            $data = [
                // 'conversion_fee' => floatval($generatedData['totalRedemptionFee']),
                // 'insurance_fee'  => floatval($generatedData['totalInsuranceFee']),
                // 'courier_fee'    => floatval($generatedData['totalCourierCharges']),
                // 'transaction_fee'        => floatval($generatedData['totalFpxFee'])
                'conversion_fee' => floatval($generatedData['totalRedemptionFee']),
                'insurans_fee' => floatval($generatedData['totalInsuranceFee']),
                'courier_charges' => floatval($generatedData['totalCourierCharges']),
                'transaction_fee' => floatval($generatedData['totalPaymentFee']),
                'totalDeliveryFee' => floatval($generatedData['totalDeliveryFee']),
                'totalHandlingFee' => floatval($generatedData['totalHandlingFee']),
                'payment_mode' => $params['payment_mode'] ?? 'fpx',
                'casa_accounts' => ($casaAccounts) ? $casaAccounts : null,
            ];
            $data['total_fee'] = $data['conversion_fee'] + $data['transaction_fee'] + $data['insurans_fee'] + $data['courier_charges'];

            echo json_encode(array('success' => true, 'data' => $data));
        } catch (\Exception $e) {
            echo json_encode(array('success' => false, 'errorMessage' => $e->getMessage()));
        }
    }

    /*
	* Function to do OTC Conversion manually in system
	* 1) string order_type
	* 2) string Settlement method
	* 3) int accountholder id
	*/
    public function doOtcConversion($app, $params)
    {
        try {
            // Start
            // Get partner id from teller session for now
            // Basically the acquired partner id is by branch AKA partner 
            $userId = $app->getUserSession()->getUserId();
            $user = $app->userStore()->getById($userId);
            // GET USER PARTNER ID FOR BRANCH
            if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
            } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
            } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
                //$partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                $partnerId = $app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            }
            // Main branch purchase by default
            $finalPartnerId = $user->partnerid != 0 ? $user->partnerid : $partnerId;

            $partner = $app->partnerStore()->getById($finalPartnerId);
            $version = "1.0my";
            $productCode = $params['product_code'] ? $params['product_code'] : "DG-999-9";
            $product = $app->productStore()->getByField('code', $productCode);
            $partnerData = $params['partner_data'] ? $params['partner_data'] : '';

            if ("DG-999-9" == $product->code) {
                $this->log(__METHOD__ . "(): Unable to perform conversion for product (Digital Gold)");
                throw MyGtpProductInvalid::fromTransaction([], ['code' => $product->code]);
            }

            $settlementMethodMap = [
                'fpx' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX,
                'wallet' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_WALLET,
                'casa' => MyConversion::LOGISTIC_FEE_PAYMENT_MODE_CASA
            ];

            if (!isset($params['payment_mode'])) {
                //$paymentMode = MyConversion::LOGISTIC_FEE_PAYMENT_MODE_FPX;        
                if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                    $settlementType = 'casa';
                } else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
                    $settlementType = 'casa';
                } else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
                    $settlementType = 'cash';
                }
            } else {
                $settlementType = $params['payment_mode'];
            }
            $paymentMode = $settlementMethodMap[$settlementType];
            
            // Get account holder
            $accHolder = $app->myaccountHolderStore()->getById($params['accountholder_id']);
            // check for note and accesstoken
            $accHolder->note = $params['note']; // 20220406 - add new parameter note under accountholder. Toyyib wallet
            $accHolder->accesstoken = $params['access_token'] ? $params['access_token'] : null; //grab accesstoken

            if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
                $additionalData = $app->achadditionaldataStore()->searchTable()->select()
                ->where('accountholderid',$accHolder->id)
                ->one();

                $additionalData->mailingaddress1 = $params['address1'] ?? null;
                $additionalData->mailingaddress2 = $params['address2'] ?? null;
                $additionalData->mailingpostcode = $params['postcode'] ?? null;
                $additionalData->mailingstate = $params['state'] ?? null;

                $additionalData = $app->achadditionaldataStore()->save($additionalData);
            } 
         

            /*additional data to add to pass to manager*/
            // $accHolder->additionaldata = array(
            //     "methodofreceive" => $decodedData['methodofreceive'],
            //     "collectedbranchcode" => $decodedData['collectedbranchcode'],
            // );
            // To be confirmed 
            $conversions = $app->mygtpconversionManager()
                ->doConversion($accHolder, $partner, $product, $params['quantity'], $version, $paymentMode, $params['campaign_code'], $partnerData);
            // Get payment wall location
            $matches = [];
            preg_match('/^CV\d+/', $conversions[0]->refno, $matches);
            $payment = $app->mypaymentdetailStore()->getByField('sourcerefno', $matches[0]);

            if (!empty($params['note'])) { //Toyyib wallet case because when Toyyib return success, it means already paid. add by DK-20220420
                //overwrite conversion obj for Toyyib
                $refno = $conversions[0]->refno;
                $conversions[0] = $app->myconversionStore()->getByField('refno', $refno);
            }

            $data = [
                'location'  => $payment->location
            ];

            // Format response
            $conversionData = $this->formatConversionHistory($app, $conversions);
            foreach ($conversionData as &$cvData) {
                unset($cvData['items'][0]['serialnumber'], $cvData['logistics_log']);
            }
            unset($cvData);
            foreach ($conversionData as $cvData) {
                $data['conversions'][] = $cvData;
            }


            $convert = $app->myconversionStore()->searchView()->select()
                ->where('refno',$data['conversions'][0]['refno'])
                ->one();

            $fees = [
                'courierCharges'  => $convert->handlingfee + $convert->courierfee,
                'insuransfee'     => $convert->rdminsurancefee,
                'redemptionfee'     => $convert->rdmredemptionfee,
            ];
            // $response = [
            //     'success' => true,
            //     'data' => $data,
            // ];

            // $sender = MyGtpApiSender::getInstance("Json", null);
            // $sender->response($app, $response);
            // return $response;
            // End
            echo json_encode(array('success' => true, 'record' => $data, 'fees' => $fees));
        } catch (\Exception $e) {
            $this->log("Failed to perform OTC Conversion for user with Accountholder ID : " . $accHolder->id, SNAP_LOG_ERROR);
            // $return = [
            // 	'error' => true,
            // 	'message' => $e->getMessage()
            // ];
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function printConversionOTCBSN($app, $params){

		// require_once '../../vendor/phpoffice/phpword/src/PhpWord/Autoloader.php';
		// \PhpOffice\PhpWord\Autoloader::register();

		$redemptionmanager= $app->redemptionManager();
		
		// // Check if Buy Or Sell
		try{
			// do double check
			// if have data do preview
			//print_r($params['data']);exit;
			if(!$params['data']){
				
				if(!$params['customerid']){
					$convertPdf = $redemptionmanager->printConversionOTC($params['refno']);
				}else{
					$convertPdf = $redemptionmanager->printConversionOTC($params['refno'], $params['customerid']);
				}

				//print_r($orderPdf);exit;

				$userId = $app->getUserSession()->getUserId();
        		$user = $app->userStore()->getById($userId);
        		$teller = $user->username;

				
				$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/convert_receipt_template.docx');

				$templateProcessor->setValues([
					'partner_name' => $convertPdf['partner_name'],
					'receipt_no' => $convertPdf['receipt_no'],
					'order_no' => $convertPdf['transactionid'],
					'date' => $convertPdf['date'],
					'full_name' => $convertPdf['fullname'],
                    'mykad_no' => $convertPdf['mykadno'],
					'finalAcePriceTitle' => $convertPdf['finalAcePriceTitle'],
                    'making_charges' => $convertPdf['making_charges'],
                    'delivery_fee' => $convertPdf['delivery_fee'],
                    'total_redemption_fee' => $convertPdf['total_redemption_fee'],
					'final_total' => $convertPdf['final_total'],
					'weight' => $convertPdf['xau'],
					'teller' => $teller,
                    'delivery_address' => $convertPdf['address'],
					'accountholder_code' => $convertPdf['accountholdercode'],
					'casa_bankaccount' => $convertPdf['casa_bankaccount'],
                    'contact_no' => $convertPdf['contact_no'],
                    'product_1g' => isset($convertPdf['arrProduct']['GS-999-9-1g']) ? $convertPdf['arrProduct']['GS-999-9-1g']/$convertPdf['totalTransaction'] : 0 ,
                    'product_5g' => isset($convertPdf['arrProduct']['GS-999-9-5g']) ? $convertPdf['arrProduct']['GS-999-9-5g']/$convertPdf['totalTransaction'] : 0 ,
                    'product_10g' => isset($convertPdf['arrProduct']['GS-999-9-10g']) ? $convertPdf['arrProduct']['GS-999-9-10g']/$convertPdf['totalTransaction'] : 0 ,
                    'product_50g' => isset($convertPdf['arrProduct']['GS-999-9-50g']) ? $convertPdf['arrProduct']['GS-999-9-50g']/$convertPdf['totalTransaction'] : 0 ,
                    'product_100g' => isset($convertPdf['arrProduct']['GS-999-9-100g']) ? $convertPdf['arrProduct']['GS-999-9-100g']/$convertPdf['totalTransaction'] : 0 ,
				]);
				$filename = $convertPdf['finalAcePriceTitle'].'_Receipt_'.$convertPdf['transactionid'].'.docx';

				$templateProcessor->saveAs('word/'.$filename);
				$fileUrl = 'word/'.$filename;
				//return("word/".$filename);
				$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
                                        $pdfPath = $matches[1];

                                        echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);
			}else{
				// do preview

				$userId = $app->getUserSession()->getUserId();
        		$user = $app->userStore()->getById($userId);
        		$partnerId = $user->partnerid;

            	$userobj = $app->partnerStore()->getById($partnerId);
            	$partnername = $userobj->name;

				$data = json_decode($params['data']);


				$finalAcePriceTitle = "REDEMPTION";


				$datetime = str_replace("GMT 0800 (Malaysia Time)","",$data->date);
            	$datetime = strtotime($datetime);
            	$datetime = date('Y-m-d H:i:s', $datetime);

				$weight = number_format($data->xau,3);
				$totalbuy = number_format(preg_replace("/[^0-9.]/", "", $data->amount),3);
				$finaltotal = number_format(preg_replace("/[^0-9.]/", "", $data->finaltotal),2);

                $product_1g = '';
                $product_5g = '';
                $product_10g = '';
                $product_50g = '';
                $product_100g = '';

                $product_1g_weight = '';
                $product_5g_weight = '';
                $product_10g_weight = '';
                $product_50g_weight = '';
                $product_100g_weight = '';

                if($data->product == '1g'){
                    $product_1g = $data->quantity;
                    $product_1g_weight = $product_1g*1;
                }else if($data->product == '5g'){
                    $product_5g = $data->quantity;
                    $product_5g_weight = $product_5g*5;
                }else if($data->product == '10g'){
                    $product_10g = $data->quantity;
                    $product_10g_weight = $product_10g*10;
                }else if($data->product == '50g'){
                    $product_50g = $data->quantity;
                    $product_50g_weight = $product_50g*50;
                }else if($data->product == '100g'){
                    $product_100g = $data->quantity;
                    $product_100g_weight = $product_100g*100;
                }

				$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/convert_aqad_template.docx');

				$templateProcessor->setValues([
					'partner_name' => $partnername,
					'date' => $datetime,
					'full_name' => $data->fullname,
                    'mykad_no' => $data->mykadno,
                    'account_no' => $data->accountnumber,
                    'achcode' => $data->accountholdercode,
					'finalAcePriceTitle' => $finalAcePriceTitle,
                    'making_charges' => $data->makingcharges,
                    'delivery_fee' => $data->deliveryfee,
                    'redemption_fee' => $data->redemptionfee,
					'final_total' => $finaltotal,
					'weight' => $weight,
                    'delivery_address' => $data->address,
                    'contact_no' => $data->contact_no,
					'teller' => $data->teller,
                    'product_1g' => $product_1g,
                    'product_5g' => $product_5g,
                    'product_10g' => $product_10g,
                    'product_50g' => $product_50g,
                    'product_100g' => $product_100g,
                    'product_1g_weight' => $product_1g_weight,
                    'product_5g_weight' => $product_5g_weight,
                    'product_10g_weight' => $product_10g_weight,
                    'product_50g_weight' => $product_50g_weight,
                    'product_100g_weight' => $product_100g_weight,
                    'quantity' => $data->quantity,
				]);
				$filename = $finalAcePriceTitle.'_Order_Confirmation'.$data->accountholdercode.'.docx';

				$templateProcessor->saveAs('word/'.$filename);
				$fileUrl = 'word/'.$filename;
				//return("word/".$filename);

				$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
                                        $pdfPath = $matches[1];

                                        echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);
				//$orderPdf = $spotordermanager->printSpotOrderOTCPreview($data);
			}

			// if ($orderPdf){
			// 	$order = $app->orderStore()->getById($params['orderid']);
			// 	$developmentEnv = $app->getConfig()->{'snap.environtment.development'};
			// 	$filename = 'ACE_Order_'.$order->orderno;
			// 	if ($developmentEnv){
			// 		$orderPdf = '<div style="font-size: 20px;">----------------DEMO----------------</div><br>'.$orderPdf;
			// 		$filename = 'DEMO_'.$filename;
			// 	}
			// }else{
			// 	echo 'Invalid Order. Please Contact Administrative.';
			// 	return;
			// }
	
			// $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 0);
            // $html2pdf->pdf->SetDisplayMode('fullpage');
			
			// $orderPdf = $this->completePdf($orderPdf, $params['partnercode']);


			// ob_start();
			// include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
			// $content = ob_get_clean();
			// $html2pdf->writeHTML($content);
			
			// $html2pdf->pdf->setTitle($filename);
			// $html2pdf->writeHTML($orderPdf);

			
			//echo json_encode(['success' => true, 'return' => $html2pdf->output($filename.'.pdf')]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
	   
       
       
        //return $html2pdf->output('FILENAME.pdf');
	}

    public function printConversionOTC($app, $params){

		// require_once '../../vendor/phpoffice/phpword/src/PhpWord/Autoloader.php';
		// \PhpOffice\PhpWord\Autoloader::register();

		$redemptionmanager= $app->redemptionManager();
		
		// // Check if Buy Or Sell
		try{
			// do double check
			// if have data do preview
			//print_r($params['data']);exit;
			if(!$params['data']){
				
				if(!$params['customerid']){
                    if(ctype_alpha(substr($params['refno'],-1)) && $this->app->getConfig()->{'projectBase'} == 'BSN'){
                        $data_multiple = [];
                        $convertPdf = [];
                        $rdm = $this->app->redemptionStore()->searchTable()->select()->where('partnerrefno', $params['refno'])->one();
                        if($rdm) {
                            $root_refno = substr($params['refno'],0,-1);
                            $rdm = $this->app->myconversionStore()->searchView()->select()
                                ->where('refno', 'like' ,$root_refno."%")
                                ->andWhere('rdmpartnerid', $rdm->partnerid)
                                ->execute();
                            if($rdm){
                                foreach($rdm as $record){
                                    $dt = $redemptionmanager->printConversionOTC($record->refno);
                                    array_push($data_multiple,$dt);
                                }

                                $convertPdf['data'] = $data_multiple;
                                $convertPdf['ismultiple'] = 1;
                            }
                            else{
                                Throw new \Exception(__METHOD__.": Data not Found for root refno ".$root_refno);
                            }
                        }
                        else{
                            Throw new \Exception(__METHOD__.": Data not Found for ".$params['refno']);
                        }
                    }
                    else{
                        $convertPdf = $redemptionmanager->printConversionOTC($params['refno']);
                    }
				}else{
                    if(ctype_alpha(substr($params['refno'],-1)) && $this->app->getConfig()->{'projectBase'} == 'BSN'){
                        $data_multiple = [];
                        $convertPdf = [];
                        $rdm = $this->app->redemptionStore()->searchTable()->select()->where('partnerrefno', $params['refno'])->one();
                        if($rdm) {
                            $root_refno = substr($params['refno'],0,-1);
                            $rdm = $this->app->myconversionStore()->searchView()->select()
                                ->where('refno', 'like' ,$root_refno."%")
                                ->andWhere('rdmpartnerid', $rdm->partnerid)
                                ->execute();
                            if($rdm){
                                foreach($rdm as $record){
                                    $dt = $redemptionmanager->printConversionOTC($record->refno);
                                    array_push($data_multiple,$dt);
                                }

                                $convertPdf['data'] = $data_multiple;
                                $convertPdf['ismultiple'] = 1;
                            }
                            else{
                                Throw new \Exception(__METHOD__.": Data not Found for root refno ".$root_refno);
                            }
                        }
                        else{
                            Throw new \Exception(__METHOD__.": Data not Found for ".$params['refno']);
                        }
                    }
                    else{
                        $convertPdf = $redemptionmanager->printConversionOTC($params['refno'], $params['customerid']);
                    }
					// $convertPdf = $redemptionmanager->printConversionOTC($params['refno'], $params['customerid']);
				}

				//print_r($orderPdf);exit;

                $teller_id = $convertPdf['tellerId'] ?? $convertPdf['data'][0]['tellerId'];
                $user = $app->userStore()->getById($teller_id);
        		$teller = $user->username;

                $productCode = $convertPdf['productCode'] ?? $convertPdf['data'][0]['productCode'];

                //$productCode = str_replace('g', '', substr($productCode, strrpos($productCode, '-') + 1));

                $projectbase = $app->getConfig()->{'projectBase'};
					
				if($projectbase == 'BSN'){
                    if($params['printType'] == 'printConvertAqadFromList'){
                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/convert_aqad_template.docx');
                    }else{
                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/convert_receipt_template.docx');
                    }
                }else{
                    if($convertPdf['accounttype'] == 'COMPANY' || $convertPdf['accounttype'] == 'SOLEPROPRIETORSHIP'){
                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_convert_company_receipt_template.docx');
                    }else{
                        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_convert_receipt_template.docx');
                    }
                    
                }

                $newfullname = $convertPdf['fullname'] ?? $convertPdf['data'][0]['fullname'];
                
                if (preg_match('/&/', $convertPdf['fullname'])) {
                    $newfullname = htmlentities(($convertPdf['fullname'] ?? $convertPdf['data'][0]['fullname']));
                }

                if(isset($convertPdf['ismultiple'])){
                    $convertPdf['making_charges'] = 0;
                    $convertPdf['delivery_fee'] = 0;
                    $convertPdf['total_redemption_fee'] = 0;
                    $convertPdf['final_total'] = 0;
                    $convertPdf['xau'] = 0;
                    $convertPdf['arrProduct'][$convertPdf['realproductcode']] = 0;
                    $convertPdf['receipt_no'] = '';
                    $receipt_no = [];
                    $product = 0;
                    // print_r($convertPdf['data']);exit;
                    foreach($convertPdf['data'] as $record){
                        $convertPdf['making_charges'] += $record['making_charges'];
                        $convertPdf['delivery_fee'] += $record['delivery_fee'];
                        $convertPdf['total_redemption_fee'] += $record['total_redemption_fee'];
                        $convertPdf['final_total'] += $record['final_total'];
                        $convertPdf['xau'] += $record['xau'];
                        $convertPdf['arrProduct'][$convertPdf['realproductcode']] += $record['arrProduct'][$record['realproductcode']];
                        array_push($receipt_no, $record['receipt_no']);
                    }
                    $convertPdf['receipt_no'] = implode($receipt_no,', ');
                    $convertPdf['final_total'] = number_format($convertPdf['final_total'],2,'.','');
                    $product = $convertPdf['arrProduct'][$convertPdf['realproductcode']]/$convertPdf['data'][0]['totalTransaction'];

                }
                $product_1g = '';
                $product_5g = '';
                $product_10g = '';
                $product_50g = '';
                $product_100g = '';
                $product_1000g = '';
                //$product = 0;
                if(isset($convertPdf['ismultiple'])){
                    $product_1g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-1g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-1g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    $product_5g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-5g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-5g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    $product_10g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-10g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-10g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    $product_50g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-50g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-50g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    $product_100g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-100g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-100g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    $product_1000g = isset($convertPdf['data'][0]['arrProduct']['GS-999-9-1000g']) ? $convertPdf['data'][0]['arrProduct']['GS-999-9-1000g']/$convertPdf['data'][0]['totalTransaction'] : '';
                    // $product = isset( $convertPdf['data'][0]['arrProduct'][$convertPdf['data'][0]['realproductcode']]) ? $convertPdf['data'][0]['arrProduct'][$convertPdf['data'][0]['realproductcode']]/$convertPdf['data'][0]['totalTransaction'] : 0;
                }
                else{
                    $product_1g = isset($convertPdf['arrProduct']['GS-999-9-1g']) ? $convertPdf['arrProduct']['GS-999-9-1g']/$convertPdf['totalTransaction'] : '';
                    $product_5g = isset($convertPdf['arrProduct']['GS-999-9-5g']) ? $convertPdf['arrProduct']['GS-999-9-5g']/$convertPdf['totalTransaction'] : '';
                    $product_10g = isset($convertPdf['arrProduct']['GS-999-9-10g']) ? $convertPdf['arrProduct']['GS-999-9-10g']/$convertPdf['totalTransaction'] : '';
                    $product_50g = isset($convertPdf['arrProduct']['GS-999-9-50g']) ? $convertPdf['arrProduct']['GS-999-9-50g']/$convertPdf['totalTransaction'] : '';
                    $product_100g = isset($convertPdf['arrProduct']['GS-999-9-100g']) ? $convertPdf['arrProduct']['GS-999-9-100g']/$convertPdf['totalTransaction'] : '';
                    $product_1000g = isset($convertPdf['arrProduct']['GS-999-9-1000g']) ? $convertPdf['arrProduct']['GS-999-9-1000g']/$convertPdf['totalTransaction'] : '';
                    $product = isset($convertPdf['arrProduct'][$convertPdf['realproductcode']]) ? $convertPdf['arrProduct'][$convertPdf['realproductcode']]/$convertPdf['totalTransaction'] : 0;
                }
                 

                $this->logDebug("User id when convert print :".$userId." Conversion print data :". $convertPdf);

                $accountHolderCode = $convertPdf['accountholdercode'] ?? $convertPdf['data'][0]['accountholdercode'];

                $partnerService = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $app->getConfig()->{'otc.bsn.partner.id'})->execute();
                
                $productFee_1g = '-';
                $productFee_5g = '-';
                $productFee_10g = '-';
                $productFee_50g = '-';
                $productFee_100g = '-';
                $productFee_1000g = '-';
                foreach($partnerService as $partnerProduct){
                    //buyclickminxau value same as deno
                    if($partnerProduct->buyclickminxau == 1){
                        $productFee_1g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');
                    }else if($partnerProduct->buyclickminxau == 5){
                        $productFee_5g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 10){
                        $productFee_10g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 50){
                        $productFee_50g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 100){
                        $productFee_100g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 1000){
                        $productFee_1000g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');
                    }
                }

				$templateProcessor->setValues([
					'partner_name' => $convertPdf['partner_name'] ?? $convertPdf['data'][0]['partner_name'],
					'receipt_no' => $convertPdf['receipt_no'],
					'order_no' => $convertPdf['transactionid'] ?? $convertPdf['data'][0]['transactionid'],
					'date' => $convertPdf['date'] ?? $convertPdf['data'][0]['date'],
                    'jointdate' => ($convertPdf['accounttype'] ?? $convertPdf['data'][0]['accounttype']) == 'COHEADING' ? ($convertPdf['date'] ?? $convertPdf['data'][0]['date']) : '',
					'full_name' => $newfullname,
                    'mykad_no' => $convertPdf['mykadno'] ?? $convertPdf['data'][0]['mykadno'],
                    'nokfull_name' => $convertPdf['nok_fullname'] ?? $convertPdf['data'][0]['nok_fullname'],
                    'nokmykad_no' => $convertPdf['nok_mykadno'] ?? $convertPdf['data'][0]['nok_mykadno'],
					'finalAcePriceTitle' => $convertPdf['finalAcePriceTitle'] ?? $convertPdf['data'][0]['finalAcePriceTitle'],
                    'making_charges' => $convertPdf['making_charges'],
                    'delivery_fee' => number_format($convertPdf['delivery_fee'],2,'.',''),
                    'total_redemption_fee' => number_format($convertPdf['total_redemption_fee'],2,'.',''),
					'final_total' => $convertPdf['final_total'],
					'weight' => $convertPdf['xau'],
					'teller' => $convertPdf['tellername'] ?? $convertPdf['data'][0]['tellername'],
                    'teller_id' => $teller,
                    'status' => 'Paid',
                    'delivery_address' => $convertPdf['address'] ?? $convertPdf['data'][0]['address'],
					'accountholder_code' => $accountHolderCode,
					'casa_bankaccount' => $convertPdf['casa_bankaccount'] ?? $convertPdf['data'][0]['casa_bankaccount'],
                    'contact_no' => $convertPdf['contact_no'] ?? $convertPdf['data'][0]['contact_no'],
                    'product_1g' => $product_1g,
                    'product_5g' => $product_5g ,
                    'product_10g' => $product_10g ,
                    'product_50g' => $product_50g ,
                    'product_100g' => $product_100g ,
                    'product_1000g' => $product_1000g ,
                    'product_1g_weight' => (($product_1g*1) != 0) ? ($product_1g*1).'g' : '',
                    'product_5g_weight' => (($product_5g*5) != 0) ? ($product_5g*5).'g' : '',
                    'product_10g_weight' => (($product_10g*10) != 0) ? ($product_10g*10).'g' : '',
                    'product_50g_weight' => (($product_50g*50) != 0) ? ($product_50g*50).'g' : '',
                    'product_100g_weight' => (($product_100g*100) != 0) ? ($product_100g*100).'g' : '',
                    'product_1000g_weight' => (($product_1000g*1000) != 0) ? ($product_1000g*1000).'g' : '',
                    'productFee_1g' => $productFee_1g,
                    'productFee_5g' => $productFee_5g,
                    'productFee_10g' => $productFee_10g,
                    'productFee_50g' => $productFee_50g,
                    'productFee_100g' => $productFee_100g,
                    'productFee_1000g' => $productFee_1000g,
                    'redemption_fee_1' => (($productFee_1g*$product_1g) != 0) ? 'RM '.(number_format($productFee_1g*$product_1g,2,'.','')) : '',
                    'redemption_fee_5' => (($productFee_1g*$product_5g) != 0) ? 'RM '.(number_format($productFee_5g*$product_5g,2,'.','')) : '',
                    'redemption_fee_10' => (($productFee_1g*$product_10g) != 0) ? 'RM '.(number_format($productFee_10g*$product_10g,2,'.','')) : '',
                    'redemption_fee_50' => (($productFee_1g*$product_50g) != 0) ? 'RM '.(number_format($productFee_50g*$product_50g,2,'.','')) : '',
                    'redemption_fee_100' => (($productFee_1g*$product_100g) != 0) ? 'RM '.(number_format($productFee_100g*$product_100g,2,'.','')) : '',
                    'redemption_fee_1000' => (($productFee_1g*$product_1000g) != 0) ? 'RM '.(number_format($productFee_1000g*$product_1000g,2,'.','')) : '',
                    'product' => $product ,
                    'productCode' => $productCode,
                    'checkername' => $convertPdf['checkername'] ?? $convertPdf['data'][0]['checkername'],
                    'checkerId'   => $convertPdf['checkerId'] ?? $convertPdf['data'][0]['checkerId'],
                    'primaryfullname' => ($convertPdf['accounttype'] ?? $convertPdf['data'][0]['accounttype']) == 'COHEADING' ? ($convertPdf['primaryfullname'] ?? $convertPdf['data'][0]['primaryfullname']) : $newfullname,
				]);

                if($params['printType'] == 'printConvertAqadFromList'){
                    $filename = ($convertPdf['finalAcePriceTitle'] ?? $convertPdf['data'][0]['finalAcePriceTitle']).'_Order_Confirmation'.$accountHolderCode.'.docx';
                }else{
				    $filename = ($convertPdf['finalAcePriceTitle'] ?? $convertPdf['data'][0]['finalAcePriceTitle']).'_Receipt_'.($convertPdf['transactionid'] ?? $convertPdf['data'][0]['transactionid']).'.docx';
                }
				$templateProcessor->saveAs('word/'.$filename);
				$fileUrl = 'word/'.$filename;

                $command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
				$output = shell_exec($command);
                
				$pattern = '/pdf\/(.*?)\.pdf/';
				preg_match($pattern, $output, $matches);
				$pdfPath = $matches[1];

                $something1 = shell_exec("sudo -u siteadm chown siteadm:netsite /usr/local/nginx/html/gtp/source/snapapp_otc/pdf/".$pdfPath.".pdf");
				$something2 = shell_exec("sudo -u siteadm chmod 664 /usr/local/nginx/html/gtp/source/snapapp_otc/pdf/".$pdfPath.".pdf");

				return('pdf/'.$pdfPath.'.pdf');

				//return("word/".$filename);
				
			}
            else{
				// do preview

				$userId = $app->getUserSession()->getUserId();
        		$user = $app->userStore()->getById($userId);
        		$partnerId = $user->partnerid;

                if ($params['partnercode'] == 'ALRAJHI'){
                    if ($partnerId == 0 ){
                        $partnerId = $app->getConfig()->{'otc.alrajhi.partner.id'};
                    } 
                }else if($params['partnercode'] == 'BSN'){
                    if ($partnerId == 0 ){
                        $partnerId = $app->getConfig()->{'otc.bsn.partner.id'};
                    } 
                }

            	$userobj = $app->partnerStore()->getById($partnerId);
            	$partnername = $userobj->name;

				$data = json_decode($params['data']);

				$finalAcePriceTitle = "REDEMPTION";

				$datetime = str_replace("GMT 0800 (Malaysia Time)","",$data->date);
            	$datetime = strtotime($datetime);
            	$datetime = date('Y-m-d H:i:s', $datetime);

				$weight = number_format($data->xau,6,'.','');
				$totalbuy = number_format(preg_replace("/[^0-9.]/", "", $data->amount),3);
				$finaltotal = number_format(preg_replace("/[^0-9.]/", "", $data->finaltotal),2);

                $productCode = $data->productCode;
                //$productCode = str_replace('g', '', substr($productCode, strrpos($productCode, '-') + 1));

                $product_1g = '';
                $product_5g = '';
                $product_10g = '';
                $product_50g = '';
                $product_100g = '';
                $product_1000g = '';

                $product_1g_weight = '';
                $product_5g_weight = '';
                $product_10g_weight = '';
                $product_50g_weight = '';
                $product_100g_weight = '';
                $product_1000g_weight = '';

                if($data->product == '1g'){
                    $product_1g = $data->quantity;
                    $product_1g_weight = $product_1g*1;
                }else if($data->product == '5g'){
                    $product_5g = $data->quantity;
                    $product_5g_weight = $product_5g*5;
                }else if($data->product == '10g'){
                    $product_10g = $data->quantity;
                    $product_10g_weight = $product_10g*10;
                }else if($data->product == '50g'){
                    $product_50g = $data->quantity;
                    $product_50g_weight = $product_50g*50;
                }else if($data->product == '100g'){
                    $product_100g = $data->quantity;
                    $product_100g_weight = $product_100g*100;
                }else if($data->product == '100g'){
                    $product_1000g = $data->quantity;
                    $product_1000g_weight = $product_1000g*1000;
                }

                $accHolder = $app->myaccountholderStore()->searchTable()->select()
						->where('accountholdercode', $data->accountHolderCode)
						->one();

                $casa = BaseCasa::getInstance($this->app->getConfig()->{'otc.casa.api'});

                if($accHolder->accounttype){
                    if($accHolder->accounttype == 22){
                        $params['searchFlag'] = 1;
                        $params['keyword'] = $accHolder->partnercusid;
                        $jointAccount = $casa->getCustomerInfo($params);
                    }
                }

                if($jointAccount){
                    $fullname = $jointAccount['data']['joinaccount'][0]['Heading1'];
                }

                if ($params['partnercode'] == 'ALRAJHI'){
                    // $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_convert_aqad_template.docx');
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_convert_confirmation_template.docx');
                }else{
                    $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/convert_aqad_template.docx');
                }

                $partnerService = $app->partnerStore()->getRelatedStore('services')->searchTable()->select()->where('partnerid', $app->getConfig()->{'otc.bsn.partner.id'})->execute();
                
                $productFee_1g = '-';
                $productFee_5g = '-';
                $productFee_10g = '-';
                $productFee_50g = '-';
                $productFee_100g = '-';
                $productFee_1000g = '-';
                foreach($partnerService as $partnerProduct){
                    //buyclickminxau value same as deno
                    if($partnerProduct->buyclickminxau == 1){
                        $productFee_1g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');
                    }else if($partnerProduct->buyclickminxau == 5){
                        $productFee_5g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 10){
                        $productFee_10g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 50){
                        $productFee_50g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 100){
                        $productFee_100g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');

                    }else if($partnerProduct->buyclickminxau == 1000){
                        $productFee_1000g = number_format($partnerProduct->redemptionpremiumfee + $partnerProduct->redemptionhandlingfee + $partnerProduct->redemptioninsurancefee,2,'.','');
                    }
                }

				$templateProcessor->setValues([
					'partner_name' => $partnername,
					'date' => $datetime,
                    'jointdate' => $accHolder->accounttype == 22 ? $datetime : '',
					'full_name' => $data->fullname,
                    'primaryfullname' => $accHolder->accounttype == 22 ? $fullname : $data->fullname,
                    'mykad_no' => $data->mykadno,
                    'casa_bankaccount' => $data->accountnumber,
                    'accountholder_code' => $data->accountholdercode,
					'finalAcePriceTitle' => $finalAcePriceTitle,
                    'making_charges' => $data->makingcharges,
                    'delivery_fee' => number_format($data->deliveryfee,2,'.',''),
                    'redemption_fee' => $data->redemptionfee,
					'final_total' => $finaltotal,
					'weight' => $weight,
                    'delivery_address' => $data->address,
                    'contact_no' => $data->contact_no,
					'teller' => $data->teller,
                    // 'teller_id' => $userAdditional->staffid,
                    'product_1g' => $product_1g,
                    'product_5g' => $product_5g,
                    'product_10g' => $product_10g,
                    'product_50g' => $product_50g,
                    'product_100g' => $product_100g,
                    'product_1000g' => $product_1000g,
                    'product_1g_weight' => (($product_1g*1) != 0) ? ($product_1g*1).'g' : '',
                    'product_5g_weight' => (($product_5g*5) != 0) ? ($product_5g*5).'g' : '',
                    'product_10g_weight' => (($product_10g*10) != 0) ? ($product_10g*10).'g' : '',
                    'product_50g_weight' => (($product_50g*50) != 0) ? ($product_50g*50).'g' : '',
                    'product_100g_weight' => (($product_100g*100) != 0) ? ($product_100g*100).'g' : '',
                    'product_1000g_weight' => (($product_1000g*1000) != 0) ? ($product_1000g*1000).'g' : '',
                    'productFee_1g' => $productFee_1g,
                    'productFee_5g' => $productFee_5g,
                    'productFee_10g' => $productFee_10g,
                    'productFee_50g' => $productFee_50g,
                    'productFee_100g' => $productFee_100g,
                    'productFee_1000g' => $productFee_1000g,
                    'redemption_fee_1' => (($productFee_1g*$product_1g) != 0) ? 'RM '.(number_format($productFee_1g*$product_1g,2,'.','')) : '',
                    'redemption_fee_5' => (($productFee_1g*$product_5g) != 0) ? 'RM '.(number_format($productFee_5g*$product_5g,2,'.','')) : '',
                    'redemption_fee_10' => (($productFee_1g*$product_10g) != 0) ? 'RM '.(number_format($productFee_10g*$product_10g,2,'.','')) : '',
                    'redemption_fee_50' => (($productFee_1g*$product_50g) != 0) ? 'RM '.(number_format($productFee_50g*$product_50g,2,'.','')) : '',
                    'redemption_fee_100' => (($productFee_1g*$product_100g) != 0) ? 'RM '.(number_format($productFee_100g*$product_100g,2,'.','')) : '',
                    'redemption_fee_1000' => (($productFee_1g*$product_1000g) != 0) ? 'RM '.(number_format($productFee_1000g*$product_1000g,2,'.','')) : '',
                    'quantity' => $data->quantity,
                    'productCode' => $productCode,
                    'join_full_name' => $accHolder->nokfullname,
					'join_nok_mykadno' => $accHolder->nokmykadno,
				]);
				$filename = $finalAcePriceTitle.'_Order_Confirmation'.$data->accountHolderCode.'.docx';

				$templateProcessor->saveAs('word/'.$filename);
				$fileUrl = 'word/'.$filename;

                $command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
				$output = shell_exec($command);
                
				$pattern = '/pdf\/(.*?)\.pdf/';
				preg_match($pattern, $output, $matches);
				$pdfPath = $matches[1];

                $something1 = shell_exec("sudo -u siteadm chown siteadm:netsite /usr/local/nginx/html/gtp/source/snapapp_otc/pdf/".$pdfPath.".pdf");
				$something2 = shell_exec("sudo -u siteadm chmod 664 /usr/local/nginx/html/gtp/source/snapapp_otc/pdf/".$pdfPath.".pdf");

				// return('pdf/'.$pdfPath.'.pdf');
                echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);
				//return("word/".$filename);

				//$orderPdf = $spotordermanager->printSpotOrderOTCPreview($data);
			}

			// if ($orderPdf){
			// 	$order = $app->orderStore()->getById($params['orderid']);
			// 	$developmentEnv = $app->getConfig()->{'snap.environtment.development'};
			// 	$filename = 'ACE_Order_'.$order->orderno;
			// 	if ($developmentEnv){
			// 		$orderPdf = '<div style="font-size: 20px;">----------------DEMO----------------</div><br>'.$orderPdf;
			// 		$filename = 'DEMO_'.$filename;
			// 	}
			// }else{
			// 	echo 'Invalid Order. Please Contact Administrative.';
			// 	return;
			// }
	
			// $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 0);
            // $html2pdf->pdf->SetDisplayMode('fullpage');
			
			// $orderPdf = $this->completePdf($orderPdf, $params['partnercode']);


			// ob_start();
			// include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
			// $content = ob_get_clean();
			// $html2pdf->writeHTML($content);
			
			// $html2pdf->pdf->setTitle($filename);
			// $html2pdf->writeHTML($orderPdf);

			
			//echo json_encode(['success' => true, 'return' => $html2pdf->output($filename.'.pdf')]);
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
	   
       
       
        //return $html2pdf->output('FILENAME.pdf');
	}

    public function checkConversionStatus($app, $params){
		// this function is to check for records that have status::pending_approval
		
		$constStatus = MyConversion::STATUS_PAYMENT_PENDING;
		try{
			if($params['refno']){
				// search for gold trx record
				$goldTrx = $this->app->myconversionStore()->searchView()->select()
				->where('refno', $params['refno'])
				->one();

                $lastCharacter = substr($goldTrx->refno, -1);

                if(ctype_alpha($lastCharacter)){
                    $refno = substr($goldTrx->refno, 0, -1);
                }else{
                    $refno = $goldTrx->refno;
                }

                $payment = $this->app->mypaymentDetailStore()->searchTable()->select()
				->where('sourcerefno', $refno)
				->one();

                $address = $goldTrx->rdmdeliveryaddress.' '.$goldTrx->rdmdeliverypostcode.' '.$goldTrx->rdmdeliverystate;

				$data = [];
				// if transaction found, check status
				if($goldTrx){

					$isPendingPayment = $constStatus== $goldTrx->status ? true : false;
					$isConvertSuccessful = false;
					
				}


			    if(!$isPendingPayment){
			    	// check status if transaction is success or fail
			    	$statusString = $goldTrx->getStatusText();
			    	//do status check and match
			    	//all status that identify as a successful transaction
			    	if(in_array($goldTrx->status, [MyConversion::STATUS_PAYMENT_PAID])){
			    		$isConvertSuccessful = true;
			    		// call and get gold trx information
                    
			    	//all status that identify as a unsuccessful transaction
			    	}else if(in_array($goldTrx->status, [MyConversion::STATUS_EXPIRED, MyConversion::STATUS_PAYMENT_CANCELLED, MyConversion::STATUS_REVERSED])){
			    		$isConvertSuccessful = false;
			    	}
			    }


			}else{
				throw new \Exception("Please include search parameters");
			}


			echo json_encode(['success' => true, 'ispendingpayment' => $isPendingPayment, 'isconvertsuccessful' => $isConvertSuccessful,
             'status' => $goldTrx->status, 'statusstring' => $statusString, 'final_total' => $payment->amount, 'address' => $address]);
		}catch (\Exception $e){
			$this->log("Failed to validate OTC Order Approval Status for goldtransaction ID : ". $params['id'], SNAP_LOG_ERROR);
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
		
	}

    public function checkFirstTimeConversion($app, $params){
        $conversion = $app->myconversionStore()->searchTable()
        ->where('accountholderid',$params['accountholder_id']);

        if($conversion){
            echo json_encode(['success' => true]);
        }else{
            echo json_encode(['success' => false]);
        }

    }
    /**
     * Return formatted data for conversion history
     *
     * @param  array $conversions
     *
     * @return array
     */
    protected function formatConversionHistory($app, $conversions)
    {
        $convMgr = $app->mygtpconversionManager();
        $conversionData = [];
        foreach ($conversions as $conversion) {
            $redemption = $app->redemptionStore()->getById($conversion->redemptionid);
            $paymentDetail = $app->mypaymentdetailStore()->getByField('sourcerefno', $conversion->refno);
            $logistic = $app->logisticStore()->searchView()->select()
                ->where('type', Logistic::TYPE_REDEMPTION)
                ->andWhere('typeid', $redemption->id)
                ->one();
            $data = [];

            $data['refno'] = $conversion->refno;
            $data['paymentrefno'] = $paymentDetail->paymentrefno;
            $data['conversion_fee'] = floatval($redemption->redemptionfee)
                + floatval($redemption->specialdeliveryfee)
                + floatval($redemption->handlingfee)
                + floatval($redemption->insurancefee);
            $data['transaction_fee'] = is_null($paymentDetail) ? 0.00 : floatval($paymentDetail->customerfee + $paymentDetail->gatewayfee);
            $data['total_fee'] = $data['conversion_fee'] + $data['transaction_fee'];
            // $data['courier_fee'] = floatval($redemption->specialdeliveryfee) + floatval($redemption->handlingfee);
            // $data['insurance_fee'] = floatval($redemption->insurancefee);
            $data['created_on'] = $conversion->createdon->format('Y-m-d H:i:s');
            $data['status'] = $conversion->getStatusText();
            $data['status_code'] = intval($conversion->status);
            $data['payment_mode'] = strtolower($conversion->logisticfeepaymentmode);

            // Get the items for this conversion
            $data['items'] = $this->formatConversionItems($app, $redemption, $conversion);

            $data['logistic_status'] = gettext("Pending");
            $data['logistic_status_code'] = Logistic::STATUS_PENDING;
            $data['logistics_log'] = [];

            // Get total quantity of items
            $data['total_items'] = 0;
            foreach ($data['items'] as $item) {
                $data['total_items'] += $item['quantity'];
            }

            // Total weight of the redemption
            $data['total_weight'] = floatval($redemption->totalweight);

            if ($logistic) {
                // Sets logistic status
                $data['logistic_status']        = $logistic->getStatusText();
                $data['logistic_status_code']   = intval($logistic->status);
                $data['logistic_trackingnum']   = $logistic->awbno;
                $data['logistic_vendorname']    = $logistic->vendorname;

                // Get the logistic log for this conversion
                $logisticlogs = $convMgr->getRedemptionLogisticLog($app, $conversion->redemptionid);
                foreach ($logisticlogs as $log) {
                    $log->timeon->setTimezone($app->getUserTimezone());
                    $data['logistics_log'][] = [
                        'status'      => $log->readablestatus,
                        'status_code' => intval($log->value),
                        'date'        =>  $log->timeon->format('Y-m-d H:i:s')
                    ];
                }
            }



            $conversionData[] = $data;
        }

        return $conversionData;
    }

     /**
     * Returns formatted array of items in redemption
     * 
     * @param Redemption $redemption       Redemption
     * @param MyConversion $conversion     Conversion
     * 
     * @return array
     */
    protected function formatConversionItems($app, $redemption, $conversion)
    {
        // Temporary following MiGA format
        // Sample response after Redemptionmanager processing
        // [{"sapreturnid": 2645, "code": "GS-999-9-1g", "serialnumber": "IGR200142", "weight":"1.00000", "sapreverseno":"1901"}]
        $items = json_decode($redemption->items, true);
        $data = [];
        foreach ($items as $item) {
            // If redemption confirmed with SAP
            if (isset($item['sapreturnid'])) {
                $tmp = [
                    'serialnumber' => $item['serialnumber'],
                    'quantity'     => $item['quantity'] ?? 1
                ];

                $product = $app->productStore()->getByField('sapitemcode', $item['code']);
                $tmp['name'] = $product->name;
            } else {
                $tmp = [
                    'serialnumber' => $item['serialno'],
                    'name'         => $item['name'],
                    'quantity'     => $item['quantity']
                ];
            }

            // Dont return serial number if not paid
            if (MyConversion::STATUS_PAYMENT_PAID != $conversion->status) {
                $tmp['serialnumber'] = '';
            }

            $data[] = $tmp;
        }
        return $data;
    }

    public function getCasaAccounts($app, $params) {	
		$toReturn = array();
		// Set fields to call getcasainfo
		// $params['partyId'] = $paddedNumber = str_pad($params['searchfield'], 16, '0', STR_PAD_LEFT);

		$casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
		$data = $casa->getCasaInfo($params);
		if($data['success']){
			// do something
		
			//branchIdent-AccTypeValue-AccIdentValue 
			foreach ($data['data'] as $record) {
				$string = $record['BranchIdent'] . '-' . $record['AcctTypeValue'] . '-' . $record['AcctIdentValue'];
				$toReturn[]= array( 
					'accountnumber' =>  $string,
					'combination' =>  $string,
				);
			}
		}

		return $toReturn;
    }
}
