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
Use Snap\rediscacher;
Use Snap\store\dbdatastore;
Use Snap\InputException;

/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class redisarraydbdatastore extends dbdatastore
{
    /**
     * Maximum number of results that we will cache for.  Otherwise better to get directly from DB
     */
    private $maxCacheItemPerQuery = 30;

    /**
     * Numbers of queries in the map before starting a cleanup process to remove stale ones.
     * @var integer
     * @var integer
     */
    private $cleanupQueryCountThreshold = 150;

    /**
     * How long to wait before running the cleanup again if query cache count exceeds $cleanupQueryCountThreshold
     * all the time.
     * @var integer
     */
    private $cleanupCycleExpiryInSeconds = 600;

    /**
     * Default timeout for all the object keys that are kept in DB.  0 or negative value means no expirt.
     * @var integer
     */
    private $defaultKeyTimeout = 0;
    /**
     * ONly limit caching data from getByField() method.
     * @var boolean
     */
    private $onlyCacheFields = false;

    /**
     * The cacher object
     * @var Snap\cacher
     */
    private $cacher = null;

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

    //These 2 variables must be paired properly to use script methods.
    private static $luaScriptLoaded = [];
    private $luaQuickGetSha = '2d688297514e8f1d28ff1b552ec94235088d1d9f';
    private $luaQuickGet = <<<LUA
    local results={}
    local theKey=ARGV[1]..":"
    local theField=ARGV[2]
    local queryMapKey=theKey.."queryMap"
    local now=ARGV[3]
    local isExists = redis.call("HEXISTS", queryMapKey, theField)
    local queryMapCount = redis.call("HLEN", queryMapKey)
    if isExists == 1 then
        local data = redis.call("HGET", queryMapKey, theField);
        table.insert(results, data)
        table.insert(results, queryMapCount)
        local sep = "|"
        for str in string.gmatch(data, "([^"..sep.."]+)") do
            if redis.call("EXISTS", theKey..str) == 1 then
                table.insert(results, redis.call("GET", theKey..str));
            end
        end
        redis.call("HSET", theKey.."lastUsed", theField, now)
    end
    return results
LUA;

    /**
     * The contructor for this class.
     * @param redisCacher  $cacher                  The cache class that is used as temporary store
     * @param PDO          $dbHandle                     PDO object to use for execution of query
     * @param String       $table                        Table name to get and update data
     * @param String       $tableSuffix                  Suffix for the table
     * @param String       $columnPrefix                 Prefix for the column names
     * @param String       $itemClassName                Item class name that interface with this datastore
     * @param array        $views                        Array of views that can be associated with datastore
     * @param array        $relatedStore                 Array of name => storeObject instance related to thsi store.
     * @param boolean      $bUseTransactions             Whether to enable transactions
     * @param boolean      $forceInsertRecord            Whether to force inserting a record for update if it is not available yet.
     * @param integer      $cleanupQueryCountThreshold   Numbers of queries in the map before starting a cleanup process to remove stale ones.
     * @param integer      $cleanupCycleExpiryInSeconds  How long to wait before running the cleanup again reached $cleanupQueryCountThreshold threshold
     * @param integer      $maxCacheItemPerQuery         Maximum number of results that we will cache for.  Otherwise better to get directly from DB
     */
    public function __construct(
        \Snap\redisarraycacher $cacher,
        PDO $dbHandle,
        $table,
        $tableSuffix,
        $columnPrefix,
        $itemClassName,
        $views = array(),
        $relatedStores = array(),
        $bUseTransactions = true,
        $forceInsertRecord = false,
        $defaultKeyTimeout = 0,
        $cleanupQueryCountThreshold = 150,
        $cleanupCycleExpiryInSeconds = 600,
        $maxCacheItemPerQuery = 30
    ) {
        assert(new $itemClassName($this, array()) instanceof \Snap\ICacheable);
        $this->cacher = $cacher;
        if (count($views)) {
            $this->hasView = true;
        }
        $this->cleanupQueryCountThreshold = $cleanupQueryCountThreshold;
        $this->cleanupCycleExpiryInSeconds = $cleanupCycleExpiryInSeconds;
        $this->maxCacheItemPerQuery = $maxCacheItemPerQuery;

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
            //reset all information.  Needs to be collected again.
            $redisCommand = [
                ['del', [$this->makeCacheKey($entityItem->id)]],
                ['del', [$this->makeCacheKey('queryMap')]],
                ['del', [$this->makeCacheKey('lastUsed')]]
            ];
            $this->cacher->runBulkCommands($redisCommand);
            unset($this->cacheObject[$entityItem->id]);
            $this->cacheQuery = [];
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
        $object = null;
        if (! $this->ignoreCacheCopy) {
            if ('id' == $column) {
                if (isset($this->cacheObject[$value])) {
                    $object =  $this->cacheObject[$value];
                }/* else {
                    $data = $this->cacher->get($this->makeCacheKey($value));
                    if (0 < strlen($data)) {
                        $object = $this->create()->fromCache($data);
                        $this->cacheObject[$value] = $object;
                    }
                }*/
            } else {
                $object = $this->getObjectsFromCache('where getByField', array($column,$value));
            }
        }
        if (! $object) {
            $object = parent::getByField($column, $value, $fields, $forUpdate, $tableOrViewIndex);
            if (0 == count($fields) && $object) {
                $this->cacher->set($this->makeCacheKey($object->id), $object->toCache(), $this->defaultKeyTimeout);
                if ('id' != $column) {
                    $this->processQueryCache('where getByField', array($column,$value), array($object));
                }
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
        $this->ignoreCacheCopy = true;
        $object = parent::save($entityItem, $updateFields, $lockRecord);
        if ($object) {
            //Added by Devon on 2018/8/22 to re-initialise another object so that cached data within the object can be properly
            //populated since the object is created in dbdatastore::onSaveExecuted() before subsequent calls to the
            //snapObject::onCompletedUpdate()
            $object = $this->create($object->toArray());
            //End Add by Devon on 2018/8/22
            if ($this->hasView) {
                $viewObject = parent::getById($object->id, [], false, 1 /* default to first view */);
                if ($viewObject) {
                    $object = $viewObject;
                }
            }
            //Updated by Devon on 2019/04/27 to also delete all queryMap in case the conditions used
            //to get the object would have resulted in different datasets due to these changes.
            $redisCommand = [
                ['set', [$this->makeCacheKey($object->id), $object->toCache()]],
                ['expire', [$this->makeCacheKey($object->id) , $this->defaultKeyTimeout]],
                ['del', [$this->makeCacheKey('queryMap')]],
                ['del', [$this->makeCacheKey('lastUsed')]]
            ];
            $this->cacher->runBulkCommands($redisCommand);
            unset($this->cacheObject[$entityItem->id]);
            $this->cacheQuery = [];
            // $this->cacher->set($this->makeCacheKey($object->id), $object->toCache(), $this->defaultKeyTimeout);
            //End update 2019/04/27
            $this->cacheObject[$object->id] = $object;
        }
        $this->ignoreCacheCopy = false;
        return $object;
    }

    public function describeCache()
    {
        $tableStyleHTML = '';
        $rowStyleHTML = 'class="normal"';
        $redisCommand = [];
        $ids = [];
        $iterator = null;
        $instance = $this->cacher->_instance($this->cacher->_target($this->makeCacheKey('*')));
        $instance->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        do {
            // Scan for some keys
            $arr_keys = $instance->scan($iterator);
            // Redis may return empty results, so protect against that
            if (false !== $arr_keys) {
                foreach ($arr_keys as $key) {
                    if (preg_match('/^.*:([0-9]+)/', $key, $regs)) {
                        if (! isset($ids[$regs[1]])) {
                            $ids[] = $key;
                        }
                    }
                }
            }
        } while (0 < $iterator);

        $buffer = 'Caching Factory: ' . get_class($this) . '<br>Total in cache is ' . count($ids) . '<br>';
        $widthToConvertToTextArea = 30;
        if (0 == count($ids)) {
            return 'none';
        }
        $allStringItems = $instance->mget($ids);
        foreach ($allStringItems as $anItem) {
            $obj = $this->create()->fromCache($anItem);
            if (! isset($this->items[$obj->id])) {
                $this->items[$obj->id] = $obj;
            }
        }
        $columns = array();
        foreach ($this->items as $one) {
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
                if ($one->{$aColumn} instanceof \DateTime) {
                    $buffer .= "<td>".$one->{$aColumn}->format('Y/m/d H:i:s')."</td>";
                } elseif (strlen($one->{$aColumn}) > $widthToConvertToTextArea) {
                    $buffer .= "<td><textarea cols=30 rows=3>".htmlspecialchars($one->{$aColumn})."</textarea></td>";
                } else {
                    $buffer .= "<td>".htmlspecialchars($one->{$aColumn})."</td>";
                }
            }
        }
        if (0 < count($this->items)) {
            $buffer .= "</table>Factory Cache Query<br><table $tableStyleHTML><tr $rowStyleHTML><td>Conditions</td><td>Last USed</td><td>Object IDs for this query</td></tr>";
        }
        $counter = 0;
        $conditionsData = [];
        $cachedConditions = $instance->hgetall($this->makeCacheKey('queryMap'));
        $lastUsedConditions = $instance->hgetall($this->makeCacheKey('lastUsed'));
        $count = 1;
        foreach ($cachedConditions as $key => $aCondition) {
            $buffer .= "<tr $rowStyleHTML><td><textarea cols=30 rows=3>{$key}</textarea></td>";
            $buffer .= "<td>" . date('Y-m-d H:i:s', $lastUsedConditions[$key]) . "</td>";
            $buffer .= "<td>" . str_replace('|', ', ', $aCondition) . "</td></tr>";
        }
        $buffer .= "</table>";
        return $buffer;
    }

    /**
     * This method will remove all keys associated with this datastore and restart the collection of cache data.
     * @return void
     */
    public function refreshCache()
    {
        $this->cacheObject = array();
        $this->cacheQuery = array();
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
                $this->cacheName = $matches[1];
            } else {
                $this->cacheName = $itemClassName;
            }
            //Added by Devon on 2019/04/23 to ensure the lua script we use is available in the server instance
            $host = $this->cacher->_target("{{$this->cacheName}}");
            if (! isset(self::$luaScriptLoaded[$host]) || ! self::$luaScriptLoaded[$host]) {
                if (sha1($this->luaQuickGet) != $this->luaQuickGetSha) {
                    throw new InputException("The lua script and its sha1 does not match.  Please regenerate and set it as " . sha1($this->luaQuickGet), InputException::GENERAL_ERROR);
                }
                if ([1] != $this->cacher->_instance($host)->script('EXISTS', $this->luaQuickGetSha)) {
                    $this->cacher->_instance($host)->script('load', $this->luaQuickGet);
                }
                self::$luaScriptLoaded[$host] = true;
            }
            //End add 2019/04/23
            return parent::initialiseLazyLoad();
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
        if (preg_match('/where (.*)/', $queryString, $matches)) {
            if ('where getByField' == $queryString) {
                $queryMD5 = sprintf("%s:%s", $queryParameters[0], $queryParameters[1]);
            } elseif (! $this->onlyCacheFields) {
                $queryMD5 = $matches[0] . '|' . implode('|', $queryParameters);
            } else {
                return null;  //nothing found since not implemented
            }
            // $queryMD5 = md5($queryMD5) . '-' . strlen($queryMD5);
            $resultSet = null;
            if (isset($this->cacheQuery[$queryMD5])) {
                $resultSet = $this->cacheQuery[$queryMD5];
            } else {
                $redisResponse = $this->getMappedValues($queryMD5);
                if (is_array($redisResponse) && count($redisResponse)) {
                    $resultSet = explode('|', $redisResponse[0]);
                    for ($i = 2; $i < count($redisResponse); $i++) {
                        $obj = $this->create()->fromCache($redisResponse[$i]);
                        if (! isset($this->cacheObject[$obj->id])) {
                            $this->cacheObject[$obj->id] = $obj;
                        }
                    }
                    if ($this->cleanupQueryCountThreshold < $redisResponse[1]) {
                        $this->cleanCacheQuery();
                    }
                }
                /*$redisCommand = [
                    ['hget', [$this->makeCacheKey('queryMap'), $queryMD5]],
                    ['hlen', [$this->makeCacheKey('queryMap')]]
                ];
                $redisResponse = $this->cacher->runBulkCommands($redisCommand);
                if (0 < strlen($redisResponse[0])) {
                    //Return the results and update the last accessed time.
                    $this->cacher->hset($this->makeCacheKey('lastUsed'), $queryMD5, time());
                    if ($this->cleanupQueryCountThreshold < $redisResponse[1]) {
                        $this->cleanCacheQuery();
                    }
                    $resultSet = explode('|', $redisResponse[0]);
                } else {
                    unset($this->cacheQuery[$queryMD5]);
                }*/
            }
            if ($resultSet && count($resultSet)) {
                $resultObj = array();
                foreach ($resultSet as $id) {
                    $anObject = $this->getById($id);
                    if ($anObject && 0 < $anObject->id) {
                        $resultObj[] = $anObject;
                    } else {
                        $this->logWarning("redisarraydbdatastore::getOBjectsFromCache() unable to retrieve object of id $id");
                    }
                }
            }
        }
        return $resultObj;
    }

    /**
     * Update the cache data
     * @return void
     */
    public function cleanCacheQuery()
    {
        if ($this->cacher->setnx($this->makeCacheKey('cleanupProcess'), time())) {
            $this->logDebug(__CLASS__ . ": Running cleaning operation now for {$this->cacheName}");
            //run once every 10 minutes.
            $this->cacher->expire($this->makeCacheKey('cleanupProcess'), $this->cleanupCycleExpiryInSeconds);
            $lastUsedData = $this->cacher->hgetall($this->makeCacheKey('lastUsed'));
            $redisCommand = [];
            $now = time();
            foreach ($lastUsedData as $key => $lastUsed) {
                if ($this->expiryDuration < $now - $lastUsed) {
                    $this->logDebug(__CLASS__ . ": Clearing {$this->cacheName}:queryMap field $key due to last used " . $now - $lastUsed . " seconds ago");
                    $redisCommand[] = ['hdel', [$this->makeCacheKey('queryMap'), $key]];
                    $redisCommand[] = ['hdel', [$this->makeCacheKey('lastUsed'), $key]];
                }
            }
            $this->cacher->runBulkCommands($redisCommand);
        }
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
        if ($this->maxCacheItemPerQuery < count($objectResults)) {
            $this->logWarning(__CLASS__ . " for {$this->itemClassName} has exceeded max caching requirement for query $queryString");
            return;
        }
        if (! is_array($queryParameters)) {
            $queryParameters = array();
        }
        if (preg_match('/where (.*)/', $queryString, $matches)) {
            if ('where getByField' == $queryString) {
                $queryMD5 = sprintf("%s:%s", $queryParameters[0], $queryParameters[1]);
            } elseif (! $this->onlyCacheFields) {
                $queryMD5 = $matches[0] . '|'.join('|', $queryParameters);
            } else {
                return;  //skip caching.
            }
            // $queryMD5 = md5($queryMD5) . '-' . strlen($queryMD5);
            $queryResults = array();
            $redisCommand = [];
            foreach ($objectResults as $anObject) {
                $queryResults[] = $anObject->id;
                if (! isset($this->cacheObject[$anObject->id])) {
                    $redisCommand[] = ['setnx', [$this->makeCacheKey($anObject->id), $anObject->toCache()]];
                    if (0 < $this->defaultKeyTimeout) {
                        $redisCommand[] = ['expire', [$this->makeCacheKey($anObject->id), $this->defaultKeyTimeout]];
                    }
                    $this->cacheObject[$anObject->id] = $anObject;
                }
            }
            $redisCommand[] = ['hset', [$this->makeCacheKey('queryMap'), $queryMD5, join('|', $queryResults)]];
            $redisCommand[] = ['hset', [$this->makeCacheKey('lastUsed'), $queryMD5, time()]];
            $this->cacher->runBulkCommands($redisCommand);
            $this->cacheQuery[$queryMD5] = $queryResults; //results
        }
    }

    /**
     * This is internal method to format a proper cache key name for use.  This method has
     * 2 modes to work.  If no object is defined, it will format using the cache name only.
     * Otherwise it will format the key as a reference to object by its ID.
     *
     * @param  String  $field  Field name to include.
     * @param  IEntity $object The object to extract field name if applicable.
     * @return String          Formatted string
     */
    private function makeCacheKey($field, $object = null)
    {
        if (! $this->cacheName) {
            $this->initialiseLazyLoad();
        }
        $arg1 = $object ? $field : '{' . $this->cacheName . '}';
        if (is_object($object)) {
            $arg2 = $object->{$field};
        } elseif (null == $object) {
            $arg2 = $field;
        } else {
            $arg2 = $object;
        }
        return sprintf('%s:%s', $arg1, $arg2);
    }

    /**
     * Gets all relevant data from Redis directly with a query key search if available.
     * @param  string $fields condition string to search
     * @return Array      The first elemenet if not empty is the condition return values.  OThers are data mapped.
     */
    private function getMappedValues($fields)
    {
        $data = $this->cacher->runBulkCommands([
            ['evalsha', [$this->luaQuickGetSha, ["{{$this->cacheName}}", $fields, time()]]]
        ], $this->makeCacheKey('queryMap'));
        if (is_array($data)) {
            return $data[0];
        } else {
            return null;
        }
    }
}
?>