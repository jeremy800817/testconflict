<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2021
 * @copyright Silverstream Technology Sdn Bhd. 2021
 */

namespace Snap\util\telegram;

use Exception;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Snap\App;

/**
 * Wrapper class around php-telegram-bot.
 * API documentation here: https://github.com/php-telegram-bot/core
 * 
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @package Snap\util\telegram
 */
class TelegramBot {
    private static $instance = null;
    private $app = null;
    private $telegram = null;

    private function __construct(App $app)
    {
        $this->app = $app;
        if (!$this->telegram) {
            $botApiKey = $app->getConfig()->{'telegram.apikey'};
            $botUsername = $app->getConfig()->{'telegram.username'};
            $usingWebhook = $app->getConfig()->{'telegram.usewebhook'};

            if (! strlen($botApiKey)) {
                throw new \Exception("Telegram API Key not set.");
            }
            if (! strlen($botUsername)) {
                throw new \Exception("Telegram Username not set.");
            }
            
            $this->telegram = new Telegram($botApiKey, $botUsername);
        }

    }

    /**
     * Get Telegram instance
     * 
     * @return TelegramBot
     * @throws Exception 
     */
    public static function getInstance() {
        if (!self::$instance) {
            $app = App::getInstance();
            self::$instance = new TelegramBot($app);
        }

        return self::$instance;
    }

    /**
     * 
     * @param string $message 
     * @param string $chatIds 
     * @return ServerResponse
     * @throws TelegramException 
     */
    public function sendMessage(string $message, string $chatId) {
        return Request::sendMessage([
            'chat_id'   => $chatId,
            'text'      => $message
        ]);
    }

}


?>