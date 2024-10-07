<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\TLogging;
use Snap\TObservable;
use Snap\IObservable;
use Snap\object\MyFee;
use Snap\object\Partner;

/**
 * This class handles account holder management
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 15-Dec-2020
 */
class MyGtpFeeManager implements IObservable
{
    use TLogging;
    use TObservable;

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Returns an array of applicable fees with the given amount and fee type
     * 
     * @param int    $partnerid 
     * @param float  $amount 
     * @param string $feeType 
     * @return MyFee[] 
     */
    public function findApplicableFees($partnerid, $amount, $feeType)
    {
        $now = new \DateTime();

        $fees = $this->app->myfeeStore()->searchTable()->select()
                    ->where('type', $feeType)
                    ->andWhere('partnerid', $partnerid)
                    ->andWhere('minamount', '<=', $amount)
                    ->andWhere('maxamount', '>=', $amount)
                    ->andWhere('validfrom', '<', $now->format('Y-m-d H:i:s'))
                    ->andWhere('validto', '>=', $now->format('Y-m-d H:i:s'))
                    ->andWhere('status', MyFee::STATUS_ACTIVE)
                    ->orderBy('id', 'ASC')
                    ->execute();

        $this->logDebug(__METHOD__."($partnerid, $amount, $feeType): ". count($fees). " fees found.", SNAP_LOG_DEBUG);
        if (0 < count($fees)) {
            $this->logDebug(__METHOD__."Fee IDs: ".implode(',', array_map(function($fee){return $fee->id;}, $fees)). ".", SNAP_LOG_DEBUG);
        }
        return $fees ?? [];
    }

    /**
     * Update the fee using id
     *
     * @param int $id
     * @param strig $type
     * @param float $value
     * @param string $calculationtype
     * @param string $mode
     * @param float $minamount
     * @param float $maxamount
     * @param DateTime $validfrom
     * @param DateTime $validto
     * @param int $status
     * @return MyFee
     */
    public function updateFee($id, $type, $value, $calculationtype, $mode, $minamount, $maxamount, $validfrom, $validto, $status)
    {   
        $fee = $this->app->myfeeStore()->getById($id);
        $fee->id = $id;
        $fee->type = $type;
        $fee->value = $value;
        $fee->calculationtype = $calculationtype;
        $fee->mode = $mode;
        $fee->minamount = $minamount;
        $fee->maxamount = $maxamount;
        $fee->validfrom = $validfrom;
        $fee->validto = $validto;
        $fee->status = $status;

        return $this->app->myfeeStore()->save($fee);
    }

    /**
     * Add new fee for the partner
     *
     * @param Partner $partner
     * @param string $type
     * @param float $value
     * @param string $calculationtype
     * @param string $mode
     * @param float $minamount
     * @param float $maxamount
     * @param DateTime $validfrom
     * @param DateTime $validto
     * @param int $status
     * @return MyFee
     */
    public function addFee(Partner $partner, $type, $value, $calculationtype, $mode, $minamount, $maxamount, $validfrom, $validto, $status)
    {
        $fee = $this->app->myfeeStore()->create([
            'partnerid' => $partner->id,
            'type' => $type,
            'value' => $value,
            'calculationtype' => $calculationtype,
            'mode' => $mode,
            'minamount' => $minamount,
            'maxamount' => $maxamount,
            'validfrom' => $validfrom,
            'validto' => $validto,
            'status' => $status
        ]);

        return $this->app->myfeeStore()->save($fee);
    }

    /**
     * Remove the fee
     *
     * @param MyFee $fee
     * @return boolean
     */
    public function removeFee(MyFee $fee)
    {
        return $this->app->myfeeStore()->delete($fee);
    }

    /**
     * Get all the fees for the partner
     *
     * @param Partner $partner
     * @return MyFee[]
     */
    public function getPartnerFees(Partner $partner)
    {
        return $this->app->myfeeStore()->searchTable()->select()->where('partnerid', $partner->id)->execute();
    }

    /**
     * Sync by removing all fees that is not in the $ids
     *
     * @param Partner $partner
     * @param array $ids
     * @return void
     */
    public function syncPartnerFees(Partner $partner, array $ids)
    {
        $fees = $this->getPartnerFees($partner);

        foreach ($fees as $fee) {
            if (!in_array($fee->id, $ids)) {
                $this->removeFee($fee);                
            }
        }
    }
}
