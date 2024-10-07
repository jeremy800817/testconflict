<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */
class myscreeninglistimportloghandler extends CompositeHandler
{

    function __construct(App $app)
    {
        parent::__construct('/root/bmmb', 'amla');

        $this->mapActionToRights('list', 'list');

        $this->app = $app;

        $myScreeningListImportLogStore = $app->myscreeninglistimportlogStore();
        $this->addChild(new ext6gridhandler($this, $myScreeningListImportLogStore, 1));
    }

    function onPreListing($objects, $params, $records)
    {
        foreach ($records as $key => $record) {
            if ('0000-00-00 00:00:00' == $records[$key]['importedon']) {
                $records[$key]['importedon'] = null;
            }
        }
        return $records;
    }
}
