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
* This class provide another implementation of cached datastore where the data object are stored
* independent of the indexes and accessed separately.  So for this implementation, cache can
* automatically expire after a predefined period. Indexing can support multiple fields but
* retrieval still limited to a single object.  I.e.  2 fields that uniquely refer to an object can be cached
* and retrieve efficiently with this store.
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class simpleAutoTimeoutCachedDBDatastore extends dbdatastore
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
     * Associative array storing keys and id mapping.
     * Actual data will be stored separately in its own key.
     * @var array
     */
    private $cacheKeyMap = null;

    /**
     * Fields that we will build a cache on.
     * Actual data will be stored separately in its own key.
     * @var array
     */
    private $cacheFields = null;

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

    /**
     * Last modified datetime of the cache
     * @var \Datetime
     */
    private $lastModified = null;

    /**
     * Checks if the datastore has view
     * @var boolean
     */
    private $hasView = false;

    /**
     * Timeout for the cached data that we store.  In seconds.
     * @var integer
     */
    private $cacheTimeoutInS = 0;

    /**
     * The contructor for this class.
     * @param Cacher  $cacher         	   The cache class that is used as temporary store
     * @param String  $systemCachePrefix   additional prefix to add when creating cache key.
     * @param array   $cacheFields         array of keys that we will store object reference for indexing
     * @param int     $cacheTimeout        time in seconds to store the data before it expires (0 - no timeout)
     * @param PDO     $dbHandle            PDO object to use for execution of query
     * @param String  $table               Table name to get and update data
     * @param String  $tableSuffix         Suffix for the table
     * @param String  $columnPrefix        Prefix for the column names
     * @param String  $itemClassName       Item class name that interface with this datastore
     * @param array   $views               Array of views that can be associated with datastore
     * @param array   $relatedStore        Array of name => storeObject instance related to thsi store.
     * @param boolean $bUseTransactions    Whether to enable transactions
     * @param boolean $forceInsertRecord   Whether to force inserting a record for update if it is not available yet.
     */
    public function __construct(
        ICacher $cacher,
        $systemCachePrefix,
        $cacheFields,
        $cacheTimeout,
        \PDO $dbHandle,
        $table,
        $tableSuffix,
        $columnPrefix,
        $itemClassName,
        $views = array(),
        $relatedStores = array(),
        $bUseTransactions = true,
        $forceInsertRecord = true
    ) {
        $this->cacheTimeoutInS = $cacheTimeout;
        $this->cacheEngine = $cacher;
        if (is_string($cacheFields)) {
            $this->cacheFields = [ $cacheFields ];
        } elseif (is_array($cacheFields)) {
            $this->cacheFields = $cacheFields;
        }
        $this->systemCachePrefix = 'k'.$systemCachePrefix;
        if (count($views)) {
            $this->hasView = true;
        }

        $ret = parent::__construct(
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
        $this->populateCache();
        return $ret;
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
            $this->updateCache([ $entityItem ], true);
        }
        return $result;
    }

    /**
     * This method will get the object by one of the unique field
     * @param  String  $fieldValueArray  Key-value pair to base search on
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $forUpdate        Whether to lock the record up for updating.
     * @param  integer $tableOrViewIndex Source of the data.  EIther from table or the availavle views.
     *                                      0 - defaults to table.  1 - first view, 2 - second view etc
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getByFields($fieldValueArray, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    {
        $object = ($this->ignoreCacheCopy) ? null : $this->getObjectFromCacheByFieldsArray($fieldValueArray);
        if (! $object) {
            $res = $this->searchView(true, $tableOrViewIndex)->select();
            foreach ($fieldValueArray as $key => $value) {
                $res->andWhere($key, $value);
            }
            $object = $res->orderby('id', 'desc')->one();
            if (0 == count($fields) && $object) {
                $this->updateCache([ $object ]);
            }
        }
        return $object;
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
        $object = ($this->ignoreCacheCopy) ? null : $this->getObjectFromCache($column, $value);
        if (! $object) {
            $object = parent::getByField($column, $value, $fields, $forUpdate, $tableOrViewIndex);
            if (0 == count($fields) && $object) {
                $this->updateCache([ $object ]);
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
            // $this->cacheObject[$object->id] = $object;  //update cache with initial data first.
            if ($this->hasView) {
                $viewObject = parent::getById($object->id, [], false, 1 /* default to first view */);
            }
            if ($viewObject) {
                $this->updateCache([ $viewObject ]);
            } else {
                $this->updateCache([$object]);
            }
        }
        $this->ignoreCacheCopy = false;
        return $object;
    }

    /**
     * This method will remove all keys associated with this datastore and restart the collection of cache data.
     * @return void
     */
    public function refreshCache()
    {
        $allIds = [];
        if (count($this->cacheKeyMap)) {
            foreach ($this->cacheKeyMap as $fieldKey => $fieldEntry) {
                foreach ($fieldEntry as $anId) {
                    $allIds[$anId] = $anId;
                }
            }
        }
        foreach ($allIds as $anId) {
            $this->cacheEngine->delete($this->getObjectIdName($anId));
        }
        $this->cacheEngine->delete($this->cacheName);
        $this->cacheKeyMap = [];
        $this->cacheObject = [];
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
        if (! in_array('id', $this->cacheFields) && 1 != count($data)) {
            //Ignore collecting objects if we are not indexing its id and returned objects is not 1.
            return;
        }
        $objectsToCache = [];
        if (! preg_match('/join/', $queryString) && preg_match('/select \\*/', $queryString) &&
            is_array($data) && count($data) && is_object($data[0])) {
            //Only cache if search for all data.
            if (is_array($data) && count($data)) {
                foreach ($data as $anItem) {
                    if (! is_object($anItem)) {
                        continue;
                    }
                    if (! isset($this->cacheKeyMap['id']) || ! in_array($anItem->id, $this->cacheKeyMap['id'], true)) {
                        $objectsToCache[] = $anItem;
                    }
                }
            }
            if (0 < count($objectsToCache)) {
                $this->updateCache($objectsToCache);
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
        if (! $this->cacheName) {
            $this->initialiseLazyLoad();
        }
        $tmp = $this->cacheEngine->get($this->cacheName);
        if ($tmp && 0 < strlen($tmp)) {
            $packed = json_decode($tmp, true);
            $this->lastModified = new \Datetime($packed['modified'], App::getInstance()->getServerTimezone());
            $this->cacheKeyMap = $packed['data'];
        } else {
            $this->cacheKeyMap = array();
        }
    }

    /**
     * Checks for cached copy of data and use it if available.
     * @param  array $fields Key value pair indicating the composite fields to store keys on.
     * @return IEntity|null        returns the object if available.  null if not.
     */
    protected function getObjectFromCacheByFieldsArray($fields)
    {
        $field = null;
        $value = null;
        foreach ($fields as $aKey => $aValue) {
            $field = (null == $field) ? $aKey : "{$field}_{$aKey}";
            $value = (null == $value) ? $aValue : "{$value}_{$aValue}";
        }
        $id = 0;
        if (isset($this->cacheKeyMap[$field]) && isset($this->cacheKeyMap[$field][$value])) {
            $id = $this->cacheKeyMap[$field][$value];
        }
        if ((0 == $id)) {
            return null;
        } elseif (isset($this->cacheObject[$id])) {
            return $this->cacheObject[$id];
        } else {
            $objectStr = $this->cacheEngine->get($this->getObjectIdName($id));
            if ($objectStr && 0 < strlen(($objectStr))) {
                $this->cacheObject[$id] = $this->create();
                $this->cacheObject[$id]->fromCache($objectStr);
                return $this->cacheObject[$id];
            }
        }
        return null;
    }

    /**
     * Checks for cached copy of data and use it if available.
     * @param  string $field field name to get data from
     * @param  string $value value corresponding to the content of the field specified to get.
     * @return IEntity|null        returns the object if available.  null if not.
     */
    protected function getObjectFromCache($field, $value)
    {
        return $this->getObjectFromCacheByFieldsArray([$field => $value]);
    }

    private function doCacheIndexCleanup()
    {
        if (! $this->cacheEngine->add($this->cacheName . '_idxclean', 1, $this->cacheTimeoutInS)) {
            return;
        }
        $all = [];
        $deleted = [];
        foreach ($this->cacheKeyMap as $fieldKey => $fields) {
            foreach ($fields as $fieldIndex => $anId) {
                if (isset($deleted[$anId])) {
                    unset($this->cacheKeyMap[$fieldKey][$fieldIndex]);
                } elseif (! isset($all[$anId])) {
                    if (0 < strlen($this->cacheEngine->get($this->getObjectIdName($anId)))) {
                        $all[$anId] = $anId; //cache still active.
                    } else {
                        unset($this->cacheKeyMap[$fieldKey][$fieldIndex]);
                        $deleted[$anId] = $anId;
                    }
                }
            }
        }
    }

    /**
     * Updates cache data
     * @param  IEntity[]   $objectArr       Array of objects to update if available
     * @param  boolean     $deleteOperation Whether the objects if being deleted or not.
     * @return void
     */
    private function updateCache(array $objectArr, $deleteOperation = false)
    {
        foreach ($objectArr as $object) {
            if (! $deleteOperation) {
                $this->cacheObject[$object->id] = $object;
            } elseif ($deleteOperation && isset($this->cacheObject[$object->id])) {
                unset($this->cacheObject[$object->id]);
            }
            if (! $deleteOperation) {
                foreach ($this->cacheFields as $aField) {
                    $theField = null;
                    $theValue = null;
                    if (is_array($aField)) {
                        foreach ($aField as $subField) {
                            $theField = (null == $theField) ? $subField : "{$theField}_{$subField}";
                            $theValue = (null == $theValue) ? $object->{$subField} : "{$theValue}_".$object->{$subField};
                        }
                    } else {
                        $theField = $aField;
                        $theValue = $object->{$aField};
                    }
                    //Only update key object ID if the object is the latest (biggest ID) or not in our registry yet.
                    if (! isset($this->cacheKeyMap[$theField][$theValue]) || ($object->id > $this->cacheKeyMap[$theField][$theValue])) {
                        $this->cacheKeyMap[$theField][$theValue] = $object->id;
                    }
                }
            } elseif ($deleteOperation) {
                foreach ($this->cacheKeyMap as $fieldKey => $fieldKeyArray) {
                    foreach ($fieldKeyArray as $deleteKey => $value) {
                        if ($value == $object->id) {
                            unset($this->cacheKeyMap[$fieldKey][$deleteKey]);
                            break;
                        }
                    }
                }
            }
        }
        $count = 0;
        $timeout = 5;
        while (! $this->cacheEngine->waitForLock('simpleAutoTimeoutUpdateWaitLock'.$this->cacheName, 1, 5, 5) && $timeout--) {
        }

        if (0 < $timeout) {
            if (! $this->cacheEngine->get($this->cacheName . '_idxclean')) {
                $this->doCacheIndexCleanup();
            }
            $this->lastModified = new \Datetime('now', App::getInstance()->getServerTimezone());
            $cacheData = json_encode(['modified' => $this->lastModified->format('Y-m-d H:i:s'), 'data' => $this->cacheKeyMap]);
            foreach ($objectArr as $object) {
                if (! $deleteOperation) {
                    $this->cacheEngine->set($this->getObjectIdName($object->id), $object->toCache(), $this->cacheTimeoutInS);
                } else {
                    $this->cacheEngine->delete($this->getObjectIdName($object->id));
                }
            }
            $this->cacheEngine->set($this->cacheName, $cacheData, 0);
        }
        $this->cacheEngine->unlock('simpleAutoTimeoutUpdateWaitLock'.$this->cacheName);
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
        if ($this->lastModified) {
            $this->lastModified->setTimezone(App::getInstance()->getUserTimezone());
        } else {
            $this->lastModified  = new \Datetime('now');
        }
        $tmpCacheFields = $this->cacheFields;
        foreach ($tmpCacheFields as $key => $value) {
            if (is_array($value)) {
                $tmpCacheFields[$key] = '[' . join(',', $value) . ']';
            }
        }
        $describedIds = [];
        $index = [];
        foreach ($this->cacheKeyMap as $fields => $fieldValue) {
            foreach ($fieldValue as $anId => $objectId) {
                $describedIds[$objectId] = $objectId;
                if ($anId == $objectId) {
                    $index[$fields][] = "$objectId";
                } else {
                    $index[$fields][] = "$anId => $objectId";
                }
            }
        }
        $buffer = 'Caching Factory: ' . get_class($this) . '<br>Factory Cache Date:  '. $this->lastModified->format("Y-m-d H:i:s") .
                  '<br>Total in cache is ' . count($describedIds) .
                  '<br>Cached fields are: ' . join(', ', $tmpCacheFields);
        foreach ($index as $field => $array) {
            $buffer .= "'<br>Indexed items for $field: " . join(', ', $array) . "<br>";
        }
        $buffer .= '<br>';
        $widthToConvertToTextArea = 30;
        if (0 == count($describedIds)) {
            // return "No Data";
        }
        $columns = array();
        foreach ($describedIds as $anId) {
            $cachedData = $this->cacheEngine->get($this->getObjectIdName($anId));
            if (! $cachedData) {
                continue;
            }
            $one = $this->create();
            $one->fromCache($cachedData);
            if (! $one) {
                continue;
            }
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
        if (0 < count($describedIds)) {
            $buffer .= "</table>";
        }

        return $buffer;
    }

    /**
     * Returns the cache name used to store particular object
     * @param  int $id object ID as identifier
     * @return string
     */
    private function getObjectIdName($id)
    {
        return $this->cacheName . '_' . $id;
    }
}
?>