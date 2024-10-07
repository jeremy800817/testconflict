<?php
////////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2020
//
//////////////////////////////////////////////////////////////////////
namespace Snap\handler;

use Snap\App;
use Snap\object\MyScreeningMatchLog;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Azam(azam@@silverstream.my)
 * @version 1.0
 */
class myamlascanlogHandler extends CompositeHandler
{
    function __construct(App $app)
    {
        parent::__construct('/root/system', 'amlascanlog');

        $this->mapActionToRights('list', 'list;/root/bmmb/profile/list');

        $this->app = $app;
        $this->addChild(new ext6gridhandler($this, $app->myamlascanlogStore(), 1));
    }


    function onPreQueryListing($params, $sqlHandle, $fields)
    {
        $accountHolder = $this->app->myaccountholderStore()->getById($params['accountholderid']);
        $params['accountholder'] = $accountHolder;
        
        $sqlHandle->andWhere(function ($q) use ($params) {
            $q->where('scmaccountholderid', $params['accountholderid']);
            $q->orWhereNull('scmaccountholderid');
            
        });

        $sqlHandle->andWhere('scannedon', '>=',  \Snap\common::convertUserDatetimeToUTC($accountHolder->createdon)->format('Y-m-d H:i:s'));

        return array($params, $sqlHandle, $fields);
    }

    public function onPreListing($objects, $params, $records)
    {        
        $accountHolder = $params['accountholder'];
        $matchedDuringRegistration = $this->app->myscreeningmatchlogStore()->searchView()->select()
            ->where('accountholderid', $params['accountholderid'])
            ->andWhere('amlascanlogid', 0)
            ->one();

        // The first match has amlascanlogid > 0 means initially the account holder pass amla during registration
        if (!$matchedDuringRegistration) {
            $initialScanRecord = [
                'id' => "0",
                'status' => "1",
                'scannedon' => ($accountHolder->createdon instanceof \DateTime) ? $accountHolder->createdon->format("c") : $accountHolder->createdon,
                'createdon' => null,
                'modifiedon' => null,
                'createdby' => null,
                'modifiedby' => null,
                'scmremarks' => 'Initial Scan',
                'scmmatcheddata' => null,
                'scmmatchedon' => null,
                'scmstatus' => null, 
                'scmaccountholderid' => null,
                'sclsourcetype' => null,
            ];
        } else {
            $initialScanRecord = [
                'id' => "0",
                'status' => "1",
                'scannedon' => ($matchedDuringRegistration->matchedon instanceof \DateTime) ? $matchedDuringRegistration->matchedon->format("c") : $matchedDuringRegistration->matchedon,
                'createdon' => null,
                'modifiedon' => null,
                'createdby' => null,
                'modifiedby' => null,
                'scmremarks' => $matchedDuringRegistration->remarks,
                'scmmatcheddata' => $matchedDuringRegistration->matcheddata,
                'scmmatchedon' => ($matchedDuringRegistration->matchedon instanceof \DateTime) ? $matchedDuringRegistration->matchedon->format("c") : $matchedDuringRegistration->matchedon,
                'scmstatus' => $matchedDuringRegistration->status,
                'scmaccountholderid' => $accountHolder->id,
                'sclsourcetype' => $matchedDuringRegistration->sourcetype,
            ];
        }

        array_unshift($records, $initialScanRecord);        
        return $records;
    }
}
