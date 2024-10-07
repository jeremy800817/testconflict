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
class querycacheddbdatastore extends dbdatastore
{
    /**
     * Maximum number of results that we will cache for.  Otherwise better to get directly from DB
     */
    private $maxObjectToCachePerQuery = 20;

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

    /**
     * Stores the cached queries
     * @var array
     */
    private $cacheQuery = null;

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
        $this->systemCachePrefix = 'q'. $systemCachePrefix;
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
        $newIndexCopy = array();
        $result = parent::delete($entityItem);
        if ($result) {
            $this->processQueryCache('DELETE_DATA', array(), array($entityItem));
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
        if ($this->ignoreCacheCopy) {
            $object = null;
        } elseif ('id' == $column && isset($this->cacheObject[$value])) {
            $object =  $this->cacheObject[$value];
        } elseif ('id' == $column) {
            $data = $this->cacheEngine->get($this->cacheName.'_'.$value);
            if (0 < strlen($data)) {
                $object = $this->create()->fromCache($data);
                $this->cacheObject[$value] = $object;
            } else {
                $object = null;
            }
        } else {
            $object = $this->getObjectsFromCache('where getByField', array($column,$value));
        }
        if (! $object) {
            $object = parent::getByField($column, $value, $fields, $forUpdate, $tableOrViewIndex);
            if (0 == count($fields) && $object && 'id' != $column) {
                $this->processQueryCache('where getByField', array($column,$value), array($object));
            }
        }
        if ($object && is_array($object)) {
            $object = $object[0];
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
        $originalId = $entityItem->id;
        $this->ignoreCacheCopy = true;
        $object = parent::save($entityItem, $updateFields, $lockRecord);
        if ($object) {
            //Added by Devon on 2018/8/22 to re-initialise another object so that cached data within the object can be properly
            //populated since the object is created in dbdatastore::onSaveExecuted() before subsequent calls to the
            //snapObject::onCompletedUpdate()
            $object = $this->create($object->toArray());
            //End Add by Devon on 2018/8/22
            if ($this->hasView) {
                $object = parent::getById($object->id, [], false, 1 /* default to first view */);
            }
            $operation = (0< $originalId) ? 'UPDATE_DATA': 'NEW_DATA';
            $this->processQueryCache($operation, array(), array($object));
        }
        $this->ignoreCacheCopy = false;
        return $object;
    }

    public function describeCache()
    {
        $stats = '';
        if (! $this->cacheQuery) {
            $this->populateCache();
        }
        $stats .= "Querycache for {$this->cacheName} info\n";
        foreach ($this->cacheQuery as $key => $data) {
            $hits = $this->cacheEngine->get($this->cacheName.'_hits_'.$key);
            $stats .= "$key => hits ($hits) objects (".join(',', $data).")\n";
        }
        //$this->cacheEngine->get($this->cacheName.'_hits_'.$queryMD5)
        return $stats;
    }

    /**
     * This method will remove all keys associated with this datastore and restart the collection of cache data.
     * @return void
     */
    public function refreshCache()
    {
        if (! $this->cacheObject) {
            $this->populateCache();
        }
        $this->ignoreCacheCopy = true;
        $queryHandle = $this->searchView()->select();
        foreach ($this->cacheObject as $id => $unused) {
            $queryHandle->orWhere('id', $id);
        }
        $array = $queryHandle->execute();
        $this->cacheObject = array();
        foreach ($array as $anObj) {
            $this->cacheObject[$anObj->id] = $anObj;
        }
        $this->ignoreCacheCopy = false;
        $this->updateCacheQuery();
    }

    /**
     * This is a callback method from the execute() call of the queryBuilder to do database integration and
     * transforming of column names etc.
     *
     * @param  Query  $query                The query object
     * @param  String $queryString          The query string to prepare statement for
     * @param  String $queryParameters      The parameters for this query
     * @param  Boolean $returnAsEntityObject True to return objects, false to return associative array.
     *
     * @return Array of objects or associative array
     */
    protected function onPrepareRunQuery($queryString, $queryParameters, $returnAsEntityObject)
    {
        $objects = null;
        if ($returnAsEntityObject) {
            $objects = $this->getObjectsFromCache($queryString, $queryParameters);
        }
        return $objects;
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
        if ($this->ignoreCacheCopy) {
            return;
        }
        if (! preg_match('/join/', $queryString) && preg_match('/select \\*/', $queryString) &&
            is_array($data) && count($data) && is_object($data[0])) {
            $this->processQueryCache($queryString, $queryParameters, $data);
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
        $this->cacheQuery = array();
        $tmp = $this->cacheEngine->get($this->cacheName);
        if (strlen($tmp)) {
            $this->cacheQuery = json_decode($tmp, true);
        }
    }

    /**
     * Checks for cached copy of data and use it if available.
     * @param  string $field field name to get data from
     * @param  string $value value corresponding to the content of the field specified to get.
     * @return IEntity|null        returns the object if available.  null if not.
     */
    private function getObjectsFromCache($queryString, $queryParameters)
    {
        if (! is_array($queryParameters)) {
            $queryParameters = array();
        }
        $resultObj = null;
        if (! $this->cacheQuery) {
            $this->populateCache();
        }
        if (preg_match('/where (.*)/', $queryString, $matches)) {
            $queryMD5 = $matches[0] . '|' . implode('|', $queryParameters);
            if (isset($this->cacheQuery[$queryMD5])) {
                $resultSet = $this->cacheQuery[$queryMD5];
                $resultObj = array();
                foreach ($resultSet as $id) {
                    $anObject = null;
                    if (isset($this->cacheObject[$id])) {
                        $anObject = $this->cacheObject[$id];
                    } else {
                        $cacheObjTxt = $this->cacheEngine->get($this->cacheName.'_'.$id);
                        if (strlen($cacheObjTxt)) {
                            $anObject = $this->create()->fromCache($cacheObjTxt);
                            $this->cacheObject[$id] =$anObject;
                        }
                    }
                    if (! $anObject) {
                        $anObject = $this->getById($id);
                        $this->cacheObject[$id] =$anObject;
                    }
                    $resultObj[] = $anObject;
                }
                // if(!$this->cacheEngine->increment($this->cacheName.'_hits_'.$queryMD5, 1, 3600)) {
                // 	$this->cacheEngine->add($this->cacheName.'_hits_'.$queryMD5, 0, 3600);
                // }
            }
        }
        return $resultObj;
    }

    /**
     * Update the cache data
     * @return void
     */
    private function updateCacheQuery()
    {
        $timeoutCount = 5;
        while (! $this->cacheEngine->waitForLock('queryCacheUpdateLock_'.$this->cacheName, 1, 5, 5) && $timeoutCount--) {
        }
        $this->cacheEngine->set($this->cacheName, json_encode($this->cacheQuery), 0);
        $this->cacheEngine->unlock('queryCacheUpdateLock_'.$this->cacheName);
    }

    /**
     * MEthod to process the cache data and map them to the query
     * @param  String    $queryString     Query string that results in the fetched results
     * @param  array     $queryParameters Parameters provided to the query string resulting in the objects
     * @param  IEntity[] $objectResults   Data from the query made.
     * @return void
     */
    private function processQueryCache($queryString, $queryParameters, $objectResults)
    {
        if ($this->maxObjectToCachePerQuery < count($objectResults)) {
            $this->logWarning(__CLASS__ . " for " . get_class($objectResults[0]) . " has exceeded max caching requirement for query $queryString");
            return;
        }
        if (! is_array($queryParameters)) {
            $queryParameters = array();
        }
        if ('UPDATE_DATA' == $queryString || 'NEW_DATA' == $queryString || 'DELETE_DATA' == $queryString) {
            $newIndexCopy = array();
            $hasChanges = false;
            foreach ($objectResults as $anObject) {
                if (! $anObject || 0 == $anObject->id) {
                    continue;
                }
                $affectedId = $anObject->id;
                if ('DELETE_DATA' == $queryString) {
                    $this->cacheEngine->delete($this->cacheName.'_'.$anObject->id);
                    unset($this->cacheObject[$anObject->id]);
                } else {
                    $this->cacheEngine->set($this->cacheName.'_'.$anObject->id, $anObject->toCache());
                    $this->cacheObject[$anObject->id] = $anObject;
                }
            }
            if ('NEW_DATA'== $queryString || 'UPDATE_DATA'== $queryString) {
                //reset the cache
                $this->cacheQuery = array();
                $this->updateCacheQuery();
            } elseif ('DELETE_DATA' == $queryString) {
                foreach ($this->cacheQuery as $key => $value) {
                    if (in_array($affectedId, $value)) {
                        $hasChanges = true;
                        $newValue = array();
                        //remove the item from the query cached result
                        foreach ($value as $key => $id) {
                            if ($entityItem->id != $id) {
                                $newValue[$key] = $id;
                                break;
                            }
                        }
                        if (count($newValue)) {
                            $newIndexCopy[$key] = $newValue;
                        }
                    } else {
                        $newIndexCopy[$key] = $value;
                    }
                }
                if ($hasChanges) {
                    $this->cacheQuery = $newIndexCopy;
                    $this->updateCacheQuery();
                }
            }
        } elseif (preg_match('/where (.*)/', $queryString, $matches)) {
            $queryMD5 = $matches[0] . '|'.join('|', $queryParameters);
            $queryResults = array();
            foreach ($objectResults as $anObject) {
                $queryResults[] = $anObject->id;
                $this->cacheEngine->set($this->cacheName.'_'.$anObject->id, $anObject->toCache());
                $this->cacheObject[$anObject->id] = $anObject;
            }
            $this->cacheQuery[$queryMD5] = $queryResults; //results
            $this->updateCacheQuery();
        }
    }
}

