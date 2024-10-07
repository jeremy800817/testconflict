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
class memcacheCacher implements ICacher
{
    Use \Snap\TLogging;

    /**
     * Module types supported
     */
    public const MODULE_MEMCACHE = 1;
    public const MODULE_MEMCACHED = 2;

    /**
    * The caching module used for shared memory caching mechanism (refer to constant MX_CACHEMODULE_*)
    * @var integer
    */
    private $module = self::MODULE_MEMCACHE;

    /**
    * Unique ID for the cache session
    *
    * @var string
    */
    private $id = '';

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
        if ('memcache' == $cachetype) {
            $this->module = self::MODULE_MEMCACHE;
            $this->client = new \Memcache();
        } else {
            $this->module = self::MODULE_MEMCACHED;
            $this->client = new \Memcached();
            $this->client->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->client->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
        }
        $serverConfig = [];
        $this->db = $cacheId;
        $servers = explode(',', $servers);
        foreach ($servers as $aServer) {
            $aServer = explode(':', $aServer);
            $host = $aServer[0];
            $port = $aServer[1];
            if (self::MODULE_MEMCACHE == $this->module) {
                $this->client->addServer($host, $port);
                $this->log('Added memcached server "'.$host.':'.$port.'" to the caching pool.', SNAP_LOG_DEBUG);
            } else {
                $serverConfig[] = array( $host, $port);
            }
        }
        if (count($serverConfig)) {
            $this->client->addServers($serverConfig);
        }
    }

    /**
     * Gets a stored data by the key
     * @param  String $key The key that the data is stored in
     * @return String      Value
     */
    public function get($key)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $data = null;
        $key = $this->id.'|'.$key;
        $this->logDebug('Getting cache for "'.$key.'"');
        $data = $this->client->get($key);
        if (false === $data) {
            $data = null;
        }
        if (null !== $data && ! is_numeric($data)) {
            $data = unserialize($data);
        } elseif (null == $data) {
            $this->logDebug('Failed getting cache for "'.$key.'"');
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
        $data = serialize($variable);
        $key = $this->id.'|'.$key;
        $ttl = intval($ttl);
        $this->logDebug('Storing "'.$key.'" ('.strlen($data).')...');
        if (self::MODULE_MEMCACHE == $this->module) {
            $res = $this->client->replace($key, $data, 0, $ttl);
            if (false == $res) {
                $res = $this->client->set($key, $data, 0, $ttl);
            }
        } else {
            $res = $this->client->replace($key, $data, $ttl);
            if (false == $res) {
                $res = $this->client->set($key, $data, $ttl);
            }
        }
        $this->keys[$key] = array('creation_time' => time(), 'ttl' => $ttl);
        if (! $res) {
            $this->log('Unable to create cache for "'.$key.'"', SNAP_LOG_DEBUG);
        }
        return $res;
    }

    /**
     * Removes the key from storage
     * @param  String $key   The key identifying data to be purged.
     * @return boolean  True if successful.  False otherwise.
     */
    public function delete($key)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        $result = false;
        if (false === strstr($key, $this->id.'|')) {
            $key = $this->id.'|'.$key;
        }
        $this->log('Removing cache for "'.$key.'"', SNAP_LOG_DEBUG);
        $result = $this->client->delete($key);
        if ($result) {
            unset($this->keys[$key]);
        }
        return $result;
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
            if ($this->add($key, $value, $ttl)) {
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
        $key = $this->id.'|'.$key;
        $this->client->delete($key);
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
        $key = $this->id.'|'.$key;
        if (self::MODULE_MEMCACHE == $this->module) {
            if ($this->client->add($key, $value, false, $ttl)) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($this->client->add($key, $value, $ttl)) {
                return true;
            } else {
                return false;
            }
        }
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
        $key = $this->id.'|'.$key;
        $funcName = (0 < $amount) ? 'increment' : 'decrement';
        $params[] = $key;
        $params[] = ((0 < $amount) ? $amount : -1 * $amount);
        if (self::MODULE_MEMCACHED == $this->module) {
            $params[] = 0;
            $params[] = $expiry;
        }
        return call_user_func_array(array($this->client, $funcName), $params);
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
     * @param  function $callbackFunction   The function should take one parameter that is the actual engine object.
     * @return mixed.                       Return data form the commands ran.
     */
    public function runBulkCommands(Array $callbackFunction, $cacheName = null)
    {
        assert(null != $this->client, 'The Redis client is not initialised');
        return $callbackFunction($this);
    }
}
?>