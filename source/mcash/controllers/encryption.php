<?php

$email = "FinmomentaSample@perfios.com";
$server = "demo.perfios.com";
$vendor = "finmomenta";
$returnURL = "https://www.google.com";
$applicationId = "dummyApplicationId";
$perfiosTransactionId = "PLEASE UPDATE ME";
$format = "xml";

$privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
    "MIIEpQIBAAKCAQEAx76oeNWYPkAbbSPyvJcPkQvAMvIHCPgY9yNdN/qsYMHytyit\n" .
    "xdO0aTatgfS/ig4zmqKFVpC9o2YMyQ7E1FYNcl/ev++r4nV+qVXA1OKcsSv4Kbj/\n" .
    "iNwxGmnhBsRDswY/dEZZDN9RisRpo9NRSOskGRv+nLEA2dLgj0/f9SSzykz3cxAP\n" .
    "bq6bV0unie5C8r6RALj+hTLU7B7QF88SkDsFx0/TYQe4H9QJtFMWJtOnhumY5Ku6\n" .
    "CL4OdZrtz71y4ji8IxQxv2nyq0JwYQ4rG84uzJQeRQf2RaJLhnSdfQnonDkP7L1z\n" .
    "NSizzi8VgHI+GVIo9FrLj7DVo+fFzMucbsvvNQIDAQABAoIBAQCMVKOR+SYzneBm\n" .
    "5hmUa2CxW5sVb7qHj54iiwLj4EYY2EnIaljjol+eh56Qrb2fpWiV3FZnQdspn/md\n" .
    "i7W3JBngYABjwmN0/20UVL3cErVZN/XqgiFtKp2I3BgPI/YYIWyVKRNJGt/z6Rf6\n" .
    "0+zImQLMbUGNHkHlxuSjas+CL93sYrXo52TXqZgk40gEkQVLE+SLrtXTFiOOlX/s\n" .
    "WWXyeUY14hl+oVQLmEO6UZd532bxAE0VlIV6Vr2pE3gJqEyaAoGgkT3inxvRPiek\n" .
    "swRm9OONWZD9frKXYqabJTsd87623Czg5h2WGimsN4fZ+LfyBXul24KKVeMDELHn\n" .
    "GvdRm95lAoGBAPMQRrb3iB8oYJc+4KwbtWR/vTQW++G69CeyIfD7WM0Ix3Gy0wod\n" .
    "FwIeKSkYsZ/R5n+9Ucx/RVFv7X86YzYajhH3hl+8/q4c+L1yAGS5hW3m21gIViDt\n" .
    "k1h3gKLI5o4EKGhCRX1teSoZ+n4G7KlYbJas8h5MX3u81GhKRmiVItr/AoGBANJg\n" .
    "KoIhxKsyNRccULosYZBGc3vpkFtpHeZ5w0qxbXaGveUIKvqzUqonGy0o3yqVNRrH\n" .
    "JJREHss+5/HqeuauawKUYLWapCqmVF6IlDc1PxTw+BLDzgzMlX2o43951iTMJXkd\n" .
    "80MOujsnyTdZq7wAzR7KNR3U/OjDFlcORxhCGrnLAoGBAONZcgtp9NTP+6j8k0Ho\n" .
    "mP5rzRmP9gHp0L3gjIbPUvxVHdhnn6ZyFzdP5sgd5ObMeoE5H+3bjYbi3o6Gmo3c\n" .
    "wM5lbDbYnI9XYgIxQ9TzAq8NpFTvV0Btd8jj3lpk9+IWWYVLl5v+bbrHmdmPuIWd\n" .
    "w9Qb6EwWu6kNss/pyXnBJV0ZAoGAPj+2VEsppn50tyHpwSzgsZAnG8NAs8umzUu6\n" .
    "PZ/ChA/aoKqKDSSCkVaA9Bvj7PW5gPLsH/MIKZuzhiGbvCZgA6Nj+liHuxb8X/yJ\n" .
    "3swink+vF95YWfEvSr9ukYm7k6fUbsIt+OmisV5Ua8xcxIR4LWQn02vyae1P7vKK\n" .
    "luL4hYECgYEAmSiiHa4bSLF8MT/IbL2YIrxK4yatABvVWZLkAV7hiFJWeEhLCmCd\n" .
    "OKcX8QSq9lT6TbS6NCEfHfCR0FFrny4nZMT3YnyDkgrYOiHhZL/YVfr3Izr62Gcy\n" .
    "PizNJH/JWoNDonAuFi+eQjgBRNfd894pMeCT4tMu2nE1SOEafzykzPA=\n" .
    "-----END RSA PRIVATE KEY-----";


$payloadStatement = "<payload>\n" .
    "<vendorId>" . $vendor . "</vendorId>\n" .
    "<txnId>" . $applicationId . "</txnId>\n" .
    "<yearMonthFrom>2017-06</yearMonthFrom>\n" .
    "<yearMonthTo>2017-12</yearMonthTo>" .
    "<emailId>#email#</emailId>\n<destination>statement</destination>\n" .
    "<returnUrl>" . $returnURL . "</returnUrl>\n" .
    "</payload>";


echo genericCreateHTML($payloadStatement, 'start', $email, $server, $privateKey);

function genericCreateHTML($payload, $operation, $email, $server, $privateKey)
{
    $email = encryptData($email, $privateKey);
    $payload = str_replace("#email#", $email, $payload);

    // Remove all line breaks
    $payload = str_replace("\n", "", $payload);

    $signature = getSignature($payload, $privateKey);

    $html = "<html>\n" . " <body onload='document.autoform.submit();'>\n" .
        "     <form name='autoform' method='post' action='https://" . $server . "/KuberaVault/insights/" . $operation . "'>\n" .
        "         <input type='hidden' name='payload' value='" . $payload . "'>\n" .
        "            <input type='hidden' name='signature' value='" . $signature . "'>\n" .
        "        </form>\n" . "  </body>\n" .
        "</html>\n";

    return $html;
}

function getSignature($data, $privateKey)
{
    // Make digest
    $digest = sha1($data);

    // Encrypt
    return encryptData($digest, $privateKey);
}

function encryptData($raw, $privateKey)
{
    $privateKey = openssl_pkey_get_private($privateKey);

    if (!$privateKey) {
        throw new RuntimeException('Invalid private key or passphrase');
    }

    // Encrypt digest using the key
    $encrypted = "";
    openssl_private_encrypt($raw, $encrypted, $privateKey, OPENSSL_PKCS1_PADDING);

    // Convert to Hex (base16)
    $result = bin2hex($encrypted);

    return $result;
}