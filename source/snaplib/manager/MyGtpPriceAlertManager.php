<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

namespace Snap\manager;

use Snap\IObservable;
use Snap\IObserver;
use Snap\IObservation;

use Snap\TObservable;
use Snap\TLogging;

use Snap\object\MyPriceAlert;
use Snap\object\MyAccountHolder;
use Snap\object\PriceStream;
use Snap\api\exception\GeneralException;
use Snap\object\MyGtpEventConfig;
use Snap\object\Partner;

/**
 * This class handles price alerts of account holder
 *
 * @author Azam <azam@silverstream.my>
 * @version 1.0
 * @created 27-Oct-2020
 */
class MyGtpPriceAlertManager implements IObservable, IObserver
{
    use TLogging;
    use TObservable;

    const KEY_MATCHED_PRICE_ALERT = '{MatchedPriceAlert}:';

    /** @var \Snap\App $app */
    private $app = null;

    public function __construct($app)
    {
        $this->app      = $app;
    }

    /**
     * Listen to the following events and update price alert as * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state)
    {
    }

    /**
     * This method return the price alert days of expiry
     *
     * @return void
     */
    public function getPriceAlertExpiryDays()
    {
        return $this->expiry;
    }

    /**
     * Add new price alert using price provider of the product.
     *
     * @todo   Each time we change price provider for the partner product, we need to reset all the prices alert
     *         priceproviderid to the updated partner product price provider id. The logic to update it
     *         may be inside the certain handler
     *
     * @param  MyAccountHolder $accountHolder
     * @param  float           $amount
     * @param  enum            $type
     * @param  string          $remarks
     * @param  string          $productCode
     * @return MyPriceAlert
     */
    public function addNewPriceAlert(MyAccountHolder $accountHolder, $amount, $type, $remarks = null, $productCode = null)
    {
        // Digital Gold by default
        if (!$productCode) {
            $productCode = "DG-999-9";
        }

        $product = $this->app->productStore()->getByField('code', $productCode);
        $partner = $accountHolder->getPartner();

        if (!$product) {
            throw GeneralException::fromTransaction([], ['message' => gettext("Product not found")]);
        }
        $provider = $this->app->priceProviderStore()->getForPartnerByProduct($partner, $product);

        $latestPrice = $this->app->priceManager()->getLatestSpotPrice($provider);
        if ($type == MyPriceAlert::TYPE_SELL){
            if ($latestPrice->companybuyppg >= $amount){
                // throw GeneralException::fromTransaction([], ['message' => gettext("Current price is ".$latestPrice->companybuyppg.". Please set less than this.")]);
                throw GeneralException::fromTransaction([], ['message' => gettext("Current price is ".number_format((float)$latestPrice->companybuyppg, 3, '.', '').". Please set more than this.")]);
            }
        }
        if ($type == MyPriceAlert::TYPE_BUY){
            if ($latestPrice->companysellppg <= $amount){
                // throw GeneralException::fromTransaction([], ['message' => gettext("Current price is ".$latestPrice->companysellppg.". Please set more than this.")]);
                throw GeneralException::fromTransaction([], ['message' => gettext("Current price is ".number_format((float)$latestPrice->companysellppg, 3, '.', '').". Please set less than this.")]);
            }
        }

        $priceAlert = $this->app->mypricealertStore()->create([
            'accountholderid' => $accountHolder->id,
            'priceproviderid' => $provider->id,
            'amount' => $amount,
            'type' => $type,
            'remarks' => $remarks,
            'status' => MyPriceAlert::STATUS_ACTIVE
        ]);

        return $this->app->mypricealertStore()->save($priceAlert);
    }

    /**
     * This method check and soft delete price alert from table for the account holder
     * if it is found
     *
     * @param  MyAccountHolder $accountHolder
     * @param  PriceAlert      $priceAlert
     * @return bool
     */
    public function deletePriceAlert(MyAccountHolder $accountHolder, $priceAlert)
    {
        if (MyPriceAlert::STATUS_INACTIVE == $priceAlert->status || $priceAlert->accountholderid != $accountHolder->id) {
            $this->log(__METHOD__ . "(): Price alert ({$priceAlert->id}) does not belong to account holder", SNAP_LOG_ERROR);
            throw \Snap\api\exception\MyPriceAlertNotFound::fromTransaction($priceAlert, ['value' => $priceAlert->id]);
        }

        $priceAlert->status = MyPriceAlert::STATUS_INACTIVE;
        $this->app->mypricealertStore()->save($priceAlert, ['status']);

        return true;
    }

    /**
     * This is the background job to process the matched price alert to do notification and other tasks.
     */
    public function processReceivedPriceStreamData($partner, $provider, $aliveTime = 60)
    {
        $startTime = time();

        while ($aliveTime >= (time() - $startTime)) {

            $latestPriceDataKey = '{PriceFeedLast}:' . $provider->id;
            $priceStreamCache = $this->app->getCache($latestPriceDataKey);
            if(0 == strlen($priceStreamCache) || null == $priceStreamCache) {
                $latestPrice = $this->app->priceStreamStore()->searchTable()
                            ->select()
                            ->where('providerid', $provider->id)
                            ->orderby('id', 'DESC')
                            ->one();
            } else {
                $latestPrice = $this->app->priceStreamStore()->create();
                $latestPrice->fromCache($priceStreamCache);
            }
        

            if ($provider->isPriceDataFresh($latestPrice)) {
                $this->onReceivedNewPriceStreamData($partner, $latestPrice);
                usleep(500000);
                continue;
            } 

            $this->log(__METHOD__ . "(): Price data is stale, skipping current pricestream", SNAP_LOG_DEBUG);
            sleep(1);
        }
    }

    /**
     * This is the background job to process the matched price alert to do notification and other tasks.
     */
    public function processPriceMatchedPriceAlert($partner, $aliveTime = 60)
    {
        $this->log(__METHOD__ . "(): Executing notification of matched price process", SNAP_LOG_DEBUG);
        $startTime = time();
        $queueManager = $this->app->queueManager();
        while ($aliveTime >= (time() - $startTime)) {
            if ($queueManager->count(self::KEY_MATCHED_PRICE_ALERT . $partner->id)) {
                $matchedInfo = json_decode($queueManager->pop(self::KEY_MATCHED_PRICE_ALERT . $partner->id), true);
                $priceStream = $this->app->priceStreamStore()->create();
                $priceAlert = $this->app->mypricealertStore()->create();
                $priceStream->fromCache($matchedInfo['priceStream']);
                $priceAlert->fromCache($matchedInfo['priceAlert']);
                // $this->log(__METHOD__ . "(): Processing matched price alert (id: {$priceAlert->id})", SNAP_LOG_DEBUG);
                $this->onPriceAlertMatched($partner, $priceAlert, $priceStream);
                usleep(500);
            } else {
                usleep(2500);
            }
        }
    }

    /**
     * Check if the new pricestream able to match any price alerts.
     *
     * @param  Partner     $partner
     * @param  PriceStream $priceStream The newly acquired price data
     * @return void
     */
    public function onReceivedNewPriceStreamData($partner, $priceStream)
    {
        if (!$priceStream) {
            $this->log(__METHOD__ . "(): Unable to retrieve pricestream data", SNAP_LOG_ERROR);
            return;
        }

        $mypricealertStore = $this->app->mypricealertStore();
        $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);
        
        $expiryDate = new \DateTime();
        $expiryDate->setTimezone($this->app->getServerTimezone());
        $expiryDate->sub(new \DateInterval("P{$settings->pricealertvaliddays}D"));
        $expiryDate = $expiryDate->format('Y-m-d H:i:s');


        $matches = $mypricealertStore->searchTable()->select()
            ->where('status', MyPriceAlert::STATUS_ACTIVE)
            ->where('priceproviderid', $priceStream->providerid)
            ->where('createdon', '>=', $expiryDate)
            ->where(function ($q) use ($priceStream) {
                // When the company buying higher that user sell price alert
                $q->where(function ($q2) use ($priceStream) {
                    $q2->where('amount', '<=', $priceStream->companybuyppg)
                        ->andWhere('type', MyPriceAlert::TYPE_SELL);
                });
                // When the company selling lower than user buy price alert
                $q->orWhere(function ($q2) use ($priceStream) {
                    $q2->where('amount', '>=', $priceStream->companysellppg)
                        ->andWhere('type', MyPriceAlert::TYPE_BUY);
                });
            })
            ->where('triggered', 0)
            ->execute();

        if (count($matches)) {

            // $this->log(__METHOD__ . "():  Matched " . count($matches) . " from priceStream {$priceStream->id}, buy: {$priceStream->companybuyppg}, sell: {$priceStream->companysellppg}", SNAP_LOG_DEBUG);

            $matchesIds = [];
            $dataArray = array_map(function ($aMatch) use ($priceStream, &$matchesIds) {
                $matchesIds[$aMatch->id] = $aMatch->id;
                return json_encode(['priceAlert' => $aMatch->toCache(), 'priceStream' => $priceStream->toCache()]);
            }, $matches);

            $this->app->queueManager()->add(self::KEY_MATCHED_PRICE_ALERT . $partner->id, $dataArray);
            $this->flagMatchesAsTriggered($matchesIds);

            // Price alert should only trigger once
            // $this->resetTriggerStatus($priceStream);
        } else {
            // Price alert should only trigger once
            // $this->resetTriggerStatus($priceStream);
        }
    }

    // Flag as triggered to avoid notifying if companysell price keep going down 
    // and companybuy price keep going up
    protected function flagMatchesAsTriggered($matchesIds)
    {
        $db        = $this->app->getDBHandle();
        $store     = $this->app->mypricealertStore();
        $prefix    = $store->getColumnPrefix();
        $tableName = $store->getTableName();
        $now       = (new \DateTime('now'))->format('Y-m-d H:i:s');
        // Set triggeredon and triggered
        $idInCondition = sprintf("{$prefix}id IN (%s)", implode(",", $matchesIds));
        $fields        = sprintf("{$prefix}lasttriggeredon = '%s', {$prefix}triggered = %d", $now, 1);
        $res           = $db->query("UPDATE {$tableName} SET {$fields} WHERE {$idInCondition}");
        if (!$res || 0 == $res->rowCount()) {
            // $this->log(__METHOD__ . "(): No price alert triggered status was set as 1", SNAP_LOG_DEBUG);
        } else {
            // $this->log(__METHOD__ . "(): Successfully set price alerts triggered status as 1", SNAP_LOG_DEBUG);
        }
    }

    // Reset triggered status for price not matching the amount of price alert
    protected function resetTriggerStatus($priceStream)
    {
        $db        = $this->app->getDBHandle();
        $store     = $this->app->mypricealertStore();
        $prefix    = $store->getColumnPrefix();
        $tableName = $store->getTableName();

        $conditions    = sprintf("{$prefix}priceproviderid = %d ", $priceStream->providerid);
        $sellCondition = sprintf("{$prefix}amount > %d AND {$prefix}type = '%s'", $priceStream->companybuyppg, MyPriceAlert::TYPE_SELL);
        $buyCondition  = sprintf("{$prefix}amount < %d AND {$prefix}type = '%s'", $priceStream->companysellppg, MyPriceAlert::TYPE_BUY);
        $fields        = sprintf("{$prefix}triggered = %d", 0);
        $res           = $db->query("UPDATE {$tableName} SET {$fields} WHERE {$conditions} AND ({$sellCondition} OR {$buyCondition});");

        if (!$res || 0 == $res->rowCount()) {
            // $this->log(__METHOD__ . "(): No non-matching price alert triggered status was set as 0", SNAP_LOG_DEBUG);
        } else {
            // $this->log(__METHOD__ . "(): Successfully set non-matching price alerts triggered status as 0", SNAP_LOG_DEBUG);
        }
    }


    /**
     * This method is called when the price alert has been matched
     *
     * @param  Partner      $partner
     * @param  MyPriceAlert $priceAlert
     * @param  PriceStream  $priceStream
     */
    public function onPriceAlertMatched($partner, $priceAlert, $priceStream)
    {
        try {

            $settings = $this->app->mypartnersettingStore()->getByField('partnerid', $partner->id);

            $duration         = $settings->pricealertvaliddays;
            $latestPriceAlert = $this->app->mypricealertStore()->getById($priceAlert->id);
            $createdon        = $latestPriceAlert->createdon;
            $expiry           = clone $createdon;
            $expiry->add(\DateInterval::createFromDateString("{$duration} days"));

            $lastTriggered = null != $latestPriceAlert->lasttriggeredon ?
                $latestPriceAlert->lasttriggeredon->format('Y-m-d H:i:s') :
                '';

            $accHolder = $this->app->myaccountholderStore()->getById($latestPriceAlert->accountholderid);

            $link = $this->app->getConfig()->{'mygtp.pricealert.sourcelink'};
            $matchPrice = $latestPriceAlert->type == MyPriceAlert::TYPE_BUY ? $priceStream->companysellppg : $priceStream->companybuyppg;

            $observation = new \Snap\IObservation(
                $latestPriceAlert,
                \Snap\IObservation::ACTION_OTHER,
                MyPriceAlert::STATUS_ACTIVE,
                [
                    'event' => MyGtpEventConfig::EVENT_PRICE_ALERT_MATCH,
                    'accountholderid'   => $latestPriceAlert->accountholderid,
                    'accountholder' => $accHolder,
                    'receiver' => $accHolder->email,
                    'matchprice' => number_format($matchPrice, 2),
                    'targetprice' => number_format($latestPriceAlert->amount, 2),
                    'type' => $latestPriceAlert->type == MyPriceAlert::TYPE_BUY ? 'Buy' : 'Sell',
                    'partnername' => $partner->name,
                    'projectBase' => $this->app->getConfig()->{'projectBase'},
                    'link' => $link,
                    'data' => [
                        'id'             => intval($latestPriceAlert->id),
                        'price'          => floatval($latestPriceAlert->amount),
                        'type'           => $latestPriceAlert->type == MyPriceAlert::TYPE_BUY ? 'Buy' : 'Sell',
                        'date'           => $createdon->format('Y-m-d H:i:s'),
                        'last_triggered' => $lastTriggered ?? '',
                        'expiry'         => $expiry->format('Y-m-d H:i:s'),
                        'remarks'        => $latestPriceAlert->remarks ?? ''
                    ]
                ]
            );

            $this->notify($observation);
        } catch (\Exception $e) {
            $this->log("Thrown exception @ " . __FILE__ . ":" . __LINE__ . " while attempting to match price alert ({$priceAlert->id}) with message " . $e->getMessage() . ".  Exception type: " . get_class($e), SNAP_LOG_ERROR);
        }
    }
}
