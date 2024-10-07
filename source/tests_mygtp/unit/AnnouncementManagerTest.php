<?php

use Snap\object\MyAccountHolder;
use Snap\object\MyAnnouncement;
use Snap\object\MyAnnouncementTheme;
use Snap\object\MyLocalizedContent;

class AnnouncementManagerTest extends BaseTestCase
{

    public static function setupBeforeClass()
    {
        parent::setupBeforeClass();
    }

    public function testCanGetAnnouncements()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $announcementTheme = $app->myannouncementthemeStore()->create([
            'name'           => $faker->word,
            'template'       => $this->getTemplate(1),
            'rank'           => 1,
            'displaystarton' => new \DateTime('first day of this month'),
            'displayendon'   => new \DateTime('last day of next month'),
            'validfrom'      => new \DateTime('first day of this year'),
            'validto'        => new \DateTime('last day of this year'),
            'status'         => MyAnnouncementTheme::STATUS_ACTIVE
        ]);
        $announcementTheme = $app->myannouncementthemeStore()->save($announcementTheme);

        $title = $faker->sentence();
        $content = $faker->paragraph();
        $title2 = $faker->sentence();
        $content2 = $faker->paragraph();
        $title3 = $faker->sentence();
        $content3 = $faker->paragraph();

        $announcement = $app->myAnnouncementStore()->create([
            'code' => $faker->numerify(strtoupper($title) . "###"),
            'title' => strtoupper($title),
            'content' => $content,
            'displaystarton' => new \DateTime('today', $app->getUserTimezone()),
            'displayendon' => new \DateTime('tomorrow', $app->getUserTimezone()),
            'type' => MyAnnouncement::TYPE_ANNOUNCEMENT,
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyAnnouncement::STATUS_APPROVED
        ]);

        $announcement2 = $app->myAnnouncementStore()->create([
            'code' => $faker->numerify(strtoupper($title2) . "###"),
            'title' => strtoupper($title2),
            'content' => $content2,
            'displaystarton' => new \DateTime('yesterday', $app->getUserTimezone()),
            'displayendon' => new \DateTime('tomorrow', $app->getUserTimezone()),
            'type' => MyAnnouncement::TYPE_ANNOUNCEMENT,
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyAnnouncement::STATUS_APPROVED
        ]);

        $announcement3 = $app->myAnnouncementStore()->create([
            'code' => $faker->numerify(strtoupper($title3) . "###"),
            'title' => strtoupper($title3),
            'content' => $content3,
            'displaystarton' => new \DateTime('yesterday', $app->getUserTimezone()),
            'displayendon' => new \DateTime('tomorrow', $app->getUserTimezone()),
            'type' => MyAnnouncement::TYPE_ANNOUNCEMENT,
            'language' => MyLocalizedContent::LANG_ENGLISH,
            'status'  => MyAnnouncement::STATUS_ACTIVE
        ]);

        $announcement = $app->myannouncementStore()->save($announcement);
        $announcement2 = $app->myannouncementStore()->save($announcement2);

        /** @var \Snap\manager\MyGtpAnnouncementManager */
        $announcementManager = $app->mygtpannouncementManager();

        $html = $announcementManager->getAnnouncements(MyAccountHolder::LANG_EN);

        $this->assertNotEmpty($html);
        $this->assertContains('Template 1', $html);
        $this->assertContains($announcement->title, $html);
        $this->assertContains($announcement->content, $html);
        $this->assertContains($announcement2->title, $html);
        $this->assertContains($announcement2->content, $html);
        $this->assertNotContains($announcement3->title, $html);
        $this->assertNotContains($announcement3->content, $html);
        $this->assertGreaterThan(strpos($html, $announcement->title), strpos($html, $announcement2->title));
        $this->assertFalse(strpos($html, '##BLOCKSTART##'));
        $this->assertFalse(strpos($html, '##BLOCKEND##'));
        $this->assertFalse(strpos($html, '##EMPTYSTART##'));
        $this->assertFalse(strpos($html, '##EMPTYEND##'));
        $this->assertFalse(strpos($html, '##EMPTYPLACEHOLDER##'));

        return [$announcement, $announcement2];
    }

    /** @depends testCanGetAnnouncements */
    public function testCanSelectCorrectTheme($announcements)
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $announcementTheme = $app->myannouncementthemeStore()->create([
            'name'           => $faker->word,
            'template'       => $this->getTemplate(2),
            'rank'           => 2,
            'displaystarton' => new \DateTime('first day of last month'),
            'displayendon'   => new \DateTime('last day of next month'),
            'validfrom'      => new \DateTime('first day of this year'),
            'validto'        => new \DateTime('last day of this year'),
            'status'         => MyAnnouncementTheme::STATUS_ACTIVE
        ]);
        $announcementTheme = $app->myannouncementthemeStore()->save($announcementTheme);

        /** @var \Snap\manager\MyGtpAnnouncementManager */
        $announcementManager = $app->mygtpannouncementManager();

        $html = $announcementManager->getAnnouncements(MyAccountHolder::LANG_EN);

        $this->assertNotEmpty($html);
        $this->assertContains('Template 2', $html);
        $this->assertContains($announcements[0]->title, $html);
        $this->assertContains($announcements[0]->content, $html);
        $this->assertContains($announcements[1]->title, $html);
        $this->assertContains($announcements[1]->content, $html);
        $this->assertGreaterThan(strpos($html, $announcements[0]->title), strpos($html, $announcements[1]->title));
        $this->assertFalse(strpos($html, '##BLOCKSTART##'));
        $this->assertFalse(strpos($html, '##BLOCKEND##'));
        $this->assertFalse(strpos($html, '##EMPTYSTART##'));
        $this->assertFalse(strpos($html, '##EMPTYEND##'));
        $this->assertFalse(strpos($html, '##EMPTYPLACEHOLDER##'));

    }

    public function testCanHandleEmptyAnnouncements()
    {
        $app = self::$app;
        $faker = $this->getFaker('en_US');

        $announcements = $app->myannouncementStore()->searchTable()->select()->get();
        foreach ($announcements as $announcement) {
            $app->myannouncementStore()->delete($announcement);
        }

        /** @var \Snap\manager\MyGtpAnnouncementManager */
        $announcementManager = $app->mygtpannouncementManager();
        $html = $announcementManager->getAnnouncements(MyAccountHolder::LANG_EN);

        $this->assertFalse(strpos($html, '##BLOCKSTART##'));
        $this->assertFalse(strpos($html, '##BLOCKEND##'));
        $this->assertFalse(strpos($html, '##EMPTYSTART##'));
        $this->assertFalse(strpos($html, '##EMPTYEND##'));
        $this->assertFalse(strpos($html, '##EMPTYPLACEHOLDER##'));
    }

    private function getTemplate($num)
    {
        $template1 = <<<TEMPLATE1
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template 1</title>
</head>

<body>
    ##BLOCKSTART##
    <div>
        <h1>##ANNOUNCEMENTTITLE##</h1>
        <p>##ANNOUNCEMENTCONTENT##</p>
    </div>
    ##BLOCKEND##
    ##EMPTYSTART##
    <div>##EMPTYPLACEHOLDER##</div>
    ##EMPTYEND##
</body>

</html>
TEMPLATE1;

        $template2 = <<<TEMPLATE2
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template 2</title>
</head>

<body>
    ##BLOCKSTART##
    <div>
        <strong>##ANNOUNCEMENTTITLE##</strong>
        <div>##ANNOUNCEMENTCONTENT##</div>
    </div>
    <hr>
    ##BLOCKEND##
    ##EMPTYSTART##
    <h1>##EMPTYPLACEHOLDER##</h1>
    ##EMPTYEND##
</body>

</html>
TEMPLATE2;

        switch ($num) {
            case 1:
                return $template1;
                break;
            case 2:
                return $template2;
                break;
            default:
                return $template1;
                break;
        };
    }
}
