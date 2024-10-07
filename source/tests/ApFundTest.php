<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */

use Snap\object\ApFund;
use Snap\object\SnapObject;

/**
 * @backupGlobals disabled
 */
final class ApFundTest extends LazyTestCase
{
    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('mbb_apfund', ApFund::class, 'apf');
    }

    /**
     * @return array
     */
     public function addTestProvider(): array
     {
         return [
             [1, 'Acebuy', 1234, 100.00, 1, 130.00, 1, 10.00, 10.00, 1]
         ];
     }

    /**
     * @dataProvider addTestProvider
     */
    public function testShouldInsertData($partnerID, $operationtype, $orderID, $beginPrice, $beginPriceID, $endPrice, $endpriceID, $amountppg, $amount, $expectedId): void
    {
        $store = $this->setupStores();

        // Assign variables to Object class
        $newRecord = $store->create();
        $newRecord->partnerid = $partnerID;
        $newRecord->operationtype = $operationtype;
        $newRecord->orderid = $orderID;
        $newRecord->beginprice = $beginPrice;
        $newRecord->beginpriceid = $beginPriceID;
        $newRecord->endprice = $endPrice;
        $newRecord->endpriceid = $endpriceID;
        $newRecord->amountppg = $amountppg;
        $newRecord->amount = $amount;

        // Create a new record or update an existing record from the entity object.
        $savedRecord = $store->save($newRecord);

        // Asserts that a variable is not null.
        $this->assertNotNull($savedRecord);
        $this->assertGreaterThan(0, $savedRecord->id);
        $this->assertEquals($expectedId, $savedRecord->id);

        $recordInDb = $store->getById($savedRecord->id);
        $this->assertNotNull($recordInDb);
        foreach (['partnerid', 'operationtype', 'orderid', 'beginprice', 'beginpriceid', 'endprice', 'endpriceid', 'amountppg', 'amount'] as $field) {
            $this->assertEquals($newRecord->{$field}, $recordInDb->{$field});
        }
    }

    /**
     * @depends testShouldInsertData
     */
    public function testShouldReadInsertedData(): void
    {
        $data = $this->setupStores()->getById(1);
        $this->assertNotNull($data);
        $this->assertEquals(1, $data->id);
    }

    /**
     * @depends testShouldInsertData
     */
    public function testShouldUpdateData(): void
    {
        $data = $this->setupStores()->getById(1);
        $data->endpriceid = 2;
        $updatedData = $this->setupStores()->save($data);
        $this->assertEquals(2, $updatedData->endpriceid);
    }
}
