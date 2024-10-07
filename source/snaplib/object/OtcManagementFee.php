<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\object;
use Snap\InputException;
use Snap\IEntity;

/**
 * Encapsulates the OtcManagementFee table on the database
 *
 * This class encapsulates the OtcManagementFee table data
 * information
 *
 * Data members:
 * Name			  	Type				Description
 * @property-read 	int 		 		$id 				   			ID of the system
 * @property       	string       		$name      						Name
 * @property       	float      			$avgdailygoldbalancegramfrom	Average daily gold balance in gram from
 * @property       	float     			$avgdailygoldbalancegramto     	Average daily gold balance in gram to
 * @property       	float     			$feepercent     				Managemnt fee in percentage
 * @property       	float     			$feeamount     					Managemnt fee in amount
 * @property       	int     			$period     					Managemnt Period in month
 * @property       	int     			$attempt     					Managemnt Attempt in times
 * @property       	int     			$jobperiod     					Managemnt Job Period in days
 * @property        DateTime            $createdon              		Created Date
 * @property        int                 $createdby						Created By User ID
 * @property        DateTime            $modifiedon						Modified Date
 * @property        int                 $modifiedby						Modified By User ID
 * @property        int                 $status							Status
 *
 * @version 1.0
 */
class OtcManagementFee extends SnapObject {


	const TYPE_SORT = 'avgdailygoldbalancegramfrom';
	
	const STATUS_PENDING_APPROVAL =  2;
	const STATUS_REJECT_APPROVAL =  3;
	
	const ACTION_ADD =  1;
	const ACTION_EDIT =  2;
	const ACTION_DELETE =  3;
	
	/**
	 * This method will initialise the 2 array members of this class with the definition of fields to be used
	 * by the object.  This method will be called in the object's contractor.
	 *
	 * @return None
	 */
	protected function reset()
	{
		$this->members = array(
			'id' => null,
			'name' => null,
			'avgdailygoldbalancegramfrom' => null,
			'avgdailygoldbalancegramto' => null,
			'feepercent' => null,
			'feeamount' => null,
			'period' => null,
			'attempt' => null,
			'jobperiod' => null,
			'starton' => null,
			'endon' => null,
			'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null,
			'parentid' => null,
            'checker' => null,
            'remarks' => null,
            'actionon' => null,
            'requestaction' => null
		);
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
		
        if (empty($value) && '0' != $value) {
            throw new InputException(gettext($message), InputException::FIELD_ERROR, $key);
        }
    }

	/**
	 * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
	 * valid state, the method will return false. Otherwise it will return true.
	 *
	 * @return boolean True if it is a valid object.  False otherwise.
	 */
	public function isValid()
	{
		//Make sure that the price model is unique.
		// Define the query using Hydrahon
		$res = $this->getStore()
			->searchTable()
			->select()
			->where(function($where) {
				$where->orWhere(function($or) {
					$or->where('avgdailygoldbalancegramfrom', '<=', $this->members['avgdailygoldbalancegramto'])
					   ->where('avgdailygoldbalancegramto', '>=', $this->members['avgdailygoldbalancegramfrom'])
					   ->where('status', '=', $this->members['status']);
				})->orWhere(function($or) {
					$or->where('avgdailygoldbalancegramfrom', '<=', $this->members['avgdailygoldbalancegramfrom'])
					   ->where('avgdailygoldbalancegramto', '>=', $this->members['avgdailygoldbalancegramto'])
					   ->where('status', '=', $this->members['status']);
				});
			});

        if($this->members['id']) {
            $res = $res->andWhere('id', '!=', $this->members['id']);
        }
        $data = $res->count();
        if ($data && self::ACTION_DELETE != $this->members['requestaction'] && self::STATUS_REJECT_APPROVAL != $this->members['status'] && self::STATUS_INACTIVE != $this->members['status']) {
            throw new InputException(sprintf(gettext('Unable to add 2 identifical management fee'), $this->members['avgdailygoldbalancegramfrom']), InputException::FIELD_ERROR, '');
        }
		
		$this->validateMandatoryField($this->members['avgdailygoldbalancegramfrom'], 'Min value is mandatory', 'avgdailygoldbalancegramfrom');
		$this->validateMandatoryField($this->members['avgdailygoldbalancegramto'], 'Max value is mandatory', 'avgdailygoldbalancegramto');
		//$this->validateMandatoryField($this->members['feepercent'], 'Management Fee value is empty!', 'feepercent');
		// $this->validateMandatoryField($this->members['status'], 'Status is mandatory', 'status');

		return true;
	}
}

?>
