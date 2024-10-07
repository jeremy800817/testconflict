<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use DateTime;
use Snap\App;
use Snap\object\OtcUserActivityLog;
use Snap\object\MyLocalizedContent;
use Snap\sqlrecorder;


/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Ang(ang@@silverstream.my)
 * @version 1.0
 */

class useractivityloghandler extends CompositeHandler
{
    function __construct(App $app)
    {   
        $this->mapActionToRights('list', '/root/bmmb/profile/list;/root/go/profile/list;/root/one/profile/list;/root/onecall/profile/list;/root/air/profile/list;/root/mcash/profile/list;/root/toyyib/profile/list;/root/ktp/profile/list;/root/kopetro/profile/list;/root/kopttr/profile/list;/root/pkbaffi/profile/list;/root/bumira/profile/list;/root/nubex/profile/list;/root/hope/profile/list;/root/mbsb/profile/list;/root/red/profile/list;/root/kodimas/profile/list;/root/kgoldaffi/profile/list;/root/koponas/profile/list;/root/wavpay/profile/list;/root/noor/profile/list;/root/bsn/profile/list;/root/alrajhi/profile/list;/root/posarrahnu/profile/list;/root/waqaf/profile/list;/root/igold/profile/list;/root/kasih/profile/list;');

        $this->app = $app;
        $currentStore = $app->otcuseractivitylogStore();
        $this->sqlRecorder = new sqlrecorder();
        if ($app->getOtcUserActivityLog()) {
            $this->addChild(new otcext6gridhandler($this, $currentStore,1));
        } 
        else {
            $this->addChild(new ext6gridhandler($this,$currentStore,1));
        }
    }

    function onPreQueryListing($params, $sqlHandle, $fields)
    {   
        if ($this->sqlRecorder->hasRecording()) $sqlHandle->andWhere( $this->sqlRecorder );

        // $sqlHandle->searchview();

        return array($params, $sqlHandle, $fields);
    }

    
}
