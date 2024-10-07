<?php
if(!isset($_SESSION)) {
  session_start();
}


if(isset($_POST['code']))
{
  
$newic = $_SESSION['TidType'];
  

  $pin1 = $_POST['code'];
 

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
      header( "location:../editbankaccountnumber-success.php"); 
    }else{
      //error_message
      $errormessage = $data['error_message'];
      $_SESSION['edit_bank_error_message'] = $errormessage;
      header( "location:../editbankaccountnumber-failure.php"); 
    }
}

?>