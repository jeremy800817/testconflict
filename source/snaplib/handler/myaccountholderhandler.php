<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use DateTime;
use Snap\App;
use Snap\manager\MyGtpAccountManager;
use Snap\object\MyAccountHolder;
use Snap\object\MyLedger;
use Snap\object\Partner;
use Snap\object\MyLocalizedContent;
use Snap\sqlrecorder;

// use Snap\api\casa\Bsn;
use Snap\api\casa\BaseCasa;

use Snap\api\exception\EmailAddressTakenException;
use Snap\api\exception\MyGtpAccountHolderMyKadExists;
use Snap\api\exception\MyOtcPartnercusidExists;

use Spipu\Html2Pdf\Html2Pdf;

use Snap\api\exception\MyGtpOccupationCategoryWithIllegalSub;
use Snap\api\exception\PartnerBranchCodeInvalid;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myaccountholderHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        //parent::__construct('/root/bmmb', 'profile');
        
        $this->mapActionToRights('list', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->mapActionToRights('pendingapproval', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->mapActionToRights('exportExcel', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->mapActionToRights('exportExcelAccounts', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');
        $this->mapActionToRights('suspendAccountHolder', '/root/bmmb/profile/suspend;/root/go/profile/suspend;/root/one/profile/suspend;/root/onecall/profile/suspend;/root/air/profile/suspend;/root/mcash/profile/suspend;/root/toyyib/profile/suspend;/root/ktp/profile/suspend;/root/kopetro/profile/suspend;/root/kopttr/profile/suspend;/root/pkbaffi/profile/suspend;/root/bumira/profile/suspend;/root/nubex/profile/suspend;/root/hope/profile/suspend;/root/mbsb/profile/suspend;/root/red/profile/suspend;/root/kodimas/profile/suspend;/root/kgoldaffi/profile/suspend;/root/koponas/profile/suspend;/root/wavpay/profile/suspend;/root/noor/profile/suspend;/root/bsn/profile/suspend;/root/alrajhi/profile/suspend;/root/posarrahnu/profile/suspend;/root/waqaf/profile/suspend;/root/igold/profile/suspend;/root/kasih/profile/suspend;/root/bursa/profile/suspend;');
        $this->mapActionToRights('unsuspendAccountHolder', '/root/bmmb/profile/unsuspend;/root/go/profile/unsuspend;/root/one/profile/unsuspend;/root/onecall/profile/unsuspend;/root/air/profile/unsuspend;/root/mcash/profile/unsuspend;/root/toyyib/profile/unsuspend;/root/ktp/profile/unsuspend;/root/kopetro/profile/unsuspend;/root/kopttr/profile/unsuspend;/root/pkbaffi/profile/unsuspend;/root/bumira/profile/unsuspend;/root/nubex/profile/unsuspend;/root/hope/profile/unsuspend;/root/mbsb/profile/unsuspend;/root/red/profile/unsuspend;/root/kodimas/profile/unsuspend;/root/kgoldaffi/profile/unsuspend;/root/koponas/profile/unsuspend;/root/wavpay/profile/unsuspend;/root/noor/profile/unsuspend;/root/bsn/profile/unsuspend;/root/alrajhi/profile/unsuspend;/root/posarrahnu/profile/unsuspend;/root/waqaf/profile/unsuspend;/root/igold/profile/unsuspend;/root/kasih/profile/unsuspend;/root/bursa/profile/unsuspend;');
        $this->mapActionToRights('activateDormant', '/root/bmmb/profile/activate;/root/go/profile/activate;/root/one/profile/activate;/root/onecall/profile/activate;/root/air/profile/activate;/root/mcash/profile/activate;/root/toyyib/profile/activate;/root/ktp/profile/activate;/root/kopetro/profile/activate;/root/kopttr/profile/activate;/root/pkbaffi/profile/activate;/root/bumira/profile/activate;/root/nubex/profile/activate;/root/hope/profile/activate;/root/mbsb/profile/activate;/root/red/profile/activate;/root/kodimas/profile/activate;/root/kgoldaffi/profile/activate;/root/koponas/profile/activate;/root/wavpay/profile/activate;/root/noor/profile/activate;/root/bsn/profile/activate;/root/alrajhi/profile/activate;/root/posarrahnu/profile/activate;/root/waqaf/profile/activate;/root/igold/profile/activate;/root/kasih/profile/activate;/root/bursa/profile/activate;');

        $this->mapActionToRights('getMerchantList', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');

        // So far only applicable to KTP
        $this->mapActionToRights('updateAccountHolderLoan', '/root/ktp/profile/updateLoan;/root/pkbaffi/profile/updateLoan;/root/bumira/profile/updateLoan;/root/kodimas/profile/updateLoan;/root/kgoldaffi/profile/updateLoan;/root/koponas/profile/updateLoan;/root/bsn/profile/updateLoan;/root/alrajhi/profile/updateLoan;/root/posarrahnu/profile/updateLoan;/root/waqaf/profile/updateLoan;/root/kasih/profile/updateLoan;');
        $this->mapActionToRights('updateAccountHolderLoanFTP', '/root/ktp/profile/updateLoan;/root/pkbaffi/profile/updateLoan;/root/bumira/profile/updateLoan;/root/kodimas/profile/updateLoan;/root/kgoldaffi/profile/updateLoan;/root/koponas/profile/updateLoan;/root/bsn/profile/updateLoan;/root/alrajhi/profile/updateLoan;/root/posarrahnu/profile/updateLoan;/root/waqaf/profile/updateLoan;/root/kasih/profile/updateLoan;');
        $this->mapActionToRights('updateAccountHolderMemberFTP', '/root/ktp/profile/updateMember;/root/pkbaffi/profile/updateMember;/root/bumira/profile/updateMember;/root/kodimas/profile/updateMember;/root/kgoldaffi/profile/updateMember;/root/koponas/profile/updateMember;/root/bsn/profile/updateMember;/root/alrajhi/profile/updateMember;/root/posarrahnu/profile/updateMember;/root/waqaf/profile/updateMember;/root/kasih/profile/updateMember;');

        $this->mapActionToRights('getPartnerId', '/all/access');

        // For easigold manual ekyc approval
        $this->mapActionToRights('approveAccountHolderEKYC', 'root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');

        $this->mapActionToRights('getIdentityPhoto', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;/root/bursa/profile/list;');

        $this->mapActionToRights('getOccupationCategory', '/all/access');
        $this->mapActionToRights('getOccupationSubCategory', '/all/access');
        $this->mapActionToRights('getBankAccounts', '/all/access');
        $this->mapActionToRights('getOtcAccountHolders', '/all/access');
        $this->mapActionToRights('otcRegister', '/all/access');
        $this->mapActionToRights('getAccountsFromCasa', '/all/access');
        $this->mapActionToRights('getCasaAccounts', '/all/access');
        
        $this->mapActionToRights('printRegisterPDF', '/all/access');
        

        $this->app = $app;

        $this->currentStore = $app->myaccountholderStore();
        $this->sqlRecorder = new sqlrecorder();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing(&$params, $sqlHandle, $fields)
    {
        
        if ($this->sqlRecorder->hasRecording()) $sqlHandle->andWhere( $this->sqlRecorder );

        if (isset($params['partnercode']) && 'BMMB' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'GO' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'ONE' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'ONECALL' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'AIR' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'MCASH' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'TOYYIB' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'NUBEX' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'HOPE' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'MBSB' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'RED' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'WAVPAY' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'NOOR' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        else if (isset($params['partnercode']) && 'IGOLD' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }else if (isset($params['partnercode']) && 'BURSA' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }
        // otc modules
        else if (isset($params['partnercode']) && 'BSN' == $params['partnercode']) {
            $partnerIdBSN = $this->app->getConfig()->{'otc.bsn.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerIdBSN)->execute();
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
            
            $dateRange = json_decode($params["daterange"]);
            
            if($partnerId != 0){
                if(isset($params['daterange'])){
                    $sqlHandle
                    ->andWhere('partnerid', $partnerId)
					->andwhere('createdon', '>=', $dateRange->startDate)
					->andWhere('createdon', '<=', $dateRange->endDate);
				}else{
					$sqlHandle->andWhere('partnerid', $partnerId);
				}
                
            }else{
              
                $groupPartnerIds = array();
                foreach ($partners as $partner){
                    array_push($groupPartnerIds,$partner->id);
                }

                if(isset($params['daterange'])){
					$sqlHandle
					->andWhere('partnerid', 'IN', $groupPartnerIds)
					->andwhere('createdon', '>=', $dateRange->startDate)
					->andWhere('createdon', '<=', $dateRange->endDate);
				}else{
					$sqlHandle->andWhere('partnerid', 'IN', $groupPartnerIds);
				}
            }

        }else if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
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
        }else if (isset($params['partnercode']) && 'POSARRAHNU' == $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'}  ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
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
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
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
        else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KODIMAS' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)
			->whereIn('parent',[Partner::PARENT_AFFILIATE, Partner::PARENT_AFFILIATEPUBLIC])
            ->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KOPONAS' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'WAQAF' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }
        else if (isset($params['partnercode']) && 'KASIH' == $params['partnercode']) {
            // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
            // $sqlHandle->andWhere('partnerid', $partnerId);
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
            $partners = $this->app->partnerStore()->searchTable()->select()->where('group','=', $partnerId)->execute();
			
			$partnerId = array();
			foreach ($partners as $partner){
				array_push($partnerId,$partner->id);
			}
			$sqlHandle->andWhere('partnerid', 'IN', $partnerId);
        }

        return array($params, $sqlHandle, $fields);
    }

    public function doAction( $app, $action, $params)
    {
        $filters = json_decode($params['filter']);

        foreach ($filters as $index => $filter) {
            // Not Dormant
            if ($filter->property == 'dormant' && $filter->value == '0') {
                
                $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
                
                $dateLastTransaction   = new \DateTime($lastTransactionsCutoff . ' ago');
                $types                 = implode("','", [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL]);
                $expression            = new \ClanCats\Hydrahon\Query\Expression(
                    '(SELECT led_accountholderid from myledger WHERE 
                    led_transactiondate > \'' . $dateLastTransaction->format('Y-m-d H:i:s') .'\' AND                    
                    led_type IN (\''.$types.'\'))'
                );
                
                $this->sqlRecorder->where(function ($q) use ($expression, $dateLastTransaction) {
                    $q->where('id', 'IN' , $expression);
                    $q->orWhere('xaubalance', '>', 0);
                    $q->orWhere('createdon', '>' , $dateLastTransaction->format('Y-m-d H:i:s'));
                });
                
                unset($filters[$index]);
            }
            // Dormant
            if ($filter->property == 'dormant' && $filter->value == '1') {
                
                $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
                
                $dateLastTransaction   = new \DateTime($lastTransactionsCutoff . ' ago');
                $types                 = implode("','", [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL]);
                $expression            = new \ClanCats\Hydrahon\Query\Expression(
                    '(SELECT led_accountholderid from myledger WHERE 
                    led_transactiondate > \'' . $dateLastTransaction->format('Y-m-d H:i:s') .'\' AND                    
                    led_type IN (\''.$types.'\'))'
                );
                
                $this->sqlRecorder->where('id', 'NOT IN' , $expression);
                $this->sqlRecorder->where('createdon', '<=', $dateLastTransaction->format('Y-m-d H:i:s'));
                $this->sqlRecorder->where(function ($q) {
                    $q->where('xaubalance', '<=' , 0);
                    $q->orWhereNull('xaubalance');
                });
                
                unset($filters[$index]);
            }
        }

        $params['filter'] = json_encode($filters);

        parent::doAction($app, $action, $params);
    }

    function onPreListing($objects, $params, $records)
    {

        $app = App::getInstance();

		foreach($records as $key => $record) {

            $createuser = $app->userStore()->getById($records[$key]['createdby']);
            $moduser = $app->userStore()->getById($records[$key]['modifiedby']);
            $records[$key]['createdbyname'] = $createuser->username;
            $records[$key]['modifiedbyname'] = $moduser->username;
            
		}

        $lastTransactionsCutoff = $this->app->getConfig()->{'mygtp.accountclosure.lasttrasactioncutoff'} ?? '6 months';
        $dateStart           = new \DateTime('now');
        $dateLastTransaction = new \DateTime($lastTransactionsCutoff . ' ago');

        $accHolderIds = [];
        foreach ($records as $key => $record) {

            $records[$key]['kycpastday'] = true;

            if (MyAccountHolder::KYC_INCOMPLETE == $records[$key]['kycstatus']) {
                $createdOn = new DateTime($record['createdon']);
                $createdOn->setTimezone($this->app->getUserTimezone());
                $createdOn->modify('+1 day');

                $now = new DateTime('now', $this->app->getUserTimezone());

                if ($createdOn > $now) {
                     $records[$key]['kycpastday'] = false;
                } 
            }

            $accHolderIds[] = $records[$key]['id'];
        }

        $withTransactionIds = $this->app->myledgerStore()
                ->searchTable(false)
                ->select(['accountholderid'])
                ->addFieldMax('transactiondate', 'led_lasttransactiondate')                
                ->whereIn('accountholderid', $accHolderIds)
                ->whereIn('type', [MyLedger::TYPE_BUY_FPX, MyLedger::TYPE_SELL])
                ->where('transactiondate','<=', $dateStart->format('Y-m-d H:i:s'))
                ->where('transactiondate','>', $dateLastTransaction->format('Y-m-d H:i:s'))
                ->groupBy('accountholderid')
                ->forwardKey('led_accountholderid')
                ->get();

        foreach($records as $key => $record) {
            $createdOn = new DateTime($record['createdon']);            

            $records[$key]['dormant'] = !array_key_exists($record['id'], $withTransactionIds) && (0 >= floatval($record['xaubalance']));
            if ($createdOn > $dateLastTransaction) {
                $records[$key]['dormant'] = false;
            }
            // Add loanapporve user name
            if($records[$key]['loanapproveby']){
                $loanApproveByName = $this->app->userFactory()->getById($records[$key]['loanapproveby'])->name;
                $records[$key]['ach_loanapprovebyname'] = $loanApproveByName;
            }
            
        }

        return $records;
    }
    

    public function pendingApproval($app, $params)
    {
        $this->doAction($app, 'list', $params);
    }


    function exportExcel($app, $params)
    {
        if (isset($params['partnercode']) && 'BMMB' == $params['partnercode']) {
            $modulename = 'BMMB_SUMMARY_LISTINGS_AT_';
        }

        $header = json_decode($params["header"]);

        $dateStart = '1970-01-01 00:00:00';
        $dateEnd = new \DateTime('now', $this->app->getUserTimezone());

        $modulename = $modulename . $dateEnd->format('Y-m-d');
        $dateEnd = $dateEnd->format('Y-m-d 23:59:59');

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateAccountReport($this->currentStore, $header, $dateStart, $dateEnd, $modulename, false, null, null, null, false);
    }
    
    public function suspendAccountHolder($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->blacklistAccountHolder($accountHolder, $params['remarks'] ?? null);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function unsuspendAccountHolder($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->unBlacklistAccountHolder($accountHolder, $params['remarks'] ?? null);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function activateDormant($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->activateDormantAccountHolder($accountHolder, $params['remarks'] ?? null);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function updateAccountHolderLoan($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);
            $partner = $accountHolder->getPartner();

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->updateAccountHolderLoan($accountHolder, $partner, $params['loantotal'], $params['loanreference'], $params['loanapprovedate']);
            echo json_encode(['success' => true]);
        } catch (\Snap\api\exception\MyGtpAccountHolderNoAccountNameOrNumber $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'User bank info is incomplete!.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    function exportExcelAccounts($app, $params){
		
        // Start Query
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'GO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.go.partner.id'};
            $modulename = 'GO_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'ONE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.one.partner.id'};
            $modulename = 'ONE_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'ONECALL' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.onecall.partner.id'};
            $modulename = 'ONECALL_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'AIR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.air.partner.id'};
            $modulename = 'AIR_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'MCASH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mcash.partner.id'};
            $modulename = 'MCASH_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'TOYYIB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.toyyib.partner.id'};
            $modulename = 'TOYYIB_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'NUBEX' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            $modulename = 'NUBEX_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'HOPE' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.hope.partner.id'};
            $modulename = 'HOPE_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'MBSB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.mbsb.partner.id'};
            $modulename = 'MBSB_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'RED' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
            $modulename = 'REDGOLD_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'WAVPAY' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.wavpay.partner.id'};
            $modulename = 'WAVPAYGOLD_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
            $modulename = 'NOOR_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'IGOLD' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.igold.partner.id'};
            $modulename = 'IGOLD_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'BURSA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bursa.partner.id'};
            $modulename = 'BURSA_ACCOUNTS';
        }
        // otc modules
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'};

            // Do check based on user branch
            $userId = $app->getUserSession()->getUserId();
            $user = $app->userStore()->getById($userId);
            $partnerId = $user->partnerid;

            $modulename = 'BSN_ACCOUNTS';
            $dateRange = json_decode($params["daterange"]);
        }else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
            $modulename = 'ALRAJHI_ACCOUNTS';
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
            $modulename = 'POS_ACCOUNTS';
        }
        //added on 10/12/2021
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'PITIH_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KOPETRO_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KOPTTR_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'PITIHAFFI_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'BUMIRA_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KGOLD_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KGOLDAFFI_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KiGA_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'ANNURGOLD_ACCOUNTS';
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = explode(',',$params['selected']);
            $modulename = 'KASIHGOLD_ACCOUNTS';
        }
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            // If userpartnerid 
            if($user->partnerid > 0 ){
                $conditions = ['partnerid','IN', $partnerId];
            }    
            
        }
        else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $conditions = ['partnerid','IN', $partnerId];
        }
        else{
            $conditions = ['partnerid' => $partnerId];
        }
        $accountHolderStore = $app->myaccountholderStore();
        //parent::$children = [];
        $this->currentStore = $accountHolderStore;

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if('createdon' === $column->index){
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "DATE(`{$prefix}createdon`) as `{$prefix}createdon`"
                );
                $header[$key]->index->original = $original;
            }
            if ('ispep' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ispep` = " . 0 . " THEN 'No'
                     WHEN `{$prefix}ispep` = " . 1 . " THEN 'Yes' END as `{$prefix}ispep`"
                );
                $header[$key]->index->original = $original;
            }
            if ('iskycmanualapproved' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}iskycmanualapproved` = " . 0 . " THEN 'No'
                     WHEN `{$prefix}iskycmanualapproved` = " . 1 . " THEN 'Yes' END as `{$prefix}iskycmanualapproved`"
                );
                $header[$key]->index->original = $original;
            }
            if ('investmentmade' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}investmentmade` = " . 0 . " THEN 'No'
                     WHEN `{$prefix}investmentmade` = " . 1 . " THEN 'Yes' END as `{$prefix}investmentmade`"
                );
                $header[$key]->index->original = $original;
            }
            if ('status' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive'
                     WHEN `{$prefix}status` = " . 1 . " THEN 'Active'
                     WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_SUSPENDED . " THEN 'Suspended'
                     WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_BLACKLISTED . " THEN 'Blacklisted'
                     WHEN `{$prefix}status` = " . MyAccountHolder::STATUS_CLOSED . " THEN 'Closed' END as `{$prefix}status`"
                );
                $header[$key]->index->original = $original;
            }
            if ('pepstatus' === $column->index) {
                
                // $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                //     "CASE WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'
                //      WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'
                //      WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed' END as `{$prefix}pepstatus`"
                // );
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ispep` <> " . MyAccountHolder::PEP_FLAG . " THEN 'N/A'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed'" .
                    "ELSE 'Pending' END as `{$prefix}pepstatus`"
                );
                $header[$key]->index->original = $original;
            }

            if ('kycstatus' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'
                     WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'
                     WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'
                     WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed' END as `{$prefix}kycstatus`"
                );
                $header[$key]->index->original = $original;
            }
            if ('amlastatus' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PENDING . " THEN 'Pending'
                     WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PASSED . " THEN 'Passed'
                     WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_FAILED . " THEN 'Failed' END as `{$prefix}amlastatus`"
                );
                $header[$key]->index->original = $original;
            }
            if ('partnerparent' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}partnerparent` = " . MyAccountHolder::PARTNERPARENT_MASTER . " THEN 'Master'
                     WHEN `{$prefix}partnerparent` = " . MyAccountHolder::PARTNERPARENT_LOAN . " THEN 'Loan'
                     WHEN `{$prefix}partnerparent` = " . MyAccountHolder::PARTNERPARENT_AFFILIATE_MEMBER . " THEN 'Affiliate Member'
                     WHEN `{$prefix}partnerparent` = " . MyAccountHolder::PARTNERPARENT_PUBLIC . " THEN 'Public' 
                     WHEN `{$prefix}partnerparent` = " . MyAccountHolder::PARTNERPARENT_AFFILIATE_PUBLIC . " THEN 'Affiliate Public' END as `{$prefix}partnerparent`"
                );
                $header[$key]->index->original = $original;
            }
            if ('loanstatus' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}loanstatus` = " . MyAccountHolder::LOAN_PENDING . " THEN 'No'
                     WHEN `{$prefix}loanstatus` = " . MyAccountHolder::LOAN_APPROVED . " THEN 'Approved'
                     WHEN `{$prefix}loanstatus` = " . MyAccountHolder::LOAN_SETTLED . " THEN 'Settled' END as `{$prefix}loanstatus`"
                );
                $header[$key]->index->original = $original;
            }
            if ('accounttype' === $column->index) {
                
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_SENDIRI . " THEN 'Sendiri'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_BERSAMA . " THEN 'Bersama'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_ORGANIS . " THEN 'Organis'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_AMANAH . " THEN 'Amanah'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_UNKNOWN . " THEN 'Unknown'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_CASHLES . " THEN 'Cashless'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_CASHLNE . " THEN 'Cashlne'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_COMPANY . " THEN 'Company'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_COHEADING . " THEN 'Co Heading'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_SOLEPROPRIETORSHIP . " THEN 'Sole Proprietorship'
                     WHEN `{$prefix}accounttype` = " . MyAccountHolder::TYPE_INDIVIDUAL . " THEN 'Individual' END as `{$prefix}accounttype`"
                );	
                $header[$key]->index->original = $original;
            }
        }
        //print_r($params['fromsearchresult'].$params['daterange']);exit;
        
        // do check if params contain special search
        if($params['fromsearchresult']){
            // search custom fieldsphp array
            // $partnerarray = ['3416696','3416695','3416694'];
            $searchResults = [
                'mykadno' => $params['mykadno'],
                // 'partnerid' => ['IN', $partnerarray],
                // 'accountholdercode' => $params['accountholdercode'],
            ];

            $this->app->reportingManager()->generateExportFileFromSearchResult($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, null, $searchResults);
        }else{
            $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null);
        }
    
    }
    
    //get the merchant list for checkbox display: KTP
    function getMerchantList($app,$params){
        // get all group from pkb@uat
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
        //$merchantdata = array(array('id'=>'2917155','name'=>'PKB'));
		return json_encode(array('success' => true, 'merchantdata' => $merchantdata));
	}

    public function updateAccountHolderLoanFTP($app, $params){
        try{
            $file = $_FILES['grnposlist'];

            if ($params['preview'] == 1){
                $preview = true;
            }else{
                $preview = false;
            }
            
            $import = $this->app->MyGtpAccountManager()->readImportAccountHolderLoan($file, $params['partnerid'], $preview);
    
            if ($import){
                $return = [
                    'success' => true
                ];
            }else{
                $return = [
                    'error' => true
                ];
            }
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("POS grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            $return = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($return);
    }

    public function updateAccountHolderMemberFTP($app, $params){
        try{
            // get partner
            if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', '0')
                ->one();
           
            }
            else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', '0')
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', '0')
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', Partner::PARENT_AFFILIATE)
                ->orWhere('parent','=', Partner::PARENT_AFFILIATEPUBLIC)
                ->one();
    
            }
            else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', '0')
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'KODIMAS' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', '0')
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'KGOLDAFFI' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', Partner::PARENT_AFFILIATE)
                ->orWhere('parent','=', Partner::PARENT_AFFILIATEPUBLIC)
                ->one();
    
            }
            else if (isset($params['partnercode']) && 'KOPONAS' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', Partner::PARENT_MASTER)
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'WAQAF' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', Partner::PARENT_MASTER)
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'KASIH' == $params['partnercode']) {
                // $partnerId = $this->app->getConfig()->{'gtp.ktp.partner.id'};
                // $sqlHandle->andWhere('partnerid', $partnerId);
                $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('group','=', $partnerId)
                ->andWhere('parent','=', Partner::PARENT_MASTER)
                ->one();
                
            }
            else if (isset($params['partnercode']) && 'RED' == $params['partnercode']) {
                $partnerId = $this->app->getConfig()->{'gtp.red.partner.id'};
                $partner = $this->app->partnerStore()->searchTable()->select()
                ->where('id','=', $partnerId)
                ->one();
            }

            $file = $_FILES['grnposlist'];
            
            if ($params['preview'] == 1){
                $preview = true;
            }else{
                $preview = false;
            }
            
            $import = $this->app->MyGtpAccountManager()->readImportAccountHolderMember($file, $partner, $preview);
    
            if ($import){
                $return = [
                    'success' => true
                ];
            }else{
                $return = [
                    'error' => true
                ];
            }
        }catch(\Exception $e){
            if ($this->app->getDbHandle()->inTransaction()){
                $this->app->getDbHandle()->rollback();
            }
            $this->log("POS grn excel upload failed on trans_sap_no:".$gtpNo, SNAP_LOG_ERROR);
            $return = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($return);
    }

    public function approveAccountHolderEKYC($app, $params)
    {
        try {

            $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $accMgr->approveAccountHolderEKYC($accountHolder, $params['remarks'] ?? null);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function getPartnerId($app, $params)
    {
        // Default parent = 1
        $parent = 1;

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
        }else if (isset($params['partnercode']) && 'NOOR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.noor.partner.id'};
        }
        // otc modules
        else if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.bsn.partner.id'};
        }else if (isset($params['partnercode']) && 'ALRAJHI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.alrajhi.partner.id'};
        }else if (isset($params['partnercode']) && 'POSARRAHNU' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'otc.posarrahnu.partner.id'} ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KTP' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KOPETRO' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KOPTTR' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
        }
        else if (isset($params['partnercode']) && 'PKBAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            $parent = 2;
        }
        else if (isset($params['partnercode']) && 'BUMIRA' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KODIMAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KGOLDAFFI' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            $parent = 2;
        }
        else if (isset($params['partnercode']) && 'KOPONAS' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
        }
        else if (isset($params['partnercode']) && 'WAQAF' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.waqaf.partner.id'} ?? $this->app->getConfig()->{'gtp.waqaf.partner.id'};
        }
        else if (isset($params['partnercode']) && 'KASIH' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'ktp.kasih.partner.id'} ?? $this->app->getConfig()->{'gtp.kasih.partner.id'};
        }
        if($partnerId){
            echo json_encode(['success' => true, 'partnerid' =>$partnerId, 'parent' => $parent]);
        }else{
            echo json_encode(['success' => false]);
        }
    }

    public function getIdentityPhoto($app, $params){
        try{
            $partnerId = 0;
            // if (isset($params['partnercode']) && 'NUBEX' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'gtp.nubex.partner.id'};
            // }else if (isset($params['partnercode']) && 'KTP' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            // }else if (isset($params['partnercode']) && 'PKBAFFI' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.pkb.partner.id'} ?? $this->app->getConfig()->{'gtp.pkb.partner.id'};
            // }else if (isset($params['partnercode']) && 'KOPETRO' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.kopten.partner.id'} ?? $this->app->getConfig()->{'gtp.kopten.partner.id'};
            // }else if (isset($params['partnercode']) && 'KOPTTR' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.kopttr.partner.id'} ?? $this->app->getConfig()->{'gtp.kopttr.partner.id'};
            // }else if (isset($params['partnercode']) && 'BUMIRA' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.bumira.partner.id'} ?? $this->app->getConfig()->{'gtp.bumira.partner.id'};
            // }else if (isset($params['partnercode']) && 'KODIMAS' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            // }else if (isset($params['partnercode']) && 'KGOLDAFFI' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.kodimas.partner.id'} ?? $this->app->getConfig()->{'gtp.kodimas.partner.id'};
            // }else if (isset($params['partnercode']) && 'KOPONAS' == $params['partnercode']) {
            //     $partnerId = $this->app->getConfig()->{'ktp.koponas.partner.id'} ?? $this->app->getConfig()->{'gtp.koponas.partner.id'};
            // }
            
            $accountHolder = $app->myaccountholderStore()->getById($params['id']);
            $ic = $accountHolder->mykadno;
            $partnerId = $accountHolder->partnerid;
              
            /** @var MyKYCSubmission $myKycSubmission */
            $myKycImage = $app->mykycimageStore()
            ->searchTable()
            ->select()
            ->where('ach_mykadno', $ic)
            ->andWhere('partnerid', $partnerId)
            ->one();

            if ($myKycImage) {
                $front = '';
                $back = '';
                if(trim($myKycImage->image) != ''){
                    $front = 'data:jpg;base64,'.$myKycImage->image;
                }

                if(trim($myKycImage->imageback) != ''){
                    $back = 'data:jpg;base64,'.$myKycImage->imageback;
                }

                $result = array(
                    'name' => $myKycImage->name,
                    'mykadno' => $myKycImage->ach_mykadno,
                    'front_image' => '<img style="height:200px;" src="'.$front.'" />',
                    'back_image' => '<img style="height:200px;" src="'.$back.'" />'
                );
    
            } else {
                $result = array(
                    'name' => '',
                    'mykadno' => '',
                    'front_image' => '',
                    'back_image' => ''
                );
                // $image = '<div style="width: 150px;height: 190px;background: #ececec;"></div>';
                // $myKadFrontImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px"></div>';    
                // $myKadBackImage = '<div style="display: table-cell;height: 250px;text-align: center;width: 360px;vertical-align: middle;"><img style="max-width:350px;max-height:230px"></div>';
                // $result = null;
                // $journeyid = null;
            }
        }
        catch (\exception $e){

        }
        echo json_encode(['success' => true, 'data' => $result]);
    }

    // OTC MODULES/
    public function getOccupationCategory($app, $params){

        $occupationCategories = $app->myoccupationcategoryFactory()->searchTable()->select()
                                ->where("status", 1)
                                ->execute();
        $toReturn = array();
        foreach( $occupationCategories as $occupationCategory) {
            $toReturn[]= array( 'id' => $occupationCategory->id, 'value' => $occupationCategory->category);
        }
        echo json_encode(array('occupationcategory'=>$toReturn));  
        
    }

    public function getOccupationSubCategory($app, $params){

        $occupationSubCategories = $app->myoccupationsubcategoryFactory()->searchTable()->select()
                                ->where("status", 1)
                                ->execute();
        $toReturn = array();
        foreach( $occupationSubCategories as $occupationSubCategory) {
            $toReturn[]= array( 'id' => $occupationSubCategory->id, 'value' => $occupationSubCategory->code, 'occ_id' => $occupationSubCategory->occupationcategoryid);
        }
        echo json_encode(array('occupationsubcategory'=>$toReturn));  
        
    }

    public function getBankAccounts($app, $params){

        $mybanks = $app->mybankFactory()->searchTable()->select()
                                ->where("status", 1)
                                ->execute();
        $toReturn = array();
        foreach( $mybanks as $mybank) {
            $toReturn[]= array( 'id' => $mybank->id, 'value' => $mybank->name, 'code' => $mybank->code);
        }
        echo json_encode(array('bankaccounts'=>$toReturn));  
        
    }

    public function getOtcAccountHolders($app, $params){

        // Get partner id from teller session for now
        // Basically the acquired partner id is by branch AKA partner 
        $userId = $app->getUserSession()->getUserId();
        $user = $app->userStore()->getById($userId);
        $partnerId = $user->partnerid;
        // Check partner id
        // If > 0 means they are limited to branch visibility
        // If 0 means they are admin and can view everything

        $query = $app->myaccountholderFactory()->searchView()->select()
        ->where("status", 1);
       
 
        if (isset($params['partner']) && 'BSN' == $params['partner']) {
            // Look for bsn parameters
            switch ($params['option']) {
                //"MyKad No"
                case '1':
                    // set search params mykadno
                    $query->andWhere(function ($q) use ($params) {
                        $q->where('partnercusid', 'like' , $params['searchparams']);
                    });
                    break;
                //"Company Registration Number"
                case '2':
                    $query->andWhere(function ($q) use ($params) {
                        $q->where('mykadno', 'like' , $params['searchparams']);
                        $q->orWhere('nokmykadno', 'like',  $params['searchparams']);
                    });
                    break;
                //"Account Number"
                case '4':
                    // search by mykadno first
                    $query->andWhere("accountnumber", $params['searchparams']);
                    break;
                default:
                    break;
            }
            
        }else if (isset($params['partner']) && 'ALRAJHI' == $params['partner']) {
            // Look for alrajhi parameters
            switch ($params['option']) {
                //"CIC No (For Join Account Only)"
                case '1':
                    // set search params to join account only
                    $query->andWhere("partnercusid", $params['searchparams']);
                    break;
                //"Identity Card No (For Individual Only)"
                case '2':
                    $query->andWhere("mykadno", $params['searchparams']);
                    break;
                //"Company Registration No"
                case '3':
                    // search by mykadno first
                    $query->andWhere("mykadno", $params['searchparams']);
                    break;
                default:
                    break;
            }
            
        }else if (isset($params['partner']) && 'POSARRAHNU' == $params['partner']) {
            // Look for alrajhi parameters
            switch ($params['option']) {
                //"Identity Card No (For Individual Only)"
                case '1':
                    $query->andWhere(function ($q) use ($params) {
                        $q->where('mykadno', 'like' , $params['searchparams']);
                        $q->orWhere('nokmykadno', 'like',  $params['searchparams']);
                    });
                    break;
                default:
                    break;
            }
            
        }

        $query->orderBy("id", "DESC");
        $myaccountholders = $query->execute();

        //echo count($myaccountholders);exit;

        // extract mykadno string and split to 2 variables
        // check if mykadno contains '-'
        if(substr_count($params['mykadno'], '-')){
            // search for param without dash
            $altMykadno = preg_replace('/-/', '', $params['mykadno']);
        }else{
            //search for param with '-'
            $altMykadno = substr($params['mykadno'],0, 6) .'-'.substr($params['mykadno'],6, 7 -5).'-'. substr($params['mykadno'],8, 12);
        }

        // if($partnerId > 0){
        //     $myaccountholders = $app->myaccountholderFactory()->searchView()->select()
        //         ->where("status", 1)
        //         ->andWhere("mykadno", "IN", [$params['mykadno'] ,   $altMykadno])
        //         ->andWhere("partnerid", $partnerId)
        //         ->execute();
        // }else{
        //     $myaccountholders = $app->myaccountholderFactory()->searchView()->select()
        //         ->where("status", 1)
        //         ->andWhere("mykadno", "IN", [$params['mykadno'],   $altMykadno])
        //         ->execute();
        // }
        // Set custom search params based on partner
        
        // $myaccountholders = $app->myaccountholderFactory()->searchView()->select()
        // ->where("status", 1)
        // ->andWhere("partnercusid", $params['searchparams'])
        // ->execute();
            
        $toReturn = array();
        foreach( $myaccountholders as $myaccountholder) {
            $addressline1 = $myaccountholder->addressline1 ? $myaccountholder->addressline1 . ', ' : '';
            $addressline2 = $myaccountholder->addressline2 ? $myaccountholder->addressline2 . ', ' : '';
            $addresspostcode = $myaccountholder->addresspostcode ? $myaccountholder->addresspostcode . ', ' : '';
            $addresscity = $myaccountholder->addresscity ? $myaccountholder->addresscity . ', ' : '';
            $addressstate = $myaccountholder->addressstate ? $myaccountholder->addressstate . ', ' : '';

            $address = $addressline1. 
                        $addressline2. 
                        $addresspostcode. 
                        $addresscity;
                        // $addressstate;
            // $address = $myaccountholder->addressline1 . ', '. 
            //             $myaccountholder->addressline2.  ', '. 
            //             $myaccountholder->addresspostcode.  ', '. 
            //             $myaccountholder->addresscity.  ', '. 
            //             $myaccountholder->addressstate;

            // Status 
            switch ($myaccountholder->status){
                case MyAccountHolder::STATUS_INACTIVE:
                    $status = 'Inactive';
                    break;
                case MyAccountHolder::STATUS_ACTIVE:
                    $status = 'Active';
                    break;
                case MyAccountHolder::STATUS_SUSPENDED:
                    $status = 'Suspended';
                    break;
                case MyAccountHolder::STATUS_BLACKLISTED:
                    $status = 'Blacklisted';
                    break;
                case MyAccountHolder::STATUS_CLOSED:
                    $status = 'Closed';
                    break;
                default:
                    

            }

            // Get accountholdersummary informations
            $accMgr = $app->mygtpAccountManager();

            $accountHolderSummary = $accMgr->getAccountHolderSummary($myaccountholder);
         
            $myaccountholderpartner = $myaccountholder->getPartner();
            $settings = $app->mypartnersettingStore()->getByField('partnerid', $myaccountholderpartner->id);
    

            // Do check if there are any kyc images for account
            $myKycImage = $app->mykycimageStore()
            ->searchTable()
            ->select()
            ->where('ach_mykadno', $myaccountholder->mykadno)
            ->andWhere('partnerid', $myaccountholder->partnerid)
            ->one();

            if ($myKycImage) {
                $front = '';
                $back = '';
                if(trim($myKycImage->image) != ''){
                    $front = 'data:jpg;base64,'.$myKycImage->image;
                }

                if(trim($myKycImage->imageback) != ''){
                    $back = 'data:jpg;base64,'.$myKycImage->imageback;
                }

                $result = array(
                    'name' => $myKycImage->name,
                    'mykadno' => $myKycImage->ach_mykadno,
                    'front_image' => '<img style="height:200px;" src="'.$front.'" />',
                    'back_image' => '<img style="height:200px;" src="'.$back.'" />'
                );
    
            }
            // If there are returns do
            $images = $result ? $result : null;
            
            // get account type
            if($myaccountholder->accounttype){
                $accounttypestring = $myaccountholder->getAccountTypeString();
            }

            // do partner related check
            if (isset($params['partner']) && 'BSN' == $params['partner']) {
                $casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});


                $balanceArr = $casa->getAccountBalance($myaccountholder);
                if($balanceArr){
                    $balance = $balanceArr['balance'];
                }else{
                    $balance = 0;
                }
                $updateFields = array(
                    'id' => $myaccountholder->id,
                );
                $accMgr->updateMyAccountHolderByCasaApi($updateFields);

            }else{
                $balance = 0;
            }

            if (isset($params['partner']) && 'ALRAJHI' == $params['partner']) {
                // Perform account update
                $updateFields = array(
                    'id' => $myaccountholder->id,
                );
                $accMgr->updateMyAccountHolderByCasaApi($updateFields);

                // get new myaccountholder and populate wont update immediately as casa takes time
                //$myaccountholder = $app->myaccountholderStore()->getById($myaccountholder->id);
            }
            
            // End check
            $toReturn[]= array( 
                'id' => $myaccountholder->id, 
                'partnerid' => $myaccountholder->partnerid, 
                'partnername' => $myaccountholder->partnername, 
                'partnercode' => $myaccountholder->partnercode, 
                'amountbalance' => $myaccountholder->amountbalance, 
                'accountholdercode' => $myaccountholder->accountholdercode,

                'fullname' => $myaccountholder->fullname, 
                'mykadno' => $myaccountholder->mykadno,
                'partnercusid' => $myaccountholder->partnercusid, 
                'email' => $myaccountholder->email,
                'phoneno' => $myaccountholder->phoneno, 
                'preferredlang' => $myaccountholder->preferredlang,
                'occupationcategory' => $myaccountholder->occupationcategory, 
                'occupationcategoryid' => $myaccountholder->occupationcategoryid,
                'occupationsubcategory' => $myaccountholder->occupationsubcategory, 
                'occupationsubcategoryid' => $myaccountholder->occupationsubcategoryid,
                'referralsalespersoncode' => $myaccountholder->referralsalespersoncode, 
                'referralbranchcode' => $myaccountholder->referralbranchcode,
                'pincode' => $myaccountholder->pincode, 
                'sapacebuycode' => $myaccountholder->sapacebuycode,
                'sapacesellcode' => $myaccountholder->sapacesellcode, 
                'bankname' => $myaccountholder->bankname,
                'accountname' => $myaccountholder->accountname, 
                'accountnumber' => $myaccountholder->accountnumber,
                'accounttype' => $myaccountholder->accounttype ? $myaccountholder->accounttype : '',
                'accounttypestr' => $accounttypestring ? $accounttypestring : '',
                'nokfullname' => $myaccountholder->nokfullname, 
                'nokmykadno' => $myaccountholder->nokmykadno,
                'nokbankname' => $myaccountholder->nokbankname, 
                'nokaccountnumber' => $myaccountholder->nokaccountnumber,
                'investmentmade' => $myaccountholder->investmentmade, 
                'xaubalance' => number_format($myaccountholder->xaubalance,3),
                'ispep' => $myaccountholder->ispep, 
                'pepdeclaration' => $myaccountholder->pepdeclaration,
                'pepstatus' => $myaccountholder->pepstatus, 'iskycmanualapproved' => $myaccountholder->iskycmanualapproved ? $myaccountholder->iskycmanualapproved : 0,

                'address' => $address,

                'status' => $myaccountholder->status,
                'statusname' => $status,
                'kycstatus' => $myaccountholder->kycstatus,
                'amlastatus' => $myaccountholder->amlastatus,
                'statusremarks' => $myaccountholder->statusremarks,
                'campaigncode' => $myaccountholder->campaigncode,
                // 'passwordmodified' => $myaccountholder->passwordmodified->format('Y-m-d H:i:s'),
                // 'lastloginon' => $myaccountholder->lastloginon->format('Y-m-d H:i:s'),
                'lastloginip' => $myaccountholder->lastloginip,
                'verifiedon' => $myaccountholder->verifiedon,
                'createdon' => $myaccountholder->createdon->format('Y-m-d H:i:s'),
                'modifiedon' => $myaccountholder->modifiedon->format('Y-m-d H:i:s'),
                'createdbyname' => $myaccountholder->createdbyname,
                'modifiedbyname' => $myaccountholder->modifiedbyname,


                // Return additional information
                'goldbalance' => $accountHolderSummary['gold_balance'],
                'avgbuyprice' => $accountHolderSummary['avgbuyprice'],
                'totalcostgoldbalance' => $accountHolderSummary['totalcostgoldbalance'],
                'diffcurrentpriceprcetage' => $accountHolderSummary['diffcurrentpriceprcetage'],
                'currentgoldvalue' => $accountHolderSummary['currentgoldvalue'],
                'availablebalance' => $accountHolderSummary['available_balance'], 
                'minbalancexau' => $settings->minbalancexau,
                
                'images' => $images,

                'accountbalance' => $balance ? $balance : 0,
            );
        }
        echo json_encode(array('success' => true, 'records'=>$toReturn));  
        
    }

    public function generate_password($length = 20){
        // $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
        //           '0123456789`-=~!@#$%^&*()_+,./<>?;:[]{}\|';

        $chars =  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
        '0123456789!@#$%&';
      
        $str = '';
        $max = strlen($chars) - 1;
      
        for ($i=0; $i < $length; $i++)
          $str .= $chars[random_int(0, $max)];
      
        return $str;
    }

    // Register accounts
    public function otcRegister($app, $params)
    {
        try {

            // $accountHolder = $app->myaccountholderStore()->getById($params['id']);

            // get partner
            // $partnerid = $app->getConfig()->{'gtp.go.partner.id'}; 
            if (isset($params['partnercode']) && 'BSN' == $params['partnercode']) {
                $partnerid = $this->app->getConfig()->{'otc.bsn.partner.id'} ?? $this->app->getConfig()->{'gtp.bsn.partner.id'};
               
                // Do BSN CUSTOM MAGIC
                // Set bankaccount type to BSN
                // search bnk_code where code = BSN
                $params['bank_account_id'] = 11;

                $allowDuplicate = 1;
                $haveWebsite = false;

            }else if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
                $partnerid = $this->app->getConfig()->{'otc.alrajhi.partner.id'} ?? $this->app->getConfig()->{'gtp.alrajhi.partner.id'};
                $haveWebsite = false;

                $params['bank_account_id'] = 22;
            
            }else if (isset($params['partnercode']) && 'POSARRAHNU' == $params['partnercode']) {
                $partnerid = $this->app->getConfig()->{'otc.posarrahnu.partner.id'}  ?? $this->app->getConfig()->{'gtp.posarrahnu.partner.id'};
                $haveWebsite = true;
            }
            if (!$partnerid || intval($partnerid) <= 0){
                $partnerid = 0;
            }

            // Do checking, if user has partnerid, save userpartnerid as partnerid 
            // Override process
            $userId = $app->getUserSession()->getUserId();
	    $user = $app->userStore()->getById($userId);

            if(!$user || $userId == '' || $userId == null || $userId == 0){
                $this->log("User id when opening account: ".$user->id);
                throw new \Exception('user id not found');
            }

            if($user->partnerid > 0 ){
                $partnerid = $user->partnerid;
            }    
            $partner = $app->partnerStore()->getById($partnerid);

            // Check if partner is within skip list
            if(!$allowDuplicate){
                // Do checking if nric exists
                $accountsByNRIC = $this->app->myaccountholderStore()->searchView()->select()
                ->where('mykadno', $params['mykad_number'])
                ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                ->execute();

                if(sizeof($accountsByNRIC) > 0){
                    // Account exists, NRIC in use
                    throw MyGtpAccountHolderMyKadExists::fromTransaction([], ['mykadno' => $params['mykad_number']]);
                }
                // Do checking if email exists
                $accountsByEmail = $this->app->myaccountholderStore()->searchView()->select()
                ->where('email', $params['email'])
                ->andWhere('status', MyAccountHolder::STATUS_ACTIVE)
                ->execute();

                
                if(sizeof($accountsByEmail) > 0){
                    // Account exists, Email in use
                    throw EmailAddressTakenException::fromTransaction($partner, ['email' => $params['email']]);
                }
            }else{
                // do checking to ensure accountnumber is not used in parcusid
                $accountsByPartnercusid = $this->app->myaccountholderStore()->searchView()->select()
                    ->where('accountnumber', $params['bank_account_number'])
                    ->execute();
                    
                if(sizeof($accountsByPartnercusid) > 0){
                    // Account exists, Email in use
                    
                    throw MyOtcPartnercusidExists::fromTransaction($partner, ['partnercusid' => $params['bank_account_number']]);
                }
            }
               
                

            // check if generate password is required
            if (isset($params['generate_password']) && $params['generate_password'] === "true"){
                $params['password'] = $this->generate_password(10);
            }
            if (isset($params['generate_pin']) && $params['generate_pin'] === "true"){
                $pin = substr($params['mykad_number'],6, 7 -5). substr($params['mykad_number'],8, 12);
                $params['new_pin'] = (string)$pin;
            }
           

            $occupationCategory = $app->myoccupationcategoryStore()->getById($params['occupation_category']);
            $occupationSubCategory = $app->myoccupationsubcategoryStore()->getById($params['occupation_subcategory']);

            $branchCode = current($partner->getBranches())->code;

            // print_r($params['transactionpurpose']);exit;

            /** @var MyGtpAccountManager $accMgr */
            $accMgr = $app->mygtpAccountManager();
            $register = $accMgr->register(
                $partner ?? null,
                strtoupper($params['full_name']) ?? null,
                $params['mykad_number'] ?? null,
                $params['phone_number'] ?? null,
                $occupationCategory ?? null,
                $occupationSubCategory ?? null,
                $params['email'] ?? null,
                $params['password'] ?? null,
                MyLocalizedContent::LANG_ENGLISH,
                $branchCode,
                $params['nok_full_name'] ?? null,
                $params['nok_mykad_number'] ?? null,
                $params['nok_phone'] ?? null,
                $params['nok_email'] ?? null,
                $params['nok_address'] ?? null,
                $params['nok_relationship'] ?? null,
                '',
                $params['referralsalespersoncode'] ?? null,
                false,
                $params['partner_customer_id'] ?? null,
                $params['partner_data'] ?? null
            );
            $accountHolder = $register;

            

            //transaction purpose reuse loan column
            $accountHolder->loanreference = $params['transactionpurpose']; 
            $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);

            $dateofbirth = date('Y-m-d H:i:s',strtotime($params['dateofbirth']));
            
            $additionalData = $this->app->achadditionaldataStore()->create([
                'accountholderid' => $accountHolder->id,
                'nationality' => $params['nationality'] ?? null,
                'dateofbirth'  => $dateofbirth ?? null,
                'religion' => $params['religion'] ?? null,
                'gender' => $params['gender'] ?? null,
                'bumiputera' => $params['bumiputera'] ?? null,
                'maritalstatus' => $params['maritalstatus'] ?? null,
                'race' => $params['race'] ?? null,
                'jointgender' => $params['jointgender'] ?? null,
                'jointdateofbirth' => $params['jointdateofbirth'] ?? null,
                'jointnationality' => $params['jointnationality'] ?? null,
                'jointreligion' => $params['jointreligion'] ?? null,
                'jointrace' => $params['jointrace'] ?? null,
                'jointbumiputera' => $params['jointbumiputera'] ?? null,
                'jointmaritalstatus' => $params['jointmaritalstatus'] ?? null,
                'category' => $params['category'] ?? null,
            ]);

            $additionalData = $this->app->achadditionaldataStore()->save($additionalData);

            if (isset($params['partnercode']) && 'BSN' == $params['partnercode']) {
               
                // Do Additional Field save
                $accountHolder->accounttype = $params['accounttype'];
                $accountHolder->partnercusid = $params['partnercusid'];

                $accountHolder->accounttype = $params['accounttype'];
                $accountHolder->accountname = $params['full_name'];
                $accountHolder->accountnumber = $params['bank_account_number'];
                // $accountHolder->statusremarks = $params['statusremarks'];
                // $accountCode = $this->generateAlrajhiAccountCode($accountHolder->id);

                //$accountHolder->accountholdercode = $accountCode;
                $accountHolder->referralintroducercode = $params['referralintroducercode'];

                $partnerCode = $this->app->partnerStore()->searchTable()->select()
                    ->where('id', $accountHolder->partnerid)
                    ->execute();

                //customize accountholdercode 	
                $accountCode = $this->generateBsnAccountCode($accountHolder->id,$accountHolder->partnerid,$partner->sapcompanysellcode1);	
                $accountHolder->accountholdercode = $accountCode;

                $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);
            
                // Update CRS Function
                // $casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
                
                // $casa->doRelationshipCreateClose($accountHolder, $user->partnercode, true);

            }else if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
                // Do Additional Field save
                $accountHolder->partnercusid = $params['partnercusid'];
                $accountHolder->referralintroducercode = $params['referralintroducercode'];

                // save acc info
                $accountHolder->accounttype = $params['accounttype'];
                $accountHolder->accountname = $params['accountname'];
                $accountHolder->accountnumber = $params['bank_account_number'];
                $accountHolder = $this->app->myaccountholderStore()->save($accountHolder);
            
            }else if (isset($params['partnercode']) && 'POSARRAHNU' == $params['partnercode']) {
                
                
            }

            // if registration is successful, update pin and bank accountinformation
            // Edit pin
            $accMgr->editPincode($accountHolder, $params['new_pin'], $accountHolder->pincode);

            $accMgr->editProfile(
                $accountHolder,
                $params['address'],
                '',// $params['address_line_2'],
                $params['postcode'] ?? null,
                $params['city'] ?? null,
                $params['state'] ?? null,
                $params['nok_full_name'] ?? null,
                $params['nok_mykad_number'] ?? null,
                $params['nok_phone'] ?? null,
                $params['nok_email'] ?? null,
                $params['nok_address'] ?? null,
                $params['nok_relationship'] ?? null,
                $occupationCategory ?? null,
                $occupationSubCategory ?? null,
                $params['referralsalespersoncode'] ?? null,
                '',
                $params['new_pin']
            );

            // Get instance of bank
            $myBank = $app->mybankStore()->getById($params['bank_account_id']);
            // if bank accoutn not filled. skip
            if(!empty($params['bank_account_id'])){
                $accMgr->editBankAccount($accountHolder, $myBank, $params['full_name'], $params['bank_account_number'], $params['new_pin']);
            }
            

            // Send email after registration
            // Generate PDF

            if(isset($params['partnercode']) && 'POSARRAHNU' == $params['partnercode']){
                $PDF = $this->generateRegisterPDF($accountHolder->id, $params['password'], $params['partnercode'], $params['new_pin'], $haveWebsite);
                $accMgr->sendEmailRegistration($accountHolder, $PDF, $params['password'], $params['partnercode'], $params['new_pin']);
            }
            
            // Check if have image uploaded, if have image save image

            // Encrypt account password before passing to frontend
            $encryptedpass = $accMgr->encrypt_decrypt($params['password'], 'encrypt');
            $encryptedpin = $accMgr->encrypt_decrypt($params['new_pin'], 'encrypt');
            
            echo json_encode(['success' => true, 'accountholderid' => $accountHolder->id, 'accountholderfullname' => $accountHolder->fullname, 'password' => $encryptedpass, 'pin' => $encryptedpin, 'havewebsite' => $haveWebsite,]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function getAccountsFromCasa($app, $params){

        // Get partner id from teller session for now
        // Basically the acquired partner id is by branch AKA partner 
        // Get typeOpt params from user input and search all available accoants
        try{

            // $json2 ='[{ 
            //     "index": 0,
            //     "data": [
            //         {
            //             "accno" : "3192301412", "name" : "Joint Account"
            //         },
            //         {
            //             "accno" : "3192301412", "name" : "Joint Account"
            //         },
            //         {
            //             "accno" : "3192301412", "name" : "Joint Account"
            //         }
            //     ]
            // }]';
              
            // $returns = json_decode($json2, true);

            // // Subject to be changed
            // if($params['type_opt']){
            //     $toReturn = array();
            //     foreach ($returns[0]['data'] as $return) {
            //         // End check
            //         $toReturn[]= array( 
            //             'accno' => $return['accno'], 
            //             'name' => $return['name'],  
            //         );
            //     }             
            // }
            //print_r($params);exit;   
            // Legend for variables across partners
            // BSN : ALRAJHI
            // option: TypeOpt : searchFlag
            // searchfield : Optinp : partyId 
            if($params['option']){
                $evidenceCode = '';
                // call casa first api]
                if (isset($params['partnercode']) && 'BSN' == $params['partnercode']) {
                    $casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
                    // always true for bsn
                    $callGetCasaInfo = true;
                    $params['typopt'] = $params['option'];
                    $params['optinp'] = $params['searchfield'];
                }else if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
                    $casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
                    $callGetCasaInfo = false;
                    // Do alrajhi check, if individual we fire
                    // For reference, searchflag is checked
                    // “1” search by CIC 
                    // “2” search by document ID 
                    // “3” search by Company ID N
         
                    $params['searchFlag'] = $params['option'];
                    $params['keyword'] = $params['searchfield'];

                  
                    //print_r($params);exit;
                    if ('1' == $params['option']) {			
                        $callGetCasaInfo = true;
                   
                        // Set fields to call getcasainfo
                        $params['searchFlag'] = $params['option'];
                        $params['partyId'] = $paddedNumber = str_pad($params['searchfield'], 16, '0', STR_PAD_LEFT);
                        // set searchfield to partyid
                        // $params['partyId'] = $params['searchfield'];
                        // get joint acc info
                        $jointAccount = $casa->getCustomerInfo($params);
                        $evidenceCode = $casa->getEvidenceCode($params['keyword']);
                    }
                    
                    //individual
                    if ('2' == $params['option']) {			
        
                        // $params['idNumber'] = $params['searchfield'];
                        $toReturn[]= array( 
                            'searchFlag' =>  $params['option'], // aka searchflag
                            'keyword' => $params['searchfield'],
                        );
                    }
                    
                    //company
                    if ('3' == $params['option']) {			

                        // $params['companyIdNumber'] = $params['searchfield'];
                        $toReturn[]= array( 
                            'searchFlag' =>  $params['option'], // aka searchflag
                            'keyword' => $params['searchfield'],
                        );
                    }
                    
                    // Perform searchflag check
                 
                    // getCustomerInfo -> getCasaInfo
                }
                
                // $params['searchFlag'] = 1;
                // $params['partyId'] = '0000000000000264';
                // check if required to call getCasaInfo()
                if($callGetCasaInfo == true){
                    $data = $casa->getCasaInfo($params);//print_r($data);exit;

                    //print_r($data);exit;
    
                    // print_r($data);exit;
                    // casa return fields 
                    //Array ( [success] => 1 [data] => Array ( [0] => Array ( [accountnumber] => adadada [accounttype] => banker ) ) [error_code] => [error_message] => )
                    // if success 
                    if($data['success']){
                        // do something
                    
                        foreach ($data['data'] as $record){
    
                            if (isset($params['partnercode']) && 'BSN' == $params['partnercode']) {
                                    $toReturn[]= array( 
                                        'accountnumber' =>  $record['accountnumber'],
                                        'accounttype' => $record['accounttype'],
                                        'accounttypestr' => $record['accounttypestr'],
                                        'optinp' =>  $record['accountnumber'],
                                        'typopt' => 4, // type 4 - accountno
                                    );
                            }else if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
                               
                                $toReturn[]= array( 
                                    'searchFlag' =>  $params['option'], // aka searchflag
                                    'keyword' => $record['PartyId'],
                                    'accountnumber' =>  $record['AcctIdentValue'],
                                    'accountname' =>  $record['AcctTitle'],
                                    'isaccountclosed' => $record['ClosedDt'] ? true : false,
                                    'accounttype' => $record['AcctTypeValue'] ? $record['AcctTypeValue'] : null,
                                    'branchident' => $record['BranchIdent'] ? $record['BranchIdent'] : null,
                                    'partnerdata' => $record['BranchIdent'] . '-' . $record['AcctTypeValue'] . '-' . $record['AcctIdentValue'],
                                    'partyacctreltype' => $record['PartyAcctRelType'] ? true : false,
                                );
                            }
    
                        
                            
                        }
                    }else{
                        throw new \Exception("Unable to acquire data");
                    }//print_r($toReturn);exit;
                }
                

                $getCasaData = $this->getAccountInfoFromCasa($casa, $toReturn, $params['partnercode']);
                
                // Do reverse check here
                if($callGetCasaInfo == false){
                    // store party id returned from getcasadata as params
                    foreach ($getCasaData as $index => $record){
                        // For alrajhi
                        if (isset($params['partnercode']) && 'ALRAJHI' == $params['partnercode']) {
       
                            $params['keyword'] = $record['partnercusid'];
                            $getCustomerAccounts[] = $casa->getCasaInfo($params);

                            // match and add account data 
                            $getCasaData[$index]['accountnumber'] = $getCustomerAccounts[0]['data'][$index]['AcctIdentValue'];
                            $getCasaData[$index]['accountname'] = $getCustomerAccounts[0]['data'][$index]['AcctTitle'];
                            $getCasaData[$index]['branchident'] = $getCustomerAccounts[0]['data'][$index]['BranchIdent'];
                            $getCasaData[$index]['partnerdata'] = $getCustomerAccounts[0]['data'][$index]['BranchIdent'] . '-' . $getCustomerAccounts[0]['data'][$index]['AcctTypeValue'] . '-' . $getCustomerAccounts[0]['data'][$index]['AcctIdentValue'];
                            // check if account is closed
                            // If closedDt has date means it is closed
                            if (empty($getCustomerAccounts[0]['data'][$index]['ClosedDt'])) {
                                // Variable is empty
                                $getCasaData[$index]['isaccountclosed'] = false;
                            } else {
                                // Variable has a value
                                $getCasaData[$index]['isaccountclosed'] = true;
                            }

                            // remove all closed accounts from record
                            if($getCasaData[$index]['isaccountclosed'] == true) {
                                // Remove from record
                                
                                
                            }
                        }

                    
                        
                    }
                    $evidenceCode = $casa->getEvidenceCode($params['partyId']);
                }

                if ($getCasaData != null) {
                    // do something if success is empty
                    $data = $getCasaData;
                }else{
                    throw new \Exception("No data found."); 
                }

                // foreach ($returns[0]['data'] as $return) {
                //     // End check
             
                // }             
            }else{
                if($params['option'] == null){
                    throw new \Exception("Please select account type."); 
                }else{
                    throw new \Exception("The field cannot be empty."); 
                }
                
            }

          
      
            

            echo json_encode(array('success' => true, 'records'=>$data, 'evidenceCode' =>$evidenceCode));  
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
       
        

        
    }

    // 2nd api to get casa account info
    public function getAccountInfoFromCasa($casa, $params, $partnerCode){

        try{

            if (empty($params)) {
                // do something if success is empty
                return;
            }

            foreach ($params as $record){
                // Search db whether casa id is already in use
                // $accountInUse = $app->myaccountholderStore()->searchTable()
                // ->where('partnercusid', $record['accountnumber'])
                // ->one();
    
                // mark account as new and ready to register
                // Call for account info
                $data = $casa->getCustomerInfo($record);//print_r($data);exit;
                //joint
                if($data['success']){
                    // do something
                    foreach ($data['data'] as $key => $returndata){
                        
                        $combinedData[$key] = $returndata;
                        
                    }
                    if (isset($partnerCode) && 'BSN' == $partnerCode) {
                        $accountsByNRIC = $this->app->myaccountholderStore()->searchView()->select()
                            ->where('accountnumber', $combinedData['myaccountholder']['accountnumber'])
                            ->execute();
    
                    }else if (isset($partnerCode) && 'ALRAJHI' == $partnerCode) {
                            $accountsByNRIC = $this->app->myaccountholderStore()->searchView()->select()
                            ->where('partnercusid', $combinedData['myaccountholder']['partnercusid'])
                            ->execute();
                    }

                    // if exist
                    if($accountsByNRIC){
                        // mark account as used - danger aka red
                        $accountstatuscolor = 'danger';
                        $accountstatus = 0;
                    }else{
                        // mark account as unused - success aka green
                        $accountstatuscolor = 'success';
                        $accountstatus = 1;
                    }

                    if (isset($partnerCode) && 'BSN' == $partnerCode) {
                        $toReturn[]= array( 
                            // 'myaccountholder' => array(
                                'fullname' => $combinedData['myaccountholder']['fullname'],
                                'partnercusid' => $combinedData['myaccountholder']['partnercusid'],
                                'email' => $combinedData['myaccountholder']['email'],
                                'mykadno' => $combinedData['myaccountholder']['mykadno'],
                                'phoneno' => $combinedData['myaccountholder']['phoneno'],
                                'accountname' =>  $combinedData['myaccountholder']['accountname'],
                                'accountnumber' =>  $combinedData['myaccountholder']['accountnumber'],
    
                                // Get back original search queries
                                'accounttype' => $record['accounttype'],
                                'accounttypestr' => $record['accounttypestr'],
                                'accountstatus' => $accountstatus,
                                'accountstatuscolor' => $accountstatuscolor,
                            // ),
                            // 'myaddress' => array(
                                'line1' => $combinedData['myaddress']['line1'],
                                'postcode' => $combinedData['myaddress']['postcode'],
                            // ),

                                'title' => $combinedData['achadditionaldata']['title'],
                                'dateofbirth' => $combinedData['achadditionaldata']['dateofbirth'],
                                'gender' => $combinedData['achadditionaldata']['gender'],
                                'maritalstatus' => $combinedData['achadditionaldata']['maritalstatus'],
                                'religion' => $combinedData['achadditionaldata']['religion'],
                                'bumiputera' => $combinedData['achadditionaldata']['bumiputera'],
                                'idtype' => $combinedData['achadditionaldata']['idtype'],
                                'category' => $combinedData['achadditionaldata']['category'],
                                'race' => $combinedData['achadditionaldata']['race'],
                                'nationality' => $combinedData['achadditionaldata']['nationality']
    
                                // extra fields for BSN
                                // 'bankaccount' => 11 // BSN
                            
                        );
                    }else if (isset($partnerCode) && 'ALRAJHI' == $partnerCode) {

                        $accounttypestr = $combinedData['myaccountholder']['accounttype'];
                        if(21 == $accounttypestr){
                            $accounttypestr = 'COMPANY';
                        }else if(22 == $accounttypestr){
                            $accounttypestr = 'COHEADING';
                        }else if(23 == $accounttypestr){
                            $accounttypestr = 'SOLE PROPRIETORSHIP';
                        }else{
                            $accounttypestr = 'INDIVIDUAL';
                        }
                        // Check if fullname has 2 returns
                        // if (strpos($combinedData['myaccountholder']['fullname'], ',') !== false) {
                      
                        if (strpos($record['accountname'], ',') !== false) {              
                            // Split the string by ","
                            //$splitArray = explode(',', $combinedData['myaccountholder']['fullnamse']);
                            $splitArray = explode(',', $record['accountname']);
                            // Remove "\\" from each element in the array
                            $cleanedArray = array_map(function($element) {
                              return str_replace('\\', '', $element);
                            }, $splitArray);
                            // custom while keeping original joint name
                            $combinedData['myaccountholder']['jointname'] = $combinedData['myaccountholder']['fullname'];
                            $combinedData['myaccountholder']['fullname'] = $cleanedArray[0];
                            $combinedData['myaccountholder']['nokfullname'] = $cleanedArray[1];
                        }else if (strpos($record['accountname'], '\\') !== false) {              
                            // Split the string by ","
                            //$splitArray = explode(',', $combinedData['myaccountholder']['fullnamse']);
                            $splitArray = explode('\\', $record['accountname']);
                            // Remove "\\" from each element in the array
                            $cleanedArray = array_map(function($element) {
                              return str_replace('\\', '', $element);
                            }, $splitArray);
           
                            // custom while keeping original joint name
                            $combinedData['myaccountholder']['jointname'] = $combinedData['myaccountholder']['fullname'];
                            $combinedData['myaccountholder']['fullname'] = $cleanedArray[0];
                            $combinedData['myaccountholder']['nokfullname'] = $cleanedArray[1];
                        }

                        // new rule dictates that if account is closed, do not show hence return is skipped
                        if (!$record['isaccountclosed']){
                            $toReturn[]= array( 
                                // 'myaccountholder' => array(
                                'fullname' => $combinedData['myaccountholder']['fullname'],
                                'nokfullname' => $combinedData['myaccountholder']['nokfullname'] ? $combinedData['myaccountholder']['nokfullname'] : '',
                                    'jointname' => $combinedData['myaccountholder']['jointname'] ? $combinedData['myaccountholder']['jointname'] : '',
                                'partnercusid' => $combinedData['myaccountholder']['partnercusid'],
                                'email' => $combinedData['myaccountholder']['email'],
                                'mykadno' => $combinedData['myaccountholder']['mykadno'],
                                'nokmykadno' => $combinedData['myaccountholder']['nokmykadno'] ? $combinedData['myaccountholder']['nokmykadno'] : '',
                                'phoneno' => $combinedData['myaccountholder']['phoneno'],
                                'accounttype' => $combinedData['myaccountholder']['accounttype'],

                                'accounttypestr' => $accounttypestr,
                                'accountname' =>  $record['accountname'] ? $record['accountname'] : '',
                                'accountnumber' =>  $record['accountnumber'] ? $record['accountnumber'] : '',
                                    'branchident' => $record['branchident'] ? $record['branchident'] : '',
                                    'partnerdata' =>  $record['partnerdata'] ? $record['partnerdata'] : '',
                                    'searchflag' =>  $record['searchFlag'] ? $record['searchFlag'] : '',
                                
    
                                // Get back original search queries
                                //'accounttypestr' => $record['accounttypestr'],
                                'accountstatus' => $accountstatus,
                                'accountstatuscolor' => $accountstatuscolor,
                                // ),
                                // 'myaddress' => array(
                                    'line1' => $combinedData['myaddress']['line1'],
                                    'line2' => $combinedData['myaddress']['line2'],
                                    'postcode' => $combinedData['myaddress']['postcode'],
                                    'city' => $combinedData['myaddress']['city'],
                                    'state' => $combinedData['myaddress']['state'],
                                    'mailingline1' => $combinedData['myaddress']['mailingline1'],
                                    'mailingline2' => $combinedData['myaddress']['mailingline2'],
                                    'mailingcity' => $combinedData['myaddress']['mailingcity'],
                                    'mailingpostcode' => $combinedData['myaddress']['mailingpostcode'],
                                    'mailingstate' => $combinedData['myaddress']['mailingstate'],
                                    'joinaccount' => $combinedData['joinaccount'],
                                // ),
    
                                // extra fields for BSN
                                // 'bankaccount' => 11 // BSN
                            
                            );
                        }  
                    }                  
                    //print_r($toReturn);exit;
                }
            }
           
            //print_r($toReturn);exit;
              
     
          
            return $toReturn; 
        } catch (\Exception $e) {
            return null;
        }
        

    }

    public function getCasaAccounts($app, $params) {

        try{
            // Set fields to call getcasainfo
            $params['partyId'] = $paddedNumber = str_pad($params['searchfield'], 16, '0', STR_PAD_LEFT);

            $casa = BaseCasa::getInstance($app->getConfig()->{'otc.casa.api'});
            $data = $casa->getCasaInfo($params);
            if($data['success']){
                // do something
            
                //branchIdent-AccTypeValue-AccIdentValue 
                foreach ($data['data'] as $record) {
                    $string = $record['branchIdent'] . '-' . $record['AccTypeValue'] . '-' . $record['AcctIdentValue'];
                    $toReturn[] = $string;
                }
            }else{
                throw new \Exception("Unable to acquire data");
            }

            echo json_encode(array('success' => true, 'records'=>$toReturn));  
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
        }
    }

    public function generateRegisterPDFOTC($accountholderid, $password = null, $partnercode = null, $pin = null, $haveWebsite = false, $haveNextOfKin = true){
        $accountholderManager=$this->app->mygtpaccountManager();

        // if($password){
        //     $registerPdf = $accountholderManager->printRegister($accountholderid, $password, $partnercode, $pin, $haveWebsite, $haveNextOfKin);
        // }else{
        //     $registerPdf = $accountholderManager->printRegister($accountholderid);
        // }
        $registerPdf = $accountholderManager->printRegisterOTC($accountholderid, $password, $partnercode, $pin, $haveWebsite, $haveNextOfKin);

        // print_r($registerPdf);exit;
        $projectBase = $this->app->getConfig()->{'projectBase'};
		if($projectBase == 'ALRAJHI'){
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/alrajhi_opening_account_template.docx');
        }else{
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('format/opening_account_template.docx');
        }
        

         //print_r($registerPdf['account_type_str']);
        if($registerPdf['account_type_str'] == 'SENDIRI' || $registerPdf['account_type_str'] == 'INDIVIDUAL'){
            $templateProcessor->setValues([
                'date'                   => $registerPdf['date'],
                'partner_name'           => $registerPdf['partner_name'],
                'parcustid'              => $registerPdf['parcustid'],
                //'account_no'             => $registerPdf['account_no'],
                'account_type'           => $registerPdf['account_type_str'],
                'typeofid'               => 'MyKad No / Passport No',
                'account_type_str'       => $registerPdf['account_type_str'],
                'mykad_no'               => $registerPdf['mykad_no'],
                'title'                  => $registerPdf['title'],
                'full_name'              => $registerPdf['full_name'],
                'nationality'            => $registerPdf['nationality'],
                'dateofbirth'            => $registerPdf['dateofbirth'],
                'bumiputera'             => $registerPdf['bumiputera'],
                'religion'               => $registerPdf['religion'],
                'gender'                 => $registerPdf['gender'],
                'maritalstatus'          => $registerPdf['maritalstatus'],
                'race'                   => $registerPdf['race'],
                'address'                => $registerPdf['address'],
                //'postcode'               => $registerPdf['postcode'],
                //'city'   	 	         => $registerPdf['city'],
                'mailingaddress1'        => $registerPdf['mailingaddress1'],
                //'mailingpostcode'        => $registerPdf['mailingpostcode'],
                //'mailingtown'            => $registerPdf['mailingtown'],
                'homephoneno'            => $registerPdf['homephoneno'],
                'email'                  => $registerPdf['email'],
                'phone_no'               => $registerPdf['phoneno'],
                'occupation'             => $registerPdf['occupation'],
                'occupation_category'    => $registerPdf['occupation_category'],
                'employername'           => $registerPdf['employername'],
                'officeaddress1'         => $registerPdf['officeaddress1'],
                //'officepostcode'         => $registerPdf['officepostcode'],
                //'officestate'            => $registerPdf['officestate'],

                'jointtypeofid'          => '',
                'join_nok_fullname'      => '',
                'join_nok_mykadno'       => '',
                'join_nok_phoneno'       => '',
                //'nok_relationship'       => '',
                'join_nok_occupation'       => '',

                'jointgender'            => '',
                'jointdateofbirth'       => '',
                'jointnationality'       => '',
                'jointreligion'          => '',
                'jointrace'              => '',
                'jointbumiputera'        => '',
                'jointmaritalstatus'     => '',
                'jointtitle'             => '',
                'join_email'              => '',

                'dateofincorporation'    => '',
                'placeofincorporation'   => '',
                'businessdesc'           => '',
                'company_nok_fullname'   => '',
                'phonenoincorporation'   => '',
                //'company_nok_phoneno'    => '',
                
                'teller'                 => $registerPdf['teller'],
            ]);

        }else if($registerPdf['account_type_str'] == 'BERSAMA' || $registerPdf['account_type_str'] == 'COHEADING'){
            $templateProcessor->setValues([
                'date'                   => $registerPdf['date'],
                'partner_name'           => $registerPdf['partner_name'],
                //'account_no'             => $registerPdf['account_no'],
                'parcustid'              => $registerPdf['parcustid'],
                'typeofid'               => 'MyKad No / Passport No',
                'account_type_str'       => $registerPdf['account_type_str'],
                'mykad_no'               => $registerPdf['mykad_no'],
                'title'                  => $registerPdf['title'],
                'full_name'              => $registerPdf['full_name'],
                'nationality'            => $registerPdf['nationality'],
                'dateofbirth'            => $registerPdf['dateofbirth'],
                'bumiputera'             => $registerPdf['bumiputera'],
                'religion'               => $registerPdf['religion'],
                'gender'                 => $registerPdf['gender'],
                'maritalstatus'          => $registerPdf['maritalstatus'],
                'race'                   => $registerPdf['race'],
                'address'                => $registerPdf['address'],
                //'postcode'               => $registerPdf['postcode'],
                //'city'   	 	         => $registerPdf['city'],
                'mailingaddress1'        => $registerPdf['mailingaddress1'],
                //'mailingpostcode'        => $registerPdf['mailingpostcode'],
                //'mailingtown'            => $registerPdf['mailingtown'],
                'homephoneno'            => $registerPdf['homephoneno'],
                'email'                  => $registerPdf['email'],
                'phone_no'               => $registerPdf['phoneno'],
                'occupation'             => $registerPdf['occupation'],
                'occupation_category'    => $registerPdf['occupation_category'],
                'employername'           => $registerPdf['employername'],
                'officeaddress1'         => $registerPdf['officeaddress1'],
                //'officepostcode'         => $registerPdf['officepostcode'],
                //'officestate'            => $registerPdf['officestate'],

                'jointtypeofid'          => '',
                'join_nok_fullname'      => $registerPdf['nok_fullname'],
                'join_nok_mykadno'       => $registerPdf['nok_mykad'],
                'join_nok_phoneno'       => $registerPdf['join_nok_phoneno'],
                //'nok_relationship'       => $registerPdf['relationship'],

                'jointgender'            => $registerPdf['jointgender'],
                'jointdateofbirth'       => $registerPdf['jointdateofbirth'],
                'jointnationality'       => $registerPdf['jointnationality'],
                'jointreligion'          => $registerPdf['jointreligion'],
                'jointrace'              => $registerPdf['jointrace'],
                'jointbumiputera'        => $registerPdf['jointbumiputera'],
                'jointmaritalstatus'     => $registerPdf['jointmaritalstatus'],
                'jointtitle'             => $registerPdf['jointtittle'],
                'join_nok_occupation'   => $registerPdf['jointoccupation'],
                'join_email'              => '',

                'dateofincorporation'    => '',
                'placeofincorporation'   => '',
                'businessdesc'           => '',
                'company_nok_fullname'   => '',
                //'company_nok_phoneno'    => '',
                'phonenoincorporation'   => '',
                
                'teller'                 => $registerPdf['teller'],
            ]);
        }else{
            $templateProcessor->setValues([
                'date'                   => $registerPdf['date'],
                'partner_name'           => $registerPdf['partner_name'],
                'parcustid'              => $registerPdf['parcustid'],
                //'account_no'             => $registerPdf['account_no'],
                'typeofid'               => 'Company Registration Number',
                'account_type_str'       => $registerPdf['account_type_str'],
                'mykad_no'               => $registerPdf['mykad_no'],
                'title'                  => '',
                'full_name'              => $registerPdf['full_name'],
                'nationality'            => '',
                'dateofbirth'            => '',
                'bumiputera'             => '',
                'religion'               => '',
                'gender'                 => '',
                'maritalstatus'          => '',
                'race'                   => '',
                'address'                => $registerPdf['address'],
                //'postcode'               => $registerPdf['postcode'],
                //'city'   	 	         => $registerPdf['city'],
                'mailingaddress1'        => $registerPdf['mailingaddress1'],
                //'mailingpostcode'        => $registerPdf['mailingpostcode'],
                //'mailingtown'            => $registerPdf['mailingtown'],
                'homephoneno'            => $registerPdf['homephoneno'],
                'email'                  => $registerPdf['email'],
                'phone_no'               => $registerPdf['phoneno'],
                'occupation'             => '',
                'occupation_category'    => '',
                'employername'           => '',
                'officeaddress1'         => '',
                //'officepostcode'         => $registerPdf['officepostcode'],
                //'officestate'            => $registerPdf['officestate'],

                'jointtitle'             => '',
                'join_email'             => '',
                'jointtypeofid'          => '',
                'join_nok_fullname'      => '',
                'join_nok_mykadno'       => '',
                'join_nok_phoneno'       => '',
                //'nok_relationship'       => '',
                'join_nok_occupation'    => '',

                'jointgender'            => '',
                'jointdateofbirth'       => '',
                'jointnationality'       => '',
                'jointreligion'          => '',
                'jointrace'              => '',
                'jointbumiputera'        => '',
                'jointmaritalstatus'     => '',

                'dateofincorporation'    => $registerPdf['dateofbirth'],
                'placeofincorporation'   => '',
                'businessdesc'           => '',
                'company_nok_fullname'   => '',
                'phonenoincorporation'   => '',
                //'company_nok_phoneno'    => '',
                
                'teller'                 => $registerPdf['teller'],
            ]);
        }

		$filename = 'BSN_REGISTER_'.$registerPdf['accountholder_code'].'.docx';
		$templateProcessor->saveAs('word/'.$filename);

		$fileUrl = 'word/'.$filename;
		//return("word/".$filename);
		$command = "sudo -u siteadm /usr/local/nginx/html/gtp/source/snapapp_otc/printScript.sh $filename";
                                        $output = shell_exec($command);

                                        $pattern = '/pdf\/(.*?)\.pdf/';
                                        preg_match($pattern, $output, $matches);
                                        $pdfPath = $matches[1];

                                        echo json_encode(['success'=> true, 'url'=> 'pdf/'.$pdfPath.'.pdf']);exit;
        //echo json_encode(array('success' => true, 'records'=>$registerPdf['jointdetails']));

        if ($registerPdf){
            $accountholder = $this->app->myaccountholderStore()->getById($accountholderid);
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            $filename = 'ACE_REGISTER_'.$accountholder->accountholdercode;
            if ($developmentEnv){
                $registerPdf = '<div style="font-size: 20px;">----------------DEMO----------------</div><br>'.$registerPdf;
                $filename = 'DEMO_'.$filename;
            }
        }else{
            echo 'Invalid Order. Please Contact Administrative.';
            return;
        }

        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        
        $registerPdf = $this->completePdf($registerPdf, $partnercode);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        $html2pdf->pdf->setTitle($filename);
        $html2pdf->writeHTML($registerPdf);

        return ['file' => $html2pdf, 'filename'=> $filename];
    }

    public function generateRegisterPDF($accountholderid, $password = null, $partnercode = null, $pin = null, $haveWebsite = false, $haveNextOfKin = true){
        $accountholderManager=$this->app->mygtpaccountManager();

        // if($password){
        //     $registerPdf = $accountholderManager->printRegister($accountholderid, $password, $partnercode, $pin, $haveWebsite, $haveNextOfKin);
        // }else{
        //     $registerPdf = $accountholderManager->printRegister($accountholderid);
        // }
        $registerPdf = $accountholderManager->printRegister($accountholderid, $password, $partnercode, $pin, $haveWebsite, $haveNextOfKin);

        if ($registerPdf){
            $accountholder = $this->app->myaccountholderStore()->getById($accountholderid);
            $developmentEnv = $this->app->getConfig()->{'snap.environtment.development'};
            $filename = 'ACE_REGISTER_'.$accountholder->accountholdercode;
            if ($developmentEnv){
                $registerPdf = '<div style="font-size: 20px;">----------------DEMO----------------</div><br>'.$registerPdf;
                $filename = 'DEMO_'.$filename;
            }
        }else{
            echo 'Invalid Order. Please Contact Administrative.';
            return;
        }

        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', 3);
        $html2pdf->pdf->SetDisplayMode('fullpage');
        
        $registerPdf = $this->completePdf($registerPdf, $partnercode);


        // ob_start();
        // include dirname(__FILE__).'/../../vendor/spipu/html2pdf/examples/res/example15.php';
        // $content = ob_get_clean();
        // $html2pdf->writeHTML($content);
        
        $html2pdf->pdf->setTitle($filename);
        $html2pdf->writeHTML($registerPdf);

        return ['file' => $html2pdf, 'filename'=> $filename];
    }

    public function printRegisterPDF($app, $params){

		
		// $accountholderManager=$this->app->mygtpaccountManager();
		
		// Check if Buy Or Sell
		try{

			// if(!$params['customerid']){
			// 	$orderPdf = $spotordermanager->printRegister($params['accountholderid']);
			// }else{
			// 	$orderPdf = $spotordermanager->printRegister($params['accountholderid'], $params['customerid']);
			// }
            // param p is password
            if($params['p']){

                if (isset($params['b']) && 'BSN' == $params['b']) {
                    $registerPdf = $this->generateRegisterPDFOTC($params['accountholderid'], null, $params['b'], null, false, false);
                }else if (isset($params['b']) && 'ALRAJHI' == $params['b']) {
                    $registerPdf = $this->generateRegisterPDFOTC($params['accountholderid'], null, $params['b'], null, false, false);
                
                }else if (isset($params['b']) && 'POSARRAHNU' == $params['b']) {
                    $registerPdf = $this->generateRegisterPDF($params['accountholderid'], $params['p'], $params['b'], $params['c'], $params['w']);
                }
               
            }else{
                $registerPdf = $this->generateRegisterPDFOTC($params['accountholderid']);
            }

            if(isset($params['b']) && 'POSARRAHNU' == $params['b']){
                echo json_encode(['success' => true, 'return' => $registerPdf['file']->output($registerPdf['filename'].'.pdf')]);
            }else{
                return $registerPdf;
            }
            
		}catch(\Exception $e){
			echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
		}
	   
       
       
        //return $html2pdf->output('FILENAME.pdf');
	}

    private function completePdf($content, $projectbase){
        $f = 1;
        
        // $wrap_start = 
        // '<page backtop="10mm" backbottom="10mm" backleft="20mm" backright="20mm">
		// <page_header>
		// 	<img style="width: 100%;" src="src/resources/images/'.strtolower($projectbase).'_header.png">
		
		// </page_header>
		// <br>
		// <br>
		// <br>
		// <br>
		// <br>
		// <br>';
        // $wrap_end = '</page>';
        
        // // $header = $this->getHeader();
        // // $footer = $this->getFooter();

        

        // $html = $wrap_start.$header.$footer.$content.$wrap_end;

        // return $html;
	}

    private function getHeader($draft = false){
        $header = '
            <page_header>
                
                <table style="width: 100%; border: solid 1px black;">
                    <tr>
                        <td style="text-align: left;    width: 33%">html2pdf</td>
                        <td style="text-align: center;    width: 34%">Test header</td>
                        <td style="text-align: right;    width: 33%">'.date("Y").'</td>
                    </tr>
                </table>
            </page_header>
        ';
        

        return $header;
    }

    private function getFooter($draft = false){
        $footer = '
            <page_footer>
            '.date('d/m/Y').'
            </page_footer>
        ';
        
        return $footer;
	}

    // Perform check to filter records
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

    protected function generateBsnAccountCode($id,$partnerId, $partnerCode,$prefix = "8", $length = 6)
    {

        
        
        $idString = str_pad($id, $length, '0', STR_PAD_LEFT);
        $accountCode = $prefix . $partnerCode . $idString;

        return $accountCode;
    }
    
}
