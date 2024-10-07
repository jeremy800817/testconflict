<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2016-2020
 */
Namespace Snap\object;

Use Snap\IEntity;
USe Snap\IEntityStore;
Use Snap\ICacheable;
Use Snap\InputException;
Use \Datetime;

/**
 * This represents the main interface class to a record in storage.
 *
 *
 * @author   Devon Koh <devon@silverstream.my>
 * @version  1.0
 * @package  snap.base
 * @abstract
 */
abstract class SnapObject implements IEntity, ICacheable
{
    Use \Snap\TLogging;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Can the members of this object be updated?
     */
    private $isFinal = 0;

    /**
     * The datastore  that created this object.
     * @var dbdatastore
     */
    private $datastore = null;

    /**
    * Member variables are defined here
    *
    * @var		array
    */
    protected $members = array();

    /**
    * Member variables from views are defined here
    *
    * @var		array
    */
    protected $viewMembers = array();

    /**
     * This method will initialise the 2 array members of this class with the definition of fields to be used
     * by the object.  This method will be called in the object's contractor.
     * @return void
     */
    abstract protected function reset();

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @throws InputException
     * @return boolean True if it is a valid object.  False otherwise.
     */
    abstract public function isValid();

    /**
     * This is the constructor for all snapObject derived classes.
     *
     * **Note: This class is not to be created by directly instantiating it.  All snapObject derieved classes should
     * be instantiated by its relevant datastore.**  The datastore in turn can be accessed from the controller.
     * @see Snap\dbdatastore
     * @see Snap\controller
     *
     * @param IEntityStore $store      The factory that created this object
     * @param Array|null   $properties A key-value pair of array to initialise this object's property
     */
    public function __construct(IEntityStore $store, Array $properties = null)
    {
        $this->datastore = $store;
        $this->reset();
        if ($properties) {
            $this->populate($properties);
        }
        return $this;
    }

    /**
     * Checks if the object is active
     * @return boolean True if the status is active.  False otherwise.
     */
    public function isActive()
    {
        return $this->members['status'] && self::STATUS_ACTIVE;
    }

    /**
     * Locks the object from being edited.
     * @return void
     */
    public function lockFromEdit($throwErrorOnEdit = false)
    {
        $this->isFinal = $throwErrorOnEdit ? 2 : 1;
        return $this;
    }

    /**
     * Checks  if the current object has been locked from being edited.
     * @return boolean True means it has been locked.  False otherwise.
     */
    public function isLockedFromEdit()
    {
        return 0 < $this->isFinal;
    }

    /**
     * Cloning of object and reset the locking of properties to empty.
     * @internal
     */
    public function __clone()
    {
        $this->isFinal = 0;
    }

    /**
     * Returns the datastore that created this object
     *
     * @return Snap\datastore\dbdatastore
     */
    protected function getStore()
    {
        return $this->datastore;
    }

    /**
     * Returns the fields that are available for this object
     * @return array
     */
    public function getFields()
    {
        return array_keys($this->members);
    }

    /**
     * Returns the fields that are available for this object for viewing purposes
     * @return array
     */
    public function getViewFields()
    {
        return array_keys($this->viewMembers);
    }

    /**
     * This method will fill up the fields specified with values.
     * @param array $properties The field value pair that contains data to be populated
     * @return void
     */
    protected function populate($properties)
    {
        if (2 == $this->isFinal) {
            throw new InputException(__CLASS__ . " with id {$this->id} has been locked from edting", InputException::GENERAL_ERROR, '');
        } elseif ($this->isFinal) {
            return;
        }  //skip setting the data
        foreach ($properties as $key => $value) {
            if (array_key_exists($key, $this->members)) {
                $this->members[$key] = $this->mapRawValues($key, $value);
            } elseif (array_key_exists($key, $this->viewMembers)) {
                $this->viewMembers[$key] = $this->mapRawValues($key, $value);
            }
        }
    }

    /**
     * This method is called before a delete operation is done.
     *
     * @return Boolean  True if can continune to delete the object.  False otherwise.
     */
    public function onPredelete()
    {
        return true;
    }

    /**
     * This method is called prior to the data being updated.  This method is intended to be used by
     * the object to make any prior actions before being updated
     *
     * @return Boolean   True if update can continue.  False otherwise.
     */
    public function onPrepareUpdate()
    {
        return true;
    }

    /**
     * This method is used to inform the object that the update has been completed.  The object can
     * perform any further post update actions as required.
     *
     * @param  IEntity $latestCopy The last copy of the object
     * @return void
     */
    public function onCompletedUpdate(IEntity $latestCopy)
    {
        if (get_class($this) == get_class($latestCopy) &&
            0 == $this->members['id'] && 0 != $latestCopy->id) {
            $this->members['id'] = $latestCopy->id;
            $this->members['createdon'] = $latestCopy->createdon;
            $this->members['createdby'] = $latestCopy->createdby;
        }
        $this->members['modifiedon'] = $latestCopy->modifiedon;
        $this->members['modifiedby'] = $latestCopy->modifiedby;
        return true;
    }

    /**
     * This method is called to custom map values of fields to special object types of data.
     *
     * @param  String $field    The name of the field that is provided for data
     * @param  mixed $rawValue The actual raw data
     * @return mixed           The actual data that we would like to set for the field.
     */
    protected function mapRawValues($field, $rawValue)
    {
        if (preg_match('/(on|to|from)$/', $field) &&
            is_string($rawValue) &&
            preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $rawValue) &&
            '0000-00-00 00:00:00' != $rawValue) {
            return new DateTime($rawValue, \Snap\App::getInstance()->getUserTimezone());
        }
        return $rawValue;
    }

    /**
    * Overloaded PHP built-in getter
    *
    * @internal magic method implementation
    *
    * @param mixed $nm
    * @return   mixed
    */
    public function __get($nm)
    {
        if (array_key_exists($nm, $this->members)) {
            return $this->members[$nm];
        }
        //Added by Devon on 2010/5/5 to provide an empty string given if trying to get view members no initialised.
        if (array_key_exists($nm, $this->viewMembers)) {
            return $this->viewMembers[$nm];
        }
        //End Add by Devon on 2010/5/5
        return null;
    }


    /**
    * Overloaded PHP built-in setter
    *
    * @internal magic method implementation
    * @param mixed $nm
    * @param mixed $val
    *
    * @return   mixed
    */
    public function __set($nm, $val)
    {
        //Added by Devon on 2010/4/8 to set any null values into here into an empty string to avoid SQL statements generating NULL for columns.
        if ($val === null) {
            $val = '';
        }
        //End Add by Devon on 2010/4/8
        if (2 == $this->isFinal) {
            throw new InputException(__CLASS__ . " with id {$this->id} has been locked from edting", InputException::GENERAL_ERROR, '');
        }
        if (array_key_exists($nm, $this->members)) {
            if ($nm == 'id'
                || $nm == 'createdon'
                || $nm == 'modifiedon'
                || $nm == 'createdby'
                || $nm == 'modifiedby') {
                return $this;
            }
            if (! $this->isFinal) {
                $this->members[$nm] = $this->mapRawValues($nm, $val);
                return $this;
            }
        }
        //Added by Devon on 2010/5/5 to include view member if it is set explicitly by the user (for query purposes?)
        if (! $this->isFinal && array_key_exists($nm, $this->viewMembers)) {
            $this->members[$nm] = $val;
            return true;
        }
        //End Add by Devon on 2010/5/5

        $this->log("Unable to set the data member $nm from ".get_class($this), SNAP_LOG_WARNING);
        return $this;
    }

    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @internal
     * @return String
     */
    public function toCache()
    {
        $cacheString = '';
        foreach (array_merge($this->members, $this->viewMembers) as $key => $value) {
            if (null === $value) {
                continue;
            }
            if (strlen($cacheString)) {
                $cacheString .= "~-~";
            }
            if ($value instanceof \DateTime) {
                $cacheString .= "$key|+|DT=".$value->format('Y-m-d H:i:sO')."=";
            } else {
                $cacheString .= "$key|+|$value";
            }
        }
        return $cacheString;
    }

    /**
     * This method will need to implement expanding the object back to its original from the cached data provided.
     * @internal
     * @param  string $data The original data provided in toCache()
     * @return void
     */
    public function fromCache($data)
    {
        $props = array();
        $fieldArray = explode('~-~', $data);
        foreach ($fieldArray as $fieldStr) {
            list($fieldName, $value) = explode('|+|', $fieldStr);
            if (preg_match('/^DT=(.*)=$/', $value, $matches)) {
                $value = $matches[1];
            }//new \DateTime($matches[1]);
            $props[$fieldName] = $value;
            // if(array_key_exists($fieldName, $this->members)) $this->members[$fieldName] = $value;
            // else if(array_key_exists($fieldName, $this->viewMembers)) $this->viewMembers[$fieldName] = $value;
        }
        $this->populate($props);
        return $this;
    }

    /**
     * This method will return all data and view members of the object as an array with key as fieldname.
     * @param  boolean $includeView whether to include view members in the array
     * @return array
     */
    public function toArray($includeView = true)
    {
        $data = $includeView ? array_merge($this->members, $this->viewMembers) : $this->members;
        return $data;
    }

    /**
     * A validation function where to check required fields
     *
     * @internal
     * @param   $value
     * @param   string $key
     * @throws  InputException
     */
    public function validateRequiredField($value, string $key): void
    {
        $message = sprintf(gettext('Field %s is required'), $key);

        if (empty($value) && '0' != $value) {
            throw new InputException($message, InputException::FIELD_ERROR, $key);
        }
    }
    
}
