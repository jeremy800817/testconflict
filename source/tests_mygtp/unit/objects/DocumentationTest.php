<?php

use Snap\InputException;
use Snap\object\MyDocumentation;
use Snap\object\MyLocalizedContent;

// php ../vendor/bin/phpunit --bootstrap ./mygtpstartup.php --test-suffix Test.php .
final class DocumentationTest extends BaseTestCase {
    
    public function testCreateSimple()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $name = $faker->word();
        $content = $faker->paragraph();
        $documentation = $app->mydocumentationStore()->create([
            'name' => strtoupper($name),
            'filename'  => $faker->word(),
            'code' => $faker->numerify(strtoupper($name)."###"),
            'filecontent' => $content,
            'language'=> MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyDocumentation::STATUS_ACTIVE
        ]);

        // $documentation->saveContentIn(LocalizedContent::LANG_ENGLISH);
        $documentation = $app->mydocumentationStore()->save($documentation);

        $documentation->language = MyLocalizedContent::LANG_BAHASA;
        $documentation->filecontent = "abc";
        $documentation = $app->mydocumentationStore()->save($documentation);

        $this->assertGreaterThan(0, $documentation->id);
        $this->assertNotNull($documentation->getLocalizedContent());
        $this->assertGreaterThan(0, $documentation->getLocalizedContent()->id);
        $this->assertEquals($content, json_decode($documentation->getLocalizedContent()->data)->filecontent);

        return $documentation;
    }

    /**
     * Creates a documentation object without setting a language
     * 
     * @expectedException Snap\InputException
     */
    public function testCreateNoLanguage()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $name = $faker->word();
        $content = $faker->paragraph();
        $documentation = $app->mydocumentationStore()->create([
            'name' => strtoupper($name),
            'code' => $faker->numerify(strtoupper($name)."###"),
            'content' => $content,
            'status'  => MyDocumentation::STATUS_ACTIVE
        ]);

        $documentation = $app->mydocumentationStore()->save($documentation);
    }

    /**
     * Create an object without content
     */
    public function testCreateNoContent()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $name = $faker->word();
        // $content = $faker->paragraph();
        $documentation = $app->mydocumentationStore()->create([
            'name' => strtoupper($name),
            'code' => $faker->numerify(strtoupper($name)."###"),
            'language'=> MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyDocumentation::STATUS_ACTIVE
        ]);

        // $documentation->saveContentIn(LocalizedContent::LANG_ENGLISH);
        $documentation = $app->mydocumentationStore()->save($documentation);

        $this->assertNull($documentation->getLocalizedContent());
    }

    /**
     * @depends testCreateSimple
     */
    public function testGetCreatedSimple(MyDocumentation $originalDoc)
    {
        $app = self::$app;

        $doc = $app->mydocumentationStore()->getById($originalDoc->id);
        $doc->getContentIn($originalDoc->getLocalizedContent()->language);
        
        $this->assertEquals($originalDoc->content, $doc->content);
    }

    /**
     * @depends testCreateSimple
     */
    public function testUpdateStatus(MyDocumentation $origDoc)
    {
        $app = self::$app;

        $doc = $app->mydocumentationStore()->getById($origDoc->id);
        $doc->status = MyDocumentation::STATUS_INACTIVE;
        $doc = $app->mydocumentationStore()->save($doc);

        $doc = $app->mydocumentationStore()->getById($origDoc->id);
        $this->assertEquals(MyDocumentation::STATUS_INACTIVE, $doc->status);

    }


    public function testCreateMultiple()
    {
        $app = self::$app;
        $enFaker = $this->getFaker('en_US');
        $name = strtoupper($enFaker->word);
        $code = $enFaker->numerify($name."###");

        /** @var Documentation $doc */
        $doc = $app->mydocumentationStore()->create([
            'name' => $name,
            'code' => $code,
            'status' => MyDocumentation::STATUS_ACTIVE,
        ]);

        // Create english content.
        $enContent = $enFaker->paragraph;
        $doc->content = $enContent;
        $doc->language = MyLocalizedContent::LANG_ENGLISH;
        $doc = $app->mydocumentationStore()->save($doc);

        // Create chinese content
        $cnContent = "請你別失敗可以嗎";
        $doc->language = MyLocalizedContent::LANG_CHINESE;
        $doc->content = $cnContent;
        $doc = $app->mydocumentationStore()->save($doc);

        $this->assertEquals(count($doc->getAvailableLanguages()), 2);

        return ['doc' => $doc, 'en' => $enContent, 'cn' => $cnContent];
    }

    /**
     * @depends testCreateMultiple
     */
    public function testGetMultiple($content)
    {
        $app = self::$app;

        $doc = $app->mydocumentationStore()->getById($content['doc']->id);

        // Try get chinese
        // $doc->getContentIn(LocalizedContent::LANG_CHINESE);
        $doc->language = MyLocalizedContent::LANG_CHINESE;
        $this->assertEquals($content['cn'], $doc->content);

        // Then try english
        $doc->getContentIn(MyLocalizedContent::LANG_ENGLISH);
        $this->assertEquals($content['en'], $doc->content);

    }


}

?>