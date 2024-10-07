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
class apilogshandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'apilog');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

		$this->mapActionToRights('list', 'list');

		$this->app = $app;

		$apilogsStore = $app->apilogfactory();

        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $apilogsStore, 1));
        } else {
            $this->addChild(new ext6gridhandler($this, $apilogsStore, 1));
        }
	}

	/*
		This method is to get data for view details
	*/
	function detailview($app, $params) {
		$object = $app->apilogfactory()->getById($params['id']);
/*
		if ($object->vendorid == 1){
			$vendorname = 'Ace Logistic';
		}else if ($object->vendorid == 2){
			$vendorname = 'GDEX';
		}else {
			$vendorname = 'Unidentified';
		}
*/
		if($object->modifiedby > 0) $modifieduser = $app->userFactory()->getById($object->modifiedby)->name;
		else $modifieduser = 'System';
		if($object->createdby > 0) $createduser = $app->userFactory()->getById($object->createdby)->name;
		else $createduser = 'System';

		$detailRecord['default'] = [ //"ID" => $object->id,
									'Type' => $object->type,
									'From IP' => $object->fromip,
									'System Initiate' => $object->systeminitiate,
									'Request Data' => $object->requestdata,
									'Response Data' => $object->responsedata,


									'Created on' => $object->createdon->format('Y-m-d h:i:s'),
									'Created by' => $createduser,
									'Modified on' => $object->modifiedon->format('Y-m-d h:i:s'),
									'Modified by' => $modifieduser,
									'Status' => $object->status,
									];

		echo json_encode(array('success' => true, 'record' => $detailRecord));
	}

	// 	public function getRights($action)
 //    {
 //        return '/all/access';
 //    }

 //    *
 //     * This method will determine is this particular handler is able to handle the action given.
 //     *
 //     * @param  App    $app    The application object (for getting user session etc to test?)
 //     * @param  String $action The action name to be handled
 //     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.

 //    public function canHandleAction($app, $action)
 //    {
 //        return true;
 //    }

	// function doAction( $app, $action, $params) {
	// 	return parent::doAction($app, $action, $params);
	// }
}

?>
