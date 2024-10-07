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

class DoExportZipCliJob extends basejob {
   
    function doJob($app, $params = array()) {
        echo "Start \n";
        try{
            $app->setUserSession();
            // $handler = new mxDataHandlerExt;
            // $action = 'exportzip';
            $app->reportingManager()->doExportZip($app, $params);
        } catch(\Exception $e) {
            echo "Exception in the processing caught...." . $e->getMessage() . "\n";
        }
        echo "End \n";
    }
    
    function describeOptions() {
        return [];
    }
}
?>