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
use Snap\object\Userswitchbranchlog;
use Snap\sqlrecorder;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Chen(chen.teng.siang@silverstream.my)
 * @version 1.0
 */

class otcuserswitchbranchloghandler extends CompositeHandler
{
    function __construct(App $app)
    {   
        $this->mapActionToRights('list', '/root/bsn/userswitchbranchlog/list;');
        $this->mapActionToRights('exportExcel', '/all/access');

        $this->app = $app;
        $currentStore = $app->userswitchbranchlogFactory();
        $this->sqlRecorder = new sqlrecorder();
        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore,1));
        } 
        else {
            $this->addChild(new ext6gridhandler($this,$currentStore,1));
        }
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {
		$app = App::getInstance();
        
		if (isset($params['partnercode']) && 'BSN' === $params['partnercode']) {
            
            $dateRange = json_decode($params["daterange"]);
            
            if(isset($params['daterange'])){
                $sqlHandle
                ->andwhere('createdon', '>=', $dateRange->startDate)
                ->andWhere('createdon', '<=', $dateRange->endDate);
            }

        }

        return array($params, $sqlHandle, $fields);
    }

    // function onPreListing($objects, $params, $records) 
    // {
	// 	$app = App::getInstance();

	// 	foreach($records as $key => $record) {
    //         // print_r($records[$key]);exit;
    //         $createuser = $app->userStore()->getById($records[$key]['createdby']);
    //         $moduser = $app->userStore()->getById($records[$key]['modifiedby']);
    //         $records[$key]['createdbyname'] = $createuser->username;
    //         $records[$key]['modifiedbyname'] = $moduser->username;
	// 	}
	// 	return $records;
	// }

    function exportExcel($app, $params){
		
        $modulename = 'USER_SWICTH_BRANCH';
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);

        $userswitchbranchlogStore = $app->userswitchbranchlogStore();
        //parent::$children = [];
        $this->currentStore = $userswitchbranchlogStore;

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

            if ('status' === $column->index) {
					
                $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                    "CASE WHEN `{$prefix}status` = " . Userswitchbranchlog::STATUS_INACTIVE . " THEN 'Inactive'
                     WHEN `{$prefix}status` = " . Userswitchbranchlog::STATUS_ACTIVE . " THEN 'Active'"
                );
                $header[$key]->index->original = $original;
            }
        }
        
        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', null, null, null);
    
    }
}
