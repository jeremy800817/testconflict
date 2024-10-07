<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\InputException;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the system
 * @property        enum                $type               The type of fee
 * @property        float               $value              The value of the fee
 * @property        enum                $calculationtype    The calculation type used to calculate the fee
 * @property        float               $minamount          Minimum amount for fee to be applicable
 * @property        float               $maxamount          Maximum amount for fee to be applicable
 * @property        int                 $productid          The id of product
 * @property        int                 $partnerid          The id of partner
 * @property        int                 $status             The status of this fee
 * @property        DateTime            $validfrom          Fee valid from
 * @property        DateTime            $validto            Fee valid to
 * @property        DateTime            $createdon          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $createdby          User ID
 * @property        int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyFee extends SnapObject
{

    const TYPE_STORAGE_FEE      = 'STORAGE';
    const TYPE_DISBURSEMENT_FEE = 'DISBURSEMENT';
    const TYPE_INSURANCE_FEE    = 'INSURANCE';
    const TYPE_HANDLING_FEE     = 'HANDLING';
    const TYPE_DELIVERY_FEE     = 'DELIVERY';
    const TYPE_FPX_FEE          = 'FPX';
    const TYPE_MIN_STORAGE_FEE  = 'MIN_STORAGE';

    const CALCULATION_TYPE_FIXED = 'FIXED';
    const CALCULATION_TYPE_FLOAT = 'FLOAT';

    const MODE_MYR = 'MYR';
    const MODE_XAU = 'XAU';

    const TYPE_MAP = [
        self::TYPE_STORAGE_FEE      => 'Admin & Storage',
        self::TYPE_DISBURSEMENT_FEE => 'Disbursement',
        self::TYPE_INSURANCE_FEE    => 'Insurance',
        self::TYPE_HANDLING_FEE     => 'Handling',
        self::TYPE_DELIVERY_FEE     => 'Delivery',
        self::TYPE_FPX_FEE          => 'FPX',
        self::TYPE_MIN_STORAGE_FEE  => 'Min Storage'
    ];

    /**
     * This method will initialise the array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's constructor.
     *
     * @return void
     */
    protected function reset()
    {
        $this->members = array(
            'id' => null,
            'type' => null,
            'value' => null,
            'calculationtype' => null,
            'mode' => null,
            'minamount' => null,
            'maxamount' => null,
            'partnerid' => null,
            'status' => null,
            'validfrom' => null,
            'validto' => null,
            'createdon' => null,
            'modifiedon' => null,
            'createdby' => null,
            'modifiedby' => null,
        );
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['mode'], 'mode');
        $this->validateRequiredField($this->members['type'], 'type');
        $this->validateRequiredField($this->members['value'], 'value');
        $this->validateRequiredField($this->members['calculationtype'], 'calculationtype');
        $this->validateRequiredField($this->members['partnerid'], 'partnerid');
        $this->validateRequiredField($this->members['validfrom'], 'validfrom');
        $this->validateRequiredField($this->members['validto'], 'validto');
        $this->validateRequiredField($this->members['minamount'], 'minamount');
        $this->validateRequiredField($this->members['maxamount'], 'maxamount');
        $this->validateUniqueType($this->members['type'], 'type');

        if (self::MODE_MYR == $this->members['mode'] && self::TYPE_MIN_STORAGE_FEE != $this->members['type']) {
            throw new \Snap\InputException(
                gettext(sprintf('Mode only available for fee type %s', self::TYPE_MIN_STORAGE_FEE)),
                \Snap\InputException::FIELD_ERROR,
                'mode'
            );
        }

        if (self::TYPE_MIN_STORAGE_FEE == $this->members['type'] && self::MODE_MYR != $this->members['mode']) {
            throw new \Snap\InputException(
                gettext(sprintf('Only %s mode available for fee type %s', self::MODE_MYR, self::TYPE_MIN_STORAGE_FEE)),
                \Snap\InputException::FIELD_ERROR,
                'mode'
            );
        }

        if (self::CALCULATION_TYPE_FLOAT == $this->members['calculationtype'] && self::TYPE_MIN_STORAGE_FEE == $this->members['type']) {
            throw new \Snap\InputException(
                gettext(sprintf('Calculation type not available for fee type %s', self::TYPE_MIN_STORAGE_FEE)),
                \Snap\InputException::FIELD_ERROR,
                'calculationtype'
            );
        }


        return true;
    }

    /**
     * Do the calculation for the fee based on calculation type
     *
     * @param  float $amount
     * @return float
     */
    public function calculate($amount = null)
    {
        if (self::CALCULATION_TYPE_FIXED == $this->members['calculationtype']) {
            return $this->members['value'];
        }

        if (self::CALCULATION_TYPE_FLOAT == $this->members['calculationtype'] && isset($amount)) {
            return $this->members['value'] * $amount;
        }

        throw new InputException('Invalid calculation type or invalid amount', InputException::GENERAL_ERROR);
    }

    public static function getType()
    {
        

        $rClass = new \ReflectionClass(__CLASS__);
        $constants = $rClass->getConstants();
        $lists = [];
        foreach ($constants as $key => $constant) {
            if (false !== strstr($key, "TYPE_" )) {
                $lists[] = $rClass->getConstant($key);
            }
        }

        $categoryArr = [];
        foreach ($lists as $key => $value) {
            $categoryArr[] = (object)array("id" => $value, "code" => self::TYPE_MAP[$value]);
        }

        return $categoryArr;
    }
    private function validateUniqueType($value)
    {
        if (null === $this->members['id']) {
            $id = 0;
        } else {
            $id = $this->members['id'];
        }

        if ($this->members['validfrom'] instanceof \DateTime) {
            $validFrom = $this->members['validfrom'];
        } else {
            $validFrom = (new \DateTime($this->members['validfrom']));
        }

        if ($this->members['validto'] instanceof \DateTime) {
            $validTo = $this->members['validto'];
        } else {
            $validTo = (new \DateTime($this->members['validto']));
        }
        

        $exists = $this->getStore()
            ->searchTable()
            ->select(['type'])
            ->where('type', $value)
            ->where('partnerid', $this->members['partnerid'])
            ->where(function ($q) use ($validFrom, $validTo) {
                $q->where('validto', '>=', $validFrom->format('Y-m-d H:i:s'));
                $q->where('validfrom', '<=', $validTo->format('Y-m-d H:i:s'));
            })
            ->andWhere('id', '!=', $id)
            ->andWhere('status', self::STATUS_ACTIVE)
            ->exists();


        if ($exists) {
            throw new \Exception(sprintf(gettext('The type %s is already in use'), self::TYPE_MAP[$value]), InputException::FIELD_ERROR);
        }
    }
}
