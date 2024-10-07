<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\util\pricematch;

Use Snap\IFutureOrderMatchStrategy;
Use Snap\InputException;
Use Snap\IEntity;

/**
 * Implements a calculator methods that will automatically implement mathematical
 * calculation with floating point in specific way as required by different
 * merchants.
 */
class FixedIntervalMatchStrategy implements IFutureOrderMatchStrategy
{
    Use \Snap\TLogging;

    private $latestPriceStream = null;

    /**
     * This method is designed to be called on obtaining a new price data.  The method should return 
     * true if a matching should be done on the price stream data.  Otherwise return false.
     * 
     * @param  \Snap\Application $app
     * @param  \Snap\object\PriceProvider $provider   The price provider
     * @param  \Snap\object\PriceStream $priceStream New price data received
     * @return boolean.  True if matching should proceed.  False otherwise.
     */
    function canMatchNewPrice($app, \Snap\object\PriceProvider $provider, \Snap\object\PriceStream $priceStream)
    {
        //Store the data for use later.
        $key = "{".\Snap\Common::getBaseClassName(__CLASS__)."}:{$provider->id}";
        $app->setCache($key, $priceStream->toCache(), 86400);
        return false;
    }

    /**
     * This method will be invoked on a scheduled interface and the strategy implementation will
     * determine if matching should proceed at this particular trigger interval.
     * @param  \Snap\Application $app
     * @param  \Snap\object\PriceProvider $provider   The price provider
     * @return boolean.  True if matching should proceed.  False otherwise
     */
    function canMatchOnTrigger($app, \Snap\object\PriceProvider $provider)
    {
        $this->log(__METHOD__."() with interval {$provider->futureorderparams}.  Time now is " . date("H:i:s"), SNAP_LOG_DEBUG);
        $key = "{".\Snap\Common::getBaseClassName(__CLASS__)."}:{$provider->id}";
        list($startTime, $endTime, $interval) = explode('||', $provider->futureorderparams);
        $now = date('H:i');
        $currentMinute = date('i');
        if( $startTime <= $now && $endTime >= $now && (0 == ($currentMinute % $interval))) {
            $this->latestPriceStream = $app->priceStreamStore()->create();
            $this->latestPriceStream->fromCache($app->getCache($key));
            if ($provider->id == 1){
                $tempBuy = bcdiv($this->latestPriceStream->companybuyppg, 1, 2);
                $tempBuy = sprintf("%.6f", $tempBuy);
                $tempSell = bcdiv($this->latestPriceStream->companysellppg, 1, 2);
                $tempSell = sprintf("%.6f", $tempSell);
                $this->latestPriceStream->companybuyppg = $tempBuy;
                $this->latestPriceStream->companysellppg = $tempSell;
            }
            if(0 < $this->latestPriceStream->id) {
               return true;
            }
        }
        return false;
    }

    /**
     * This method is called when a new future order is received from the system so that the strategy can decide if
     * matching should be reset or not.
     * 
     * @param  \Snap\object\OrderQueue $futureOrder  The future order object
     */
    function onNewFutureOrderReceived($app, \Snap\object\OrderQueue $futureOrder)
    {
        //Nothing to do as this is based on interval
    }

    /**
     * Returns the configuration that we need to perform the matching with.  This
     * method will only be called when canMatchNewPrice() or canMatchOnTrigger() returns true.
     * @return array with values ($toMatchCompanyBuy, $toMatchCompanySell, $priceStreamObject)
     */
    function getMatchFutureOrderConfig()
    {
        return [ true, true, $this->latestPriceStream];
    }
}