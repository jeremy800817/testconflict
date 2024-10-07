<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2019
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2019
 */
Namespace Snap\manager;

/**
* This class implements a basic data storage service optimise to use with Redis that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class queueManager 
{
    /**
     * Redis cacher class
     * @var Snap\redisCacher
     */
    private $cacher = null;

    /**
     * Constructor of the class
     * @param Snap\App $app Application instance.
     */
    public function __construct($app)
    {
        $config = $app->getConfig();
        $this->cacher = new mxredisCacher('redis', mxApp::getInstance()->getConfig()->redisservers, mxApp::getInstance()->getConfig()->redisid);
    }

    /**
     * Creates a queue object that just simplifies the add/pop/empty/count commands for easier usage.
     * @param  String $queueName [description]
     * @return Queue
     */
    public function getQueue($queueName)
    {
        return new Queue($this, $queueName);
    }

    /**
     * Add an item to the queue
     * @param String $queueName Name of the queue
     * @param String $data      Data to store
     * @param Number $ranking   Ordering (a number).  If null, it will be replaced with time
     */
    public function add($queueName, $data, $ranking = null)
    {
        $redisCommand = [];
        if (null == $ranking) {
            $ranking = time();
        }
        if (! is_array($data)) {
            $data = [$data];
        }
        if (is_array($data)) {
            $count = 0;
            foreach ($data as $aMember) {
                $aRanking = (is_array($ranking) && $count < count($ranking)) ? $ranking[$count++] : $ranking;
                $redisCommand[] = ['zadd', [$queueName, ['NX'], $aRanking, $aMember]];
            }
        }
        return $this->cacher->runBulkCommands($redisCommand);
    }

    /**
     * This method will get the required number of items out of the queue and remove them from queue.
     * @param  String  $queueName    Name of the queue
     * @param  integer $numItems     Number of items to get out from the queue.
     * @param  boolean $bFirstItem   Whether to get the item from the start (min rank) or from the end (max rank)
     * @return array of the items.
     */
    public function pop($queueName, $numItems = 1, $bFirstItem = true)
    {
        $results = [];
        $command = ($bFirstItem ? 'ZPopMin' : 'ZPopMax');
        // if ($this->cacher->waitForLock($queueName.':lock', 0, 5, 20)) {
            $results = array_keys(call_user_func_array(array($this->cacher, $command), [$queueName, $numItems]));
            // $results = call_user_func_array(array($this->cacher, $command), [$queueName, 0, $numItems - 1]);
            // call_user_func_array(array($this->cacher, 'zrem'), array_merge([$queueName], $results));
            // $this->cacher->unlock($queueName.':lock');
            //$this->logDebug("Obtained from queue $queueName - " . join(',', $results));
        // } else {
            //$this->logWarning("redisQueueManager::pop() - Unable to secure a lock for queue $queueName");
        // }
        return $results;
    }

    /**
     * Remove the queue and all its items
     * @param  String  $queueName    Name of the queue
     */
    public function empty1($queueName)
    {
        return $this->cacher->del($queueName);
    }

    /**
     * Returns the number of items that are still in the queue
     * @param  string $queueName Name of the queue
     * @return integer   Number of items still in the queue.
     */
    public function count($queueName)
    {
        return $this->cacher->zcount($queueName, '-inf', '+inf');
    }
}

class queue 
{
    /**
     * Name of the queue
     * @var String
     */
    private $queueName;

    /**
     * The queueManager
     * @var Snap\redisQueueManqager
     */
    private $manager;

    public function __construct($manager, $queueName) 
    {
        $this->queueName = $queueName;
        $this->manager = $manager;
    }

    /**
     * Add an item to the queue
     * @param String $data      Data to store
     * @param Number $ranking   Ordering (a number).  If null, it will be replaced with time
     */
    public function add($data, $ranking = null)
    {
        return $this->manager->add($this->queueName, $data, $ranking);
    }

    /**
     * This method will get the required number of items out of the queue and remove them from queue.
     * @param  integer $numItems     Number of items to get out from the queue.
     * @param  boolean $bFirstItem   Whether to get the item from the start (min rank) or from the end (max rank)
     * @return array of the items.
     */
    public function pop($numItems = 1, $bFirstItem = true)
    {
        return $this->manager->pop($this->queueName, $numItems, $bFirstItem);
    }

    /**
     * Remove the queue and all its items
     */
    public function empty1()
    {
        return $this->manager->empty($this->queueName);
    }

    /**
     * Returns the number of items that are still in the queue
     * @return integer   Number of items still in the queue.
     */
    public function count()
    {
        return $this->manager->count($this->queueName, '-inf', '+inf');
    }
}
?>
