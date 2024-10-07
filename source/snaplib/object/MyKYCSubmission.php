<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\object\MyImage;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type            Description
 * @property-read   int             $id                 ID of the system
 * @property        string          $mykadfrontimageid  Front MYKadImage
 * @property        string          $mykadbackimageid   Back MYKadImage
 * @property        string          $faceimageid        FaceImage / Selfie
 * @property        enum            $documenttype       Type of document submitted in replace of mykad e.g. MyTentera
 * @property        string          $remarks            Remarks for this submission
 * @property        string          $journeyid          Journey id used for this subsmission
 * @property        int             $accountholderid    Account holder id submitting this
 * @property        int             $status             The status of this KYCSubmission
 * @property        DateTime        $lastjourneyidon    Time this record journey id created
 * @property        DateTime        $submittedon        Time this record is submitted
 * @property        DateTime        $createdon          Time this record is created
 * @property        DateTime        $modifiedon         Time this record is last modified
 * @property        int             $createdby          User ID
 * @property        int             $modifiedby         User ID
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyKYCSubmission extends SnapObject
{
    private $result = null;
    private $images = [];

    const STATUS_INCOMPLETE         = 4;    // Incomplete submission, to be reused later
    const STATUS_PENDING_SUBMISSION = 3;    // Completed submission, but not sent to e-KYC Provider yet
    const STATUS_PENDING_RESULT     = 2;    // Submitted to e-KYC provider, but not yet processed for results
    const STATUS_COMPLETE           = 1;    // Processed for result already

    const DOC_TYPE_MYTENTERA = 'MYTENTERA';
    const DOC_TYPE_MYKAD_OLD = 'MYKAD_OLD';
    const DOC_TYPE_MYKAD     = 'MYKAD';

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
            'mykadfrontimageid' => null,
            'mykadbackimageid' => null,
            'faceimageid' => null,
            'doctype' => null,
            'remarks' => null,
            'journeyid' => null,
            'accountholderid' => null,
            'status' => null,
            'lastjourneyidon' => null,
            'submittedon' => null,
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
        $this->validateRequiredField($this->members['accountholderid'], 'accountholderid');
        $this->validateRequiredField($this->members['status'], 'status');

        return true;
    }

    /**
     * Get the result for this submission
     *
     * @return \Snap\object\MyKYCResult
     */
    public function getResult()
    {
        $this->result = $this->getStore()->getRelatedStore('result')->getByField('submissionid', $this->members['id']);
        return $this->result;
    }

    public function getStatus()
    {
        return $this->members['status'];
    }

    public function getStatusString()
    {
        switch ($this->members['status']) {
            case self::STATUS_INCOMPLETE:
                return 'Pending';
                break;
            case self::STATUS_PENDING_SUBMISSION:
                return 'Pending Submission';
                break;
            case self::STATUS_PENDING_RESULT:
                return 'Pending Result';
                break;
            case self::STATUS_COMPLETE:
                return 'Complete';
                break;
            default:
                return 'Incomplete';
        }
    }

    /**
     * Load all images related to this submission from the database
     *
     * @return array
     */
    protected function getAllImages()
    {
        $imageIds = array_filter([
            $this->members['mykadfrontimageid'],
            $this->members['mykadbackimageid'],
            $this->members['faceimageid'],
        ]);

        if (!count($imageIds)) {
            return [];
        }

        $images = $this->getStore()
            ->getRelatedStore('image')
            ->searchTable()
            ->select()
            ->whereIn('id', $imageIds)
            ->get();

        foreach ($images as $image) {
            $this->images[$image->id] = $image->image;
        }

        return $this->images;
    }

    /**
     * Get the image from the array using the key
     *
     * @param  int $id
     * @return mixed
     */
    protected function getImageById($id)
    {
        if (isset($this->images[$id])) {
            return $this->images[$id];
        }

        $images = $this->getAllImages();
        return $images[$id] ?? null;
    }

    /**
     * Get the base64 mykad front image stored
     *
     * @return mixed
     */
    public function getMyKadFrontImage()
    {
        return $this->getImageById($this->members['mykadfrontimageid']);
    }

    /**
     * Get the base64 mykad back image stored
     *
     * @return mixed
     */
    public function getMyKadBackImage()
    {
        return $this->getImageById($this->members['mykadbackimageid']);
    }

    /**
     * Get the base64 face image stored
     *
     * @return mixed
     */
    public function getFaceImage()
    {
        return $this->getImageById($this->members['faceimageid']);
    }

    /**
     * Save the base 64 image into the database and return the id of the image
     *
     * @param  string $imageBase64
     * @return int
     */
    public function saveImage(string $imageBase64)
    {
        $imageStore = $this->getStore()->getRelatedStore('image');
        $image = $imageStore->create([
            'image' => $imageBase64,
            'type' => MyImage::TYPE_BASE64,
            'status' => MyImage::STATUS_ACTIVE,
        ]);

        $image = $imageStore->save($image);

        return $image->id;
    }

    /**
     * To check if all images is saved
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return boolean
     */
    public function hasAllImages()
    {
        return $this->members['mykadfrontimageid'] && $this->members['mykadbackimageid'] && $this->members['faceimageid'];
    }
}
