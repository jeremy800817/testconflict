<?php

include_once('../common.php');

if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}


if(isset($_POST['submit_form']))
{
  //$formtype = $_POST['formtype'];
  
  // Temp M2U fix
  // if bank is m2u
  if($_POST['bank'] == 'Malayan Banking Berhad (M2U)'){
    $_POST['bank'] = 'Malayan Banking Berhad';
  }
  // string checker
  if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $_POST['bank']))
  {
    $_POST['bank'] = preg_replace("/\(|\)/",'', $_POST['bank']); // Removes special chars.
      // one or more of the 'special characters' found in $string
  }


  $pin1 = $_POST['pin1'];
  $pin2 = $_POST['pin2'];
  $pin3 = $_POST['pin3'];
  $pin4 = $_POST['pin4'];
  $pin5 = $_POST['pin5'];
  $pin6 = $_POST['pin6'];

  $error="No";
  $pin = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;

  // Start
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
        "action": "bank_account_update",
        "bank_id": "' . $_POST['bank'] .'",
        "bank_acc_name": "' . $_POST['username'] .'",
        "bank_acc_number": "' . $_POST['accountnumber'] .'",
        "pin": "' . $pin .'"
    }',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer ' . $_SESSION['token'],
        'Content-Type: application/json',
        'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
      ),
    ));
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    
    // End
    $data = json_decode($response, true);

    if ($data['success']) {
      $_SESSION['bank_id'] = $_POST['bank'];
      $_SESSION['bank_name_editbank'] = $_POST['bankname'];
      $_SESSION['bank_acc_number'] = $_POST['accountnumber'];
      $_SESSION['bank_acc_name'] = $_POST['username'];
      header( "location:../editbankaccountnumber-success.php"); 
    }else{
      //error_message
      if ($data['error'] === 20005) {
        $_SESSION['edit_bank_error_message'] = $lang['IncorrectPIN'];
      } else {
        $_SESSION['edit_bank_error_message'] = $data['error_message'];
        
      }
      header( "location:../editbankaccountnumber-failure.php"); 
    }
}
/*
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
    "action": "bank_account_update",
    "bank_id": 1,
    "bank_acc_name": "lee chong wei",
    "bank_acc_number": "669655421145",
    "pin": "123123"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
*/


?>