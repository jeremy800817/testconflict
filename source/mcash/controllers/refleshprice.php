<?php 
if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

$sessiontoken = $_SESSION['token'];
echo $sessiontoken;
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => $_SESSION['APIURL'],
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_POSTFIELDS =>'{
    "version": "1.0my",
    "merchant_id": "' .  $_SESSION['par_code'] .'",
    "action": "pricestream"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $sessiontoken ,
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

$data = json_decode($response, true);
  
    $_SESSION['uuid'] = $data['data'][0]['uuid'];
    $_SESSION['customerbuy'] = $data['data'][0]['companysell'];
    $_SESSION['customersell'] = $data['data'][0]['companybuy'];
    




?>