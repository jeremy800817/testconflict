<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////


Namespace Snap\handler;

USe Snap\App;
use Snap\object\Order;
use Snap\InputException;
/**
 *
 * @author Shahanas <shahanas@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class SpotOrderHandler extends CompositeHandler
{
    public function __construct(App $app)
    {
        parent::__construct('/root/trading', 'order');
        $this->mapActionToRights("list", "list");
        $this->mapActionToRights("add", "add");
        $this->mapActionToRights("edit", "edit");
        $this->mapActionToRights("delete", "delete");
        $this->mapActionToRights("freeze", "freeze");
        $this->mapActionToRights("unfreeze", "unfreeze");
        $this->mapActionToRights("isunique", "add");
        $this->mapActionToRights("viewdetail", "viewprofile");
        $this->mapActionToRights("fillform", "edit");
        $this->mapActionToRights("fillform", "add");

        $this->mapActionToRights("makeOrder", "add");
        $this->app = $app;
        $currentStore = $app->orderStore();  
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
    function fillform( $app, $params) {		
    }
    function makeOrder($app, $params){   
        if($this->app->hasPermission('/root/trading/order/add')){             
            $partnerid=$this->app->getUserSession()->getUser()->partnerid;
            $partner = $app->partnerStore()->getById($partnerid);  
            $product=$this->app->productStore()->getById($params['productitem']);           
            $spotordermanager=$this->app->spotorderManager(); 
            //$hash = hash('sha256', $partnerid+time());    
            $partnerrefno=$partnerid+time();  
            $apiversion="MANUAL"; 
            $transactiontype=($params['sellorbuy']=='sell')?Order::TYPE_COMPANYSELL:Order::TYPE_COMPANYBUY;         
            $uuid=$params['uuid'];
            $futureorderrefno=NULL;
            $ordertype=($params['amount'] != '' && $params['amount'] != 'null') ? 'amount' : (($params['weight'] != '' && $params['weight'] != 'null') ? 'weight' : NULL);
            $ordervalue=($ordertype=='amount')?$params['amount']:(($ordertype=='weight')?$params['weight']:NULL);
            $lockedinprice=$params['sellorbuy']=='sell'?$params['sellprice']:($params['sellorbuy']=='buy'?$params['buyprice']:'');
            $ordertotal="";
            $notifyurl="";
            $reference=NULL;
            $timestamp=date('Y-m-d H:i:s');        
            try{
                $return= $spotordermanager->bookOrder($partner, $apiversion, $partnerrefno, $transactiontype, $product, $uuid, $futureorderrefno, $ordertype, $ordervalue, $lockedinprice, $ordertotal, $notifyurl, $reference, $timestamp);
                echo json_encode(['success' => true]);
            }catch(\Exception $e){
                echo json_encode(['success' => false, 'field' => '', 'errorMessage' => $e->getMessage()]);
            }    
        }else{
            echo json_encode(['success' => false, 'field' => '', 'errorMessage' => 'No Permission']);
        }
    }

    
}


?>