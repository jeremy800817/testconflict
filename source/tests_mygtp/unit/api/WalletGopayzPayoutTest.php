<?php

use Snap\api\payout\GoPayzPayout;
use Snap\IObservation;
use Snap\manager\MyGtpDisbursementManager;
use Snap\object\MyGoldTransaction;

class TestGopayzPayout extends GoPayzPayout {

    public function __construct($app)
    {
        $this->app = $app;
    }
}

class GoPayzPayoutTest extends BaseTestCase
{
    static $accountHolder = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        $accountHolder = self::createDummyAccountHolder();
        $accountHolder->bankid = 0;
        $accountHolder->accountname = $accountHolder->fullname;
        $accountHolder->accountnumber = '500000218591';
        $accountHolder->partnercusid = '500000218591';

        self::$accountHolder = self::$app->myaccountholderStore()->save($accountHolder);       
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }


    private static function sampleData($refno ,$status = 'Successful', $statusCode = '00')
    {
        $data = [
            'data' => [
                'H,ACE,20210705,0001',
                'D,0000001,'. $refno . ',ACE GOLD TRADING,1,23429,500000218591,20210705,GOLD SELL,'. $statusCode .','.$status,
                'T, 1, 23429, 0, 0, 1, 23429'
            ]
        ];

        return $data;
    }

    public function testCanHandleSuccessResponse()
    {                
        $goldTx = self::createDummyGoldTransaction(self::$partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_WALLET);
        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');

        $dbmMgr = \Mockery::mock(MyGtpDisbursementManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $gopayz = \Mockery::mock(TestGopayzPayout::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $gopayz->attach($dbmMgr);

        $dbmMgr->shouldReceive('onDisbursementCompleted')->once();        

        /** @var Snap\api\payout\GoPayzPayout $gopayz */
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Successful', '00'), null);
    }

    public function testCanHandleFailedResponse()
    {                       
        $dbmMgr = \Mockery::mock(MyGtpDisbursementManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $gopayz = \Mockery::mock(TestGopayzPayout::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        /** @var Snap\api\payout\GoPayzPayout $gopayz */
        $gopayz->attach($dbmMgr);

        $dbmMgr->shouldReceive('onDisbursementChange')->atLeast()->times(4);        
        
        $goldTx = self::createDummyGoldTransaction(self::$partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_WALLET);

        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Failed', '02'), null);
        $disbursement->transactionrefno = $disbursement->id . $disbursement->transactionrefno;
        self::$app->mydisbursementStore()->save($disbursement);       
        
        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Failed', '02'), null);
        $disbursement->transactionrefno = $disbursement->id . $disbursement->transactionrefno;
        self::$app->mydisbursementStore()->save($disbursement);       

        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Failed', '02'), null);
        $disbursement->transactionrefno = $disbursement->id . $disbursement->transactionrefno;
        self::$app->mydisbursementStore()->save($disbursement);       

        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');        
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Failed', '02'), null);
        $count = self::$app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', 'LIKE', '%'.$disbursement->transactionrefno.'%')->count();
        $this->assertEquals(4, $count);
    }

    public function testCanHandleInvalidResponse()
    {                       
        $dbmMgr = \Mockery::mock(MyGtpDisbursementManager::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        $gopayz = \Mockery::mock(TestGopayzPayout::class, [self::$app])->shouldAllowMockingProtectedMethods()->makePartial();
        /** @var Snap\api\payout\GoPayzPayout $gopayz */
        $gopayz->attach($dbmMgr);

        $dbmMgr->shouldReceive('onDisbursementChange')->atMost()->times(2);        
        $dbmMgr->shouldReceive('onDisbursementRejected')->once();        
        
        $goldTx = self::createDummyGoldTransaction(self::$partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_WALLET);

        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Invalid Account Number/Unique Reference Number', '01'), null);
        $disbursement->transactionrefno = $disbursement->id . $disbursement->transactionrefno;
        self::$app->mydisbursementStore()->save($disbursement);       
        
        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Invalid Account Number/Unique Reference Number', '01'), null);
        $disbursement->transactionrefno = $disbursement->id . $disbursement->transactionrefno;
        self::$app->mydisbursementStore()->save($disbursement);       

        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'GoPayzTestPayout');        
        $gopayz->handleResponse($this->sampleData($disbursement->refno, 'Invalid Account Number/Unique Reference Number', '01'), null);
        $count = self::$app->mydisbursementStore()->searchTable()->select()->where('transactionrefno', 'LIKE', '%'.$disbursement->transactionrefno.'%')->count();
        $this->assertEquals(3, $count);
    }
}