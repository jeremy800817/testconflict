<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 *
 * 
 */

use PHPUnit\Framework\TestCase;
use Snap\api\param\validator\ApiParamValidator;
use Snap\api\param\validator\GtpApiParamValidator;

/**
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
final class ApiParamValidatorTest extends TestCase
{
    public function setupTest() {
        $validator = new GtpApiParamValidator(\Snap\App::getInstance());
        $testParams1 = [
            'testRequired' => 23,
            'shortString' => 'Some data',
            'longString' => 'sdafkldflkjklsdjkljflkjfljlktrrwe32',
            'float' => 423.34234,
            'integer' => 23434,
            'datetime' => '2020-03-21 12:22:25',
            'digest' => sha1('testRequired=23&shortString=some data')
        ];
        return [$validator, $testParams1];
    }

    public function testRequired() {
        list($validator, $params) = $this->setupTest();
        $requiredTestScenario = [
            array( 'testRequired',  'required;numeric', true),
            array( 'notRequired',   'required',         false),
            array( 'shortString',   'required',         true),
            array( 'longString',    '',  true),
            array( 'float',         'string;required',  true),
            array( 'integer',       'string;required',  true),
            array( 'datetime',      'required;string',  true),
            array( 'digest',        'string;required',  true)
        ];
        foreach($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($validator->pass($aTest[1], $aTest[0], isset($params[$aTest[0]])?$params[$aTest[0]] : null, $params));
            } catch(\Exception $e) {
                $this->assertFalse($aTest[2], 'Test failed for condition ' . json_encode($aTest));
            }
        }
    }

    public function testString() {
        list($validator, $params) = $this->setupTest();
        $requiredTestScenario = [
            array( 'testRequired',  'required;string',       false),
            array( 'notRequired',   '',                      true),
            array( 'notRequired',   'string',                true),
            array( 'shortString',   'string|max=40required', true),
            array( 'shortString',   'string|max=4required',  false),
            array( 'shortString',   'string|min=17;required', false),
            array( 'longString',    'required;string|min=4|max=80',  true),
            array( 'longString',    'required;string|min=40|max=80',  false),
            array( 'longString',    'required;string|min=4|max=8',  false),
            array( 'float',         'string;required',  true),
            array( 'integer',       'string;required',  true),
            array( 'datetime',      'required;string',  true),
            array( 'digest',        'string;required',  true)
        ];
        foreach($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($validator->pass($aTest[1], $aTest[0], isset($params[$aTest[0]])?$params[$aTest[0]] : null, $params),
                                "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])? $params[$aTest[0]] :'').'condition ' . json_encode($aTest));
            } catch(\Exception $e) {
                $this->assertFalse($aTest[2], "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])?$params[$aTest[0]]:'').'condition ' . json_encode($aTest));
            }
        }
    }

    public function testContains() {
        list($validator, $params) = $this->setupTest();
        $requiredTestScenario = [
            array( 'testRequired',  'required;contains|23|24|25',       true),
            array( 'testRequired',  'required;contains|24|25|23',       true),
            array( 'testRequired',  'required;contains|2|3|5',         false),
            array( 'shortString',   'string|max=40required;contains|some data|other data', true),
            array( 'shortString',   'contains|other data|some data;string',  true),
            array( 'shortString',   'contains|some_data|other_data;string',  false)
        ];
        foreach($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($validator->pass($aTest[1], $aTest[0], isset($params[$aTest[0]])?$params[$aTest[0]] : null, $params),
                                "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])? $params[$aTest[0]] :'').'condition ' . json_encode($aTest));
            } catch(\Exception $e) {
                $this->assertFalse($aTest[2], "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])?$params[$aTest[0]]:'').'condition ' . json_encode($aTest));
            }
        }
    }

    public function testDatetime() {
        list($validator, $params) = $this->setupTest();
        $requiredTestScenario = [
            array( 'testRequired',  'datetime;contains|23|24|25',       false),
            array( 'testRequired',  'datetime;contains|24|25|23',       false),
            array( 'testRequired',  'datetime;contains|2|3|5',         false),
            array( 'shortString',   'datetime|max=40required;contains|some data|other data', false),
            array( 'shortString',   'contains|other data|some data;datetime',  false),
            array( 'shortString',   'contains|some_data|other_data;datetime',  false),
            array( 'datetime',   'datetime',  false)
        ];
        foreach($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($validator->pass($aTest[1], $aTest[0], isset($params[$aTest[0]])?$params[$aTest[0]] : null, $params),
                                "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])? $params[$aTest[0]] :'').'condition ' . json_encode($aTest));
            } catch(\Exception $e) {
                $this->assertFalse($aTest[2], "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])?$params[$aTest[0]]:'').'condition ' . json_encode($aTest));
            }
        }
    }

    public function testNumeric() {
        list($validator, $params) = $this->setupTest();
        $requiredTestScenario = [
            array( 'testRequired',  'required;numeric', true),
            array( 'notRequired',   'numeric',         false),
            array( 'shortString',   'numeric',         false)
        ];
        foreach($requiredTestScenario as $aTest) {
            try {
                $this->assertTrue($validator->pass($aTest[1], $aTest[0], isset($params[$aTest[0]])?$params[$aTest[0]] : null, $params),
                                "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])? $params[$aTest[0]] :'').'condition ' . json_encode($aTest));
            } catch(\Exception $e) {
                $this->assertFalse($aTest[2], "Test failed for {$aTest[0]} (".(isset($params[$aTest[0]])?$params[$aTest[0]]:'').'condition ' . json_encode($aTest));
            }
        }
    }
}
?>