<?php
$curl = curl_init();
     
curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://10.10.55.10/mygtp.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "version": "1.0my",
        "action": "get_account_no",
        "mykadno": "B2940912"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
    ),
));
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
$response = curl_exec($curl);

curl_close($curl);

$data = json_decode($response, true);

print_r($data);
