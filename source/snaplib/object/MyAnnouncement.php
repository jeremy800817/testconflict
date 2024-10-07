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
 * Name                 Type                Description
 * @property-read       int                 $id                 ID of the system
 * @property-read       string              $language           Language of this announcement object
 * @property            string              $code               The code
 * @property            string              $title              Title of the announcement
 * @property            string              $content            Content of the announcement
 * @property            enum                $type               The type of announcement
 * @property            int                 $status             The status for this announcement.  (Active / Inactive)
 * @property            DateTime            $displaystarton     Time this announcement start displaying
 * @property            DateTime            $displayendon       Time this announcement end displaying
 * @property            DateTime            $approvedon         Time this announcement approved
 * @property            DateTime            $disabledon         Time this announcement disabled
 * @property            DateTime            $createdon          Time this record is created
 * @property            DateTime            $modifiedon         Time this record is last modified
 * @property            int                 $approvedby         User ID
 * @property            int                 $disabledby         User ID
 * @property            int                 $createdby          User ID
 * @property            int                 $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyAnnouncement extends MyLocalizedObject
{
    const STATUS_INACTIVE  = 0;
    const STATUS_PENDING   = 1;
    const STATUS_APPROVED  = 2;
    const STATUS_QUEUED    = 3;        // Queued to be pushed. Only valid for push announcements
    const STATUS_COMPLETED = 4;        // For completed queued

    const TYPE_PUSH = 'PUSH';
    const TYPE_ANNOUNCEMENT = 'ANNOUNCEMENT';

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
            'code' => null,
            'status' => null,
            'displaystarton' => null,
            'displayendon' => null,
            'approvedon' => null,
            'disabledon' => null,
            'createdon' => null,
            'modifiedon' => null,
            'approvedby' => null,
            'disabledby' => null,
            'createdby' => null,
            'modifiedby' => null,
        );

        $this->localizableMembers = array(
            'title'     => null,
            'content'   => null
        );
    }

    protected function getContentType()
    {
        return MyLocalizedContent::TYPE_ANNOUNCEMENT;
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid()
    {
        $this->validateRequiredField($this->members['type'], 'type');
        $this->validateRequiredField($this->members['code'], 'code');
        $this->validateRequiredField($this->members['displaystarton'], 'displaystarton');
        $this->validateRequiredField($this->members['displayendon'], 'displayendon');
        // $this->validateRequiredField($this->localizableMembers['title'], 'title');
        // $this->validateRequiredField($this->localizableMembers['content'], 'content');

        if ($this->members['displaystarton'] instanceof DateTime) {
            $dateFrom = $this->members['displaystarton'];
        } else {
            $dateFrom = new \DateTime($this->members['displaystarton']);
        }

        if ($this->members['displayendon'] instanceof DateTime) {
            $dateTo = $this->members['displayendon'];
        } else {
            $dateTo = new \DateTime($this->members['displayendon']);
        }

        if ($dateFrom > $dateTo) {
            throw new InputException("Start date cannot be later than end date", InputException::FIELD_ERROR);
        }

        return true;
    }
}
