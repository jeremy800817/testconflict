<?php

use Snap\object\MyHistoricalPrice;

final class HistoricalPriceManagerTest extends BaseTestCase
{

    protected static $partner;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();

        self::$partner = self::createDummyPartner();
    }

    public function testCanCollectOHLCPrice()
    {
        $app = self::$app;
        $partner = self::$partner;
        $productCode = 'DG-999-9';

        $product = self::$app->productStore()->getByField('code', $productCode);
        $priceProvider = self::$app->priceproviderStore()->getForPartnerByProduct($partner, $product);

        $yesterday = new \DateTime('yesterday', self::$app->getUserTimezone());
        $yesterday = \Snap\common::convertUserDatetimeToUTC($yesterday);
        $today     = new \DateTime('today', self::$app->getUserTimezone());
        $today = \Snap\common::convertUserDatetimeToUTC($today);

        /** @var \Snap\manager\MyGtpHistoricalPriceManager */
        $historicalPriceManager = $app->mygtphistoricalpriceManager();
        $historicalPriceManager->collectOhlcDataForProvider($priceProvider);
        $historicalprice = $app->myhistoricalpriceStore()->searchTable()->select()->orderBy('id', 'desc')->one();

        $highLowPrice = $app->priceStreamStore()->searchTable(false)->select()
            ->addFieldMax('companysellppg', 'sell_high')
            ->addFieldMin('companysellppg', 'sell_low')
            ->addFieldMax('id', 'close_id')
            ->addFieldMin('id', 'open_id')
            ->where('createdon', '>=', $yesterday->format('Y-m-d H:i:s'))
            ->where('createdon', '<',  $today->format('Y-m-d H:i:s'))
            ->where('providerid', $priceProvider->id)
            ->one();

        $openPrice = $app->priceStreamStore()
            ->searchTable(false)
            ->select()
            ->addField('companysellppg', 'sell_open')
            ->where('createdon', '>=', $yesterday->format('Y-m-d H:i:s'))
            ->where('createdon', '<',  $today->format('Y-m-d H:i:s'))
            ->where('id', $highLowPrice['open_id'])
            ->one();

        $closePrice = $app->priceStreamStore()
            ->searchTable(false)
            ->select()
            ->addField('companysellppg', 'sell_close')
            ->where('createdon', '>=', $yesterday->format('Y-m-d H:i:s'))
            ->where('createdon', '<',  $today->format('Y-m-d H:i:s'))
            ->where('id', $highLowPrice['close_id'])
            ->one();

        $this->assertEquals(1, count($historicalprice));
        $this->assertEquals($openPrice['sell_open'], $historicalprice->open);
        $this->assertEquals($closePrice['sell_close'], $historicalprice->close);
        $this->assertEquals($highLowPrice['sell_high'], $historicalprice->high);
        $this->assertEquals($highLowPrice['sell_low'], $historicalprice->low);
    }
}
