<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\api\price;

Use \Snap\InputException;
Use \Snap\TLogging;
Use \Snap\IGtpPriceCollectorAPI;


/**
 * Encapsulates the tag table on the database
 *
 * This class encapsulates the tag table data
 * information
 *
 * Data members:
 * Name             Type                Description
 * @property-read   int                 $ID                 ID of the system
 * @property        string          $code           tag category
 * @property        string              $name           code used for this record
 * @property        int                 $pricesourceid          description of this record
 * @property        int                 $pullmode           The status for this staff.  (Active / Inactive)
 * @property        int                 $currencyid         Time this record is created
 * @property        string              $whitelistip        Time this record is last modified
 * @property        string              $url            Time this record is created
 * @property        string              $connectinfo        Time this record is last modified
 * @property        int                 $lapsetimeallowance         Time this record is created
 * @property        DateTime            $createdon          Time this record is created
 * @property        int                 $createdby          Time this record is created
 * @property        DateTime            $modifiedon         Time this record is last modified
 * @property        int                 $modifiedby         Time this record is last modified
 * @property        int                 $status             The status for this vendor.  (Active / Inactive)
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.api.price
 */
class RedisPubsubPriceCollectorApi implements \Snap\IGtpPriceCollectorAPI {
    Use \Snap\TLogging;

    private $app = null;

    private $priceProvider = null;

    private $options = [];

    /**
     * Initialise the api with its specific configuration
     * @param  \Snap\App                  $app           App object
     * @param  \Snap\object\PriceProvider $priceProvider Configuration to use for this api
     */
    function initialise($app, \Snap\object\PriceProvider $priceProvider) {
        $this->priceProvider = $priceProvider;
        $this->app = $app;
        //Next to ensure that the configuration provided by the price provider is correct and appropriate for this api.
        //Get needed data and do setup.
        preg_match_all('/(\S+):\/\/(\S+):(\S+)\?(.*)/m', $priceProvider->connectinfo, $matches, PREG_SET_ORDER);
        $matches = $matches[0];
        $this->options['redisServer'] = $matches[2];
        $this->options['redisPort'] = $matches[3];
        $options = $matches[4];
        foreach(explode('&', $options) as $anOption) {
            list($key, $value) = explode('=', $anOption);
            $this->options[$key] = $value;
        }
    }

    /**
     * This method will start the price collection engine.
     */
    function run() {
        //Step 1:  Check if there is another instance running.  Ignore if already running.
        $processKey = '{PubsubPriceCollector}:' . $this->priceProvider->id;
        $stopSignalKey = '{PubsubPriceCollectorStop}:' . $this->priceProvider->id;
        if(0 < $this->app->getCacher()->exists($processKey)) {
            $this->app->logInfo("Skip running " . __CLASS__ . " with ID {$this->priceProvider->id} because another instance is running " . $this->app->getCache($processKey) . " with cache key $processKey");
            return;
        }
        //Step 2:  Setup to indicate instance is now running.
        $this->app->setCache($processKey, $this->priceProvider->id, 60);

        //Step 3:  While loop to setup a pub-sub connection and monitor for price updates or changes.
        $this->app->logDebug("Now coneecting to price service @ {$this->options['redisServer']}:{$this->options['redisPort']}");
        $redis = new \Redis();
        $redis->pconnect($this->options['redisServer'], $this->options['redisPort']);
        $start = time();
        $runTime = 0;
        $app = $this->app;
        $channels = explode('|',  $this->options['channels']);
        $priceProvider = $this->priceProvider;
        $priceChannel = $this->options['priceChannel'];
        try {
            $redis->subscribe($channels, 
                    function ($redis, $channel, $message) use ($app, $channels, $priceProvider, $stopSignalKey, $processKey, $priceChannel) {
                        //Step 4:  On new price data obtained, call the onReceiveNewPriceData() in  PriceManager method to register a new
                        //         price stream object.
                        $priceCollected = json_decode($message);
                        if($priceChannel == $channel) {
                            $app->priceManager()->onReceiveNewPriceData($priceProvider, 
                                                    $priceCollected->gp_livebuyprice_gm,
                                                    $priceCollected->gp_livesellprice_gm,
                                                    $priceCollected->gp_rawfxusdbuy,
                                                    $priceCollected->gp_rawfxusdsell,
                                                    $priceCollected->gp_rawfxsource,
                                                    $priceCollected->gp_uuid,
                                                    $priceCollected->gp_createDate,
                                                    $message );                            
                        }
                        //Step 5)  Keep running until no we get signal to stop or we didn't receive goodfeed signal.
                        if($priceProvider->id == $app->getCache($stopSignalKey)) {
                            $redis->unsubscribe($channels);
                            $app->delCache($processKey);
                            $app->delCache($stopSignalKey);
                            echo "Ending price collection due to receiving signal to shut down\n";
                            exit;
                            // $redis->close();
                        } else {
                            $runTime = time() - $start + 1;
                            $app->setCache($processKey, $runTime, 60);
                        }
                    }
            );            
        } catch(Exception $e) {
            echo sprintf( "Ending price collection due to %s\n", htmlspecialchars($e->getMessage()) );
        }
    }

    /**
     * This method will stop the price collection process
     */
    function stop() {
        $stopSignalKey = '{PubsubPriceCollectorStop}:' . $this->priceProvider->id;
        $this->app->setCache($stopSignalKey, $this->priceProvider->id, 20);
    }

    /**
     * Determines if the price collection engine is currently operating or not.
     * @return boolean True if is running.  False otherwise.
     */
    function isRunning() {
        $processKey = '{PubsubPriceCollector}:' . $this->priceProvider->id;
        return 0 < $this->app->getCache($processKey);
    }
}
?>