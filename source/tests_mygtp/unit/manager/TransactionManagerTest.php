<?php

use Snap\api\fpx\BaseFpx;
use Snap\IObservation;
use Snap\IObserver;
use Snap\manager\MyGtpPartnerManager;
use Snap\manager\MyGtpTransactionManager;
use Snap\object\MyGoldTransaction;
use Snap\object\MyLedger;
use Snap\object\MyPaymentDetail;
use Snap\object\Order;

class TransactionManagerTest extends AuthenticatedTestCase
{
    private static $payment = null;
    private static $order = null;
    private static $goldTransaction = null;
    private static $product = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->eventtriggerStore();
        self::$app->mybankStore();

        $now = new \DateTime('now', self::$app->getServerTimezone());
        $xau = self::getFaker()->numberBetween(1,10);
        $amount = $xau * self::getFaker()->randomFloat(3, 245, 248);

        $order = self::$app->orderStore()->create([
            'partnerid' => self::$accountHolder->partnerid,
            'partnerrefid' => self::getFaker()->word,
            'orderno'      => self::getFaker()->word,
            'buyerid'       => self::$accountHolder->id,
            'apiversion'    => '1.0B',
            'byweight'  => 1,
            'xau'       => 2,
            'bookingon'  => $now->format("Y-m-d H:i:s"),
            'bookingprice'  => 246.03,
            'bookingpricestreamid'  => 1,
            'confirmon'  => $now->format("Y-m-d H:i:s"),
            'confirmprice'  => 246.03,
            'confirmby'  => 1,
            'confirmpricestreamid'  => 1,
            'confirmreference'  => '',
            'salespersonid' => 1,
            'pricestreamid' => 1,
            'productid'     => 1,
            'type'          => Order::TYPE_COMPANYSELL,
            'isspot'        => 1,
            'amount'        => $amount,
            'status'        => Order::STATUS_PENDING,
            'cancelon'      => '0000-00-00 00:00:00',
            'cancelby'      => 0,
            'cancelpricestreamid'   => 0,
            'notifyurl' => '',
            'reconciled' => 1,
            'reconciledon' => $now->format('Y-m-d H:i:s'),
            'reconciledby' => 1,

        ]);
        $order = self::$app->orderStore()->save($order);
        self::$order = $order;

        $gt = self::$app->mygoldtransactionStore()->create([
            'originalamount'    => self::getFaker()->randomFloat(2),
            'settlementmethod'  => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
            'salespersoncode'   => self::getFaker()->word,
            'refno'             => "GT".self::getFaker()->word,
            'orderid'           => $order->id,
            'status'            => MyGoldTransaction::STATUS_PENDING_PAYMENT,
        ]);
        $gt = self::$app->mygoldtransactionStore()->save($gt);
        self::$goldTransaction = $gt;

        $product = self::$app->productStore()->create([
            'categoryid'    => 1,
            'code'          => 'DG-TEST',
            'name'          => 'Test Product',
            'weight'        => '0.1',
            'companycanbuy' => 1,
            'companycansell' => 1,
            'trxbyweight'   => 1,
            'trxbycurrency'   => 1,
            'deliverable'   => 1,
            'sapitemcode'   => 'MYGTPTEST',
            'status'        => 1
        ]);
        $product = self::$app->productStore()->save($product);
        self::$product = $product;
    }

    public function testGenerateRefno()
    {
        $store = self::$app->mygoldtransactionStore();
        $refno = self::$app->mygtptransactionManager()->generateRefNo("TST", $store);

        $this->assertNotNull($refno);
        $this->assertStringStartsWith("TST", $refno);
    }

    public function testPaymentSuccessCallback()
    {
        $txMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $txMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $txMgr->shouldReceive('onPaymentSuccess')->atLeast()->once();

        $fpx = \Mockery::mock(BaseFpx::class);
        $payment = $txMgr->createPaymentDetailForTransaction(self::$goldTransaction, self::$order, self::$accountHolder->getPartner(), self::$product);
        $payment->status = MyPaymentDetail::STATUS_SUCCESS;
        $txMgr->onObservableEventFired($fpx, new IObservation($payment, IObservation::ACTION_CONFIRM, 0));
    }

    public function testPrintReceiptPage()
    {
        $this->markTestSkipped("We are now returning empty page instead");
        $txMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $txMgr->shouldAllowMockingProtectedMethods()->makePartial();

        $payment = $txMgr->createPaymentDetailForTransaction(self::$goldTransaction, self::$order, self::$accountHolder->getPartner(), self::$product);
        $page = $txMgr->createReceiptPage($payment);

        $this->assertNotNull($page);
        $expectedMsg = "Your order has been received";
        $this->assertContains($expectedMsg, $page, "Receipt page does not contain '$expectedMsg'");
    }

    public function testLedgerAdded()
    {
        // Stub sapBookNewOrder
        $apiMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $apiMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $apiMgr->shouldReceive('sapBookNewOrder')->andReturn([['success' => 'Y']]);

        // Skip notifications
        $txMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $txMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $txMgr->shouldReceive('notify')->andReturns();

        // Skip submitting to SAP
        $oldAutoSubmit = self::$partner->autosubmitorder;
        self::$partner->autosubmitorder = 0;
        self::$partner = self::$app->partnerStore()->save(self::$partner);

        // Run confirm gold tx
        self::$goldTransaction = $txMgr->confirmBookGoldTransaction(self::$goldTransaction, MyLedger::TYPE_BUY_FPX, $apiMgr);

        self::$partner->autosubmitorder = $oldAutoSubmit;
        self::$partner = self::$app->partnerStore()->save(self::$partner);

        // Assert accountledger is added
        $ledger = self::$app->myledgerStore()->getByField('refno', self::$goldTransaction->refno);
        $this->assertInstanceOf(MyLedger::class, $ledger);
        $this->assertEquals(MyGoldTransaction::STATUS_PAID, self::$goldTransaction->status);
    }

    /**
     * @depends testLedgerAdded
     */
    public function testPartnerInventoryDeducted()
    {
        $ledger = self::$app->myledgerStore()->searchTable()->select()
                    ->where('partnerid', self::$partner->id)
                    ->andWhere('accountholderid', 0)
                    ->andWhere('typeid', self::$goldTransaction->id)
                    ->andWhere('type', MyLedger::TYPE_ACESELL)
                    ->andWhere('status', MyLedger::STATUS_ACTIVE)
                    ->one();

        $this->assertInstanceOf(MyLedger::class, $ledger);
        $this->assertGreaterThan(0, $ledger->debit);
    }

    /**
     * @depends testLedgerAdded
     */
    public function testConfirmGoldTransactionBookingSuccess()
    {
        // Stub submitting to SAP
        $txMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $txMgr->shouldAllowMockingProtectedMethods()->makePartial();
        $txMgr->shouldReceive('submitOrderToSAP')->andReturn(true);

        $goldTx = $txMgr->confirmGoldTransaction(self::$goldTransaction);
        $order = $goldTx->getOrder();

        $this->assertEquals(MyGoldTransaction::STATUS_CONFIRMED, $goldTx->status);
        $this->assertEquals(Order::STATUS_CONFIRMED, $order->status);
    }

    /**
     * @expectedException \Snap\api\exception\MyGtpPartnerInsufficientGoldBalance
     */
    public function testInventoryReservedForPendingTransaction()
    {
        $partnerMgr = \Mockery::mock(MyGtpPartnerManager::class, [self::$app]);
        $partnerMgr->shouldReceive('getPartnerInventoryBalance')->andReturn(10.0);

        $now = new \DateTime();
        $order = self::$app->orderStore()->create([
            'xau'           => 5.00,
            'partnerid'     => self::$partner->id,
            'buyerid'       => self::$accountHolder->id,
            'partnerrefid'  => 'TEST',
            'orderno'       => 'TEST01',
            'pricestreamid' => 1,
            'productid'     => 1,
            'salespersonid' => 0,
            'apiversion'    => 'test',
            'isspot'        => 0,
            'byweight'      => 1,
            'type'          => Order::TYPE_COMPANYSELL,
            'bookingon'     => $now,
            'bookingpricestreamid' => 1,
            'confirmon'     => $now,
            'cancelon'     => $now,
            'cancelby'     => 1,
            'confirmby'     => 1,
            'confirmreference'     => 1,
            'confirmpricestreamid' => 1,
            'cancelpricestreamid' => 0,
            'amount'        => 5 * 5,
            'notifyurl'     => '',
            'reconciled'     => 1,
            'reconciledon'     => $now,
            'reconciledby'     => 1,
            'status'        => 1,
        ]);
        $order = self::$app->orderStore()->save($order);

        $goldTx = self::$app->mygoldtransactionStore()->create([
            'originalamount' => $order->xau,
            'settlementmethod'  => MyGoldTransaction::SETTLEMENT_METHOD_FPX,
            'salespersoncode'   => '',
            'refno'             => 'test',
            'orderid'           => $order->id,
            'status'            => MyGoldTransaction::STATUS_PENDING_PAYMENT,
        ]);
        $goldTx = self::$app->mygoldtransactionStore()->save($goldTx);

        $goldTxMgr = \Mockery::mock(MyGtpTransactionManager::class, [self::$app]);
        $goldTxMgr->shouldAllowMockingProtectedMethods()->makePartial();

        $goldTxMgr->checkPartnerBalanceSufficient(self::$partner, 10, $partnerMgr);

    }

}