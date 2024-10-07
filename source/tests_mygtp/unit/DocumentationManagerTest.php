<?php

use Snap\object\MyDocumentation;
use Snap\object\MyLocalizedContent;

final class DocumentationManagerTest extends BaseTestCase
{
    protected static $accountHolder;
    protected static $partner;

    public static function setUpBeforeClass()
    {
        parent::setupBeforeClass();
        self::$app->partnerStore();

        self::$partner = self::createDummyPartner(); 
    }

    public function testCanGetDocumentContent()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $aqadDocument = $app->mydocumentationStore()->create([
            'name' => strtoupper($faker->word()),
            'code' => MyDocumentation::CODE_AQAD,
            'content' => $faker->paragraph(),
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyDocumentation::STATUS_ACTIVE
        ]);

        /** @var MyDocumentation $aqadDocument */
        $aqadDocument = $app->mydocumentationStore()->save($aqadDocument);

        $tncDocument = $app->mydocumentationStore()->create([
            'name' => strtoupper($faker->word()),
            'code' => MyDocumentation::CODE_TNC,
            'content' => $faker->paragraph(),
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyDocumentation::STATUS_ACTIVE
        ]);

        /** @var MyDocumentation $tncDocument = */
        $tncDocument = $app->mydocumentationStore()->save($tncDocument);

        /** @var \Snap\manager\MyGtpDocumentationManager */
        $documentationManager = self::$app->mygtpdocumentationManager();
        $storedAqadDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_AQAD, MyLocalizedContent::LANG_ENGLISH, self::$partner);
        $storedTncDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_TNC, MyLocalizedContent::LANG_ENGLISH, self::$partner);

        $this->assertEquals($aqadDocument->content, $storedAqadDocument->content);
        $this->assertEquals($tncDocument->content, $storedTncDocument->content);
        $this->assertNotEquals($storedAqadDocument->content, $storedTncDocument->content);

        return [$aqadDocument, $tncDocument];
    }

    /** @depends testCanGetDocumentContent */
    public function testCanGetForDifferentLanguage($documents)
    {
        /** @var MyDocument */
        list($aqadDocument, $tncDocument) = $documents;

        $app = self::$app;
        $faker = $this->getFaker('ms_MY');

        $aqadDocument->language = MyLocalizedContent::LANG_BAHASA;
        $aqadDocument->content  = $faker->paragraph();
        $aqadDocument = $app->mydocumentationStore()->save($aqadDocument);

        $tncDocument->language = MyLocalizedContent::LANG_BAHASA;
        $tncDocument->content  = $faker->paragraph();
        $tncDocument = $app->mydocumentationStore()->save($tncDocument);

        /** @var \Snap\manager\MyGtpDocumentationManager */
        $documentationManager = self::$app->mygtpdocumentationManager();
        $storedMyAqadDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_AQAD, MyLocalizedContent::LANG_BAHASA, self::$partner);
        $storedEnAqadDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_AQAD, MyLocalizedContent::LANG_ENGLISH, self::$partner);

        $storedMyTncDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_TNC, MyLocalizedContent::LANG_BAHASA, self::$partner);
        $storedEnTncDocument = $documentationManager->getDocumentForLanguage(MyDocumentation::CODE_TNC, MyLocalizedContent::LANG_ENGLISH, self::$partner);

        $this->assertNotNull($storedEnAqadDocument->content);
        $this->assertNotNull($storedMyAqadDocument->content);
        $this->assertNotNull($storedEnTncDocument->content);
        $this->assertNotNull($storedMyTncDocument->content);
        $this->assertNotEquals($storedMyAqadDocument->content, $storedEnAqadDocument->content);
        $this->assertNotEquals($storedMyTncDocument->content, $storedEnTncDocument->content);
    }
}
