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
Use Snap\object\Order;

/**
 * Implements a calculator methods that will automatically implement mathematical
 * calculation with floating point in specific way as required by different
 * merchants.
 */
class DefaultMatchStrategy implements IFutureOrderMatchStrategy
{
    Use \Snap\TLogging;

    private $latestPriceStream = null;
    private $matchCompanyBuy = null;
    private $matchConpanySell = null;

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
        $this->log(__METHOD__."() - attempting to match priceStream {$priceStream->id} with buy {$priceStream->companybuyppg} and sell {$priceStream->companysellppg}", SNAP_LOG_DEBUG);
        //Store the data for use later.
        $now = strtotime("-8 hours");
        $key = "{".\Snap\Common::getBaseClassName(__CLASS__)."}:{$provider->id}";
        $highestCompanyBuy = $app->getCache($key.date('Ymd', $now).'-Buy');
        $lowestCompanySell = $app->getCache($key.date('Ymd', $now).'-Sell');
        $this->log(__METHOD__."() - cached buy = $highestCompanyBuy and sell = $lowestCompanySell", SNAP_LOG_DEBUG);
        // $app->setCache($key, $priceStream->toCache(), 86400);
        $this->matchCompanyBuy = $this->matchCompanySell = false;

        if($priceStream->companybuyppg > $highestCompanyBuy) {
            $this->log(__METHOD__."() - Buy price needs matching", SNAP_LOG_DEBUG);
            $app->setCache($key.date('Ymd', $now).'-Buy', $priceStream->companybuyppg, 3600);
            $this->latestPriceStream = $priceStream;
            $this->matchCompanyBuy = true;
        }
        if($priceStream->companysellppg < $lowestCompanySell || 0 == $lowestCompanySell) {
            $this->log(__METHOD__."() - Sell price needs matching", SNAP_LOG_DEBUG);
            $app->setCache($key.date('Ymd', $now).'-Sell', $priceStream->companysellppg, 3600);
            $this->latestPriceStream = $priceStream;
            $this->matchCompanySell = true;
        }
        return $this->matchCompanySell || $this->matchCompanyBuy;
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
        $this->log(__METHOD__."() - attempting to match priceStream {$priceStream->id} with buy {$priceStream->companybuyppg} and sell {$priceStream->companysellppg}", SNAP_LOG_DEBUG);
        //Basic implementation is just to check 
        $now = strtotime("-8 hours");
        $partner = $app->partnerStore()->getById($futureOrder->partnerid);
        $product = $app->productStore()->getById($futureOrder->productid);
        $provider = $app->priceProviderStore()->getForPartnerByProduct($partner, $product);
        $key = "{".\Snap\Common::getBaseClassName(__CLASS__)."}:{$provider->id}}";
        $highestCompanyBuy = $app->getCache($key.date('Ymd', $now).'-Buy');
        $lowestCompanySell = $app->getCache($key.date('Ymd', $now).'-Sell');
        if(Order::TYPE_COMPANYBUY == $futureOrder->ordertype && $highestCompanyBuy > $futureOrder->targetprice) {
            //Reset the matching level
            $app->setCache($key.date('Ymd', $now).'-Buy', 0, 3600);
        } else if(Order::TYPE_COMPANYSELL == $futureOrder->ordertype && $lowestCompanySell < $futureOrder->targetprice) {
            //Reset the matching level
            $app->setCache($key.date('Ymd', $now).'-Sell', 99999999999, 3600);
        }
    }

    /**
     * Returns the configuration that we need to perform the matching with.  This
     * method will only be called when canMatchNewPrice() or canMatchOnTrigger() returns true.
     * @return array with values ($toMatchCompanyBuy, $toMatchCompanySell, $priceStreamObject)
     */
    function getMatchFutureOrderConfig()
    {
        return [  $this->matchCompanyBuy, $this->matchCompanySell, $this->latestPriceStream];
    }
}