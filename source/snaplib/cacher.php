<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
* Shared memory management class for caching persistent data / objects
*
* @author  Devon Koh <devon@silverstream.my>
* @version  1.0
* @package  snap.base
*/
class cacher
{
    /**
    * Singleton instance
    * @var      Cacher
    */
    private static $instance = null;

    /**
     * Constructor function
     *
     * @param string $id Unique ID for the cache session
     * @param integer $module The caching module used for shared memory caching mechanism, default is MX_CACHEMODULE_AUTO. Possible value: MX_CACHEMODULE_AUTO, MX_CACHEMODULE_MMCACHE, MX_CACHEMODULE_EACCELERATOR, MX_CACHEMODULE_APC
     * @param array $memcachedServers If cacher type is MX_CACHEMODULE_MEMCACHE, $memcachedServers array contains the list of memcached servers info in the form of host:port
     *
     */
    private function __construct($cacheType, $servers, $cacheId)
    {
        if ('redis' == strtolower($cacheType)) {
            return new \Snap\redisarraycacher($cacheType, $servers, $cacheId);
        } else {
            return new \Snap\memcachecacher($cacheType, $servers, $cacheId);
        }
    }

    /**
     * Returns an instance of ICacher interface object for caching data
     * @param  \Snap\config    $config       Config object to get configuration information from
     * @param  string  	       $typeField    The key in config to get the cache type used.
     * @param  string  	       $serversField The key to get the servers configuration used.
     * @param  string  	       $idField      The key to get the cache id configuration used.
     * @return ICacher implemented object
     */
    public static function getInstance($cacheId, $cacheType, $servers)
    {
        if (null === self::$instance) {
            if ('redis' == strtolower($cacheType)) {
                self::$instance = new \Snap\redisarraycacher($cacheType, $servers, $cacheId);
            } else {
                self::$instance = new \Snap\memcachecacher($cacheType, $servers, $cacheId);
            }
        }
        return self::$instance;
    }
}
?>