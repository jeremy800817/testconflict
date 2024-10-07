<?php

use PHPUnit\Framework\TestCase;
use Snap\object\SnapObject;
Use Snap\manager\RedemptionManager;
use Snap\object\Redemption;

/**
 * @covers partner objects
 */
final class redemptionManagerTest extends TestCase
{
    static public $app = null;
    public function __construct() {
        $this->backupGlobals = false;
    }

    public static function setUpBeforeClass() {
        self::$app = \Snap\App::getInstance();
    }

    
    public function testThatWillBeExecuted()
    {

        $this->doSpotRedemption();
        $this->doDeliveryRedemption();
        $this->doSpecialDeliveryRedemption();
        $this->doAppointmentRedemption();
        $this->updateRedemption();

    }


    public function doSpotRedemption(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $redemptionStore = $app->redemptionStore();
        $now = new \DateTime();
        
        // create items
        $items = [];
        $items[0] = (object) [
            'serialno' => 'PAMP00001',
            'denomination' => '1g',
            'quantity' => 1,
        ];
        $items[1] = (object) [
            'serialno' => 'PAMP00002',
            'denomination' => '1g',
            'quantity' => 1,
        ];

        $deliveryDetails = (object) [
            'contactname1' => 'contactname1',
            'contactname2' => 'contactname2',
            'contact_mobile1' => 'contact_mobile1',
            'contact_mobile2' => 'contact_mobile2',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'city',
            'postcode' => 'postcode',
            'state' => 'state',
            'country' => 'country'
        ];
        $scheduleInfo = (object) [
            'branch_id' => '2',
            'datetime' => '2020-05-01 10:00:00'
        ];
        
        // create redemption
        $apiVersion = '1.0m';
        $refid = 'RDM_REF_1';
        $branchId = 1;
        $type = Redemption::TYPE_BRANCH;
        $totalWeight = '2';
        $totalQuantity = '2';
        $itemArray = (array) $items;
        $deliveryDetails = null;
        $scheduleInfo = null;
        $remarks = '_REMARKS';
        $timestamp = '2020-04-01 10:00:00';
        
        $return = $app->redemptionManager()->doRedemption($partner, $apiVersion, $refid, $branchId, $type, 
            $totalWeight, $totalQuantity, $itemArray, $deliveryDetails, $scheduleInfo, 
            $remarks, $timestamp, $priceStream = null);
        
        $this->assertEquals(Redemption::STATUS_COMPLETED, $return->status);
        
    }

    public function doDeliveryRedemption(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $redemptionStore = $app->redemptionStore();
        $now = new \DateTime();
        
        // create items
        $items = [];
        $items[0] = (object) [
            'serialno' => 'PAMP00001',
            'denomination' => '1g',
            'quantity' => 1,
        ];
        $items[1] = (object) [
            'serialno' => 'PAMP00002',
            'denomination' => '1g',
            'quantity' => 1,
        ];

        $deliveryDetails = (object) [
            'contactname1' => 'contactname1',
            'contactname2' => 'contactname2',
            'contact_mobile1' => 'contact_mobile1',
            'contact_mobile2' => 'contact_mobile2',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'city',
            'postcode' => 'postcode',
            'state' => 'state',
            'country' => 'country'
        ];
        $scheduleInfo = (object) [
            'branch_id' => '2',
            'datetime' => '2020-05-01 10:00:00'
        ];
        
        // create redemption
        $apiVersion = '1.0m';
        $refid = 'RDM_REF_1';
        $branchId = 1;
        $type = Redemption::TYPE_DELIVERY;
        $totalWeight = '2';
        $totalQuantity = '2';
        $itemArray = (array) $items;
        $deliveryDetails = $deliveryDetails;
        $scheduleInfo = null;
        $remarks = '_REMARKS';
        $timestamp = '2020-04-01 10:00:00';
        
        $return = $app->redemptionManager()->doRedemption($partner, $apiVersion, $refid, $branchId, $type, 
            $totalWeight, $totalQuantity, $itemArray, $deliveryDetails, $scheduleInfo, 
            $remarks, $timestamp, $priceStream = null);
        
        $this->assertEquals(Redemption::TYPE_DELIVERY, $return->type);
        $this->assertEquals($deliveryDetails->contactname1, $return->deliverycontactname1);
        
    }


    public function doSpecialDeliveryRedemption(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $redemptionStore = $app->redemptionStore();
        $now = new \DateTime();
        
        // create items
        $items = [];
        $items[0] = (object) [
            'serialno' => 'PAMP00001',
            'denomination' => '1g',
            'quantity' => 1,
        ];
        $items[1] = (object) [
            'serialno' => 'PAMP00002',
            'denomination' => '1g',
            'quantity' => 1,
        ];

        $deliveryDetails = (object) [
            'contactname1' => 'contactname1',
            'contactname2' => 'contactname2',
            'contact_mobile1' => 'contact_mobile1',
            'contact_mobile2' => 'contact_mobile2',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'city',
            'postcode' => 'postcode',
            'state' => 'state',
            'country' => 'country'
        ];
        $scheduleInfo = (object) [
            'branch_id' => '2',
            'datetime' => '2020-05-01 10:00:00'
        ];
        
        // create redemption
        $apiVersion = '1.0m';
        $refid = 'RDM_REF_1';
        $branchId = 1;
        $type = Redemption::TYPE_SPECIALDELIVERY;
        $totalWeight = '2';
        $totalQuantity = '2';
        $itemArray = (array) $items;
        $deliveryDetails = $deliveryDetails;
        $scheduleInfo = null;
        $remarks = '_REMARKS';
        $timestamp = '2020-04-01 10:00:00';
        
        $return = $app->redemptionManager()->doRedemption($partner, $apiVersion, $refid, $branchId, $type, 
            $totalWeight, $totalQuantity, $itemArray, $deliveryDetails, $scheduleInfo, 
            $remarks, $timestamp, $priceStream = null);
        
        $this->assertEquals(Redemption::TYPE_SPECIALDELIVERY, $return->type);
        $this->assertEquals($deliveryDetails->contactname1, $return->deliverycontactname1);
        
    }

    public function doAppointmentRedemption(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $redemptionStore = $app->redemptionStore();
        $now = new \DateTime();
        
        // create items
        $items = [];
        $items[0] = (object) [
            'serialno' => 'PAMP00001',
            'denomination' => '1g',
            'quantity' => 1,
        ];
        $items[1] = (object) [
            'serialno' => 'PAMP00002',
            'denomination' => '1g',
            'quantity' => 1,
        ];

        $deliveryDetails = (object) [
            'contactname1' => 'contactname1',
            'contactname2' => 'contactname2',
            'contact_mobile1' => 'contact_mobile1',
            'contact_mobile2' => 'contact_mobile2',
            'address1' => 'address1',
            'address2' => 'address2',
            'city' => 'city',
            'postcode' => 'postcode',
            'state' => 'state',
            'country' => 'country'
        ];
        $scheduleInfo = (object) [
            'branch_id' => '2',
            'datetime' => '2020-05-01 10:00:00'
        ];
        
        // create redemption
        $apiVersion = '1.0m';
        $refid = 'RDM_REF_1';
        $branchId = 1;
        $type = Redemption::TYPE_APPOINTMENT;
        $totalWeight = '2';
        $totalQuantity = '2';
        $itemArray = (array) $items;
        $deliveryDetails = null;
        $scheduleInfo = $scheduleInfo;
        $remarks = '_REMARKS';
        $timestamp = '2020-04-01 10:00:00';
        
        $return = $app->redemptionManager()->doRedemption($partner, $apiVersion, $refid, $branchId, $type, 
            $totalWeight, $totalQuantity, $itemArray, $deliveryDetails, $scheduleInfo, 
            $remarks, $timestamp, $priceStream = null);
        
        $this->assertEquals(Redemption::TYPE_APPOINTMENT, $return->type);
        $this->assertEquals($scheduleInfo->branch_id, $return->appointmentbranchid);
        
    }
    
    /**
     * @depends doDeliveryRedemption
     */
    public function updateRedemption(){
        $app = self::$app;
        $partner = $app->partnerStore()->getById(1);
        $this->assertEquals(1, $partner->id);
        $productStore = $app->productStore();
        $redemptionStore = $app->redemptionStore();
        $now = new \DateTime();
        
        // create items

        $deliveryDetails = (object) [
            'contactname1' => 'update_contactname1',
            'contactname2' => 'update_contactname2',
            'contact_mobile1' => 'update_contact_mobile1',
            'contact_mobile2' => 'update_contact_mobile2',
            'address1' => 'update_address1',
            'address2' => 'update_address2',
            'city' => 'update_city',
            'postcode' => 'update_postcode',
            'state' => 'update_state',
            'country' => 'update_country'
        ];
        $scheduleInfo = (object) [
            'branch_id' => '3',
            'datetime' => '2020-06-01 10:00:00'
        ];
        
        // create redemption
        $refid = 'RDM_REF_1';
        $deliveryDetails = $deliveryDetails;
        $scheduleInfo = null;
        
        $return = $app->redemptionManager()->updateRedemption($partner, $refid, $deliveryDetails, $scheduleInfo);

        $this->assertEquals('update_address1', $return->deliveryaddress1);
        
    }
   
}
?>