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
class myoutstandingmanagementfeeHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/bmmb/report;/root/go/report;/root/one/report;/root/onecall/report;/root/air/report;/root/mcash/report;/root/ktp/report;/root/kopetro/report;/root/kopttr/report;/root/pkbaffi/report;/root/bumira/report;/root/toyyib/report;/root/nubex/report;/root/hope/report;/root/mbsb/report;/root/red/report;/root/kodimas/report;/root/kgoldaffi/report;/root/koponas/report;/root/wavpay/report;/root/noor/report;/root/waqaf/report;/root/kasih/report;/root/posarrahnu/report;/root/igold/report;/root/bursa/report;/root/bsn/report;/root/alrajhi/report/storagefee/list;/root/bimb/report/storagefee/list;/root/alrajhi/forcesell/report;', 'storagefee');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('PayManagementFee', 'PayManagementFee');

        $this->app = $app;

        $this->currentStore = $app->mymonthlystoragefeeStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 3));
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

    function onPreListing($objects, $params, $records)
    {

        $app = App::getInstance();

        foreach($records as $key => $record ) {
            $partner = $app->partnerStore()->searchTable()->select()
                ->where('code',$records[$key]['partnercode'])
                ->one();

            $records[$key]['partnername'] = $partner->name;
        }

        return $records;
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();


			if($partnerId != 0){
				$sqlHandle->andWhere('partnerid', $partnerId);
			}else{
				$groupPartnerIds = array();
				foreach ($partners as $partner){
					array_push($groupPartnerIds,$partner->id);
				}
				$sqlHandle->andWhere('partnerid', 'IN', $groupPartnerIds);
			}

        }else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']){
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
			$partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
            // Do checking
            $partnerId = $this->getPartnerIdForBranch();


			if($partnerId != 0){
				$sqlHandle->andWhere('partnerid', $partnerId);
			}else{
				$groupPartnerIds = array();
				foreach ($partners as $partner){
					array_push($groupPartnerIds,$partner->id);
				}
				$sqlHandle->andWhere('partnerid', 'IN', $groupPartnerIds);
			}
        }
        
        if (isset($params['accountholderid'])) {
            $sqlHandle->andWhere('accountholderid', $params['accountholderid']);
        }

        if (isset($params['status'])) {
            $sqlHandle->andWhere('apjstatus', $params['status']);
        }

        return array($params, $sqlHandle, $fields);
    }

    private function getPartnerIdForBranch() {
		$app = App::getInstance();
		$userId = $app->getUserSession()->getUserId();
		$user = $app->userStore()->getById($userId);
		if($user->partnerid > 0 ){
			$partnerid = $user->partnerid;
		}else{
			$partnerid = 0;
		}     
		return $partnerid;
	}

    public function PayManagementFee($app, $params){
        $this->app->startCLIJob("BsnMonthlyManagementFeeReAttemptJob.php", [
            'accountholderid' => $params['accountholder_id'],
        ]);
    }
    

}
