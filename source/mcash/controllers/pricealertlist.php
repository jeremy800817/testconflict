<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
$today = date("Y-m-d");
$date = strtotime('' . $today .' -1 year');

$yearbefore = date('Y-m-d', $date);

$curl = curl_init();

curl_setopt_array($curl, array(
CURLOPT_URL => $_SESSION['APIURL'],
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS =>'{
"version": "1.0my",
"merchant_id": "' .  $_SESSION['par_code'] . '",
"action": "price_alerts",
"page_number": 1,
"page_size": 10,
"date_from": "'.  $yearbefore . ' 00:00:00",
"date_to": "'. $today .' 23:59:59"
}',
CURLOPT_HTTPHEADER => array(
'Authorization: Bearer ' . $_SESSION['token'],
'Content-Type: application/json',
'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
),
));

$response = curl_exec($curl);

curl_close($curl);
// echo $response;
$data = json_decode($response, true);
$priceAlerts = [];
if ($data['success']) {
  $priceAlerts = $data['data'];
}


?>