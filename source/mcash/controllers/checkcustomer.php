<?php
include_once('log.php');
if(!isset($_SESSION)) {
        session_set_cookie_params(0);
        session_start();
    }

if (isset($_POST['checkcontact'])) {

    // Join country code with contact input
    $phonenumber = $_POST['countrycode'] .  $_POST['contact'];
   
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
    "action": "checkcontact",
    "phone_number": "' . $phonenumber . '"
  
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'] ,
    'Content-Type: application/json',
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);

    $data = json_decode($response, true);
  
    if ($data['success'] === false) {
      if ($data['error'] === 50000) {
        $_SESSION['checkcustomer_error'] = $lang['CheckCustomerErr'];
      } else {
        $_SESSION['checkcustomer_error'] = $data['error_message'];
      }

      header('location: ../transfer.php?status=error');
      // header('location: ./convert.php');
      exit();
    }
  
    $_SESSION['transferfullname'] = $data['data']['fullname'];
    // $_SESSION['insurance_fee'] = $data['data']['insurance_fee'];
    $_SESSION['transferemail'] = $data['data']['email'];
    // $_SESSION['courier_fee'] = $data['data']['courier_fee'];
     $_SESSION['transferphone_number'] = $data['data']['phone_number'];
     $_SESSION['transferaccountcode'] = $data['data']['accountcode'];

     header('location: ../transfer-details.php');
    //  $_SESSION['totalconvertion'] = $_SESSION['conversion_fee'] + $_SESSION['insurance_fee'] + $_SESSION['courier_fee']  + $_SESSION['transaction_fee'];
}
?>