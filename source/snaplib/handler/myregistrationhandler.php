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

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam (azam@@silverstream.my)
 * @version 1.0
 */
class myregistrationhandler extends CompositeHandler
{
    const STATUS_FAILED = 'FAILED';

    function __construct(App $app)
    {
        parent::__construct('/root/bmmb/report', 'registration');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('exportExcel', 'list');

        $this->app = $app;

        $this->currentStore = $app->myaccountholderStore();
        $this->addChild(new ext6gridhandler($this, $this->currentStore, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerId = $this->app->getConfig()->{'gtp.bmmb.partner.id'};
            $sqlHandle->andWhere('partnerid', $partnerId);
        }

        if (isset($params['status']) && self::STATUS_FAILED === $params['status']) {
            $sqlHandle->where(function ($q) {
                $q->where('kycstatus', MyAccountHolder::KYC_FAILED);
                $q->orWhere('amlastatus', MyAccountHolder::AMLA_FAILED);
                $q->orWhere('pepstatus', MyAccountHolder::PEP_FAILED);
            });

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

    function exportExcel($app, $params)
    {
        $partnerid = 0;
        $modulename = 'GTP_REGISTRATION';
        $conditions = null;

        if (isset($params['partnercode']) && 'BMMB' === $params['partnercode']) {
            $partnerid = $app->getConfig()->{'gtp.bmmb.partner.id'};
            $modulename = 'BMMB_REGISTRATION';
        }        

        $header     = json_decode($params["header"]);
        $dateRange  = json_decode($params["daterange"]);
        $status     = $params['status'];
        $modulename = $modulename . '_' . $status;
        
        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('kycstatus' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_FAILED . " THEN 'Failed'" .
                    "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_INCOMPLETE . " THEN 'Incomplete'" .
                    "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PASSED . " THEN 'Passed'" .
                    "WHEN `{$prefix}kycstatus` = " . MyAccountHolder::KYC_PENDING . " THEN 'Pending'" .
                    "ELSE 'Pending' END as `{$prefix}kycstatus`"
                );

                $header[$key]->index->original = $original;
            } 
            
            if ('amlastatus' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_FAILED . " THEN 'Failed'" .
                    "WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PASSED . " THEN 'Passed'" .
                    "WHEN `{$prefix}amlastatus` = " . MyAccountHolder::AMLA_PENDING . " THEN 'Pending'" .
                    "ELSE 'Pending' END as `{$prefix}amlastatus`"
                );
                $header[$key]->index->original = $original;
            }

            if ('pepstatus' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}ispep` <> " . MyAccountHolder::PEP_FLAG . " THEN 'N/A'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PENDING . " THEN 'Pending'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_PASSED . " THEN 'Passed'" .
                    "WHEN `{$prefix}pepstatus` = " . MyAccountHolder::PEP_FAILED . " THEN 'Failed'" .
                    "ELSE 'Pending' END as `{$prefix}pepstatus`"
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

        $conditions = function ($q) use ($startAt, $endAt, $partnerid, $status) {
            $q->where('createdon', '>=', $startAt->format('Y-m-d H:i:s'));
            $q->andWhere('createdon', '<=', $endAt->format('Y-m-d H:i:s'));
            $q->andWhere('partnerid', $partnerid);

            if (self::STATUS_FAILED === $status) {
                $q->where(function ($r) {
                    $r->where('kycstatus', MyAccountHolder::KYC_FAILED);
                    $r->orWhere('amlastatus', MyAccountHolder::AMLA_FAILED);
                    $r->orWhere('pepstatus', MyAccountHolder::PEP_FAILED);
                });
            }
        };

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null, 2, false);
    }
}
