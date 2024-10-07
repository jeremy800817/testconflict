<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyFee;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam(azam@silverstream.my)
 * @version 1.0
 */
class myfeehandler extends CompositeHandler
{

    function __construct(App $app)
    {
        parent::__construct('/root/bmmb', 'fee');

        $this->mapActionToRights('listmyfeetype', 'list');
        $this->mapActionToRights('listmyfeecalculationtype', 'list');
        $this->mapActionToRights('listmyfeemode', 'list');
        $this->mapActionToRights('list', 'list');
        $this->mapActionToRights('add',  'add');
        $this->mapActionToRights('edit', 'edit');

        $this->app = $app;

        $myfeeStore = $app->myfeeStore();
        $this->addChild(new ext6gridhandler($this, $myfeeStore));
    }

    public function listMyFeeType($app, $params)
    {
        $this->genericList(MyFee::getType());
    }

    private function genericList($data)
    {
        $dataArray['records'] = array();
        foreach ($data as $value) {
            $dataArray['records'][] = ['id' => $value->id, 'code' => $value->code];
        }
        $dataArray['recordsReturned'] = count($dataArray['records']);
        $result = json_encode($dataArray, JSON_PARTIAL_OUTPUT_ON_ERROR);
        echo $result;
    }
}
