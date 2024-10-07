<?php

/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 *
 *
 */

use Snap\api\exception\ApiException;
use Snap\api\param\validator\MyGtpApiParamValidator;
use Snap\object\MyOccupationCategory;
use Snap\object\MyLocalizedContent;

/**
 * @author  Azam <azam@silverstream.my>
 * @version 1.0
 */
final class MyGtpApiParamValidatorTest extends AuthenticatedTestCase
{
    private $params;
    private $validator;

    public function setUp()
    {
        parent::setUp();
        $this->params = [
            'email'            => 'valid@example.com',
            'phone'            => '+60123456789',
            'phone2'           => '+601112345678',
            'invalid_email'    => 'notvalid@example',
            'invalid_phone'    => '1123456789',
            'invalid_phone2'   => '+6012-3456789',
            'invalid_phone3'          => '+01112345678',
            'password'         => '!secretPassword',
            'confirm_password' => '!secretPassword',
            'typo'             => '!typoPassword',
            'confirm_typo'     => '!typoPasswordOpss',
            'category_id'      => 1,
            'invalid_code'     => 'Invalid!',
            'empty'            => '',
            'zero'             => 0,
            'one'              => 1,
            'datetime_start'   => '2020-01-01 00:00:00',
            'datetime_end'     => '2021-01-01 00:00:00',
            'bool_true'        => true,
            'bool_false'       => false,
            'string_true'      => 'true',
            'string_false'     => 'false',
            'null'             => null,
        ];

        $this->validator = new MyGtpApiParamValidator(\Snap\App::getInstance());
    }

    public function testPhone()
    {
        $requiredTestScenario = [
            array('phone', 'required;contains|+60123456789;phone|mobile-my', true),
            array('phone', 'required;contains|+60123456789;phone', true),
            array('phone', 'required;contains|+60123456789;phone', true),
            array('phone', 'phone|mobile-my', true),
            array('phone', 'phone', true),

            array('phone2', 'phone|mobile-my', true),
            array('phone2', 'phone', true),

            array('invalid_phone', 'required;contains|1123456789;phone|mobile-my', false),
            array('invalid_phone', 'required;contains|1123456789;phone', false),
            array('invalid_phone', 'phone|mobile-my', false),
            array('invalid_phone', 'phone', false),
            array('invalid_phone2', 'phone|mobile-my', false),
            array('invalid_phone2', 'phone', false),

            array('invalid_phone3', 'phone|mobile-my', false),
            array('invalid_phone3', 'phone', false),

            array('phone', 'phone|nonexistent', false),
            array('invalid_phone2', 'phone|nonexistent', false),
            array('invalid_phone', 'phone|nonexistent', false),
        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testEmail()
    {
        $requiredTestScenario = [
            array('email', 'required;contains|valid@example.com;email', true),
            array('email', 'email', true),
            array('invalid_email', 'required;contains|invalid@example;email', false),
            array('invalid_email', 'email', false),

        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testConfirm()
    {
        $requiredTestScenario = [
            array('password', 'required;string|min=8;confirm', true),
            array('password', 'string|max=20;confirm', true),
            array('password', 'confirm', true),
            array('typo', 'required;string|min=8;confirm', false),
            array('typo', 'string|max=20;confirm', false),
            array('typo', 'confirm', false),
        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testExists()
    {
        $app = self::$app;

        $occupationCategory = $app->myoccupationCategoryStore()->create([
            'category'    => 'IT',
            'description' => 'Software Engineer, Software Developer, Software Analyst, Mobile Developer',
            'status'      => MyOccupationCategory::STATUS_ACTIVE,
            'language'    => MyLocalizedContent::LANG_ENGLISH,
        ]);
        $app->myoccupationCategoryStore()->save($occupationCategory);

        $requiredTestScenario = [
            array('category_id', 'required;exists|myoccupationCategory|id', true),
            array('category_id', 'string|exists|myoccupationCategory|id', true),
            array('category_id', 'exists|myoccupationCategory|id', true),
            array('category_id', 'exists|myoccupationCategory|invalidattribute', false),
            array('invalid_code', 'exists|myoccupationCategory|invalidattribute', false),
            array('invalid_code', 'exists|myoccupationCategory|id', false),
            array('empty', 'exists|myoccupationCategory|invalidattribute', false),
            array('empty', 'exists|myoccupationCategory|id', false),
            array('zero', 'exists|myoccupationCategory|invalidattribute', false),
            array('zero', 'exists|myoccupationCategory|id', false),
        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testBearer()
    {
        $result = $this->validator->pass('bearer', 'token', '', []);
        $this->assertTrue($result);
    }

    public function testRequiredWithAny()
    {
        $requiredTestScenario = [
            array('email', 'requiredWithAny|phone', true),
            array('phone', 'requiredWithAny|email', true),
            array('empty', 'requiredWithAny|email', false), // Throw an exception
            array('empty', 'requiredWithAny|empty', false), // Return false
        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testDatetimeCompare()
    {
        $requiredTestScenario = [
            array('datetime_start', 'required;datetime;datetimeCompare|==|datetime_start', true),
            array('datetime_start', 'required;datetime;datetimeCompare|<=|datetime_end', true),
            array('datetime_start', 'required;datetime;datetimeCompare|<|datetime_end', true),
            array('datetime_end', 'required;datetime;datetimeCompare|>=|datetime_start', true),
            array('datetime_end', 'required;datetime;datetimeCompare|>|datetime_start', true),
            array('datetime_start', 'required;datetime;datetimeCompare|!=|datetime_end', true),
            array('datetime_start', 'required;datetime;datetimeCompare|<>|datetime_end', true),

            array('datetime_end', 'required;datetime;datetimeCompare|<|datetime_start', false),
            array('datetime_end', 'required;datetime;datetimeCompare|<=|datetime_start', false),
            array('datetime_end', 'required;datetime;datetimeCompare|==|datetime_start', false),
            array('datetime_start', 'required;datetime;datetimeCompare|>=|datetime_end', false),
            array('datetime_start', 'required;datetime;datetimeCompare|>|datetime_end', false),
            array('datetime_start', 'required;datetime;datetimeCompare|!=|datetime_start', false),
            array('datetime_start', 'required;datetime;datetimeCompare|<>|datetime_start', false),
        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testRequiredWhen()
    {
        $requiredTestScenario = [
            array('email', 'requiredWhen|bool_true|true', true),
            array('email', 'requiredWhen|bool_false|false', true),
            array('email', 'requiredWhen|string_true|true', true),
            array('email', 'requiredWhen|string_false|false', true),
            array('email', 'requiredWhen|string_false|false', true),
            array('email', 'requiredWhen|one|1', true),
            array('email', 'requiredWhen|zero|0', true),
            
            array('empty', 'requiredWhen|string_true|false', true), // Pass since other param value not match with expected
            array('empty', 'requiredWhen|string_false|true', true), // Pass since other param value not match with expected
            array('empty', 'requiredWhen|bool_true|false', true), // Pass since other param value not match with expected
            array('empty', 'requiredWhen|bool_false|true', true), // Pass since other param value not match with expected
            array('empty', 'requiredWhen|one|true', true), // Pass since other param value not match with expected
            array('empty', 'requiredWhen|zero|false', true), // Pass since other param value not match with expected
            

            array('empty', 'requiredWhen|one|true', false),
            array('empty', 'requiredWhen|zero|false', false),
            array('empty', 'requiredWhen|category_id|1', false),
            array('empty', 'requiredWhen|bool_true|true', false),
            array('empty', 'requiredWhen|bool_false|false', false),
            array('empty', 'requiredWhen|string_true|true', false),
            array('empty', 'requiredWhen|string_false|false', false),
            array('empty', 'requiredWhen|datetime_start|2020-01-01 00:00:00', false),
            array('empty', 'requiredWhen|one|1',false),
            array('empty', 'requiredWhen|zero|0', false),

            // Test null
            array('email', 'requiredWhen|empty|null', true),
            array('email', 'requiredWhen|null|null', true),
            array('empty', 'requiredWhen|null|null', false),

        ];

        foreach ($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($this->validator->pass($aTest[1], $aTest[0], isset($this->params[$aTest[0]]) ? $this->params[$aTest[0]] : null, $this->params));
            } catch (\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testFullname()
    {
        $validator = "fullname";
        $key = "full_name";
        $testCases = [
            "Test Name" => true,
            $this->getFaker()->name => true,
            "M. Night Shyamalan"    => true,
            "Jean-Pierre Polnareff" => true,
            "Jame'e"                => true,
            "James A/L Ragunathan"  => true,
            "Jame's @ John"         => true,
            "John & Son"            => false,
            "160cm [3.1M️]"          => false,
            "160cm 3.1M❤"           => false,
            "😀😡 some name 🙌"    => false
        ];

        foreach ($testCases as $value => $result) {
            try {
                $this->assertTrue($this->validator->pass($validator, $key, $value, $testCases), "Failed test for $value");
            } catch (ApiException $e) {
                $this->assertFalse($result, "Failed test for $value");
            }
        }
    }
}
