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

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class mypaymentdetailHandler extends CompositeHandler {

	function __construct(App $app) {
		parent::__construct('/root/bmmb', 'fpx');      

		$this->mapActionToRights('list', 'list');

		$this->app = $app;

		$myPaymentDetailStore = $app->mypaymentdetailFactory();
		$this->addChild(new ext6gridhandler($this, $myPaymentDetailStore));
	}



}
