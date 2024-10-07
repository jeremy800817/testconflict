<?php
include('log.php');
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
$curl = curl_init();
$id = $_POST['price_alert_id'];


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
    "action": "delete_price_alert",    
    "id": "'.  $id . '"    
  }',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'],
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
logaccess($_SESSION['email'],"deletepricealert",$response);
  $data = json_decode($response, true);
  header("location: ../price-alert.php");
?>