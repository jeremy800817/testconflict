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

class sharedbuybackHandler extends CompositeHandler {
    function __construct(App $app) {
        //parent::__construct('/root/trading/mbb', 'order');
        $this->mapActionToRights('detailview', '/root/tekun/buyback/list;/root/koponas/buyback/list;/root/sahabat/buyback/list');

        $this->mapActionToRights('list', '/root/tekun/buyback/list;/root/koponas/buyback/list;/root/sahabat/buyback/list');
        
        $this->mapActionToRights("exportExcel", "/root/tekun/buyback/export;/root/koponas/buyback/list;/root/sahabat/buyback/list");

        $this->app = $app;

        $store = $app->buybackFactory();
        $this->currentStore = $store;
        $this->addChild(new ext6gridhandler($this, $store, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
        $app = App::getInstance();
        if (isset($params['partner']) && 'tekun' === $params['partner']) {
           
			$sharedPartnerId_s = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'},
            ];
        }
		else if (isset($params['partner']) && 'koponas' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'},
            ];
        }
		else if (isset($params['partner']) && 'sahabat' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'},
            ];
        }
        
        $sqlHandle->andWhere('partnerid', 'IN', $sharedPartnerId_s);
        return array($params, $sqlHandle, $fields);
    }

    function exportExcel($app, $params){
        if (isset($params['partner']) && 'tekun' === $params['partner']) {
			$sharedPartnerId_s = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'},
            ];
            $modulename = 'TEKUN_BUYBACK';
        }
		else if (isset($params['partner']) && 'koponas' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'},
            ];
            $modulename = 'KOPONAS_BUYBACK';
        }
		else if (isset($params['partner']) && 'sahabat' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'},
            ];
            $modulename = 'SAHABAT_BUYBACK';
        }

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        
        

        $conditions = ["partnerid", "IN", $sharedPartnerId_s];

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