<?php
use Snap\object\SnapObject;
Use Snap\object\PriceValidation;

/**
 * @covers medicalrecord objects
 * @backupGlobals disabled
 */
final class PriceValidationTest extends LazyTestCase
{

    /**
     * Setup Test Configurations
     */
    public function setUp()
    {
        parent::setUpBeforeClass('pricevalidation', PriceValidation::class, 'pva');
    }

    public function addTestProvider(): array
    {
        return [
            [1, 1, 1, 'v2.0', 12345, PriceValidation::REQUEST_COMPANYBUY, 12345, 12345, '2018-10-21 00:00:00', 1, 'reference', '2018-10-21 00:00:00', 1]
        ];
    }

    /**
     * @dataProvider addTestProvider
     */
    public function testAddNew($apilogid, $partnerid, $pricestreamid, $apiversion, $validityref, $requestedtype, $premiumfee, $refineryfee, $validtill, $orderid, $reference, $timestamp, $expectedId)
    {
        $store = $this->setupStores();

        $new = $store->create();
        $new->apilogid = $apilogid;
        $new->partnerid = $partnerid;
        $new->pricestreamid = $pricestreamid;
        $new->apiversion = $apiversion;
        $new->validityref = $validityref;
        $new->requestedtype = $requestedtype;
        $new->premiumfee = $premiumfee;
        $new->refineryfee = $refineryfee;
        $new->validtill = new \DateTime($validtill);
        $new->orderid = $orderid;
        $new->reference = $reference;
        $new->timestamp = new \DateTime($timestamp);
        $new->createdon = new \DateTime('now');
        $new->modifiedon = new \DateTime('now');
        $new->status = SnapObject::STATUS_ACTIVE;
        $new->createdby = 1;
        $new->modifiedby = 1;
        $saved = $store->save($new);

        $this->assertNotNull($saved);
        $this->assertGreaterThan(0, $saved->id);
        $this->assertEquals($expectedId, $saved->id);

        $inDB = $store->getById($saved->id);
        $this->assertNotNull($inDB);
        $this->assertEquals($saved->id, $inDB->id);
        foreach (['apilogid', 'partnerid', 'pricestreamid', 'apiversion', 'validityref', 'requestedtype', 'premiumfee', 'refineryfee', 'validtill', 'orderid', 'reference', 'timestamp'] as $field) {
            if ( 'validtill' !== $field && 'timestamp' !== $field) {
                $this->assertEquals($new->{$field}, $inDB->{$field});
            } else {
                $datetime = date_format($new->{$field}, "Y-m-d");
                $datetime2 = date_format($inDB->{$field}, "Y-m-d");

                $this->assertEquals($datetime, $datetime2);
            }
        }
    }

    /**
     * @depends testAddNew
     */
    public function testRead()
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $this->assertNotNull($inDB);
        $this->assertEquals(1, $inDB->id);
    }

    /**
     * @depends testAddNew
     */
    public function testUpdate()
    {
        $store = $this->setupStores();
        $inDB = $store->getById(1);
        $inDB->status = SnapObject::STATUS_INACTIVE;
        $updated = $store->save($inDB);
        $this->assertEquals("0", $updated->status);
    }
}

?>