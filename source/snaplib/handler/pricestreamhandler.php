<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Calvin(calvin.thien@ace2u.com)
 * @version 1.0
 */
class pricestreamhandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'pricestream');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

		$this->mapActionToRights('list', 'list');

		$this->app = $app;

		$pricestreamStore = $app->pricestreamfactory();
		$this->addChild(new ext6gridhandler($this, $pricestreamStore,1 ));
	}

/*
	public function onPreListing($objects, $params, $records)
    {
		/*
        foreach ($records as $key => $record) {
			$rekod[$key] = $record;

        }

		$rekod = App::getInstance()->tagFactory()->searchTable()->select()
                    ->order(desc, )
                    ->execute();
		$length = $records.length();
		print_r($length);
		foreach ($records as $key => $record) {
			$rekod[$key] = $record;

        }
        return $rekod;
    }*/

/*
	function onPreQueryListing($params, $sqlHandle, $fields) {

		$permission = $this->app->hasPermission('/root/hq/columns/show_all_branches');

		//$sqlHandle->andWhere('id', 1);

		// Today's date
		$dateToday = date('Y-m-d H:i:s');
		//$timeDateToday = strtotime($dateToday);
		// Date 1 week ago
		$dateWeek = date('Y-m-d H:i:s', mktime(0, 0, 0, date("m"), date("d")-5, date("Y")));
		//print_r($dateWeek);



		if(!$permission){
				 	$sqlHandle->andWhere('pricesourceon', '<=' ,$dateToday)
								->andWhere('pricesourceon', '>=' ,$dateWeek);
								//->andWhere('id', '<=', 8);
								//->andWhere('id', '<=', 8);

								// print_r($total);
		}

		return array($params, $sqlHandle, $fields);

	} */

}

?>
