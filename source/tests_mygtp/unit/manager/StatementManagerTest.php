<?php

use Snap\object\MyLedger;

final class StatementManagerTest extends BaseTestCase
{
    protected static $partner;
    protected static $accountHolder;
    protected static $date;
    protected static $dateStart;
    protected static $dateEnd;
    protected static $untilDate;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        $faker = self::getFaker('ms_MY');
        // Tempory fix for event trigger view requiring eventmessage table 
        self::$app->userStore();
        self::$app->tagStore();
        self::$app->partnerStore();
        self::$app->eventMessageStore();
        self::$app->userStore();
        self::$app->tagStore();
        self::$app->myaccountholderStore();
        self::$app->productStore();
        self::$app->mybankStore();
        self::$app->orderStore();
        self::$app->mypaymentDetailStore();
        self::$app->mymonthlystoragefeeStore();
        self::$app->mygoldtransactionStore();
        self::$app->redemptionStore();
        self::$app->myConversionStore();
        self::$app->myledgerStore();
        
        self::$partner = self::createDummyPartner();
        self::$accountHolder = self::createDummyAccountHolder();

        self::$date      = new \DateTime('now',self::$app->getUserTimezone());
        self::$dateStart = new \DateTime(self::$date->format('Y-m-d 00:00:00'), self::$app->getUserTimezone());
        self::$dateEnd   = new \DateTime(self::$date->format('Y-m-d 23:59:59'), self::$app->getUserTimezone());
        self::$untilDate = clone self::$dateStart;
        self::$untilDate->modify("-1 second");
    }

    public function testCanGetOpeningBalance()
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();
        $accHolder = self::$accountHolder;        
        
        $openingBalance = $statementManager->getStatementOpeningBalance($accHolder, self::$untilDate);
        $this->assertArrayHasKey('led_amountin', $openingBalance);
        $this->assertArrayHasKey('led_amountout', $openingBalance);
        $this->assertArrayHasKey('led_credit', $openingBalance);
        $this->assertArrayHasKey('led_debit', $openingBalance);
        $this->assertArrayHasKey('led_amountbalance', $openingBalance);
        $this->assertArrayHasKey('led_xaubalance', $openingBalance);

        return $openingBalance;
    }

    public function testCanGetRecords()
    {
        $invalidId = 300;
        $ledger = self::$app->myledgerStore()->create([
            'type'              => MyLedger::TYPE_BUY_FPX,
            'typeid'            => 1,
            'accountholderid'   => $invalidId,
            'partnerid'         => $invalidId,
            'refno'             => mt_rand(),
            'transactiondate'   => new \DateTime('now', self::$app->getUserTimezone()),
            'status'            => MyLedger::STATUS_ACTIVE,
        ]);

        $ledger->debit = 0;
        $ledger->credit = 50;
        $ledger = self::$app->myledgerStore()->save($ledger);

        $ledger = self::$app->myledgerStore()->create([
            'type'              => MyLedger::TYPE_BUY_FPX,
            'typeid'            => 1,
            'accountholderid'   => self::$accountHolder->id,
            'partnerid'         => self::$accountHolder->partnerid,
            'refno'             => mt_rand(),
            'transactiondate'   => new \DateTime('now', self::$app->getUserTimezone()),
            'status'            => MyLedger::STATUS_ACTIVE,
        ]);

        $ledger->debit = 0;
        $ledger->credit = 30;
        $ledger = self::$app->myledgerStore()->save($ledger);

        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();
        $accHolder = self::$accountHolder;        

        $records = $statementManager->getStatementRecords($accHolder, self::$dateStart, self::$dateEnd);

        foreach ($records as $record) {
            $this->assertArrayHasKey('led_amountin', $record);
            $this->assertArrayHasKey('led_amountout', $record);
            $this->assertArrayHasKey('led_credit', $record);
            $this->assertArrayHasKey('led_debit', $record);

            $this->assertEquals($record['led_accountholderid'], $accHolder->id);
        }

        return $records;
    }

    /** 
     * @depends testCanGetOpeningBalance
     * @depends testCanGetRecords
     */
    public function testCanFormatRecords($openingBalance, $records)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();
        $records = $statementManager->formatStatementRecords($records);
        $accHolder = self::$accountHolder; 

        array_unshift($records, $openingBalance);

        foreach ($records as $record) {
            $this->assertArrayHasKey('led_amountin', $record);
            $this->assertArrayHasKey('led_amountout', $record);
            $this->assertArrayHasKey('led_credit', $record);
            $this->assertArrayHasKey('led_debit', $record);
            $this->assertArrayHasKey('led_amountbalance', $record);
            $this->assertArrayHasKey('led_xaubalance', $record);

            $this->assertEquals($record['led_accountholderid'], $accHolder->id);
        }
        
        return $records;
    }

    /** @depends  testCanGetRecords */
    public function testCanGetStatementAsHtml($records)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();
        $accHolder = self::$accountHolder;     

        $titleDate = 'Transaction Listing For Individual Customers as At ' . self::$dateEnd->format('d F Y');
        $html      = $statementManager->getStatementAsHtml($accHolder, $titleDate, $records);

        $this->assertContains($titleDate, $html);
        $this->assertNotRegExp('/##[\s\S]*##/', $html);

        return $html;
    }

    /** @depends testCanGetStatementAsHtml */
    public function testCanExportPdfFromHtml($html)
    {
        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();

        $pdfString = $statementManager->exportHtmlAsPdfString($html);
        $this->assertNotNull($pdfString);
        $this->assertStringStartsWith('%PDF',$pdfString);
    }

    public function testStatementDatesGMT8()
    {
        $faker = self::getFaker();
        $accHolder = self::$accountHolder;        

        $userTime = new \DateTime('now', self::$app->getUserTimezone());
        $ledger = self::$app->myledgerStore()->create([
            'type'              => MyLedger::TYPE_BUY_FPX,
            'typeid'            => $faker->randomNumber(0),
            'accountholderid'   => $accHolder->id,
            'partnerid'         => $accHolder->partnerid,
            'refno'             => $faker->word,
            'transactiondate'   => $userTime,
            'debit'             => 0,
            'credit'            => 30,
            'status'            => MyLedger::STATUS_ACTIVE,
        ]);
        $ledger = self::$app->myledgerStore()->save($ledger);   

        /** @var \Snap\manager\MyGtpStatementManager */
        $statementManager = self::$app->mygtpStatementManager();

        $records = $statementManager->getStatementRecords($accHolder, self::$dateStart, self::$dateEnd);
        $formattedRecords = $statementManager->formatStatementRecords($records);
        $record = end($formattedRecords);

        $this->assertArrayHasKey('led_transactiondate', $record);
        $this->assertEquals($userTime->format('Y-m-d H:i:s'), $record['led_transactiondate']);
    }
}
