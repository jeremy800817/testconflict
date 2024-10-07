<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016-2018
 */
Namespace Snap;

Use Snap\ICacher;
Use Snap\InputException;
Use \Datetime;

/**
 * This represents the main interface class to a record in storage.
 *
 *
 * @author   Devon Koh <devon@silverstream.my>
 * @version  1.0
 * @package  snap.base
 */
class redisarraycacher implements ICacher
{
    Use \Snap\TLogging;

    /**
     * DB Identifier
     * @var integer
     */
    private $db = 0;

    /**
     * Actual connection to the redis
     * @var  predis\client
     */
    private $client = null;

    /**
     * Create an instance of the redisClient
     * @param [type] $config [description]
     */
    public function __construct($cachetype, $servers, $cacheId)
    {
        $serverConfig = [];
        $this->db = $cacheId;
        if(preg_match('/,/', $servers)) {
            $servers = explode(',', $servers);
        } else {
            $servers = array($servers);
        }
        // foreach ($servers as $aServer) {
        //     $aServer = explode(':', $aServer);
        //     $aServerConfig['host'] = $aServer[0];
        //     if (1 < count($aServer)) {
        //         $aServerConfig['port'] = $aServer[1];
        //     }
        //     $aServerConfig['database'] = $this->db;
        //     $aServerConfig['persistent'] = "1";
        //     $serverConfig[] = $aServerConfig;
        // }
        $this->client = new \RedisArray($servers, 
                        array(
			    "lazy_connect" => 1,
                            "retry_timeout" => 100,
                            "connect_timeout" => 5,
                            "read_timeout" => 30,
                            "consistent" => true,
			    "persistent" => true
                        ));
        $this->client->select($this->db);
    }

    /**
     * Gets a stored data by the key
     * @param  String $key The key that the data is stored in
     * @return String      Value
     */
    public function get($key)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $data = $this->client->get($key);
        if (false === $data) {
            $data = null;
        }
        if (is_string($data) && preg_match('/^To[ACS]\S|||/', $data)) {
            $parts = explode('|||', $data);
            switch ($parts[0]) {
                case 'ToSeRiAlIzE':
                    $data = unserialize($parts[1]);
                    break;
                case 'ToArRaY':
                    $data = new $parts[1];
                    $data->fromArray(json_decode($parts[2], true));
                    break;
                case 'ToCaChE':
                    $data = new $parts[1];
                    $data->fromCache($parts[2]);
                    break;
            }
        }
        return $data;
    }

    /**
     * Sets data by given the key
     * @param  String $key      The key that the data is stored in
     * @param  String $variable The data to store
     * @param  String $ttl      How long should the data be kept
     * @return boolean  True if successful.  False otherwise.
     */
    public function set($key, $variable, $ttl = 0)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        if (is_object($variable)) {
            if (method_exists($variable, 'toArray') && method_exists($variable, 'fromArray')) {
                $dataPart = json_encode($variable->toArray());
                $variable = 'ToArRaY|||' . get_class($variable) . '|||' . $dataPart;
            } elseif (method_exists($variable, 'toCache') || method_exists($variable, 'fromCache')) {
                $dataPart = $variable->toCache();
                $variable = 'ToCaChE|||' . get_class($variable) . '|||' . $dataPart;
            } else {
                $variable = 'ToSeRiAlIzE|||' . serialize($variable);
            }
        } elseif (is_array($variable)) {
            $variable = 'ToSeRiAlIzE|||' . serialize($variable);
        }
        if (0 < $ttl) {
            $return = $this->client->setex($key, $ttl, $variable);
        } else {
            $return = $this->client->set($key, $variable);
        }
        return $return;  //'OK' == $return->getPayload();
    }

    /**
     * Removes the key from storage
     * @param  String $key   The key identifying data to be purged.
     * @return boolean  True if successful.  False otherwise.
     */
    public function delete($key)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        return $this->client->del($key);
    }

    /**
     * Pessimistic locking and reserving for a key.

     * @param  String $key          The key that the data is stored in
     * @param  String $value        The data to store
     * @param  Number $ttl          How long should the data be kept
     * @param  Number $timeout      How long to wait for the reservation before timing out.
     * @return boolean.  True if successful reserved the key and set the value.  False otherwise.
     */
    public function waitForLock($key, $value, $ttl, $timeout = -1)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $startTime = time();
        do {
            if ($this->client->setnx($key, $value)) {
                $this->client->expire($key, $ttl);
                return true;
            }
            usleep(300000);
        } while ($timeout != -1 &&  (time() - $startTime) < $timeout);
        return false;
    }

    /**
     * Unlocks the key for use by other processes.  Pairs this with waitForLock()
     * @param  String $key          The key that the data is stored in
     */
    public function unlock($key)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $this->delete($key);
    }

    /**
     * Atomically sets adds a key
     * @param  String $key          The key that the data is stored in
     * @param  String $value        The data to store
     * @param  Number $ttl          How long should the data be kept
     */
    public function add($key, $value, $ttl)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $command = [
            ['setnx', [$key, $value]]
        ];
        if (0 < $ttl) {
            $command[] = ['expire', [$key, $ttl]];
        }
        $replies = $this->runBulkCommands($command);
        return 1 == $replies[0];
    }

    /**
     * Atomically increment a value that is stored
     * @param  String $key          The key that the data is stored in
     * @param  Number $amount       Increment or decrement by the amount set.
     *                               positive number means increment.  negative numbers means decrement.
     * @param  Number $ttl          How long should the data be kept
     */
    public function increment($key, $amount = 1, $ttl = 0)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $command = [
            ['incrbyfloat', [$key,$amount]]
        ];
        if (0 < $ttl) {
            $command[] = ['expire', [$key, $ttl]];
        }
        $replies = $this->runBulkCommands($command);
        return $replies[0];
    }

    /**
     * This method uses the cache interface to directly and in a thread safe manner increment values
     *
     * @param  mixed  	$entityOrId  Object or Id to increment for
     * @param  integer 	$decrementBy Amount to decrease by
     * @return integer              Value after subtracting
     */
    public function decrement($entityOrId, $decrementBy, $ttl = 0)
    {
        return $this->increment($entityOrId, -1 * $decrementBy, $ttl);
    }

    /**
     * Returns the actual instance of the underlying engine to implement engine specific actions.
     * @return Cache engine object.
     */
    public function getEngine()
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        return $this->client;
    }

    /**
     * Provides a quickway to run multiple commands at the same time.
     * @param  mixed $callbackFunction   This parameter should be Array of array of commands and its argument to run.  E.g.
     *                                           [
     *                                               ['exists', ['somedata']],
     *                                               ['set', ['myfield', 'myData']],
     *                                               ['get', ['myfield']]
     *                                           ]
     *
     * @return mixed.                       Return data form the commands ran.
     */
    public function runBulkCommands(Array $callbackFunction, $cacheName = null)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $returnData = [];
        $host = null;
        $keyTarget = $cacheName ? $cacheName : $callbackFunction[0][1][0];
        if (preg_match('/^{.*}(:.*)?/', $keyTarget)) {
            $host = $this->client->_target($keyTarget);
        }
        $pipeline = ($host ? $this->client->multi($host, \Redis::PIPELINE) : $this->client);
        foreach ($callbackFunction as $aCommand) {
            $returnData[] = call_user_func_array(array($pipeline, $aCommand[0]), $aCommand[1]);
        }
        if ($host) {
            $this->logDebug(__CLASS__.":runBulkCommands() Using pipeline to execute batch received");
            return $pipeline->exec();
        } else {
            $this->logDebug(__CLASS__.":runBulkCommands() ran all commands normally");
            return $returnData;
        }
    }

    /**
     * Magic function to wrap all the Predis\client methods
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->client, $method), $arguments);
    }
}
?>