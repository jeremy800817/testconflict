<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\object;

use Snap\object\MyKYCImage;

/**
 * Encapsulates the service table on the database
 *
 * This class encapsulates the service table data
 * information
 *
 * Data members:
 * Name             Type            Description
 * @property-read   int             $id                 Table ID
 * @property        string          $name               Account holder name
 * @property        string          $ach_mykadno        Account holder IC
 * @property        int             $image              Front IC image (Base64)
 * @property        DateTime        $uploaded           Date uploaded
 * @property        string          $imagename          front IC image file name
 * @property        string          $partnerid          partner ID
 * @property        int             $imageback          Back IC image (Base64)
 * @property        string          $imagebackname      Back IC image file name
 *
 * @author Azam
 * @version 1.0
 * @created 2020/09/30 5:15 PM
 */
class MyKYCImage extends SnapObject
{
    private $result = null;
    private $images = [];

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
            'ach_mykadno' => null,
            'image' => null,
            'uploaded' => null,
            'imagename' => null,
            'partnerid' => null,
            'imageback' => null,
            'imagebackname' => null,
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
        $this->validateRequiredField($this->members['image'], 'image');
        $this->validateRequiredField($this->members['ach_mykadno'], 'ach_mykadno');

        return true;
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
    public function getFrontImage()
    {
        return $this->getImageById($this->members['image']);
    }

    /**
     * Get the base64 mykad back image stored
     *
     * @return mixed
     */
    public function getBackImage()
    {
        return $this->getImageById($this->members['imageback']);
    }

    /**
     * To check if all images is saved
     *
     * @param  MyKycSubmission $myEkycSubmission
     * @return boolean
     */
    public function hasAllImages()
    {
        return $this->members['image'] && $this->members['imageback'];
    }

    /*
     * Function for retrieving 1 record from db
     */
    public function getSelectedRecordById($id){
        $images = $this->getStore()
            ->getRelatedStore('image')
            ->searchTable()
            ->select()
            ->whereIn('id', $id)
            ->get();

        foreach ($images as $image) {
            $this->images[$image->id] = $image->image;
        }

        $this->images = $images;
        return $this->images;
    }
}
