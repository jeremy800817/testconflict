<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\object\MyHistoricalPrice;
use Snap\TLogging;
use Snap\object\PriceProvider;

/**
 * This class handles the historical data management
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 15-Oct-2020
 */
class MyGtpHistoricalPriceManager
{
    use TLogging;

    /** @var Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Collect the OHLC price from yesterday pricestream
     *
     * @param PriceProvider $provider
     * @return array
     */
    public function collectOhlcDataForProvider(PriceProvider $provider)
    {
        $yesterday = new \DateTime('yesterday', $this->app->getUserTimezone());
        $yesterday = \Snap\common::convertUserDatetimeToUTC($yesterday);
        $today     = new \DateTime('today', $this->app->getUserTimezone());
        $today = \Snap\common::convertUserDatetimeToUTC($today);

        $priceStreamStore = $this->app->pricestreamStore();
        $tableName = $priceStreamStore->getTableName();
        $columnPrefix = $priceStreamStore->getColumnPrefix();
        $queryBuilder = $priceStreamStore->searchTable(false)->table($tableName, 'parent');

        $result = $queryBuilder
            ->select([])
            ->addFieldMax('companysellppg', 'sell_high')
            ->addFieldMin('companysellppg', 'sell_low')
            ->addField($queryBuilder->raw('(SELECT child.' . $columnPrefix . 'companysellppg FROM ' . $tableName . ' child WHERE child.' . $columnPrefix . 'id = MIN(parent.' . $columnPrefix . 'id))'), 'sell_open')
            ->addField($queryBuilder->raw('(SELECT child.' . $columnPrefix . 'companysellppg FROM ' . $tableName . ' child WHERE child.' . $columnPrefix . 'id = MAX(parent.' . $columnPrefix . 'id))'), 'sell_close')
            ->where('createdon', '>=', $yesterday->format('Y-m-d H:i:s'))
            ->where('createdon', '<',  $today->format('Y-m-d H:i:s'))
            ->where('providerid', $provider->id)
            ->one();

        // Because this will be saved as UTC automatically
        $yesterday =  new \DateTime('yesterday', $this->app->getUserTimezone());
        $historicalPrice = $this->app->myhistoricalpriceStore()->create([
            'open'            => $result['sell_open'],
            'close'           => $result['sell_close'],
            'high'            => $result['sell_high'],
            'low'             => $result['sell_low'],
            'priceproviderid' => $provider->id,
            'priceon'         => $yesterday->format('Y-m-d H:i:s'),
            'status'          => MyHistoricalPrice::STATUS_ACTIVE
        ]);

        $this->app->myhistoricalpriceStore()->save($historicalPrice);
        $this->log(__METHOD__ . '() OHLC Data for provider with id' . $provider->id . ' collected', SNAP_LOG_DEBUG);
    }
}
