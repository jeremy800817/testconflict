<?php

include_once('common.php');

if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
if (isset($_POST['wallet'])) {

$sellcampaigncode = $_POST['sellcampaigncode'];
$settlementMethod = 'wallet';
  

$pin1 = $_POST['pin1'];
$pin2 = $_POST['pin2'];
$pin3 = $_POST['pin3'];
$pin4 = $_POST['pin4'];
$pin5 = $_POST['pin5'];
$pin6 = $_POST['pin6'];

$pin = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;

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
    "merchant_id": "' .  $_SESSION['par_code'] .'",
    "action": "spot_acebuy",
    "product": "DG-999-9",
    "settlement_method": "'.$settlementMethod.'",
    "weight": "' .  $_SESSION['weight'] .'",
    "uuid": "' . $_SESSION['uuid']  .'",
    "from_alert": false,
    "campaign_code": "' . $sellcampaigncode .'",
    "partner_data": "' . $_SESSION['payload'] .'",
    "pin": "' . $pin  .'"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($response, true);

    if ($data['success']) {
      $_SESSION['lastaction'] ="creditbywallet";
      parse_str($data['data']['location'], $requestData);
     
    }else{

      if ($data['error'] === 50000) {
        $_SESSION['get_transaction_error_message'] = $lang['PendingConversionErr'];
      } elseif ($data['error'] === 40009) {
        $_SESSION['get_transaction_error_message'] = $lang['TradingHourErr'];
      } elseif ($data['error'] === 20006) {
        $_SESSION['get_transaction_error_message'] = $lang['AccountNumberErr'];
      } else {
        $_SESSION['get_transaction_error_message'] = $data['error_message'];
      }
      
        //echo '<META HTTP-EQUIV="Refresh" Content="0; URL=../failedpayment.php">';
        //echo '<script>alert("'.$data['error_message'] .'")</script>';
        $_SESSION['get_transaction_error_message_type'] = 'spot_acebuy';        
        header("Location: ../failedbuysell.php");
        exit();
        
    }
}