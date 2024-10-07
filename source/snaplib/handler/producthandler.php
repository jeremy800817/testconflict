<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\product;
use Snap\InputException;
/**
 *
 * @author Shahanas <shahanas@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class productHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        //parent::__construct('/root/system', 'product');
        $this->mapActionToRights("list", "/root/system/product/list");
        $this->mapActionToRights("add", "/root/system/product/add");
        $this->mapActionToRights("edit", "/root/system/product/edit");
        $this->mapActionToRights("delete", "/root/system/product/delete");
        $this->mapActionToRights("freeze", "/root/system/product/freeze");
        $this->mapActionToRights("unfreeze", "/root/system/product/unfreeze");
        $this->mapActionToRights("isunique", "/root/system/product/add");
        $this->mapActionToRights("viewdetail", "/root/system/product/viewprofile");
        $this->mapActionToRights("fillform", "/root/system/product/edit");
        $this->mapActionToRights("fillform", "/root/system/product/add");
        $this->mapActionToRights("getProducts", "/all/access");
        
        $this->app = $app;
        $currentStore = $app->productStore();  
        $this->addChild(new ext6gridhandler($this, $currentStore,1));
    }  
  
    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */

    function onPreAddEditCallback($object, $params) {            
        return $object;    
    }   
    
    public function getProducts(){       
        $productlists = $this->app->productStore()->searchTable()->select()->execute();
        $product=array();
        foreach($productlists as $productlist) {        
            $product[]= array( 'id' => $productlist->id, 'name' => $productlist->name);
        }        
        echo json_encode(array('items'=>$product));          
    }
	
 

    
    

   
    
}


?>