<?php

include_once('log.php');
include_once('config/db.php');

if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}

if (isset($_POST['fpx']) || isset($_POST['wallet'])) {

    if (isset($_POST['wallet'])) {
      $_SESSION['lastaction'] = "walletconvertion";
      $payMethod = 'wallet';
    } else {
      $_SESSION['lastaction'] = "conversionfpx";
      $payMethod = 'fpx';
    }
  
    $_SESSION['payment_mode'] = $payMethod;

    validateConversionInfo($_POST, $lang);
    
    $product = $_POST['product'];
    $_SESSION['product'] = $product ;

    $weight = $_POST['weight'];
    $_SESSION['weight'] = $weight ;

    $quantity = $_POST['quantity'];
    $_SESSION['quantity'] = $quantity ;

    $totalWeight = $weight * $quantity;
    $_SESSION['gram'] = $totalWeight;

    $data = editProfile($_POST);

    if ($data['success']) {
      $data = doConversion($_POST, $payMethod);

      if ($data['success'] == true) {
        $_SESSION['transTime1'] = date('Y-m-d H:i:s');
        if(isset($_SESSION['transTime2'])){
            unset($_SESSION['transTime2']);
        }
        $location = $data['data']['location'];
        // Used in dashboard-buy-gold-redirect-wallet.php

        header('location: ../dashboard-convert-gold-redirect-wallet.php?' . $location);
        exit();
      } else {
        $location = '';
        $createdOn = New DateTime("now", new DateTimeZone("Asia/Kuala_Lumpur"));
			  $createdOn = $createdOn->format('Y-m-d H:i:s');
        // $createdOn = date('Y-m-d H:i:s');
        $status = $lang['Failed'];
      }

    }else{
      
      // $_SESSION['Reviewaddress'] = "Error";
      // $_SESSION['Message'] = $data['data']['message'];
      if ($data['error'] === 50000) {
        $_SESSION['conversion_error'] = $lang['PendingConversionErr'];
      } else {
        $_SESSION['conversion_error'] = $data['error_message'];
      }

      header("location: ./convert-review.php");
      exit();
    }
}


function editProfile($data) 
{
    $address_line_1 = $data['address_line_1'];
    $address_line_2 = $data['address_line_2'];
    $city = $data['city'];
    $postcode = $data['postcode'];
    $state = $data['state'];
    $nok_full_name = $data['nok_full_name'];
    $nok_phone = $data['nok_phone'];
    $pin = implode($data['pin']);
    $_SESSION['address_line_1'] = $address_line_1;
    $_SESSION['address_line_2'] = $address_line_2;
    $_SESSION['postcode'] = $postcode;
    $_SESSION['city'] = $city;
    $_SESSION['state'] = $state;


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
    "merchant_id": "'. $_SESSION['par_code'] .'",
    "action": "profile_update",
    "address_line_1": "'. $address_line_1  . '",
    "address_line_2": "'. $address_line_2 .'",
    "postcode": "'. $postcode . '", 
    "city": "'. $city .'",
    "state": "'. $state .'",
    "nok_full_name": "'. $nok_full_name .'",
    "nok_phone": "' . $nok_phone .'",
    "nok_email": "",
    "nok_address": "",
    "nok_relationship": "",
    "occupation_category_id": 3,
    "occupation_subcategory_id": "",
    "referral_salesperson_code": "",
    "pin": "'. $pin  .'"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'],
    'Content-Type: application/json'
  ),
));

    $response = curl_exec($curl);
    logaccess($_SESSION['email'],"editprofile",$response);
  curl_close($curl);
  //echo $response;
  $data = json_decode($response, true);

  return $data;
}

function doConversion($data, $paymentMode) {
  $curl = curl_init();
  $pin = implode($data['pin']);
  $campaignCode = $data['campaigncode'];

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
    "merchant_id": "'. $_SESSION['par_code'] .'",
    "action": "conversion",
    "product": "'. $_SESSION['product'] .'",
    "quantity": '. $_SESSION['quantity'] . ' ,
    "pin": "'. $pin . '",
    "payment_mode":"'. $paymentMode . '",
    "campaign_code":"'. $campaignCode . '",
    "partner_data": "'.$_SESSION['payload'].'"
  }',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json'
  ),
));

  $response = curl_exec($curl);
  logaccess($_SESSION['email'],"convertion",$response);
  curl_close($curl);
 
  $data = json_decode($response, true);
  
  return $data;
}

function validateConversionInfo($data, $lang) {

  $pass = true;

  if (strlen($data['address_line_1']) === 0) {
    $pass = false;
    $_SESSION['error_address_line_1'] = $lang['FieldRequired'];
  }

  if (strlen($data['city']) === 0) {
    $pass = false;
    $_SESSION['error_city'] = $lang['FieldRequired'];
  }

  if (strlen($data['postcode']) === 0) {
    $pass = false;
    $_SESSION['error_postcode'] = $lang['FieldRequired'];
  }

  if (strlen($data['state']) === 0) {
    $pass = false;
    $_SESSION['error_state'] = $lang['FieldRequired'];
  }
  
  if (strlen(implode($data['pin'])) < 6) {
    $pass = false;
    $_SESSION['error_pin'] = $lang['IncorrectPIN'];
  }

  if (! $pass) {
    header('location: ./convert-review.php');
    exit();
  }
}
