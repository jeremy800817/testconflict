<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use DateTime;
use Snap\InputException;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type        Description
 * @property-read   int         $id                 ID of the system
 * @property        string      $name               The name of this AnnouncementTheme
 * @property        string      $template           The html template for this AnnouncementTheme
 * @property        int         $rank               The rank for this AnnouncementTheme.  (Active / Inactive)
 * @property        int         $status             The status for this AnnouncementTheme.  (Active / Inactive)
 * @property        DateTime    $displaystarton     Time this AnnouncementTheme start displaying
 * @property        DateTime    $displayendon       Time this AnnouncementTheme end displaying
 * @property        DateTime    $validfrom          Time this AnnouncementTheme start valid
 * @property        DateTime    $validto            Time this AnnouncementTheme end valid
 * @property        DateTime    $createdon          Time this record is created
 * @property        DateTime    $modifiedon         Time this record is last modified
 * @property        int         $createdby          User ID
 * @property        int         $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyAnnouncementTheme extends SnapObject
{
    
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
            'name' => null,
            'template' => null,
            'rank' => null,
            'status' => null,
            'displaystarton' => null,
            'displayendon' => null,
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
        $this->validateRequiredField($this->members['name'], 'name');
        $this->validateRequiredField($this->members['template'], 'template');
        $this->validateRequiredField($this->members['rank'], 'rank');
        $this->validateRequiredField($this->members['displaystarton'], 'displaystarton');
        $this->validateRequiredField($this->members['displayendon'], 'displayendon');
        $this->validateRequiredField($this->members['validfrom'], 'validfrom');
        $this->validateRequiredField($this->members['validto'], 'validto');

        $this->validateDateTime($this->members['displaystarton'], $this->members['displayendon']);
        $this->validateDateTime($this->members['validfrom'], $this->members['validto']);

        return true;
    }

    private function validateDateTime($startDate, $endDate)
    {
        if ($startDate instanceof DateTime) {
            $dateFrom = $startDate;
        } else {
            $dateFrom = new \DateTime($startDate);
        }

        if ($endDate instanceof DateTime) {
            $dateTo = $endDate;
        } else {
            $dateTo = new \DateTime($endDate);
        }

        if ($dateFrom > $dateTo) {
            throw new InputException("Start date cannot be later than end date", InputException::FIELD_ERROR);
        }
    }
}
