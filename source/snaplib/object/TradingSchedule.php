<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;
Use Snap\InputException;
Use Snap\IEntity;

/**
 * TradingSchedule Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property        int                 $categoryid         ID of trading category in TAG table
 * @property        \DateTime           $startat            Time of schedule business start
 * @property        \DateTime           $endat              TIme of schedule business end
 * @property        \DateTime           $createdon          Time this record created
 * @property        int                 $createdby          User ID
 * @property        \DateTime           $modifiedon         Time this record last modified
 * @property        int                 $modifiedby         User ID
 * @property        int                 $status             status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class TradingSchedule extends SnapObject
{
    const TYPE_DAILY = 'DAILY';  //Regular trading schedule
    const TYPE_WEEKDAYS = 'WEEKDAYS';  //Regular trading schedule
    const TYPE_WEEKENDS = 'WEEKENDS';  //Regular trading schedule
    const TYPE_STOP = 'STOP';

    private static $now = null;
    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'categoryid' => null,
            'type' => null,
            'startat' => null,
            'endat' => null,
            'type' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null
        ];
        $this->viewMembers = [
            'categoryname' => null,
            'categorycode' => null,
            'createdbyname' => null,
            'modifiedbyname' => null,
        ];
    }

    /*
        This method to get the listing of Trading Schedule's Type 
    */
    public function getType() {

		$typeArr = [];
		$lists = [
			self::TYPE_DAILY,
			self::TYPE_WEEKDAYS,
			self::TYPE_WEEKENDS,
			self::TYPE_STOP
		];

		foreach ($lists as $key => $value) {
			$typeArr[] = (object)array("id" => $value, "code" => ucfirst(strtolower($value)));
		}

		return $typeArr;
	}
    /**
     * A validation function where to check mandatory fields
     *
     * @internal
     * @param $value
     * @param null|string $message
     * @param null|string $key
     * @throws InputException
     */
    private function validateMandatoryField($value, ?string $message, ?string $key): void
    {
        if (empty($value)) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        $this->validateMandatoryField($this->members['categoryid'], 'categoryid is mandatory', 'categoryid');
        $this->validateMandatoryField($this->members['type'], 'type is mandatory', 'type');
        $this->validateMandatoryField($this->members['startat'], 'startat is mandatory', 'startat');
        $this->validateMandatoryField($this->members['endat'], 'endat is mandatory', 'endat');

        /**
        //category id id is mandatory
        if(empty($this->members['categoryid']) || $this->members['categoryid'] == 0) {
            throw new InputException(gettext('The category ID field is mandatory'), InputException::FIELD_ERROR, 'categoryid');}
        //start time is mandatory
        if(empty($this->members['startat']) || $this->members['startat'] == 0) {
            throw new InputException(gettext('The start at field is mandatory'), InputException::FIELD_ERROR, 'startat');}
        //end time is mandatory
        if(empty($this->members['endat']) || $this->members['endat'] == 0) {
            throw new InputException(gettext('The end at field is mandatory'), InputException::FIELD_ERROR, 'endat');}
        */
        return true;
    }

    public function canTradeNow($now = null) : bool
    {
        if(! $now) {
            if(! self::$now) {
                self::$now = \Snap\Common::convertUTCToUserDatetime(new \DateTime(gmdate("Y-m-d\TH:i:s\Z", time())));
            }
            $now = self::$now;
        }
        $this->log(sprintf("%s(%d) type %s start @ %s, ends @ %s and now is %s",
                            __METHOD__, $this->members['id'],
                            $this->members['type'],
                            $this->members['startat']->format('Y-m-d H:i:s'),
                            $this->members['endat']->format('Y-m-d H:i:s'),
                            $now->format('Y-m-d H:i:s')), SNAP_LOG_DEBUG);
        $dayOfWeek = $now->format('N');
        if(self::TYPE_DAILY == $this->members['type'] || 
           (self::TYPE_WEEKDAYS == $this->members['type'] && 5 >= $dayOfWeek) ||
           (self::TYPE_WEEKENDS == $this->members['type'] && 6 <= $dayOfWeek)) {
            return ($this->members['startat']->format('His') <= $now->format('His') && 
                    $this->members['endat']->format('His') >= $now->format('His'));
        } elseif(self::TYPE_STOP == $this->members['type']) {
            if ($this->members['startat']->format('Ymd') == $now->format('Ymd')){
                return ! ($this->members['startat'] <= $now && $this->members['endat'] >= $now);
            }else{
                return true;
            }
        }
        //Default will be can not do trading.
        return false;
    }
}
?>