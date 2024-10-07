<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
Use \Snap\object\Order;
Use \Snap\object\Partner;
Use \Snap\object\Product;
Use \Snap\object\Logistic;
Use \Snap\object\Buyback;
Use \Snap\object\PriceValidation;

class EventTestManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        
    }
    
    public function call(){
        $this->sendNotification();
    }

    public function sendNotification(){
        $order = $this->app->orderStore()->getById(1);
        $oldStatus = 0;

        $observation = new \Snap\IObservation(
            $order, 
            \Snap\IObservation::ACTION_NEW, 
            $oldStatus, 
            []);
        $this->notify($observation);
    }
}  
?>