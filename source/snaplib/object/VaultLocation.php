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
 * VaultLocation Class
 *
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $id                 ID of the table
 * @property        int                 $partnerid          ID of the partner table
 * @property        string              $name               Name of the vault or branch
 * @property        int                 $type               Type of location
 * @property        int                 $minimumlevel       Minimum level of vault
 * @property        int                 $reorderlevel       Level of re-order
 * @property        int                 $defaultlocation    Default location
 * @property        \DateTime           $createdon          Time this record created
 * @property        int                 $createdby          User ID
 * @property        \DateTime           $modifiedon         Time this record is last modified
 * @property        int                 $modifiedby         User ID
 * @property        int                 $status             Api status
 *
 * @author  Calvin <calvin.thien@ace2u.com>
 * @version 1.0
 * @package Snap\object
 */
class VaultLocation extends SnapObject
{
    const TYPE_START = 'Start';  //Starting location - E.g.  Taipan Office
    const TYPE_INTERMIDIATE = 'Intermediate';  //intermediate location, e.g.  ACE rack in G4S
    const TYPE_END = 'End';  //Starting location - Final rack, i.e.  MBB vault / rack space.

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    protected function reset(): void
    {
        $this->members = [
            'id' => null,
            'partnerid' => null,
            'name' => null,
            'type' => null,
            'minimumlevel' => null,
            'reorderlevel' => null,
            'defaultlocation' => null,
            'createdon' => null,
            'createdby' => null,
            'modifiedon' => null,
            'modifiedby' => null,
            'status' => null
        ];
    }

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid(): bool
    {
        //partner id is mandatory
        if(empty($this->members['partnerid']) || 0 == strlen($this->members['partnerid'])) {
            throw new InputException(gettext('The partner ID field is mandatory'), InputException::FIELD_ERROR, 'partnerid');}
        //name is mandatory
        if(empty($this->members['name']) || 0 == strlen($this->members['name'] == 0)) {
            throw new InputException(gettext('The name field is mandatory'), InputException::FIELD_ERROR, 'name');}
        //default location is mandatory
        if(0 == strlen($this->members['defaultlocation'])) {
            throw new InputException(gettext('The default location field is mandatory'), InputException::FIELD_ERROR, 'defaultlocation');}


        return true;
    }

    /**
     * Returns true if the location is final location (E.g.  MBB vault rack)
     * @return boolean [description]
     */
    public function isFinalLocation()
    {
        return self::TYPE_END == $this->type;
    }

    /**
     * Return true if this is an intermediate location
     */
    public function isIntermediateLocation()
    {
        return self::TYPE_INTERMIDIATE == $this->type;
    }

    /**
     * Returns true if this is the starting location (e.g.  Taipan office)
     */
    public function isStartingLocation()
    {
        return self::TYPE_START == $this->type;
    }
}
?>
