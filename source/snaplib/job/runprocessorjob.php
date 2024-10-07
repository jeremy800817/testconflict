<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;

class runprocessorjob extends basejob {
    public function doJob($app, $params = array()) {
        $app->notificationmanager()->runProcessorJob();
    }

    function describeOptions() {
        return [];
    }
}
?>