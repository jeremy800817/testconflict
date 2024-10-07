<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\store;

Use PDO;
use PDOException;
use PDORow;
Use Snap\IObservable;
Use Snap\IEntityStore;
Use Snap\IEntity;
Use Snap\App;
Use Snap\InputException;
Use \ClanCats\Hydrahon\Builder;
use \ClanCats\Hydrahon\BaseQuery;
Use \ClanCats\Hydrahon\Query\Sql;
Use \ClanCats\Hydrahon\Query\Sql\Select;
Use \ClanCats\Hydrahon\Query\Sql\Exists;
Use \ClanCats\Hydrahon\Query\Sql\Func;
use \ClanCats\Hydrahon\Query\Expression;

//Declare a special handling for returning of snapObject data to support other
//selection methods like count(), min(), max(), find() etc
Builder::extend('snap-mysql', 'Snap\\override\\hydrahon\\snapsql', '\\ClanCats\\Hydrahon\\Translator\\Mysql');

/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class dbdatastore implements IEntityStore, IObservable
{
    Use \Snap\TLogging;
    Use \Snap\TObservable;

    protected $app;
    /**
     * The database handle
     * @var PDO database handle
     */
    protected $dbHandle;

    /**
     * Array of the view names that are available for this datastore
     * @var Array List of view names
     */
    private $views = array();

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
     * The prefix to attach to a column name
     * @var String
     */
    private $columnPrefix;

    /**
     * Array storing the column names (without the prefix) for table
     * @var Array
     */
    private $columnInfo = array();

    /**
     * Array storing the columns for views (without the prefix)
     * @var String
     */
    private $viewColumnInfo = array();

    /**
     * The entity object class name to instantiates and interact with
     * @var String
     */
    protected $itemClassName;

    /**
     * Whether to use transaction to manage this datastore
     * @var Boolean
     */
    protected $useTransaction;

    /**
     * Container to store all other related data store.
     * @var array
     */
    private $relatedStores = array();

    /**
     * Force insert if record not available for update
     */
    private $forceInsert = false;

    /**
     * Query builder
     * @var \ClanCats\Hydrahon\Builder
     */
    private $queryBuilder = null;

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
    public function __construct(
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
        $this->app = \Snap\App::getInstance();
        $this->dbHandle = $dbHandle;
        $this->tableName = $table;
        $this->columnPrefix = (0 == strlen($columnPrefix) ? '' : ($columnPrefix . '_'));
        $this->tableSuffix = (0 == strlen($tableSuffix) ? '' : ('_' . $tableSuffix));
        $this->itemClassName = $itemClassName;
        $this->views = $views;
        $this->useTransaction = $bUseTransactions;
        $this->relatedStores = $relatedStores;
        $this->forceInsert = $forceInsertRecord;

        return $this;
    }

    /**
     * This method allows users to add related stores into this store object to facilitate
     * getting related stores easily to do queries.  Useful for relationships of type n..n
     */
    public function addRelatedStore($storeName, IEntityStore $storeInstance)
    {
        if ($storeInstance->getTableName() != $this->getTableName()) {
            $this->relatedStores[$storeName] = $storeInstance;
            return true;
        }
        return false;
    }

    /**
     * This method returns the store that is child to this store.
     *
     * @param  String $storeName Key of the store to get
     * @return IEntityStore datastore object
     */
    public function getRelatedStore($storeName)
    {
        if (isset($this->relatedStores[$storeName])) {
            if (is_object($this->relatedStores[$storeName])) {
                return $this->relatedStores[$storeName];
            } elseif (is_string($this->relatedStores[$storeName]) &&
                    (preg_match('/(.*)(lazystore|lazyfactory)$/i', trim($this->relatedStores[$storeName]), $matches) ||
                     preg_match('/(.*)(store|factory)$/i', trim($this->relatedStores[$storeName]), $matches))) {
                $this->relatedStores[$storeName] = App::getInstance()->getStore($matches[1], $this);
                return $this->relatedStores[$storeName];
            }
        }
        return null;
    }

    /**
     * Returns the table name used by this datastore
     * @return String
     */
    public function getTableName()
    {
        return $this->tableName .  $this->tableSuffix;
    }

    /**
     * Returns the column prefix used for fields
     * @return String
     */
    public function getColumnPrefix()
    {
        return $this->columnPrefix;
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
    public function save(IEntity $entityItem, $updateFields = array(), $lockRecord = true)
    {
        $this->initialiseLazyLoad();
        $startedTransaction = false;
        $insertRecordWithID = false;  //Insert record with ID
        //1.  Check if the object is valid state and can proceed to be updated / created.
        if (! $entityItem instanceof $this->itemClassName) {
            throw new InputException(__CLASS__."::save() method can only accept objects of class {$this->itemClassName} instead of " . get_class($entityItem), InputException::GENERAL_ERROR);
        }
        if (! $entityItem->isValid()) {
            throw new InputException(gettext('Some field values has been left empty of is incorrect'), InputException::FIELD_ERROR);
        }
        if (! $this->dbHandle->inTransaction() && $this->useTransaction) {
            $startedTransaction = true;
            $this->dbHandle->beginTransaction();
        }
        if (0 < $entityItem->id /*&& $lockRecord*/) {
            $fields[] = 'id';
            if (in_array('modifiedon', $this->columnInfo, true)) {
                $fields[] = 'modifiedon';
            }
            $object = $this->getById($entityItem->id, $fields, $lockRecord);

            if ($object && $object->modifiedon > $entityItem->modifiedon) {
                $logInfo = sprintf(
                    "Attempting to save an outdated item ID %d in table %s DB modified on %s and object modified on %s.",
                    $entityItem->id,
                    $this->tableName,
                    $object->modifiedon instanceof \Datetime ? $object->modifiedon->format('Y-m-d H:i:s') : $object->modifiedon,
                    $entityItem->modifiedon instanceof \DateTime ? $entityItem->modifiedon->format('Y-m-d H:i:s') : $entityItem->modifiedon
                );
                $this->log($logInfo, SNAP_LOG_ERROR);
                throw new InputException(gettext("You are attempting to save an old or outdated version of the record.  Please refresh to get latest version and try again."), InputException::GENERAL_ERROR);
            } elseif (! $object) {
                $insertRecordWithID = true;
            }  //Item has ID but the ID not in DB
        }
        //2.  Communicate with the entity that we are preparing to update the item.  This allows the object to prepare
        //    itself to also save its associated objects.
        if (! $entityItem->onPrepareUpdate()) {
            if ($startedTransaction) {
                $this->dbHandle->rollBack();
            }
            return null;
        }
        //3.  Prepare the query builder class to work the entity.
        $me =  $this;
        $queryBuilder = new Builder('snap-mysql', function ($query, $queryString, $queryParameters) Use ($startedTransaction, $entityItem, $me) {
            //Callback to process the statement.  Quirks of this hydrahon framework.
            return $me->onSaveExecuted($query, $queryString, $queryParameters, $entityItem, $startedTransaction);
        });
        //4.  Prepare compulsory fields to be updated with timestamp.
        $now = new \DateTime("now", $this->app->getserverTimezone());
        $now = $now->format("Y-m-d H:i:s");
        if ($this->app->isSession()) {
            $userId = $this->app->getUserSession()->getUserId();
        } else {
            $userId = 0;
        }
        
        $insertArr = array();
        if (0 < $entityItem->id && 0 < ! $insertRecordWithID) {
            $queryBuilder=$queryBuilder->table($this->getTableName())->update()->where($this->columnPrefix.'id', $entityItem->id);
            if (in_array('modifiedon', $this->columnInfo, true)) {
                $queryBuilder=$queryBuilder->set($this->columnPrefix.'modifiedon', $now);
            }
            if (in_array('modifiedby', $this->columnInfo, true)) {
                $queryBuilder=$queryBuilder->set($this->columnPrefix.'modifiedby', $userId);
            }
        } else {
            if (in_array('createdon', $this->columnInfo, true)) {
                $insertArr[$this->columnPrefix.'createdon']=$now;
            }
            if (in_array('createdby', $this->columnInfo, true)) {
                $insertArr[$this->columnPrefix.'createdby']=$userId;
            }
            if (in_array('modifiedon', $this->columnInfo, true)) {
                $insertArr[$this->columnPrefix.'modifiedon']=$now;
            }
            if (in_array('modifiedby', $this->columnInfo, true)) {
                $insertArr[$this->columnPrefix.'modifiedby']=$userId;
            }
        }
        $fieldsToUpdate=count($updateFields) ? $updateFields : $this->columnInfo;
        foreach ($fieldsToUpdate as $aField) {
            if ((! $insertRecordWithID && 'id' == $aField) || 'createdon' == $aField || 'modifiedon' == $aField || 'createdby' == $aField || 'modifiedby' == $aField) {
                continue;
            }
            if (($insertRecordWithID || 0 == $entityItem->id) && null !== $entityItem->{$aField}) {
                $insertArr[$this->columnPrefix . $aField] = $this->mapObjectDataToColumn($queryBuilder, $aField, $entityItem->{$aField});
            } elseif (null !== $entityItem->{$aField}) {
                $queryBuilder = $queryBuilder->set($this->columnPrefix.$aField, $this->mapObjectDataToColumn($queryBuilder, $this->columnPrefix . $aField, $entityItem->{$aField}));
            }
        }
        if (count($insertArr)) {
            return $queryBuilder->table($this->getTableName())->insert([$insertArr])->execute();
        } else {
            return $queryBuilder->execute();
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
        $dbHandle = $this->dbHandle;
        $startedTransaction = false;
        if (! $entityItem instanceof $this->itemClassName) {
            throw new InputException(_CLASS__.'::save() method can only accept objects of class {$this->itemClassName}'. InputException::GENERAL_ERROR);
        }
        if (! $this->dbHandle->inTransaction() && $this->useTransaction) {
            $startedTransaction = true;
            $this->dbHandle->beginTransaction();
        }
        try {
            if ($entityItem->onPredelete()) {
                $queryBuilder = new Builder('snap-mysql', function ($query, $queryString, $queryParameters) use ($entityItem, $startedTransaction) {
                    $statement = $this->dbHandle->prepare($queryString);
                    if (! $statement) {
                        $errorInfo = $statement->errorinfo();
                        $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
                        if ($startedTransaction) {
                            $this->dbHandle->rollBack();
                        }
                        throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
                    }
                    if (! $statement->execute($queryParameters)) {
                        $errorInfo = $statement->errorinfo();
                        $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
                        if ($startedTransaction) {
                            $this->dbHandle->rollBack();
                        }
                        throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
                    }
                    if ($startedTransaction) {
                        $this->dbHandle->commit();
                    }
                    return true;
                });
                return $queryBuilder->table($this->getTableName())->delete()->where($this->columnPrefix.'id', $entityItem->id)->execute();
            }
        } catch (\Exception $e) {
            if ($startedTransaction) {
                $this->dbHandle->rollBack();
            }
            throw $e;
        }
        return false;
    }

    /**
     * This method will get the object referenced by its ID.
     * @param  integer  $id              ID of the record to obtain
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $forUpdate        Whether to lock the record up for updating.
     * @param  integer $tableOrViewIndex Source of the data.  EIther from table or the availavle views.
     *                                      0 - defaults to table.  1 - first view, 2 - second view etc
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getById($id, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    {
        return $this->getByField('id', $id, $fields, $forUpdate, $tableOrViewIndex);
    }

    /**
     * This method will get the object by one of the unique field
     * @param  String  $column          The field name to search for
     * @param  integer  $id              value of the field
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $forUpdate        Whether to lock the record up for updating.
     * @param  integer $tableOrViewIndex Source of the data.  EIther from table or the availavle views.
     *                                      0 - defaults to table.  1 - first view, 2 - second view etc
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getByField($column, $value, $fields = array(), $forUpdate = false, $tableOrViewIndex = 0)
    {
        $this->initialiseLazyLoad();
        if (! in_array($column, $this->columnInfo, true)) {
            throw new InputException(sprintf("The field name %s is not in this object (%s)", $column, $this->itemClassName), InputException::GENERAL_ERROR);
        }

        //1.  Select the table or views to get data from
        if (0 < $tableOrViewIndex && count($this->views) >= $tableOrViewIndex) {
            $selectSource = $this->views[--$tableOrViewIndex];
        } else {
            $selectSource = $this->getTableName();
        }
        //Selected fields criteria
        if (count($fields)) {
            $selectFields = $this->columnPrefix . implode(", {$this->columnPrefix}", $fields);
        } else {
            $selectFields = '*';
        }
        //Manually prepare the statement and run
        $sql = sprintf(
            "select %s from `%s` where %s = %s %s",
                                $selectFields,
                                $selectSource,
                                $this->columnPrefix.$column,
                                $this->dbHandle->quote($value),
                                ($forUpdate ? ' FOR UPDATE' : '')
        );
        $statement = $this->dbHandle->query($sql);
        if (! $statement) {
            $errorInfo = $this->dbHandle->errorInfo();
            $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
            throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
        }
        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        if (null == $row) {
            return null;
        }
        $entity = new $this->itemClassName($this, $this->mapColumnToAttr($row));
        $this->onCompletedQuery($sql, '', array($entity));
        return $entity;
    }

    /**
     * This method will return a hydrahon query builder to start a custom query for objects.  Complete
     * the query by calling the method execute().  See the website below for more details
     * https://github.com/ClanCats/Hydrahon
     *
     * @param  String  $tableAlias           The alias name for the current table
     * @param  boolean $returnAsEntityObject To return the data as object (true) or associative arrays (false)
     * @return IEntity[]|null of objects of associative array containing the required data.
     */
    public function searchTable($returnAsEntityObject = true)
    {
        return $this->searchBuilder(0, $returnAsEntityObject);
    }

    /**
     * This method will return a hydrahon query builder to start a custom query for objects.  Complete
     * the query by calling the method execute().  See the website below for more details
     * https://github.com/ClanCats/Hydrahon
     *
     * @param  boolean $returnAsEntityObject To return the data as object (true) or associative arrays (false)
     * @param  integer $viewNo               Which source to get data from. 0 = table, 1 = first view, 2 = second view etc
     * @return IEntity[]|null of objects of associative array containing the required data.
     */
    public function searchView($returnAsEntityObject = true, $viewNo = 1)
    {
        return $this->searchBuilder($viewNo, $returnAsEntityObject);
    }

    /**
     * This method will return a hydrahon query builder to start a custom query for objects.  Complete
     * the query by calling the method execute().  See the website below for more details
     * https://github.com/ClanCats/Hydrahon
     *
     * @param  integer $tableOrViewIndex               Which source to get data from. 0 = table, 1 = first view, 2 = second view etc
     * @param  String  $tableAlias           The alias name for the current table
     * @param  boolean $returnAsEntityObject To return the data as object (true) or associative arrays (false)
     * @return Array of objects of associative array containing the required data.
     */
    public function searchBuilder($tableOrViewIndex = 0, $returnAsEntityObject = true)
    {
        $me = $this;
        $this->queryBuilder = new Builder('snap-mysql', function ($query, $queryString, $queryParameters) use ($me, $returnAsEntityObject) {
            return $me->queryBuilderCallbackExec($query, $queryString, $queryParameters, $returnAsEntityObject);
        });
        $theTableViewName = (0 == $tableOrViewIndex || count($this->views) < $tableOrViewIndex) ? $this->getTableName() : $this->views[$tableOrViewIndex-1];
        return $this->queryBuilder->table($theTableViewName);
    }

    /**
     * This method will run through all the column names and append a prefix to the name if it
     * is available for the object's member or view member
     *
     * @param  string $field Column name provided.
     * @return string        new column name
     */
    private function reformatColumnName($field)
    {
        if (0 < strlen($this->columnPrefix) && is_string($field) &&
            (in_array($field, $this->columnInfo) || in_array($field, $this->viewColumnInfo))) {
            $field = $this->columnPrefix . $field;
        }
        return $field;
    }

    /**
     * Run through all attributes in the query object and converts columns and append the column prefix
     * string to columns that does not have yet been prefixed.
     *
     * @param  BaseQuery $query Query object to operate on
     */
    private function processQueryForColumnPrefix(BaseQuery $query)
    {
        //All class attributes (member variable name) and is mapping to the field column that we should check.
        $attributeInfo = ['fields' => '0', 'wheres' => 1, 'groups' => 'val', 'orders' => 'key',
                          'joins' => 2, 'select' => 'var', 'ons' => 1];
        $allObjectVariables = $query->attributes();
        foreach ($attributeInfo as $fieldKey => $pos) {
            if (isset($allObjectVariables[$fieldKey])) {
                if ('var' == $pos) {
                    $tmp = $allObjectVariables[$fieldKey];
                    $allObjectVariables[$fieldKey] = [ $tmp ];
                }
                $overwrite = [];
                foreach ($allObjectVariables[$fieldKey] as $key => $value) {
                    $compare = null;
                    if ('var' == $pos || 'val' == $pos) {
                        $compare = $value;
                    } elseif ('key' == $pos) {
                        $compare = $key;
                    } else {
                        $compare = $value[intval($pos)];
                    }
                    if ($compare instanceof Func) {
                        $args = [ $compare->name() ];
                        foreach ($compare->arguments() as $aField) {
                            $args[] = $this->reformatColumnName($aField);
                        }
                        $r = new \ReflectionClass('\ClanCats\Hydrahon\Query\Sql\Func');
                        $compare = $r->newInstanceArgs($args);
                    } elseif ($compare instanceof BaseQuery) {
                        //Recursively process the sub-queries that are available
                        $this->processQueryForColumnPrefix($compare);
                    } else {
                        $compare = $this->reformatColumnName($compare);
                    }
                    if ('val' == $pos) {
                        $overwrite[$key] = $compare;
                    } elseif ('var' == $pos) {
                        $overwrite = $compare;
                    } elseif ('key' == $pos) {
                        $overwrite[$compare] = $value;
                    } else {
                        $value[$pos] = $compare;
                        $overwrite[$key] = $value;
                    }
                }
                if ('var' == $pos || count($overwrite)) {
                    $query->overwriteAttributes([$fieldKey => $overwrite]);
                }
            }
        }
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
    private function queryBuilderCallbackExec($query, $queryString, $queryParameters, $returnAsEntityObject)
    {
        $this->initialiseLazyLoad();
        $this->processQueryForColumnPrefix($query);
        list($queryString, $queryParameters) = $this->queryBuilder->translateQuery($query);
        $data = array();
        //1. Make sure it is a select statement only
        if (! preg_match("/^select/", $queryString)) {
            throw Exception(__CLASS__ . ":queryBuilderCallbackExec()  The query builder callback is only able to execute for select queries");
        }
        //Added by Devon on 2018/8/20 to force it to return array if keywords count, sum, avg, exists
        if ($query instanceof \ClanCats\Hydrahon\Query\Sql\Exists ||
            (preg_match('/select (count|sum|avg|min|max)\(([`a-zA-Z_\*]+)\) from/i', $queryString))) {
            $returnAsEntityObject = false;
        }
        //End Add by Devon on 2018/8/20
        
        $data = $this->onPrepareRunQuery($queryString, $queryParameters, $returnAsEntityObject);
        if (! $data && false !== $data) {
            //4.  Preparing statements for execution
            if (defined('SNAP_APP_LOGSQL')) {
                $debug = print_r($queryParameters, true);
                $this->log(__CLASS__.":Completed SQL - $queryString with parameters " . $debug, SNAP_LOG_INFO);
            }
            $statement = $this->dbHandle->prepare($queryString);
            if (! $statement) {
                $errorInfo = $this->dbHandle->errorinfo();
                $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
                throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
            }
            //5.  Run the query
            if (! $statement->execute($queryParameters)) {
                $errorInfo = $statement->errorinfo();
                $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
                throw new \Exception("Error in SQL statement: " . $errorInfo[2] . " with SQL: ". $queryString);
            }
            //6. Data collection
            if ($query instanceof \ClanCats\Hydrahon\Query\Sql\FetchableInterface) {
                if (! $returnAsEntityObject) {
                    return $statement->fetchAll(\PDO::FETCH_ASSOC);
                }
                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    $entity = new $this->itemClassName($this, $this->mapColumnToAttr($row));
                    $data[] = $entity;
                }
                $this->onCompletedQuery($queryString, $queryParameters, $data);
                return $data;
            }
        }
        return $data;
    }

    /**
     * This method will convert object value on each particular field to a database understandable format.
     *
     * @param  String $field Field name
     * @param  Mixed  $data  Data
     * @return String        The data to be publish into the database record
     */
    protected function mapObjectDataToColumn($queryBuilder, $field, $data)
    {
        //Added by Devon on 2017/6/4 to accoutn for timezone mapping....
        if (is_string($data) && preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})[T]([0-9]{2}):([0-9]{2}):([0-9]{2})[\.+0-9:zZ]*$/', trim($data), $matches)) {
            if ('0000-00-00 00:00:00' != $data) {
                $dateObj = new \DateTime(trim($data), \Snap\App::getInstance()->getUserTimezone());
                $dateObj->setTimezone(\Snap\App::getInstance()->getserverTimezone());
                return $dateObj->format("Y-m-d H:i:s");
            }
            return sprintf("%d-%02d-%02d %02d:%02d:%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]);
        //End Add by Devon on 2017/6/4
        } elseif ($data instanceof \DateTime) {
            if (0 != $data->format('Y')) {
                $newDate = new \DateTime($data->format("Y-m-d\TH:i:s"), $this->app->getUserTimezone());
                $newDate->setTimezone(\Snap\App::getInstance()->getserverTimezone());
            } else {
                $newDate = $data;
            }
            return $newDate->format("Y-m-d H:i:s");
        } elseif (is_array($data) && array_key_exists('func', $data)) {
            return $queryBuilder->raw($data['func']);
        }
        return $data;
    }

    /**
     * This method is to transform database record column to object representation.  for instantiation to object
     *
     * @param  Arrau $rawDBRow  Associative array of the record in DB
     * @return Array of the polished data
     */
    protected function mapColumnToAttr($rawDBRow)
    {
        $transform = array();
        foreach ($rawDBRow as $column => $value) {
            //Added by Devon on 2017/6/4 to process the date/time values for local context
            if (is_string($value) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/', trim($value)) && '0000-00-00 00:00:00' != $value) {
                $dateObj = new \Datetime($value, \Snap\App::getInstance()->getserverTimezone());
                $dateObj->setTimezone(\Snap\App::getInstance()->getUserTimezone());
                $value = $dateObj;
            }
            //End Add by Devon on 2017/6/4
            if (0 == strlen($this->columnPrefix)) {
                $transform[$column] = $value;
            } elseif (substr($column, 0, 4) == $this->columnPrefix) {
                $transform[substr($column, 4)] = $value;
            }
        }
        return $transform;
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
        }
    }

    /**
     * Callback method for actually saving the data of objects
     * @param  HydrahonQuery $query              The hydrahon query object
     * @param  String $queryString         The query string provided
     * @param  Array $queryParameters      Array of parameters to pass to the statement
     * @param  IEntity $entityItem         The object that is to be saved / executed
     * @param  boolean $startedTransaction Check if the transaction has been started
     * @return IEntity   The newly created or updated object.  Null if error.
     */
    private function onSaveExecuted($query, $queryString, $queryParameters, $entityItem, $startedTransaction)
    {
        $this->log(__CLASS__.":START SQL - $queryString with parameters ", SNAP_LOG_INFO);
        if ($this->forceInsert && preg_match('/^update/', $queryString)) {
            $queryString = preg_replace(array('/^update /', '/where /'), array('replace into ', 'where '), $queryString);
        }
        $statement = $this->dbHandle->prepare($queryString);
        if (! $statement) {
            $errorInfo = $statement->errorinfo();
            if (defined('SNAP_APP_LOGSQL')) {
                $debug = print_r($queryParameters, true);
                $this->log(__CLASS__.":Error SQL - $queryString with parameters " . $debug, SNAP_LOG_INFO);
            }
            $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
            if ($startedTransaction) {
                $this->dbHandle->rollBack();
            }
            throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
        }
        if (! $statement->execute($queryParameters)) {
            $errorInfo = $statement->errorinfo();
            if (defined('SNAP_APP_LOGSQL')) {
                $debug = print_r($queryParameters, true);
                $this->log(__CLASS__.":Error SQL - $queryString with parameters " . $debug, SNAP_LOG_INFO);
            }
            $this->log("Error in SQL statement: " . $errorInfo[2], SNAP_LOG_ERROR);
            if ($startedTransaction) {
                $this->dbHandle->rollBack();
            }
            throw new \Exception("Error in SQL statement: " . $errorInfo[2]);
        }
        if (defined('SNAP_APP_LOGSQL')) {
            $debug = print_r($queryParameters, true);
            $this->log(__CLASS__.":Completed SQL - $queryString with parameters " . $debug, SNAP_LOG_INFO);
        }
        if (0 == $entityItem->id) {
            $newId = $this->dbHandle->lastInsertId();
        } else {
            $newId = $entityItem->id;
        }
        $object = $this->getById($newId);
        if ($object && 0 < $object->id) {
            if ($startedTransaction) {
                $this->dbHandle->commit();
            }
            $entityItem->onCompletedUpdate($object);
            $this->onCompletedQuery($queryString, $queryParameters, array($entityItem));
            $this->log(__CLASS__.":ENDED SQL - $queryString with parameters ", SNAP_LOG_INFO);
            return $entityItem;
        } else {
            $this->log("Error with datastore for " . get_class($object) . " was unable to get the id after add/edit operation", SNAP_LOG_ERROR);
            throw new \Exception("Error with datastore for " . get_class($object) . " was unable to get the id after add/edit operation.");
        }
        return null;
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
    }

    protected function onPrepareRunQuery($queryString, $queryParameters, $returnAsEntityObject)
    {
        return null;
    }
    /**
     * Returns the Database handle for quoting a string.
     * @return String  Quoted string
     */
    public function quoteData($raw)
    {
        return $this->dbHandle->quote($raw);
    }

    /**
     * This is just a diagnostic method to return the available stores that are related to this current store
     * @return array Array of the stores
     */
    public function getRelatedStoreList()
    {
        return array_keys($this->relatedStores);
    }
    
    /**
     * Checks if a stored procedure with the given name exists in the database.
     *
     * @param string $procedureName The name of the stored procedure.
     * @return bool Returns true if the stored procedure exists, false otherwise.
     */
    public function isStoreProcedureExist($procedureName) {
        $stmt = $this->dbHandle->prepare("SELECT COUNT(*) FROM information_schema.routines WHERE routine_type = 'PROCEDURE' AND routine_name = :name");
        $stmt->execute(['name' => $procedureName]);
        
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Calls a stored procedure with the given name and parameters.
     *
     * @param string $procedureName The name of the stored procedure.
     * @param int $id The ID parameter to pass to the stored procedure.
     * @param array $updateColumns An array of column-value pairs to pass to the stored procedure.
     * @return PDOStatement Returns the result of the stored procedure.
     */
    public function callStoreProcedure($procedureName, $id, $updateColumns)
    {
        $noEmptyValueArray = array_filter($updateColumns, function($value) {
            return !is_null($value) && $value !== ''; 
        });
        $strUpdateColumns = implode(',', array_map(function ($column, $value, $prefix) {
            return $prefix . "{$column}='{$value}'";
        }, array_keys($noEmptyValueArray), array_values($noEmptyValueArray), array_fill(0, count($noEmptyValueArray), $this->getColumnPrefix())));
        if (0 == strlen($strUpdateColumns)) return 'no new data no update.';
        $statement = $this->dbHandle->prepare("CALL {$procedureName}(:id, \"{$strUpdateColumns}\")");
        $statement->execute(['id' => $id]);
        
        return $statement;
    }

}
?>