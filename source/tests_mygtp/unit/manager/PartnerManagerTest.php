<?php

use Snap\object\VaultItem;
use Snap\object\VaultLocation;

class PartnerManagerTest extends AuthenticatedTestCase
{
    private static $testVaultLocation = null;
    private static $defVaultLocation = null;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->eventmessageStore();

        $vaultLocation = self::$app->vaultLocationStore()->create([
            'partnerid'         => self::$partner->id,
            'name'              => "Test Vault Location",
            'type'              => VaultLocation::TYPE_END,
            'defaultlocation'   => 0,
            'status'            => VaultLocation::STATUS_ACTIVE
        ]);
        $vaultLocation = self::$app->vaultLocationStore()->save($vaultLocation);
        self::$testVaultLocation = $vaultLocation;

        self::$defVaultLocation = self::$app->vaultLocationStore()->searchTable()->select()
                                        ->where('defaultlocation', 1)
                                        ->andWhere('type', VaultLocation::TYPE_START)
                                        ->one();

        // vw_mygoldtransaction dependency
        self::$app->orderStore();
        self::$app->mybankStore();
    }

    public function testVaultTransferAddedToBalance()
    {
        // Create a vault item to be transferred to test vault
        $vaultItem = self::$app->vaultItemStore()->create([
            'partnerid'             => self::$partner->id,
            'vaultlocationid'       => 1,
            'productid'             => 1,
            'weight'                => 300.00,
            'brand'                 => 'test',
            'serialno'              => 'TESTSERIAL',
            'utilised'              => 0,
            'allocated'             => 0,
            'deliveryordernumber'   => '',
            'allocatedon'           => null,
            'movetovaultlocationid' => 0,
            'status'                => VaultItem::STATUS_ACTIVE
        ]);
        $vaultItem = self::$app->vaultItemStore()->save($vaultItem);

        $bankVaultMgr = self::$app->bankvaultManager();
        $vaultItems = $bankVaultMgr->requestMoveItemToLocationBmmb([$vaultItem], self::$testVaultLocation);

        // Confirm transfer
        $vaultItem = self::$app->bankvaultManager()->markItemsArrivedAtLocation($vaultItems)[0];

        // Get balance
        $partnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance(self::$partner);

        $this->assertGreaterThan(0, $partnerBalance);
        return $vaultItem;
    }

    /**
     * @param VaultItem     $vaultItem
     * @depends testVaultTransferAddedToBalance
     */
    public function testVaultTransferDeductedFromBalance($vaultItem)
    {
        /** @var \Snap\manager\BankVaultManager */
        $bankVaultMgr = self::$app->bankvaultManager();
        $vaultItems = $bankVaultMgr->requestMoveItemToLocation([$vaultItem], self::$defVaultLocation);

        $vaultItem = $bankVaultMgr->markItemsArrivedAtLocation($vaultItems)[0];
        $partnerBalance = self::$app->mygtppartnerManager()->getPartnerInventoryBalance(self::$partner);
        $this->assertEquals(0, $partnerBalance);
    }

}

?>