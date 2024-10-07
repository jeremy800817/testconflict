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

class UpdateMyAccountHolderByCasaApiJob extends basejob {
    public function doJob($app, $params = array()) {
        
        echo "start job : ". date('Y-m-d H:i:s', time()) . "\n";
        
        $result = $app->mygtpaccountManager()->updateMyAccountHolderByCasaApi($params);
        
        echo "result : " . $result . "\n";
        
        echo "end job : ". date('Y-m-d H:i:s', time()) . "\n";
    }

    function describeOptions() {
        return [];
    }
}
?>