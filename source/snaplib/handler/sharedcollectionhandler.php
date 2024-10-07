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

class sharedcollectionHandler extends collectionHandler {
    function __construct(App $app) {
        //parent::__construct('/root/trading/mbb', 'order');
        $this->mapActionToRights('detailview', '/root/tekun/collection/list;/root/koponas/collection/list;/root/sahabat/collection/list');

        $this->mapActionToRights('list', '/root/tekun/collection/list;/root/koponas/collection/list;/root/sahabat/collection/list');
        
        $this->mapActionToRights("exportExcel", "/root/tekun/collection/export;/root/koponas/collection/list;/root/sahabat/collection/list");

        $this->app = $app;

        $store = $app->goodsreceivenoteStore();
        $this->currentStore = $store;
        $this->addChild(new ext6gridhandler($this, $store, 1));
    }

    function onPreQueryListing($params, $sqlHandle, $fields){
        $app = App::getInstance();
        if (isset($params['partner']) && 'TEKUN' === $params['partner']) {
           
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'},
            ];
            $partnername = 'TEKUN';
        }
        else if (isset($params['partner']) && 'KOPONAS' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'},
            ];
            $partnername = 'KOPONAS';
        }
        else if (isset($params['partner']) && 'SAHABAT' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'},
            ];
            $partnername = 'SAHABAT';
        }
        $sqlHandle->andWhere('partnerid', 'IN', $sharedPartnerId_s);
        return array($params, $sqlHandle, $fields);
    }

    function exportExcel($app, $params){
        
        if (isset($params['partner']) && 'TEKUN' === $params['partner']) {
           
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.tekun1.partner.id'},
                $app->getConfig()->{'gtp.tekun2.partner.id'},
            ];
            $partnername = 'TEKUN';
        }
        else if (isset($params['partner']) && 'KOPONAS' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.koponas1.partner.id'},
                $app->getConfig()->{'gtp.koponas2.partner.id'},
            ];
            $partnername = 'KOPONAS';
        }
        else if (isset($params['partner']) && 'SAHABAT' === $params['partner']) {
            $sharedPartnerId_s = [
                $app->getConfig()->{'gtp.sahabat1.partner.id'},
                $app->getConfig()->{'gtp.sahabat2.partner.id'},
            ];
            $partnername = 'SAHABAT';
        }

        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        
        $modulename = $partnername.'_COLLECTION';

        $conditions = ["partnerid", "IN", $sharedPartnerId_s];

        $statusRenderer = [
			0 => "Inactive",
			1 => "Active",
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, '', '', $conditions, null, null, $statusRenderer);
    
    }
}