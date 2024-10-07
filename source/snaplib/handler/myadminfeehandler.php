<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2021
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyMonthlyStorageFee;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author  Azam
 * @version 1.0
 */
class myadminfeeHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/bmmb/report', 'adminfee');

        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('exportExcel', 'list');

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
        }

        if (isset($params['accountholderid'])) {
            $sqlHandle->andWhere('accountholderid', $params['accountholderid']);
        }

        $sqlHandle->andWhere('status', MyMonthlyStorageFee::STATUS_ACTIVE);

        return array($params, $sqlHandle, $fields);
    }

    function onPreListing($objects, $params, $records)
    {
        foreach($records as $key => $record ) {
            $records[$key]['adminfeeamount'] = $records[$key]['adminfeexau'] * $records[$key]['price'];
        }

        return $records;
    }
    



    function exportExcel($app, $params)
    {
        $header = json_decode($params["header"]);
        $modulename = 'ADMIN_FEE';
        $dateRange = json_decode($params["daterange"]);
        $specialRenderer = null;
        $prefix = $this->currentStore->getColumnPrefix();

        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('adminfeeamount' === $column->index) {
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "`{$prefix}adminfeexau` * `{$prefix}price` as `{$prefix}adminfeeamount`"
                );

                $header[$key]->index->original = $original;
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

        $conditions = function ($q) use ($startAt, $endAt) {
            $q->where('chargedon', '>=', $startAt->format('Y-m-d H:i:s'));
            $q->andWhere('chargedon', '<=', $endAt->format('Y-m-d H:i:s'));
        };

        /** @var \Snap\manager\ReportingManager $reportingManager */
        $reportingManager = $this->app->reportingManager();
        $reportingManager->generateMyGtpReport($this->currentStore, $header, $modulename, null, $conditions, null);
    }
}
