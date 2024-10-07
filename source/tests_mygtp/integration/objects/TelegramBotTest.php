<?php

use Snap\App;
use Snap\util\telegram\TelegramBot;

class TelegramBotTest extends BaseTestCase
{
    static $skipAllTests = false;
    static $app = null;
    static $testChatId = null;

    public static function setupBeforeClass()
    {
        self::$app = App::getInstance();
        $testChatId = self::$app->getConfig()->{'telegram.testchatid'};
        if (! strlen($testChatId)) {
            echo "All integration tests in " . __CLASS__ . " are skipped due to missing test chat ID. \n";
            self::$skipAllTests = true;
        }
        self::$testChatId = $testChatId;
    }

    public function setUp()
    {
        if (self::$skipAllTests) {
            $this->markTestSkipped("All tests are skipped");
        }
    }

    public function testSendMessage()
    {
        $tgBot = TelegramBot::getInstance();
        $testMessage = "test";
        $return = $tgBot->sendMessage($testMessage, self::$testChatId);

        $this->assertTrue($return->isOk());
        $this->assertEquals($testMessage, $return->getResult()->getText());
    }




}

?>