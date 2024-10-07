<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */

/**
 * @author Cheok Jia Fuei <cheok@silverstream.my>
 * @version 1.0
 * @created 03-Nov-2020
 */

namespace Snap\util\pushnotification;

use Fcm\Request;
use GuzzleHttp\Client;
use Snap\TLogging;

class SnapFcmClient extends \Fcm\FcmClient
{
    use TLogging;
    private const SCOPE_FCM = "https://www.googleapis.com/auth/cloud-platform";

    protected $app = null;
    public $options = [];
    protected $apiKey = "";
    protected $senderid = "";

    public function __construct(\Snap\App $app, array $options = [])
    {
        $this->app = $app;
        $this->apiKey = $app->getConfig()->{'mygtp.firebase.serverkey'};
        $this->senderid = $app->getConfig()->{'mygtp.firebase.senderid'};

        if (!$this->apiKey || !$this->senderid) {
            $this->logDebug(__CLASS__.": Firebase server key or sender id not set");
            throw new \Exception("FCM key not set");
        }

        if ( isset( $options["http_errors"] ) ) {
             $options["http_errors"] = (bool)$options["http_errors"];
         } else {
             $options["http_errors"] = false;
         }
         $this->options = $options;
    }

    public function getGuzzleClient(): Client
    {
        return new Client($this->getHttpClientDefaults());
    }

    protected function getHttpClientDefaults()
    {
        $defaults =  [
            // 'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                // 'Authorization' => 'Bearer ' . self::getFirebaseAccessToken($this->app),
                'Authorization' => 'key='.$this->apiKey,
                'project_id'    => $this->senderid
            ],
            'http_errors'   => $this->options["http_errors"]
        ];

        $defaults = array_merge($this->options, $defaults);

        return $defaults;
    }




    /**
     * @param Request $request
     *
     * @return array
     */
    public function send(Request $request): array
    {
        // Build guzzle api client.
        $client = $this->getGuzzleClient();

        // Generate request url.
        $url = $request->getUrl();

        // Create and send the request.
        $response = $client->post($url, [
            'json' => $request->getBody()
        ]);

        // Decode the response body from json to a plain php array.
        $body = json_decode($response->getBody()->getContents(), true);
        $body['statusCode'] = $response->getStatusCode();
        if ($body === null || json_last_error() !== JSON_ERROR_NONE) {
            $body['error'] =  $response->getReasonPhrase();
        }

        return $body;
    }


    /**
     * Code below not used for now. (FCM HTTPv1 / OAuth 2.0)
     * Requires "google/auth" in composer.json
     */

    /**
     * Gets access token from cache or from firebase servers
     *
     * @param \Snap\App $app        App
     *
     * @return string
     */
    public static function getFirebaseAccessToken($app)
    {
        $tokenArr = json_decode(self::getFirebaseAccessTokenJson($app), true);
        $token = $tokenArr['access_token'];

        return $token;
    }

    /**
     * Get the full access token object in json format
     *
     * @return string
     */
    public static function getFirebaseAccessTokenJson($app)
    {
        // Try to get from cache first
        $project = $app->getConfig()->{'projectBase'};
        $token = $app->getCache("fcm_access_token_$project");

        // Get new token if not present in cache
        if (! strlen($token)) {
            $tokenArr = self::getFreshFirebaseAccessToken($app);
            $token = json_encode($tokenArr);

            $app->setCache("fcm_access_token_$project",
                           $token,
                           $tokenArr['expires_in']);

        }

        return $token;
    }


    /**
     * Gets a fresh access token from firebase.
     * This method should be less preferred than getFirebaseAccessToken()
     *
     * @return array
     */
    public static function getFreshFirebaseAccessToken($app)
    {
        // $credentialPath = $app->getConfig()->{'mygtp.firebase.serviceaccount'};
        // if (! strlen($credentialPath)) {
        //     throw new \Exception("Firebase credential configuration file path not found.");
        // }
        // // https://github.com/googleapis/google-auth-library-php
        // // https://github.com/googleapis/google-api-php-client/issues/1714

        // // Specify path to app credentials
        // putenv("GOOGLE_APPLICATION_CREDENTIALS=$credentialPath");

        // // Create credential loader
        // $credentials = CredentialsLoader::makeCredentials(self::SCOPE_FCM,
        //                                    json_decode(file_get_contents($credentialPath), true));

        // // Get the access token
        // $token = $credentials->fetchAuthToken();
        // if (!$token || ! is_array($token)) {
        //     throw new \Exception("Unable to mint access token.");
        // }

        // return $token;
    }
}


?>