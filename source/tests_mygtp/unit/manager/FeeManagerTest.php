<?php

use Snap\manager\MyGtpFeeManager;
use Snap\object\MyFee;

class FeeManagerTest extends BaseTestCase
{
    protected static $partner1;
    protected static $partner2;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        // Tempory fix for event trigger view requiring eventmessage table 
        self::$app->eventMessageStore();
        parent::setupBeforeClass();

        self::$partner1 = self::createDummyPartner();
        self::$partner2 = self::createDummyPartner();
    }

    public function testAddFee()
    {
        $this->markTestSkipped(
            'This is no longer used.'
        );

        /** @var MyGtpFeeManager $feeMgr */
        $feeMgr = self::$app->mygtpfeeManager();

        $fee = $feeMgr->addFee(
            self::$partner1,
            MyFee::TYPE_DELIVERY_FEE,
            '20',
            MyFee::CALCULATION_TYPE_FIXED,
            MyFee::MODE_XAU,
            10,
            20,
            new \DateTime('today', self::$app->getUserTimezone()),
            new \DateTime('today', self::$app->getUserTimezone()),
            MyFee::STATUS_ACTIVE
        );

        $fee2 = $feeMgr->addFee(
            self::$partner2,
            MyFee::TYPE_STORAGE_FEE,
            '20',
            MyFee::CALCULATION_TYPE_FIXED,
            MyFee::MODE_XAU,
            10,
            20,
            new \DateTime('tomorrow', self::$app->getUserTimezone()),
            new \DateTime('today', self::$app->getUserTimezone()),
            MyFee::STATUS_ACTIVE
        );

        $storedFee = self::$app->myfeeStore()->getById($fee->id);
        $storedFee2 = self::$app->myfeeStore()->getById($fee2->id);


        $this->assertGreaterThan(0, $fee->id);
        $this->assertNotNull($fee);

        $this->assertEquals($storedFee->id, $fee->id);
        $this->assertEquals($storedFee->partnerid, $fee->partnerid);
        $this->assertEquals($storedFee->type, $fee->type);
        $this->assertEquals($storedFee->value, $fee->value);
        $this->assertEquals($storedFee->minamount, $fee->minamount);
        $this->assertEquals($storedFee->maxamount, $fee->maxamount);
        $this->assertEquals($storedFee->calculationtype, $fee->calculationtype);
        $this->assertEquals($storedFee->validfrom, $fee->validfrom);
        $this->assertEquals($storedFee->validto, $fee->validto);
        $this->assertEquals($storedFee->status, $fee->status);

        $this->assertGreaterThan(0, $fee2->id);
        $this->assertNotNull($fee2);

        $this->assertEquals($storedFee2->id, $fee2->id);
        $this->assertEquals($storedFee2->partnerid, $fee2->partnerid);
        $this->assertEquals($storedFee2->type, $fee2->type);
        $this->assertEquals($storedFee2->value, $fee2->value);
        $this->assertEquals($storedFee2->minamount, $fee2->minamount);
        $this->assertEquals($storedFee2->maxamount, $fee2->maxamount);
        $this->assertEquals($storedFee2->calculationtype, $fee2->calculationtype);
        $this->assertEquals($storedFee2->validfrom, $fee2->validfrom);
        $this->assertEquals($storedFee2->validto, $fee2->validto);
        $this->assertEquals($storedFee2->status, $fee2->status);

        return [$fee, $fee2];
    }

    /** @depends testAddFee */
    public function testUpdateFee($fees)
    {
        /** @var MyGtpFeeManager $feeMgr */
        $feeMgr = self::$app->mygtpfeeManager();

        $updatedFee = $feeMgr->updateFee(
            $fees[0]->id,
            MyFee::TYPE_STORAGE_FEE,
            '50',
            MyFee::CALCULATION_TYPE_FLOAT,
            MyFee::MODE_XAU,            
            30,
            60,
            new \DateTime('tomorrow', self::$app->getUserTimezone()),
            new \DateTime('tomorrow', self::$app->getUserTimezone()),
            MyFee::STATUS_INACTIVE
        );


        $this->assertEquals($updatedFee->id, $fees[0]->id);
        $this->assertEquals($updatedFee->partnerid, $fees[0]->partnerid);

        $this->assertNotEquals($updatedFee->type, $fees[0]->type);
        $this->assertNotEquals($updatedFee->value, $fees[0]->value);
        $this->assertNotEquals($updatedFee->minamount, $fees[0]->minamount);
        $this->assertNotEquals($updatedFee->maxamount, $fees[0]->maxamount);
        $this->assertNotEquals($updatedFee->calculationtype, $fees[0]->calculationtype);
        $this->assertNotEquals($updatedFee->validfrom, $fees[0]->validfrom);
        $this->assertNotEquals($updatedFee->validto, $fees[0]->validto);
        $this->assertNotEquals($updatedFee->status, $fees[0]->status);

        return $updatedFee;
    }

    /** @depends testUpdateFee */
    public function testRemoveFee($fee)
    {
        /** @var MyGtpFeeManager $feeMgr */
        $feeMgr = self::$app->mygtpfeeManager();

        $feeMgr->removeFee($fee);

        $count = self::$app->myfeeStore()->searchTable()->select()->where('id', $fee->id)->count();

        $this->assertEquals(0, $count);
    }

    public function testSyncFee()
    {
        $this->markTestSkipped(
            'This is no longer used.'
        );

        /** @var MyGtpFeeManager $feeMgr */
        $feeMgr = self::$app->mygtpfeeManager();
        $initialCount = self::$app->myfeeStore()->searchTable()->select()->where('partnerid', self::$partner1->id)->count();

        $fee1 = $feeMgr->addFee(
            self::$partner1,
            MyFee::TYPE_DELIVERY_FEE,
            '20',
            MyFee::CALCULATION_TYPE_FIXED,
            MyFee::MODE_XAU,
            10,
            20,
            new \DateTime('today', self::$app->getUserTimezone()),
            new \DateTime('today', self::$app->getUserTimezone()),
            MyFee::STATUS_ACTIVE
        );

        $fee2 = $feeMgr->addFee(
            self::$partner1,
            MyFee::TYPE_DELIVERY_FEE,
            '20',
            MyFee::CALCULATION_TYPE_FIXED,
            MyFee::MODE_XAU,
            10,
            20,
            new \DateTime('today', self::$app->getUserTimezone()),
            new \DateTime('today', self::$app->getUserTimezone()),
            MyFee::STATUS_ACTIVE
        );

        $fee3 = $feeMgr->addFee(
            self::$partner1,
            MyFee::TYPE_DELIVERY_FEE,
            '20',
            MyFee::CALCULATION_TYPE_FIXED,
            MyFee::MODE_XAU,
            10,
            20,
            new \DateTime('today', self::$app->getUserTimezone()),
            new \DateTime('today', self::$app->getUserTimezone()),
            MyFee::STATUS_ACTIVE
        );

        $fees = self::$app->myfeeStore()->searchTable()->select()->where('partnerid', self::$partner1->id)->execute();
        
        $this->assertCount($initialCount + 3, $fees);
        $feeMgr->syncPartnerFees(self::$partner1, [$fee1->id, $fee2->id]);
        
        $fees = self::$app->myfeeStore()->searchTable()->select()->where('partnerid', self::$partner1->id)->execute();
        $this->assertCount(2, $fees);
        $this->assertEquals($fee1->id, $fees[0]->id);
        $this->assertEquals($fee2->id, $fees[1]->id);
    }
}
