<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
Use Snap\App;
use Snap\object\order;
use Snap\InputException;
use Snap\object\account;
use Snap\object\rebateConfig;

class posbuybackHandler extends CompositeHandler {
    function __construct(App $app) {
        //parent::__construct('/root/trading/mbb', 'order');
        $this->mapActionToRights('detailview', '/root/pos/buyback/list');

        $this->mapActionToRights('list', '/root/pos/buyback/list');
        
        $this->mapActionToRights("exportExcel", "/root/pos/buyback/export");

        $this->app = $app;

        $store = $app->buybackFactory();
        $this->currentStore = $store;
        $this->addChild(new ext6gridhandler($this, $store, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
        $app = App::getInstance();
        $posPartnerId_s = [
            $app->getConfig()->{'gtp.pos1.partner.id'},
            $app->getConfig()->{'gtp.pos2.partner.id'},
            $app->getConfig()->{'gtp.pos3.partner.id'},
            $app->getConfig()->{'gtp.pos4.partner.id'}
        ];
        $sqlHandle->andWhere('partnerid', 'IN', $posPartnerId_s);
        return array($params, $sqlHandle, $fields);
    }

    function exportExcel($app, $params){
        
        $posPartnerId_s = [
            $app->getConfig()->{'gtp.pos1.partner.id'},
            $app->getConfig()->{'gtp.pos2.partner.id'},
            $app->getConfig()->{'gtp.pos3.partner.id'},
            $app->getConfig()->{'gtp.pos4.partner.id'}
        ];

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        
        $modulename = 'POS_BUYBACK';

        $conditions = ["partnerid", "IN", $posPartnerId_s];

        $statusRenderer = [
			0 => "Pending",
			1 => "Confirmed",
			2 => "Process Collect",
			3 => "Completed",
			4 => "Failed",
			5 => "Reversed"
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    
    }
}