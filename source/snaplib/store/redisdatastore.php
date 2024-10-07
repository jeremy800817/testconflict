<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use Snap\IEntityStore;
Use Snap\IEntity;
Use Snap\App;
Use Snap\cacher;

/**
* This class implements a basic hot cache based datastore class to easily manage and group cached item keys
* into logical silos.  The datastore will be based on memcache implementation and will have support for
* increment / decrements as well as proper locking of records for updates if desired
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class redisatastore implements IEntityStore
{
    Use \Snap\TLogging;

    private $app;

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
     * Table name for the data contained
     * @var String
     */
    private $tableName;

    /**
     * The table suffix to append to the original name
     * @var String
     */
    private $tableSuffix;

    /**
     * The entity object class name to instantiates and interact with
     * @var String
     */
    protected $itemClassName;

    /**
     * Whether to use transaction to manage this datastore
     * @var Boolean
     */
    private $useTransaction;

    /**
     * Whether to use transaction to manage this datastore
     * @var Boolean
     */
    private $keepIndexes = true;

    /**
     * Index storage
     */
    private $indexStore = null;

    private $defaultTimeout = 86400;

    /**
     * The contructor for this class.
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
    /**
     * The contructor for this class.
     * @param Cacher  $cacher            Cacher object to be used for storing temporary data
     * @param String  $systemCachePrefix The prefix stirng
     * @param String  $table             The table name to prefix items stored in this store
     * @param string  $itemClassName     The name of the class that will store values to be kept
     * @param boolean $bUseTransactions  Whether to lock the table during writing of data
     * @param integer $defaultTimeout    Items stored inside the cache to last for how long (in seconds) 0 = never expires
     * @param boolean $keepIndex         Whether to keep an array of indexes stored inside the store
     */
    public function __construct(Cacher $cacher, $systemCachePrefix, $table, $itemClassName = '\Snap\object\keyvalueitem', $bUseTransactions = true, $defaultTimeout = 0 /**Never expire */, $keepIndex = true)
    {
        $this->app = \Snap\App::getInstance();
        $this->cacheEngine = $cacher;
        $this->tableName = $table;
        $this->itemClassName = $itemClassName;
        $this->useTransaction = $bUseTransactions;
        $this->defaultTimeout = $defaultTimeout;
        $this->keepIndexes = $keepIndex;

        return $this;
    }

    /**
     * Returns the table name used by this datastore
     * @return String
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    public function getIndexes()
    {
        return $this->indexStore;
    }

    /**
     * This method quickly checks if the cache data with the provided id still exists or not.
     *
     * @param integer $id    The key name for the cache
     * @return boolean   		True if the item with id exists.  False otherwise.
     */
    public function exists($id)
    {
        $this->initialiseLazyLoad();
        $value = $this->cacheEngine->get($this->getItemCacheName($id));
        if ('' == $value || null == $value) {
            if ($this->keepIndexes && in_array($id, $this->indexStore)) {
                unset($this->indexStore[$id]);
                $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
            }
            return false;
        }
        return true;
    }

    /**
     * A shortcut method to directly set data to the cache
     *
     * @param integer $id    The key name for the cache
     * @param mixed   $value Data to store
     */
    public function set($id, $value)
    {
        if (! preg_match('/keyvalueitem/', $this->itemClassName)) {
            $this->log(__CLASS__."::set() The object storage does not support use of this method. This method only supports the keyvalueitem class", SNAP_LOG_ERROR);
            throw new InputException('This method can only be used with keyvalueitem object', InputException::GENERAL_ERROR, 'itemClassName');
        }
        return $this->save($this->create(['id' => $id, 'value' => $value]))->value;
    }

    /**
     * This is another shortcut method to quickly return the cache data given its key.
     * @param  string $id The key to retrieve the cache for
     * @return mixed     Data previously stored under this key
     */
    public function get($id)
    {
        if (! preg_match('/keyvalueitem/', $this->itemClassName)) {
            $this->log(__CLASS__."::get() The object storage does not support use of this method. This method only supports the keyvalueitem class", SNAP_LOG_ERROR);
            throw new InputException('This method can only be used with keyvalueitem object', InputException::GENERAL_ERROR, 'itemClassName');
        }
        return $this->getById($id)->value;
    }

    /**
     * Creation method for the entity item related to this datastore
     * @param  array  $initialProperties Key-Value pair array to initialise the object with.
     * @return IEntity  created object
     */
    public function create($initialProperties = array())
    {
        $anObject = new $this->itemClassName($this, $initialProperties);
        return $anObject;
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
    public function save(IEntity $entityItem, $updateFields = array(), $lockRecord = false)
    {
        if (defined('SNAP_APP_LOGSQL')) {
            $this->log(__CLASS__.":::save() Saving cache entry for id {$entityItem->id}", SNAP_LOG_DEBUG);
        }
        $this->initialiseLazyLoad();
        $startedTransaction = false;
        $lockName = $this->getItemLockName($entityItem->id);
        try {
            if (! in_array('id', $this->columnInfo)) {
                throw new InputException(gettext('Object must support id field'), InputException::FIELD_ERROR, 'id');
            }
            if (! $entityItem->isValid()) {
                throw new InputException(gettext('Some field values has been left empty of is incorrect'), InputException::FIELD_ERROR, 'value');
            }
            
            if ($this->useTransaction) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::save() Attempting to get exclusive access to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->waitForLock($lockName, 1, 10, -1);
                $startedTransaction = true;
            }
            if (2 == count($this->columnInfo) && in_array('value', $this->columnInfo)) {
                $value = $entityItem->value;
            } elseif ($entityItem instanceof ICacheable) {
                $value = $entityItem->toCache();
            } else {
                throw new InputException(__CLASS__."::save() does not support entity that does not have value field or does not implement ICacheable interface");
            }

            if ($entityItem->onPrepareUpdate()) {
                $this->cacheEngine->set($this->getItemCacheName($entityItem->id), $value, $this->defaultTimeout);
                $newItem = $this->getById($entityItem->id);
                $entityItem->onCompletedUpdate($newItem);
                if ($this->keepIndexes && ! in_array($entityItem->id, $this->indexStore)) {
                    $this->indexStore[] = $entityItem->id;
                    $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
                }
            }
            if ($this->useTransaction && $startedTransaction) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::save() Release lock to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->unlock($lockName, 1, 10, -1);
            }
            return $newItem;
        } catch (InputException $e) {
            $this->log(__CLASS__."::save() thrown exception with message ({$e->getMessage()})", SNAP_LOG_ERROR);
            if ($this->useTransaction && $startedTransaction) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::save() Release lock due to exception to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->unlock($lockName);
            }
            throw $e;
        }
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
        if (defined('SNAP_APP_LOGSQL')) {
            $this->log(__CLASS__.":::delete() Deleting cache item with ID {$entityItem->id}", SNAP_LOG_DEBUG);
        }
        $startedTransaction = false;

        if (! in_array('id', $this->columnInfo)) {
            throw new InputException(gettext('Object must support id field'), InputException::FIELD_ERROR, 'id');
        }
        if (! $entityItem->isValid()) {
            throw new InputException(gettext('Some field values has been left empty of is incorrect'), InputException::FIELD_ERROR, 'value');
        }
        $lockName = $this->getItemLockName($entityItem->id);
        //1.  Check if the object is valid state and can proceed to be updated / created.
        if ($this->useTransaction) {
            if (defined('SNAP_APP_LOGSQL')) {
                $this->log(__CLASS__.":::delete() Attempting to get exclusive access to item with lockName {$lockName}", SNAP_LOG_DEBUG);
            }
            $this->cacheEngine->waitForLock($lockName, 1, 10, -1);
            $startedTransaction = true;
        }
        if ($entityItem->onPredelete()) {
            $this->cacheEngine->delete($this->getItemCacheName($entityItem->id));
        }
        if ($this->keepIndexes && in_array($entityItem->id, $this->indexStore)) {
            unset($this->indexStore[$entityItem->id]);
            $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
        }
        if ($this->useTransaction && $startedTransaction) {
            if (defined('SNAP_APP_LOGSQL')) {
                $this->log(__CLASS__.":::delete() Release exclusive access item with lockName {$lockName}", SNAP_LOG_DEBUG);
            }
            $this->cacheEngine->unlock($lockName, 1, 10, -1);
        }
        return true;
    }

    /**
     * This method will get the object referenced by its ID.
     * @param  integer  $id              ID of the record to obtain
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $lockForUpdate    Whether to lock the record up for updating.
     * @param  integer $tableOrViewIndex Source of the data.  EIther from table or the availavle views.
     *                                   	0 - defaults to table.  1 - first view, 2 - second view etc
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getById($id, $updateFields = array(), $lockRecord = false)
    {
        if (defined('SNAP_APP_LOGSQL')) {
            $this->log(__CLASS__.":::getById() Getting cache item with ID {$id}", SNAP_LOG_DEBUG);
        }
        $this->initialiseLazyLoad();
        $startedTransaction = false;
        $lockName = $this->getItemLockName($id);

        try {
            if (! in_array('id', $this->columnInfo)) {
                throw new InputException(gettext('Object must support id field'), InputException::FIELD_ERROR, 'id');
            }

            if ($this->useTransaction && $lockForUpdate) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::getById() Attempting to get exclusive access to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->waitForLock($lockName, 1, 10, -1);
                $startedTransaction = true;
            }
            $value = $this->cacheEngine->get($this->getItemCacheName($id));
            if (2 == count($this->columnInfo) && in_array('value', $this->columnInfo)) {
                $obj =  $this->create(['id' => $id, 'value' => $value]);
            } elseif ($entityItem instanceof ICacheable) {
                $obj =  $this->create(['id' => $id]);
                $obj->fromCache($value);
            } else {
                throw new InputException(__CLASS__."::getById() does not support entity that does not have value field or does not implement ICacheable interface");
            }
            if ($this->keepIndexes && in_array($id, $this->indexStore) && ('' == $value || null == $value)) {
                unset($this->indexStore[$id]);
                $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
            } elseif ($this->keepIndexes && ! in_array($obj->id, $this->indexStore)) {
                $this->indexStore[] = $obj->id;
                $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
            }
            if ($this->useTransaction && $startedTransaction) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::getById() Unlocking exclusive access to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->unlock($lockName, 1, 10, -1);
            }
            return $obj;
        } catch (InputException $e) {
            $this->log(__CLASS__."::getById() thrown exception with message ({$e->getMessage()})", SNAP_LOG_ERROR);
            if ($this->useTransaction && $startedTransaction) {
                if (defined('SNAP_APP_LOGSQL')) {
                    $this->log(__CLASS__.":::getById() Unlocking exclusive access due to exception to item with lockName {$lockName}", SNAP_LOG_DEBUG);
                }
                $this->cacheEngine->unlock($lockName);
            }
            throw $e;
        }
    }

    /**
     * This method uses the cache interface to directly and in a thread safe manner increment values
     *
     * @param  mixed  	$entityOrId  Object or Id to increment for
     * @param  integer 	$incrementBy Amount to increment by
     * @return integer              Value after adding
     */
    public function increment($entityOrId, $incrementBy)
    {
        $incrementBy = intval($incrementBy);
        $id = ($entityOrId instanceof IEntity) ? $entityOrId->id : $entityOrId;
        if (defined('SNAP_APP_LOGSQL')) {
            $this->log(__CLASS__.":::increment() Getting cache item with ID {$id} with increment of $incrementBy", SNAP_LOG_DEBUG);
        }
        $cacheName = $this->getItemCacheName($id);
        $this->cacheEngine->add($cacheName, 0, $this->defaultTimeout);
        $value = $this->cacheEngine->increment($cacheName, $incrementBy);
        if ($entityOrId instanceof IEntity) {
            $entityOrId->value = $value;
        }
        if ($this->keepIndexes && ! in_array($id, $this->indexStore)) {
            $this->indexStore[] = $id;
            $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
        }
        return $value;
    }

    /**
     * This method uses the cache interface to directly and in a thread safe manner increment values
     *
     * @param  mixed  	$entityOrId  Object or Id to increment for
     * @param  integer 	$decrementBy Amount to decrease by
     * @return integer              Value after subtracting
     */
    public function decrement($entityOrId, $decrementBy)
    {
        return $this->increment($entityOrId, -1 * $decrementBy);
    }

    /**
     * This method will delay the loading of the entity class object etc until it is really necessary so that
     * initialising of the DBDatastore object will not incur loading objects.
     *
     * @return void
     */
    protected function initialiseLazyLoad()
    {
        if (0 == count($this->columnInfo)) {
            $testObject = new $this->itemClassName($this, array());
            if (! $testObject || ! $testObject instanceof IEntity) {
                throw new Exception("The provided class {$this->itemClassName} does not implement IEntity interface.  Unable to proceed", 1);
            }
            $this->columnInfo = $testObject->getFields();
            $this->viewColumnInfo = $testObject->getViewFields();

            if ($this->keepIndexes) {
                $this->indexStore = json_decode($this->cacheEngine->get($this->getCacheIndexName()));
            }
        }
    }

    /**
     * Internal method to format a consistent name for the cache.
     * @param  string $id Item cache identifier
     * @return string     The unique name that will be saved into the cache.
     */
    protected function getItemCacheName($id)
    {
        return sprintf("{%s}:%s", $this->tableName, $id);
    }

    /**
     * Internal method to format a consistent exclusive lock name for the cache.
     * @param  string $id Item cache identifier
     * @return string     The unique name that will be saved into the cache.
     */
    protected function getItemLockName($id)
    {
        return sprintf("{%s}:%s:lock", $this->tableName, $id);
    }

    /**
     * Internal method to get the index storage name.
     * @return string     The unique name that will be used to save the store
     */
    protected function getCacheIndexName()
    {
        return sprintf("{%s}:index", $this->tableName);
    }

    public function refreshCache()
    {
        $this->initialiseLazyLoad();
        foreach ($this->indexStore as $cacheid) {
            $cacheName = $this->getItemCacheName($cacheid);
            $this->cacheEngine->delete($cacheName);
            $this->indexStore = [];
            $this->cacheEngine->set($this->getCacheIndexName(), json_encode($this->indexStore), 0);
        }
    }

    /**
     *  This method is used to describe the contents of this factory for debugging purposes.
     *  @param String $tableStyleHTML  The table styling within the table tag
     *  @param String $rowStyleHTML  The row styling within the td tag.
     *
     *  @return  String  The HTML output describing this factory
     */
    public function describeCache($tableStyleHTML = '', $rowStyleHTML = 'class="normal"')
    {
        $this->initialiseLazyLoad();

        $buffer = 'Caching Factory: ' . get_class($this) . '<br>Key Prefix: '. $this->getItemCacheName().'<br>Total in cache is ' . count($this->indexStore) . '<br>';
        $widthToConvertToTextArea = 30;
        if (0 == count($this->indexStore) || ! $this->keepIndexes) {
            return "No Data";
        }
        $columns = array();
        foreach ($this->indexStore as $cacheid) {
            $one = $this->getById($cacheid);
            if (0 == count($columns)) {
                $columns = array_merge($one->getFields(), $one->getViewFields());
                $buffer .= "<table $tableStyleHTML><tr $rowStyleHTML>";
                foreach ($columns as $aColumn) {
                    $buffer .= "<td>".htmlspecialchars($aColumn)."</td>";
                }
                $buffer .= "</tr>";
            }
            $buffer .= "<tr $rowStyleHTML>";
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
        if (0 < count($this->indexStore)) {
            $buffer .= "</table>";
        }

        return $buffer;
    }
}
?>