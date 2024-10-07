<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2023
 * @copyright Silverstream Technology Sdn Bhd. 2023
 */

namespace Snap\manager;

use DateTime;
use Snap\IObservable;
use Snap\TObservable;
use Snap\TLogging;

/**
 * This class handles token management
 *
 * @author Jeremy <jeremy@silverstream.my>
 * @version 1.0
 * @created 03-Jan-2023
 */
class AuditManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function saveOtcUserActivityLog ($module, $action, $activity) {
        $otcUserActivityLogStore = $this->app->otcuseractivitylogStore();
        
        $otcUserActivityLog = $otcUserActivityLogStore->create([
            'usrid' => $this->app->getUsersession()->getUser()->id,
            'module' => $module,
            'action' => $action,
            'activitydetail' => $activity,
            'ip' => $this->app->getRemoteIP(),
            'browser' => $_SERVER['HTTP_USER_AGENT'],
            'activitytime' => $_SERVER['REQUEST_TIME']
        ]);
        
        $otcUserActivityLogStore->save($otcUserActivityLog);
    }
}

