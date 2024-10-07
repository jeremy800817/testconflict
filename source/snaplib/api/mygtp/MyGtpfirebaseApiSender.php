<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2020
 * @copyright Silverstream Technology Sdn Bhd. 2020
 */
Namespace Snap\api\mygtp;

use Google\Auth\ApplicationDefaultCredentials;
use Google\Auth\CredentialsLoader;
use GuzzleHttp\HandlerStack;
use Snap\InputException;

/**
 * This class implements responding to client request with JSON formatted data
 *
 * @author Cheok <cheok@silverstream.my>
 */
class MyGtpfirebaseApiSender extends MyGtpApiSender
{
    // https://developers.google.com/identity/protocols/oauth2/scopes
    private const SCOPE_FCM = "https://www.googleapis.com/auth/cloud-platform";

    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null)
    {
        // This sender is not needed at the moment unless 
        // Google deprecates/disables the FCM Legacy Http API

        throw new InputException("This class is not used at the moment.", InputException::GENERAL_ERROR);
        $client = new \GuzzleHttp\Client($this->getHttpClientDefaults($app));

        return json_encode($responseData);
        
    }

    protected function getHttpClientDefaults($app)
    {
        $defaults =  [
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json; UTF-8',
                'Authorization' => 'Bearer ' . $this->getAccessToken($app),
            ],
        ];

        return $defaults;
    }

    /**
     * Gets access token from cache or from firebase servers
     * 
     * @param \Snap\App $app        App
     */
    public function getAccessToken($app)
    {
        // Try to get from cache first
        $project = $app->getConfig()->{'projectBase'};
        $token = $app->getCache("fcm_access_token_$project");

        // Get new token if not present in cache
        if (! strlen($token)) {
            $tokenArr = $this->getFreshAccessToken($app);

            $app->setCache("fcm_access_token_$project", 
                           $tokenArr['access_token'],
                           $tokenArr['expires_in']);

            $token = $tokenArr['access_token'];
        }
        return $token;
    } 

    public function getFreshAccessToken($app)
    {
        $credentialPath = $app->getConfig()->{'mygtp.firebase.credentialpath'};
        if (! strlen($credentialPath)) {
            throw new \Exception("Firebase credential configuration file path not found.");
        }
        // https://github.com/googleapis/google-auth-library-php
        // https://github.com/googleapis/google-api-php-client/issues/1714

        // Specify path to app credentials
        putenv("GOOGLE_APPLICATION_CREDENTIALS=$credentialPath");

        // Create credential loader
        $credentials = CredentialsLoader::makeCredentials(self::SCOPE_FCM, 
                                           json_decode(file_get_contents($credentialPath), true));

        // Get the access token
        $token = $credentials->fetchAuthToken();
        if (!$token || ! is_array($token)) {
            throw new \Exception("Unable to mint access token.");
        }

        return $token;
    }

    /**
     * Disallow instantiation of the class through new.
     */
    protected function __construct()
    {
    }
}
?>