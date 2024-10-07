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
Use \Snap\object\VaultItem;
Use \Snap\object\VaultLocation;
Use \Snap\object\Partner;
Use \Snap\object\Documents;

class DocumentsManager implements IObservable, IObserver
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

    public function createPendingDocuments($entity, $type, $transaction){
        if (!in_array($transaction, ['Logistic','VaultItem'])){
            return false;
        }

        $transactionid = $entity->id;
        $latestDocuments = $this->app->DocumentsStore()->searchTable()->select()->where('type', $type)->orderby('id', 'DESC')->one();
        if (!$latestDocuments){
            $documentNo = 1;
            // init seq number;
        }else{
            $documentNo = $latestDocuments->increment();
        }
        $documentNo = $this->formatTypeNumber($documentNo, $type);
        
        $newDocuments = $this->app->DocumentsStore()->create([
            'type' => $type,
            'typenumber' => null,
            'transactiontype' => $transaction,
            'transactionid' => $transactionid,
            'status' => Documents::STATUS_PENDING,
        ]);

        $save = $this->app->DocumentsStore()->save($newDocuments);
        return $save;
    }

    // createDocuments View, confirm, selected transaction(s) to Single document
    public function createDocuments($entities, $type, $transaction, $scheduledate = null){
        if (!in_array($transaction, ['Logistic','VaultItem'])){
            return false;
        }

        // $transactionid = $entity->id;
        $latestDocuments = $this->app->DocumentsStore()->searchTable()->select()->where('type', $type)->orderby('id', 'desc')
        ->andWhere('status', Documents::STATUS_ACTIVE)
        ->one();
        // print_r($latestDocuments);
        if (!$latestDocuments){
            $documentNo = 1;
            // init seq number;
        }else{
            $documentNo = $latestDocuments->increment();
        }
        $documentNo = $this->formatTypeNumber($documentNo, $type);
        foreach ($entities as $entity){
            $entity->typenumber = $documentNo;
            $entity->status = Documents::STATUS_ACTIVE;
            // If have Scheduledate, do scheduledate
            if($scheduledate){
                $scheduledate = new \DateTime($scheduledate);
                $scheduledate = \Snap\common::convertUserDatetimeToUTC($scheduledate);
                $entity->scheduledon = $scheduledate->format('Y-m-d H:i:s');
            }
            $save = $this->app->DocumentsStore()->save($entity);
            // $newDocuments = $this->app->DocumentsStore()->create([
            //     'type' => $type,
            //     'typenumber' => $documentNo,
            //     'transactiontype' => $transaction,
            //     'transactionid' => $transactionid,
            //     'status' => Documents::STATUS_ACTIVE,
            // ]);
            
        }
        // $save = $this->app->DocumentsStore()->save($newDocuments);
        return $save;
    }

     // Save date of print for document
     public function savePrintDate($documents){
        if (!$documents){
            return false;
        }


        foreach ($documents as $document){
            $now = new \DateTime(gmdate('Y-m-d\TH:i:s\Z'));
            $now = new \DateTime("now", $this->app->getUserTimezone());
            $document->printon = $now;
                
            $save = $this->app->DocumentsStore()->save($document);
            
        }
      
            // $newDocuments = $this->app->DocumentsStore()->create([
            //     'type' => $type,
            //     'typenumber' => $documentNo,
            //     'transactiontype' => $transaction,
            //     'transactionid' => $transactionid,
            //     'status' => Documents::STATUS_ACTIVE,
            // ]);
        // $save = $this->app->DocumentsStore()->save($newDocuments);
        return $save;
    }

    public function formatTypeNumber($number, $type){
        
        if ($type == 'ConsignmentNote'){
            $prefex = 'CN';
        }
        if ($type == 'TransferNote'){
            $prefex = 'TN';
        }
        if ($type == 'DeliveryNote'){
            $prefex = 'DN';
        }
        
        $nextSequence = strtoupper(sprintf("%s%05d", $prefex, $number));
        return $nextSequence;
    }

    // handler createDocuments View, get data
    public function getPendingDocuments($type){
        $documents = $this->app->DocumentsStore()->searchTable()->select()
        ->where('status', Documents::STATUS_PENDING)
        ->andWhere('type', $type)
        ->execute();


        $lists = [];
        foreach ($documents as $document){
            if ($document->type == 'TransferNote'){
                $list = $this->app->vaultItemStore()->getById($document->transactionid);
                if($list) {
                    $lists[] = $list->toArray();
                }
            }
            // ..
        }
        return $lists;
    }

}