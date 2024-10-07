<?php

use Snap\utils\pushnotification\SnapFcmClient;

/**
* Copyright Silverstream Technology Sdn Bhd.
* All Rights Reserved (c) 2020
* @copyright Silverstream Technology Sdn Bhd. 2020
*
* @author Cheok Jia Fuei <cheok@silverstream.my>
* @version 1.0
* @created 03-Nov-2020
*/

class SnapFcmClientTest extends BaseTestCase
{
    /** @var SnapFcmClient $client */
    private $client = null;

    public function setUp()
    {
        $this->client = new SnapFcmClient(self::$app);
    }

    // public function testGetBearerToken()
    // {
    //     $config = $this->client->getGuzzleClient()->getConfig("headers")['Authorization'];
    //     $this->assertStringMatchesFormat('Bearer %s', $config);
    // }

    public function testPushToBadTopic()
    {
        $noti = $this->client->pushNotification('1', '2', '3');
        $noti->addRecipient("badtopic")
             ->addData('test', 1);
        $resp = $this->client->send($noti);
        $this->assertGreaterThan(0, $resp['failure']);
    }
}