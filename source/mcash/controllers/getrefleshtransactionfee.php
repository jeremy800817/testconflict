<?php

if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
if (isset($_POST['settlementMethod'])) {
    $curl = curl_init();

if (($_POST['settlementMethod'] =="wallet")) {
  $settlementMethod = 'wallet';
} elseif (($_POST['settlementMethod'] =="bank_account")) {
  $settlementMethod = 'bank_account';
} else {
  $settlementMethod = 'fpx';
}


$_SESSION['settlementmethod'] = $settlementMethod;
$buyweigh =  $_SESSION['weight'];
$type = $_POST['type'];
$_SESSION['type'] = $type;
$customerbuy = $_SESSION['customerbuy'];


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


//$customersell =  $_SESSION['customersell'];
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
    "action": "aqad",
    "product": "DG-999-9",
    "uuid": "' . $_SESSION['uuid'] . '",
    "weight": "' . $buyweigh . '",
    "type": "'. $_SESSION['type'] .'",
    "settlement_method":"'.$_SESSION['settlementmethod'].'"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'],
    'Content-Type: application/json'
    
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);

    //logaccess($_SESSION['email'],"gettransactionfee",$response);
  $data = json_decode($response, true);
  echo $data;
  if ($data['success']) {
      $_SESSION['weight'] = $data['data']['weight'];
      $_SESSION['unit_price'] = $data['data']['price'];
      $_SESSION['amount'] = $data['data']['amount'];
      $_SESSION['transaction_fee'] = $data['data']['transaction_fee'];
      $_SESSION['total_transaction_amount'] = $data['data']['total_transaction_amount'];
      $_SESSION['settlement_method'] = $settlementMethod;      

      if ($settlementMethod === 'wallet') {
        $originalTotal = $_SESSION['total_transaction_amount'];
      // old wallet fees
      /*
        if ($type === 'spot_acesell') {
          $fees = $originalTotal  * 0.25/100;
          $finalTotalWallet = $fees + $originalTotal;          
        } else {
          $fees = 0;
          $finalTotalWallet = $originalTotal - $fees;          
        }
        */

        // New Wallet Fees
        $fees = 0;
        $finalTotalWallet = $originalTotal - $fees;    

        $_SESSION['original_total'] = $originalTotal;
        $_SESSION['wallet_fee'] = $fees;
        $_SESSION['transaction_fee'] = $fees;
        $_SESSION['total_wallet'] = $finalTotalWallet;
        $_SESSION['total_transaction_amount'] = $finalTotalWallet;
      }
      //$_SESSION['bank_fee'] = ???;
   
  }else{

    if ($data['error'] === 50000) {
      $errormessage = $lang['PendingConversionErr'];
    } elseif ($data['error'] === 40009) {
      $errormessage = $lang['TradingHourErr'];
    } elseif ($data['error'] === 20006) {
      $errormessage = $lang['AccountNumberErr'];
    } else {
      $errormessage = $data['error_message'];
    }
    
    $_SESSION['get_transaction_error_message'] = $errormessage;
    $_SESSION['get_transaction_error_message_type'] = $_POST['type'];
   // header( "location:../failedbuysell.php"); 
    //echo '<script>alert(' . $errormessage . '". Please try again.")</script>';
  }
  //$partnerurl ="https://standalone.gopayz.com.my/standalone/doPayment/?channel=ACE&payload=v1";
  //$_SESSION['walleturl'] = $partnerurl;


}













?>