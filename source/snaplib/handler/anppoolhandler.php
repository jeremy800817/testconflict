<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

class anppoolhandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/mbb', 'anppool');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

		$this->mapActionToRights('exportExcel', 'export');
		$this->mapActionToRights('exportZip', 'export');

		$this->app = $app;

        $currentStore = $app->mbbapfundStore();
        $this->currentStore = $currentStore;
		$this->addChild(new ext6gridhandler($this, $currentStore, 1));
	}

    function exportExcel($app, $params){
        
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        // $params['summary']

        $modulename = 'MIB_ANPFUND';

        $statusRenderer = [
			0 => "Inactive",
			1 => "Active"
		];

        $this->app->reportingManager()->generateExportFile($this->currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, null, null, null, null, 'ordercreatedon', $statusRenderer);
    }

    function exportZip($app, $params){
        
        $header = json_decode($params["header"]);
        $dateRange = json_decode($params["daterange"]);
        // $params['summary']

        $modulename = 'MIB_ANPFUND';

        $prefix = $this->currentStore->getColumnPrefix();
        foreach ($header as $key => $column) {

            // Overwrite index value with expression
            $original = $column->index;
            if ('status' === $column->index) {
                
                // $header[$key]->index = $this->currentStore->searchTable(false)->raw(
                //     "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive'
                //      WHEN `{$prefix}status` = " . 1 . " THEN 'Active' END as `{$prefix}status`"
                // );
                $header[$key]->hydrahon = true;
                // $header[$key]->index->original = $original;
                $header[$key]->value = "CASE WHEN `{$prefix}status` = " . 0 . " THEN 'Inactive' WHEN `{$prefix}status` = " . 1 . " THEN 'Active' END as `{$prefix}status`";
            }
        }


		//Get Lightweight Current store name
		$currentStore = $this->currentStore->getTableName();

        $this->app->reportingManager()->generateExportFileZip($currentStore, $header, $dateRange->startDate, $dateRange->endDate, $modulename, null, null, null, null, 'ordercreatedon', null, $params['email']);
    }
}
?>
