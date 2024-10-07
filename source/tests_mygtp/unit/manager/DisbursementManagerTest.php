<?php

use Snap\manager\MyGtpDisbursementManager;
use Snap\object\MyDisbursement;
use Snap\object\MyGoldTransaction;

final class DisbursementManagerTest extends AuthenticatedTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->userStore();
        self::$app->priceValidationStore();
        self::$app->partnerStore();
        self::$app->userStore();

        self::$app->mybankstore();
        self::$app->redemptionstore();
        self::$app->myaddressStore();
        self::$app->myaccountholderstore();
        self::$app->myconversionstore();
        self::$app->productstore();
        self::$app->orderstore();
        self::$app->mygoldtransactionstore();
        self::$app->myledgerstore();


        $faker = self::getFaker();
        $bank = self::createDummyBank();
        self::$accountHolder =  self::$app->myaccountholderStore()->getById(self::$accountHolder->id);
        self::$accountHolder = self::$app->mygtpaccountManager()->editBankAccount(self::$accountHolder, $bank, self::$accountHolder->fullname, $faker->bankAccountNumber);
    }

    function testSuccessCreateDisbursement()
    {
        $goldTx = self::createDummyGoldTransaction(self::$partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT);
        $disbursement = self::$app->mygtpdisbursementManager()->createDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder);

        $this->assertEquals(MyDisbursement::STATUS_IN_PROGRESS, $disbursement->status);
        $this->assertEquals($goldTx->refno, $disbursement->transactionrefno);

        return $disbursement;
    }

    function testSuccessCreateWalletDisbursement()
    {
        $parcusid = self::getFaker()->bankAccountNumber;
        self::$accountHolder->partnercusid = $parcusid;
        $goldTx = self::createDummyGoldTransaction(self::$partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT);
        $disbursement = self::$app->mygtpdisbursementManager()->createWalletDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder, 'WALLET');

        $this->assertEquals(MyDisbursement::STATUS_IN_PROGRESS, $disbursement->status);
        $this->assertEquals($goldTx->refno, $disbursement->transactionrefno);
        $this->assertEquals(0, $disbursement->bankid);
        $this->assertEquals('WALLET', $disbursement->accountname);
        $this->assertEquals($parcusid, $disbursement->accountnumber);

        return $disbursement;
    }

    /**
     * @depends testSuccessCreateDisbursement
     * @param MyDisbursement $disbursement
     * @return void 
     */
    function testConfirmDisbursementManual($disbursement)
    {
        /** @var MyGtpDisbursementManager $disbursementMgr */
        $disbursementMgr = self::$app->mygtpdisbursementManager();
        $newDisbursement = $disbursementMgr->manualConfirmDisbursement($disbursement, "TESTBANKREF", $disbursement->amount, false);

        $this->assertEquals(MyDisbursement::STATUS_COMPLETED, $newDisbursement->status);
    }

    /**
     * @expectedException Snap\api\exception\GeneralException
     */
    function testRejectCompletedDisbursement()
    {

        $partner = self::createDummyPartner();

        $goldTx = self::createDummyGoldTransaction($partner, self::$accountHolder, null, null, false, MyGoldTransaction::SETTLEMENT_METHOD_BANKACCOUNT);
        $disbursement = self::$app->mygtpdisbursementManager()->createDisbursementForGoldTransaction($goldTx, self::$partner, self::$accountHolder);

        $disbursement->status = MyDisbursement::STATUS_COMPLETED;
        /** @var MyGtpDisbursementManager $disbursementMgr */
        $disbursementMgr = self::$app->mygtpdisbursementManager();
        $newDisbursement = $disbursementMgr->manualConfirmDisbursement($disbursement, "TESTBANKREF", $disbursement->amount, false);

        // Error should happen here
        //$this->assertEquals(MyDisbursement::STATUS_COMPLETED, $newDisbursement->status);
    }

}
