<?php

use Snap\object\MyPriceAlert;

final class PriceAlertManagerTest extends BaseTestCase
{
    protected static $accountHolder;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->pricestreamStore();

        self::$accountHolder = self::createDummyAccountHolder();
        self::$app->queueManager()->empty(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . self::$accountHolder->partnerid);

        // Cleanup existing
        $priceAlerts = self::$app->mypricealertStore()
        ->searchTable()
        ->select()
        ->get();

        foreach($priceAlerts as $priceAlert) {
            self::$app->mypricealertStore()->delete($priceAlert);
        }
    }

    public function testCanAddNewPriceAlert()
    {
        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = self::$app->mygtppricealertManager();

        $priceAlert1 = $priceAlertManager->addNewPriceAlert(self::$accountHolder, 190, MyPriceAlert::TYPE_BUY, 'TEST');
        $priceAlert2 = $priceAlertManager->addNewPriceAlert(self::$accountHolder, 210, MyPriceAlert::TYPE_SELL, 'TEST');

        $this->assertEquals(2, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_ACTIVE)->count());

        $this->assertGreaterThan(0, $priceAlert1->id);
        $this->assertGreaterThan(0, $priceAlert2->id);
        $this->assertEquals(MyPriceAlert::TYPE_BUY, $priceAlert1->type);
        $this->assertEquals(MyPriceAlert::TYPE_SELL, $priceAlert2->type);

        return [$priceAlert1, $priceAlert2];
    }

    /** @depends testCanAddNewPriceAlert */
    public function testCanDeletePriceAlert($priceAlerts)
    {
        $this->assertEquals(2, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_ACTIVE)->count());

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = self::$app->mygtppricealertManager();
        $priceAlertManager->deletePriceAlert(self::$accountHolder, $priceAlerts[0]);

        $this->assertEquals(1, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_INACTIVE)->count());

        $this->assertEquals(1, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_ACTIVE)->count());

        $priceAlertManager->deletePriceAlert(self::$accountHolder, $priceAlerts[1]);

        $this->assertEquals(2, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_INACTIVE)->count());

        $this->assertEquals(0, self::$app->mypricealertStore()
            ->searchTable()
            ->select()
            ->where('accountholderid', self::$accountHolder->id)
            ->where('status', MyPriceAlert::STATUS_ACTIVE)->count());

        return $priceAlerts;
    }

    /** @depends testCanDeletePriceAlert */
    public function testCannotDeleteNonExistentPriceAlert($priceAlerts)
    {
        $this->expectException(\Snap\api\exception\MyPriceAlertNotFound::class);

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = self::$app->mygtppricealertManager();
        $priceAlertManager->deletePriceAlert(self::$accountHolder, $priceAlerts[0]);
    }

    /** @depends testCanDeletePriceAlert */
    public function testCannotDeleteOthersPriceAlert($priceAlerts)
    {
        $this->expectException(\Snap\api\exception\MyPriceAlertNotFound::class);

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = self::$app->mygtppricealertManager();
        $priceAlertManager->deletePriceAlert(self::createDummyAccountHolder(), $priceAlerts[0]);
    }


    public function testCanMatchPriceAlert()
    {
        $partner = self::$accountHolder->getPartner();
        $product = self::$app->productStore()->getByField('code', 'DG-999-9');
        $provider = self::$app->priceproviderStore()->getForPartnerByProduct($partner, $product);

        /** @var \Snap\manager\MyGtpPriceAlertManager */
        $priceAlertManager = self::$app->mygtppricealertManager();
        $priceAlertManager->addNewPriceAlert(self::$accountHolder, 190, MyPriceAlert::TYPE_BUY, 'TEST');
        $priceAlertManager->addNewPriceAlert(self::$accountHolder, 210, MyPriceAlert::TYPE_SELL, 'TEST');
        $priceAlertManager->addNewPriceAlert(self::$accountHolder, 230, MyPriceAlert::TYPE_SELL, 'TEST');

        //Test Matching code.  Not matching any trx
        $newPricestream = self::$app->pricestreamStore()->create([
            'pricesourceid' => 1,
            'providerid' => $provider->id,
            'providerpriceid' => 1,
            'currencyid' => 1,
            'companybuyppg' => 180,
            'companysellppg' => 200,
            'uuid' => 'XZ1',
            'pricesourceon' => new \Datetime,
            'status' => \Snap\object\Pricestream::STATUS_ACTIVE,
        ]);

        self::$app->pricestreamStore()->save($newPricestream);
        $this->assertGreaterThan(0, $newPricestream->id);

        self::$app->mygtpPriceAlertManager()->processReceivedPricestreamData($partner, $provider, 1);
        $this->assertEquals(0, self::$app->queueManager()->count('{PriceFeedLast}'.$provider->id));
        $this->assertEquals(0, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        // Test matching buy price alert
        $newPricestream = self::$app->pricestreamStore()->create([
            'pricesourceid' => 1,
            'providerid' => $provider->id,
            'providerpriceid' => 1,
            'currencyid' => 1,
            'companybuyppg' => 170,
            'companysellppg' => 190,
            'uuid' => 'XZ2',
            'pricesourceon' => new \Datetime,
            'status' => \Snap\object\Pricestream::STATUS_ACTIVE,
        ]);

        self::$app->pricestreamStore()->save($newPricestream);
        $this->assertGreaterThan(0, $newPricestream->id);

        self::$app->mygtpPriceAlertManager()->processReceivedPricestreamData($partner, $provider, 1);
        $this->assertEquals(1, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        self::$app->mygtpPriceAlertManager()->processPriceMatchedPriceAlert($partner, 1);
        $this->assertEquals(0, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        // Test matching sell price alert
        $newPricestream = self::$app->pricestreamStore()->create([
            'pricesourceid' => 1,
            'providerid' => $provider->id,
            'providerpriceid' => 1,
            'currencyid' => 1,
            'companybuyppg' => 220,
            'companysellppg' => 300,
            'uuid' => 'XZ2',
            'pricesourceon' => new \Datetime,
            'status' => \Snap\object\Pricestream::STATUS_ACTIVE,
        ]);

        self::$app->pricestreamStore()->save($newPricestream);
        $this->assertGreaterThan(0, $newPricestream->id);

        self::$app->mygtpPriceAlertManager()->onObservableEventFired(self::$app->priceManager(), new \Snap\IObservation($newPricestream, \Snap\IObservation::ACTION_NEW, 0));

        self::$app->mygtpPriceAlertManager()->processReceivedPricestreamData($partner, $provider, 1);
        $this->assertEquals(1, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        self::$app->mygtpPriceAlertManager()->processPriceMatchedPriceAlert($partner, 1);
        $this->assertEquals(0, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        // Test matching sell price alert and not matching triggered alert within interval
        $newPricestream = self::$app->pricestreamStore()->create([
            'pricesourceid' => 1,
            'providerid' => $provider->id,
            'providerpriceid' => 1,
            'currencyid' => 1,
            'companybuyppg' => 240,
            'companysellppg' => 250,
            'uuid' => 'XZ2',
            'pricesourceon' => new \Datetime,
            'status' => \Snap\object\Pricestream::STATUS_ACTIVE,
        ]);

        self::$app->pricestreamStore()->save($newPricestream);
        $this->assertGreaterThan(0, $newPricestream->id);

        self::$app->mygtpPriceAlertManager()->onObservableEventFired(self::$app->priceManager(), new \Snap\IObservation($newPricestream, \Snap\IObservation::ACTION_NEW, 0));

        self::$app->mygtpPriceAlertManager()->processReceivedPricestreamData($partner, $provider, 1);
        $this->assertEquals(1, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        self::$app->mygtpPriceAlertManager()->processPriceMatchedPriceAlert($partner, 1);
        $this->assertEquals(0, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));

        // Test price flip
        self::$app->mygtpPriceAlertManager()->onObservableEventFired(self::$app->priceManager(), new \Snap\IObservation($newPricestream, \Snap\IObservation::ACTION_NEW, 0));
        self::$app->mygtpPriceAlertManager()->processReceivedPricestreamData($partner, $provider, 1);
        $this->assertEquals(0, self::$app->queueManager()->count(\Snap\manager\MyGtpPriceAlertManager::KEY_MATCHED_PRICE_ALERT . $partner->id));
    }
}
