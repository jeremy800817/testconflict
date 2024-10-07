<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\job;

USe Snap\App;
Use Snap\ICliJob;
// Use Snap\TLogging;
Use Snap\object\OrderQueue;
Use Snap\object\Order;
Use Snap\object\MbbApFund;
Use Snap\object\GoodsReceivedNoteDraft;
/**
 * This class represents a base job class that basically just implements the ICLIJob interface and adds a few utility
 * functions such as logging and putting the app object into its protected variable for use in derived classes.
 * To implement a job, you can either copy from this class a a template or inherited from it.  Both methods will
 * require the implementation of the doJob() and describeOptions() methods.
 *
 * @version 1.0
 * @package  snap.job
 */
class GrnDraftStructureJob extends basejob {

    // php /usr/local/nginx/html/gtp/source/snaplib/cli.php -f /usr/local/nginx/html/gtp/source/snaplib/job/GrnDraftStructureJob.php -c /usr/local/nginx/html/gtp/source/snapapp/config.ini -p "action=constuct"
    // php snaplib/cli.php -f snaplib/job/GrnDraftStructureJob.php -c snapapp/config.ini -p "action=constuct"
    public function doJob($app, $params = array()) {
        
        $pos1 = $app->getConfig()->{'gtp.pos1.partner.id'};
        $pos2 = $app->getConfig()->{'gtp.pos2.partner.id'};
        $pos3 = $app->getConfig()->{'gtp.pos3.partner.id'};
        $pos4 = $app->getConfig()->{'gtp.pos4.partner.id'};
        $partnerids = [$pos1, $pos2, $pos3, $pos4];

        // ensure config has value;
        if (empty($pos3) || empty($pos4)){
            $message = "Unable to process GrnDraftStructureJob, could not get pos/etc partnerId";
            $this->log($message, SNAP_LOG_ERROR);
            echo $message;
            exit;
        }
        
        if ($params['action'] == 'constuct'){
            $this->constuct($app, $partnerids);
        }
        
        
    }

    function describeOptions() {

    }

    private function constuct($app, $partnerids){
        
            $buybacks = $app->buybackStore()->searchTable()->select()->where('partnerid', 'IN', $partnerids)->execute();
        
            foreach ($buybacks as $buyback){

                $grn_order = $app->goodsreceivenoteorderStore()->searchTable()->select()->where('buybackid', $buyback->id)->one();

                $grn_draft = $app->goodsreceivenotedraftStore()->searchTable()->select()->where('id', $grn_order->id)->execute();
                
                $items = json_decode($buyback->items);
                foreach ($items as $item){
                    $details = [
                        "purity" => $item->purity,
                        "weight" => $item->weight,
                    ];
                    // truncate goodreceivedraft table
                    // NOW draft table is BY item basis
                    $goodReceiveDraft = $app->goodsreceivenotedraftStore()->create([
                        "goodreceivednoteorderid" => $grn_order->id,
                        "branchid" => $buyback->branchid,
                        "referenceno" => $buyback->partnerrefno,
                        "product" => $buyback->productid, // string from api
                        "details" => json_encode(
                            $details
                        ),
                        "desc" => $item->desc,
                        "status" => GoodsReceivedNoteDraft::STATUS_ACTIVE,
                        // GoodReceiveNoteDraft::STATUS_COMPLETED => after submit to SAP
                    ]);
                    $goodReceiveDraft = $app->goodsreceivenotedraftStore()->save($goodReceiveDraft);
                }

            }

        
    }
}
