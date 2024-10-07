<?php
if(!isset($_SESSION)) {
  session_set_cookie_params(0);
  session_start();
}
include('log.php');
if (isset($_POST['submit_form'])) {
   
  $pin1 = $_POST['pin1'];
  $pin2 = $_POST['pin2'];
  $pin3 = $_POST['pin3'];
  $pin4 = $_POST['pin4'];
  $pin5 = $_POST['pin5'];
  $pin6 = $_POST['pin6'];


  $error="No";
  $code = $pin1 . $pin2 . $pin3 . $pin4 . $pin5 . $pin6 ;
//echo $code;
     $newpin1 = $_POST['pinnumber1'];
     $newpin2 = $_POST['pinnumber2'];
     $newpin3 = $_POST['pinnumber3'];
     $newpin4 = $_POST['pinnumber4'];
     $newpin5 = $_POST['pinnumber5'];
     $newpin6 = $_POST['pinnumber6'];

$newpin = $newpin1 . $newpin2 . $newpin3 . $newpin4 . $newpin5 . $newpin6 ;
//echo $newpin;
   // $confirmpin = $_POST['confirmpin'];
    
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
    "action": "reset_pin",
    "new_pin": "'. $newpin  . '",
    "confirm_new_pin": "' . $newpin  . '",
    "code": "'. $code .'"
}',
  CURLOPT_HTTPHEADER => array(
    'Authorization: Bearer ' . $_SESSION['token'],
    'Content-Type: application/json',
    'Cookie: __cfduid=d26a069fadd3dbbdb73c6d695ccb062141619581503'
  ),
));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response;
    $data = json_decode($response, true);
    logaccess($_SESSION['Temail'] , "resetpin", $response );
   
    if ($data['success']) {
    
      header( "location:../resetpin-success.php"); 
    }else{
      //error_message
    
      header( "location:../resetpin-failure.php"); 
    }
}

?>