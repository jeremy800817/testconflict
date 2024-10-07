<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

use Snap\api\param\converter\MyGtpApiParamConverter;
use Snap\object\MyOccupationCategory;
use Snap\object\MyOccupationSubCategory;
use Snap\object\MyBank;
use Snap\App;
use Snap\object\MyLocalizedContent;
use Snap\object\MyPriceAlert;

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 19-Oct-2020
 */
class MyGtpApiParamConverterTest extends AuthenticatedTestCase
{

    private $converter;
    private $params;

    public function setUp()
    {
        parent::setUp();

        $this->params = [
            'occupation_category_id' => 1,
        ];

        $this->decoded = [];
        $this->converter = new MyGtpApiParamConverter(App::getInstance());
    }


    public function testBearerToAccountHolder()
    {

        $this->converter->convert('tokenToAccountHolder', '', '', [], $this->decoded);
        $this->assertArrayHasKey('accountholder', $this->decoded);

        // Refresh
        $accountHolder = self::$app->myaccountholderStore()->getById(self::$accountHolder->id);
        $this->assertEquals($this->decoded['accountholder'], $accountHolder);
    }

    public function testCanConvertToOccupationCategory()
    {
        $app = self::$app;

        $occupationCategory = $app->myoccupationCategoryStore()->create([
            'category'    => 'IT',
            'description' => 'Software Engineer, Software Developer, Software Analyst, Mobile Developer',
            'language'    => MyLocalizedContent::LANG_ENGLISH,
            'status'      => MyOccupationCategory::STATUS_ACTIVE,
        ]);

        $occupationCategory = $app->myoccupationCategoryStore()->save($occupationCategory);

        $convertedData = [];

        $this->converter->convert('toOccupationCategory', 'occupation_category_id', $occupationCategory->id, [], $convertedData);

        $this->assertNotEmpty($convertedData);
        $this->assertArrayHasKey('occupation_category', $convertedData);
        $this->assertInstanceOf(MyOccupationCategory::class, $convertedData['occupation_category']);
        $this->assertEquals($occupationCategory->id, $convertedData['occupation_category']->id);
    }

    /**
     * @expectedException Snap\api\exception\MyOccupationCategoryNotFound
     */
    public function testCannotConvertToOccupationCategory()
    {
        $convertedData = [];
        $this->converter->convert('toOccupationCategory', 'occupation_category_id', 0, $this->params, $convertedData);
    }

    public function testConvertToDateTime()
    {
        $this->converter->convert('toDateTime', 'date', '2020-01-01 11:00:11', [], $this->decoded);
        $this->assertInstanceOf(\DateTime::class, $this->decoded['date']);
    }

    public function testCanConvertToBank()
    {
        $app = self::$app;

        $bank = $app->mybankStore()->create([
            'code'     => 'WBK',
            'name'     => 'World Bank',
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'   => Mybank::STATUS_ACTIVE,
        ]);

        $bank = $app->mybankStore()->save($bank);

        $convertedData = [];

        $this->converter->convert('tobank', 'bank_id', $bank->id, [], $convertedData);

        $this->assertNotEmpty($convertedData);
        $this->assertArrayHasKey('bank', $convertedData);
        $this->assertInstanceOf(Mybank::class, $convertedData['bank']);
        $this->assertEquals($bank->id, $convertedData['bank']->id);
    }

    /**
     * @expectedException Snap\api\exception\MyBankNotFound
     */
    public function testCannotConvertToBank()
    {
        $convertedData = [];
        $this->converter->convert('toBank', 'bank_id', 0, $this->params, $convertedData);
    }

    public function testConvertToPriceAlert()
    {
        $app = self::$app;

        $pricealert = $app->mypricealertStore()->create([
            'accountholderid' => 1,
            'priceproviderid' => 1,
            'amount' => 1000,
            'type' => MyPriceAlert::TYPE_BUY,
            'remarks' => 'Remarks',
            'status' => MyPriceAlert::STATUS_ACTIVE
        ]);

        $pricealert = $app->mypricealertStore()->save($pricealert);

        $convertedData = [];

        $this->converter->convert('toPriceAlert', 'pricealert_id', $pricealert->id, [], $convertedData);

        $this->assertNotEmpty($convertedData);
        $this->assertArrayHasKey('price_alert', $convertedData);
        $this->assertInstanceOf(MyPriceAlert::class, $convertedData['price_alert']);
        $this->assertEquals($pricealert->id, $convertedData['price_alert']->id);
    }

    /**
     * @expectedException Snap\api\exception\MyPriceAlertNotFound
     */
    public function testCannotConvertToPriceAlert()
    {
        $convertedData = [];
        $this->converter->convert('toPriceAlert', 'price_alert_id', 0, $this->params, $convertedData);
    }

    public function testCanConvertToOccupationSubCategory()
    {
        $app = self::$app;

        $occupationSubCategory = $app->myoccupationSubCategoryStore()->create([
            'occupationcategoryid' => 1,
            'code' => 'IT',
            'politicallyexposed' => 1,
            'name' => 'Software Engineer, Software Developer, Software Analyst, Mobile Developer',
            'language'    => MyLocalizedContent::LANG_ENGLISH,
            'status'      => MyOccupationSubCategory::STATUS_ACTIVE,
        ]);

        $occupationCategory = $app->myoccupationSubCategoryStore()->save($occupationSubCategory);

        $convertedData = [];

        $this->converter->convert('toOccupationSubCategory', 'occupation_subcategory_id', $occupationSubCategory->id, [], $convertedData);

        $this->assertNotEmpty($convertedData);
        $this->assertArrayHasKey('occupation_subcategory', $convertedData);
        $this->assertInstanceOf(MyOccupationSubCategory::class, $convertedData['occupation_subcategory']);
        $this->assertEquals($occupationSubCategory->id, $convertedData['occupation_subcategory']->id);
    }

    /**
     * @expectedException Snap\api\exception\MyOccupationSubCategoryNotFound
     */
    public function testCannotConvertToOccupationSubCategory()
    {
        $convertedData = [];
        $this->converter->convert('toOccupationSubCategory', 'occupation_subcategory_id', 0, $this->params, $convertedData);
    }
}

?>