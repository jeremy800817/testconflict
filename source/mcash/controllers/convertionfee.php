<?php
include_once('log.php');
if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

if (isset($_POST['paybywallet']) || isset($_POST['paybyfpx'])) {

  if (isset($_POST['paybywallet'])) {
    $payMethod = 'wallet';
  } else {
    $payMethod = 'fpx';
  }

  $_SESSION['payment_mode'] = $payMethod;

    $curl = curl_init();
    $quantity = $_POST['quantity'];  
    $product  = $_POST['product'];
    $weight   = $_POST['weight'];

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
    "action": "conversion_fee",
    "product": "' . $product . '",
    "quantity": ' . $quantity . ',
    "payment_mode": "' . $payMethod . '"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($response, true);
    logaccess($_SESSION['email'],"convertionfee",$response);
    if ($data['success'] === false) {
      if ($data['error'] === 50000) {
        $_SESSION['conversion_error'] = $lang['PendingConversionErr'];
      } else {
        $_SESSION['conversion_error'] = $data['error_message'];
      }

      
      header('location: ./convert.php');
      exit();
    }
  
    $_SESSION['conversion_fee'] = $data['data']['conversion_fee'];
    // $_SESSION['insurance_fee'] = $data['data']['insurance_fee'];
    $_SESSION['total_fee'] = $data['data']['total_fee'];
    // $_SESSION['courier_fee'] = $data['data']['courier_fee'];
     $_SESSION['transaction_fee'] = $data['data']['transaction_fee'];
    //  $_SESSION['totalconvertion'] = $_SESSION['conversion_fee'] + $_SESSION['insurance_fee'] + $_SESSION['courier_fee']  + $_SESSION['transaction_fee'];
}
?>