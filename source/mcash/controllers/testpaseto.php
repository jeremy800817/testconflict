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
include('config/db.php'); 
//$privateKey = new AsymmetricSecretKey(file_get_contents("C:\laragon\www\gtp2\source\gopayz\controllers\key\privatersa.pem"), new Version1());
$token ="v1.public.eyJyZXF1ZXN0RGF0ZSI6IjIwMjEwNjA5MTczNzExIiwiY2hhbm5lbFVzZXJJZCI6IjI1MjE5MTMwIiwiaWRUeXBlIjoiTiIsImlkTm8iOiI4NjA2MTAwODkwOTYiLCJuYXRpb25hbGl0eSI6Ik1ZIiwiZW1haWwiOiJqY2hlb25nODYxQGdtYWlsLmNvbSIsInRlbEhwIjoiNjAxMjY4Mzg2MjEiLCJuYW1lIjoiSk9FIENIRU9ORyIsImRvYiI6IjE5ODYwNjEwIiwiaG9tZUFkZHJlc3MxIjoiMSwgSkFMQU4gUEFOR0tPT1IiLCJob21lQWRkcmVzczIiOiJUSVRJV0FOR1NBIFNFTlRSQUwiLCJob21lU3RhdGUiOiIxNCIsImhvbWVaaXAiOiI1MDQ1MCIsImhvbWVUb3duIjoiS1VBTEEgTFVNUFVSIiwiaG9tZUNvdW50cnkiOiJNWSIsIm1haWxBZGRyZXNzMSI6IjEsIEpBTEFOIFBBTkdLT1IiLCJtYWlsQWRkcmVzczIiOiJUSVRJV0FOR1NBIFNFTlRSQUwiLCJtYWlsU3RhdGUiOiIxNCIsIm1haWxaaXAiOiI1MDQ1MCIsIm1haWxUb3duIjoiS1VBTEEgTFVNUFVSIiwibWFpbENvdW50cnkiOiJNWSIsImxhbmd1YWdlIjoiZW4iLCJjaGFubmVsTG9naW5Ub2tlbiI6eyJ0eXBlIjoiQnVmZmVyIiwiZGF0YSI6WzcxLDIxMyw4Miw0OSwyLDE2MCwxOTQsMjIzLDExNCwxODEsMTM5LDgzLDEwMCwxMjEsNDYsNjgsMTAxLDE2NiwyMTcsOTYsMTE2LDE0NCwxMTEsMTQzLDIyNSw1Niw1OCwxMzMsMjI3LDIxMiwxMTUsMTAyLDIzOCw4NCwxNzIsMjgsMzEsNzAsMTQ3LDE5NywyMzEsMjU0LDE5NSwxMzcsOTgsMTQ2LDIwMSwxMDIsMTI1LDY0LDE5MCw3NSwxNjMsMTgxLDExOCwxMzUsMjI4LDI0LDEzOCwyNTIsMTU2LDIyOSwxMDEsMjEwLDg3LDI1MSwxMTMsNTgsODUsNiwyNTMsMjAzLDI1LDI0NiwxMjQsNzQsNSwxNzgsMjAwLDE1LDIyNiw1OCwxODksMjQ3LDE5OCw0OSwyMzksMTIsMTA2LDYwLDE3OSw0NSwxNjMsMjQxLDYzLDE2MywxMTgsOCw5NCwxMTgsMTQ4LDE4MywyNDYsMTM2LDk5LDQsNTAsMTc3LDIyNSwyMzUsMTk0LDIzMCwyMzcsMTcyLDM2LDE5NiwyMzAsMTYwLDE4NCwxOTMsNjUsMTM2LDEwOSw2NiwxMTMsMTM3LDE2MiwxNjQsNTksMjU0LDk2LDIxNywxNjgsMjI3LDQ3LDIzOSwxMzQsMzgsMTQ5LDE5MywyMTIsOCwxNTYsMzMsMjMzLDE2NiwxMzgsMTA4LDI2LDIwNywxNjQsMjExLDE4NiwxNjMsMSw2NCw1Myw1NywyNDQsMjExLDIyMywyNDEsMTU3LDE4NSwyNTQsMTcxLDM2LDExNSwyMDgsMTMzLDg1LDEwMSwxNjgsMTA0LDc2LDQ0LDQ3LDIzMCw5MSwxNjUsMjE4LDI3LDEzMCwzOSw5NiwxNjgsMTcyLDE1MSwyMDksMTk4LDcyLDE2LDc3LDI3LDE1NSwxMiwyMzksNDYsMSwyMDIsMTgyLDE5Myw2LDIwOCw3MSwyNDEsMTczLDI0LDIyMiwyMjIsMTEyLDE3MCwxMDAsMTY1LDM4LDI1MCwyMjEsMTcxLDIxMSwxMjIsOTgsMjMwLDE5LDIwMywxMTUsMTY3LDkzLDIwMSwyLDE5MCw1MywyMDgsMTAyLDg2LDExOSwyMDIsMjI4LDUwLDE1Nyw2MiwyNTAsMTI5LDE1MywxMzgsMjE3LDIxOSwxNDMsNzgsMjM4LDE4OSwxMjUsMTE3LDIwNiw3Niw2OCwxNDRdfSwicGFydG5lcklkIjoiMDAwMDAwMDAwMDExNzA4IiwiY2hhbm5lbCI6IkdPUEFZWiJ9XxP-2Uwwo7rYiaT6CCAMcqPJykpDFrFiiwoFZ85PVv4CHFr0X-jFIo2QUKqL4xNFlDimYOsKiUm7IiUsLXu9_ZwrjBF1l8w39m4i1AfPpnUcC_S3ZrskVQgd0RLqrrf8jp5zfjTLOz0esdd0q6-finvs9E-1CdDld6YUosFfn5LXcWRu5K0GZw1IgEEpJy-H6szUTAsb4oxSZZFTWphfcc8ND876j0ds62bKhUmC9wQdCb_rolTZYaCc_ScQDA2dvbhPvMIXThNUiNIeGATvilGRT7BWVCaHKnVYQEBhsuO3p2vZ3Z0KY5PRSjVLL46YWeJzGGJpb23f-beBewrZrw";
$publicKey = new AsymmetricPublicKey(file_get_contents("C:\laragon\www\gtp2\source\gopayz\controllers\gopayz\publicKey.pem"), new Version1()); 

//$sharedKey = new Asyme(random_bytes(32));
//echo $res;
 //$rsaPrivate = file_get_contents('privatersa.pem');
// $privateKeyV1 = new AsymmetricSecretKey($rsaPrivate, new Version1());
//$publicKeyV1  = $privateKeyV1->getPublicKey();
//$vsToken = Version1::generateAsymmetricSecretKey();
//$privateKey = new AsymmetricSecretKey(sodium_crypto_sign_keypair());
//$privateKey = new AsymmetricSecretKey(sodium_crypto_aead_aes256gcm_keygen());
//$privateKey = new AsymmetricSecretKey(file_get_contents('file:/gopayz/controllers/privatersa.pem'));
//$sharedKey = new SymmetricKey(sodium_crypto_sign_keypair());
//$token = Builder::getLocal($sharedKey, new Version1());
//$privateKeyV1 = new AsymmetricSecretKey($rsaPrivate, new Version1());
  //  $publicKeyV1  = $privateKeyV1->getPublicKey();
//echo $publicKey;
/*
  $token = Builder::getPublic($privateKey, new Version1());

  $token = (new Builder())
  ->setKey($privateKey)
  ->setVersion(new Version1())
  ->setPurpose(Purpose::public())
  // Set it to expire in one day
  ->setIssuedAt()
  //->setIssuer('ace')
  //->setNotBefore()
  //->setExpiration(
  //    (new DateTime())->add(new DateInterval('P01D'))
 // )
  // Store arbitrary data
  ->setClaims([
      "requestDate" => '2021-05-01',
      "channelUserId" => '1211As321',
      'idType' => 'N',
      'idNo' => '807677093433',
      'nationality' => 'MY',
      'email' => 'jeff@silverstream.my',
      'telHp' => '0145565576',
      'channel' => 'GOPAYZ',
      'name' => 'Andy',
      'dob' => '1995-04-01',
      'homeAddress1' => 'No 1, Jalan Kuching',
      'homeAddress2' => '56000 KL',
      'homeAddress3' => '',
      'homeAddress4' => '',
      'homeState' => '14',
      'homeZip' => '56000',
      'homeTown' => 'KL',
      'homeCountry' => 'Malaysia',
      'language ' => 'bm',
      'channelLoginToken' => 'avbnsmmmksjjjjskjs',
      'partnerId' => 'avbnsmmmksjjjjskjs',
      'kycInd' => '0',
      'locAcct' => '123423224456'
     
  ]);
//echo $token->toString(); // Converts automatically to a string
/*
  $decoded = $token->getClaims();
        $gname = $decoded['channelUserId'];
        $gname = $decoded['idType'];
        $gname = $decoded['idNo'];
        $gname = $decoded['nationality'];
        $gname = $decoded['email'];
        $gname = $decoded['telHp'];
        $gname = $decoded['channel'];
        $gname = $decoded['name'];
        $gname = $decoded['dob'];
        $gname = $decoded['homeAddress1'];
        $gname = $decoded['homeAddress2'];
        $gname = $decoded['homeAddress3'];
        $gname = $decoded['homeAddress4'];
        $gname = $decoded['homeState'];
        $gname = $decoded['homeZip'];
        $gname = $decoded['homeTown'];
        $gname = $decoded['homeCountry'];
        $gname = $decoded['channelLoginToken'];
        $gname = $decoded['partnerId'];
        $gname = $decoded['kycInd']; //basic-0, premium-1
$gname = $decoded['locAcct']; //wallet account number
$gname = $decoded['channelLoginToken'];
        $gname = $decoded['channelLoginToken'];


*/
// var_dump($token->toString());exit;
//$token_string = $token->toString();
//echo $token_string ;
//$token = Version1::sign($token, $privateKey);
//echo "channel=GOPAYZ&payload=" . $token;
//echo ' <br/> ';

//$token = Version1::verify($token, $publicKey);
$parser = Parser::getPublic($publicKey, ProtocolCollection::v1());
// This is the same as:
// $parser = (new Parser())
//     ->setKey($publicKey)
//     // Adding rules to be checked against the token
//     //->addRule(new ValidAt)
//     ->addRule(new IssuedBy('issuer defined during creation'))
//     ->setPurpose(Purpose::public())
//     // Only allow version 2////
//     ->setAllowedVersions(ProtocolCollection::v2());
//     //$parser->parse($token);
//   //  echo $parser;
try {
  //echo $token;
 // $arJson = json_decode( $strJson, true );

  $token = $parser->parse($token);
  
  //echo $claims;
} catch (PasetoException $ex) {
    /* Handle invalid token cases here. */
   echo ('error: '. $ex);
}
$decoded = $token->getClaims();
echo "<pre>";
print_r($decoded['email']);exit;
echo "</pre>";
var_dump($token);
// print_r(json_decode($token->toString()));
var_dump($token instanceof \ParagonIE\Paseto\JsonToken);

# Version 2:
//$v2Token = Version2::encrypt($message, $key);

//var_dump((string) $v2Token);
// string(109) "v2.local.0qOisotef_M2W1gK0b6SiUrO4fkPb24Se0eNJAkALmDvS3IlVu-72birx07hIqU4MYtrCrTJTTElYaWxOyz5Wx8wXh8cQUOF6wOo"
//var_dump(Version2::decrypt($v2Token, $key));
// string(35) "This is a signed, non-JSON message."

?>