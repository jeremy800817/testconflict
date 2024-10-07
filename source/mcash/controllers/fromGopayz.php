<?php
namespace GOPAYZ;
require __DIR__ . "/../vendor/autoload.php";

use DateInterval;
use DateTime;
use ParagonIE\Paseto\Keys\{AsymmetricSecretKey, SymmetricKey};
 use ParagonIE\Paseto\Protocol\{Version1, Version2};
 use ParagonIE\Paseto\Builder;
 use ParagonIE\Paseto\Purpose;
 use ParagonIE\Paseto\Parser;
 use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Keys\Version1\AsymmetricPublicKey;
use ParagonIE\Paseto\Rules\{
    IssuedBy,
    ValidAt
};
use ParagonIE\Paseto\ProtocolCollection;

if (isset($_GET['channel'])) {
    $channel = $_GET['channel'];
    $payload = $_GET['payload'];
echo $channel;
echo $payload;

    if ($channel = "GOPAYZ") {
        $res = openssl_pkey_get_private(file_get_contents("C:\Users\HP\Downloads\Gopayz\gopayz\controllers\key\privatersa.pem"));
        echo $res;
     //   $privateKey = new AsymmetricSecretKey(sodium_crypto_sign_keypair());
        $publicKey = $_SESSION['publicKey'];


        $token = Version2::verify($token, $publicKey);
        $parser = Parser::getPublic($publicKey, ProtocolCollection::v2());

        try {
            //echo $token;
            // $arJson = json_decode( $strJson, true );
  
            $token = $parser->parse($payload);
    
            //echo $claims;
        } catch (PasetoException $ex) {
            /* Handle invalid token cases here. */
            echo('error: '. $ex);
        }
        $decoded = $token->getClaims();
        $channelUserId = $decoded['channelUserId'];
        $idType = $decoded['idType'];
        $idNo = $decoded['idNo'];
        $nationality = $decoded['nationality'];
        $email = $decoded['email'];
        $telHp = $decoded['telHp'];
        $channel = $decoded['channel'];
        $name = $decoded['name'];
        $dob = $decoded['dob'];
        $homeAddress1 = $decoded['homeAddress1'];
        $homeAddress2 = $decoded['homeAddress2'];
        $homeAddress3 = $decoded['homeAddress3'];
        $homeAddress4 = $decoded['homeAddress4'];
        $homeState = $decoded['homeState'];
        $homeZip = $decoded['homeZip'];
        $homeTown = $decoded['homeTown'];
        $homeCountry = $decoded['homeCountry'];
        $channelLoginToken = $decoded['channelLoginToken'];
        $partnerId = $decoded['partnerId'];
        $kycInd = $decoded['kycInd']; //basic-0, premium-1
$locAcct = $decoded['locAcct']; //wallet account number
$channelLoginToken = $decoded['channelLoginToken'];
        $channelLoginToken = $decoded['channelLoginToken'];

        echo "<pre>";
        print_r($decoded['channelUserId']);
        exit;
        echo "</pre>";
        var_dump($token);
        // print_r(json_decode($token->toString()));
        var_dump($token instanceof \ParagonIE\Paseto\JsonToken);
    }
}

?>