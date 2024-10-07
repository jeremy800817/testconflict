<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2017
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\manager;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IObserver;
Use \Snap\IObservable;
Use \Snap\IObservation;
use Snap\object\MbbApFund;
use Snap\object\Order;

class MbbAnPManager implements IObservable, IObserver
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;
    
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * This method will be called by IObserverable object to notify of changes that has been done to it.
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
        //$this->log($changed .' - '. $state ." - OBSERVATION", SNAP_LOG_DEBUG);
        if($changed instanceof \Snap\manager\SpotOrderManager && preg_match('/[0-9\.]+m$/', $state->target->apiversion) && ($state->target instanceof \Snap\object\Order)) {
            $p2price = $state->otherParams['bookingPriceObject'];
            try{
                if($state->isNewAction() && $state->target->isspot == 1) {
                    $order = $state->target;
                    if ($order->status == \Snap\object\Order::STATUS_PENDING){
                        $type = MbbApFund::TYPE_ORDERCONFIRM;
                        $this->createConfirmAnP($order, $type, $p2price);
                    }
                }
    
                if($state->isCancelAction() && $state->target->isspot == 1) {
                    $order = $state->target;
                    if ($order->cancelprice && $order->status == \Snap\object\Order::STATUS_CANCELLED){
                        $type = MbbApFund::TYPE_ORDERREVERSE;
                        $this->createReversalAnP($order, $type);
                    }
                }
            }catch(\Exception $e){
                // can implement extra func, alert, etc
                // $this->log($e->getMessage(), $e->getCode());
                $this->log($e->getMessage(), SNAP_LOG_ERROR);
            }
            
        }
    }


    public function createConfirmAnP($order, $type, $p2price = null){
        $pricestreamStore = $this->app->PriceStreamStore();
        $p1 = $pricestreamStore->getById($order->pricestreamid);
        if ($p2price){
            $p2 = $p2price;
        }else{
            $p2 = $pricestreamStore->getById($order->bookingpricestreamid);
        }

        $dev = $this->app->getConfig()->{'snap.environtment.development'};
        if (!$p2 && !$dev){
            // send email notify
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s'));  
            $_now = \Snap\common::convertUTCToUserDatetime($now);

            $mailer = $this->app->getMailer();
            $receiverArr = ['gtp.support@silverstream.my'];
            foreach ($receiverArr as $receiver) {
                $receiver = trim($receiver, ' ');
                $mailer->addAddress($receiver);
            }
            $mailer->isHTML();
            $mailer->SMTPDebug = 0; // return debug code off
            $mailer->Subject = 'MBB ANP P2 null - '.$order->orderno;
            $message = 'Order No: '.$order->orderno.'<br>TIME: '.$_now->format('Y-m-d H:i:s');
            
            $mailer->Body = $message;

            $mailer->send();
        }

        $partner = $this->app->partnerStore()->getById($order->partnerid);

        if ($order->type == Order::TYPE_COMPANYSELL){
            $beginprice = $p1->companysellppg;
            $endprice = $p2->companysellppg;
        }
        if ($order->type == Order::TYPE_COMPANYBUY){
            $beginprice = $p1->companybuyppg;
            $endprice = $p2->companybuyppg;
        }
        if (!$endprice && !$beginprice){
            throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        }

        $beginprice = $partner->calculator()->round($beginprice);
        $endprice = $partner->calculator()->round($endprice);

        $p1buy = $partner->calculator()->round($p1->companybuyppg);
        $p1sell = $partner->calculator()->round($p1->companysellppg);
        $p2buy = $partner->calculator()->round($p2->companybuyppg);
        $p2sell = $partner->calculator()->round($p2->companysellppg);
        // $p3buy = $partner->calculator()->round($p3->companybuyppg);
        // $p3sell = $partner->calculator()->round($p3->companysellppg);

        $calculator = $partner->calculator();
        $amountppg = $this->poolCalculation($beginprice, $endprice, $order, $calculator, $type);
        // echo $amountppg.'<br><br>';
        // echo $beginprice.'<br><br>';
        // echo $endprice.'<br><br>';
        // if amount == 0, still insert, happen on reversal only
        // if ($amount == 0){
        //     $this->log("AnPFund - ({$order->orderno}) - anp Amount 0, NO insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        //     return true;
        // }
        
        $amount = $calculator->multiply($order->xau, $amountppg);
        $apfund = $this->app->mbbapfundStore()->create([
            'partnerid' => $order->partnerid,
            'operationtype' => $type,
            'orderid' => $order->id,
            
            'p1pricestreamid' => $p1->id,
            'p2pricestreamid' => $p2->id,
            // 'p3pricestreamid' => $p3->id,
            'p1buyprice' => $p1buy,
            'p1sellprice' => $p1sell,
            'p2buyprice' => $p2buy,
            'p2sellprice' => $p2sell,
            // 'p3buyprice' => $p3buy,
            // 'p3sellprice' => $p3sell,
            'p1priceon' => $p1->createdon,
            'p2priceon' => $p2->createdon,
            
            'beginprice' => $beginprice,
            'beginpriceid' => $order->pricestreamid,
            'endprice' => $endprice,
            'endpriceid' => $order->bookingpricestreamid,
            'amountppg' => $amountppg, 
            'amount' => $amount,
            'status' => MbbApFund::STATUS_ACTIVE
        ]);
        $saveAPFund = $this->app->mbbapfundStore()->save($apfund);
        if ($saveAPFund){
            $this->log("AnPFund - ({$order->orderno}) - insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_DEBUG);
        }else{
            throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        }
    }
    public function createReversalAnP($order, $type){
        $pricestreamStore = $this->app->PriceStreamStore();
        $p1 = $pricestreamStore->getById($order->pricestreamid);
        $p2 = $pricestreamStore->getById($order->bookingpricestreamid);
        $p3 = $pricestreamStore->getById($order->cancelpricestreamid);

        $partner = $this->app->partnerStore()->getById($order->partnerid);

        if ($order->type == Order::TYPE_COMPANYSELL){
            $beginprice = $p1->companysellppg;
            $endprice = $p3->companybuyppg;
        }
        if ($order->type == Order::TYPE_COMPANYBUY){
            $beginprice = $p1->companybuyppg;
            $endprice = $p3->companysellppg;
        }
        if (!$endprice && !$beginprice){
            throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        }

        $beginprice = $partner->calculator()->round($beginprice);
        $endprice = $partner->calculator()->round($endprice);

        $p1buy = $partner->calculator()->round($p1->companybuyppg);
        $p1sell = $partner->calculator()->round($p1->companysellppg);
        $p2buy = $partner->calculator()->round($p2->companybuyppg);
        $p2sell = $partner->calculator()->round($p2->companysellppg);
        $p3buy = $partner->calculator()->round($p3->companybuyppg);
        $p3sell = $partner->calculator()->round($p3->companysellppg);

        $calculator = $partner->calculator();
        $amountppg = $this->poolCalculation($beginprice, $endprice, $order, $calculator, $type);
        // if amount == 0, still insert, happen on reversal only
        // if ($amount == 0){
        //     $this->log("AnPFund - ({$order->orderno}) - anp Amount 0, NO insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        //     return true;
        // }
        $amount = $partner->calculator()->multiply($order->xau, $amountppg);
        $apfund = $this->app->mbbapfundStore()->create([
            'partnerid' => $order->partnerid,
            'operationtype' => $type,
            'orderid' => $order->id,
            
            'p1pricestreamid' => $p1->id,
            'p2pricestreamid' => $p2->id,
            'p3pricestreamid' => $p3->id,
            'p1buyprice' => $p1buy,
            'p1sellprice' => $p1sell,
            'p2buyprice' => $p2buy,
            'p2sellprice' => $p2sell,
            'p3buyprice' => $p3buy,
            'p3sellprice' => $p3sell,

            'p1priceon' => $p1->createdon,
            'p2priceon' => $p2->createdon,
            'p3priceon' => $p3->createdon,

            'beginprice' => $beginprice,
            'beginpriceid' => $order->pricestreamid,
            'endprice' => $endprice,
            'endpriceid' => $order->cancelpricestreamid,
            'amountppg' => $amountppg, 
            'amount' => $amount,
            'status' => MbbApFund::STATUS_ACTIVE
        ]);
        $saveAPFund = $this->app->mbbapfundStore()->save($apfund);
        if ($saveAPFund){
            $this->log("AnPFund - ({$order->orderno}) - insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_DEBUG);
        }else{
            throw new \Exception("AnPFund - ({$order->orderno}) - FAILED insert AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        }
    }

    public function poolCalculation($price1, $price2, $order, $calculator, $type = null){
        if (MbbApFund::TYPE_ORDERCONFIRM == $type){
            if ($order->type == Order::TYPE_COMPANYSELL){
                return $calculator->minus($price1, $price2);
            }
            if ($order->type == Order::TYPE_COMPANYBUY){
                return $calculator->minus($price2, $price1);
            }
        }
        if (MbbApFund::TYPE_ORDERREVERSE == $type){
            if ($order->type == Order::TYPE_COMPANYSELL){
                return $calculator->minus($price2, $price1);
            }
            if ($order->type == Order::TYPE_COMPANYBUY){
                return $calculator->minus($price1, $price2);
            }
            // return $price2 - $price1;
        }
        return false;
    }

    public function updateAnP($order, $type){
        $partner = $this->app->partnerStore()->getById($order->partnerid);
        $calculator = $partner->calculator();

        $apfund = $this->app->mbbapfundStore()->searchTable()->select()->where('orderid', $order->id)->andWhere('operationtype', $type)->one();

        $amountppg = $this->poolCalculation($apfund->beginprice, $apfund->endprice, $order, $calculator, $apfund->operationtype);
        $amount = $partner->calculator()->multiply($order->xau, $amountppg);

        $apfund->amountppg = $amountppg;
        $apfund->amount = $amount;
        $saveAPFund = $this->app->mbbapfundStore()->save($apfund);
        if ($saveAPFund){
            $this->log("AnPFund - ({$order->orderno}) - update AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_DEBUG);
        }else{
            throw new \Exception("AnPFund - ({$order->orderno}) - FAILED update AP fund of ".$type." now is:" . gmdate('Y-m-d H:i:s') . " - order-createdon:" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
        }
    }

    // 01-March-2021 => patch p1,p2,p3 price timestamp into previous table.
    public function patchp1p2p3timestamp($mbbanpfund_id){

        if ($mbbanpfund_id == "ALL"){
            $mbbanpfund_records = $this->app->mbbapfundStore()->searchTable()->select()->execute();
        }else{
            $mbbanpfund_records = $this->app->mbbapfundStore()->getById($mbbanpfund_id);
        }

        $pricestreamStore = $this->app->PriceStreamStore();
        foreach ($mbbanpfund_records as $record){
            // ignore if already had value
            if ($record->p1pricestreamid && $record->p2pricestreamid){
                
                $p1 = $pricestreamStore->getById($record->p1pricestreamid);
                $p2 = $pricestreamStore->getById($record->p2pricestreamid);

                $record->p1priceon = $p1->createdon;
                $record->p2priceon = $p2->createdon;

            }
            if ($record->p3pricestreamid){

                $p3 = $pricestreamStore->getById($record->p3pricestreamid);

                $record->p3priceon = $p3->createdon;

            }

            $saveAPFund = $this->app->mbbapfundStore()->save($record);
            if ($saveAPFund){
                $this->log("AnPFund - ({$order->orderno}) - update {patchp1p2p3timestamp} AP fund" , SNAP_LOG_DEBUG);
            }else{
                throw new \Exception("AnPFund - ({$order->orderno}) - FAILED update {patchp1p2p3timestamp} AP fund" . $order->createdon->getTimeStamp(), SNAP_LOG_ERROR);
            }
        }
    }


    public function moveConfirmPriceToBookingPrice($mbb_partner_id, $endDate){
        // $endDate format - sql data format

        // truncate table before execute
        $mbbanpfund_records = $this->app->mbbapfundStore()->searchTable()->select()->execute();
        if ($mbbanpfund_records){
            throw new \Exception("FAILED - Must be Truncate before this process");
        }

        $orders = $this->app->orderStore()->searchTable()->select()->where('partnerid', $mbb_partner_id)->andWhere('createdon', '<=', $endDate)->execute();

        foreach ($orders as $order){
            if ($order->confirmprice){
                $order->bookingpricestreamid = $order->confirmpricestreamid;
                $order->bookingprice = $order->confirmprice;
                $order->bookingon = $order->confirmon;
    
                $save = $this->app->orderStore()->save($order);
                if (!$save){
                    return false;
                }
            }
        }
        return true;
    }
}
?>