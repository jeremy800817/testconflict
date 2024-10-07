<?php

use Snap\util\ekyc\Innov8tifProvider;
use Snap\object\MyKycSubmission;
use Snap\object\MyKycResult;

final class Innov8tifProviderTest extends BaseTestCase
{
    /** @var Innov8tifProvider $provider */
    public static $provider;
    public static $ekycSubmission;
    public static $accountHolder;

    public static $mykadfrontb64;
    public static $mykadbackb64;
    public static $faceimageb64;

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();

        self::$provider = new Innov8tifProvider(self::$app);
        self::$mykadfrontb64   = base64_encode(file_get_contents(self::$app->getConfig()->{'tests.file.mykadfront'}));
        self::$mykadbackb64    = base64_encode(file_get_contents(self::$app->getConfig()->{'tests.file.mykadback'}));
        self::$faceimageb64    = base64_encode(file_get_contents(self::$app->getConfig()->{'tests.file.faceimage'}));
        self::$accountHolder = self::createDummyAccountHolder();
    }

    public function testCanGetNewJourneyId()
    {
        $provider = self::$provider;

        $journeyId = $provider->getNewJourneyId();

        $this->assertNotNull($journeyId);


        return $journeyId;
    }

    /** @depends testCanGetNewJourneyId */
    public function testCanCreateSubmission($journeyId)
    {
        if (!self::$mykadfrontb64 || !self::$mykadbackb64 || !self::$faceimageb64) {
            $this->markTestSkipped(
                'The required base64 image file was not provided.'
            );
        }

        $ekycSubmission = self::$provider->createSubmission(self::$app, self::$accountHolder, [
            'mykad_front_b64' => self::$mykadfrontb64,
            'mykad_back_b64' => self::$mykadbackb64,
            'face_image_b64' => self::$faceimageb64,
        ]);

        $ekycSubmission->journeyid = $journeyId;
        $ekycSubmission->submittedon = new \DateTime();
        $ekycSubmission = self::$app->mykycSubmissionStore()->save($ekycSubmission);

        self::$ekycSubmission = $ekycSubmission;

        $this->assertNotNull($ekycSubmission);
        $this->assertNotNull($ekycSubmission->id);
        $this->assertEquals($ekycSubmission->journeyid, $journeyId);
    }

    /** @depends testCanCreateSubmission */
    public function testCanSendOkayIdSubmission()
    {
        if (!self::$mykadfrontb64 || !self::$mykadbackb64 || !self::$faceimageb64) {
            $this->markTestSkipped(
                'The required base64 image file was not provided.'
            );
        }

        $provider = self::$provider;
        $ekycSubmission = self::$ekycSubmission;

        $response = $provider->submitOkayID($ekycSubmission);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);

        return $response;
    }

    /** @depends testCanSendOkayIdSubmission */
    public function testCanSendOkayDocSubmission($okayIdResponse)
    {
        if (!self::$mykadfrontb64 || !self::$mykadbackb64 || !self::$faceimageb64) {
            $this->markTestSkipped(
                'The required base64 image file was not provided.'
            );
        }

        $provider = self::$provider;
        $ekycSubmission = self::$ekycSubmission;

        $documentType = $okayIdResponse['documentType'];
        $response = $provider->submitOkayDoc($ekycSubmission, $documentType);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
    }

    /** @depends testCanCreateSubmission */
    public function testCanSendOkayFaceSubmission()
    {
        if (!self::$mykadfrontb64 || !self::$mykadbackb64 || !self::$faceimageb64) {
            $this->markTestSkipped(
                'The required base64 image file was not provided.'
            );
        }

        $provider = self::$provider;
        $ekycSubmission = self::$ekycSubmission;


        $response = $provider->submitOkayFace($ekycSubmission);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('result_idcard', $response);
        $this->assertArrayHasKey('confidence', $response['result_idcard']);

        $this->assertArrayHasKey('imageBestLiveness', $response);
        $this->assertArrayHasKey('probability', $response['imageBestLiveness']);
        $this->assertArrayHasKey('score', $response['imageBestLiveness']);
        $this->assertArrayHasKey('quality', $response['imageBestLiveness']);
    }

    /** @depends testCanCreateSubmission */
    public function testCanGetScorecardResults()
    {
        $provider = self::$provider;
        $ekycSubmission = self::$ekycSubmission;

        $response = $provider->getScorecardResult($ekycSubmission);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('scorecardResultList', $response);
        $this->assertNotEmpty($response['scorecardResultList']);
        $this->assertArrayHasKey('scorecardStatus', $response['scorecardResultList'][0]);
    }

    public function testCanSearchPepRecords()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');

        $provider = self::$provider;
        $accountHolder = self::$accountHolder;

        // Override to view
        $searchResult = $provider->searchForPepRecords(self::$app, $accountHolder, [
            'Forename'         => 'Najib',
            'Middlename'       => null,
            'Surname'          => 'Razak',
            'DateOfBirth'      => '1953-07-23',
            'YearOfBirth'      => '1953',
        ]);

        $cachedResult = $provider->searchForPepRecords(self::$app, $accountHolder, [
            'Forename'         => 'Najib',
            'Middlename'       => null,
            'Surname'          => 'Razak',
            'DateOfBirth'      => '1953-07-23',
            'YearOfBirth'      => '1953',
        ]);

        $response = json_decode($searchResult->response, true);

        $this->assertNotNull($response);
        $this->assertArrayHasKey('recordsFound', $response);
        $this->assertArrayHasKey('matches', $response);
        $this->assertNotEmpty($response['matches']);
        $this->assertArrayHasKey('person', $response['matches'][0]);
        $this->assertEquals($searchResult->id, $cachedResult->id);
    }

    public function testCanGetPepPdf()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');

        $provider = self::$provider;
        $response = $provider->getPepPdf(873938);

        $fp = tmpfile();
        fwrite($fp, $response->file);
        fseek($fp, 0);

        $mimeType = mime_content_type($fp);

        $this->assertNotNull($response->file);
        $this->assertEquals('application/pdf', $mimeType);

        return $response;
    }

    /** @depends testCanGetPepPdf */
    public function testCanGetSavedPepPdf($previousResponse)
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');

        $provider = self::$provider;
        $response = $provider->getPepPdf(873938);
        $this->assertEquals($previousResponse->id, $response->id);
        $this->assertEquals($previousResponse->file, $response->file);
        $this->assertEquals($previousResponse->modifiedon, $response->modifiedon);
    }

    public function testCanGetPepJson()
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');

        $provider = self::$provider;
        $response = $provider->getPepJson(873938);
        $file = json_decode($response->file, true);
        $this->assertNotNull($file);
        $this->assertArrayHasKey('id', $file);
        $this->assertEquals(873938, $file['id']);

        return $response;
    }

    /** @depends testCanGetPepJson */
    public function testCanGetSavedPepJson($previousResponse)
    {
        $this->markTestSkipped('Skipped because we do not want to waste API call');

        $provider = self::$provider;
        $response = $provider->getPepJson(873938);
        $file = json_decode($response->file, true);
        $previousFile = json_decode($previousResponse->file, true);

        $this->assertEquals($previousResponse->id, $response->id);
        $this->assertEquals($previousResponse->modifiedon, $response->modifiedon);
        $this->assertEquals($previousFile, $file);
    }

    
}
