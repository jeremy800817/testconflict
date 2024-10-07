<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use PDO;
use PDOException;
use PDORow;
Use Snap\IEntityStore;
Use Snap\IEntity;
Use Snap\App;
Use Snap\ICacher;
Use Snap\store\dbdatastore;

/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class simplecacheddbdatastore extends dbdatastore
{
    /**
     * The cacher object
     * @var Snap\cacher
     */
    private $cacheEngine = null;

    /**
     * Keep the system wide prefix for the cache
     * @var string
     */
    private $systemCachePrefix = null;

    /**
     * Cache key name
     * @var string
     */
    private $cacheName = null;

    /**
     * Container to store items retrieved from cache
     * @var array
     */
    private $cacheObject = array();

    /**
     * Indicate whether to ignore the cache data and retrieve from DB directly
     * @var boolean
     */
    private $ignoreCacheCopy = false;

    private $lastModified = null;

    /**
     * Checks if the datastore has view
     * @var boolean
     */
    private $hasView = false;
    /**
     * The contructor for this class.
     * @param Cacher  $cacher         	The cache class that is used as temporary store
     * @param PDO     $dbHandle         PDO object to use for execution of query
     * @param String  $table            Table name to get and update data
     * @param String  $tableSuffix      Suffix for the table
     * @param String  $columnPrefix     Prefix for the column names
     * @param String  $itemClassName    Item class name that interface with this datastore
     * @param array   $views            Array of views that can be associated with datastore
     * @param array   $relatedStore     Array of name => storeObject instance related to thsi store.
     * @param boolean $bUseTransactions Whether to enable transactions
     * @param boolean $forceInsertRecord Whether to force inserting a record for update if it is not available yet.
     */
    public function __construct(
        ICacher $cacher,
        $systemCachePrefix,
        PDO $dbHandle,
        $table,
        $tableSuffix,
        $columnPrefix,
        $itemClassName,
                                $views = array(),
        $relatedStores = array(),
        $bUseTransactions = true,
        $forceInsertRecord = false
    ) {
        $this->cacheEngine = $cacher;
        $this->systemCachePrefix = 's'.$systemCachePrefix;
        if (count($views)) {
            $this->hasView = true;
        }
        return parent::__construct(
            $dbHandle,
            $table,
            $tableSuffix,
            $columnPrefix,
            $itemClassName,
                                $views,
            $relatedStores,
            $bUseTransactions,
            $forceInsertRecord
        );
    }

    /**
     * This method will remove the storage record for the related object by its ID
     *
     * @param  IEntity $entityItem Object to remove
     * @throws InputException if any SQL errors are found
     * @return Boolean  True if successful.  False otherwise.
     */
    public function delete(\Snap\IEntity $entityItem)
    {
        $result = parent::delete($entityItem);
        if ($result) {
            if (isset($this->cacheObject[$entityItem->id])) {
                unset($this->cacheObject[$entityItem->id]);
                $this->updateCache();
            }
        }
        return $result;
    }

    /**
     * This method will get the object by one of the unique field
     * @param  String  $column 			The field name to search for
     * @param  integer  $id              value of the field
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $forUpdate        Whether to lock the record up for updating.
     * @param  integer $tableOrViewIndex Source of the data.  EIther from table or the availavle views.
     *                                   	0 - defaults to table.  1 - first view, 2 - second view etc
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getByField($column, $value, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    {
        if (! $this->cacheObject) {
            $this->populateCache();
        }
        $object = ($this->ignoreCacheCopy) ? null : $this->getObjectFromCache($column, $value);
        if (! $object) {
            $object = parent::getByField($column, $value, $fields, $forUpdate, $tableOrViewIndex);
            if (0 == count($fields) && $object) {
                $this->cacheObject[$object->id] = $object;
                $this->updateCache();
            }
        }
        return $object;
    }

    /**
     * This method will created a new record or update an existing record from the entity object.
     *
     * @param  IEntity $entityItem   Object to be updated
     * @param  array   $updateFields Selected fields to update.  Otherwise default will update all fields
     * @param  boolean $lockRecord   Whether to add a FOR UPDATE statement into the record.
     * @throws InputException if any SQL errors are found
     * @return IEntity  The updated object
     */
    public function save(IEntity $entityItem, $updateFields = array(), $lockRecord = true)
    {
        //Temporary ignore cache....because we want to get latest data from DB after saving it.
        $this->ignoreCacheCopy = true;
        $viewObject = null;
        $object = parent::save($entityItem, $updateFields, $lockRecord);
        if ($object) {
            //Added by Devon on 2018/8/22 to re-initialise another object so that cached data within the object can be properly
            //populated since the object is created in dbdatastore::onSaveExecuted() before subsequent calls to the
            //snapObject::onCompletedUpdate()
            $object = $this->create($object->toArray());
            //End Add by Devon on 2018/8/22
            $this->cacheObject[$object->id] = $object;  //update cache with initial data first.
            if ($this->hasView) {
                $viewObject = parent::getById($object->id, [], false, 1 /* default to first view */);
            }
            if ($viewObject) {
                $this->cacheObject[$object->id] = $viewObject;
            }
            $this->updateCache();
        }
        $this->ignoreCacheCopy = false;
        return $object;
    }

    public function refreshCache()
    {
        if (! $this->cacheObject) {
            $this->populateCache();
        }
        $this->ignoreCacheCopy = true;
        if ($this->hasView) {
            $queryHandle = $this->searchView()->select();
        } else {
            $queryHandle = $this->searchTable()->select();
        }
        foreach ($this->cacheObject as $id => $unused) {
            $queryHandle->orWhere('id', $id);
        }
        $array = $queryHandle->execute();
        $this->cacheObject = array();
        if (is_array($array) && count($array)) {
            foreach ($array as $anObj) {
                $this->cacheObject[$anObj->id] = $anObj;
            }
        }
        $this->ignoreCacheCopy = false;
        $this->updateCache();
    }

    /**
     * This is a callback method to allow derived class to do caching etc.
     * @param  String $queryString        Query String
     * @param  Array $queryParameters     Parameters for the query string
     * @param  Array $data                Data resulted from this query.
     * @return void
     */
    protected function onCompletedQuery($queryString, $queryParameters, $data)
    {
        if (! preg_match('/join/', $queryString) && preg_match('/select \\*/', $queryString) &&
            is_array($data) && count($data) && is_object($data[0])) {
            $toUpdateCache = false;
            if (! $this->cacheObject) {
                $this->populateCache();
            }
            //Only cache if search for all data.
            if (is_array($data) && count($data)) {
                foreach ($data as $anItem) {
                    if (! is_object($anItem)) {
                        continue;
                    }
                    if (! isset($this->cacheObject[$anItem->id])) {
                        $toUpdateCache = true;
                        $this->cacheObject[$anItem->id] = $anItem;
                    }
                }
            }
            if ($toUpdateCache) {
                $this->updateCache();
            }
        }
    }

    /**
     * This method will delay the loading of the entity class object etc until it is really necessary so that
     * initialising of the DBDatastore object will not incur loading objects.
     *
     * @return void
     */
    protected function initialiseLazyLoad()
    {
        //Further checking on the ICacheable interface for objects that uses this datastore
        if (null == $this->cacheName) {
            $testObject = new $this->itemClassName($this, array());
            if (! $testObject || (! $testObject instanceof \Snap\ICacheable)) {
                throw new \Exception("The provided class {$this->itemClassName} does not implement ICacheable interface.  Unable to proceed", 1);
            }
            if (preg_match('/([a-zA-Z]+)$/', $this->itemClassName, $matches)) {
                $this->cacheName = $this->systemCachePrefix . '_' . $matches[1]. '_factory';
            } else {
                $this->cacheName = $this->systemCachePrefix . '_' . $this->itemClassName . '_factory';
            }
            return parent::initialiseLazyLoad();
        }
    }

    /**
     * Initialise the cached data.
     * @return void
     */
    private function populateCache()
    {
        $this->cacheObject = array();
        if (! $this->cacheName) {
            $this->initialiseLazyLoad();
        }
        $tmp = $this->cacheEngine->get($this->cacheName);
        if (strlen($tmp)) {
            $packed = json_decode($tmp, true);
            $this->lastModified = new \Datetime($packed['modified'], App::getInstance()->getServerTimezone());
            $rawArray = $packed['data'];
            foreach ($rawArray as $aRawObjectData) {
                $object = $this->create()->fromCache($aRawObjectData);
                $this->cacheObject[$object->id] = $object;
            }
        }
    }

    /**
     * Checks for cached copy of data and use it if available.
     * @param  string $field field name to get data from
     * @param  string $value value corresponding to the content of the field specified to get.
     * @return IEntity|null        returns the object if available.  null if not.
     */
    private function getObjectFromCache($field, $value)
    {
        if (! $this->cacheObject) {
            $this->populateCache();
        }
        if ('id' == $field && isset($this->cacheObject[$value])) {
            return $this->cacheObject[$value];
        } elseif ('id' == $field) {
            return null;
        }

        foreach ($this->cacheObject as $object) {
            if ($object->{$field} == $value) {
                return $object;
            }
        }
        return null;
    }

    /**
     * Update the cache data
     * @param  boolean $bForce whether to force update or wait till cache is open.
     * @return void
     */
    private function updateCache($bForce = false)
    {
        $cacheData = array();
        foreach ($this->cacheObject as $anObject) {
            $cacheData[] = $anObject->toCache();
        }
        $bTimeout = false;
        while (! $this->cacheEngine->waitForLock('simpleCacheUpdateLock_'.$this->cacheName, 1, 5, 5)) {
            if (! $bForce) {
                $bTimeout = true;
                break;
            }
        }
        if (! $bTimeout) {
            $this->lastModified = new \Datetime('now', App::getInstance()->getServerTimezone());
            $this->cacheEngine->set($this->cacheName, json_encode(['modified' => $this->lastModified->format('Y-m-d H:i:s'), 'data' => $cacheData]), 0);
        }
        $this->cacheEngine->unlock('simpleCacheUpdateLock_'.$this->cacheName);
    }

    /**
     *  This method is used to describe the contents of this factory for debugging purposes.
     *  @param String $tableStyleHTML  The table styling within the table tag
     *  @param String $rowStyleHTML  The row styling within the td tag.
     *
     *  @return  String  The HTML output describing this factory
     */
    public function describeCache($tableStyleHTML = '', $rowStyleHTML = 'class="normal"', $rawCacheData = false)
    {
        if (0 == count($this->cacheObject)) {
            $this->populateCache();
        }
        if ($this->lastModified) {
            $this->lastModified->setTimezone(App::getInstance()->getUserTimezone());
        } else {
            $this->lastModified  = new \Datetime('now');
        }
        $buffer = 'Caching Factory: ' . get_class($this) . '<br>Factory Cache Date:  '. $this->lastModified->format("Y-m-d H:i:s") . '<br>Total in cache is ' . count($this->cacheObject) . '<br>';
        $widthToConvertToTextArea = 30;
        if (0 == count($this->cacheObject)) {
            return "No Data";
        }
        $columns = array();
        foreach ($this->cacheObject as $one) {
            if (0 == count($columns) && ! $rawCacheData) {
                $columns = array_merge($one->getFields(), $one->getViewFields());
                $buffer .= "<table $tableStyleHTML><tr $rowStyleHTML>";
                foreach ($columns as $aColumn) {
                    $buffer .= "<td>".htmlspecialchars($aColumn)."</td>";
                }
                $buffer .= "</tr>";
            } elseif ($rawCacheData && 0 == count($columns)) {
                $columns[] = 'dummy';
                $buffer .= "<table $tableStyleHTML><tr $rowStyleHTML><td>Raw Data</td>";
            }
            $buffer .= "<tr $rowStyleHTML>";
            if ($rawCacheData || 0 == count($columns)) {
                $buffer .= "<td>".$one->toCache()."</td>";
            } else {
                foreach ($columns as $aColumn) {
                    if ($one->{$aColumn} instanceof \Datetime) {
                        $buffer .= "<td>".$one->{$aColumn}->format('Y/m/d H:i:s')."</td>";
                    } elseif (strlen($one->{$aColumn}) > $widthToConvertToTextArea) {
                        $buffer .= "<td><textarea cols=30 rows=10>".htmlspecialchars($one->{$aColumn})."</textarea></td>";
                    } else {
                        $buffer .= "<td>".htmlspecialchars($one->{$aColumn})."</td>";
                    }
                }
            }
        }
        if (0 < count($this->cacheObject)) {
            $buffer .= "</table>";
        }

        return $buffer;
    }
}
?>