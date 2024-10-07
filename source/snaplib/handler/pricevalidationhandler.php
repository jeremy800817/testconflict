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
class pricevalidationhandler extends CompositeHandler {
	function __construct(App $app) {
		parent::__construct('/root/system', 'pricevalidation');
		// $currentStore = $app->pricevalidationFactory();
		// $this->addChild(new ext6gridhandler($this, $currentStore));

		$this->mapActionToRights('list', 'list');

		$this->app = $app;

		$pricevalidationStore = $app->pricevalidationfactory();
		$this->addChild(new ext6gridhandler($this, $pricevalidationStore, 1 ));
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