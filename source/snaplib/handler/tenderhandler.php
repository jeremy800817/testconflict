<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\App;
Use Snap\IHandler;
Use Snap\InputException;

class tenderHandler extends CompositeHandler
{
    private $app = null;

    function __construct(App $app) {
		//parent::__construct('/root/developer', 'tag');

		$this->mapActionToRights('fillform', '');
        $this->mapActionToRights("uploadTenderFile", "/root/system/partner");
		$this->app = $app;
		$tagStore = $app->tagfactory();
		$this->addChild(new ext6gridhandler($this, $tagStore, 1));
    }
    
   
    public function uploadTenderFile($app, $params){
        $data = $_FILES['tenderlist'];
		// echo 'a';print_r($data);exit;

        // print_r($params);exit;
        
        $date = $params['fromdate'];
        $goldprice = $params['goldprice'];
        $file = $_FILES['tenderlist'];

        if ($params['preview'] == 1){
            $preview = true;
        }else{
            $preview = false;
        }
        
        $import = $this->app->buybackManager()->readImportTender($date, $goldprice, $file, null, $preview, $params['partnercode']);
        // if ($import){
        //     $return['success'] = true;
        // }else{
        //     $return['success'] = false;
        // }

        return json_encode($import);
        // preview
        // exception
        // success
    }

}