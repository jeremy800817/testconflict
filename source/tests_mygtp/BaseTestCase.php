<?php
use Snap\App;
use Snap\object\MyAccountHolder;
use Snap\object\MyLocalizedContent;
use Snap\object\MyOccupationCategory;
use Snap\object\MyOccupationSubCategory;
use Snap\object\Partner;
use Snap\object\MyBank;
use Snap\object\MyCloseReason;
use Snap\object\MyGoldTransaction;
use Snap\object\MyPartnerSetting;
use Snap\object\MyPushNotification;
use Snap\object\Order;
use Snap\object\Product;
use Snap\object\Tag;
use Snap\object\TradingSchedule;

/**
 * Template base class
 *
 * @backupGlobals disabled
 */
class BaseTestCase extends \Mockery\Adapter\Phpunit\MockeryTestCase
{

    /** @var App $app */
    public static $app = null;

    /** @var array $faker */
    private static $fakers = null;

    /** @var Partner $partner */
    protected static $partner = null;

    public function __construct()
    {
        $this->backupGlobals = false;
    }

    /**
     * Returns a faker generator object
     *
     * Available locales : zh_CN, en_US, ms_MY . Refer to https://github.com/fzaninotto/Faker#localization for available formatters
     * @return \Faker\Generator
     */
    public static function getFaker(string $locale = 'ms_MY')
    {
        if (! self::$fakers[$locale]) {
            $faker = \Faker\Factory::create($locale);
            $faker->seed(1);        // Get same data for testing purpose
            self::$fakers[$locale] = $faker;
        }

        return self::$fakers[$locale];
    }

    public static function setupBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$app = App::getInstance();
        self::$partner = self::createDummyPartner();
        self::$app->eventtriggerstore();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

    protected static function createDummyOccupationCategory($isPoliticallyExposed = false)
    {
        $enFaker = self::getFaker('en_US');
        $bmFaker = self::getFaker("ms_MY");
        $cnFaker = self::getFaker('zh_CN');
        
        $occupationCat = self::$app->myoccupationcategoryStore()->create([
            //'language'  => MyLocalizedContent::LANG_BAHASA,
            //'description'   => $faker->sentence($faker->numberBetween(1,4)),
            //'category'  => $faker->word,
            'politicallyexposed' => (int)filter_var($isPoliticallyExposed, FILTER_VALIDATE_BOOLEAN),
            'status'    => MyOccupationCategory::STATUS_ACTIVE
        ]);

        // Create Malay Content
        $occupationCat->language = MyLocalizedContent::LANG_BAHASA;
        $occupationCat->description = $bmFaker->sentence($bmFaker->numberBetween(1,4));
        $occupationCat->category =  $bmFaker->word;
        $occupationCat = self::$app->myoccupationcategoryStore()->save($occupationCat);

        // Create Mandarin Content
        $occupationCat->language = MyLocalizedContent::LANG_CHINESE;
        $occupationCat->description = $cnFaker->sentence($cnFaker->numberBetween(1,4));
        $occupationCat->category =  $cnFaker->word;
        $occupationCat = self::$app->myoccupationcategoryStore()->save($occupationCat);

        // Create English Content
        $occupationCat->language = MyLocalizedContent::LANG_ENGLISH;
        $occupationCat->description = $enFaker->sentence($enFaker->numberBetween(1,4));
        $occupationCat->category =  $enFaker->word;
        $occupationCat = self::$app->myoccupationcategoryStore()->save($occupationCat);
        
        return $occupationCat;
    }

    protected static function createDummyOccupationSubCategory($occupationCat, $isPoliticallyExposed = false)
    {

        $enFaker = self::getFaker('en_US');
        $bmFaker = self::getFaker("ms_MY");
        $cnFaker = self::getFaker('zh_CN');

        $occupationSubCat = self::$app->myoccupationsubcategoryStore()->create([
            'occupationcategoryid' => $occupationCat->id,
            //'language'  => MyLocalizedContent::LANG_BAHASA,
            'politicallyexposed' => (int)filter_var($isPoliticallyExposed, FILTER_VALIDATE_BOOLEAN),
            //'name'   => $faker->sentence($faker->numberBetween(1,4)),
            //'code'  => $faker->word,
            'status'    => MyOccupationSubCategory::STATUS_ACTIVE
        ]);

        // Create Malay Content
        $occupationSubCat->language = MyLocalizedContent::LANG_BAHASA;
        $occupationSubCat->name = $bmFaker->sentence($bmFaker->numberBetween(1,4));
        $occupationSubCat->code =  $bmFaker->word;
        $occupationSubCat = self::$app->myoccupationsubcategoryStore()->save($occupationSubCat);

        // Create Mandarin Content
        $occupationSubCat->language = MyLocalizedContent::LANG_CHINESE;
        $occupationSubCat->name = $cnFaker->sentence($cnFaker->numberBetween(1,4));
        $occupationSubCat->code =  $cnFaker->word;
        $occupationSubCat = self::$app->myoccupationsubcategoryStore()->save($occupationSubCat);
        
        // Create English Content
        $occupationSubCat->language = MyLocalizedContent::LANG_ENGLISH;
        $occupationSubCat->name = $enFaker->sentence($enFaker->numberBetween(1,4));
        $occupationSubCat->code =  $enFaker->word;
        $occupationSubCat = self::$app->myoccupationsubcategoryStore()->save($occupationSubCat);

        return $occupationSubCat;
    }

    protected static function createDummyCode()
    {
        $faker = self::getFaker("ms_MY");
        $word = substr(strtoupper($faker->word), 0, 5);
        $code = $faker->numerify($word."###");
        return $code;
    }

    protected static function createDummyPartner()
    {
        $faker = self::getFaker("ms_MY");
        // Create test_user
        $userStore = self::$app->userStore();

        $partnerCode = self::createDummyCode();

        // Create TS for partner
        $tag = self::$app->tagStore()->create([
            'category'  => Tag::TRADINGSCHEDULE_CATEGORY,
            'code'      => "TESTTS".":".$partnerCode,
            'description' => $partnerCode . " trading schedule",
            'value'     => "TSC".$partnerCode ,
            'status'    => 1
        ]);
        $tag = self::$app->tagStore()->save($tag);

        $tsStart = new \DateTime('1 year ago', self::$app->getUserTimezone());
        $tsStart->setTime(0,0,0);
        $tsStart->setTimezone(self::$app->getServerTimezone());
        $tsEnd = new \DateTime('1 year', self::$app->getUserTimezone());
        $tsEnd->setTime(23, 59, 59);
        $tsEnd->setTimezone(self::$app->getServerTimezone());

        $ts = self::$app->tradingscheduleStore()->create([
            'categoryid' => $tag->id,
            'type' => TradingSchedule::TYPE_DAILY,
            'startat' => $tsStart->format("Y-m-d H:i:s"),
            'endat' => $tsEnd->format("Y-m-d H:i:s"),
            'status' => 1
        ]);
        $ts = self::$app->tradingscheduleStore()->save($ts);

        // Create Price source & price provider
        $ps = self::$app->tagStore()->create([
            'category'  => Tag::PRICESOURCE_CATEGORY,
            'code'      => "TESTPS".":".$partnerCode,
            'description' => $partnerCode . " price source",
            'value'     => $partnerCode ,
            'status'    => 1
        ]);
        $ps = self::$app->tagStore()->save($ps);

        $pp = self::$app->priceproviderStore()->create([
            'code'      => "TEST.".$partnerCode,
            'name'      => "Testing Price Provider for " .$partnerCode,
            'pricesourceid'     => $ps->id,
            'productcategoryid' => 3,       // Assume copy data from tag table
            'pullmode'          => 0,
            'currencyid'        => 5,
            'whitelistip'       => '',
            'url'               => '',
            'connectinfo'       => 'invaliduri',
            'lapsetimeallowance'=> 200,
            'futureorderstrategy' => '\Snap\util\pricematch\DefaultMatchStrategy',
            'futureorderparams' => 10,
            'status'            => 1
        ]);
        $pp = self::$app->priceproviderStore()->save($pp);

        $partner = self::$app->partnerStore()->create([
            'code' => $partnerCode,
            'name' => $faker->company,
            'address' => $faker->address,
            'postcode' => $faker->postcode,
            'state' => substr($faker->state, 0, 20),
            'pricesourceid' => $ps->id,
            'tradingscheduleid' => $tag->id,    // This is tag id
            'sapcompanysellcode1' => self::createDummyCode(),
            'sapcompanybuycode1' => self::createDummyCode(),
            'sapcompanysellcode2' => self::createDummyCode(),
            'sapcompanybuycode2' => self::createDummyCode(),
            'dailybuylimitxau' => 1.2,
            'dailyselllimitxau' => 1.2,
            'pricelapsetimeallowance' => 12,
            'orderingmode' => Partner::MODE_WEB,
            'autosubmitorder' => 1,
            'autocreatematchedorder' => 1,
            'orderconfirmallowance' => 1000,
            'ordercancelallowance' => 1,
            'apikey' => 1234,
            'type' => Partner::TYPE_CUSTOMER,
            'status' => Partner::STATUS_ACTIVE,
        ]);

        $partner = self::$app->partnerStore()->save($partner);
        $partner->registerBranch(null, self::createDummyCode(), 'A Branch', self::createDummyCode(), $faker->address, $faker->postcode, $faker->city, $faker->mobileNumber(true, false), 1);
        $partner = self::$app->partnerStore()->save($partner);

        self::createPartnerSetting($partner);
        return $partner;
    }

    /**
     * TODO: Use builder pattern for partner
     * @param Partner $partner 
     * @return void 
     */
    protected static function createPartnerSetting(Partner $partner)
    {
        $partnerSetting = self::$app->mypartnersettingStore()->create([
            'sapdgcode' => "DG-999-9-".$partner->code,
            'sapmintedwhs' => "TEST_WHS",
            'sapkilobarwhs'=> "TEST_WHS",
            'mininitialxau' => 1,
            'minbalancexau' => 1,
            'mindisbursement' => 1,
            'verifyachemail' => 1,
            'verifyachphone' => 1,
            'verifyachpin' => 0,
            'achcancloseaccount' => 1,
            'skipekyc' => 0,
            'skipamla' => 0,
            'amlablacklistimmediately' => 1,
            'ekycprovider' => "TestKycProvider",
            'partnerpaymentprovider' => "",
            'companypaymentprovider' => "",
            'payoutprovider' => "\Snap\api\payout\CompanyManualPayout",
            'transactionfee' => 1,
            'payoutfee' => 1,
            'courierfee' => 1,
            'storagefeeperannum' => 1,
            'adminfeeperannum' => 1,
            'minstoragecharge' => 1,
            'minstoragefeethreshold' => 1,
            'maxxauperdelivery' => 100,
            'maxpcsperdelivery' => 30,
            'dgpartnersellcommission' => 1,
            'dgpartnerbuycommission' => 1,
            'dgpeakpartnersellcommission' => 1,
            'dgpeakpartnerbuycommission' => 1,
            'dgpeakhourfrom' => 1,
            'dgpeakhourto' => 1,
            'dgpartnersellcommission' => 1,
            'dgpartnerbuycommission' => 1,
            'dgacesellcommission' => 1,
            'dgacebuycommission' => 1,
            'pricealertvaliddays' => 3,
            'strictinventoryutilisation' => 1,
            'enablepushnotification' => 1,
            'uniquenric' => 1,
            'accesstokenlifetime' => 300,
            'refreshtokenlifetime' => 600,
            'partnerid' => $partner->id,
            'status' => MyPartnerSetting::STATUS_ACTIVE
        ]);
        self::$app->mypartnersettingStore()->save($partnerSetting);
    }

    /**
     * Registers an accountholder with the password "dummy"
     * @param Partner $partner 
     * @param bool $createPartner 
     * @param string $password 
     * @return MyAccountHolder 
     * @throws InvalidArgumentException 
     */
    protected static function createDummyAccountHolder($partner = null, $createPartner = false, $password = "dummy")
    {
        $faker = self::getFaker("ms_MY");

        if (!$partner) {
            $partner = $createPartner ? self::createDummyPartner() : self::$partner;
        }

        $occupationCat =  self::createDummyOccupationCategory(false);
        $occupationSubCat = self::createDummyOccupationSubCategory($occupationCat, false);
        $branchCode = current($partner->getBranches())->code;
        $accountHolder = self::$app->mygtpaccountManager()->register(
            $partner,
            $faker->firstName . $faker->lastName,
            $faker->myKadNumber,
            '+' . $faker->mobileNumber(true, false),
            $occupationCat,
            //$faker->jobTitle,
            $occupationSubCat,
            //self::createDummyOccupationSubCategory($occupationCat),
            $faker->safeEmail,
            $password,
            MyLocalizedContent::LANG_ENGLISH,
            $branchCode,
            $faker->name,
            $faker->myKadNumber,
            $faker->mobileNumber(true, false),
            $faker->safeEmail,
            $faker->address,
            $faker->word,
            '', '', true
        );

        $accountHolder->addAddress($faker->streetAddress, null, $faker->city, $faker->postcode, $faker->state);

        return $accountHolder;
    }

    protected static function createDummyBank()
    {
        $faker = self::getFaker("ms_MY");
        $bank = self::$app->mybankStore()->create([
            'language' => MyLocalizedContent::LANG_BAHASA,
            'name'     => $faker->sentence($faker->numberBetween(1, 4)),
            'code'     => $faker->word,
            'status'   => MyBank::STATUS_ACTIVE
        ]);

        $bank = self::$app->mybankStore()->save($bank);
        return $bank;
    }

    protected static function createDummyCloseReason()
    {
        $faker = self::getFaker("ms_MY");
        $closeReason = self::$app->myclosereasonStore()->create([
            'language'  => MyLocalizedContent::LANG_BAHASA,
            'reason'   => $faker->sentence($faker->numberBetween(1, 4)),
            'code'  => $faker->word,
            'status'    => MyCloseReason::STATUS_ACTIVE
        ]);

        $closeReason = self::$app->myclosereasonStore()->save($closeReason);
        return $closeReason;
    }

    protected static function createDummyPushNotification($eventType, $rank, $validFrom = null, $validTo = null)
    {
        if (!$validFrom) {
            $validFrom = new \DateTime('yesterday', self::$app->getUserTimezone());
        }
        
        if (!$validTo) {            
            $validTo = new \DateTime('tomorrow', self::$app->getUserTimezone());
        }

        $faker = self::getFaker("ms_MY");
        
        $myPushNotification = self::$app->mypushnotificationStore()->create([
            'language' => MyLocalizedContent::LANG_BAHASA,
            'code' => $faker->sentence($faker->numberBetween(1, 4)),
            'title' => $faker->sentence(),
            'body' => $faker->paragraph(),
            'eventtype' => $eventType,
            'validfrom' => $validFrom->format('Y-m-d H:i:s'),
            'validto' => $validTo->format('Y-m-d H:i:s'),
            'rank' => $rank,
            'status' => MyPushNotification::STATUS_ACTIVE
        ]);

        $myPushNotification = self::$app->mypushnotificationStore()->save($myPushNotification);
        return $myPushNotification;
    }

    protected static function createDummyProduct($randomWeight = true, $weight = 0, $canBuy = true, $canSell = true, $deliverable = true)
    {
        $faker = self::getFaker();
        if ($randomWeight) {
            $weight = $faker->randomFloat(2,1,1000);
        }

        $product = self::$app->productStore()->create([
            'categoryid' => 1,
            'code'      => $faker->word,
            'name'      => $faker->sentence($faker->numberBetween(1, 4)),
            'weight'    => $weight,
            'companycansell'    => $canSell ? 1 : 0,
            'companycanbuy'     => $canBuy ? 1 : 0,
            'trxbyweight'       => 1,
            'trxbycurrency'     => 1,
            'deliverable'       => $deliverable ? 1 : 0,
            'status'            => Product::STATUS_ACTIVE
        ]);
        $product->sapitemcode = $product->code;
        $product = self::$app->productStore()->save($product);
        return $product;
    }

    /**
     * Create dummy Order object
     * @param Partner $partner 
     * @param MyAccountHolder $accHolder 
     * @param float $xau
     * @param float $ppg
     * @param string $partnerrefno 
     * @param bool $isCompanyBuy 
     * @param bool $statusPending 
     * @return \Snap\object\Order
     * @throws InvalidArgumentException 
     */
    protected static function createDummyOrder(Partner $partner, MyAccountHolder $accHolder, $xau = null, $ppg = null, $partnerrefno = '', bool $isCompanyBuy, $statusPending = true )
    {
        $faker = self::getFaker();

        $now = new \DateTime();
        $now->setTimezone(self::$app->getUserTimezone());

        // /** @var MyPartnerSetting $partnerSettings */
        // $partnerSettings = self::$app->mypartnerSettingStore()->getByField('partnerid', $partner->id);
        $randomXau = $xau ?? $faker->randomFloat(2,1,100);
        $randomPpg = $ppg ?? $faker->randomFloat(2,100,300);
        $order = self::$app->orderStore()->create([
            'partnerid' => $partner->id,
            'partnerrefid' => 0 < strlen($partnerrefno) ? $partnerrefno : $faker->word,
            'orderno'      => $faker->word,
            'buyerid'       => $accHolder->id,
            'apiversion'    => "TEST",
            'byweight'  => 1,
            'xau'       => $randomXau,
            'bookingon'  => $now,
            'bookingprice'  => $randomPpg,
            'bookingpricestreamid'  => 1,
            'confirmon'  => $now,
            'confirmprice'  => $randomPpg,
            'confirmby'  => 1,
            'confirmpricestreamid'  => 1,
            'confirmreference'  => '',
            'salespersonid' => 1,
            'pricestreamid' => 1,
            'productid'     => 1,
            'type'          => $isCompanyBuy ? Order::TYPE_COMPANYBUY : Order::TYPE_COMPANYSELL,
            'isspot'        => 1,
            'amount'        => $randomPpg * $randomXau,
            'status'        => $statusPending ? Order::STATUS_PENDING : Order::STATUS_CONFIRMED,
            'cancelon'      => '0000-00-00 00:00:00',
            'cancelby'      => 0,
            'cancelpricestreamid'   => 0,
            'notifyurl' => '',
            'reconciled' => 1,
            'reconciledon' => $now->format('Y-m-d H:i:s'),
            'reconciledby' => 1,
        ]);
        $order = self::$app->orderStore()->save($order);
        return $order;
    }

    /**
     * Create dummy gold transaction
     * 
     * @param Partner $partner 
     * @param MyAccountHolder $accHolder 
     * @param float $xau 
     * @param float $ppg 
     * @param bool $isCustomerBuy 
     * @param string $settlementMethod 
     * @param string $refno 
     * @param int $txStatus 
     * @param bool $orderPending 
     * @return MyGoldTransaction
     */
    protected static function createDummyGoldTransaction(Partner $partner, MyAccountHolder $accHolder, $xau = null, $ppg = null,
                                                         $isCustomerBuy = true, $settlementMethod = MyGoldTransaction::SETTLEMENT_METHOD_FPX, $refno = '', $txStatus = MyGoldTransaction::STATUS_PENDING_PAYMENT,
                                                         $orderPending = true)
    {
        $order = self::createDummyOrder($partner, $accHolder, $xau, $ppg, $refno, !$isCustomerBuy, $orderPending);

        $gt = self::$app->mygoldtransactionStore()->create([
            'originalamount'    => $order->amount,
            'settlementmethod'  => $settlementMethod,
            'salespersoncode'   => self::getFaker()->word,
            'refno'             => "GT".self::getFaker()->randomNumber(5, true),
            'orderid'           => $order->id,
            'status'            => $txStatus,
        ]);

        $gt = self::$app->mygoldtransactionStore()->save($gt);
        return $gt;
    }

}


?>